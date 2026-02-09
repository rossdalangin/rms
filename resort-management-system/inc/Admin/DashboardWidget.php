<?php
namespace ResortManager\Admin;

class DashboardWidget {
	public function __construct() {
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
	}

	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'resort_manager_status_widget',
			__( 'LuxeResort Status', 'resort-manager' ),
			[ $this, 'render_widget' ]
		);
	}

	public function render_widget() {
		global $wpdb;
		$today = date( 'Y-m-d' );
		$table_availability = $wpdb->prefix . 'resort_availability';

		$booked_today = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(DISTINCT room_id) FROM $table_availability WHERE date = %s AND status = 'booked'",
			$today
		) );

		$pending_count = count( get_posts( [
			'post_type'  => 'booking',
			'meta_key'   => '_resort_status',
			'meta_value' => 'pending',
			'numberposts' => -1
		] ) );

		?>
		<div class="resort-widget-content">
			<p><strong><?php _e( 'Occupied Today:', 'resort-manager' ); ?></strong> <?php echo $booked_today; ?> <?php _e( 'Rooms', 'resort-manager' ); ?></p>
			<p><strong><?php _e( 'Pending Bookings:', 'resort-manager' ); ?></strong> <?php echo $pending_count; ?></p>
			<hr>
			<ul class="subsubsub" style="float:none;">
				<li><a href="<?php echo admin_url('admin.php?page=resort-reports'); ?>"><?php _e( 'View Reports', 'resort-manager' ); ?></a> |</li>
				<li><a href="<?php echo admin_url('admin.php?page=resort-calendar'); ?>"><?php _e( 'Calendar', 'resort-manager' ); ?></a></li>
			</ul>
		</div>
		<?php
	}
}
