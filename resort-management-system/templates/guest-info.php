<div class="resort-guest-info">
	<h3><?php _e( 'Guest Details', 'resort-manager' ); ?></h3>
	<form id="resort-guest-form">
		<div class="resort-field">
			<label for="first_name"><?php _e( 'First Name', 'resort-manager' ); ?></label>
			<input type="text" id="resort-first-name" name="first_name" required>
		</div>
		<div class="resort-field">
			<label for="last_name"><?php _e( 'Last Name', 'resort-manager' ); ?></label>
			<input type="text" id="resort-last-name" name="last_name" required>
		</div>
		<div class="resort-field">
			<label for="email"><?php _e( 'Email Address', 'resort-manager' ); ?></label>
			<input type="email" id="resort-email" name="email" required>
		</div>
		<div class="resort-field">
			<label for="phone"><?php _e( 'Phone Number', 'resort-manager' ); ?></label>
			<input type="tel" id="resort-phone" name="phone">
		</div>
		<div class="resort-field">
			<label for="special_requests"><?php _e( 'Special Requests', 'resort-manager' ); ?></label>
			<textarea id="resort-special-requests" name="special_requests"></textarea>
		</div>
		<button type="submit" class="resort-btn"><?php _e( 'Continue to Payment', 'resort-manager' ); ?></button>
	</form>
</div>
