<div class="resort-intro">
	<h2><?php _e( 'Plan Your Perfect Escape', 'resort-manager' ); ?></h2>
	<p><?php _e( 'Select your preferred dates to discover our exclusive accommodations and seasonal offers.', 'resort-manager' ); ?></p>
</div>
<div class="resort-search-form" style="margin-bottom: 30px;">
	<div class="resort-field" style="margin-bottom: 0;">
		<label for="checkin"><?php _e( 'Arrival', 'resort-manager' ); ?></label>
		<input type="date" id="resort-checkin" name="checkin" min="<?php echo date( 'Y-m-d' ); ?>">
	</div>
	<div class="resort-field" style="margin-bottom: 0;">
		<label for="checkout"><?php _e( 'Departure', 'resort-manager' ); ?></label>
		<input type="date" id="resort-checkout" name="checkout">
	</div>
	<div class="resort-field" style="margin-bottom: 0;">
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

<div class="resort-search-filters" style="margin-top: 35px; display: flex; gap: 20px; justify-content: center; align-items: center; background: #f0fafa; padding: 20px; border-radius: 12px; border: 1px solid #d1eaea;">
	<span style="font-weight: bold; font-size: 12px; color: var(--resort-primary);"><?php _e( 'FILTERS:', 'resort-manager' ); ?></span>
	<div class="resort-field" style="margin-bottom: 0;">
		<select id="resort-filter-price" name="filter_price">
			<option value=""><?php _e( 'Any Price', 'resort-manager' ); ?></option>
			<option value="0-5000">₱0 - ₱5,000</option>
			<option value="5000-10000">₱5,000 - ₱10,000</option>
			<option value="10000+">₱10,000+</option>
		</select>
	</div>
	<div class="resort-field" style="margin-bottom: 0;">
		<select id="resort-filter-type" name="filter_type">
			<option value=""><?php _e( 'All Types', 'resort-manager' ); ?></option>
			<option value="room"><?php _e( 'Standard Rooms', 'resort-manager' ); ?></option>
			<option value="package"><?php _e( 'Exclusive Packages', 'resort-manager' ); ?></option>
		</select>
	</div>
</div>
