<?php
namespace ResortManager\API;

class Availability {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route( 'resort/v1', '/availability', [
			'methods'  => 'GET',
			'callback' => [ $this, 'get_availability' ],
			'permission_callback' => '__return_true',
		] );

		register_rest_route( 'resort/v1', '/guest/history', [
			'methods'  => 'GET',
			'callback' => [ $this, 'get_guest_history' ],
			'permission_callback' => function() { return is_user_logged_in(); },
		] );

		register_rest_route( 'resort/v1', '/guest/requests', [
			'methods'  => 'POST',
			'callback' => [ $this, 'create_guest_request' ],
			'permission_callback' => function() { return is_user_logged_in(); },
		] );
	}

	public function get_availability( $request ) {
		$checkin = $request->get_param( 'checkin' );
		$checkout = $request->get_param( 'checkout' );
		$guests = $request->get_param( 'guests' );
		$price_range = $request->get_param( 'price_range' );
		$filter_type = $request->get_param( 'type' );

		// Check Cut-off Time for Same-Day Bookings
		if ( $checkin === date('Y-m-d') ) {
			$cutoff = get_option( 'resort_cutoff_time', '14:00' );
			if ( time() > strtotime( date('Y-m-d') . ' ' . $cutoff ) ) {
				return rest_ensure_response( [] ); // Disable same-day if past cutoff
			}
		}

		if ( empty( $checkin ) || empty( $checkout ) ) {
			return rest_ensure_response( [] );
		}

		$rooms = \ResortManager\Core\AvailabilityEngine::get_available_rooms( $checkin, $checkout );
		$available_room_ids = wp_list_pluck( $rooms, 'ID' );

		$response = [];

		// Standard Rooms
		if ( empty($filter_type) || 'room' === $filter_type ) {
			foreach ( $rooms as $room ) {
				$total_price = \ResortManager\Core\PricingEngine::calculate_total( $room->ID, $checkin, $checkout );

				// Price Range Filter
				if ( ! empty($price_range) ) {
					if ( '0-5000' === $price_range && $total_price > 5000 ) continue;
					if ( '5000-10000' === $price_range && ($total_price < 5000 || $total_price > 10000) ) continue;
					if ( '10000+' === $price_range && $total_price < 10000 ) continue;
				}

				$response[] = [
					'id'          => $room->ID,
					'type'        => 'room',
					'title'       => $room->post_title,
					'description' => $room->post_excerpt,
					'price'       => $total_price,
					'capacity'    => get_post_meta( $room->ID, '_resort_capacity', true ),
					'image'       => get_the_post_thumbnail_url( $room->ID, 'medium' ) ?: get_post_meta( $room->ID, '_resort_sample_image', true ),
				];
			}
		}

		// Packages
		if ( empty($filter_type) || 'package' === $filter_type ) {
			$packages = get_posts( [ 'post_type' => 'resort_package', 'numberposts' => -1 ] );
			foreach ( $packages as $package ) {
				$base_room_id = get_post_meta( $package->ID, '_resort_package_room_id', true );
				if ( in_array( $base_room_id, $available_room_ids ) ) {
					$package_price_per_night = get_post_meta( $package->ID, '_resort_package_price', true );
					$days = ( new \DateTime($checkin) )->diff( new \DateTime($checkout) )->days;
					$total_package_price = floatval($package_price_per_night) * $days;

					// Price Range Filter
					if ( ! empty($price_range) ) {
						if ( '0-5000' === $price_range && $total_package_price > 5000 ) continue;
						if ( '5000-10000' === $price_range && ($total_package_price < 5000 || $total_package_price > 10000) ) continue;
						if ( '10000+' === $price_range && $total_package_price < 10000 ) continue;
					}

					$response[] = [
						'id'          => $package->ID,
						'type'        => 'package',
						'title'       => '[PACKAGE] ' . $package->post_title,
						'description' => $package->post_content ?: $package->post_excerpt,
						'price'       => $total_package_price,
						'capacity'    => get_post_meta( $base_room_id, '_resort_capacity', true ),
						'image'       => get_the_post_thumbnail_url( $package->ID, 'medium' ) ?: get_post_meta( $base_room_id, '_resort_sample_image', true ),
						'room_id'     => $base_room_id,
						'services'    => get_post_meta( $package->ID, '_resort_package_services', true ) ?: [],
					];
				}
			}
		}

		return rest_ensure_response( $response );
	}

	public function get_guest_history( $request ) {
		$user_id = get_current_user_id();
		$bookings = get_posts( [
			'post_type'  => 'booking',
			'meta_key'   => '_resort_guest_id',
			'meta_value' => $user_id,
			'numberposts' => -1
		] );

		$response = [];
		foreach ( $bookings as $booking ) {
			$response[] = [
				'id'      => $booking->ID,
				'checkin' => get_post_meta( $booking->ID, '_resort_checkin', true ),
				'checkout'=> get_post_meta( $booking->ID, '_resort_checkout', true ),
				'status'  => get_post_meta( $booking->ID, '_resort_status', true ),
				'total'   => get_post_meta( $booking->ID, '_resort_total_price', true ),
			];
		}

		return rest_ensure_response( $response );
	}

	public function create_guest_request( $request ) {
		$booking_id = intval( $request->get_param( 'booking_id' ) );
		$type = sanitize_text_field( $request->get_param( 'type' ) );
		$details = sanitize_textarea_field( $request->get_param( 'details' ) );

		// Permission Check
		$guest_id = get_post_meta( $booking_id, '_resort_guest_id', true );
		if ( intval($guest_id) !== get_current_user_id() ) {
			return new \WP_Error( 'rest_forbidden', __( 'Unauthorized access.', 'resort-manager' ), [ 'status' => 403 ] );
		}

		$log = get_post_meta( $booking_id, '_resort_communication_log', true ) ?: [];
		$log[] = [
			'date'    => date( 'Y-m-d H:i' ),
			'message' => sprintf( 'MOBILE REQUEST (%s): %s', strtoupper($type), $details )
		];
		update_post_meta( $booking_id, '_resort_communication_log', $log );

		return rest_ensure_response( [ 'success' => true, 'message' => __( 'Request sent via API.', 'resort-manager' ) ] );
	}
}
