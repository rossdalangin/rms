<?php
namespace ResortManager\Core;

class ICalSync {
	public function __construct() {
		add_action( 'init', [ $this, 'handle_ical_request' ] );
		add_action( 'resort_daily_sync', [ $this, 'sync_all_external_calendars' ] );
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

	public function sync_all_external_calendars() {
		$rooms = get_posts( [
			'post_type'   => 'accommodation',
			'numberposts' => -1,
		] );

		foreach ( $rooms as $room ) {
			$external_url = get_post_meta( $room->ID, '_resort_ical_url', true );
			if ( ! empty( $external_url ) ) {
				$this->import_external_ical( $room->ID, $external_url );
			}
		}
	}

	private function import_external_ical( $room_id, $url ) {
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return;
		}

		$content = wp_remote_retrieve_body( $response );

		// Very basic regex-based VCALENDAR parser for VEVENT dates
		preg_match_all( '/BEGIN:VEVENT.*?DTSTART(?:;VALUE=DATE)?:(\d{8}).*?DTEND(?:;VALUE=DATE)?:(\d{8}).*?END:VEVENT/s', $content, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'resort_availability';

		// Clear previous sync blocks for this room to avoid duplicates
		$wpdb->delete( $table, [ 'room_id' => $room_id, 'status' => 'sync' ] );

		foreach ( $matches as $match ) {
			$start = date( 'Y-m-d', strtotime( $match[1] ) );
			$end = date( 'Y-m-d', strtotime( $match[2] ) );

			// Mark each day between start and end as 'sync'
			$current = strtotime( $start );
			$last = strtotime( $end );

			while ( $current < $last ) {
				$date = date( 'Y-m-d', $current );
				$wpdb->insert( $table, [
					'room_id' => $room_id,
					'date'    => $date,
					'status'  => 'sync',
				] );
				$current = strtotime( '+1 day', $current );
			}
		}
	}
}
