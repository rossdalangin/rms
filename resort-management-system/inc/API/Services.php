<?php
namespace ResortManager\API;

class Services {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route( 'resort/v1', '/services', [
			'methods'  => 'GET',
			'callback' => [ $this, 'get_services' ],
			'permission_callback' => '__return_true',
		] );
	}

	public function get_services() {
		$services = get_posts( [
			'post_type'   => 'service',
			'numberposts' => -1,
		] );

		$response = [];
		foreach ( $services as $service ) {
			$response[] = [
				'id'    => $service->ID,
				'title' => $service->post_title,
				'price' => get_post_meta( $service->ID, '_resort_service_price', true ),
			];
		}

		return rest_ensure_response( $response );
	}
}
