<?php
namespace ResortManager\Core;

class CRM {
	public function __construct() {
		add_action( 'resort_booking_confirmed', [ $this, 'sync_guest_to_hubspot' ], 20, 1 );
		add_action( 'resort_booking_confirmed', [ $this, 'sync_guest_to_zoho' ], 21, 1 );
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

	public function sync_guest_to_zoho( $booking_id ) {
		$client_id = get_option( 'resort_zoho_client_id' );
		$client_secret = get_option( 'resort_zoho_client_secret' );
		$refresh_token = get_option( 'resort_zoho_refresh_token' );

		if ( empty( $client_id ) || empty( $client_secret ) || empty( $refresh_token ) ) return;

		// 1. Get Access Token
		$token_url = "https://accounts.zoho.com/oauth/v2/token?refresh_token=$refresh_token&client_id=$client_id&client_secret=$client_secret&grant_type=refresh_token";
		$token_res = wp_remote_post( $token_url );
		if ( is_wp_error( $token_res ) ) return;
		$token_data = json_decode( wp_remote_retrieve_body( $token_res ) );
		$access_token = $token_data->access_token ?? '';

		if ( empty( $access_token ) ) return;

		// 2. Push Contact
		$fname = get_post_meta( $booking_id, '_resort_first_name', true );
		$lname = get_post_meta( $booking_id, '_resort_last_name', true );
		$email = get_post_meta( $booking_id, '_resort_guest_email', true );

		$data = [
			'data' => [
				[
					'First_Name' => $fname,
					'Last_Name'  => $lname ?: 'Guest',
					'Email'      => $email,
					'Lead_Source'=> 'LuxeResort Plugin'
				]
			]
		];

		$url = 'https://www.zohoapis.com/crm/v2/Contacts';
		wp_remote_post( $url, [
			'headers' => [
				'Authorization' => 'Zoho-oauthtoken ' . $access_token,
				'Content-Type'  => 'application/json',
			],
			'body' => json_encode( $data ),
		] );

		\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Guest synced to Zoho CRM for Booking #%d.', 'resort-manager' ), $booking_id ) );
	}
}
