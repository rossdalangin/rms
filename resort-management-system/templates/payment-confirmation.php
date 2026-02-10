<div id="resort-payment-screen" class="resort-payment">
	<h3><?php _e( 'Secure Your Reservation', 'resort-manager' ); ?></h3>
	<p><?php _e( 'Review your selection and choose a payment method to finalize your booking.', 'resort-manager' ); ?></p>
	<div class="booking-summary">
		<!-- Summary content -->
	</div>
	<div class="resort-coupon-section" style="margin-bottom: 20px;">
		<input type="text" id="resort-coupon-code" placeholder="Coupon Code">
		<button type="button" id="resort-apply-coupon" class="resort-btn">Apply</button>
		<div id="coupon-message"></div>
	</div>

	<?php if ( is_user_logged_in() ) :
		$points = get_user_meta( get_current_user_id(), '_resort_loyalty_points', true ) ?: 0;
		if ( $points > 0 ) :
	?>
	<div class="resort-loyalty-redemption" style="background: #fff8e5; padding: 20px; border-radius: 12px; border: 1px solid #ffb900; margin-bottom: 20px;">
		<h4><?php _e( 'Redeem Loyalty Points', 'resort-manager' ); ?></h4>
		<p><?php printf( __( 'You have %d points available.', 'resort-manager' ), $points ); ?></p>
		<p><small><?php _e( '10 points = 1 PHP discount', 'resort-manager' ); ?></small></p>
		<div style="display:flex; gap:10px; align-items:center;">
			<input type="number" id="resort-redeem-points" max="<?php echo $points; ?>" min="0" placeholder="Points to use" style="width: 120px;">
			<button type="button" id="resort-apply-points" class="resort-btn" style="margin:0;"><?php _e( 'Redeem', 'resort-manager' ); ?></button>
		</div>
		<div id="points-message"></div>
	</div>
	<?php endif; endif; ?>
	<?php
	$stripe_enabled  = get_option( 'resort_payment_stripe_enabled', '1' ) === '1';
	$paypal_enabled  = get_option( 'resort_payment_paypal_enabled', '1' ) === '1';
	$offline_enabled = get_option( 'resort_payment_offline_enabled', '1' ) === '1';

	// Count enabled methods
	$enabled_count = ( $stripe_enabled ? 1 : 0 ) + ( $paypal_enabled ? 1 : 0 ) + ( $offline_enabled ? 1 : 0 );

	// If only offline or none are enabled, we might skip the UI but still need a default radio for the JS to pick up
	$hide_payment_ui = ($enabled_count <= 1);
	?>
	<div class="payment-methods" <?php echo $hide_payment_ui ? 'style="display:none;"' : ''; ?>>
		<h4><?php _e( 'Select Payment Method', 'resort-manager' ); ?></h4>
		<?php if ( $stripe_enabled ) : ?>
		<label>
			<input type="radio" name="payment_method" value="stripe" checked>
			Stripe
		</label>
		<?php endif; ?>

		<?php if ( $paypal_enabled ) : ?>
		<label>
			<input type="radio" name="payment_method" value="paypal" <?php echo !$stripe_enabled ? 'checked' : ''; ?>>
			PayPal
		</label>
		<?php endif; ?>

		<?php if ( $offline_enabled || $enabled_count === 0 ) : ?>
		<label>
			<input type="radio" name="payment_method" value="offline" <?php echo $enabled_count === 0 || (!$stripe_enabled && !$paypal_enabled) ? 'checked' : ''; ?>>
			Pay at Resort (Offline)
		</label>
		<?php endif; ?>
	</div>
	<button type="button" id="resort-complete-booking" class="resort-btn"><?php _e( 'Confirm & Pay', 'resort-manager' ); ?></button>
</div>

<div id="resort-confirmation-screen" class="resort-confirmation" style="display:none;">
	<div class="success-message">
		<h3><?php _e( 'Booking Confirmed!', 'resort-manager' ); ?></h3>
		<p><?php _e( 'Thank you for your reservation. A confirmation email has been sent.', 'resort-manager' ); ?></p>
		<div class="booking-details">
			<p><strong><?php _e( 'Booking ID:', 'resort-manager' ); ?></strong> <span id="resort-conf-id"></span></p>
		</div>
		<button type="button" class="resort-btn" onclick="window.location.reload();"><?php _e( 'Make Another Booking', 'resort-manager' ); ?></button>
	</div>
</div>
