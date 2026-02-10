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
			'show_in_menu'       => 'resort-manager',
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
		add_filter( 'manage_booking_posts_columns', [ self::class, 'booking_columns' ] );
		add_action( 'manage_booking_posts_custom_column', [ self::class, 'booking_column_data' ], 10, 2 );

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
			'show_in_menu'       => 'resort-manager',
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

	public static function booking_columns( $columns ) {
		$new_columns = [
			'cb'         => $columns['cb'],
			'title'      => $columns['title'],
			'guest'      => __( 'Guest Details', 'resort-manager' ),
			'dates'      => __( 'Stay Dates', 'resort-manager' ),
			'total'      => __( 'Total Price', 'resort-manager' ),
			'status'     => __( 'Status', 'resort-manager' ),
			'check_status' => __( 'Check-in Status', 'resort-manager' ),
			'date'       => $columns['date'],
		];
		return $new_columns;
	}

	public static function booking_column_data( $column, $post_id ) {
		switch ( $column ) {
			case 'guest':
				$fname = get_post_meta( $post_id, '_resort_first_name', true );
				$lname = get_post_meta( $post_id, '_resort_last_name', true );
				$email = get_post_meta( $post_id, '_resort_guest_email', true );

				if ( ! empty( $fname ) || ! empty( $lname ) ) {
					echo '<strong>' . esc_html( "$fname $lname" ) . '</strong><br>';
				} else {
					// Fallback to title if names aren't in meta yet
					$title = get_the_title( $post_id );
					echo '<strong>' . esc_html( str_replace( 'Booking for ', '', $title ) ) . '</strong><br>';
				}
				echo '<small>' . esc_html( $email ) . '</small>';
				break;
			case 'dates':
				$checkin = get_post_meta( $post_id, '_resort_checkin', true );
				$checkout = get_post_meta( $post_id, '_resort_checkout', true );
				echo esc_html( "$checkin to $checkout" );
				break;
			case 'total':
				$total = get_post_meta( $post_id, '_resort_total_price', true );
				echo \ResortManager\Core\PricingEngine::format_price( floatval($total) );
				break;
			case 'status':
				$status = get_post_meta( $post_id, '_resort_status', true );
				echo '<span class="status-badge status-' . esc_attr($status) . '">' . esc_html( ucfirst($status) ) . '</span>';
				break;
			case 'check_status':
				$check_status = get_post_meta( $post_id, '_resort_check_status', true ) ?: 'pending';
				echo '<span class="status-badge status-' . esc_attr($check_status) . '">' . esc_html( ucfirst($check_status) ) . '</span>';
				break;
		}
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
			'show_in_menu'       => 'resort-manager',
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
			'show_in_menu'       => 'resort-manager',
			'supports'           => [ 'title', 'excerpt' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'service', $args );
	}
}
