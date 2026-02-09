<?php
namespace ResortManager\Admin;

class Reports {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_reports_page' ] );
	}

	public function add_reports_page() {
		add_submenu_page(
			'resort-manager',
			__( 'Revenue Reports', 'resort-manager' ),
			__( 'Reports', 'resort-manager' ),
			'manage_options',
			'resort-reports',
			[ $this, 'render_reports_page' ]
		);
	}

	public function render_reports_page() {
		if ( isset( $_POST['resort_cleanup_abandoned'] ) && check_admin_referer( 'resort_cleanup_nonce' ) ) {
			$cleaned = \ResortManager\Admin\Maintenance::cleanup_abandoned_bookings();
			echo '<div class="updated"><p>' . sprintf( __( '%d abandoned bookings have been cleaned up and inventory released.', 'resort-manager' ), $cleaned ) . '</p></div>';
		}

		$bookings = get_posts( [
			'post_type'   => 'booking',
			'numberposts' => -1,
			'post_status' => 'publish',
		] );

		$total_revenue = 0;
		$total_bookings = count( $bookings );
		$confirmed_bookings = 0;

		foreach ( $bookings as $booking ) {
			$status = get_post_meta( $booking->ID, '_resort_status', true );
			if ( 'confirmed' === $status ) {
				$total_revenue += floatval( get_post_meta( $booking->ID, '_resort_total_price', true ) );
				$confirmed_bookings++;
			}
		}

		$rooms_count = wp_count_posts( 'accommodation' )->publish;
		$occupancy_rate = 0;
		if ( $rooms_count > 0 ) {
			// Simple calculation for today's occupancy
			global $wpdb;
			$today = date( 'Y-m-d' );
			$table_availability = $wpdb->prefix . 'resort_availability';
			$booked_today = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(DISTINCT room_id) FROM $table_availability WHERE date = %s AND status = 'booked'",
				$today
			) );
			$occupancy_rate = ( $booked_today / $rooms_count ) * 100;
		}

		?>
		<div class="wrap">
			<h1><?php _e( 'Resort Analytics & Reports', 'resort-manager' ); ?></h1>

			<div class="resort-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
				<div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
					<h3><?php _e( 'Total Revenue', 'resort-manager' ); ?></h3>
					<p style="font-size: 24px; font-weight: bold; color: #c5a059;">$<?php echo number_format( $total_revenue, 2 ); ?></p>
					<p><small><?php _e( 'From confirmed bookings', 'resort-manager' ); ?></small></p>
				</div>
				<div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
					<h3><?php _e( 'Total Bookings', 'resort-manager' ); ?></h3>
					<p style="font-size: 24px; font-weight: bold;"><?php echo $total_bookings; ?></p>
					<p><small><?php echo $confirmed_bookings; ?> <?php _e( 'confirmed', 'resort-manager' ); ?></small></p>
				</div>
				<div class="stat-card" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
					<h3><?php _e( 'Occupancy Rate', 'resort-manager' ); ?></h3>
					<p style="font-size: 24px; font-weight: bold;"><?php echo round( $occupancy_rate, 1 ); ?>%</p>
					<p><small><?php _e( 'For today', 'resort-manager' ); ?></small></p>
				</div>
			</div>

			<div style="margin-top: 40px; background: #fff; padding: 20px; border: 1px solid #ccd0d4;">
				<h3><?php _e( 'Inventory Maintenance', 'resort-manager' ); ?></h3>
				<p><?php _e( 'Pending bookings older than 30 minutes are considered abandoned. Clean them up to release room availability.', 'resort-manager' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'resort_cleanup_nonce' ); ?>
					<button type="submit" name="resort_cleanup_abandoned" class="button button-secondary"><?php _e( 'Clean Up Abandoned Bookings', 'resort-manager' ); ?></button>
				</form>
			</div>

			<h2 style="margin-top: 40px;"><?php _e( 'Recent Bookings', 'resort-manager' ); ?></h2>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Guest', 'resort-manager' ); ?></th>
						<th><?php _e( 'Dates', 'resort-manager' ); ?></th>
						<th><?php _e( 'Total', 'resort-manager' ); ?></th>
						<th><?php _e( 'Status', 'resort-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( array_slice( $bookings, 0, 10 ) as $booking ) :
						$price = get_post_meta( $booking->ID, '_resort_total_price', true );
						$status = get_post_meta( $booking->ID, '_resort_status', true );
						$checkin = get_post_meta( $booking->ID, '_resort_checkin', true );
						$checkout = get_post_meta( $booking->ID, '_resort_checkout', true );
						?>
						<tr>
							<td><strong><?php echo esc_html( $booking->post_title ); ?></strong></td>
							<td><?php echo esc_html( $checkin ); ?> to <?php echo esc_html( $checkout ); ?></td>
							<td>$<?php echo number_format( floatval($price), 2 ); ?></td>
							<td><?php echo esc_html( ucfirst( $status ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
