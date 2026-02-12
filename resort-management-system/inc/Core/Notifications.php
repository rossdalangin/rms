<?php
namespace ResortManager\Core;

class Notifications {
	public function __construct() {
		add_action( 'resort_booking_confirmed', [ $this, 'send_confirmation_email' ], 10, 1 );
		add_action( 'resort_cleanup_abandoned', [ $this, 'trigger_abandoned_reminders' ] );
		add_action( 'resort_daily_sync', [ $this, 'trigger_daily_concierge_emails' ] );
		add_action( 'resort_service_request_submitted', [ $this, 'send_service_request_to_admin' ], 10, 2 );
	}

	public function send_confirmation_email( $booking_id ) {
		$email = get_post_meta( $booking_id, '_resort_guest_email', true );
		$phone = get_post_meta( $booking_id, '_resort_guest_phone', true );

		$subject = __( 'Your Resort Booking Confirmation', 'resort-manager' );

		$template = get_option( 'resort_email_template_confirmation' );
		if ( ! $template ) {
			$body = "<h1>" . __( 'Booking Confirmed!', 'resort-manager' ) . "</h1>";
			$body .= "<p>" . sprintf( __( 'Thank you for your reservation. Your Booking ID is #%d.', 'resort-manager' ), $booking_id ) . "</p>";
			$body .= "<p>" . __( 'We are preparing your paradise sanctuary. You can view your stay details in your private dashboard.', 'resort-manager' ) . "</p>";
		} else {
			$body = $this->replace_placeholders( $template, $booking_id );
		}

		$message = $this->wrap_email_template( $body );

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		wp_mail( $email, $subject, $message, $headers );

		// Trigger SMS if phone exists
		if ( $phone ) {
			$sms_message = sprintf( __( 'Aloha! Your LuxeResort booking #%d is confirmed. See you soon!', 'resort-manager' ), $booking_id );
			self::send_sms( $phone, $sms_message );
		}
	}

	public function send_service_request_to_admin( $booking_id, $details ) {
		$admin_email = get_option( 'admin_email' );
		$subject = sprintf( __( 'New Service Request: Booking #%d', 'resort-manager' ), $booking_id );

		$body = "<h2>" . __( 'Service Request Received', 'resort-manager' ) . "</h2>";
		$body .= "<p><strong>" . __( 'Booking ID:', 'resort-manager' ) . "</strong> #" . $booking_id . "</p>";
		$body .= "<p><strong>" . __( 'Details:', 'resort-manager' ) . "</strong><br>" . nl2br( esc_html( $details ) ) . "</p>";
		$body .= "<p><a href='" . admin_url( 'post.php?post=' . $booking_id . '&action=edit' ) . "' class='resort-btn'>" . __( 'View Booking', 'resort-manager' ) . "</a></p>";

		$message = $this->wrap_email_template( $body );

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		wp_mail( $admin_email, $subject, $message, $headers );
	}

	public function trigger_daily_concierge_emails() {
		$this->send_pre_arrival_concierge_emails();
		$this->send_post_departure_feedback_emails();
	}

	private function send_pre_arrival_concierge_emails() {
		$target_date = date( 'Y-m-d', strtotime( '+2 days' ) );
		$bookings = get_posts( [
			'post_type'  => 'booking',
			'meta_query' => [
				[ 'key' => '_resort_checkin', 'value' => $target_date ],
				[ 'key' => '_resort_status', 'value' => 'confirmed' ],
				[ 'key' => '_resort_pre_arrival_sent', 'compare' => 'NOT EXISTS' ]
			],
			'numberposts' => -1
		] );

		foreach ( $bookings as $booking ) {
			$email = get_post_meta( $booking->ID, '_resort_guest_email', true );
			$subject = __( 'Your Paradise Escape Awaits - Pre-Arrival Concierge', 'resort-manager' );

			$template = get_option( 'resort_email_template_pre_arrival' );
			if ( ! $template ) {
				$fname = get_post_meta( $booking->ID, '_resort_first_name', true );
				$body = "<h2>" . sprintf( __( 'Aloha, %s!', 'resort-manager' ), $fname ) . "</h2>";
				$body .= "<p>" . __( 'We are thrilled to welcome you to LuxeResort in just 2 days. To ensure your stay is perfect, would you like to pre-book any spa treatments or airport transfers?', 'resort-manager' ) . "</p>";
				$body .= "<p><a href='" . home_url('/guest-dashboard/') . "' class='resort-btn'>" . __( 'Personalize My Stay', 'resort-manager' ) . "</a></p>";
			} else {
				$body = $this->replace_placeholders( $template, $booking->ID );
			}

			$message = $this->wrap_email_template( $body );
			wp_mail( $email, $subject, $message, [ 'Content-Type: text/html; charset=UTF-8' ] );
			update_post_meta( $booking->ID, '_resort_pre_arrival_sent', '1' );
		}
	}

	private function send_post_departure_feedback_emails() {
		$target_date = date( 'Y-m-d', strtotime( '-1 day' ) );
		$bookings = get_posts( [
			'post_type'  => 'booking',
			'meta_query' => [
				[ 'key' => '_resort_checkout', 'value' => $target_date ],
				[ 'key' => '_resort_status', 'value' => 'confirmed' ],
				[ 'key' => '_resort_post_departure_sent', 'compare' => 'NOT EXISTS' ]
			],
			'numberposts' => -1
		] );

		foreach ( $bookings as $booking ) {
			$email = get_post_meta( $booking->ID, '_resort_guest_email', true );
			$subject = __( 'Thank you for staying at LuxeResort', 'resort-manager' );

			$template = get_option( 'resort_email_template_post_departure' );
			if ( ! $template ) {
				$fname = get_post_meta( $booking->ID, '_resort_first_name', true );
				$body = "<h2>" . sprintf( __( 'Mahalo, %s!', 'resort-manager' ), $fname ) . "</h2>";
				$body .= "<p>" . __( 'We hope you enjoyed your time in paradise. Your feedback helps us maintain our 5-star standards. Would you mind sharing a brief review of your stay?', 'resort-manager' ) . "</p>";
				$body .= "<p><a href='" . home_url('/guest-dashboard/') . "' class='resort-btn'>" . __( 'Leave a Review', 'resort-manager' ) . "</a></p>";
			} else {
				$body = $this->replace_placeholders( $template, $booking->ID );
			}

			$message = $this->wrap_email_template( $body );
			wp_mail( $email, $subject, $message, [ 'Content-Type: text/html; charset=UTF-8' ] );
			update_post_meta( $booking->ID, '_resort_post_departure_sent', '1' );
		}
	}

	public function trigger_abandoned_reminders() {
		// Use the existing maintenance methods to handle logic centrally
		\ResortManager\Admin\Maintenance::send_reminders();
		\ResortManager\Admin\Maintenance::cleanup_abandoned_bookings();
	}

	public static function send_abandoned_reminder( $booking_id ) {
		$email = get_post_meta( $booking_id, '_resort_guest_email', true );
		$subject = __( 'Still interested in your stay at LuxeResort?', 'resort-manager' );

		$instance = new self();
		$template = get_option( 'resort_email_template_abandoned' );
		if ( ! $template ) {
			$body = "<h1>" . __( 'We noticed you left something behind...', 'resort-manager' ) . "</h1>";
			$body .= "<p>" . __( 'You started a booking but didn’t complete the payment. We have held your room for a short time, but it will be released soon.', 'resort-manager' ) . "</p>";
			$body .= "<p><a href='" . home_url('/book-your-stay') . "' class='resort-btn'>" . __( 'Complete your booking now', 'resort-manager' ) . "</a></p>";
		} else {
			$body = $instance->replace_placeholders( $template, $booking_id );
		}

		$message = $instance->wrap_email_template( $body );

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		wp_mail( $email, $subject, $message, $headers );

		update_post_meta( $booking_id, '_resort_reminder_sent', '1' );
	}

	private function replace_placeholders( $content, $booking_id ) {
		$placeholders = [
			'{booking_id}' => $booking_id,
			'{first_name}' => get_post_meta( $booking_id, '_resort_first_name', true ),
			'{last_name}'  => get_post_meta( $booking_id, '_resort_last_name', true ),
			'{checkin}'    => get_post_meta( $booking_id, '_resort_checkin', true ),
			'{checkout}'   => get_post_meta( $booking_id, '_resort_checkout', true ),
		];
		return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $content );
	}

	private function wrap_email_template( $content ) {
		$resort_name = get_option( 'resort_name', 'LuxeResort' );
		$primary_color = '#008080';

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<style>
				body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #f4f7f7; margin: 0; padding: 0; }
				.email-container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
				.header { background: <?php echo $primary_color; ?>; padding: 30px; text-align: center; color: #ffffff; }
				.header h1 { margin: 0; font-size: 24px; letter-spacing: 2px; }
				.content { padding: 40px; color: #333333; line-height: 1.6; }
				.resort-btn { display: inline-block; padding: 12px 25px; background: #FF7F50; color: #ffffff !important; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
				.footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #999999; }
			</style>
		</head>
		<body>
			<div class="email-container">
				<div class="header">
					<h1><?php echo esc_html($resort_name); ?></h1>
				</div>
				<div class="content">
					<?php echo $content; ?>
				</div>
				<div class="footer">
					<p>&copy; <?php echo date('Y'); ?> <?php echo esc_html($resort_name); ?>. <?php _e( 'All rights reserved.', 'resort-manager' ); ?></p>
					<p><?php _e( 'This is an automated message from our luxury concierge system.', 'resort-manager' ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php
		return ob_get_clean();
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
