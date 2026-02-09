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
		] );
	}
}
