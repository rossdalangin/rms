<?php
namespace ResortManager\Admin;

class Onboarding {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_onboarding_page' ], 10 );
	}

	public function add_onboarding_page() {
		// This will be handled as the main landing page by Settings.php registration
	}

	public function render_onboarding_page() {
		if ( isset( $_POST['resort_one_click_setup'] ) && check_admin_referer( 'resort_onboarding_nonce' ) ) {
			\ResortManager\Admin\Maintenance::install_sample_data();
			\ResortManager\Admin\Maintenance::create_default_pages();
			echo '<div class="updated"><p>' . __( 'Success! Sample data installed and default pages created.', 'resort-manager' ) . '</p></div>';
		}

		?>
		<div class="wrap resort-onboarding-wrap" style="max-width: 800px; margin: 40px auto; background: #fff; padding: 40px; border: 1px solid #ccd0d4; border-radius: 8px; box-shadow: 0 5px 25px rgba(0,0,0,0.05);">
			<div style="text-align: center; margin-bottom: 40px;">
				<span class="dashicons dashicons-palmtree" style="font-size: 60px; width: 60px; height: 60px; color: #c5a059;"></span>
				<h1 style="font-family: 'Playfair Display', serif; font-size: 32px; margin: 20px 0 10px;"><?php _e( 'Welcome to LuxeResort Manager', 'resort-manager' ); ?></h1>
				<p style="font-size: 18px; color: #636e72;"><?php _e( 'Let’s get your premium resort website up and running in minutes.', 'resort-manager' ); ?></p>
			</div>

			<div class="onboarding-steps" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px;">
				<div class="step-card" style="padding: 20px; border: 1px solid #f1f1f1; border-radius: 4px;">
					<h3 style="margin-top: 0;"><span style="color: #c5a059;">1.</span> <?php _e( 'Configure Settings', 'resort-manager' ); ?></h3>
					<p><?php _e( 'Set your resort name, currency, and payment keys (Stripe/PayPal) to start accepting bookings.', 'resort-manager' ); ?></p>
					<a href="<?php echo admin_url('admin.php?page=resort-settings'); ?>" class="button"><?php _e( 'Go to Settings', 'resort-manager' ); ?></a>
				</div>
				<div class="step-card" style="padding: 20px; border: 1px solid #f1f1f1; border-radius: 4px;">
					<h3 style="margin-top: 0;"><span style="color: #c5a059;">2.</span> <?php _e( 'Manage Inventory', 'resort-manager' ); ?></h3>
					<p><?php _e( 'Add your rooms, suites, and villas. Define capacity, amenities, and seasonal pricing rules.', 'resort-manager' ); ?></p>
					<a href="<?php echo admin_url('edit.php?post_type=accommodation'); ?>" class="button"><?php _e( 'Manage Rooms', 'resort-manager' ); ?></a>
				</div>
			</div>

			<div style="background: #fdfcfb; padding: 30px; border: 1px solid #c5a059; border-radius: 4px; text-align: center;">
				<h2 style="margin-top: 0;"><?php _e( 'Quick Start: One-Click Setup', 'resort-manager' ); ?></h2>
				<p><?php _e( 'New to the plugin? Click below to automatically install sample rooms, services, and create all necessary pages.', 'resort-manager' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'resort_onboarding_nonce' ); ?>
					<button type="submit" name="resort_one_click_setup" class="button button-primary button-hero" style="background: #c5a059; border-color: #c5a059;"><?php _e( 'Install Demo Content', 'resort-manager' ); ?></button>
				</form>
				<p><small><?php _e( 'Note: This will reset any existing LuxeResort data.', 'resort-manager' ); ?></small></p>
			</div>

			<div style="margin-top: 40px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
				<p><?php _e( 'Need help?', 'resort-manager' ); ?> <a href="<?php echo RESORT_MANAGER_URL . 'MANUAL.md'; ?>" target="_blank"><?php _e( 'Read the full manual', 'resort-manager' ); ?></a></p>
			</div>
		</div>
		<?php
	}
}
