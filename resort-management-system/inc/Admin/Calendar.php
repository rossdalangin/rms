<?php
namespace ResortManager\Admin;

class Calendar {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_calendar_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function add_calendar_page() {
		add_submenu_page(
			'edit.php?post_type=booking',
			__( 'Reservation Calendar', 'resort-manager' ),
			__( 'Calendar', 'resort-manager' ),
			'manage_options',
			'resort-calendar',
			[ $this, 'render_calendar_page' ]
		);
	}

	public function enqueue_assets( $hook ) {
		if ( 'booking_page_resort-calendar' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'resort-admin-calendar', RESORT_MANAGER_URL . 'assets/css/admin-calendar.css', [], RESORT_MANAGER_VERSION );
	}

	public function render_calendar_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Reservation Calendar', 'resort-manager' ); ?></h1>
			<div id="resort-calendar-container">
				<p><?php _e( 'Timeline view coming soon...', 'resort-manager' ); ?></p>
				<div class="calendar-grid">
					<!-- Simplified grid representation -->
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php _e( 'Room / Date', 'resort-manager' ); ?></th>
								<?php for ( $i = 0; $i < 7; $i++ ) : ?>
									<th><?php echo date( 'D, M j', strtotime( "+$i days" ) ); ?></th>
								<?php endfor; ?>
							</tr>
						</thead>
						<tbody>
							<?php
							global $wpdb;
							$table_availability = $wpdb->prefix . 'resort_availability';
							$rooms = get_posts( [ 'post_type' => 'accommodation', 'numberposts' => -1 ] );

							$start_date = date( 'Y-m-d' );
							$end_date   = date( 'Y-m-d', strtotime( '+6 days' ) );

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
									for ( $i = 0; $i < 7; $i++ ) :
										$date = date( 'Y-m-d', strtotime( "+$i days" ) );
										$status = isset( $availability_map[$room->ID][$date] ) ? $availability_map[$room->ID][$date] : 'available';

										$class = $status === 'booked' ? 'status-booked' : 'status-available';
										$label = $status === 'booked' ? __( 'Booked', 'resort-manager' ) : __( 'Available', 'resort-manager' );
										?>
										<td class="<?php echo $class; ?>"><?php echo $label; ?></td>
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
