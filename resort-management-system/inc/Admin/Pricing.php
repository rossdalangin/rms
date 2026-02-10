<?php
namespace ResortManager\Admin;

class Pricing {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_pricing_page' ] );
	}

	public function add_pricing_page() {
		add_submenu_page(
			'resort-manager',
			__( 'Pricing Rules', 'resort-manager' ),
			__( 'Pricing Rules', 'resort-manager' ),
			'manage_options',
			'resort-pricing',
			[ $this, 'render_pricing_page' ]
		);
	}

	public function render_pricing_page() {
		global $wpdb;
		$table_pricing = $wpdb->prefix . 'resort_pricing';

		if ( isset( $_POST['add_rule'] ) && check_admin_referer( 'add_pricing_rule' ) ) {
			$wpdb->insert( $table_pricing, [
				'room_id'        => intval( $_POST['room_id'] ),
				'start_date'     => sanitize_text_field( $_POST['start_date'] ),
				'end_date'       => sanitize_text_field( $_POST['end_date'] ),
				'price_modifier' => floatval( $_POST['price_modifier'] ),
				'modifier_type'  => sanitize_text_field( $_POST['modifier_type'] ),
				'priority'       => intval( $_POST['priority'] ),
			] );
			echo '<div class="updated"><p>Rule added!</p></div>';
		}

		$rules = $wpdb->get_results( "SELECT * FROM $table_pricing ORDER BY room_id, priority DESC" );
		$rooms = get_posts( [ 'post_type' => 'accommodation', 'numberposts' => -1 ] );
		?>
		<div class="wrap">
			<h1><?php _e( 'Dynamic Pricing Rules', 'resort-manager' ); ?></h1>
			<p class="description"><?php _e( 'Set up seasonal rates or weekend surcharges to maximize your revenue. Pricing rules override the base accommodation price for the specified date range. Rules with higher priority take precedence in case of overlaps.', 'resort-manager' ); ?></p>

			<div class="postbox" style="padding: 20px;">
				<h3><?php _e( 'Add New Pricing Rule', 'resort-manager' ); ?></h3>
				<form method="post">
					<?php wp_nonce_field( 'add_pricing_rule' ); ?>
					<table class="form-table">
						<tr>
							<th><label for="room_id"><?php _e( 'Accommodation', 'resort-manager' ); ?></label></th>
							<td>
								<select name="room_id" id="room_id">
									<?php foreach ( $rooms as $room ) : ?>
										<option value="<?php echo $room->ID; ?>"><?php echo esc_html( $room->post_title ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="start_date"><?php _e( 'Start Date', 'resort-manager' ); ?></label></th>
							<td><input type="date" name="start_date" id="start_date" required></td>
						</tr>
						<tr>
							<th><label for="end_date"><?php _e( 'End Date', 'resort-manager' ); ?></label></th>
							<td><input type="date" name="end_date" id="end_date" required></td>
						</tr>
						<tr>
							<th><label for="price_modifier"><?php _e( 'Modifier', 'resort-manager' ); ?></label></th>
							<td>
								<input type="number" name="price_modifier" id="price_modifier" step="0.01" required>
								<select name="modifier_type">
									<option value="fixed"><?php _e( 'Fixed Amount (+/-)', 'resort-manager' ); ?></option>
									<option value="percentage"><?php _e( 'Percentage (+/- %)', 'resort-manager' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="priority"><?php _e( 'Priority', 'resort-manager' ); ?></label></th>
							<td><input type="number" name="priority" id="priority" value="0"></td>
						</tr>
					</table>
					<input type="submit" name="add_rule" class="button button-primary" value="<?php _e( 'Add Rule', 'resort-manager' ); ?>">
				</form>
			</div>

			<h3><?php _e( 'Existing Rules', 'resort-manager' ); ?></h3>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Accommodation', 'resort-manager' ); ?></th>
						<th><?php _e( 'Dates', 'resort-manager' ); ?></th>
						<th><?php _e( 'Modifier', 'resort-manager' ); ?></th>
						<th><?php _e( 'Priority', 'resort-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rules as $rule ) :
						$room = get_post( $rule->room_id );
						?>
						<tr>
							<td><?php echo $room ? esc_html( $room->post_title ) : 'Deleted'; ?></td>
							<td><?php echo $rule->start_date; ?> to <?php echo $rule->end_date; ?></td>
							<td><?php echo $rule->price_modifier; ?> (<?php echo $rule->modifier_type; ?>)</td>
							<td><?php echo $rule->priority; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
