<?php
namespace ResortManager\Gateways;

class PayPal {
	public function __construct() {
		add_action( 'wp_ajax_resort_paypal_checkout', [ $this, 'process_payment' ] );
		add_action( 'wp_ajax_nopriv_resort_paypal_checkout', [ $this, 'process_payment' ] );
	}

	public function process_payment() {
		check_ajax_referer( 'resort_booking_nonce', 'nonce' );

		if ( get_option( 'resort_payment_paypal_enabled', '1' ) !== '1' ) {
			wp_send_json_error( [ 'message' => __( 'PayPal payment is currently disabled.', 'resort-manager' ) ] );
		}

		$booking_id = intval( $_POST['booking_id'] );
		$client_id = get_option( 'resort_paypal_client_id' );
		$secret = get_option( 'resort_paypal_secret' );

		if ( empty( $client_id ) || empty( $secret ) ) {
			// Fallback simulation
			update_post_meta( $booking_id, '_resort_payment_status', 'completed' );
			update_post_meta( $booking_id, '_resort_payment_method', 'paypal' );
			update_post_meta( $booking_id, '_resort_status', 'confirmed' );
			do_action( 'resort_booking_confirmed', $booking_id );
			wp_send_json_success( [ 'message' => __( 'PayPal simulation successful.', 'resort-manager' ) ] );
			return;
		}

		$total_price = get_post_meta( $booking_id, '_resort_total_price', true );
		$deposit_percent = get_option( 'resort_deposit_percentage', '100' );
		$payable_amount = ( floatval($total_price) * intval($deposit_percent) ) / 100;

		$currency = get_option( 'resort_currency', 'USD' );
		$mode = get_option( 'resort_paypal_test_mode', '1' ) === '1' ? 'sandbox' : 'live';
		$api_url = $mode === 'sandbox' ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';

		// 1. Get Access Token
		$auth_response = wp_remote_post( "$api_url/v1/oauth2/token", [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( "$client_id:$secret" ),
				'Accept'        => 'application/json',
			],
			'body' => 'grant_type=client_credentials',
		] );

		if ( is_wp_error( $auth_response ) ) {
			wp_send_json_error( [ 'message' => $auth_response->get_error_message() ] );
		}

		$auth_body = json_decode( wp_remote_retrieve_body( $auth_response ), true );
		$access_token = $auth_body['access_token'] ?? '';

		if ( ! $access_token ) {
			wp_send_json_error( [ 'message' => __( 'PayPal Auth Failed', 'resort-manager' ) ] );
		}

		// 2. Create Order
		$order_response = wp_remote_post( "$api_url/v2/checkout/orders", [
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			],
			'body' => json_encode( [
				'intent' => 'CAPTURE',
				'purchase_units' => [[
					'amount' => [
						'currency_code' => strtoupper($currency),
						'value'         => number_format( floatval($payable_amount), 2, '.', '' ),
					],
					'description' => sprintf( __( 'Resort Booking #%d', 'resort-manager' ), $booking_id ),
				]],
				'application_context' => [
					'return_url' => home_url( '/?resort_paypal_status=success&booking_id=' . $booking_id ),
					'cancel_url'  => home_url( '/?resort_paypal_status=cancel&booking_id=' . $booking_id ),
				],
			] ),
		] );

		if ( is_wp_error( $order_response ) ) {
			wp_send_json_error( [ 'message' => $order_response->get_error_message() ] );
		}

		$order_body = json_decode( wp_remote_retrieve_body( $order_response ), true );
		$checkout_url = '';
		if ( isset( $order_body['links'] ) ) {
			foreach ( $order_body['links'] as $link ) {
				if ( $link['rel'] === 'approve' ) {
					$checkout_url = $link['href'];
					break;
				}
			}
		}

		if ( $checkout_url ) {
			wp_send_json_success( [ 'checkout_url' => $checkout_url ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'PayPal Order Creation Failed', 'resort-manager' ) ] );
		}
	}
}
