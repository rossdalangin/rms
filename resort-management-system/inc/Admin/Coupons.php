<?php
namespace ResortManager\Admin;

class Coupons {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_coupons_page' ] );
	}

	public function add_coupons_page() {
		add_submenu_page(
			'resort-settings',
			__( 'Coupons', 'resort-manager' ),
			__( 'Coupons', 'resort-manager' ),
			'manage_options',
			'resort-coupons',
			[ $this, 'render_coupons_page' ]
		);
	}

	public function render_coupons_page() {
		global $wpdb;
		$table_coupons = $wpdb->prefix . 'resort_coupons';

		if ( isset( $_POST['add_coupon'] ) && check_admin_referer( 'add_coupon' ) ) {
			$wpdb->insert( $table_coupons, [
				'code'            => sanitize_text_field( $_POST['code'] ),
				'discount_amount' => floatval( $_POST['discount_amount'] ),
				'discount_type'   => sanitize_text_field( $_POST['discount_type'] ),
				'expiry_date'     => sanitize_text_field( $_POST['expiry_date'] ),
			] );
		}

		$coupons = $wpdb->get_results( "SELECT * FROM $table_coupons" );
		?>
		<div class="wrap">
			<h1><?php _e( 'Coupons & Promo Codes', 'resort-manager' ); ?></h1>
			<form method="post">
				<?php wp_nonce_field( 'add_coupon' ); ?>
				<input type="text" name="code" placeholder="CODE" required>
				<input type="number" name="discount_amount" placeholder="Amount" step="0.01" required>
				<select name="discount_type">
					<option value="fixed">Fixed</option>
					<option value="percentage">Percentage</option>
				</select>
				<input type="date" name="expiry_date">
				<input type="submit" name="add_coupon" class="button" value="Add Coupon">
			</form>

			<h3>Existing Coupons</h3>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>Code</th>
						<th>Discount</th>
						<th>Expiry</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $coupons as $coupon ) : ?>
						<tr>
							<td><?php echo esc_html( $coupon->code ); ?></td>
							<td><?php echo $coupon->discount_amount; ?> (<?php echo $coupon->discount_type; ?>)</td>
							<td><?php echo $coupon->expiry_date; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
