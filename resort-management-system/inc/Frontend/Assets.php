<?php
namespace ResortManager\Frontend;

class Assets {
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'resort-booking-css', RESORT_MANAGER_URL . 'assets/css/booking.css', [], RESORT_MANAGER_VERSION );
		wp_enqueue_script( 'resort-booking-js', RESORT_MANAGER_URL . 'assets/js/booking.js', [ 'jquery' ], RESORT_MANAGER_VERSION, true );

		wp_localize_script( 'resort-booking-js', 'resortData', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'resort_booking_nonce' ),
			'api_url'  => get_rest_url( null, 'resort/v1' ),
			'rules'    => [
				'min_nights' => get_option( 'resort_min_nights', '1' ),
				'max_nights' => get_option( 'resort_max_nights', '30' ),
				'book_ahead' => get_option( 'resort_book_ahead_days', '0' ),
			],
			'currency' => [
				'code' => get_option( 'resort_currency', 'USD' ),
				'pos'  => get_option( 'resort_currency_symbol_pos', 'before' ),
			],
			'deposit_percent' => get_option( 'resort_deposit_percentage', '100' ),
			'waiver_text' => get_option( 'resort_waiver_text', 'Liability Release: By booking, you agree to waive all liability for tropical accidents...' ),
			'terms_text' => get_option( 'resort_terms_text', 'Standard Resort Terms: 1. No pets. 2. Quiet hours after 10 PM. 3. Full refund if canceled 7 days prior.' ),
			'payments' => [
				'stripe'  => get_option( 'resort_payment_stripe_enabled', '1' ),
				'paypal'  => get_option( 'resort_payment_paypal_enabled', '1' ),
				'offline' => get_option( 'resort_payment_offline_enabled', '1' ),
			]
		] );
	}
}
