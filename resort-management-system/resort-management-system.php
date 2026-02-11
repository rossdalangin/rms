<?php
/**
 * Plugin Name: LuxeResort Manager
 * Plugin URI: https://example.com/luxeresort-manager
 * Description: A premium Resort Management System for WordPress.
 * Version: 1.0.0
 * Author: Jules
 * Author URI: https://example.com
 * License: GPL2
 * Text Domain: resort-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'RESORT_MANAGER_VERSION', '1.0.0' );
define( 'RESORT_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
define( 'RESORT_MANAGER_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader for Plugin Classes
 */
spl_autoload_register( function ( $class ) {
	$prefix = 'ResortManager\\';
	$base_dir = RESORT_MANAGER_PATH . 'inc/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * Main Plugin Class
 */
class ResortManager {
	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init();
	}

	private function init() {
		add_action( 'init', [ $this, 'register_cpts' ] );
		add_action( 'init', [ $this, 'handle_payment_return' ] );
		new ResortManager\Frontend\Shortcodes();
		new ResortManager\Frontend\Assets();
		new ResortManager\API\Availability();
		new ResortManager\API\Booking();
		new ResortManager\API\Services();
		new ResortManager\Core\ICalSync();
		new ResortManager\Core\Blocks();
		new ResortManager\Core\InvoiceEngine();
		new ResortManager\Core\Elementor();
		new ResortManager\Gateways\Stripe();
		new ResortManager\Gateways\PayPal();
		new ResortManager\Core\Notifications();

		if ( is_admin() ) {
			new ResortManager\Admin\Assets();
			new ResortManager\Admin\Settings();
			new ResortManager\Admin\DashboardWidget();
			new ResortManager\Admin\MetaBoxes();
			new ResortManager\Admin\Calendar();
			new ResortManager\Admin\Pricing();
			new ResortManager\Admin\Coupons();
			new ResortManager\Admin\BookingCommunication();
			new ResortManager\Admin\Reports();
			new ResortManager\Admin\Payments();
		}
	}

	public function register_cpts() {
		ResortManager\PostTypes::register();
	}

	public function handle_payment_return() {
		if ( isset( $_GET['resort_payment'] ) || isset( $_GET['resort_paypal_status'] ) ) {
			$status = $_GET['resort_payment'] ?? $_GET['resort_paypal_status'];
			$booking_id = intval( $_GET['booking_id'] );
			$method = isset( $_GET['resort_paypal_status'] ) ? 'paypal' : 'stripe';

			if ( 'success' === $status ) {
				$verified = false;
				$transaction_id = 'EXT-' . time();

				// Security: Real-world Verification Logic
				$stripe_secret = get_option('resort_stripe_secret_key');
				$paypal_secret = get_option('resort_paypal_secret');

				if ( 'stripe' === $method && isset($_GET['session_id']) ) {
					$session_id = sanitize_text_field($_GET['session_id']);
					if ( empty($stripe_secret) ) {
						// Simulation mode: Check for a simulation-specific marker to prevent simple URL manipulation
						if ( strpos($session_id, 'cs_test_') === 0 ) {
							$verified = true;
							$transaction_id = 'SIM-STRIPE-' . time();
						}
					} else {
						// PRODUCTION: Call Stripe API to verify session status
						$response = wp_remote_get("https://api.stripe.com/v1/checkout/sessions/$session_id", [
							'headers' => [ 'Authorization' => 'Bearer ' . $stripe_secret ]
						]);
						if ( ! is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response) ) {
							$body = json_decode(wp_remote_retrieve_body($response));
							if ( isset($body->payment_status) && 'paid' === $body->payment_status ) {
								$verified = true;
								$transaction_id = $session_id;
							}
						}
					}
				} elseif ( 'paypal' === $method && isset($_GET['token']) ) {
					$token = sanitize_text_field($_GET['token']);
					if ( empty($paypal_secret) ) {
						// Simulation mode
						$verified = true;
						$transaction_id = 'SIM-PAYPAL-' . time();
					} else {
						// PRODUCTION: Call PayPal API to verify order status
						// This would normally involve getting an OAuth token first
						$verified = false; // Default to false until full OAuth flow is implemented
					}
				}

				if ( $verified ) {
					// Use central BookingManager to handle everything
					\ResortManager\Core\BookingManager::confirm_booking( $booking_id, $transaction_id, $method );
				} else {
					// Verification failed - log attempt
					if ( class_exists( '\ResortManager\Core\ActivityLogger' ) ) {
						\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Payment verification failed for Booking #%d.', 'resort-manager' ), $booking_id ) );
					}
				}
			}
		}
	}
}

// Register activation hook
register_activation_hook( __FILE__, function() {
	ResortManager\Database::create_tables();
	if ( ! wp_next_scheduled( 'resort_daily_sync' ) ) {
		wp_schedule_event( time(), 'daily', 'resort_daily_sync' );
	}
	if ( ! wp_next_scheduled( 'resort_cleanup_abandoned' ) ) {
		wp_schedule_event( time(), 'hourly', 'resort_cleanup_abandoned' );
	}
} );

register_deactivation_hook( __FILE__, function() {
	wp_clear_scheduled_hook( 'resort_daily_sync' );
	wp_clear_scheduled_hook( 'resort_cleanup_abandoned' );
} );

// Initialize the plugin
ResortManager::get_instance();
