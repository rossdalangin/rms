<?php
namespace ResortManager\Frontend;

class Shortcodes {
	public function __construct() {
		add_shortcode( 'resort_booking', [ $this, 'render_booking_engine' ] );
	}

	public function render_booking_engine( $atts ) {
		ob_start();
		?>
		<div id="resort-booking-app" class="resort-booking-container">
			<div class="resort-step-indicator">
				<span class="step active" data-step="1">1. Search</span>
				<span class="step" data-step="2">2. Select</span>
				<span class="step" data-step="3">3. Details</span>
				<span class="step" data-step="4">4. Payment</span>
			</div>

			<div id="resort-step-content">
				<div id="resort-step-1" class="resort-step">
					<?php include RESORT_MANAGER_PATH . 'templates/search-interface.php'; ?>
				</div>
				<div id="resort-step-2" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/room-selection.php'; ?>
				</div>
				<div id="resort-step-3" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/guest-info.php'; ?>
				</div>
				<div id="resort-step-4" class="resort-step" style="display:none;">
					<?php include RESORT_MANAGER_PATH . 'templates/payment-confirmation.php'; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
