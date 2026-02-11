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
		<div class="resort-widget-content" style="background: linear-gradient(135deg, #008080 0%, #004d4d 100%); color: #fff; padding: 20px; border-radius: 12px; margin: -12px;">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
				<h3 style="margin:0; color:#fff; font-family:'Playfair Display', serif;"><?php _e( 'Your Paradise', 'resort-manager' ); ?></h3>
				<span class="dashicons dashicons-palmtree" style="font-size:30px; width:30px; height:30px;"></span>
			</div>
			<div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:15px;">
				<div style="background:rgba(255,255,255,0.1); padding:10px; border-radius:8px; text-align:center;">
					<span style="font-size:24px; font-weight:700; display:block;"><?php echo $booked_today; ?></span>
					<small style="text-transform:uppercase; font-size:9px; opacity:0.8;"><?php _e( 'Occupied', 'resort-manager' ); ?></small>
				</div>
				<div style="background:rgba(255,255,255,0.1); padding:10px; border-radius:8px; text-align:center;">
					<span style="font-size:24px; font-weight:700; display:block;"><?php echo $pending_count; ?></span>
					<small style="text-transform:uppercase; font-size:9px; opacity:0.8;"><?php _e( 'Pending', 'resort-manager' ); ?></small>
				</div>
			</div>
			<div style="display:flex; gap:10px;">
				<a href="<?php echo admin_url('admin.php?page=resort-calendar'); ?>" class="button button-small" style="background:#fff; color:#008080; border:none; flex:1; text-align:center;"><?php _e( 'Calendar', 'resort-manager' ); ?></a>
				<a href="<?php echo admin_url('admin.php?page=resort-reports'); ?>" class="button button-small" style="background:#FF7F50; color:#fff; border:none; flex:1; text-align:center;"><?php _e( 'Reports', 'resort-manager' ); ?></a>
			</div>
		</div>
		<?php
	}
}
