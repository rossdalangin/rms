<?php
namespace ResortManager\Gateways;

class Stripe {
	public function __construct() {
		add_action( 'wp_ajax_resort_stripe_checkout', [ $this, 'process_payment' ] );
		add_action( 'wp_ajax_nopriv_resort_stripe_checkout', [ $this, 'process_payment' ] );
	}

	public function process_payment() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		$booking_id = intval( $_POST['booking_id'] );
		$api_key = get_option( 'stripe_api_key' );

		if ( empty( $api_key ) ) {
			// In a real scenario, we would use the Stripe SDK here.
			// For this demo, we'll simulate a successful payment if no key is provided (for testing).
		}

		// Simulate payment processing
		update_post_meta( $booking_id, '_resort_payment_status', 'completed' );
		update_post_meta( $booking_id, '_resort_payment_method', 'stripe' );
		update_post_meta( $booking_id, '_resort_status', 'confirmed' );

		do_action( 'resort_booking_confirmed', $booking_id );

		wp_send_json_success( [
			'message' => __( 'Stripe payment processed successfully (simulated).', 'resort-manager' ),
		] );
	}
}
