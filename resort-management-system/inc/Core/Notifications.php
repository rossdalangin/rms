<?php
namespace ResortManager\Core;

class Notifications {
	public function __construct() {
		add_action( 'resort_booking_confirmed', [ $this, 'send_confirmation_email' ], 10, 1 );
	}

	public function send_confirmation_email( $booking_id ) {
		$email = get_post_meta( $booking_id, '_resort_guest_email', true );
		$phone = get_post_meta( $booking_id, '_resort_guest_phone', true );

		$subject = __( 'Your Resort Booking Confirmation', 'resort-manager' );

		$template = get_option( 'resort_email_template_confirmation' );
		if ( ! $template ) {
			$template = "<h1>" . __( 'Booking Confirmed!', 'resort-manager' ) . "</h1>";
			$template .= "<p>" . sprintf( __( 'Thank you for your reservation. Your Booking ID is #%d.', 'resort-manager' ), $booking_id ) . "</p>";
		} else {
			$template = str_replace( '{booking_id}', $booking_id, $template );
		}

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		wp_mail( $email, $subject, $template, $headers );

		// Trigger SMS if phone exists
		if ( $phone ) {
			$sms_message = sprintf( __( 'Aloha! Your LuxeResort booking #%d is confirmed. See you soon!', 'resort-manager' ), $booking_id );
			self::send_sms( $phone, $sms_message );
		}
	}

	public static function send_abandoned_reminder( $booking_id ) {
		$email = get_post_meta( $booking_id, '_resort_guest_email', true );
		$subject = __( 'Still interested in your stay at LuxeResort?', 'resort-manager' );

		$message = "<h1>" . __( 'We noticed you left something behind...', 'resort-manager' ) . "</h1>";
		$message .= "<p>" . __( 'You started a booking but didnt complete the payment. We have held your room for a short time, but it will be released soon.', 'resort-manager' ) . "</p>";
		$message .= "<p><a href='" . home_url('/book-your-stay') . "'>" . __( 'Complete your booking now', 'resort-manager' ) . "</a></p>";

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		wp_mail( $email, $subject, $message, $headers );

		update_post_meta( $booking_id, '_resort_reminder_sent', '1' );
	}

	public static function send_sms( $to, $message ) {
		$sid = get_option( 'resort_twilio_sid' );
		$token = get_option( 'resort_twilio_token' );
		$from = get_option( 'resort_twilio_number' );

		if ( empty( $sid ) || empty( $token ) ) return false;

		$url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";

		$response = wp_remote_post( $url, [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode( "$sid:$token" ),
			],
			'body' => [
				'From' => $from,
				'To'   => $to,
				'Body' => $message,
			],
		] );

		return ! is_wp_error( $response );
	}
}
