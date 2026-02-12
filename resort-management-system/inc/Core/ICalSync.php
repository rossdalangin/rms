<?php
namespace ResortManager\Core;

class ICalSync {
	public function __construct() {
		add_action( 'init', [ $this, 'handle_ical_request' ] );
		add_action( 'resort_daily_sync', [ $this, 'sync_all_external_calendars' ] );
	}

	public function handle_ical_request() {
		if ( isset( $_GET['resort_ical'] ) ) {
			if ( $_GET['resort_ical'] === 'all' ) {
				$this->generate_master_ical();
			} else {
				$room_id = intval( $_GET['resort_ical'] );
				$this->generate_ical( $room_id );
			}
			exit;
		}

		if ( isset( $_GET['resort_ical_booking'] ) ) {
			$booking_id = intval( $_GET['resort_ical_booking'] );
			$this->generate_booking_ical( $booking_id );
			exit;
		}
	}

	private function generate_booking_ical( $booking_id ) {
		$booking = get_post( $booking_id );
		if ( ! $booking || 'booking' !== $booking->post_type ) return;

		$checkin = get_post_meta( $booking_id, '_resort_checkin', true );
		$checkout = get_post_meta( $booking_id, '_resort_checkout', true );
		$room_id = get_post_meta( $booking_id, '_resort_room_id', true );
		$room_title = get_the_title( $room_id );

		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="booking-' . $booking_id . '.ics"' );

		echo "BEGIN:VCALENDAR\n";
		echo "VERSION:2.0\n";
		echo "PRODID:-//LuxeResort//NONSGML v1.0//EN\n";
		echo "BEGIN:VEVENT\n";
		echo "UID:" . $booking_id . "@" . $_SERVER['HTTP_HOST'] . "\n";
		echo "DTSTAMP:" . date( 'Ymd\THis\Z' ) . "\n";
		echo "DTSTART;VALUE=DATE:" . date( 'Ymd', strtotime( $checkin ) ) . "\n";
		echo "DTEND;VALUE=DATE:" . date( 'Ymd', strtotime( $checkout ) ) . "\n";
		echo "SUMMARY:" . sprintf( __( 'Stay at LuxeResort - %s', 'resort-manager' ), $room_title ) . "\n";
		echo "DESCRIPTION:" . sprintf( __( 'Your paradise escape awaits. Booking ID: #%d', 'resort-manager' ), $booking_id ) . "\n";
		echo "END:VEVENT\n";
		echo "END:VCALENDAR\n";
	}

	private function generate_master_ical() {
		$bookings = get_posts( [
			'post_type'   => 'booking',
			'numberposts' => -1,
		] );

		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="master-calendar.ics"' );

		echo "BEGIN:VCALENDAR\n";
		echo "VERSION:2.0\n";
		echo "PRODID:-//LuxeResort//NONSGML v1.0//EN\n";

		foreach ( $bookings as $booking ) {
			$checkin = get_post_meta( $booking->ID, '_resort_checkin', true );
			$checkout = get_post_meta( $booking->ID, '_resort_checkout', true );
			$room_id = get_post_meta( $booking->ID, '_resort_room_id', true );
			$room_title = get_the_title( $room_id );

			echo "BEGIN:VEVENT\n";
			echo "UID:" . $booking->ID . "@" . $_SERVER['HTTP_HOST'] . "\n";
			echo "DTSTAMP:" . date( 'Ymd\THis\Z' ) . "\n";
			echo "DTSTART;VALUE=DATE:" . date( 'Ymd', strtotime( $checkin ) ) . "\n";
			echo "DTEND;VALUE=DATE:" . date( 'Ymd', strtotime( $checkout ) ) . "\n";
			echo "SUMMARY:" . sprintf( __( 'LuxeResort Booking #%d (%s)', 'resort-manager' ), $booking->ID, $room_title ) . "\n";
			echo "END:VEVENT\n";
		}

		echo "END:VCALENDAR\n";
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
			echo "SUMMARY:" . sprintf( __( 'Booking #%d', 'resort-manager' ), $booking->ID ) . "\n";
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
			update_post_meta( $room_id, '_resort_ical_last_sync_status', 'error' );
			update_post_meta( $room_id, '_resort_ical_last_sync_error', $response->get_error_message() );
			update_post_meta( $room_id, '_resort_ical_last_sync_time', current_time( 'mysql' ) );
			return;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			update_post_meta( $room_id, '_resort_ical_last_sync_status', 'error' );
			update_post_meta( $room_id, '_resort_ical_last_sync_error', sprintf( 'HTTP %d', $status_code ) );
			update_post_meta( $room_id, '_resort_ical_last_sync_time', current_time( 'mysql' ) );
			return;
		}

		$content = wp_remote_retrieve_body( $response );

		// Unfold lines as per RFC 5545 (remove CRLF followed by space/tab)
		$content = preg_replace( '/\r?\n[ \t]/', '', $content );

		// Match VEVENTS and extract dates (handles both DATE and DATE-TIME formats)
		preg_match_all( '/BEGIN:VEVENT.*?DTSTART(?:;VALUE=DATE|;VALUE=DATE-TIME)?:?(\d{8}(?:T\d{6}Z?)?).*?DTEND(?:;VALUE=DATE|;VALUE=DATE-TIME)?:?(\d{8}(?:T\d{6}Z?)?).*?END:VEVENT/s', $content, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'resort_availability';

		// Clear previous sync blocks for this room to avoid duplicates
		$wpdb->delete( $table, [ 'room_id' => $room_id, 'status' => 'sync' ] );

		$count = 0;
		foreach ( $matches as $match ) {
			$raw_start = $match[1];
			$raw_end   = $match[2];

			// Normalize to Y-m-d
			$start_ts = strtotime( substr($raw_start, 0, 8) );
			$end_ts   = strtotime( substr($raw_end, 0, 8) );

			if ( ! $start_ts || ! $end_ts ) continue;

			$start = date( 'Y-m-d', $start_ts );
			$end   = date( 'Y-m-d', $end_ts );

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
			$count++;
		}

		update_post_meta( $room_id, '_resort_ical_last_sync_status', 'success' );
		update_post_meta( $room_id, '_resort_ical_last_sync_count', $count );
		update_post_meta( $room_id, '_resort_ical_last_sync_time', current_time( 'mysql' ) );
		delete_post_meta( $room_id, '_resort_ical_last_sync_error' );
	}
}
