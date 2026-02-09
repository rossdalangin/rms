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
		new ResortManager\Frontend\Shortcodes();
		new ResortManager\Frontend\Assets();
		new ResortManager\API\Availability();
		new ResortManager\API\Booking();
		new ResortManager\Core\ICalSync();
		new ResortManager\Gateways\Stripe();
		new ResortManager\Gateways\PayPal();
		new ResortManager\Core\Notifications();

		if ( is_admin() ) {
			new ResortManager\Admin\Settings();
			new ResortManager\Admin\MetaBoxes();
			new ResortManager\Admin\Calendar();
			new ResortManager\Admin\Pricing();
			new ResortManager\Admin\Coupons();
			new ResortManager\Admin\BookingCommunication();
		}
	}

	public function register_cpts() {
		ResortManager\PostTypes::register();
	}
}

// Register activation hook
register_activation_hook( __FILE__, [ 'ResortManager\Database', 'create_tables' ] );

// Initialize the plugin
ResortManager::get_instance();
