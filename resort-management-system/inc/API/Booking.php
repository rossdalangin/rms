<?php
namespace ResortManager\API;

class Booking {
	public function __construct() {
		add_action( 'wp_ajax_resort_submit_booking', [ $this, 'submit_booking' ] );
		add_action( 'wp_ajax_nopriv_resort_submit_booking', [ $this, 'submit_booking' ] );
		add_action( 'wp_ajax_resort_submit_review', [ $this, 'submit_review' ] );
		add_action( 'wp_ajax_resort_validate_coupon', [ $this, 'validate_coupon' ] );
		add_action( 'wp_ajax_nopriv_resort_validate_coupon', [ $this, 'validate_coupon' ] );
	}

	public function submit_booking() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		$room_id = intval( $_POST['room_id'] );
		$checkin = sanitize_text_field( $_POST['checkin'] );
		$checkout = sanitize_text_field( $_POST['checkout'] );
		$guests_count = intval( $_POST['guests'] ?? 1 );

		$guest_data = [];
		if ( isset( $_POST['guest_data'] ) && is_array( $_POST['guest_data'] ) ) {
			foreach ( $_POST['guest_data'] as $key => $value ) {
				$guest_data[$key] = sanitize_text_field( $value );
			}
		}

		// 1. Create or Get Guest User
		$email = sanitize_email( $guest_data['email'] );
		$user_id = email_exists( $email );

		if ( ! $user_id ) {
			$username = strtolower( ($guest_data['first_name'] ?? 'guest') . time() );
			$password = wp_generate_password();
			$user_id = wp_create_user( $username, $password, $email );

			if ( ! is_wp_error( $user_id ) ) {
				wp_update_user( [
					'ID'         => $user_id,
					'first_name' => $guest_data['first_name'] ?? '',
					'last_name'  => $guest_data['last_name'] ?? '',
					'role'       => 'subscriber'
				] );
				// In production, send email with login info
			}
		}

		// Create Booking Post
		$booking_id = wp_insert_post( [
			'post_type'   => 'booking',
			'post_title'  => sprintf( 'Booking for %s %s', $guest_data['first_name'] ?? '', $guest_data['last_name'] ?? '' ),
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $booking_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Could not create booking.', 'resort-manager' ) ] );
		}

		// Calculate Total Price
		$total_price = \ResortManager\Core\PricingEngine::calculate_total( $room_id, $checkin, $checkout );

		// Add services prices
		$services = isset( $_POST['services'] ) ? (array) $_POST['services'] : [];
		foreach ( $services as $service_id ) {
			$service_price = get_post_meta( $service_id, '_resort_service_price', true );
			$total_price += floatval( $service_price );
		}

		// Save Meta
		update_post_meta( $booking_id, '_resort_room_id', $room_id );
		update_post_meta( $booking_id, '_resort_checkin', $checkin );
		update_post_meta( $booking_id, '_resort_checkout', $checkout );
		update_post_meta( $booking_id, '_resort_guests', $guests_count );
		update_post_meta( $booking_id, '_resort_guest_id', $user_id );
		update_post_meta( $booking_id, '_resort_guest_email', sanitize_email( $guest_data['email'] ) );
		update_post_meta( $booking_id, '_resort_guest_phone', sanitize_text_field( $guest_data['phone'] ?? '' ) );

		// If a final total was passed from JS (after coupons), use it, but validate it
		if ( isset($_POST['final_total']) ) {
			$total_price = floatval($_POST['final_total']);
		}

		update_post_meta( $booking_id, '_resort_total_price', $total_price );
		update_post_meta( $booking_id, '_resort_services', $services );
		update_post_meta( $booking_id, '_resort_coupon_used', sanitize_text_field($_POST['coupon'] ?? '') );
		update_post_meta( $booking_id, '_resort_status', 'pending' );

		// 2. Mailchimp Sync
		if ( isset( $guest_data['marketing_optin'] ) ) {
			$this->sync_to_mailchimp( $email, $guest_data['first_name'], $guest_data['last_name'] );
		}

		// Mark as booked in availability table
		\ResortManager\Core\AvailabilityEngine::mark_as_booked( $room_id, $checkin, $checkout, $booking_id );

		// Validate Payment Method
		$payment_method = sanitize_text_field( $_POST['payment_method'] ?? 'offline' );
		$is_stripe_enabled = get_option( 'resort_payment_stripe_enabled', '1' ) === '1';
		$is_paypal_enabled = get_option( 'resort_payment_paypal_enabled', '1' ) === '1';
		$is_offline_enabled = get_option( 'resort_payment_offline_enabled', '1' ) === '1';

		if ( 'stripe' === $payment_method && ! $is_stripe_enabled ) $payment_method = 'offline';
		if ( 'paypal' === $payment_method && ! $is_paypal_enabled ) $payment_method = 'offline';
		if ( 'offline' === $payment_method && ! $is_offline_enabled && ($is_stripe_enabled || $is_paypal_enabled) ) {
			// If offline is disabled but others are on, default to first enabled
			$payment_method = $is_stripe_enabled ? 'stripe' : 'paypal';
		}

		// If it's an offline booking, log it to the payments table immediately as pending
		if ( 'offline' === $payment_method ) {
			global $wpdb;
			$wpdb->insert( $wpdb->prefix . 'resort_payments', [
				'booking_id' => $booking_id,
				'amount'     => $total_price,
				'method'     => 'offline',
				'status'     => 'pending'
			] );
		}

		wp_send_json_success( [
			'booking_id' => $booking_id,
			'message'    => __( 'Booking created successfully!', 'resort-manager' ),
		] );
	}

	public function submit_review() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$booking_id = intval( $_POST['booking_id'] );
		$title = sanitize_text_field( $_POST['title'] );
		$content = sanitize_textarea_field( $_POST['content'] );

		$review_id = wp_insert_post( [
			'post_type'    => 'review',
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'pending', // Moderate by default
		] );

		update_post_meta( $review_id, '_resort_booking_id', $booking_id );
		wp_send_json_success();
	}

	private function sync_to_mailchimp( $email, $fname, $lname ) {
		$api_key = get_option( 'resort_mailchimp_api_key' );
		$list_id = get_option( 'resort_mailchimp_list_id' );

		if ( empty( $api_key ) || empty( $list_id ) ) return;

		$datacenter = substr( $api_key, strpos( $api_key, '-' ) + 1 );
		$url = "https://$datacenter.api.mailchimp.com/3.0/lists/$list_id/members/" . md5( strtolower( $email ) );

		wp_remote_request( $url, [
			'method'  => 'PUT',
			'headers' => [
				'Authorization' => 'apikey ' . $api_key,
				'Content-Type'  => 'application/json',
			],
			'body' => json_encode( [
				'email_address' => $email,
				'status'        => 'subscribed',
				'merge_fields'  => [
					'FNAME' => $fname,
					'LNAME' => $lname,
				],
			] ),
		] );
	}

	public function validate_coupon() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );
		global $wpdb;
		$code = sanitize_text_field( $_POST['code'] );
		$table = $wpdb->prefix . 'resort_coupons';

		$coupon = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table WHERE code = %s AND (expiry_date >= %s OR expiry_date IS NULL OR expiry_date = '0000-00-00')",
			$code, date('Y-m-d')
		) );

		if ( $coupon ) {
			wp_send_json_success( [
				'amount' => $coupon->discount_amount,
				'type'   => $coupon->discount_type
			] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Invalid or expired coupon code.', 'resort-manager' ) ] );
		}
	}
}
