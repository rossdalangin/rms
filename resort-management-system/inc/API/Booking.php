<?php
namespace ResortManager\API;

class Booking {
	public function __construct() {
		add_action( 'wp_ajax_resort_submit_booking', [ $this, 'submit_booking' ] );
		add_action( 'wp_ajax_nopriv_resort_submit_booking', [ $this, 'submit_booking' ] );
	}

	public function submit_booking() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		$room_id = intval( $_POST['room_id'] );
		$checkin = sanitize_text_field( $_POST['checkin'] );
		$checkout = sanitize_text_field( $_POST['checkout'] );
		$guest_data = $_POST['guest_data'];

		// Create Booking Post
		$booking_id = wp_insert_post( [
			'post_type'   => 'booking',
			'post_title'  => sprintf( 'Booking for %s %s', $guest_data['first_name'], $guest_data['last_name'] ),
			'post_status' => 'publish',
		] );

		if ( is_wp_error( $booking_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Could not create booking.', 'resort-manager' ) ] );
		}

		// Calculate Total Price
		$total_price = \ResortManager\Core\PricingEngine::calculate_total( $room_id, $checkin, $checkout );

		// Save Meta
		update_post_meta( $booking_id, '_resort_room_id', $room_id );
		update_post_meta( $booking_id, '_resort_checkin', $checkin );
		update_post_meta( $booking_id, '_resort_checkout', $checkout );
		update_post_meta( $booking_id, '_resort_guest_email', sanitize_email( $guest_data['email'] ) );
		update_post_meta( $booking_id, '_resort_total_price', $total_price );
		update_post_meta( $booking_id, '_resort_status', 'pending' );

		// Mark as booked in availability table
		\ResortManager\Core\AvailabilityEngine::mark_as_booked( $room_id, $checkin, $checkout, $booking_id );

		wp_send_json_success( [
			'booking_id' => $booking_id,
			'message'    => __( 'Booking created successfully!', 'resort-manager' ),
		] );
	}
}
