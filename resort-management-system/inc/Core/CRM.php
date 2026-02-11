<?php
namespace ResortManager\Core;

class CRM {
	public function __construct() {
		add_action( 'resort_booking_confirmed', [ $this, 'sync_guest_to_hubspot' ], 20, 1 );
	}

	public function sync_guest_to_hubspot( $booking_id ) {
		$api_key = get_option( 'resort_hubspot_api_key' );
		if ( empty( $api_key ) ) return;

		$fname = get_post_meta( $booking_id, '_resort_first_name', true );
		$lname = get_post_meta( $booking_id, '_resort_last_name', true );
		$email = get_post_meta( $booking_id, '_resort_guest_email', true );
		$phone = get_post_meta( $booking_id, '_resort_guest_phone', true );

		$data = [
			'properties' => [
				'email'     => $email,
				'firstname' => $fname,
				'lastname'  => $lname,
				'phone'     => $phone,
				'lifecyclestage' => 'customer'
			]
		];

		$url = 'https://api.hubapi.com/crm/v3/objects/contacts';

		$response = wp_remote_post( $url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			],
			'body' => json_encode( $data ),
		] );

		if ( is_wp_error( $response ) ) {
			\ResortManager\Core\ActivityLogger::log( sprintf( __( 'HubSpot sync failed for Booking #%d: %s', 'resort-manager' ), $booking_id, $response->get_error_message() ) );
		} else {
			\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Guest synced to HubSpot for Booking #%d.', 'resort-manager' ), $booking_id ) );
		}
	}
}
