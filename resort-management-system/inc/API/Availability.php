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

		$response = [];
		foreach ( $rooms as $room ) {
			$total_price = \ResortManager\Core\PricingEngine::calculate_total( $room->ID, $checkin, $checkout );
			$response[] = [
				'id'          => $room->ID,
				'title'       => $room->post_title,
				'description' => $room->post_excerpt,
				'price'       => $total_price,
				'capacity'    => get_post_meta( $room->ID, '_resort_capacity', true ),
				'image'       => get_the_post_thumbnail_url( $room->ID, 'medium' ) ?: get_post_meta( $room->ID, '_resort_sample_image', true ),
			];
		}

		return rest_ensure_response( $response );
	}
}
