<?php
namespace ResortManager\Admin;

class BookingCommunication {
	public function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_communication_meta_box' ] );
		add_action( 'save_post_booking', [ $this, 'save_communication_note' ] );
	}

	public function add_communication_meta_box() {
		add_meta_box(
			'booking_communication',
			__( 'Guest Communication Log', 'resort-manager' ),
			[ $this, 'render_communication_log' ],
			'booking',
			'side',
			'default'
		);
	}

	public function render_communication_log( $post ) {
		$log = get_post_meta( $post->ID, '_resort_communication_log', true ) ?: [];
		?>
		<div class="resort-communication-log" style="max-height: 200px; overflow-y: auto; margin-bottom: 10px;">
			<?php if ( empty( $log ) ) : ?>
				<p><em><?php _e( 'No communication recorded.', 'resort-manager' ); ?></em></p>
			<?php else : ?>
				<?php foreach ( $log as $entry ) : ?>
					<div style="margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px;">
						<strong><?php echo esc_html( $entry['date'] ); ?>:</strong><br>
						<?php echo esc_html( $entry['message'] ); ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<textarea name="resort_communication_note" style="width: 100%;" placeholder="<?php _e( 'Add a note or record a follow-up...', 'resort-manager' ); ?>"></textarea>
		<?php
		wp_nonce_field( 'resort_comm_nonce', 'resort_comm_nonce_field' );
	}

	public function save_communication_note( $post_id ) {
		if ( ! isset( $_POST['resort_comm_nonce_field'] ) || ! wp_verify_nonce( $_POST['resort_comm_nonce_field'], 'resort_comm_nonce' ) ) {
			return;
		}

		if ( ! empty( $_POST['resort_communication_note'] ) ) {
			$log = get_post_meta( $post_id, '_resort_communication_log', true ) ?: [];
			$log[] = [
				'date'    => date( 'Y-m-d H:i' ),
				'message' => sanitize_textarea_field( $_POST['resort_communication_note'] )
			];
			update_post_meta( $post_id, '_resort_communication_log', $log );
		}
	}
}
