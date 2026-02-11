<?php
namespace ResortManager\Core;

class AvailabilityEngine {
	public static function get_available_rooms( $checkin, $checkout ) {
		$cache_key = 'resort_avail_' . md5( $checkin . $checkout );
		$cached = get_transient( $cache_key );
		if ( false !== $cached ) return $cached;

		global $wpdb;
		$table_availability = $wpdb->prefix . 'resort_availability';

		// Find rooms that have any 'booked' status in the given range
		$booked_rooms = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT room_id FROM $table_availability
			 WHERE date >= %s AND date < %s AND status = 'booked'",
			$checkin, $checkout
		) );

		$args = [
			'post_type'   => 'accommodation',
			'numberposts' => -1,
		];

		if ( ! empty( $booked_rooms ) ) {
			$args['post__not_in'] = $booked_rooms;
		}

		$rooms = get_posts( $args );
		set_transient( $cache_key, $rooms, HOUR_IN_SECONDS );
		return $rooms;
	}

	public static function mark_as_booked( $room_ids, $checkin, $checkout, $booking_id ) {
		global $wpdb;
		$table_availability = $wpdb->prefix . 'resort_availability';

		if ( ! is_array( $room_ids ) ) {
			$room_ids = [ $room_ids ];
		}

		$start = new \DateTime( $checkin );
		$end = new \DateTime( $checkout );
		$interval = new \DateInterval( 'P1D' );
		$period = new \DatePeriod( $start, $interval, $end );

		foreach ( $room_ids as $room_id ) {
			foreach ( $period as $date ) {
				$wpdb->insert( $table_availability, [
					'room_id'    => intval($room_id),
					'booking_id' => intval($booking_id),
					'date'       => $date->format( 'Y-m-d' ),
					'status'     => 'booked',
				] );
			}
		}

		// Flush all availability transients
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_resort_avail_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_resort_avail_%'" );
	}
}
