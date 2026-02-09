<?php
namespace ResortManager\Core;

class ICalSync {
	public function __construct() {
		add_action( 'init', [ $this, 'handle_ical_request' ] );
	}

	public function handle_ical_request() {
		if ( ! isset( $_GET['resort_ical'] ) ) {
			return;
		}

		$room_id = intval( $_GET['resort_ical'] );
		$this->generate_ical( $room_id );
		exit;
	}

	private function generate_ical( $room_id ) {
		$bookings = get_posts( [
			'post_type'  => 'booking',
			'meta_query' => [
				[
					'key'   => '_resort_room_id',
					'value' => $room_id,
				],
			],
			'numberposts' => -1,
		] );

		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="room-' . $room_id . '.ics"' );

		echo "BEGIN:VCALENDAR\n";
		echo "VERSION:2.0\n";
		echo "PRODID:-//LuxeResort//NONSGML v1.0//EN\n";

		foreach ( $bookings as $booking ) {
			$checkin = get_post_meta( $booking->ID, '_resort_checkin', true );
			$checkout = get_post_meta( $booking->ID, '_resort_checkout', true );

			echo "BEGIN:VEVENT\n";
			echo "UID:" . $booking->ID . "@" . $_SERVER['HTTP_HOST'] . "\n";
			echo "DTSTAMP:" . date( 'Ymd\THis\Z' ) . "\n";
			echo "DTSTART;VALUE=DATE:" . date( 'Ymd', strtotime( $checkin ) ) . "\n";
			echo "DTEND;VALUE=DATE:" . date( 'Ymd', strtotime( $checkout ) ) . "\n";
			echo "SUMMARY:Booking #" . $booking->ID . "\n";
			echo "END:VEVENT\n";
		}

		echo "END:VCALENDAR\n";
	}
}
