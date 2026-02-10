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
			$booking_id = intval( $_POST['booking_id'] );
			$transaction_id = sanitize_text_field( $_POST['transaction_id'] );
			$method = sanitize_text_field( $_POST['method'] );
			$status = sanitize_text_field( $_POST['status'] );

			$wpdb->insert( $table_payments, [
				'booking_id'     => $booking_id,
				'transaction_id' => $transaction_id,
				'amount'         => floatval( $_POST['amount'] ),
				'method'         => $method,
				'status'         => $status
			] );

			if ( 'completed' === $status ) {
				\ResortManager\Core\BookingManager::confirm_booking( $booking_id, $transaction_id, $method );
			}

			echo '<div class="updated"><p>' . __( 'Payment record added.', 'resort-manager' ) . '</p></div>';
		}

		if ( isset( $_POST['resort_update_payment'] ) && check_admin_referer( 'resort_payment_action' ) ) {
			$payment_id = intval( $_POST['resort_update_payment'] );
			$new_status = sanitize_text_field( $_POST['status_' . $payment_id] );
			$transaction_id = sanitize_text_field( $_POST['transaction_id_' . $payment_id] );

			$wpdb->update( $table_payments, [
				'transaction_id' => $transaction_id,
				'status'         => $new_status
			], [ 'id' => $payment_id ] );

			// Sync booking status with new payment status
			$payment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_payments WHERE id = %d", $payment_id ) );
			if ( $payment_data && ! empty( $payment_data->booking_id ) ) {
				if ( 'completed' === $new_status ) {
					\ResortManager\Core\BookingManager::confirm_booking( $payment_data->booking_id, $transaction_id, $payment_data->method );
				} else {
					\ResortManager\Core\BookingManager::sync_status( $payment_data->booking_id, $new_status );
				}
			}

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

			<form method="post">
				<?php wp_nonce_field( 'resort_payment_action' ); ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php _e( 'ID', 'resort-manager' ); ?></th>
							<th><?php _e( 'Booking', 'resort-manager' ); ?></th>
							<th><?php _e( 'Reference', 'resort-manager' ); ?></th>
							<th><?php _e( 'Details', 'resort-manager' ); ?></th>
							<th><?php _e( 'Amount', 'resort-manager' ); ?></th>
							<th><?php _e( 'Method', 'resort-manager' ); ?></th>
							<th><?php _e( 'Status', 'resort-manager' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $payments ) ) : ?>
							<tr>
								<td colspan="8"><?php _e( 'No payment records found.', 'resort-manager' ); ?></td>
							</tr>
						<?php else : ?>
							<?php foreach ( $payments as $payment ) :
								$fname = get_post_meta( $payment->booking_id, '_resort_first_name', true );
								$lname = get_post_meta( $payment->booking_id, '_resort_last_name', true );
								$booking_title = ( ! empty( $fname ) || ! empty( $lname ) ) ? "$fname $lname" : str_replace( 'Booking for ', '', get_the_title( $payment->booking_id ) );
								$room_id = get_post_meta( $payment->booking_id, '_resort_room_id', true );
								$room_title = get_the_title( $room_id );
								$checkin = get_post_meta( $payment->booking_id, '_resort_checkin', true );
								?>
								<tr>
									<td><?php echo $payment->id; ?></td>
									<td>
										<a href="<?php echo get_edit_post_link( $payment->booking_id ); ?>"><strong>#<?php echo $payment->booking_id; ?></strong></a><br>
										<small><?php echo esc_html( $booking_title ); ?></small>
									</td>
									<td>
										<input type="text" name="transaction_id_<?php echo $payment->id; ?>" value="<?php echo esc_attr( $payment->transaction_id ); ?>" style="width: 100%;">
									</td>
									<td>
										<small><?php echo esc_html( $room_title ); ?></small><br>
										<small><?php echo esc_html( $checkin ); ?></small>
									</td>
									<td><strong><?php echo \ResortManager\Core\PricingEngine::format_price( $payment->amount ); ?></strong></td>
									<td><?php echo esc_html( ucfirst( $payment->method ) ); ?></td>
									<td>
										<select name="status_<?php echo $payment->id; ?>">
											<option value="completed" <?php selected($payment->status, 'completed'); ?>>Completed</option>
											<option value="pending" <?php selected($payment->status, 'pending'); ?>>Pending</option>
											<option value="failed" <?php selected($payment->status, 'failed'); ?>>Failed</option>
										</select>
									</td>
									<td>
										<button type="submit" name="resort_update_payment" class="button button-small" value="<?php echo $payment->id; ?>">
											<?php _e( 'Update', 'resort-manager' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</form>
		</div>
		<?php
	}
}
