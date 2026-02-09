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
		<div class="wrap resort-onboarding-wrap resort-admin-card" style="max-width: 900px; margin: 40px auto;">
			<div style="text-align: center; margin-bottom: 50px;">
				<div class="tropical-icon" style="display:inline-block; padding:20px; background:var(--resort-light-teal); border-radius:50%; margin-bottom:20px;">
					<span class="dashicons dashicons-palmtree" style="font-size: 80px; width: 80px; height: 80px; color: var(--resort-teal);"></span>
				</div>
				<h1><?php _e( 'Welcome to LuxeResort Manager', 'resort-manager' ); ?></h1>
				<p style="font-size: 20px; color: var(--resort-muted); max-width: 600px; margin: 0 auto;"><?php _e( 'The definitive 5-star hospitality solution. Let’s get your oasis ready for guests.', 'resort-manager' ); ?></p>
			</div>

			<div class="onboarding-steps" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 50px;">
				<div class="step-card resort-admin-card" style="margin-bottom:0; border-top: 4px solid var(--resort-sand);">
					<h3 style="margin-top: 0;"><span style="color: var(--resort-sand);">1.</span> <?php _e( 'Paradise Configuration', 'resort-manager' ); ?></h3>
					<p><?php _e( 'Set your currency, timezones, and secure your paradise with Stripe or PayPal.', 'resort-manager' ); ?></p>
					<a href="<?php echo admin_url('admin.php?page=resort-settings'); ?>" class="button button-primary" style="background:var(--resort-teal);"><?php _e( 'Launch Settings', 'resort-manager' ); ?></a>
				</div>
				<div class="step-card resort-admin-card" style="margin-bottom:0; border-top: 4px solid var(--resort-coral);">
					<h3 style="margin-top: 0;"><span style="color: var(--resort-coral);">2.</span> <?php _e( 'Inventory & Suites', 'resort-manager' ); ?></h3>
					<p><?php _e( 'Define your villas and amenities. Create the perfect stay for every guest.', 'resort-manager' ); ?></p>
					<a href="<?php echo admin_url('edit.php?post_type=accommodation'); ?>" class="button button-primary" style="background:var(--resort-teal);"><?php _e( 'Manage Suites', 'resort-manager' ); ?></a>
				</div>
			</div>

			<div class="quick-start-box" style="background: var(--resort-light-teal); padding: 40px; border-radius: var(--resort-radius); text-align: center; border: 2px dashed var(--resort-teal);">
				<h2 style="font-family:'Playfair Display', serif; color: var(--resort-teal);"><?php _e( 'The One-Click Tropical Setup', 'resort-manager' ); ?></h2>
				<p style="font-size:16px;"><?php _e( 'New to LuxeResort? We can automatically build your rooms, services, and guest pages in seconds.', 'resort-manager' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'resort_onboarding_nonce' ); ?>
					<button type="submit" name="resort_one_click_setup" class="button button-primary button-hero" style="background: var(--resort-coral); border-color: var(--resort-coral); box-shadow: 0 4px 0 #cc6633;"><?php _e( 'Build My Paradise', 'resort-manager' ); ?></button>
				</form>
				<p style="margin-top:15px; color:var(--resort-muted);"><small><?php _e( 'Disclaimer: This replaces current demo content with fresh tropical samples.', 'resort-manager' ); ?></small></p>
			</div>

			<div style="margin-top: 40px; text-align: center; border-top: 1px solid #eee; padding-top: 20px;">
				<p><?php _e( 'Need help?', 'resort-manager' ); ?> <a href="<?php echo RESORT_MANAGER_URL . 'MANUAL.md'; ?>" target="_blank"><?php _e( 'Read the full manual', 'resort-manager' ); ?></a></p>
			</div>
		</div>
		<?php
	}
}
