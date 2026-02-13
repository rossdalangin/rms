<?php
namespace ResortManager\Admin;

class Settings {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_menu_page() {
		add_menu_page(
			__( 'LuxeResort Manager', 'resort-manager' ),
			'LuxeResort',
			'edit_posts', // Accessible by staff
			'resort-manager',
			[ $this, 'render_onboarding_proxy' ],
			'dashicons-palmtree',
			25
		);

		// The first submenu is the same as the parent, we can rename it.
		add_submenu_page(
			'resort-manager',
			__( 'Getting Started', 'resort-manager' ),
			__( 'Getting Started', 'resort-manager' ),
			'edit_posts',
			'resort-manager',
			[ $this, 'render_onboarding_proxy' ]
		);

		add_submenu_page(
			'resort-manager',
			__( 'Settings', 'resort-manager' ),
			__( 'Settings', 'resort-manager' ),
			'manage_options', // Only admins
			'resort-settings',
			[ $this, 'render_settings_page' ]
		);

		add_submenu_page(
			'resort-manager',
			__( 'Guest Profiles', 'resort-manager' ),
			__( 'Guest Profiles', 'resort-manager' ),
			'edit_posts',
			'resort-guests',
			[ $this, 'render_guest_profiles_page' ]
		);

		add_submenu_page(
			'resort-manager',
			__( 'Activity Logs', 'resort-manager' ),
			__( 'Activity Logs', 'resort-manager' ),
			'edit_posts',
			'resort-logs',
			[ $this, 'render_logs_page' ]
		);

		add_submenu_page(
			'resort-manager',
			__( 'Shortcode Helper', 'resort-manager' ),
			__( 'Shortcode Helper', 'resort-manager' ),
			'edit_posts',
			'resort-shortcodes',
			[ $this, 'render_shortcode_helper_page' ]
		);
	}

	public function register_settings() {
		register_setting( 'resort_settings_group', 'resort_name' );
		register_setting( 'resort_settings_group', 'resort_logo' );
		register_setting( 'resort_settings_group', 'resort_currency' );
		register_setting( 'resort_settings_group', 'resort_currency_symbol_pos' );
		register_setting( 'resort_settings_group', 'resort_min_nights' );
		register_setting( 'resort_settings_group', 'resort_max_nights' );
		register_setting( 'resort_settings_group', 'resort_book_ahead_days' );
		register_setting( 'resort_settings_group', 'resort_cutoff_time' );
		register_setting( 'resort_settings_group', 'resort_deposit_percentage' );
		register_setting( 'resort_settings_group', 'resort_tax_rate' );
		register_setting( 'resort_settings_group', 'resort_cleaning_fee' );
		register_setting( 'resort_settings_group', 'resort_base_fee' );
		register_setting( 'resort_settings_group', 'resort_waiver_text' );
		register_setting( 'resort_settings_group', 'resort_terms_text' );
		register_setting( 'resort_settings_group', 'resort_email_template_confirmation' );
		register_setting( 'resort_settings_group', 'resort_email_template_pre_arrival' );
		register_setting( 'resort_settings_group', 'resort_email_template_post_departure' );
		register_setting( 'resort_settings_group', 'resort_email_template_abandoned' );
		register_setting( 'resort_settings_group', 'resort_mailchimp_api_key' );
		register_setting( 'resort_settings_group', 'resort_mailchimp_list_id' );
		register_setting( 'resort_settings_group', 'resort_hubspot_api_key' );
		register_setting( 'resort_settings_group', 'resort_zoho_client_id' );
		register_setting( 'resort_settings_group', 'resort_zoho_client_secret' );
		register_setting( 'resort_settings_group', 'resort_zoho_refresh_token' );
		register_setting( 'resort_settings_group', 'resort_webhook_url' );
		register_setting( 'resort_settings_group', 'resort_competitors' );
		register_setting( 'resort_settings_group', 'resort_twilio_sid' );
		register_setting( 'resort_settings_group', 'resort_twilio_token' );
		register_setting( 'resort_settings_group', 'resort_twilio_number' );
		register_setting( 'resort_settings_group', 'resort_google_calendar_id' );

		// Payment Gateways Enable/Disable
		register_setting( 'resort_settings_group', 'resort_payment_stripe_enabled' );
		register_setting( 'resort_settings_group', 'resort_payment_paypal_enabled' );
		register_setting( 'resort_settings_group', 'resort_payment_offline_enabled' );
		register_setting( 'resort_settings_group', 'resort_payment_woocommerce_enabled' );

		// Stripe Settings
		register_setting( 'resort_settings_group', 'resort_stripe_publishable_key' );
		register_setting( 'resort_settings_group', 'resort_stripe_secret_key' );
		register_setting( 'resort_settings_group', 'resort_stripe_test_mode' );

		// PayPal Settings
		register_setting( 'resort_settings_group', 'resort_paypal_client_id' );
		register_setting( 'resort_settings_group', 'resort_paypal_secret' );
		register_setting( 'resort_settings_group', 'resort_paypal_test_mode' );

		add_settings_section(
			'resort_general_section',
			__( 'General Settings', 'resort-manager' ),
			[ $this, 'render_general_section_desc' ],
			'resort-settings'
		);

		add_settings_field(
			'resort_taxes_fees',
			__( 'Taxes & Fees', 'resort-manager' ),
			[ $this, 'render_taxes_fees_fields' ],
			'resort-settings',
			'resort_general_section'
		);

		add_settings_field(
			'resort_name',
			__( 'Resort Name', 'resort-manager' ),
			[ $this, 'render_name_field' ],
			'resort-settings',
			'resort_general_section'
		);

		add_settings_field(
			'resort_logo',
			__( 'Resort Logo URL', 'resort-manager' ),
			[ $this, 'render_logo_field' ],
			'resort-settings',
			'resort_general_section'
		);

		add_settings_field(
			'resort_currency_settings',
			__( 'Currency Display', 'resort-manager' ),
			[ $this, 'render_currency_settings' ],
			'resort-settings',
			'resort_general_section'
		);

		add_settings_field(
			'resort_booking_rules',
			__( 'Stay Duration Rules', 'resort-manager' ),
			[ $this, 'render_duration_rules' ],
			'resort-settings',
			'resort_general_section'
		);

		add_settings_field(
			'resort_deposit_policy',
			__( 'Deposit Policy (%)', 'resort-manager' ),
			[ $this, 'render_deposit_field' ],
			'resort-settings',
			'resort_general_section'
		);

		add_settings_field(
			'resort_waiver_settings',
			__( 'Waiver & Terms', 'resort-manager' ),
			[ $this, 'render_waiver_fields' ],
			'resort-settings',
			'resort_general_section'
		);

		add_settings_section(
			'resort_payments_section',
			__( 'Payment Gateway Configurations', 'resort-manager' ),
			[ $this, 'render_payments_section_desc' ],
			'resort-settings'
		);

		add_settings_field(
			'resort_payment_methods',
			__( 'Enabled Payment Methods', 'resort-manager' ),
			[ $this, 'render_payment_method_toggles' ],
			'resort-settings',
			'resort_payments_section'
		);

		add_settings_field(
			'resort_stripe_keys',
			__( 'Stripe API Keys', 'resort-manager' ),
			[ $this, 'render_stripe_fields' ],
			'resort-settings',
			'resort_payments_section'
		);

		add_settings_field(
			'resort_paypal_keys',
			__( 'PayPal API Keys', 'resort-manager' ),
			[ $this, 'render_paypal_fields' ],
			'resort-settings',
			'resort_payments_section'
		);

		add_settings_section(
			'resort_notifications_section',
			__( 'Notifications & Marketing', 'resort-manager' ),
			[ $this, 'render_notifications_section_desc' ],
			'resort-settings'
		);

		add_settings_field(
			'resort_email_templates',
			__( 'Email Templates', 'resort-manager' ),
			[ $this, 'render_email_templates_fields' ],
			'resort-settings',
			'resort_notifications_section'
		);

		add_settings_field(
			'resort_webhook_settings',
			__( 'Ecosystem Webhooks', 'resort-manager' ),
			[ $this, 'render_webhook_fields' ],
			'resort-settings',
			'resort_notifications_section'
		);

		add_settings_field(
			'resort_marketing_integrations',
			__( 'Marketing (API Keys)', 'resort-manager' ),
			[ $this, 'render_marketing_fields' ],
			'resort-settings',
			'resort_notifications_section'
		);

		add_settings_section(
			'resort_intelligence_section',
			__( 'Market Intelligence & Competitors', 'resort-manager' ),
			[ $this, 'render_intelligence_section_desc' ],
			'resort-settings'
		);

		add_settings_field(
			'resort_competitors',
			__( 'Competitor Resorts', 'resort-manager' ),
			[ $this, 'render_competitor_fields' ],
			'resort-settings',
			'resort_intelligence_section'
		);
	}

	public function render_general_section_desc() {
		echo '<p>' . __( 'Basic identification and operational rules for your resort. These settings affect the primary display and booking constraints across the site.', 'resort-manager' ) . '</p>';
		echo '<p><strong>' . __( 'Example:', 'resort-manager' ) . '</strong> ' . __( 'Setting "Min Nights" to 3 ensures that all bookings must be at least 3 nights long, which is common for luxury villas during peak season.', 'resort-manager' ) . '</p>';
	}

	public function render_payments_section_desc() {
		echo '<p>' . __( 'Configure how you accept money. You can enable multiple gateways to give guests choice, or just one to simplify the flow. If only "Offline" is enabled, the system automatically skips the payment choice step for a faster checkout.', 'resort-manager' ) . '</p>';
		echo '<p><strong>' . __( 'Pro Tip:', 'resort-manager' ) . '</strong> ' . __( 'Enable "Simulation Mode" by leaving the Secret Key empty if you want to test the checkout process without making real transactions.', 'resort-manager' ) . '</p>';
	}

	public function render_notifications_section_desc() {
		echo '<p>' . __( 'Manage guest communications and marketing integrations. Customize the confirmation email and connect your resort to Mailchimp or Twilio for automated engagement.', 'resort-manager' ) . '</p>';
		echo '<p><strong>' . __( 'Integration Example:', 'resort-manager' ) . '</strong> ' . __( 'Connect Twilio to send an instant "Aloha!" SMS to guests as soon as their booking is confirmed.', 'resort-manager' ) . '</p>';
	}

	public function render_intelligence_section_desc() {
		echo '<p>' . __( 'Configure your local competitors to receive market alerts and dynamic pricing suggestions. This data is used by the Smart Pricing engine to help you stay competitive.', 'resort-manager' ) . '</p>';
	}

	public function render_payment_method_toggles() {
		$stripe = get_option( 'resort_payment_stripe_enabled', '1' );
		$paypal = get_option( 'resort_payment_paypal_enabled', '1' );
		$offline = get_option( 'resort_payment_offline_enabled', '1' );
		?>
		<label>
			<input type="checkbox" name="resort_payment_stripe_enabled" value="1" <?php checked( $stripe, '1' ); ?>>
			<?php _e( 'Enable Stripe', 'resort-manager' ); ?>
		</label><br>
		<label>
			<input type="checkbox" name="resort_payment_paypal_enabled" value="1" <?php checked( $paypal, '1' ); ?>>
			<?php _e( 'Enable PayPal', 'resort-manager' ); ?>
		</label><br>
		<label>
			<input type="checkbox" name="resort_payment_offline_enabled" value="1" <?php checked( $offline, '1' ); ?>>
			<?php _e( 'Enable Offline (Pay at Resort)', 'resort-manager' ); ?>
		</label><br>
		<label>
			<input type="checkbox" name="resort_payment_woocommerce_enabled" value="1" <?php checked( get_option('resort_payment_woocommerce_enabled'), '1' ); ?> <?php echo !class_exists('WooCommerce') ? 'disabled' : ''; ?>>
			<?php _e( 'Enable WooCommerce Checkout (Use any WC Gateway)', 'resort-manager' ); ?>
			<?php if(!class_exists('WooCommerce')) echo '<small style="color:red;"> (' . __('WooCommerce not detected', 'resort-manager') . ')</small>'; ?>
		</label>
		<?php
	}

	public function render_stripe_fields() {
		$pk = get_option( 'resort_stripe_publishable_key', '' );
		$sk = get_option( 'resort_stripe_secret_key', '' );
		$test = get_option( 'resort_stripe_test_mode', '1' );
		?>
		<label><?php _e( 'Publishable Key:', 'resort-manager' ); ?></label><br>
		<input type="text" name="resort_stripe_publishable_key" value="<?php echo esc_attr( $pk ); ?>" class="regular-text"><br><br>
		<label><?php _e( 'Secret Key:', 'resort-manager' ); ?></label><br>
		<input type="password" name="resort_stripe_secret_key" value="<?php echo esc_attr( $sk ); ?>" class="regular-text"><br><br>
		<label>
			<input type="checkbox" name="resort_stripe_test_mode" value="1" <?php checked( $test, '1' ); ?>>
			<?php _e( 'Enable Test Mode', 'resort-manager' ); ?>
		</label>
		<?php
	}

	public function render_email_templates_fields() {
		$conf = get_option( 'resort_email_template_confirmation', '<h1>Booking Confirmed!</h1><p>Thank you for choosing LuxeResort. Your ID is {booking_id}.</p>' );
		$pre = get_option( 'resort_email_template_pre_arrival', '<h1>See You Soon!</h1><p>We are excited to welcome you to paradise in 2 days. Need anything before you arrive?</p>' );
		$post = get_option( 'resort_email_template_post_departure', '<h1>Thank You for Staying!</h1><p>We hope you enjoyed your tropical escape. Would you mind leaving us a review?</p>' );
		$abandoned = get_option( 'resort_email_template_abandoned', '<h1>Still Interested?</h1><p>We noticed you didn\'t finish your booking. Your paradise sanctuary is still waiting!</p>' );

		echo '<h4>' . __( '1. Confirmation Email', 'resort-manager' ) . '</h4>';
		wp_editor( $conf, 'resort_email_template_confirmation', [ 'textarea_rows' => 5 ] );

		echo '<br><h4>' . __( '2. Pre-Arrival Concierge (2 days prior)', 'resort-manager' ) . '</h4>';
		wp_editor( $pre, 'resort_email_template_pre_arrival', [ 'textarea_rows' => 5 ] );

		echo '<br><h4>' . __( '3. Post-Departure Feedback (1 day after)', 'resort-manager' ) . '</h4>';
		wp_editor( $post, 'resort_email_template_post_departure', [ 'textarea_rows' => 5 ] );

		echo '<br><h4>' . __( '4. Abandoned Booking Reminder', 'resort-manager' ) . '</h4>';
		wp_editor( $abandoned, 'resort_email_template_abandoned', [ 'textarea_rows' => 5 ] );

		echo '<p class="description">' . __( 'Available placeholders: {booking_id}, {first_name}, {last_name}, {checkin}, {checkout}', 'resort-manager' ) . '</p>';
	}

	public function render_competitor_fields() {
		$competitors = get_option( 'resort_competitors' );
		if ( ! is_array( $competitors ) ) {
			$competitors = [
				[ 'name' => 'Blue Waters Resort', 'price' => 5200 ],
				[ 'name' => 'Sunset Sands Hotel', 'price' => 4800 ]
			];
		}
		?>
		<div id="resort-competitors-wrap">
			<?php foreach ( $competitors as $index => $comp ) : ?>
				<div class="competitor-row" style="margin-bottom: 10px; background: #f9f9f9; padding: 10px; border-radius: 4px;">
					<input type="text" name="resort_competitors[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $comp['name'] ); ?>" placeholder="Competitor Name">
					<input type="number" name="resort_competitors[<?php echo $index; ?>][price]" value="<?php echo esc_attr( $comp['price'] ); ?>" placeholder="Avg Price">
				</div>
			<?php endforeach; ?>
		</div>
		<p class="description"><?php _e( 'Add your main competitors and their approximate average nightly rates.', 'resort-manager' ); ?></p>
		<?php
	}

	public function render_webhook_fields() {
		$url = get_option( 'resort_webhook_url', '' );
		$gcal_id = get_option( 'resort_google_calendar_id', '' );
		?>
		<div style="margin-bottom: 20px;">
			<label><strong><?php _e( 'Webhook URL:', 'resort-manager' ); ?></strong></label><br>
			<input type="url" name="resort_webhook_url" value="<?php echo esc_attr($url); ?>" class="regular-text" placeholder="https://hooks.zapier.com/...">
			<p class="description"><?php _e( 'Enter a URL to receive a POST request with booking data every time a reservation is confirmed. Use this to connect with Zapier, Make, or Slack.', 'resort-manager' ); ?></p>
		</div>
		<div>
			<label><strong><?php _e( 'Public Google Calendar ID:', 'resort-manager' ); ?></strong></label><br>
			<input type="text" name="resort_google_calendar_id" value="<?php echo esc_attr($gcal_id); ?>" class="regular-text" placeholder="your-resort@gmail.com">
			<p class="description"><?php _e( 'Enter your public Google Calendar ID to provide guests with a "Subscribe to Resort Events" link.', 'resort-manager' ); ?></p>
		</div>
		<?php
	}

	public function render_marketing_fields() {
		$mc_key = get_option( 'resort_mailchimp_api_key', '' );
		$mc_list = get_option( 'resort_mailchimp_list_id', '' );
		$hs_key = get_option( 'resort_hubspot_api_key', '' );
		$zoho_cid = get_option( 'resort_zoho_client_id', '' );
		$zoho_secret = get_option( 'resort_zoho_client_secret', '' );
		$zoho_refresh = get_option( 'resort_zoho_refresh_token', '' );
		$tw_sid = get_option( 'resort_twilio_sid', '' );
		$tw_token = get_option( 'resort_twilio_token', '' );
		$tw_num = get_option( 'resort_twilio_number', '' );
		?>
		<p><strong><?php _e( 'Mailchimp Integration', 'resort-manager' ); ?></strong></p>
		<label>API Key:</label><br>
		<input type="text" name="resort_mailchimp_api_key" value="<?php echo esc_attr($mc_key); ?>" class="regular-text"><br>
		<label>Audience (List) ID:</label><br>
		<input type="text" name="resort_mailchimp_list_id" value="<?php echo esc_attr($mc_list); ?>" class="regular-text"><br><br>

		<p><strong><?php _e( 'HubSpot CRM Integration', 'resort-manager' ); ?></strong></p>
		<label>HubSpot API Key (Private App Token):</label><br>
		<input type="password" name="resort_hubspot_api_key" value="<?php echo esc_attr($hs_key); ?>" class="regular-text"><br><br>

		<p><strong><?php _e( 'Zoho CRM Integration (OAuth)', 'resort-manager' ); ?></strong></p>
		<label>Client ID:</label><br>
		<input type="text" name="resort_zoho_client_id" value="<?php echo esc_attr($zoho_cid); ?>" class="regular-text"><br>
		<label>Client Secret:</label><br>
		<input type="password" name="resort_zoho_client_secret" value="<?php echo esc_attr($zoho_secret); ?>" class="regular-text"><br>
		<label>Refresh Token:</label><br>
		<input type="password" name="resort_zoho_refresh_token" value="<?php echo esc_attr($zoho_refresh); ?>" class="regular-text"><br><br>

		<p><strong><?php _e( 'Twilio SMS Integration', 'resort-manager' ); ?></strong></p>
		<label>Account SID:</label><br>
		<input type="text" name="resort_twilio_sid" value="<?php echo esc_attr($tw_sid); ?>" class="regular-text"><br>
		<label>Auth Token:</label><br>
		<input type="password" name="resort_twilio_token" value="<?php echo esc_attr($tw_token); ?>" class="regular-text"><br>
		<label>Twilio Number:</label><br>
		<input type="text" name="resort_twilio_number" value="<?php echo esc_attr($tw_num); ?>" class="regular-text">
		<?php
	}

	public function render_paypal_fields() {
		$cid = get_option( 'resort_paypal_client_id', '' );
		$secret = get_option( 'resort_paypal_secret', '' );
		$test = get_option( 'resort_paypal_test_mode', '1' );
		?>
		<label><?php _e( 'Client ID:', 'resort-manager' ); ?></label><br>
		<input type="text" name="resort_paypal_client_id" value="<?php echo esc_attr( $cid ); ?>" class="regular-text"><br><br>
		<label><?php _e( 'Secret:', 'resort-manager' ); ?></label><br>
		<input type="password" name="resort_paypal_secret" value="<?php echo esc_attr( $secret ); ?>" class="regular-text"><br><br>
		<label>
			<input type="checkbox" name="resort_paypal_test_mode" value="1" <?php checked( $test, '1' ); ?>>
			<?php _e( 'Enable Sandbox Mode', 'resort-manager' ); ?>
		</label>
		<?php
	}

	public function render_name_field() {
		$value = get_option( 'resort_name', '' );
		echo '<input type="text" name="resort_name" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	public function render_logo_field() {
		$value = get_option( 'resort_logo', '' );
		echo '<input type="url" name="resort_logo" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="https://example.com/logo.png">';
		if ( $value ) {
			echo '<br><img src="' . esc_url($value) . '" style="max-height: 50px; margin-top: 10px;">';
		}
	}

	public function render_currency_settings() {
		$currency = get_option( 'resort_currency', 'PHP' );
		$pos = get_option( 'resort_currency_symbol_pos', 'before' );
		?>
		<input type="text" name="resort_currency" value="<?php echo esc_attr($currency); ?>" placeholder="PHP" style="width: 80px;">
		<select name="resort_currency_symbol_pos">
			<option value="before" <?php selected($pos, 'before'); ?>><?php _e( 'Symbol Before ($100)', 'resort-manager' ); ?></option>
			<option value="after" <?php selected($pos, 'after'); ?>><?php _e( 'Symbol After (100 €)', 'resort-manager' ); ?></option>
		</select>
		<?php
	}

	public function render_waiver_fields() {
		$waiver = get_option( 'resort_waiver_text', 'Liability Release: By booking, you agree to waive all liability for tropical accidents...' );
		$terms = get_option( 'resort_terms_text', 'Standard Resort Terms: 1. No pets. 2. Quiet hours after 10 PM. 3. Full refund if canceled 7 days prior.' );
		?>
		<label><strong><?php _e( 'Liability Waiver:', 'resort-manager' ); ?></strong></label><br>
		<textarea name="resort_waiver_text" class="large-text" rows="4"><?php echo esc_textarea($waiver); ?></textarea><br><br>
		<label><strong><?php _e( 'Terms & Conditions:', 'resort-manager' ); ?></strong></label><br>
		<textarea name="resort_terms_text" class="large-text" rows="4"><?php echo esc_textarea($terms); ?></textarea>
		<?php
	}

	public function render_duration_rules() {
		$min = get_option( 'resort_min_nights', '1' );
		$max = get_option( 'resort_max_nights', '30' );
		$ahead = get_option( 'resort_book_ahead_days', '0' );
		$cutoff = get_option( 'resort_cutoff_time', '14:00' );
		?>
		<div style="margin-bottom: 10px;">
			<label><strong><?php _e( 'Min Nights:', 'resort-manager' ); ?></strong></label>
			<input type="number" name="resort_min_nights" value="<?php echo esc_attr( $min ); ?>" style="width: 60px;">
			<span class="description"><?php _e( '(e.g., 2 nights minimum)', 'resort-manager' ); ?></span>
		</div>
		<div style="margin-bottom: 10px;">
			<label><strong><?php _e( 'Max Nights:', 'resort-manager' ); ?></strong></label>
			<input type="number" name="resort_max_nights" value="<?php echo esc_attr( $max ); ?>" style="width: 60px;">
			<span class="description"><?php _e( '(e.g., limit stays to 14 days)', 'resort-manager' ); ?></span>
		</div>
		<div style="margin-bottom: 10px;">
			<label><strong><?php _e( 'Book Ahead (Days):', 'resort-manager' ); ?></strong></label>
			<input type="number" name="resort_book_ahead_days" value="<?php echo esc_attr( $ahead ); ?>" style="width: 60px;">
			<span class="description"><?php _e( '(e.g., "2" means guests must book at least 48 hours before arrival)', 'resort-manager' ); ?></span>
		</div>
		<div>
			<label><strong><?php _e( 'Same-Day Cut-off Time:', 'resort-manager' ); ?></strong></label>
			<input type="time" name="resort_cutoff_time" value="<?php echo esc_attr( $cutoff ); ?>">
			<span class="description"><?php _e( '(e.g., "14:00" means same-day bookings are disabled after 2 PM)', 'resort-manager' ); ?></span>
		</div>
		<?php
	}

	public function render_taxes_fees_fields() {
		$tax = get_option( 'resort_tax_rate', '0' );
		$cleaning = get_option( 'resort_cleaning_fee', '0' );
		$base = get_option( 'resort_base_fee', '0' );
		?>
		<div style="margin-bottom: 10px;">
			<label><strong><?php _e( 'Tax Rate (%):', 'resort-manager' ); ?></strong></label>
			<input type="number" name="resort_tax_rate" value="<?php echo esc_attr( $tax ); ?>" style="width: 80px;">
			<p class="description"><?php _e( 'Percentage tax applied to the subtotal (rooms + services).', 'resort-manager' ); ?></p>
		</div>
		<div style="margin-bottom: 10px;">
			<label><strong><?php _e( 'Cleaning Fee (Fixed):', 'resort-manager' ); ?></strong></label>
			<input type="number" name="resort_cleaning_fee" value="<?php echo esc_attr( $cleaning ); ?>" style="width: 100px;">
			<p class="description"><?php _e( 'A one-time fee per booking for cleaning.', 'resort-manager' ); ?></p>
		</div>
		<div>
			<label><strong><?php _e( 'Resort / Base Fee (Fixed):', 'resort-manager' ); ?></strong></label>
			<input type="number" name="resort_base_fee" value="<?php echo esc_attr( $base ); ?>" style="width: 100px;">
			<p class="description"><?php _e( 'A one-time mandatory fee per booking.', 'resort-manager' ); ?></p>
		</div>
		<?php
	}

	public function render_deposit_field() {
		$value = get_option( 'resort_deposit_percentage', '100' );
		?>
		<input type="number" name="resort_deposit_percentage" value="<?php echo esc_attr( $value ); ?>" style="width: 80px;"> %
		<p class="description">
			<?php _e( 'Define how much the guest pays immediately. 100% means full payment is required to confirm. 25% means they pay a quarter now and the rest later.', 'resort-manager' ); ?>
			<br><strong><?php _e( 'Example:', 'resort-manager' ); ?></strong> <?php _e( 'On a $1000 booking with a 50% policy, the guest pays $500 to secure the room.', 'resort-manager' ); ?>
		</p>
		<?php
	}

	public function render_onboarding_proxy() {
		$onboarding = new \ResortManager\Admin\Onboarding();
		$onboarding->render_onboarding_page();
	}

	public function render_shortcode_helper_page() {
		$shortcodes = [
			[
				'tag' => 'resort_booking',
				'desc' => __( 'Main 5-step booking engine. Usually placed on a dedicated "Book Now" page.', 'resort-manager' ),
				'atts' => []
			],
			[
				'tag' => 'resort_rooms_grid',
				'desc' => __( 'Displays a beautiful responsive grid of all your accommodations.', 'resort-manager' ),
				'atts' => []
			],
			[
				'tag' => 'resort_guest_dashboard',
				'desc' => __( 'Private portal for guests to manage stays, request services, and view invoices.', 'resort-manager' ),
				'atts' => []
			],
			[
				'tag' => 'resort_reviews',
				'desc' => __( 'Tropical modern review slider/grid showing approved guest memories.', 'resort-manager' ),
				'atts' => [
					'featured' => __( 'Set to "1" to only show featured reviews.', 'resort-manager' ),
					'limit'    => __( 'Number of reviews to display (default: 10).', 'resort-manager' )
				],
				'example' => '[resort_reviews featured="1" limit="5"]'
			],
			[
				'tag' => 'resort_lead_form',
				'desc' => __( 'Captures guest interest and syncs to Mailchimp/CRM.', 'resort-manager' ),
				'atts' => []
			],
			[
				'tag' => 'resort_room_calendar',
				'desc' => __( 'Mini 30-day availability calendar for a specific room.', 'resort-manager' ),
				'atts' => [ 'id' => __( 'The ID of the accommodation post.', 'resort-manager' ) ],
				'example' => '[resort_room_calendar id="123"]'
			],
			[
				'tag' => 'resort_service_booking',
				'desc' => __( 'Standalone form for booking experiences like Spa or Tours.', 'resort-manager' ),
				'atts' => []
			],
			[
				'tag' => 'resort_currency_switcher',
				'desc' => __( 'A dropdown that allows guests to toggle between PHP, USD, EUR, and GBP.', 'resort-manager' ),
				'atts' => []
			],
			[
				'tag' => 'resort_gated_content',
				'desc' => __( 'Hides inner content until a lead form is submitted. Perfect for "Secret Deals".', 'resort-manager' ),
				'atts' => [
					'title' => __( 'Header for the gated block.', 'resort-manager' ),
					'desc'  => __( 'Instructions for the guest.', 'resort-manager' )
				],
				'example' => '[resort_gated_content title="Secret Promo"] Your Code: ALOHA2024 [/resort_gated_content]'
			],
		];
		?>
		<div class="wrap">
			<h1><?php _e( 'LuxeResort Shortcode Helper', 'resort-manager' ); ?></h1>
			<p class="description"><?php _e( 'Copy and paste these codes into any page or post to bring your paradise to life.', 'resort-manager' ); ?></p>

			<div class="resort-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap:20px; margin-top:30px;">
				<?php foreach ( $shortcodes as $s ) : ?>
					<div class="resort-admin-card" style="margin-bottom:0; border-top-color: var(--resort-teal);">
						<h3 style="margin-top:0;"><code>[<?php echo $s['tag']; ?>]</code></h3>
						<p><?php echo $s['desc']; ?></p>

						<?php if ( ! empty($s['atts']) ) : ?>
							<div style="background:#f9f9f9; padding:10px; border-radius:4px; margin: 15px 0;">
								<strong><?php _e( 'Attributes:', 'resort-manager' ); ?></strong>
								<ul style="margin:5px 0 0 20px; list-style:disc;">
									<?php foreach ( $s['atts'] as $attr => $val ) : ?>
										<li><code><?php echo $attr; ?></code> - <?php echo $val; ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>

						<div style="margin-top:20px;">
							<label><strong><?php _e( 'Example Code:', 'resort-manager' ); ?></strong></label><br>
							<input type="text" value="<?php echo esc_attr($s['example'] ?? '['.$s['tag'].']'); ?>" class="large-text" readonly onclick="this.select();">
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	public function render_logs_page() {
		global $wpdb;
		$table_logs = $wpdb->prefix . 'resort_activity_logs';
		$logs = $wpdb->get_results( "SELECT * FROM $table_logs ORDER BY created_at DESC LIMIT 100" );
		?>
		<div class="wrap">
			<h1><?php _e( 'Staff Activity Logs', 'resort-manager' ); ?></h1>
			<p class="description"><?php _e( 'Track all administrative actions performed by your staff. This log ensures full accountability for price changes, booking modifications, and system settings updates.', 'resort-manager' ); ?></p>

			<div style="margin-bottom: 20px;">
				<a href="<?php echo wp_nonce_url( admin_url('admin.php?page=resort-reports&resort_export_logs=1'), 'resort_export_logs_nonce' ); ?>" class="button button-secondary">
					<span class="dashicons dashicons-download" style="vertical-align: middle;"></span> <?php _e( 'Export Logs to CSV', 'resort-manager' ); ?>
				</a>
			</div>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Date', 'resort-manager' ); ?></th>
						<th><?php _e( 'User', 'resort-manager' ); ?></th>
						<th><?php _e( 'Action', 'resort-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $logs as $log ) :
						$user = get_userdata( $log->user_id );
						?>
						<tr>
							<td><?php echo $log->created_at; ?></td>
							<td><?php echo $user ? esc_html( $user->display_name ) : 'System'; ?></td>
							<td><?php echo esc_html( $log->action ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function render_guest_profiles_page() {
		$guests = get_users( [ 'role__in' => [ 'subscriber', 'customer' ] ] );
		?>
		<div class="wrap">
			<h1><?php _e( 'Guest Profiles', 'resort-manager' ); ?></h1>
			<p class="description"><?php _e( 'A centralized database of your guests. Monitor their stay history, accumulated loyalty points, and contact information to provide a more personalized service.', 'resort-manager' ); ?></p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Guest Name', 'resort-manager' ); ?></th>
						<th><?php _e( 'Email', 'resort-manager' ); ?></th>
						<th><?php _e( 'Loyalty Points', 'resort-manager' ); ?></th>
						<th><?php _e( 'Total Bookings', 'resort-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $guests as $guest ) :
						$points = get_user_meta( $guest->ID, '_resort_loyalty_points', true ) ?: 0;
						$bookings_count = count( get_posts( [
							'post_type'  => 'booking',
							'meta_key'   => '_resort_guest_id',
							'meta_value' => $guest->ID,
							'numberposts' => -1
						] ) );
						?>
						<tr>
							<td><strong><?php echo esc_html( $guest->display_name ); ?></strong></td>
							<td><?php echo esc_html( $guest->user_email ); ?></td>
							<td><?php echo $points; ?></td>
							<td><?php echo $bookings_count; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function render_settings_page() {
		if ( isset( $_POST['resort_maintenance_action'] ) && check_admin_referer( 'resort_maintenance_nonce' ) ) {
			if ( isset( $_POST['resort_reset_data'] ) ) {
				\ResortManager\Admin\Maintenance::reset_data();
				\ResortManager\Core\ActivityLogger::log( __( 'Entire plugin data reset.', 'resort-manager' ) );
				echo '<div class="updated"><p>' . __( 'All data has been reset.', 'resort-manager' ) . '</p></div>';
			}

			if ( isset( $_POST['resort_sample_data'] ) ) {
				\ResortManager\Admin\Maintenance::install_sample_data();
				\ResortManager\Core\ActivityLogger::log( __( 'Sample data re-installed.', 'resort-manager' ) );
				echo '<div class="updated"><p>' . __( 'Sample data has been installed.', 'resort-manager' ) . '</p></div>';
			}

			if ( isset( $_POST['resort_create_pages'] ) ) {
				\ResortManager\Admin\Maintenance::create_default_pages();
				\ResortManager\Core\ActivityLogger::log( __( 'Default pages regenerated.', 'resort-manager' ) );
				echo '<div class="updated"><p>' . __( 'Default pages created successfully.', 'resort-manager' ) . '</p></div>';
			}
		}

		if ( isset($_GET['settings-updated']) ) {
			\ResortManager\Core\ActivityLogger::log( __( 'General settings updated.', 'resort-manager' ) );
		}

		?>
		<div class="wrap toplevel_page_resort-manager">
			<h1><?php _e( 'LuxeResort Settings', 'resort-manager' ); ?></h1>

			<div class="resort-admin-card">
				<form method="post" action="options.php">
					<?php
					settings_fields( 'resort_settings_group' );
					do_settings_sections( 'resort-settings' );
					submit_button( __( 'Save All Settings', 'resort-manager' ), 'primary' );
					?>
				</form>
			</div>

			<div class="resort-admin-card" style="border-top-color: var(--resort-coral);">
				<h2 style="color: var(--resort-coral);"><?php _e( 'Tropical Maintenance', 'resort-manager' ); ?></h2>
				<p><?php _e( 'Use these tools to reset or refresh your resort data. Note: These actions are permanent.', 'resort-manager' ); ?></p>
				<form method="post" action="" style="background: var(--resort-light-teal); padding: 20px; border-radius: 8px;">
					<?php wp_nonce_field( 'resort_maintenance_nonce' ); ?>
					<input type="hidden" name="resort_maintenance_action" value="1">
					<button type="submit" name="resort_sample_data" class="button button-secondary">
						<?php _e( 'Refresh Demo Content', 'resort-manager' ); ?>
					</button>
					&nbsp;
					<button type="submit" name="resort_create_pages" class="button button-secondary">
						<?php _e( 'Regenerate Pages', 'resort-manager' ); ?>
					</button>
					&nbsp;
					<button type="submit" name="resort_reset_data" class="button button-link-delete" onclick="return confirm('Are you sure? This will delete ALL rooms, bookings, and settings.');">
						<?php _e( 'Reset Entire Plugin', 'resort-manager' ); ?>
					</button>
				</form>

				<div style="margin-top: 30px; padding: 25px; background: #e7f3ff; border-left: 4px solid var(--resort-teal); border-radius: 4px;">
					<h3 style="margin-top:0;"><?php _e( 'Need a Guide?', 'resort-manager' ); ?></h3>
					<p><?php _e( 'Our comprehensive manual covers everything from payment setup to developer hooks.', 'resort-manager' ); ?></p>
					<a href="<?php echo RESORT_MANAGER_URL . 'MANUAL.md'; ?>" target="_blank" class="button button-secondary"><?php _e( 'Explore Manual', 'resort-manager' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}
}
