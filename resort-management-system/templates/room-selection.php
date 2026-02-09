<div class="resort-room-results">
	<h3><?php _e( 'Available Accommodations', 'resort-manager' ); ?></h3>
	<div id="resort-rooms-grid" class="resort-grid">
		<!-- Dynamic content -->
	</div>
</div>

<template id="resort-room-card-template">
	<div class="resort-room-card">
		<div class="room-image">
			<img src="" alt="">
		</div>
		<div class="room-details">
			<h4 class="room-title"></h4>
			<p class="room-description"></p>
			<div class="room-meta">
				<span class="room-capacity"></span>
				<span class="room-price"></span>
			</div>
			<button type="button" class="resort-btn select-room-btn"><?php _e( 'Select Room', 'resort-manager' ); ?></button>
		</div>
	</div>
</template>
