<div class="resort-services-selection">
	<h3><?php _e( 'Enhance Your Stay', 'resort-manager' ); ?></h3>
	<p><?php _e( 'Select additional services and extras to make your stay even more memorable.', 'resort-manager' ); ?></p>
	<div id="resort-services-list" class="resort-services-list">
		<!-- Dynamic content -->
	</div>
	<button type="button" id="resort-services-next" class="resort-btn"><?php _e( 'Continue', 'resort-manager' ); ?></button>
</div>

<template id="resort-service-item-template">
	<div class="resort-service-item" style="display:flex; justify-content:space-between; align-items:center; padding:15px; border:1px solid #eee; margin-bottom:10px;">
		<div class="service-info">
			<h4 class="service-title" style="margin:0;"></h4>
			<span class="service-price" style="color:#c5a059;"></span>
		</div>
		<div class="service-action">
			<input type="checkbox" class="resort-service-checkbox">
		</div>
	</div>
</template>
