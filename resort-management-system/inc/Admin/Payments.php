<?php
namespace ResortManager\Admin;

class Payments {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_payments_page' ] );
	}

	public function add_payments_page() {
		add_submenu_page(
			'resort-manager',
			__( 'Payment History', 'resort-manager' ),
			__( 'Payments', 'resort-manager' ),
			'manage_options',
			'resort-payments',
			[ $this, 'render_payments_page' ]
		);
	}

	public function render_payments_page() {
		global $wpdb;
		$table_payments = $wpdb->prefix . 'resort_payments';

		$payments = $wpdb->get_results( "SELECT * FROM $table_payments ORDER BY created_at DESC" );
		?>
		<div class="wrap">
			<h1><?php _e( 'Payment Transaction History', 'resort-manager' ); ?></h1>
			<p><?php _e( 'Review all processed payments from Stripe, PayPal, and Offline transactions.', 'resort-manager' ); ?></p>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'ID', 'resort-manager' ); ?></th>
						<th><?php _e( 'Booking ID', 'resort-manager' ); ?></th>
						<th><?php _e( 'Transaction ID', 'resort-manager' ); ?></th>
						<th><?php _e( 'Amount', 'resort-manager' ); ?></th>
						<th><?php _e( 'Method', 'resort-manager' ); ?></th>
						<th><?php _e( 'Status', 'resort-manager' ); ?></th>
						<th><?php _e( 'Date', 'resort-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $payments ) ) : ?>
						<tr>
							<td colspan="7"><?php _e( 'No payment records found.', 'resort-manager' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $payments as $payment ) : ?>
							<tr>
								<td><?php echo $payment->id; ?></td>
								<td><a href="<?php echo get_edit_post_link( $payment->booking_id ); ?>">#<?php echo $payment->booking_id; ?></a></td>
								<td><?php echo esc_html( $payment->transaction_id ?: '-' ); ?></td>
								<td><strong><?php echo get_option( 'resort_currency', 'USD' ); ?> <?php echo number_format( $payment->amount, 2 ); ?></strong></td>
								<td><?php echo esc_html( ucfirst( $payment->method ) ); ?></td>
								<td><span class="status-badge status-<?php echo esc_attr( strtolower($payment->status) ); ?>"><?php echo esc_html( ucfirst($payment->status) ); ?></span></td>
								<td><?php echo esc_html( $payment->created_at ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
