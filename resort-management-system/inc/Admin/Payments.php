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

		if ( isset( $_POST['resort_record_payment'] ) && check_admin_referer( 'resort_payment_action' ) ) {
			$wpdb->insert( $table_payments, [
				'booking_id'     => intval( $_POST['booking_id'] ),
				'transaction_id' => sanitize_text_field( $_POST['transaction_id'] ),
				'amount'         => floatval( $_POST['amount'] ),
				'method'         => sanitize_text_field( $_POST['method'] ),
				'status'         => sanitize_text_field( $_POST['status'] )
			] );
			echo '<div class="updated"><p>' . __( 'Payment record added.', 'resort-manager' ) . '</p></div>';
		}

		if ( isset( $_POST['resort_update_payment'] ) && check_admin_referer( 'resort_payment_action' ) ) {
			$wpdb->update( $table_payments, [
				'transaction_id' => sanitize_text_field( $_POST['transaction_id'] ),
				'status'         => sanitize_text_field( $_POST['status'] )
			], [ 'id' => intval( $_POST['payment_id'] ) ] );
			echo '<div class="updated"><p>' . __( 'Payment record updated.', 'resort-manager' ) . '</p></div>';
		}

		$payments = $wpdb->get_results( "SELECT * FROM $table_payments ORDER BY created_at DESC" );
		?>
		<div class="wrap toplevel_page_resort-manager">
			<h1><?php _e( 'Payment Transaction History', 'resort-manager' ); ?></h1>

			<div class="resort-admin-card" style="border-top-color: var(--resort-green);">
				<h3><span class="dashicons dashicons-money-alt" style="color:var(--resort-green);"></span> <?php _e( 'Record Manual Payment', 'resort-manager' ); ?></h3>
				<form method="post" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; background:var(--resort-light-teal); padding:20px; border-radius:8px;">
					<?php wp_nonce_field( 'resort_payment_action' ); ?>
					<div>
						<label><?php _e( 'Booking ID', 'resort-manager' ); ?></label><br>
						<input type="number" name="booking_id" required style="width: 100px;">
					</div>
					<div>
						<label><?php _e( 'Amount', 'resort-manager' ); ?></label><br>
						<input type="number" name="amount" step="0.01" required style="width: 120px;">
					</div>
					<div>
						<label><?php _e( 'Method', 'resort-manager' ); ?></label><br>
						<select name="method">
							<option value="cash">Cash</option>
							<option value="bank_transfer">Bank Transfer</option>
							<option value="check">Check</option>
							<option value="offline">Other Offline</option>
						</select>
					</div>
					<div>
						<label><?php _e( 'Ref / Trans ID', 'resort-manager' ); ?></label><br>
						<input type="text" name="transaction_id">
					</div>
					<div>
						<label><?php _e( 'Status', 'resort-manager' ); ?></label><br>
						<select name="status">
							<option value="completed">Completed</option>
							<option value="pending">Pending</option>
						</select>
					</div>
					<button type="submit" name="resort_record_payment" class="button button-primary"><?php _e( 'Record Payment', 'resort-manager' ); ?></button>
				</form>
			</div>

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
								<form method="post">
									<?php wp_nonce_field( 'resort_payment_action' ); ?>
									<input type="hidden" name="payment_id" value="<?php echo $payment->id; ?>">
									<td><?php echo $payment->id; ?></td>
									<td><a href="<?php echo get_edit_post_link( $payment->booking_id ); ?>">#<?php echo $payment->booking_id; ?></a></td>
									<td>
										<input type="text" name="transaction_id" value="<?php echo esc_attr( $payment->transaction_id ); ?>" style="width: 100%;">
									</td>
									<td><strong><?php echo \ResortManager\Core\PricingEngine::format_price( $payment->amount ); ?></strong></td>
									<td><?php echo esc_html( ucfirst( $payment->method ) ); ?></td>
									<td>
										<select name="status">
											<option value="completed" <?php selected($payment->status, 'completed'); ?>>Completed</option>
											<option value="pending" <?php selected($payment->status, 'pending'); ?>>Pending</option>
											<option value="failed" <?php selected($payment->status, 'failed'); ?>>Failed</option>
										</select>
									</td>
									<td>
										<input type="submit" name="resort_update_payment" class="button button-small" value="Update">
									</td>
								</form>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
