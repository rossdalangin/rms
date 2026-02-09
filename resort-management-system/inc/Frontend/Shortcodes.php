<?php
namespace ResortManager\Frontend;

class Shortcodes {
	public function __construct() {
		add_shortcode( 'resort_booking', [ $this, 'render_booking_engine' ] );
		add_shortcode( 'resort_rooms_grid', [ $this, 'render_rooms_grid' ] );
		add_shortcode( 'resort_guest_dashboard', [ $this, 'render_guest_dashboard' ] );
		add_shortcode( 'resort_reviews', [ $this, 'render_reviews' ] );
	}

	public function render_booking_engine( $atts ) {
		ob_start();
		?>
		<div id="resort-booking-app" class="resort-booking-container">
			<div class="resort-step-indicator">
				<span class="step active" data-step="1">1. Search</span>
				<span class="step" data-step="2">2. Select</span>
				<span class="step" data-step="3">3. Details</span>
				<span class="step" data-step="4">4. Payment</span>
			</div>

			<div id="resort-step-content">
				<div id="resort-step-1" class="resort-step">
					<?php include RESORT_MANAGER_PATH . 'templates/search-interface.php'; ?>
				</div>
				<div id="resort-step-2" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/room-selection.php'; ?>
				</div>
				<div id="resort-step-3" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/guest-info.php'; ?>
				</div>
				<div id="resort-step-4" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/payment-confirmation.php'; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_rooms_grid( $atts ) {
		$rooms = get_posts( [ 'post_type' => 'accommodation', 'numberposts' => -1 ] );
		ob_start();
		?>
		<div class="resort-rooms-grid resort-grid">
			<?php foreach ( $rooms as $room ) :
				$price = get_post_meta( $room->ID, '_resort_price', true );
				$image = get_the_post_thumbnail_url( $room->ID, 'medium' ) ?: get_post_meta( $room->ID, '_resort_sample_image', true );
				?>
				<div class="resort-room-card">
					<?php if ( $image ) : ?>
						<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $room->post_title ); ?>">
					<?php endif; ?>
					<h4><?php echo esc_html( $room->post_title ); ?></h4>
					<p><?php echo esc_html( $room->post_excerpt ); ?></p>
					<div class="room-meta">
						<strong>$<?php echo esc_html( $price ); ?> / night</strong>
					</div>
					<a href="<?php echo get_permalink( $room->ID ); ?>" class="resort-btn" style="display:inline-block; text-decoration:none; margin-top:10px;">
						<?php _e( 'View Details', 'resort-manager' ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_guest_dashboard( $atts ) {
		if ( ! is_user_logged_in() ) {
			return '<p>' . __( 'Please log in to view your bookings.', 'resort-manager' ) . '</p>';
		}

		$current_user = wp_get_current_user();
		$bookings = get_posts( [
			'post_type'  => 'booking',
			'meta_query' => [
				[
					'key'   => '_resort_guest_email',
					'value' => $current_user->user_email,
				]
			],
			'numberposts' => -1
		] );

		ob_start();
		?>
		<div class="resort-guest-dashboard">
			<h3><?php _e( 'My Reservations', 'resort-manager' ); ?></h3>
			<?php if ( empty( $bookings ) ) : ?>
				<p><?php _e( 'You have no reservations.', 'resort-manager' ); ?></p>
			<?php else : ?>
				<table class="resort-table" style="width:100%; border-collapse: collapse;">
					<thead>
						<tr style="border-bottom: 2px solid #eee;">
							<th style="text-align:left; padding:10px;">ID</th>
							<th style="text-align:left; padding:10px;">Dates</th>
							<th style="text-align:left; padding:10px;">Status</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $bookings as $booking ) :
							$checkin = get_post_meta( $booking->ID, '_resort_checkin', true );
							$checkout = get_post_meta( $booking->ID, '_resort_checkout', true );
							$status = get_post_meta( $booking->ID, '_resort_status', true );
							?>
							<tr style="border-bottom: 1px solid #eee;">
								<td style="padding:10px;">#<?php echo $booking->ID; ?></td>
								<td style="padding:10px;"><?php echo esc_html( $checkin ); ?> - <?php echo esc_html( $checkout ); ?></td>
								<td style="padding:10px;"><?php echo esc_html( ucfirst( $status ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_reviews( $atts ) {
		$reviews = get_posts( [ 'post_type' => 'review', 'numberposts' => 5 ] );
		ob_start();
		?>
		<div class="resort-reviews-section">
			<h3><?php _e( 'Guest Reviews', 'resort-manager' ); ?></h3>
			<?php if ( empty( $reviews ) ) : ?>
				<p><?php _e( 'No reviews yet.', 'resort-manager' ); ?></p>
			<?php else : ?>
				<?php foreach ( $reviews as $review ) : ?>
					<div class="resort-review-card" style="border: 1px solid #eee; padding: 15px; margin-bottom: 10px;">
						<h4><?php echo esc_html( $review->post_title ); ?></h4>
						<div><?php echo wp_kses_post( $review->post_content ); ?></div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
