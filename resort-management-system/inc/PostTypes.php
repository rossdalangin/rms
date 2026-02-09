<?php
namespace ResortManager;

class PostTypes {
	public static function register() {
		self::register_accommodation();
		self::register_booking();
		self::register_review();
		self::register_service();
	}

	private static function register_accommodation() {
		$labels = [
			'name'               => _x( 'Accommodations', 'post type general name', 'resort-manager' ),
			'singular_name'      => _x( 'Accommodation', 'post type singular name', 'resort-manager' ),
			'menu_name'          => _x( 'Accommodations', 'admin menu', 'resort-manager' ),
			'name_admin_bar'     => _x( 'Accommodation', 'add new on admin bar', 'resort-manager' ),
			'add_new'            => _x( 'Add New', 'accommodation', 'resort-manager' ),
			'add_new_item'       => __( 'Add New Accommodation', 'resort-manager' ),
			'new_item'           => __( 'New Accommodation', 'resort-manager' ),
			'edit_item'          => __( 'Edit Accommodation', 'resort-manager' ),
			'view_item'          => __( 'View Accommodation', 'resort-manager' ),
			'all_items'          => __( 'All Accommodations', 'resort-manager' ),
			'search_items'       => __( 'Search Accommodations', 'resort-manager' ),
			'not_found'          => __( 'No accommodations found.', 'resort-manager' ),
			'not_found_in_trash' => __( 'No accommodations found in Trash.', 'resort-manager' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'accommodation' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 5,
			'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'accommodation', $args );
	}

	private static function register_booking() {
		$labels = [
			'name'               => _x( 'Bookings', 'post type general name', 'resort-manager' ),
			'singular_name'      => _x( 'Booking', 'post type singular name', 'resort-manager' ),
			'menu_name'          => _x( 'Bookings', 'admin menu', 'resort-manager' ),
			'name_admin_bar'     => _x( 'Booking', 'add new on admin bar', 'resort-manager' ),
			'add_new'            => _x( 'Add New', 'booking', 'resort-manager' ),
			'add_new_item'       => __( 'Add New Booking', 'resort-manager' ),
			'new_item'           => __( 'New Booking', 'resort-manager' ),
			'edit_item'          => __( 'Edit Booking', 'resort-manager' ),
			'view_item'          => __( 'View Booking', 'resort-manager' ),
			'all_items'          => __( 'All Bookings', 'resort-manager' ),
			'search_items'       => __( 'Search Bookings', 'resort-manager' ),
			'not_found'          => __( 'No bookings found.', 'resort-manager' ),
			'not_found_in_trash' => __( 'No bookings found in Trash.', 'resort-manager' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'booking' ],
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 6,
			'supports'           => [ 'title' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'booking', $args );
	}

	private static function register_review() {
		$labels = [
			'name'               => _x( 'Reviews', 'post type general name', 'resort-manager' ),
			'singular_name'      => _x( 'Review', 'post type singular name', 'resort-manager' ),
			'menu_name'          => _x( 'Reviews', 'admin menu', 'resort-manager' ),
			'add_new'            => _x( 'Add New', 'review', 'resort-manager' ),
			'add_new_item'       => __( 'Add New Review', 'resort-manager' ),
			'edit_item'          => __( 'Edit Review', 'resort-manager' ),
			'all_items'          => __( 'All Reviews', 'resort-manager' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'supports'           => [ 'title', 'editor' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'review', $args );
	}

	private static function register_service() {
		$labels = [
			'name'               => _x( 'Services/Extras', 'post type general name', 'resort-manager' ),
			'singular_name'      => _x( 'Service', 'post type singular name', 'resort-manager' ),
			'menu_name'          => _x( 'Services/Extras', 'admin menu', 'resort-manager' ),
			'add_new'            => _x( 'Add New', 'service', 'resort-manager' ),
			'add_new_item'       => __( 'Add New Service', 'resort-manager' ),
			'edit_item'          => __( 'Edit Service', 'resort-manager' ),
			'all_items'          => __( 'All Services', 'resort-manager' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => 'edit.php?post_type=accommodation',
			'supports'           => [ 'title', 'excerpt' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'service', $args );
	}
}
