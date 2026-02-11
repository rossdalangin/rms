<?php
namespace ResortManager\Admin;

class Waivers {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_waivers_page' ] );
	}

	public function add_waivers_page() {
		add_submenu_page(
			'resort-manager',
			__( 'Guest Waivers', 'resort-manager' ),
			__( 'Waivers', 'resort-manager' ),
			'edit_posts',
			'resort-waivers',
			[ $this, 'render_waivers_page' ]
		);
	}

	public function render_waivers_page() {
		$waivers = get_posts( [
			'post_type'    => 'booking',
			'numberposts'  => -1,
			'meta_key'     => '_resort_digital_waiver',
			'meta_value'   => 'accepted'
		] );

		?>
		<div class="wrap">
			<h1><?php _e( 'Digital Guest Waivers', 'resort-manager' ); ?></h1>
			<p class="description"><?php _e( 'List of guests who have accepted the digital liability waiver during booking.', 'resort-manager' ); ?></p>

			<table class="wp-list-table widefat fixed striped" style="margin-top:20px;">
				<thead>
					<tr>
						<th><?php _e( 'Guest Name', 'resort-manager' ); ?></th>
						<th><?php _e( 'Booking ID', 'resort-manager' ); ?></th>
						<th><?php _e( 'Accepted Date', 'resort-manager' ); ?></th>
						<th><?php _e( 'Actions', 'resort-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $waivers ) ) : ?>
						<tr><td colspan="4"><?php _e( 'No waivers found.', 'resort-manager' ); ?></td></tr>
					<?php else : ?>
						<?php foreach ( $waivers as $booking ) :
							$fname = get_post_meta( $booking->ID, '_resort_first_name', true );
							$lname = get_post_meta( $booking->ID, '_resort_last_name', true );
							?>
							<tr>
								<td><strong><?php echo esc_html( "$fname $lname" ); ?></strong></td>
								<td>#<?php echo $booking->ID; ?></td>
								<td><?php echo get_the_date( 'Y-m-d H:i', $booking->ID ); ?></td>
								<td>
									<a href="<?php echo admin_url( 'post.php?post=' . $booking->ID . '&action=edit' ); ?>" class="button button-small"><?php _e( 'View Booking', 'resort-manager' ); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
