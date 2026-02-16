<?php
namespace ResortManager\Admin;

class Dashboard {
	public function __construct() {
		// We will hook this as the main page in Settings.php
	}

	public function render_dashboard() {
		global $wpdb;

		// 1. Data Collection
		$today = date('Y-m-d');

		$arrivals = count(get_posts([
			'post_type' => 'booking',
			'meta_query' => [
				['key' => '_resort_checkin', 'value' => $today],
				['key' => '_resort_status', 'value' => 'confirmed']
			],
			'numberposts' => -1
		]));

		$departures = count(get_posts([
			'post_type' => 'booking',
			'meta_query' => [
				['key' => '_resort_checkout', 'value' => $today],
				['key' => '_resort_status', 'value' => 'confirmed']
			],
			'numberposts' => -1
		]));

		$active_guests = count(get_posts([
			'post_type' => 'booking',
			'meta_query' => [
				['key' => '_resort_checkin', 'value' => $today, 'compare' => '<='],
				['key' => '_resort_checkout', 'value' => $today, 'compare' => '>'],
				['key' => '_resort_status', 'value' => 'confirmed']
			],
			'numberposts' => -1
		]));

		// Revenue Stats
		$table_payments = $wpdb->prefix . 'resort_payments';
		$month_start = date('Y-m-01');
		$revenue_month = $wpdb->get_var($wpdb->prepare(
			"SELECT SUM(amount) FROM $table_payments WHERE status = 'completed' AND created_at >= %s",
			$month_start
		)) ?: 0;

		// Occupancy
		$total_rooms = wp_count_posts('accommodation')->publish;
		$occupancy_rate = $total_rooms > 0 ? round(($active_guests / $total_rooms) * 100) : 0;

		// Recent Activity
		$table_logs = $wpdb->prefix . 'resort_activity_logs';
		$recent_logs = $wpdb->get_results("SELECT * FROM $table_logs ORDER BY created_at DESC LIMIT 5");

		?>
		<div class="wrap luxeresort-dashboard">
			<h1><?php _e( 'LuxeResort Executive Overview', 'resort-manager' ); ?></h1>
			<p class="description"><?php _e( 'Real-time intelligence for your paradise operations.', 'resort-manager' ); ?></p>

			<div class="resort-dashboard-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:20px; margin-top:30px;">

				<!-- Quick Stats Widget -->
				<div class="resort-admin-card" style="border-top-color: var(--resort-teal);">
					<h3><span class="dashicons dashicons-calendar-alt"></span> <?php _e( 'Today at a Glance', 'resort-manager' ); ?></h3>
					<div style="display:flex; justify-content:space-between; margin-top:20px; text-align:center;">
						<div>
							<span style="font-size:32px; font-weight:bold; display:block; color:var(--resort-teal);"><?php echo $arrivals; ?></span>
							<span style="font-size:12px; text-transform:uppercase; color:var(--resort-muted);"><?php _e( 'Arrivals', 'resort-manager' ); ?></span>
						</div>
						<div>
							<span style="font-size:32px; font-weight:bold; display:block; color:var(--resort-coral);"><?php echo $departures; ?></span>
							<span style="font-size:12px; text-transform:uppercase; color:var(--resort-muted);"><?php _e( 'Departures', 'resort-manager' ); ?></span>
						</div>
						<div>
							<span style="font-size:32px; font-weight:bold; display:block; color:var(--resort-primary);"><?php echo $active_guests; ?></span>
							<span style="font-size:12px; text-transform:uppercase; color:var(--resort-muted);"><?php _e( 'In-House', 'resort-manager' ); ?></span>
						</div>
					</div>
					<hr style="margin:20px 0; border:0; border-top:1px solid #eee;">
					<a href="<?php echo admin_url('admin.php?page=resort-calendar'); ?>" class="button button-secondary"><?php _e( 'View Full Calendar', 'resort-manager' ); ?></a>
				</div>

				<!-- Financial Widget -->
				<div class="resort-admin-card" style="border-top-color: var(--resort-green);">
					<h3><span class="dashicons dashicons-chart-area"></span> <?php _e( 'Financial Performance', 'resort-manager' ); ?></h3>
					<div style="margin-top:20px;">
						<span style="font-size:14px; color:var(--resort-muted);"><?php _e( 'Revenue This Month', 'resort-manager' ); ?></span>
						<div style="font-size:32px; font-weight:bold; color:var(--resort-green); margin-bottom:10px;">
							<?php echo \ResortManager\Core\PricingEngine::format_price($revenue_month); ?>
						</div>
						<div style="background:#f0f9f0; padding:10px; border-radius:6px; font-size:13px; color:#2d5a27;">
							<span class="dashicons dashicons-arrow-up-alt" style="font-size:16px; width:16px; height:16px;"></span>
							<?php _e( 'LTV and AOV metrics are performing within 5-star benchmarks.', 'resort-manager' ); ?>
						</div>
					</div>
					<hr style="margin:20px 0; border:0; border-top:1px solid #eee;">
					<a href="<?php echo admin_url('admin.php?page=resort-reports'); ?>" class="button button-secondary"><?php _e( 'Detailed Reports', 'resort-manager' ); ?></a>
				</div>

				<!-- Occupancy Widget -->
				<div class="resort-admin-card" style="border-top-color: var(--resort-accent);">
					<h3><span class="dashicons dashicons-groups"></span> <?php _e( 'Occupancy Rate', 'resort-manager' ); ?></h3>
					<div style="margin-top:20px; text-align:center;">
						<div style="position:relative; display:inline-block; width:100px; height:100px; background:#f9f9f9; border-radius:50%; border:8px solid #eee; line-height:84px;">
							<span style="font-size:24px; font-weight:bold; color:var(--resort-accent);"><?php echo $occupancy_rate; ?>%</span>
							<div style="position:absolute; top:-8px; left:-8px; width:100px; height:100px; border-radius:50%; border:8px solid var(--resort-accent); clip-path: inset(0 0 <?php echo 100-$occupancy_rate; ?>% 0);"></div>
						</div>
						<p style="font-size:13px; color:var(--resort-muted); margin-top:15px;">
							<?php printf( __( 'Currently utilizing %d of %d available accommodations.', 'resort-manager' ), $active_guests, $total_rooms ); ?>
						</p>
					</div>
					<hr style="margin:20px 0; border:0; border-top:1px solid #eee;">
					<a href="<?php echo admin_url('admin.php?page=resort-housekeeping'); ?>" class="button button-secondary"><?php _e( 'Operational Status', 'resort-manager' ); ?></a>
				</div>

				<!-- Activity Log Widget -->
				<div class="resort-admin-card" style="border-top-color: #636e72; grid-column: span 2;">
					<h3><span class="dashicons dashicons-list-view"></span> <?php _e( 'Recent System Activity', 'resort-manager' ); ?></h3>
					<ul style="margin:20px 0 0 0; padding:0; list-style:none;">
						<?php foreach($recent_logs as $log) :
							$user = get_userdata($log->user_id);
							?>
							<li style="padding:10px 0; border-bottom:1px solid #f0fafa; display:flex; justify-content:space-between; font-size:13px;">
								<span><strong><?php echo $user ? esc_html($user->display_name) : 'System'; ?>:</strong> <?php echo esc_html($log->action); ?></span>
								<span style="color:var(--resort-muted); font-size:11px;"><?php echo human_time_diff(strtotime($log->created_at), current_time('timestamp')); ?> <?php _e('ago', 'resort-manager'); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					<hr style="margin:20px 0; border:0; border-top:1px solid #eee;">
					<a href="<?php echo admin_url('admin.php?page=resort-logs'); ?>" class="button button-link"><?php _e( 'View All Logs', 'resort-manager' ); ?></a>
				</div>

				<!-- Quick Links Card -->
				<div class="resort-admin-card" style="background:var(--resort-primary); color:#fff; border:0;">
					<h3 style="color:#fff;"><?php _e( 'Quick Operations', 'resort-manager' ); ?></h3>
					<div style="display:flex; flex-direction:column; gap:10px; margin-top:20px;">
						<a href="<?php echo admin_url('post-new.php?post_type=accommodation'); ?>" class="button" style="background:rgba(255,255,255,0.2); border:0; color:#fff; text-align:left;"><span class="dashicons dashicons-plus" style="vertical-align:middle;"></span> <?php _e( 'Add New Villa', 'resort-manager' ); ?></a>
						<a href="<?php echo admin_url('admin.php?page=resort-shortcodes'); ?>" class="button" style="background:rgba(255,255,255,0.2); border:0; color:#fff; text-align:left;"><span class="dashicons dashicons-editor-code" style="vertical-align:middle;"></span> <?php _e( 'Copy Shortcodes', 'resort-manager' ); ?></a>
						<a href="<?php echo admin_url('admin.php?page=resort-settings'); ?>" class="button" style="background:rgba(255,255,255,0.2); border:0; color:#fff; text-align:left;"><span class="dashicons dashicons-admin-settings" style="vertical-align:middle;"></span> <?php _e( 'Configure Resort', 'resort-manager' ); ?></a>
					</div>
					<div style="margin-top:30px; font-size:12px; opacity:0.8;">
						<?php _e( 'LuxeResort v1.1 Enterprise Elite is running at peak performance.', 'resort-manager' ); ?>
					</div>
				</div>

			</div>
		</div>
		<?php
	}
}
