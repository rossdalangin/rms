<?php
namespace ResortManager\Core;

class InvoiceEngine {
	public function __construct() {
		add_action( 'init', [ $this, 'handle_invoice_request' ] );
	}

	public function handle_invoice_request() {
		if ( ! isset( $_GET['resort_invoice'] ) ) {
			return;
		}

		$booking_id = intval( $_GET['resort_invoice'] );
		$this->render_invoice( $booking_id );
		exit;
	}

	private function render_invoice( $booking_id ) {
		$booking = get_post( $booking_id );
		if ( ! $booking || 'booking' !== $booking->post_type ) return;

		// Check permissions - only admin or the guest who booked it
		if ( ! current_user_can( 'manage_options' ) ) {
			$guest_id = get_post_meta( $booking_id, '_resort_guest_id', true );
			if ( intval($guest_id) !== get_current_user_id() ) {
				wp_die( __( 'You do not have permission to view this invoice.', 'resort-manager' ) );
			}
		}

		$fname = get_post_meta( $booking_id, '_resort_first_name', true );
		$lname = get_post_meta( $booking_id, '_resort_last_name', true );
		$email = get_post_meta( $booking_id, '_resort_guest_email', true );
		$checkin = get_post_meta( $booking_id, '_resort_checkin', true );
		$checkout = get_post_meta( $booking_id, '_resort_checkout', true );
		$room_id = get_post_meta( $booking_id, '_resort_room_id', true );
		$total = get_post_meta( $booking_id, '_resort_total_price', true );
		$services = get_post_meta( $booking_id, '_resort_services', true ) ?: [];

		$resort_name = get_option( 'resort_name', 'LuxeResort' );

		?>
		<!DOCTYPE html>
		<html>
		<head>
			<title>Invoice #<?php echo $booking_id; ?> - <?php echo esc_html($resort_name); ?></title>
			<style>
				body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; padding: 40px; max-width: 800px; margin: 0 auto; }
				.header { border-bottom: 2px solid #008080; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
				.resort-info h1 { margin: 0; color: #008080; }
				.invoice-meta { text-align: right; }
				.details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px; }
				table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
				th { text-align: left; background: #f9f9f9; padding: 12px; border-bottom: 2px solid #eee; }
				td { padding: 12px; border-bottom: 1px solid #eee; }
				.total-row { font-size: 1.2em; font-weight: bold; background: #f0fafa; }
				.footer { font-size: 0.9em; color: #777; text-align: center; margin-top: 60px; border-top: 1px solid #eee; padding-top: 20px; }
				@media print { .print-btn { display: none; } }
				.print-btn { background: #008080; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; float: right; margin-bottom: 20px; }
			</style>
		</head>
		<body>
			<button class="print-btn" onclick="window.print();"><?php _e( 'Print Invoice', 'resort-manager' ); ?></button>
			<div class="header">
				<div class="resort-info">
					<h1><?php echo esc_html($resort_name); ?></h1>
					<p><?php _e( 'Paradise Found', 'resort-manager' ); ?></p>
				</div>
				<div class="invoice-meta">
					<h2><?php _e( 'INVOICE', 'resort-manager' ); ?></h2>
					<p><strong><?php _e( 'ID:', 'resort-manager' ); ?></strong> #<?php echo $booking_id; ?></p>
					<p><strong><?php _e( 'Date:', 'resort-manager' ); ?></strong> <?php echo date('F j, Y'); ?></p>
				</div>
			</div>

			<div class="details-grid">
				<div>
					<h4><?php _e( 'Guest Information', 'resort-manager' ); ?></h4>
					<p><strong><?php _e( 'Name:', 'resort-manager' ); ?></strong> <?php echo esc_html("$fname $lname"); ?></p>
					<p><strong><?php _e( 'Email:', 'resort-manager' ); ?></strong> <?php echo esc_html($email); ?></p>
				</div>
				<div>
					<h4><?php _e( 'Stay Details', 'resort-manager' ); ?></h4>
					<p><strong><?php _e( 'Room:', 'resort-manager' ); ?></strong> <?php echo get_the_title($room_id); ?></p>
					<p><strong><?php _e( 'Dates:', 'resort-manager' ); ?></strong> <?php echo esc_html("$checkin to $checkout"); ?></p>
				</div>
			</div>

			<table>
				<thead>
					<tr>
						<th><?php _e( 'Description', 'resort-manager' ); ?></th>
						<th style="text-align: right;"><?php _e( 'Amount', 'resort-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php printf( __( 'Accommodation (%s - %s)', 'resort-manager' ), esc_html($checkin), esc_html($checkout) ); ?></td>
						<td style="text-align: right;"><?php echo \ResortManager\Core\PricingEngine::format_price($total); // Simplified for this release ?></td>
					</tr>
					<?php foreach ( $services as $s_id ) :
						$s_post = get_post($s_id);
						$s_price = get_post_meta($s_id, '_resort_service_price', true);
						?>
						<tr>
							<td>+ <?php echo esc_html($s_post->post_title); ?></td>
							<td style="text-align: right;"><?php echo \ResortManager\Core\PricingEngine::format_price($s_price); ?></td>
						</tr>
					<?php endforeach; ?>
					<tr class="total-row">
						<td><?php _e( 'TOTAL PAID', 'resort-manager' ); ?></td>
						<td style="text-align: right;"><?php echo \ResortManager\Core\PricingEngine::format_price($total); ?></td>
					</tr>
				</tbody>
			</table>

			<div class="footer">
				<p><?php printf( __( 'Thank you for choosing %s. We look forward to your arrival.', 'resort-manager' ), esc_html($resort_name) ); ?></p>
				<p><small><?php _e( 'This is a computer-generated document. No signature required.', 'resort-manager' ); ?></small></p>
			</div>
		</body>
		</html>
		<?php
	}
}
