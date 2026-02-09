<?php
namespace ResortManager\Core;

class Notifications {
	public function __construct() {
		add_action( 'resort_booking_confirmed', [ $this, 'send_confirmation_email' ], 10, 1 );
	}

	public function send_confirmation_email( $booking_id ) {
		$email = get_post_meta( $booking_id, '_resort_guest_email', true );
		$subject = __( 'Your Resort Booking Confirmation', 'resort-manager' );

		$message = "<h1>" . __( 'Booking Confirmed!', 'resort-manager' ) . "</h1>";
		$message .= "<p>" . sprintf( __( 'Thank you for your reservation. Your Booking ID is #%d.', 'resort-manager' ), $booking_id ) . "</p>";

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];

		wp_mail( $email, $subject, $message, $headers );
	}
}
