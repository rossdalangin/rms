<?php
namespace ResortManager\API;

class Booking {
	public function __construct() {
		add_action( 'wp_ajax_resort_submit_booking', [ $this, 'submit_booking' ] );
		add_action( 'wp_ajax_nopriv_resort_submit_booking', [ $this, 'submit_booking' ] );
		add_action( 'wp_ajax_resort_submit_review', [ $this, 'submit_review' ] );
		add_action( 'wp_ajax_resort_validate_coupon', [ $this, 'validate_coupon' ] );
		add_action( 'wp_ajax_nopriv_resort_validate_coupon', [ $this, 'validate_coupon' ] );
		add_action( 'wp_ajax_resort_submit_lead', [ $this, 'submit_lead' ] );
		add_action( 'wp_ajax_nopriv_resort_submit_lead', [ $this, 'submit_lead' ] );
		add_action( 'wp_ajax_resort_modify_request', [ $this, 'handle_modify_request' ] );
	}

	public function submit_booking() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		$room_id = $_POST['room_id'];
		$room_ids = is_array($room_id) ? array_map('intval', $room_id) : [ intval($room_id) ];
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
		$total_price = 0;
		foreach ( $room_ids as $r_id ) {
			$total_price += \ResortManager\Core\PricingEngine::calculate_total( $r_id, $checkin, $checkout );
		}

		// Add services prices
		$services = isset( $_POST['services'] ) ? (array) $_POST['services'] : [];
		foreach ( $services as $service_id ) {
			$service_price = get_post_meta( $service_id, '_resort_service_price', true );
			$total_price += floatval( $service_price );
		}

		// Save Meta
		update_post_meta( $booking_id, '_resort_room_id', $room_ids[0] ); // Fallback for old code
		update_post_meta( $booking_id, '_resort_room_ids', $room_ids );

		if ( isset($_POST['package_id']) ) {
			update_post_meta( $booking_id, '_resort_package_id', intval($_POST['package_id']) );
		}

		update_post_meta( $booking_id, '_resort_checkin', $checkin );
		update_post_meta( $booking_id, '_resort_checkout', $checkout );
		update_post_meta( $booking_id, '_resort_guests', $guests_count );
		update_post_meta( $booking_id, '_resort_guest_id', $user_id );
		update_post_meta( $booking_id, '_resort_first_name', sanitize_text_field( $guest_data['first_name'] ?? '' ) );
		update_post_meta( $booking_id, '_resort_last_name', sanitize_text_field( $guest_data['last_name'] ?? '' ) );
		update_post_meta( $booking_id, '_resort_guest_email', sanitize_email( $guest_data['email'] ) );
		update_post_meta( $booking_id, '_resort_guest_phone', sanitize_text_field( $guest_data['phone'] ?? '' ) );
		update_post_meta( $booking_id, '_resort_meal_preference', sanitize_text_field( $guest_data['meal_preference'] ?? 'none' ) );
		update_post_meta( $booking_id, '_resort_special_requests', sanitize_textarea_field( $guest_data['special_requests'] ?? '' ) );
		update_post_meta( $booking_id, '_resort_marketing_optin', isset( $guest_data['marketing_optin'] ) ? 'yes' : 'no' );
		update_post_meta( $booking_id, '_resort_digital_waiver', isset( $guest_data['digital_waiver'] ) ? 'accepted' : 'declined' );

		// Server-side Price Re-validation (Security Fix)
		$coupon_code = sanitize_text_field($_POST['coupon'] ?? '');
		if ( ! empty( $coupon_code ) ) {
			global $wpdb;
			$table_coupons = $wpdb->prefix . 'resort_coupons';
			$coupon = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM $table_coupons WHERE code = %s AND (expiry_date >= %s OR expiry_date IS NULL OR expiry_date = '0000-00-00')",
				$coupon_code, date('Y-m-d')
			) );

			if ( $coupon ) {
				if ( $coupon->discount_type === 'fixed' ) {
					$total_price -= floatval( $coupon->discount_amount );
				} else {
					$total_price -= ( $total_price * floatval( $coupon->discount_amount ) ) / 100;
				}
				$total_price = max( 0, $total_price );
			}
		}

		update_post_meta( $booking_id, '_resort_total_price', $total_price );
		update_post_meta( $booking_id, '_resort_services', $services );
		update_post_meta( $booking_id, '_resort_coupon_used', sanitize_text_field($_POST['coupon'] ?? '') );

		// Handle Loyalty Points Redemption
		$points_redeemed = intval($_POST['points_redeemed'] ?? 0);
		if ( $points_redeemed > 0 && is_user_logged_in() ) {
			$available_points = get_user_meta( get_current_user_id(), '_resort_loyalty_points', true ) ?: 0;
			if ( $points_redeemed <= $available_points ) {
				$points_discount = $points_redeemed / 10;
				$total_price = max(0, $total_price - $points_discount);
				update_post_meta( $booking_id, '_resort_points_redeemed', $points_redeemed );

				// Deduct points immediately
				update_user_meta( get_current_user_id(), '_resort_loyalty_points', $available_points - $points_redeemed );
				\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Guest #%d redeemed %d loyalty points.', 'resort-manager' ), get_current_user_id(), $points_redeemed ) );
			}
		}

		update_post_meta( $booking_id, '_resort_total_price', $total_price );
		update_post_meta( $booking_id, '_resort_status', 'pending' );

		// 2. Mailchimp Sync
		if ( isset( $guest_data['marketing_optin'] ) ) {
			$this->sync_to_mailchimp( $email, $guest_data['first_name'], $guest_data['last_name'] );
		}

		// Mark as booked in availability table
		\ResortManager\Core\AvailabilityEngine::mark_as_booked( $room_ids, $checkin, $checkout, $booking_id );

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

	public function submit_lead() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		$name = sanitize_text_field( $_POST['guest_name'] );
		$email = sanitize_email( $_POST['guest_email'] );

		if ( ! is_email( $email ) ) {
			wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'resort-manager' ) ] );
		}

		$lead_id = wp_insert_post( [
			'post_type'   => 'resort_lead',
			'post_title'  => $name . ' (' . $email . ')',
			'post_status' => 'publish',
		] );

		update_post_meta( $lead_id, '_resort_guest_name', $name );
		update_post_meta( $lead_id, '_resort_guest_email', $email );

		// Sync to Mailchimp
		$this->sync_to_mailchimp( $email, $name, '' );

		wp_send_json_success( [ 'message' => __( 'Welcome to the club! Check your inbox for your first gift.', 'resort-manager' ) ] );
	}

	public function handle_modify_request() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) wp_send_json_error();

		$booking_id = intval( $_POST['booking_id'] );
		$details = sanitize_textarea_field( $_POST['details'] );

		// Verify this booking belongs to the user
		$guest_id = get_post_meta( $booking_id, '_resort_guest_id', true );
		if ( intval($guest_id) !== get_current_user_id() ) wp_send_json_error();

		// Record the request in the communication log
		$log = get_post_meta( $booking_id, '_resort_communication_log', true ) ?: [];
		$log[] = [
			'date'    => date( 'Y-m-d H:i' ),
			'message' => 'GUEST MODIFICATION REQUEST: ' . $details
		];
		update_post_meta( $booking_id, '_resort_communication_log', $log );

		\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Guest requested modification for Booking #%d.', 'resort-manager' ), $booking_id ) );

		wp_send_json_success( [ 'message' => __( 'Your request has been sent to our staff. We will contact you shortly.', 'resort-manager' ) ] );
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
