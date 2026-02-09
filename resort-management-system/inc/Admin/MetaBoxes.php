<?php
namespace ResortManager\Admin;

class MetaBoxes {
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_accommodation_meta_boxes' ] );
		add_action( 'save_post_accommodation', [ $this, 'save_accommodation_meta' ] );
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
