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
	}

	public function get_availability( $request ) {
		$checkin = $request->get_param( 'checkin' );
		$checkout = $request->get_param( 'checkout' );
		$guests = $request->get_param( 'guests' );

		if ( empty( $checkin ) || empty( $checkout ) ) {
			return rest_ensure_response( [] );
		}

		$rooms = \ResortManager\Core\AvailabilityEngine::get_available_rooms( $checkin, $checkout );
		$available_room_ids = wp_list_pluck( $rooms, 'ID' );

		$response = [];
		// Standard Rooms
		foreach ( $rooms as $room ) {
			$total_price = \ResortManager\Core\PricingEngine::calculate_total( $room->ID, $checkin, $checkout );
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

		// Packages
		$packages = get_posts( [ 'post_type' => 'resort_package', 'numberposts' => -1 ] );
		foreach ( $packages as $package ) {
			$base_room_id = get_post_meta( $package->ID, '_resort_package_room_id', true );
			if ( in_array( $base_room_id, $available_room_ids ) ) {
				$package_price_per_night = get_post_meta( $package->ID, '_resort_package_price', true );
				$days = ( new \DateTime($checkin) )->diff( new \DateTime($checkout) )->days;
				$total_package_price = floatval($package_price_per_night) * $days;

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

		return rest_ensure_response( $response );
	}
}
