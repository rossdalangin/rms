<?php
namespace ResortManager\Admin;

class MetaBoxes {
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_accommodation_meta_boxes' ] );
		add_action( 'save_post_accommodation', [ $this, 'save_accommodation_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_service_meta_boxes' ] );
		add_action( 'save_post_service', [ $this, 'save_service_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_booking_meta_boxes' ] );
	}

	public function add_accommodation_meta_boxes() {
		add_meta_box(
			'accommodation_details',
			__( 'Accommodation Details', 'resort-manager' ),
			[ $this, 'render_accommodation_details' ],
			'accommodation',
			'normal',
			'high'
		);
	}

	public function add_booking_meta_boxes() {
		add_meta_box(
			'booking_reservation_details',
			__( 'Reservation Information', 'resort-manager' ),
			[ $this, 'render_booking_details' ],
			'booking',
			'normal',
			'high'
		);
	}

	public function render_booking_details( $post ) {
		$room_id = get_post_meta( $post->ID, '_resort_room_id', true );
		$checkin = get_post_meta( $post->ID, '_resort_checkin', true );
		$checkout = get_post_meta( $post->ID, '_resort_checkout', true );
		$guests_count = get_post_meta( $post->ID, '_resort_guests', true );
		$guest_id = get_post_meta( $post->ID, '_resort_guest_id', true );
		$total_price = get_post_meta( $post->ID, '_resort_total_price', true );
		$services = get_post_meta( $post->ID, '_resort_services', true ) ?: [];
		$coupon = get_post_meta( $post->ID, '_resort_coupon_used', true );

		$room = get_post( $room_id );
		$guest = get_userdata( $guest_id );

		?>
		<div class="booking-details-admin" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
			<div>
				<h4><?php _e( 'Stay Information', 'resort-manager' ); ?></h4>
				<table class="form-table">
					<tr>
						<th><?php _e( 'Accommodation:', 'resort-manager' ); ?></th>
						<td><?php echo $room ? '<a href="'.get_edit_post_link($room->ID).'">'.esc_html($room->post_title).'</a>' : 'N/A'; ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Check-in:', 'resort-manager' ); ?></th>
						<td><?php echo esc_html( $checkin ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Check-out:', 'resort-manager' ); ?></th>
						<td><?php echo esc_html( $checkout ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Guests:', 'resort-manager' ); ?></th>
						<td><?php echo esc_html( $guests_count ); ?></td>
					</tr>
				</table>
			</div>
			<div>
				<h4><?php _e( 'Guest Details', 'resort-manager' ); ?></h4>
				<table class="form-table">
					<tr>
						<th><?php _e( 'Name:', 'resort-manager' ); ?></th>
						<td><?php echo $guest ? esc_html( $guest->display_name ) : 'N/A'; ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Email:', 'resort-manager' ); ?></th>
						<td><?php echo $guest ? esc_html( $guest->user_email ) : 'N/A'; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<hr>
		<h4><?php _e( 'Extras & Services', 'resort-manager' ); ?></h4>
		<ul>
			<?php if ( empty( $services ) ) : ?>
				<li><em><?php _e( 'No extras selected.', 'resort-manager' ); ?></em></li>
			<?php else : ?>
				<?php foreach ( $services as $s_id ) :
					$s_post = get_post( $s_id );
					?>
					<li><?php echo $s_post ? esc_html( $s_post->post_title ) : 'Unknown Service'; ?></li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
		<hr>
		<div style="font-size: 1.2em; font-weight: bold; color: var(--resort-teal);">
			<?php _e( 'Final Price:', 'resort-manager' ); ?> <?php echo \ResortManager\Core\PricingEngine::format_price( $total_price ); ?>
			<?php if ( $coupon ) : ?>
				<span style="font-size: 0.8em; color: var(--resort-coral); margin-left: 20px;">
					(<?php printf( __( 'Coupon "%s" applied', 'resort-manager' ), $coupon ); ?>)
				</span>
			<?php endif; ?>
		</div>
		<?php
	}

	public function add_service_meta_boxes() {
		add_meta_box(
			'service_details',
			__( 'Service Details', 'resort-manager' ),
			[ $this, 'render_service_details' ],
			'service',
			'normal',
			'high'
		);
	}

	public function render_service_details( $post ) {
		wp_nonce_field( 'service_meta_box', 'service_meta_box_nonce' );
		$price = get_post_meta( $post->ID, '_resort_service_price', true );
		?>
		<p>
			<label for="resort_service_price"><?php _e( 'Price:', 'resort-manager' ); ?></label>
			<input type="number" id="resort_service_price" name="resort_service_price" value="<?php echo esc_attr( $price ); ?>" step="0.01">
		</p>
		<?php
	}

	public function save_service_meta( $post_id ) {
		if ( ! isset( $_POST['service_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['service_meta_box_nonce'], 'service_meta_box' ) ) {
			return;
		}
		if ( isset( $_POST['resort_service_price'] ) ) {
			update_post_meta( $post_id, '_resort_service_price', sanitize_text_field( $_POST['resort_service_price'] ) );
		}
	}

	public function render_accommodation_details( $post ) {
		wp_nonce_field( 'accommodation_meta_box', 'accommodation_meta_box_nonce' );

		$price = get_post_meta( $post->ID, '_resort_price', true );
		$capacity = get_post_meta( $post->ID, '_resort_capacity', true );
		$amenities = get_post_meta( $post->ID, '_resort_amenities', true );

		?>
		<p>
			<label for="resort_price"><?php _e( 'Price per Night:', 'resort-manager' ); ?></label>
			<input type="number" id="resort_price" name="resort_price" value="<?php echo esc_attr( $price ); ?>" step="0.01">
		</p>
		<p>
			<label for="resort_capacity"><?php _e( 'Max Capacity:', 'resort-manager' ); ?></label>
			<input type="number" id="resort_capacity" name="resort_capacity" value="<?php echo esc_attr( $capacity ); ?>">
		</p>
		<p>
			<label for="resort_amenities"><?php _e( 'Amenities (comma separated):', 'resort-manager' ); ?></label>
			<textarea id="resort_amenities" name="resort_amenities" class="widefat"><?php echo esc_textarea( $amenities ); ?></textarea>
		</p>
		<?php
	}

	public function save_accommodation_meta( $post_id ) {
		if ( ! isset( $_POST['accommodation_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['accommodation_meta_box_nonce'], 'accommodation_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['resort_price'] ) ) {
			update_post_meta( $post_id, '_resort_price', sanitize_text_field( $_POST['resort_price'] ) );
		}
		if ( isset( $_POST['resort_capacity'] ) ) {
			update_post_meta( $post_id, '_resort_capacity', sanitize_text_field( $_POST['resort_capacity'] ) );
		}
		if ( isset( $_POST['resort_amenities'] ) ) {
			update_post_meta( $post_id, '_resort_amenities', sanitize_textarea_field( $_POST['resort_amenities'] ) );
		}
	}
}
