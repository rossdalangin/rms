<?php
namespace ResortManager;

class PostTypes {
	public static function register() {
		self::register_accommodation();
		self::register_booking();
		self::register_review();
		self::register_service();
		self::register_lead();
		self::register_package();
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
		add_filter( 'post_row_actions', [ self::class, 'booking_row_actions' ], 10, 2 );
		add_action( 'admin_init', [ self::class, 'handle_booking_actions' ] );

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

	public static function booking_row_actions( $actions, $post ) {
		if ( 'booking' !== $post->post_type ) return $actions;

		$check_status = get_post_meta( $post->ID, '_resort_check_status', true ) ?: 'pending';

		if ( 'pending' === $check_status ) {
			$actions['checkin'] = '<a href="' . wp_nonce_url( admin_url( 'edit.php?post_type=booking&resort_action=checkin&booking_id=' . $post->ID ), 'resort_booking_action' ) . '" style="color:green;">' . __( 'Mark Checked In', 'resort-manager' ) . '</a>';
		} elseif ( 'checked_in' === $check_status ) {
			$actions['checkout'] = '<a href="' . wp_nonce_url( admin_url( 'edit.php?post_type=booking&resort_action=checkout&booking_id=' . $post->ID ), 'resort_booking_action' ) . '" style="color:orange;">' . __( 'Mark Checked Out', 'resort-manager' ) . '</a>';
		}

		$status = get_post_meta( $post->ID, '_resort_status', true );
		if ( 'cancelled' !== $status ) {
			$actions['cancel_booking'] = '<a href="' . wp_nonce_url( admin_url( 'edit.php?post_type=booking&resort_action=cancel&booking_id=' . $post->ID ), 'resort_booking_action' ) . '" style="color:#d63638;">' . __( 'Cancel Booking', 'resort-manager' ) . '</a>';
		}

		return $actions;
	}

	public static function handle_booking_actions() {
		if ( ! isset( $_GET['resort_action'] ) || ! current_user_can( 'edit_posts' ) ) return;
		check_admin_referer( 'resort_booking_action' );

		$booking_id = intval( $_GET['booking_id'] );
		$action = $_GET['resort_action'];

		if ( 'checkin' === $action ) {
			update_post_meta( $booking_id, '_resort_check_status', 'checked_in' );
			\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Booking #%d marked as Checked In.', 'resort-manager' ), $booking_id ) );
		} elseif ( 'checkout' === $action ) {
			update_post_meta( $booking_id, '_resort_check_status', 'checked_out' );
			\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Booking #%d marked as Checked Out.', 'resort-manager' ), $booking_id ) );
		} elseif ( 'cancel' === $action ) {
			\ResortManager\Core\BookingManager::sync_status( $booking_id, 'cancelled' );
			\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Booking #%d cancelled by admin.', 'resort-manager' ), $booking_id ) );
		}

		wp_redirect( remove_query_arg( [ 'resort_action', 'booking_id', '_wpnonce' ] ) );
		exit;
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
			'supports'           => [ 'title', 'editor', 'comments' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'review', $args );
	}

	private static function register_lead() {
		$labels = [
			'name'               => _x( 'Leads', 'post type general name', 'resort-manager' ),
			'singular_name'      => _x( 'Lead', 'post type singular name', 'resort-manager' ),
			'menu_name'          => _x( 'Leads', 'admin menu', 'resort-manager' ),
			'all_items'          => __( 'All Leads', 'resort-manager' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => 'resort-manager',
			'supports'           => [ 'title' ],
		];

		register_post_type( 'resort_lead', $args );
	}

	private static function register_package() {
		$labels = [
			'name'               => _x( 'Packages', 'post type general name', 'resort-manager' ),
			'singular_name'      => _x( 'Package', 'post type singular name', 'resort-manager' ),
			'menu_name'          => _x( 'Packages', 'admin menu', 'resort-manager' ),
			'add_new'            => _x( 'Add New', 'package', 'resort-manager' ),
			'all_items'          => __( 'All Packages', 'resort-manager' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_in_menu'       => 'resort-manager',
			'supports'           => [ 'title', 'editor', 'thumbnail' ],
			'show_in_rest'       => true,
		];

		register_post_type( 'resort_package', $args );
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
