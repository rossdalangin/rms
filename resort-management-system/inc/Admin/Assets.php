<?php
namespace ResortManager\Admin;

class Assets {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	public function enqueue_admin_assets( $hook ) {
		// Only load on our plugin pages
		if ( strpos( $hook, 'resort-' ) === false && strpos( $hook, 'accommodation' ) === false && strpos( $hook, 'booking' ) === false && strpos( $hook, 'review' ) === false ) {
			return;
		}

		wp_enqueue_style( 'resort-google-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap', [], RESORT_MANAGER_VERSION );
		wp_enqueue_style( 'resort-admin-style', RESORT_MANAGER_URL . 'assets/css/admin-style.css', [], RESORT_MANAGER_VERSION );

		if ( strpos( $hook, 'resort-reports' ) !== false ) {
			wp_enqueue_script( 'resort-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.9.1', true );
		}
	}
}
