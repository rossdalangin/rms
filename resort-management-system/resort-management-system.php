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
		new ResortManager\Gateways\Stripe();
		new ResortManager\Gateways\PayPal();
		new ResortManager\Core\Notifications();

		if ( is_admin() ) {
			new ResortManager\Admin\Assets();
			new ResortManager\Admin\Onboarding();
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

				// Simple verification logic
				if ( 'stripe' === $method && isset($_GET['session_id']) ) {
					// In production, call Stripe API to check session status
					$verified = true;
					$transaction_id = sanitize_text_field($_GET['session_id']);
				} elseif ( 'paypal' === $method ) {
					// In production, call PayPal API to check order status
					$verified = true;
				}

				if ( $verified ) {
					update_post_meta( $booking_id, '_resort_status', 'confirmed' );
					update_post_meta( $booking_id, '_resort_payment_status', 'completed' );

				// Add Loyalty Points (1 point per $10)
				$guest_id = get_post_meta( $booking_id, '_resort_guest_id', true );
				if ( $guest_id ) {
					$amount = get_post_meta( $booking_id, '_resort_total_price', true );
					$points = floor( floatval( $amount ) / 10 );
					$current_points = get_user_meta( $guest_id, '_resort_loyalty_points', true ) ?: 0;
					update_user_meta( $guest_id, '_resort_loyalty_points', intval($current_points) + $points );
				}

					// Log to Payments table
					global $wpdb;
					$table_payments = $wpdb->prefix . 'resort_payments';
					$amount = get_post_meta( $booking_id, '_resort_total_price', true );

					$wpdb->insert( $table_payments, [
						'booking_id'     => $booking_id,
						'transaction_id' => $transaction_id,
						'amount'         => $amount,
						'method'         => $method,
						'status'         => 'completed'
					] );

					do_action( 'resort_booking_confirmed', $booking_id );
				}
			}
		}
	}
}

// Register activation hook
register_activation_hook( __FILE__, [ 'ResortManager\Database', 'create_tables' ] );

// Initialize the plugin
ResortManager::get_instance();
