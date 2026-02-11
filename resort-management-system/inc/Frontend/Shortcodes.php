<?php
namespace ResortManager\Frontend;

class Shortcodes {
	public function __construct() {
		add_shortcode( 'resort_booking', [ $this, 'render_booking_engine' ] );
		add_shortcode( 'resort_rooms_grid', [ $this, 'render_rooms_grid' ] );
		add_shortcode( 'resort_guest_dashboard', [ $this, 'render_guest_dashboard' ] );
		add_shortcode( 'resort_reviews', [ $this, 'render_reviews' ] );
		add_shortcode( 'resort_lead_form', [ $this, 'render_lead_form' ] );
		add_shortcode( 'resort_room_calendar', [ $this, 'render_room_calendar' ] );
	}

	public function render_booking_engine( $atts ) {
		$logo = get_option( 'resort_logo' );
		ob_start();
		?>
		<div id="resort-booking-app" class="resort-booking-container">
			<?php if ( $logo ) : ?>
				<div class="resort-booking-logo" style="text-align:center; margin-bottom:30px;">
					<img src="<?php echo esc_url($logo); ?>" style="max-height: 80px;">
				</div>
			<?php endif; ?>
			<div class="resort-step-indicator">
				<span class="step active" data-step="1">1. Search</span>
				<span class="step" data-step="2">2. Room</span>
				<span class="step" data-step="3">3. Extras</span>
				<span class="step" data-step="4">4. Details</span>
				<span class="step" data-step="5">5. Payment</span>
			</div>

			<div id="resort-step-content">
				<div id="resort-step-1" class="resort-step">
					<?php include RESORT_MANAGER_PATH . 'templates/search-interface.php'; ?>
				</div>
				<div id="resort-step-2" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/room-selection.php'; ?>
				</div>
				<div id="resort-step-3" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/services-selection.php'; ?>
				</div>
				<div id="resort-step-4" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/guest-info.php'; ?>
				</div>
				<div id="resort-step-5" class="resort-step" style="display:none;">
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
						<strong><?php echo \ResortManager\Core\PricingEngine::format_price( $price ); ?> / night</strong>
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
			return '<div class="resort-booking-container"><p>' . __( 'Please log in to view your personalized dashboard.', 'resort-manager' ) . '</p>' . wp_login_form(['echo' => false]) . '</div>';
		}

		$current_user = wp_get_current_user();
		$loyalty_points = get_user_meta( $current_user->ID, '_resort_loyalty_points', true ) ?: 0;
		$display_name = $current_user->first_name ?: $current_user->display_name;

		$bookings = get_posts( [
			'post_type'  => 'booking',
			'meta_query' => [
				[
					'key'   => '_resort_guest_id',
					'value' => $current_user->ID,
				]
			],
			'numberposts' => -1
		] );

		ob_start();
		?>
		<div class="resort-guest-dashboard resort-booking-container">
			<div class="guest-welcome" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; border-bottom:1px solid #eee; padding-bottom:20px;">
				<div>
					<h2 style="margin:0;"><?php printf( __( 'Aloha, %s!', 'resort-manager' ), $display_name ); ?></h2>
					<p><?php _e( 'Welcome to your private guest portal.', 'resort-manager' ); ?></p>
				</div>
				<div class="loyalty-badge" style="background:var(--resort-primary); color:#fff; padding:15px; border-radius:12px; text-align:center;">
					<span style="font-size:24px; font-weight:bold; display:block;"><?php echo $loyalty_points; ?></span>
					<span style="font-size:10px; text-transform:uppercase;"><?php _e( 'Loyalty Points', 'resort-manager' ); ?></span>
				</div>
			</div>

			<h3><?php _e( 'Your Stay History', 'resort-manager' ); ?></h3>
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
								<td style="padding:10px;">
									<?php
									$p_status = get_post_meta( $booking->ID, '_resort_payment_status', true );
									echo esc_html( ucfirst( $status ) );
									if ( $p_status === 'pending' ) {
										echo ' <small style="color:var(--resort-coral);">(' . __( 'Payment Pending', 'resort-manager' ) . ')</small>';
									}
									?>
									<?php if ( 'confirmed' === $status ) : ?>
										<div style="margin-top:10px;">
											<a href="<?php echo home_url('/?resort_invoice=' . $booking->ID); ?>" target="_blank" class="resort-btn-small" style="background:var(--resort-teal); text-decoration:none; margin-right:5px;">📄 Invoice</a>
											<a href="<?php echo home_url('/?resort_ical_booking=' . $booking->ID); ?>" class="resort-btn-small" style="background:#636e72; text-decoration:none; margin-right:5px;">🗓️ iCal</a>
											<a href="<?php
												$gcal_url = 'https://www.google.com/calendar/render?action=TEMPLATE';
												$gcal_url .= '&text=' . urlencode('Stay at LuxeResort');
												$gcal_url .= '&dates=' . date('Ymd', strtotime($checkin)) . '/' . date('Ymd', strtotime($checkout));
												$gcal_url .= '&details=' . urlencode('Booking ID: #' . $booking->ID);
												echo $gcal_url;
											?>" target="_blank" class="resort-btn-small" style="background:#4285F4; text-decoration:none; margin-right:5px;">🔵 Google</a>
											<?php if ( strtotime( $checkin ) > time() ) : ?>
												<button class="resort-btn-small show-modify-form" data-booking="<?php echo $booking->ID; ?>" style="background:var(--resort-secondary);"><?php _e( 'Modify Stay', 'resort-manager' ); ?></button>
											<?php endif; ?>
											<?php if ( $p_status === 'pending' ) : ?>
												<button class="resort-btn-small show-pay-modal" data-booking="<?php echo $booking->ID; ?>" style="background:var(--resort-teal);"><?php _e( 'Pay Balance', 'resort-manager' ); ?></button>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<?php if ( 'confirmed' === $status && strtotime( $checkout ) < time() ) : ?>
										<br><button class="resort-btn-small show-review-form" data-booking="<?php echo $booking->ID; ?>"><?php _e( 'Leave a Review', 'resort-manager' ); ?></button>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>

		<div id="resort-review-modal" class="resort-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; padding:30px; box-shadow:0 0 20px rgba(0,0,0,0.2); z-index:1000; width:400px;">
			<h3><?php _e( 'Leave a Review', 'resort-manager' ); ?></h3>
			<form id="resort-review-form">
				<input type="hidden" name="booking_id" id="review-booking-id">
				<div class="resort-field">
					<label><?php _e( 'Title', 'resort-manager' ); ?></label>
					<input type="text" name="title" required>
				</div>
				<div class="resort-field">
					<label><?php _e( 'Your Experience', 'resort-manager' ); ?></label>
					<textarea name="content" required></textarea>
				</div>
				<div style="margin-top:20px;">
					<button type="submit" class="resort-btn"><?php _e( 'Submit Review', 'resort-manager' ); ?></button>
					<button type="button" class="resort-btn-secondary close-modal"><?php _e( 'Cancel', 'resort-manager' ); ?></button>
				</div>
			</form>
		</div>

		<div id="resort-modify-modal" class="resort-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; padding:30px; box-shadow:0 0 20px rgba(0,0,0,0.2); z-index:1000; width:400px;">
			<h3><?php _e( 'Modify Your Stay', 'resort-manager' ); ?></h3>
			<p><?php _e( 'Please describe the changes you would like to make to your reservation.', 'resort-manager' ); ?></p>
			<form id="resort-modify-form">
				<input type="hidden" name="booking_id" id="modify-booking-id">
				<div class="resort-field">
					<label><?php _e( 'Requested Changes', 'resort-manager' ); ?></label>
					<textarea name="request_details" placeholder="e.g. Change dates to Aug 12-15..." required></textarea>
				</div>
				<div style="margin-top:20px;">
					<button type="submit" class="resort-btn"><?php _e( 'Send Request', 'resort-manager' ); ?></button>
					<button type="button" class="resort-btn-secondary close-modal"><?php _e( 'Cancel', 'resort-manager' ); ?></button>
				</div>
			</form>
		</div>

		<div id="resort-pay-modal" class="resort-modal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; padding:30px; box-shadow:0 0 20px rgba(0,0,0,0.2); z-index:1000; width:400px;">
			<h3><?php _e( 'Pay Your Balance', 'resort-manager' ); ?></h3>
			<p><?php _e( 'Select a payment method to complete your reservation.', 'resort-manager' ); ?></p>
			<div class="resort-field">
				<label><input type="radio" name="dashboard_payment_method" value="stripe" checked> Stripe (Credit Card)</label><br>
				<label><input type="radio" name="dashboard_payment_method" value="paypal"> PayPal</label>
			</div>
			<div style="margin-top:20px;">
				<button type="button" id="resort-dashboard-pay-now" class="resort-btn"><?php _e( 'Pay Now', 'resort-manager' ); ?></button>
				<button type="button" class="resort-btn-secondary close-modal"><?php _e( 'Cancel', 'resort-manager' ); ?></button>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_lead_form( $atts ) {
		ob_start();
		?>
		<div class="resort-lead-form-container resort-booking-container" style="max-width:600px;">
			<h3><?php _e( 'Unlock Exclusive Offers', 'resort-manager' ); ?></h3>
			<p><?php _e( 'Join our elite guest list to receive seasonal discounts and resort news directly in your inbox.', 'resort-manager' ); ?></p>
			<form id="resort-lead-form">
				<div class="resort-field">
					<label><?php _e( 'Your Name', 'resort-manager' ); ?></label>
					<input type="text" name="guest_name" required>
				</div>
				<div class="resort-field">
					<label><?php _e( 'Email Address', 'resort-manager' ); ?></label>
					<input type="email" name="guest_email" required>
				</div>
				<div style="margin-top:20px;">
					<button type="submit" class="resort-btn" style="width:100%;"><?php _e( 'Get My Invites', 'resort-manager' ); ?></button>
				</div>
				<div id="lead-form-message" style="margin-top:15px; text-align:center;"></div>
			</form>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('#resort-lead-form').on('submit', function(e) {
				e.preventDefault();
				const form = $(this);
				const btn = form.find('button');
				const data = {
					action: 'resort_submit_lead',
					nonce: '<?php echo wp_create_nonce("resort_booking_nonce"); ?>',
					guest_name: form.find('[name="guest_name"]').val(),
					guest_email: form.find('[name="guest_email"]').val()
				};

				btn.prop('disabled', true).text('Submitting...');

				$.post('<?php echo admin_url("admin-ajax.php"); ?>', data, function(res) {
					if (res.success) {
						form.html('<div style="color:green; padding:20px;">' + res.data.message + '</div>');
					} else {
						$('#lead-form-message').text(res.data.message).css('color', 'red');
						btn.prop('disabled', false).text('Get My Invites');
					}
				});
			});
		});
		</script>
		<?php
		return ob_get_clean();
	}

	public function render_room_calendar( $atts ) {
		$atts = shortcode_atts( [
			'id' => 0,
		], $atts );

		$room_id = intval( $atts['id'] );
		if ( ! $room_id ) return '';

		global $wpdb;
		$table = $wpdb->prefix . 'resort_availability';
		$start = date('Y-m-d');
		$end = date('Y-m-d', strtotime('+30 days'));

		$blocks = $wpdb->get_results( $wpdb->prepare(
			"SELECT date, status FROM $table WHERE room_id = %d AND date >= %s AND date <= %s",
			$room_id, $start, $end
		) );

		$availability = [];
		foreach ( $blocks as $b ) {
			$availability[$b->date] = $b->status;
		}

		ob_start();
		?>
		<div class="resort-room-calendar-wrap resort-booking-container" style="max-width:500px;">
			<h4><?php _e( 'Availability - Next 30 Days', 'resort-manager' ); ?></h4>
			<div class="resort-calendar-mini-grid" style="display:grid; grid-template-columns: repeat(7, 1fr); gap:5px; margin-top:15px;">
				<?php
				for ( $i = 0; $i < 30; $i++ ) {
					$date = date('Y-m-d', strtotime("+$i days"));
					$status = $availability[$date] ?? 'available';
					$color = ($status === 'available') ? '#46b450' : '#d63638';
					if ($status === 'sync') $color = '#636e72';
					?>
					<div title="<?php echo $date; ?>" style="background:<?php echo $color; ?>; height:30px; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:10px;">
						<?php echo date('j', strtotime($date)); ?>
					</div>
					<?php
				}
				?>
			</div>
			<div style="margin-top:15px; font-size:11px; display:flex; gap:15px; justify-content:center;">
				<span><span style="display:inline-block; width:10px; height:10px; background:#46b450; border-radius:2px;"></span> Available</span>
				<span><span style="display:inline-block; width:10px; height:10px; background:#d63638; border-radius:2px;"></span> Booked</span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_reviews( $atts ) {
		$reviews = get_posts( [ 'post_type' => 'review', 'numberposts' => 10, 'post_status' => 'publish' ] );
		ob_start();
		?>
		<div class="resort-reviews-section resort-booking-container">
			<h3><?php _e( 'Guest Memories', 'resort-manager' ); ?></h3>

			<div class="review-filters" style="margin-bottom:30px;">
				<button class="resort-btn-small" style="background:var(--resort-primary);"><?php _e( 'Most Recent', 'resort-manager' ); ?></button>
				<button class="resort-btn-small" style="background:#eee; color:#333; margin-left:10px;"><?php _e( 'Top Rated', 'resort-manager' ); ?></button>
			</div>

			<?php if ( empty( $reviews ) ) : ?>
				<p><?php _e( 'No stories shared yet. Be the first after your stay!', 'resort-manager' ); ?></p>
			<?php else : ?>
				<div class="reviews-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
				<?php foreach ( $reviews as $review ) :
					$rating = get_post_meta( $review->ID, '_resort_rating', true ) ?: 5;
					?>
					<div class="resort-review-card" style="padding: 25px; margin-bottom: 0;">
						<div class="stars" style="color:var(--resort-accent); margin-bottom:10px;">
							<?php for($i=0; $i<$rating; $i++) echo '★'; ?>
						</div>
						<h4 style="margin:0 0 10px 0;"><?php echo esc_html( $review->post_title ); ?></h4>
						<div style="font-style:italic; color:var(--resort-muted); font-size:14px;">
							<?php echo wp_kses_post( $review->post_content ); ?>
						</div>
					</div>
				<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
