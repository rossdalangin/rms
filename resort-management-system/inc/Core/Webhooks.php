<?php
namespace ResortManager\Core;

class Webhooks {
	public function __construct() {
		add_action( 'resort_booking_confirmed', [ $this, 'trigger_webhook' ], 30, 1 );
	}

	public function trigger_webhook( $booking_id ) {
		$webhook_url = get_option( 'resort_webhook_url' );
		if ( empty( $webhook_url ) ) return;

		$booking = get_post( $booking_id );
		$data = [
			'event'      => 'booking_confirmed',
			'booking_id' => $booking_id,
			'guest'      => [
				'first_name' => get_post_meta( $booking_id, '_resort_first_name', true ),
				'last_name'  => get_post_meta( $booking_id, '_resort_last_name', true ),
				'email'      => get_post_meta( $booking_id, '_resort_guest_email', true ),
			],
			'stay'       => [
				'checkin'  => get_post_meta( $booking_id, '_resort_checkin', true ),
				'checkout' => get_post_meta( $booking_id, '_resort_checkout', true ),
				'total'    => get_post_meta( $booking_id, '_resort_total_price', true ),
			]
		];

		wp_remote_post( $webhook_url, [
			'headers' => [ 'Content-Type' => 'application/json' ],
			'body'    => json_encode( $data ),
			'blocking' => false, // Don't slow down the user
		] );

		\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Outgoing webhook triggered for Booking #%d.', 'resort-manager' ), $booking_id ) );
	}
}
