<?php
namespace ResortManager\Gateways;

class PayPal {
	public function __construct() {
		add_action( 'wp_ajax_resort_paypal_checkout', [ $this, 'process_payment' ] );
		add_action( 'wp_ajax_nopriv_resort_paypal_checkout', [ $this, 'process_payment' ] );
	}

	public function process_payment() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		$booking_id = intval( $_POST['booking_id'] );

		// Simulate PayPal redirect and callback logic
		update_post_meta( $booking_id, '_resort_payment_status', 'completed' );
		update_post_meta( $booking_id, '_resort_payment_method', 'paypal' );
		update_post_meta( $booking_id, '_resort_status', 'confirmed' );

		do_action( 'resort_booking_confirmed', $booking_id );

		wp_send_json_success( [
			'message' => __( 'PayPal payment processed successfully (simulated).', 'resort-manager' ),
		] );
	}
}
