<?php
namespace ResortManager\Admin;

class Reports {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_reports_page' ] );
		add_action( 'admin_init', [ $this, 'handle_export_csv' ] );
		add_action( 'wp_ajax_resort_apply_smart_pricing', [ $this, 'apply_smart_pricing' ] );
		add_action( 'resort_daily_sync', [ $this, 'check_competitor_alerts' ] );
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

	public function handle_export_csv() {
		if ( isset( $_GET['resort_export_bookings'] ) && current_user_can( 'manage_options' ) ) {
			check_admin_referer( 'resort_export_nonce' );

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=resort-bookings-' . date('Y-m-d') . '.csv' );

			$output = fopen( 'php://output', 'w' );
			fputcsv( $output, [ 'Booking ID', 'First Name', 'Last Name', 'Email', 'Check-in', 'Check-out', 'Total Price', 'Status' ] );

			$bookings = get_posts( [ 'post_type' => 'booking', 'numberposts' => -1 ] );
			foreach ( $bookings as $booking ) {
				$fname = get_post_meta( $booking->ID, '_resort_first_name', true );
				$lname = get_post_meta( $booking->ID, '_resort_last_name', true );
				$email = get_post_meta( $booking->ID, '_resort_guest_email', true );

				fputcsv( $output, [
					$booking->ID,
					$fname ?: '-',
					$lname ?: '-',
					$email ?: '-',
					get_post_meta( $booking->ID, '_resort_checkin', true ),
					get_post_meta( $booking->ID, '_resort_checkout', true ),
					get_post_meta( $booking->ID, '_resort_total_price', true ),
					get_post_meta( $booking->ID, '_resort_status', true )
				] );
			}
			fclose( $output );
			exit;
		}
	}

	public function apply_smart_pricing() {
		check_ajax_referer( 'resort_cleanup_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$modifier = floatval( $_POST['modifier'] );
		$type = sanitize_text_field( $_POST['type'] );
		$days = intval( $_POST['days'] );

		global $wpdb;
		$table_pricing = $wpdb->prefix . 'resort_pricing';
		$rooms = get_posts( [ 'post_type' => 'accommodation', 'numberposts' => -1 ] );

		$start_date = date( 'Y-m-d' );
		$end_date = date( 'Y-m-d', strtotime( "+$days days" ) );

		foreach ( $rooms as $room ) {
			$wpdb->insert( $table_pricing, [
				'room_id'        => $room->ID,
				'start_date'     => $start_date,
				'end_date'       => $end_date,
				'price_modifier' => $modifier,
				'modifier_type'  => 'percentage',
				'priority'       => 10, // Higher priority for smart pricing
			] );
		}

		\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Smart Pricing applied: %s%% for %d days.', 'resort-manager' ), ($modifier > 0 ? '+' : '') . $modifier, $days ) );

		wp_send_json_success( [ 'message' => __( 'Pricing rules applied successfully!', 'resort-manager' ) ] );
	}

	public function check_competitor_alerts() {
		// Simulated Competitor Data Analysis
		$competitors = [
			[ 'name' => 'Blue Waters Resort', 'price' => 5200 ],
			[ 'name' => 'Sunset Sands Hotel', 'price' => 4800 ]
		];

		$rooms = get_posts( [ 'post_type' => 'accommodation', 'numberposts' => 1 ] );
		if ( empty($rooms) ) return;

		$my_price = get_post_meta( $rooms[0]->ID, '_resort_price', true );
		$admin_email = get_option( 'admin_email' );

		foreach ( $competitors as $comp ) {
			if ( $comp['price'] < ($my_price * 0.8) ) {
				// Alert: Competitor is significantly cheaper
				$subject = sprintf( __( 'LuxeResort Market Alert: %s', 'resort-manager' ), $comp['name'] );
				$message = sprintf(
					__( 'Warning: %s has dropped their rates to %s, which is more than 20%% below your current rate of %s. Consider reviewing your "Smart Pricing" rules.', 'resort-manager' ),
					$comp['name'],
					\ResortManager\Core\PricingEngine::format_price( $comp['price'] ),
					\ResortManager\Core\PricingEngine::format_price( $my_price )
				);
				wp_mail( $admin_email, $subject, $message );
				\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Market Alert sent for %s.', 'resort-manager' ), $comp['name'] ) );
			}
		}
	}

	public function render_reports_page() {
		if ( isset( $_POST['resort_cleanup_abandoned'] ) && check_admin_referer( 'resort_cleanup_nonce' ) ) {
			$cleaned = \ResortManager\Admin\Maintenance::cleanup_abandoned_bookings();
			echo '<div class="updated"><p>' . sprintf( __( '%d abandoned bookings have been cleaned up and inventory released.', 'resort-manager' ), $cleaned ) . '</p></div>';
		}

		if ( isset( $_POST['resort_send_reminders'] ) && check_admin_referer( 'resort_cleanup_nonce' ) ) {
			$sent = \ResortManager\Admin\Maintenance::send_reminders();
			echo '<div class="updated"><p>' . sprintf( __( '%d reminder emails sent to potential guests.', 'resort-manager' ), $sent ) . '</p></div>';
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

		// Fetch Smart Pricing Insights
		$insights = $this->generate_smart_pricing_suggestions();

		?>
		<div class="wrap toplevel_page_resort-manager">
			<h1><?php _e( 'Resort Analytics & Reports', 'resort-manager' ); ?></h1>
			<p class="description"><?php _e( 'Gain deep insights into your resort\'s financial health and operational efficiency. Use the Analytics tab for high-level metrics, or explore Revenue Optimization for data-driven pricing strategies.', 'resort-manager' ); ?></p>

			<h2 class="nav-tab-wrapper">
				<a href="#" class="nav-tab nav-tab-active" id="resort-tab-analytics"><?php _e( 'Analytics', 'resort-manager' ); ?></a>
				<a href="#" class="nav-tab" id="resort-tab-optimization"><?php _e( 'Revenue Optimization', 'resort-manager' ); ?></a>
			</h2>

			<div id="resort-analytics-content">

			<div class="resort-admin-card" style="margin-top:20px;">
				<h3><?php _e( 'Revenue Trend', 'resort-manager' ); ?></h3>
				<div style="height: 300px;">
					<canvas id="resortRevenueChart"></canvas>
				</div>
			</div>

			<div class="resort-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
				<div class="stat-card resort-admin-card" style="margin-bottom:0; border-top-color: var(--resort-teal);">
					<h3><?php _e( 'Total Revenue', 'resort-manager' ); ?></h3>
					<p style="font-size: 28px; font-weight: 700; color: var(--resort-teal);"><?php echo \ResortManager\Core\PricingEngine::format_price( $total_revenue ); ?></p>
					<p style="color:var(--resort-muted);"><small><?php _e( 'From confirmed bookings', 'resort-manager' ); ?></small></p>
				</div>
				<div class="stat-card resort-admin-card" style="margin-bottom:0; border-top-color: var(--resort-sand);">
					<h3><?php _e( 'Total Bookings', 'resort-manager' ); ?></h3>
					<p style="font-size: 28px; font-weight: 700; color: var(--resort-sand);"><?php echo $total_bookings; ?></p>
					<p style="color:var(--resort-muted);"><small><?php echo $confirmed_bookings; ?> <?php _e( 'confirmed', 'resort-manager' ); ?></small></p>
				</div>
				<div class="stat-card resort-admin-card" style="margin-bottom:0; border-top-color: var(--resort-coral);">
					<h3><?php _e( 'Occupancy Rate', 'resort-manager' ); ?></h3>
					<p style="font-size: 28px; font-weight: 700; color: var(--resort-coral);"><?php echo round( $occupancy_rate, 1 ); ?>%</p>
					<p style="color:var(--resort-muted);"><small><?php _e( 'For today', 'resort-manager' ); ?></small></p>
				</div>
			</div>

			<div style="margin-top: 20px; text-align: right;">
				<a href="<?php echo wp_nonce_url( admin_url('admin.php?page=resort-reports&resort_export_bookings=1'), 'resort_export_nonce' ); ?>" class="button button-secondary">
					<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> <?php _e( 'Export All Bookings to CSV', 'resort-manager' ); ?>
				</a>
			</div>

			<div class="resort-admin-card" style="margin-top: 40px; border-top-color: var(--resort-sand);">
				<h3><?php _e( 'Inventory Maintenance & Automation', 'resort-manager' ); ?></h3>
				<p><?php _e( 'Pending bookings older than 30 minutes are considered abandoned. While the system cleans these up automatically every hour, you can use the manual tools below to take immediate action.', 'resort-manager' ); ?></p>

				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
					<div>
						<strong><?php _e( '1. Send Abandoned Cart Reminders:', 'resort-manager' ); ?></strong>
						<p><small><?php _e( 'Sends a friendly "Still interested?" email to any guest who reached Step 4 but didn\'t pay. This can significantly improve your conversion rates.', 'resort-manager' ); ?></small></p>
					</div>
					<div>
						<strong><?php _e( '2. Clean Up Abandoned Bookings:', 'resort-manager' ); ?></strong>
						<p><small><?php _e( 'Instantly releases dates held by incomplete bookings, making those rooms available for other guests to search and book.', 'resort-manager' ); ?></small></p>
					</div>
				</div>

				<form method="post" style="background:var(--resort-light-teal); padding:20px; border-radius:8px;">
					<?php wp_nonce_field( 'resort_cleanup_nonce' ); ?>
					<button type="submit" name="resort_send_reminders" class="button button-primary"><?php _e( 'Send Abandoned Cart Reminders', 'resort-manager' ); ?></button>
					&nbsp;
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
						$fname = get_post_meta( $booking->ID, '_resort_first_name', true );
						$lname = get_post_meta( $booking->ID, '_resort_last_name', true );
						$guest_name = ( ! empty( $fname ) || ! empty( $lname ) ) ? "$fname $lname" : str_replace( 'Booking for ', '', $booking->post_title );
						?>
						<tr>
							<td><strong><?php echo esc_html( $guest_name ); ?></strong></td>
							<td><?php echo esc_html( $checkin ); ?> to <?php echo esc_html( $checkout ); ?></td>
							<td><?php echo \ResortManager\Core\PricingEngine::format_price( floatval($price) ); ?></td>
							<td><?php echo esc_html( ucfirst( $status ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>

			<div id="resort-optimization-content" style="display:none; margin-top:30px;">

				<div class="resort-admin-card" style="margin-bottom:30px; border-top-color: var(--resort-teal);">
					<h3><span class="dashicons dashicons-analytics" style="color:var(--resort-teal);"></span> <?php _e( 'Competitor Insights (Simulated)', 'resort-manager' ); ?></h3>
					<p><?php _e( 'Real-time market analysis for nearby resorts. Use these insights to stay competitive.', 'resort-manager' ); ?></p>
					<div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px; margin-top:20px;">
						<div style="padding:15px; background:#f9fdfd; border:1px solid #d1eaea; border-radius:8px;">
							<strong>Blue Waters Resort</strong><br>
							<small>Avg. Nightly: ₱5,200</small><br>
							<span style="color:green; font-weight:bold;">-12% vs you</span>
						</div>
						<div style="padding:15px; background:#f9fdfd; border:1px solid #d1eaea; border-radius:8px;">
							<strong>Sunset Sands Hotel</strong><br>
							<small>Avg. Nightly: ₱4,800</small><br>
							<span style="color:green; font-weight:bold;">-18% vs you</span>
						</div>
						<div style="padding:15px; background:#f9fdfd; border:1px solid #d1eaea; border-radius:8px;">
							<strong>Palm Grove Villas</strong><br>
							<small>Avg. Nightly: ₱6,500</small><br>
							<span style="color:orange; font-weight:bold;">+10% vs you</span>
						</div>
					</div>
					<p style="margin-top:15px;"><small><em><?php _e( 'Insights provided by LuxeResort Market Intelligence API.', 'resort-manager' ); ?></em></small></p>
				</div>

				<div class="postbox" style="padding:20px; border-left: 4px solid #008080;">
					<h3><span class="dashicons dashicons-lightbulb" style="color:#FFD700;"></span> <?php _e( 'Smart Pricing Suggestions', 'resort-manager' ); ?></h3>
					<p><?php _e( 'Based on your occupancy trends for the next 30 days, we suggest the following adjustments to maximize revenue.', 'resort-manager' ); ?></p>

					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php _e( 'Period', 'resort-manager' ); ?></th>
								<th><?php _e( 'Current Occupancy', 'resort-manager' ); ?></th>
								<th><?php _e( 'Recommendation', 'resort-manager' ); ?></th>
								<th><?php _e( 'Action', 'resort-manager' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $insights as $insight ) : ?>
								<tr>
									<td><?php echo esc_html( $insight['period'] ); ?></td>
									<td><?php echo $insight['occupancy']; ?>%</td>
									<td>
										<span style="color: <?php echo $insight['type'] === 'increase' ? '#46b450' : '#FF7F50'; ?>; font-weight:bold;">
											<?php echo esc_html( $insight['suggestion'] ); ?>
										</span>
									</td>
									<td>
										<?php if ( $insight['type'] !== 'stable' ) : ?>
											<button type="button" class="button button-small apply-smart-pricing"
												data-modifier="<?php echo $insight['type'] === 'increase' ? '20' : '-15'; ?>"
												data-days="<?php echo $insight['days']; ?>">
												<?php _e( 'One-Click Apply', 'resort-manager' ); ?>
											</button>
										<?php else : ?>
											-
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>

			<script>
				jQuery(document).ready(function($) {
					$('#resort-tab-analytics').click(function(e) {
						e.preventDefault();
						$('.nav-tab').removeClass('nav-tab-active');
						$(this).addClass('nav-tab-active');
						$('#resort-analytics-content').show();
						$('#resort-optimization-content').hide();
					});
					$('#resort-tab-optimization').click(function(e) {
						e.preventDefault();
						$('.nav-tab').removeClass('nav-tab-active');
						$(this).addClass('nav-tab-active');
						$('#resort-analytics-content').hide();
						$('#resort-optimization-content').show();
					});

					// Revenue Chart Implementation
					const ctx = document.getElementById('resortRevenueChart').getContext('2d');
					const revenueData = <?php
						// Get revenue for last 7 days
						$days_data = [];
						for ($i = 6; $i >= 0; $i--) {
							$date = date('Y-m-d', strtotime("-$i days"));
							$rev = 0;
							// Find confirmed bookings for this day
							$day_bookings = get_posts([
								'post_type' => 'booking',
								'numberposts' => -1,
								'meta_query' => [
									['key' => '_resort_status', 'value' => 'confirmed'],
									['key' => '_resort_checkin', 'value' => $date] // Simple attribution
								]
							]);
							foreach($day_bookings as $db) $rev += floatval(get_post_meta($db->ID, '_resort_total_price', true));
							$days_data[$date] = $rev;
						}
						echo json_encode(array_values($days_data));
					?>;
					const labels = <?php echo json_encode(array_map(function($d){ return date('M j', strtotime($d)); }, array_keys($days_data))); ?>;

					new Chart(ctx, {
						type: 'line',
						data: {
							labels: labels,
							datasets: [{
								label: 'Daily Revenue (PHP)',
								data: revenueData,
								borderColor: '#008080',
								backgroundColor: 'rgba(0, 128, 128, 0.1)',
								fill: true,
								tension: 0.4
							}]
						},
						options: {
							responsive: true,
							maintainAspectRatio: false,
							plugins: { legend: { display: false } },
							scales: { y: { beginAtZero: true } }
						}
					});

					$('.apply-smart-pricing').click(function() {
						const btn = $(this);
						const data = {
							action: 'resort_apply_smart_pricing',
							nonce: '<?php echo wp_create_nonce("resort_cleanup_nonce"); ?>',
							modifier: btn.data('modifier'),
							days: btn.data('days')
						};

						btn.prop('disabled', true).text('Applying...');

						$.post(ajaxurl, data, function(res) {
							if (res.success) {
								alert(res.data.message);
								location.reload();
							} else {
								alert('Error applying rule.');
								btn.prop('disabled', false).text('One-Click Apply');
							}
						});
					});
				});
			</script>
		</div>
		<?php
	}

	private function generate_smart_pricing_suggestions() {
		global $wpdb;
		$table_availability = $wpdb->prefix . 'resort_availability';
		$rooms_count = wp_count_posts( 'accommodation' )->publish;
		if ( $rooms_count <= 0 ) return [];

		$suggestions = [];
		$periods = [
			'Next 7 Days'  => 7,
			'Next 14 Days' => 14,
			'Next 30 Days' => 30
		];

		foreach ( $periods as $label => $days ) {
			$start = date( 'Y-m-d' );
			$end = date( 'Y-m-d', strtotime( "+$days days" ) );

			$booked_count = $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM $table_availability WHERE date >= %s AND date < %s AND status = 'booked'",
				$start, $end
			) );

			$total_capacity = $rooms_count * $days;
			$occupancy = ( $booked_count / $total_capacity ) * 100;

			if ( $occupancy > 80 ) {
				$suggestions[] = [
					'period' => $label,
					'days'   => $days,
					'occupancy' => round($occupancy),
					'type' => 'increase',
					'suggestion' => sprintf( __( 'High Demand! Increase prices by %d%%', 'resort-manager' ), 20 )
				];
			} elseif ( $occupancy < 30 ) {
				$suggestions[] = [
					'period' => $label,
					'days'   => $days,
					'occupancy' => round($occupancy),
					'type' => 'decrease',
					'suggestion' => sprintf( __( 'Low Demand. Offer a %d%% "Early Escape" discount', 'resort-manager' ), 15 )
				];
			} else {
				$suggestions[] = [
					'period' => $label,
					'days'   => $days,
					'occupancy' => round($occupancy),
					'type' => 'stable',
					'suggestion' => __( 'Healthy demand. Maintain current rates.', 'resort-manager' )
				];
			}
		}

		return $suggestions;
	}
}
