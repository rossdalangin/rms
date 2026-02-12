<?php
namespace ResortManager\Admin;

class Housekeeping {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_housekeeping_page' ] );
		add_action( 'admin_init', [ $this, 'handle_status_update' ] );
	}

	public function add_housekeeping_page() {
		add_submenu_page(
			'resort-manager',
			__( 'Housekeeping', 'resort-manager' ),
			__( 'Housekeeping', 'resort-manager' ),
			'edit_posts', // Accessible by staff/editors
			'resort-housekeeping',
			[ $this, 'render_housekeeping_page' ]
		);
	}

	public function handle_status_update() {
		if ( isset( $_GET['resort_update_room_status'] ) && current_user_can( 'edit_posts' ) ) {
			check_admin_referer( 'resort_update_status' );
			$room_id = intval( $_GET['room_id'] );
			$status = sanitize_text_field( $_GET['status'] );
			update_post_meta( $room_id, '_resort_housekeeping_status', $status );

			\ResortManager\Core\ActivityLogger::log( sprintf( __( 'Room #%d housekeeping status updated to %s.', 'resort-manager' ), $room_id, $status ) );

			wp_redirect( remove_query_arg( [ 'resort_update_room_status', 'room_id', 'status', '_wpnonce' ] ) );
			exit;
		}
	}

	public function render_housekeeping_page() {
		$rooms = get_posts( [ 'post_type' => 'accommodation', 'numberposts' => -1 ] );
		$today = date( 'Y-m-d' );

		// Find today's departures for housekeeping priority
		$departures = get_posts( [
			'post_type'  => 'booking',
			'meta_query' => [
				[ 'key' => '_resort_checkout', 'value' => $today ],
				[ 'key' => '_resort_status', 'value' => 'confirmed' ]
			],
			'numberposts' => -1
		] );

		?>
		<div class="wrap">
			<h1><?php _e( 'Housekeeping & Maintenance', 'resort-manager' ); ?></h1>
			<p class="description"><?php _e( 'Manage room readiness and cleaning schedules. This view is optimized for operational staff.', 'resort-manager' ); ?></p>

			<div class="resort-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; margin-bottom: 30px;">
				<div class="stat-card resort-admin-card" style="margin-bottom:0; border-top-color: var(--resort-coral);">
					<h3><?php _e( 'Check-outs Today', 'resort-manager' ); ?></h3>
					<p style="font-size: 28px; font-weight: 700; color: var(--resort-coral);"><?php echo count($departures); ?></p>
					<p style="color:var(--resort-muted);"><small><?php _e( 'High priority for cleaning', 'resort-manager' ); ?></small></p>
				</div>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Accommodation', 'resort-manager' ); ?></th>
						<th><?php _e( 'Cleaning Status', 'resort-manager' ); ?></th>
						<th><?php _e( 'Availability', 'resort-manager' ); ?></th>
						<th><?php _e( 'Actions', 'resort-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rooms as $room ) :
						$status = get_post_meta( $room->ID, '_resort_housekeeping_status', true ) ?: 'clean';
						$color = ($status === 'dirty') ? '#d63638' : (($status === 'cleaning') ? '#ffb900' : '#46b450');

						// Simple occupied check
						global $wpdb;
						$is_occupied = $wpdb->get_var( $wpdb->prepare(
							"SELECT COUNT(*) FROM {$wpdb->prefix}resort_availability WHERE room_id = %d AND date = %s AND status = 'booked'",
							$room->ID, $today
						) );
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $room->post_title ); ?></strong>
								<?php
								$checklist = get_post_meta( $room->ID, '_resort_housekeeping_checklist', true );
								if ( $checklist ) :
									$tasks = explode("\n", $checklist);
								?>
									<div class="room-checklist" style="margin-top:10px; font-size:11px; color:#666; background:#f9f9f9; padding:8px; border-radius:4px; border-left:3px solid var(--resort-sand);">
										<strong><?php _e( 'Tasks:', 'resort-manager' ); ?></strong><br>
										<?php foreach ( $tasks as $task ) : if(trim($task)) : ?>
											<label style="display:block; margin-top:3px;">
												<input type="checkbox"> <?php echo esc_html(trim($task)); ?>
											</label>
										<?php endif; endforeach; ?>
									</div>
								<?php endif; ?>
							</td>
							<td>
								<span class="housekeeping-status-dot" style="background:<?php echo $color; ?>;"></span>
								<?php echo esc_html( ucfirst($status) ); ?>
							</td>
							<td>
								<?php if ( $is_occupied ) : ?>
									<span class="status-badge status-pending"><?php _e( 'Occupied', 'resort-manager' ); ?></span>
								<?php else : ?>
									<span class="status-badge status-confirmed"><?php _e( 'Vacant', 'resort-manager' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<select onchange="location.href=this.value;">
									<option value=""><?php _e( 'Update Status...', 'resort-manager' ); ?></option>
									<option value="<?php echo wp_nonce_url( admin_url('admin.php?page=resort-housekeeping&resort_update_room_status=1&room_id='.$room->ID.'&status=clean'), 'resort_update_status' ); ?>"><?php _e( 'Mark Clean', 'resort-manager' ); ?></option>
									<option value="<?php echo wp_nonce_url( admin_url('admin.php?page=resort-housekeeping&resort_update_room_status=1&room_id='.$room->ID.'&status=cleaning'), 'resort_update_status' ); ?>"><?php _e( 'Mark Cleaning...', 'resort-manager' ); ?></option>
									<option value="<?php echo wp_nonce_url( admin_url('admin.php?page=resort-housekeeping&resort_update_room_status=1&room_id='.$room->ID.'&status=dirty'), 'resort_update_status' ); ?>"><?php _e( 'Mark Dirty', 'resort-manager' ); ?></option>
								</select>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2 style="margin-top:40px;"><?php _e( 'Active In-Stay Requests', 'resort-manager' ); ?></h2>
			<div class="resort-admin-card">
				<?php
				$active_requests = get_posts([
					'post_type' => 'booking',
					'meta_query' => [
						[ 'key' => '_resort_checkin', 'value' => date('Y-m-d'), 'compare' => '<=' ],
						[ 'key' => '_resort_checkout', 'value' => date('Y-m-d'), 'compare' => '>=' ]
					],
					'numberposts' => -1
				]);

				$found_request = false;
				foreach ( $active_requests as $booking ) {
					$log = get_post_meta( $booking->ID, '_resort_communication_log', true ) ?: [];
					foreach ( array_reverse($log) as $entry ) {
						if ( strpos($entry['message'], 'IN-STAY REQUEST') !== false ) {
							$found_request = true;
							echo '<div style="padding:15px; border-bottom:1px solid #eee;">';
							echo '<strong>Room: '.get_the_title(get_post_meta($booking->ID, '_resort_room_id', true)).'</strong><br>';
							echo '<small>'.$entry['date'].'</small><br>';
							echo esc_html($entry['message']);
							echo '</div>';
						}
					}
				}

				if ( ! $found_request ) {
					echo '<p>'.__( 'No active service requests.', 'resort-manager' ).'</p>';
				}
				?>
			</div>
		</div>
		<?php
	}
}
