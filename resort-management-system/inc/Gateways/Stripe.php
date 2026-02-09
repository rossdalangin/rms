<?php
namespace ResortManager\Gateways;

class Stripe {
	public function __construct() {
		add_action( 'wp_ajax_resort_stripe_checkout', [ $this, 'process_payment' ] );
		add_action( 'wp_ajax_nopriv_resort_stripe_checkout', [ $this, 'process_payment' ] );
	}

	public function process_payment() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		if ( get_option( 'resort_payment_stripe_enabled', '1' ) !== '1' ) {
			wp_send_json_error( [ 'message' => __( 'Stripe payment is currently disabled.', 'resort-manager' ) ] );
		}

		$booking_id = intval( $_POST['booking_id'] );
		$secret_key = get_option( 'resort_stripe_secret_key' );

		if ( empty( $secret_key ) ) {
			// Fallback to simulation if no key is set
			update_post_meta( $booking_id, '_resort_payment_status', 'completed' );
			update_post_meta( $booking_id, '_resort_payment_method', 'stripe' );
			update_post_meta( $booking_id, '_resort_status', 'confirmed' );
			do_action( 'resort_booking_confirmed', $booking_id );
			wp_send_json_success( [ 'message' => __( 'Stripe simulation successful.', 'resort-manager' ) ] );
			return;
		}

		$total_price = get_post_meta( $booking_id, '_resort_total_price', true );
		$deposit_percent = get_option( 'resort_deposit_percentage', '100' );
		$payable_amount = ( floatval($total_price) * intval($deposit_percent) ) / 100;

		$currency = get_option( 'resort_currency', 'USD' );

		// Create Stripe Checkout Session via REST API
		$response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', [
			'headers' => [
				'Authorization' => 'Bearer ' . $secret_key,
				'Content-Type'  => 'application/x-www-form-urlencoded',
			],
			'body' => [
				'payment_method_types' => ['card'],
				'line_items' => [[
					'price_data' => [
						'currency' => strtolower($currency),
						'product_data' => [
							'name' => sprintf( __( 'Resort Booking #%d', 'resort-manager' ), $booking_id ),
						],
						'unit_amount' => round( floatval($payable_amount) * 100 ),
					],
					'quantity' => 1,
				]],
				'mode' => 'payment',
				'success_url' => home_url( '/?resort_payment=success&booking_id=' . $booking_id . '&session_id={CHECKOUT_SESSION_ID}' ),
				'cancel_url'  => home_url( '/?resort_payment=cancel&booking_id=' . $booking_id ),
			],
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => $response->get_error_message() ] );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['url'] ) ) {
			wp_send_json_success( [ 'checkout_url' => $body['url'] ] );
		} else {
			wp_send_json_error( [ 'message' => $body['error']['message'] ?? __( 'Stripe Error', 'resort-manager' ) ] );
		}
	}
}
