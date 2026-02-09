<div id="resort-payment-screen" class="resort-payment">
	<h3><?php _e( 'Payment', 'resort-manager' ); ?></h3>
	<div class="booking-summary">
		<!-- Summary content -->
	</div>
	<div class="resort-coupon-section">
		<input type="text" id="resort-coupon-code" placeholder="Coupon Code">
		<button type="button" id="resort-apply-coupon" class="resort-btn">Apply</button>
		<div id="coupon-message"></div>
	</div>
	<div class="payment-methods">
		<label>
			<input type="radio" name="payment_method" value="stripe" checked>
			Stripe
		</label>
		<label>
			<input type="radio" name="payment_method" value="paypal">
			PayPal
		</label>
		<label>
			<input type="radio" name="payment_method" value="offline">
			Pay at Resort (Offline)
		</label>
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
