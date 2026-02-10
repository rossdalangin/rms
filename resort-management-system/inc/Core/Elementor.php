<?php
namespace ResortManager\Core;

class Elementor {
	public function __construct() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	public function register_widgets( $widgets_manager ) {
		// Define a base widget class for our shortcode wrappers
		require_once RESORT_MANAGER_PATH . 'inc/Core/ElementorWidget.php';

		$widgets_manager->register( new \ResortManager\Core\ElementorWidget( [
			'name' => 'resort_booking',
			'title' => __( 'Resort Booking Engine', 'resort-manager' ),
			'icon' => 'eicon-calendar',
			'shortcode' => '[resort_booking]'
		] ) );

		$widgets_manager->register( new \ResortManager\Core\ElementorWidget( [
			'name' => 'resort_rooms_grid',
			'title' => __( 'Resort Rooms Grid', 'resort-manager' ),
			'icon' => 'eicon-gallery-grid',
			'shortcode' => '[resort_rooms_grid]'
		] ) );

		$widgets_manager->register( new \ResortManager\Core\ElementorWidget( [
			'name' => 'resort_reviews',
			'title' => __( 'Resort Guest Reviews', 'resort-manager' ),
			'icon' => 'eicon-testimonial',
			'shortcode' => '[resort_reviews]'
		] ) );
	}
}
