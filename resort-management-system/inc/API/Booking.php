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

		$guest_data = [];
		if ( isset( $_POST['guest_data'] ) && is_array( $_POST['guest_data'] ) ) {
			foreach ( $_POST['guest_data'] as $key => $value ) {
				$guest_data[$key] = sanitize_text_field( $value );
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
		update_post_meta( $booking_id, '_resort_guest_email', sanitize_email( $guest_data['email'] ) );

		// If a final total was passed from JS (after coupons), use it, but validate it
		if ( isset($_POST['final_total']) ) {
			$total_price = floatval($_POST['final_total']);
		}

		update_post_meta( $booking_id, '_resort_total_price', $total_price );
		update_post_meta( $booking_id, '_resort_services', $services );
		update_post_meta( $booking_id, '_resort_coupon_used', sanitize_text_field($_POST['coupon'] ?? '') );
		update_post_meta( $booking_id, '_resort_status', 'pending' );

		// Mark as booked in availability table
		\ResortManager\Core\AvailabilityEngine::mark_as_booked( $room_id, $checkin, $checkout, $booking_id );

		// If it's an offline booking, log it to the payments table immediately as pending
		if ( isset($_POST['payment_method']) && 'offline' === $_POST['payment_method'] ) {
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
