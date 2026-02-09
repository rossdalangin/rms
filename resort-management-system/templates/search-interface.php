<div class="resort-search-form">
	<div class="resort-field">
		<label for="checkin"><?php _e( 'Check-in Date', 'resort-manager' ); ?></label>
		<input type="date" id="resort-checkin" name="checkin" min="<?php echo date( 'Y-m-d' ); ?>">
	</div>
	<div class="resort-field">
		<label for="checkout"><?php _e( 'Check-out Date', 'resort-manager' ); ?></label>
		<input type="date" id="resort-checkout" name="checkout">
	</div>
	<div class="resort-field">
		<label for="guests"><?php _e( 'Guests', 'resort-manager' ); ?></label>
		<select id="resort-guests" name="guests">
			<option value="1">1 Guest</option>
			<option value="2">2 Guests</option>
			<option value="3">3 Guests</option>
			<option value="4">4 Guests</option>
			<option value="5+">5+ Guests</option>
		</select>
	</div>
	<button type="button" id="resort-search-btn" class="resort-btn"><?php _e( 'Search Availability', 'resort-manager' ); ?></button>
</div>
