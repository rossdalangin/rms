<?php
namespace ResortManager\Admin;

class MetaBoxes {
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_accommodation_meta_boxes' ] );
		add_action( 'save_post_accommodation', [ $this, 'save_accommodation_meta' ] );
		add_action( 'add_meta_boxes', [ $this, 'add_service_meta_boxes' ] );
		add_action( 'save_post_service', [ $this, 'save_service_meta' ] );
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
