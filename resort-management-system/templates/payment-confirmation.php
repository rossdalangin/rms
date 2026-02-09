<div id="resort-payment-screen" class="resort-payment">
	<h3><?php _e( 'Secure Your Reservation', 'resort-manager' ); ?></h3>
	<p><?php _e( 'Review your selection and choose a payment method to finalize your booking.', 'resort-manager' ); ?></p>
	<div class="booking-summary">
		<!-- Summary content -->
	</div>
	<div class="resort-coupon-section">
		<input type="text" id="resort-coupon-code" placeholder="Coupon Code">
		<button type="button" id="resort-apply-coupon" class="resort-btn">Apply</button>
		<div id="coupon-message"></div>
	</div>
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
