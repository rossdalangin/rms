<?php
namespace ResortManager\Admin;

class Calendar {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_calendar_page' ] );
	}

	public function add_calendar_page() {
		add_submenu_page(
			'resort-manager',
			__( 'Reservation Calendar', 'resort-manager' ),
			__( 'Calendar', 'resort-manager' ),
			'manage_options',
			'resort-calendar',
			[ $this, 'render_calendar_page' ]
		);
	}

	public function render_calendar_page() {
		$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : date( 'Y-m-d' );
		$duration = isset( $_GET['duration'] ) ? intval( $_GET['duration'] ) : 14;
		$end_date = date( 'Y-m-d', strtotime( "$start_date +" . ($duration - 1) . " days" ) );

		?>
		<div class="wrap">
			<h1><?php _e( 'Reservation Calendar', 'resort-manager' ); ?></h1>
			<p class="description">
				<?php _e( 'Manage your resort\'s daily inventory and occupancy at a glance. You can use the controls below to navigate through time and adjust the timeline density.', 'resort-manager' ); ?>
			</p>

			<div style="background: #fff; border: 1px solid #ccd0d4; padding: 10px; margin-bottom: 20px; display: inline-block;">
				<span style="margin-right: 20px;"><strong><?php _e( 'Key:', 'resort-manager' ); ?></strong></span>
				<span style="display:inline-block; width:15px; height:15px; background:#46b450; vertical-align:middle;"></span> <?php _e( 'Booked (Confirmed/Pending)', 'resort-manager' ); ?> &nbsp;&nbsp;
				<span style="display:inline-block; width:15px; height:15px; background:#636e72; vertical-align:middle;"></span> <?php _e( 'Sync (External iCal Block)', 'resort-manager' ); ?> &nbsp;&nbsp;
				<span style="display:inline-block; width:15px; height:15px; background:#fff; border:1px solid #ddd; vertical-align:middle;"></span> <?php _e( 'Free (Available)', 'resort-manager' ); ?>
			</div>

			<div class="calendar-controls" style="background: #fff; padding: 15px; border: 1px solid #ccd0d4; margin-bottom: 20px;">
				<form method="get" action="">
					<input type="hidden" name="page" value="resort-calendar">
					<label><?php _e( 'Start Date:', 'resort-manager' ); ?></label>
					<input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>">

					<label style="margin-left: 20px;"><?php _e( 'Timeline Range:', 'resort-manager' ); ?></label>
					<select name="duration">
						<option value="7" <?php selected( $duration, 7 ); ?>>7 Days</option>
						<option value="14" <?php selected( $duration, 14 ); ?>>14 Days</option>
						<option value="30" <?php selected( $duration, 30 ); ?>>30 Days</option>
					</select>

					<input type="submit" class="button" value="<?php _e( 'Update View', 'resort-manager' ); ?>">

					<div style="float:right;">
						<a href="<?php echo home_url('/?resort_ical=all'); ?>" class="button button-secondary">
							<span class="dashicons dashicons-calendar-alt" style="vertical-align: middle;"></span> <?php _e( 'Export Master iCal', 'resort-manager' ); ?>
						</a>
					</div>
				</form>
			</div>

			<div id="resort-calendar-container">
				<div class="calendar-grid" style="overflow-x: auto;">
					<table class="wp-list-table widefat fixed striped" style="min-width: <?php echo ($duration * 100 + 200); ?>px;">
						<thead>
							<tr>
								<th style="width: 200px;"><?php _e( 'Room / Date', 'resort-manager' ); ?></th>
								<?php for ( $i = 0; $i < $duration; $i++ ) : ?>
									<th><?php echo date( 'D, M j', strtotime( "$start_date +$i days" ) ); ?></th>
								<?php endfor; ?>
							</tr>
						</thead>
						<tbody>
							<?php
							global $wpdb;
							$table_availability = $wpdb->prefix . 'resort_availability';
							$rooms = get_posts( [ 'post_type' => 'accommodation', 'numberposts' => -1 ] );

							// Fetch all availability for the range in one query
							$availabilities = $wpdb->get_results( $wpdb->prepare(
								"SELECT room_id, date, status FROM $table_availability WHERE date >= %s AND date <= %s",
								$start_date, $end_date
							) );

							$availability_map = [];
							foreach ( $availabilities as $av ) {
								$availability_map[$av->room_id][$av->date] = $av->status;
							}

							foreach ( $rooms as $room ) :
								?>
								<tr>
									<td><strong><?php echo esc_html( $room->post_title ); ?></strong></td>
									<?php
									for ( $i = 0; $i < $duration; $i++ ) :
										$date = date( 'Y-m-d', strtotime( "$start_date +$i days" ) );
										$status = isset( $availability_map[$room->ID][$date] ) ? $availability_map[$room->ID][$date] : 'available';

										$class = 'calendar-cell';
										if ( $status === 'booked' ) $class .= ' status-booked';
										elseif ( $status === 'sync' ) $class .= ' status-sync';
										else $class .= ' status-available';

										$label = $status === 'booked' ? __( 'Booked', 'resort-manager' ) : ( $status === 'sync' ? __( 'Sync', 'resort-manager' ) : __( 'Free', 'resort-manager' ) );
										?>
										<td class="<?php echo $class; ?>" title="<?php echo esc_attr($date . ' - ' . $label); ?>"><?php echo $label; ?></td>
									<?php endfor; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}
}
