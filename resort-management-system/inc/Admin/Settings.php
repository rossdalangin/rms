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
			'manage_options',
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
			'manage_options',
			'resort-manager',
			[ $this, 'render_onboarding_proxy' ]
		);

		add_submenu_page(
			'resort-manager',
			__( 'Settings', 'resort-manager' ),
			__( 'Settings', 'resort-manager' ),
			'manage_options',
			'resort-settings',
			[ $this, 'render_settings_page' ]
		);

		add_submenu_page(
			'resort-manager',
			__( 'Guest Profiles', 'resort-manager' ),
			__( 'Guest Profiles', 'resort-manager' ),
			'manage_options',
			'resort-guests',
			[ $this, 'render_guest_profiles_page' ]
		);

		add_submenu_page(
			'resort-manager',
			__( 'Activity Logs', 'resort-manager' ),
			__( 'Activity Logs', 'resort-manager' ),
			'manage_options',
			'resort-logs',
			[ $this, 'render_logs_page' ]
		);
	}

	public function register_settings() {
		register_setting( 'resort_settings_group', 'resort_name' );
		register_setting( 'resort_settings_group', 'resort_currency' );
		register_setting( 'resort_settings_group', 'resort_currency_symbol_pos' );
		register_setting( 'resort_settings_group', 'resort_min_nights' );
		register_setting( 'resort_settings_group', 'resort_max_nights' );
		register_setting( 'resort_settings_group', 'resort_book_ahead_days' );
		register_setting( 'resort_settings_group', 'resort_deposit_percentage' );
		register_setting( 'resort_settings_group', 'resort_email_template_confirmation' );
		register_setting( 'resort_settings_group', 'resort_mailchimp_api_key' );
		register_setting( 'resort_settings_group', 'resort_mailchimp_list_id' );
		register_setting( 'resort_settings_group', 'resort_twilio_sid' );
		register_setting( 'resort_settings_group', 'resort_twilio_token' );
		register_setting( 'resort_settings_group', 'resort_twilio_number' );

		// Payment Gateways Enable/Disable
		register_setting( 'resort_settings_group', 'resort_payment_stripe_enabled' );
		register_setting( 'resort_settings_group', 'resort_payment_paypal_enabled' );
		register_setting( 'resort_settings_group', 'resort_payment_offline_enabled' );

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
			null,
			'resort-settings'
		);

		add_settings_field(
			'resort_name',
			__( 'Resort Name', 'resort-manager' ),
			[ $this, 'render_name_field' ],
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
			null,
			'resort-settings'
		);

		add_settings_field(
			'resort_email_template',
			__( 'Confirmation Email Template', 'resort-manager' ),
			[ $this, 'render_email_template_field' ],
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
	}

	public function render_payments_section_desc() {
		echo '<p>' . __( 'Configure your payment methods and API credentials below. If only Offline is enabled, the payment selection will be skipped during booking.', 'resort-manager' ) . '</p>';
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

	public function render_email_template_field() {
		$value = get_option( 'resort_email_template_confirmation', '<h1>Booking Confirmed!</h1><p>Thank you for choosing LuxeResort.</p>' );
		wp_editor( $value, 'resort_email_template_confirmation' );
	}

	public function render_marketing_fields() {
		$mc_key = get_option( 'resort_mailchimp_api_key', '' );
		$mc_list = get_option( 'resort_mailchimp_list_id', '' );
		$tw_sid = get_option( 'resort_twilio_sid', '' );
		$tw_token = get_option( 'resort_twilio_token', '' );
		$tw_num = get_option( 'resort_twilio_number', '' );
		?>
		<p><strong><?php _e( 'Mailchimp Integration', 'resort-manager' ); ?></strong></p>
		<label>API Key:</label><br>
		<input type="text" name="resort_mailchimp_api_key" value="<?php echo esc_attr($mc_key); ?>" class="regular-text"><br>
		<label>Audience (List) ID:</label><br>
		<input type="text" name="resort_mailchimp_list_id" value="<?php echo esc_attr($mc_list); ?>" class="regular-text"><br><br>

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

	public function render_currency_settings() {
		$currency = get_option( 'resort_currency', 'USD' );
		$pos = get_option( 'resort_currency_symbol_pos', 'before' );
		?>
		<input type="text" name="resort_currency" value="<?php echo esc_attr($currency); ?>" placeholder="USD" style="width: 80px;">
		<select name="resort_currency_symbol_pos">
			<option value="before" <?php selected($pos, 'before'); ?>><?php _e( 'Symbol Before ($100)', 'resort-manager' ); ?></option>
			<option value="after" <?php selected($pos, 'after'); ?>><?php _e( 'Symbol After (100 €)', 'resort-manager' ); ?></option>
		</select>
		<?php
	}

	public function render_duration_rules() {
		$min = get_option( 'resort_min_nights', '1' );
		$max = get_option( 'resort_max_nights', '30' );
		$ahead = get_option( 'resort_book_ahead_days', '0' );
		?>
		<label><?php _e( 'Min Nights:', 'resort-manager' ); ?></label>
		<input type="number" name="resort_min_nights" value="<?php echo esc_attr( $min ); ?>" style="width: 60px;">
		<label style="margin-left: 20px;"><?php _e( 'Max Nights:', 'resort-manager' ); ?></label>
		<input type="number" name="resort_max_nights" value="<?php echo esc_attr( $max ); ?>" style="width: 60px;">
		<label style="margin-left: 20px;"><?php _e( 'Book Ahead (Days):', 'resort-manager' ); ?></label>
		<input type="number" name="resort_book_ahead_days" value="<?php echo esc_attr( $ahead ); ?>" style="width: 60px;">
		<p class="description"><?php _e( 'Book Ahead defines the minimum days in advance a guest can book.', 'resort-manager' ); ?></p>
		<?php
	}

	public function render_deposit_field() {
		$value = get_option( 'resort_deposit_percentage', '100' );
		?>
		<input type="number" name="resort_deposit_percentage" value="<?php echo esc_attr( $value ); ?>" style="width: 80px;"> %
		<p class="description"><?php _e( 'Set to 100 for full payment at booking.', 'resort-manager' ); ?></p>
		<?php
	}

	public function render_onboarding_proxy() {
		$onboarding = new \ResortManager\Admin\Onboarding();
		$onboarding->render_onboarding_page();
	}

	public function render_logs_page() {
		global $wpdb;
		$table_logs = $wpdb->prefix . 'resort_activity_logs';
		$logs = $wpdb->get_results( "SELECT * FROM $table_logs ORDER BY created_at DESC LIMIT 100" );
		?>
		<div class="wrap">
			<h1><?php _e( 'Staff Activity Logs', 'resort-manager' ); ?></h1>
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
				echo '<div class="updated"><p>' . __( 'All data has been reset.', 'resort-manager' ) . '</p></div>';
			}

			if ( isset( $_POST['resort_sample_data'] ) ) {
				\ResortManager\Admin\Maintenance::install_sample_data();
				echo '<div class="updated"><p>' . __( 'Sample data has been installed.', 'resort-manager' ) . '</p></div>';
			}

			if ( isset( $_POST['resort_create_pages'] ) ) {
				\ResortManager\Admin\Maintenance::create_default_pages();
				echo '<div class="updated"><p>' . __( 'Default pages created successfully.', 'resort-manager' ) . '</p></div>';
			}
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
