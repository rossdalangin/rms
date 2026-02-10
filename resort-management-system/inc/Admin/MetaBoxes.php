<?php
namespace ResortManager\Admin;

class MetaBoxes {
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_accommodation_meta_boxes' ] );
		add_action( 'save_post_accommodation', [ $this, 'save_accommodation_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_service_meta_boxes' ] );
		add_action( 'save_post_service', [ $this, 'save_service_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_package_meta_boxes' ] );
		add_action( 'save_post_resort_package', [ $this, 'save_package_meta' ] );
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

	public function add_package_meta_boxes() {
		add_meta_box(
			'package_details',
			__( 'Package Configuration', 'resort-manager' ),
			[ $this, 'render_package_details' ],
			'resort_package',
			'normal',
			'high'
		);
	}

	public function render_package_details( $post ) {
		wp_nonce_field( 'package_meta_box', 'package_meta_box_nonce' );
		$room_id = get_post_meta( $post->ID, '_resort_package_room_id', true );
		$package_price = get_post_meta( $post->ID, '_resort_package_price', true );
		$included_services = get_post_meta( $post->ID, '_resort_package_services', true ) ?: [];

		$rooms = get_posts( [ 'post_type' => 'accommodation', 'numberposts' => -1 ] );
		$services = get_posts( [ 'post_type' => 'service', 'numberposts' => -1 ] );
		?>
		<p>
			<label><strong><?php _e( 'Base Accommodation:', 'resort-manager' ); ?></strong></label><br>
			<select name="resort_package_room_id" class="widefat">
				<option value=""><?php _e( '-- Select Room --', 'resort-manager' ); ?></option>
				<?php foreach ( $rooms as $room ) : ?>
					<option value="<?php echo $room->ID; ?>" <?php selected( $room_id, $room->ID ); ?>><?php echo esc_html( $room->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label><strong><?php _e( 'Included Services/Extras:', 'resort-manager' ); ?></strong></label><br>
			<?php foreach ( $services as $service ) : ?>
				<label style="display:block; margin-bottom:5px;">
					<input type="checkbox" name="resort_package_services[]" value="<?php echo $service->ID; ?>" <?php checked( in_array( $service->ID, $included_services ) ); ?>>
					<?php echo esc_html( $service->post_title ); ?>
				</label>
			<?php endforeach; ?>
		</p>
		<p>
			<label><strong><?php _e( 'Package Price (Total per night):', 'resort-manager' ); ?></strong></label><br>
			<input type="number" name="resort_package_price" value="<?php echo esc_attr( $package_price ); ?>" step="0.01" class="regular-text">
			<p class="description"><?php _e( 'This price will override the standard room + extras sum.', 'resort-manager' ); ?></p>
		</p>
		<?php
	}

	public function save_package_meta( $post_id ) {
		if ( ! isset( $_POST['package_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['package_meta_box_nonce'], 'package_meta_box' ) ) {
			return;
		}
		update_post_meta( $post_id, '_resort_package_room_id', intval( $_POST['resort_package_room_id'] ) );
		update_post_meta( $post_id, '_resort_package_price', floatval( $_POST['resort_package_price'] ) );
		update_post_meta( $post_id, '_resort_package_services', isset( $_POST['resort_package_services'] ) ? array_map( 'intval', $_POST['resort_package_services'] ) : [] );
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
		$room_ids = get_post_meta( $post->ID, '_resort_room_ids', true );
		if ( empty( $room_ids ) ) {
			$room_ids = [ get_post_meta( $post->ID, '_resort_room_id', true ) ];
		}
		$checkin = get_post_meta( $post->ID, '_resort_checkin', true );
		$checkout = get_post_meta( $post->ID, '_resort_checkout', true );
		$guests_count = get_post_meta( $post->ID, '_resort_guests', true );
		$guest_id = get_post_meta( $post->ID, '_resort_guest_id', true );
		$guest_phone = get_post_meta( $post->ID, '_resort_guest_phone', true );
		$meal_pref = get_post_meta( $post->ID, '_resort_meal_preference', true );
		$special_req = get_post_meta( $post->ID, '_resort_special_requests', true );
		$marketing = get_post_meta( $post->ID, '_resort_marketing_optin', true );
		$waiver = get_post_meta( $post->ID, '_resort_digital_waiver', true );
		$total_price = get_post_meta( $post->ID, '_resort_total_price', true );
		$services = get_post_meta( $post->ID, '_resort_services', true ) ?: [];
		$coupon = get_post_meta( $post->ID, '_resort_coupon_used', true );

		$guest = get_userdata( $guest_id );
		$fname = get_post_meta( $post->ID, '_resort_first_name', true );
		$lname = get_post_meta( $post->ID, '_resort_last_name', true );
		$display_name = ( ! empty( $fname ) || ! empty( $lname ) ) ? "$fname $lname" : ( $guest ? $guest->display_name : 'N/A' );

		?>
		<div class="booking-details-admin" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
			<div>
				<h4><?php _e( 'Stay Information', 'resort-manager' ); ?></h4>
				<table class="form-table">
					<tr>
						<th><?php _e( 'Accommodation(s):', 'resort-manager' ); ?></th>
						<td>
							<?php
							foreach ( $room_ids as $r_id ) {
								$room = get_post( $r_id );
								if ( $room ) {
									echo '<a href="'.get_edit_post_link($room->ID).'">'.esc_html($room->post_title).'</a><br>';
								}
							}
							?>
						</td>
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
						<td><?php echo esc_html( $display_name ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Email:', 'resort-manager' ); ?></th>
						<td><?php echo $guest ? esc_html( $guest->user_email ) : 'N/A'; ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Phone:', 'resort-manager' ); ?></th>
						<td><?php echo esc_html( $guest_phone ?: 'N/A' ); ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Marketing:', 'resort-manager' ); ?></th>
						<td><?php echo 'yes' === $marketing ? '<span style="color:green;">✔ Subscribed</span>' : 'No'; ?></td>
					</tr>
					<tr>
						<th><?php _e( 'Waiver:', 'resort-manager' ); ?></th>
						<td><?php echo 'accepted' === $waiver ? '<span style="color:green;">✔ Accepted</span>' : '<span style="color:red;">✘ Not Accepted</span>'; ?></td>
					</tr>
				</table>
			</div>
		</div>
		<hr>
		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
			<div>
				<h4><?php _e( 'Preferences & Requests', 'resort-manager' ); ?></h4>
				<p><strong><?php _e( 'Meal Preference:', 'resort-manager' ); ?></strong> <?php echo esc_html( ucfirst( $meal_pref ) ?: 'Standard' ); ?></p>
				<p><strong><?php _e( 'Special Requests:', 'resort-manager' ); ?></strong></p>
				<div style="background: #f9f9f9; padding: 10px; border-left: 4px solid #ddd;">
					<?php echo nl2br( esc_html( $special_req ?: 'No special requests.' ) ); ?>
				</div>
			</div>
			<div>
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
			</div>
		</div>
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
		<p class="description"><?php _e( 'Define the price for this optional extra. Guests can select this service during the booking process.', 'resort-manager' ); ?></p>
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
		$ical_url = get_post_meta( $post->ID, '_resort_ical_url', true );

		?>
		<p class="description"><?php _e( 'Configure the core properties of this accommodation. These details will be displayed to guests in the room grid and search results.', 'resort-manager' ); ?></p>
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
			<textarea id="resort_amenities" name="resort_amenities" class="widefat" placeholder="e.g. WiFi, Ocean View, King Bed, Private Pool"><?php echo esc_textarea( $amenities ); ?></textarea>
		</p>
		<hr>
		<h4><?php _e( 'External iCal Sync', 'resort-manager' ); ?></h4>
		<p class="description"><?php _e( 'Import availability from external platforms like Airbnb or VRBO by providing their iCal feed URL. The system will automatically block these dates during daily syncs.', 'resort-manager' ); ?></p>
		<p>
			<label for="resort_ical_url"><?php _e( 'External iCal URL:', 'resort-manager' ); ?></label>
			<input type="url" id="resort_ical_url" name="resort_ical_url" value="<?php echo esc_attr( $ical_url ); ?>" class="widefat">
		</p>
		<p class="description">
			<small><?php _e( 'Your private export URL for this room:', 'resort-manager' ); ?> <br>
			<code><?php echo home_url('/?resort_ical=' . $post->ID); ?></code></small>
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
		if ( isset( $_POST['resort_ical_url'] ) ) {
			update_post_meta( $post_id, '_resort_ical_url', esc_url_raw( $_POST['resort_ical_url'] ) );
		}
	}
}
