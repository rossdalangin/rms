<div class="resort-guest-info">
	<h3><?php _e( 'Personalize Your Stay', 'resort-manager' ); ?></h3>
	<p><?php _e( 'Please provide your details so we can prepare for your arrival.', 'resort-manager' ); ?></p>
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
			<label for="meal_preference"><?php _e( 'Meal Preferences', 'resort-manager' ); ?></label>
			<select name="meal_preference">
				<option value="none">Standard</option>
				<option value="vegan">Vegan</option>
				<option value="vegetarian">Vegetarian</option>
				<option value="gluten_free">Gluten Free</option>
			</select>
		</div>
		<div class="resort-field">
			<label for="special_requests"><?php _e( 'Special Requests', 'resort-manager' ); ?></label>
			<textarea id="resort-special-requests" name="special_requests" placeholder="Honeymoon, Anniversary, Late arrival..."></textarea>
		</div>
		<div class="resort-field" style="background:#fdfcfb; padding:20px; border:1px solid #eee; margin-top:20px;">
			<label>
				<input type="checkbox" name="marketing_optin" checked>
				<?php _e( 'Send me exclusive tropical offers and resort news (Mailchimp).', 'resort-manager' ); ?>
			</label>
		</div>
		<div class="resort-field" style="background:#fdfcfb; padding:20px; border:1px solid #eee; margin-top:10px;">
			<label>
				<input type="checkbox" name="digital_waiver" required>
				<?php _e( 'I agree to the Resort Terms of Service and Liability Waiver.', 'resort-manager' ); ?>
			</label>
			<p><small><a href="#" id="resort-view-waiver-link"><?php _e( 'Read Liability Waiver', 'resort-manager' ); ?></a></small></p>
		</div>

		<!-- Waiver Modal -->
		<div id="resort-waiver-modal" class="resort-modal" style="display:none;">
			<div class="resort-modal-content">
				<span class="close-modal">&times;</span>
				<h3><?php _e( 'Resort Waiver & Terms', 'resort-manager' ); ?></h3>
				<div style="max-height: 400px; overflow-y: auto; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd; white-space: pre-wrap;" id="resort-waiver-content-area"></div>
				<button type="button" class="resort-btn close-modal" style="margin-top:20px;"><?php _e( 'Close', 'resort-manager' ); ?></button>
			</div>
		</div>
		<button type="submit" class="resort-btn" style="margin-top:20px;"><?php _e( 'Continue to Payment', 'resort-manager' ); ?></button>
	</form>
</div>
