<?php
namespace ResortManager\Core;

class BookingManager {
	/**
	 * Confirms a booking and handles associated tasks like loyalty points and notifications.
	 *
	 * @param int    $booking_id     The booking ID.
	 * @param string $transaction_id The gateway transaction ID.
	 * @param string $method         The payment method.
	 */
	public static function confirm_booking( $booking_id, $transaction_id, $method ) {
		$current_status = get_post_meta( $booking_id, '_resort_status', true );
		if ( 'confirmed' === $current_status ) {
			return; // Already confirmed
		}

		// Update Booking Meta
		update_post_meta( $booking_id, '_resort_status', 'confirmed' );
		update_post_meta( $booking_id, '_resort_payment_status', 'completed' );
		update_post_meta( $booking_id, '_resort_payment_method', $method );
		update_post_meta( $booking_id, '_resort_transaction_id', $transaction_id );

		// Ensure record exists/updated in Payments table
		global $wpdb;
		$table_payments = $wpdb->prefix . 'resort_payments';
		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_payments WHERE booking_id = %d", $booking_id ) );
		$amount = get_post_meta( $booking_id, '_resort_total_price', true );

		if ( $existing ) {
			$wpdb->update( $table_payments, [
				'transaction_id' => $transaction_id,
				'status'         => 'completed',
				'method'         => $method
			], [ 'id' => $existing ] );
		} else {
			$wpdb->insert( $table_payments, [
				'booking_id'     => $booking_id,
				'transaction_id' => $transaction_id,
				'amount'         => $amount,
				'method'         => $method,
				'status'         => 'completed'
			] );
		}

		// Award Loyalty Points (1 point per $10)
		$guest_id = get_post_meta( $booking_id, '_resort_guest_id', true );
		if ( $guest_id ) {
			$amount = get_post_meta( $booking_id, '_resort_total_price', true );
			$points = floor( floatval( $amount ) / 10 );
			$current_points = get_user_meta( $guest_id, '_resort_loyalty_points', true ) ?: 0;
			update_user_meta( $guest_id, '_resort_loyalty_points', intval($current_points) + $points );
		}

		// Trigger notifications
		do_action( 'resort_booking_confirmed', $booking_id );

		// Log Activity
		if ( class_exists( '\ResortManager\Core\ActivityLogger' ) ) {
			\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Booking #%d confirmed via %s.', 'resort-manager' ), $booking_id, $method ) );
		}
	}

	/**
	 * Synchronizes booking status with a new payment status.
	 *
	 * @param int    $booking_id
	 * @param string $payment_status
	 */
	public static function sync_status( $booking_id, $payment_status ) {
		$old_status = get_post_meta( $booking_id, '_resort_status', true );

		// Map payment status to booking status
		$new_booking_status = $payment_status;
		if ( 'completed' === $payment_status ) {
			$new_booking_status = 'confirmed';
		}

		if ( $old_status === $new_booking_status ) {
			return;
		}

		// If transitioning FROM confirmed, reverse loyalty points earned
		if ( 'confirmed' === $old_status && 'confirmed' !== $new_booking_status ) {
			$guest_id = get_post_meta( $booking_id, '_resort_guest_id', true );
			if ( $guest_id ) {
				$amount = get_post_meta( $booking_id, '_resort_total_price', true );
				$points_earned = floor( floatval( $amount ) / 10 );
				$current_points = get_user_meta( $guest_id, '_resort_loyalty_points', true ) ?: 0;
				update_user_meta( $guest_id, '_resort_loyalty_points', max( 0, intval($current_points) - $points_earned ) );
			}
		}

		// If status becomes failed/cancelled, refund points spent
		if ( in_array( $new_booking_status, [ 'failed', 'cancelled', 'abandoned' ] ) ) {
			$guest_id = get_post_meta( $booking_id, '_resort_guest_id', true );
			$points_redeemed = get_post_meta( $booking_id, '_resort_points_redeemed', true );
			if ( $guest_id && $points_redeemed ) {
				$current_points = get_user_meta( $guest_id, '_resort_loyalty_points', true ) ?: 0;
				update_user_meta( $guest_id, '_resort_loyalty_points', intval($current_points) + intval($points_redeemed) );
				delete_post_meta( $booking_id, '_resort_points_redeemed' );
				\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Guest #%d refunded %d points due to %s status.', 'resort-manager' ), $guest_id, $points_redeemed, $new_booking_status ) );
			}
		}

		// Release inventory if status becomes failed, cancelled, or abandoned
		if ( in_array( $new_booking_status, [ 'failed', 'cancelled', 'abandoned' ] ) ) {
			global $wpdb;
			$table_availability = $wpdb->prefix . 'resort_availability';
			$wpdb->delete( $table_availability, [ 'booking_id' => $booking_id ] );
		}

		// Update Meta
		update_post_meta( $booking_id, '_resort_status', $new_booking_status );
		update_post_meta( $booking_id, '_resort_payment_status', $payment_status );

		if ( class_exists( '\ResortManager\Core\ActivityLogger' ) ) {
			\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Booking #%d status updated to %s due to payment update.', 'resort-manager' ), $booking_id, $new_booking_status ) );
		}
	}
}
