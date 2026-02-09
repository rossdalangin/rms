<?php
namespace ResortManager\Admin;

class Settings {
	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function add_menu_page() {
		add_menu_page(
			__( 'LuxeResort Settings', 'resort-manager' ),
			__( 'Resort Settings', 'resort-manager' ),
			'manage_options',
			'resort-settings',
			[ $this, 'render_settings_page' ],
			'dashicons-admin-settings',
			30
		);
	}

	public function register_settings() {
		register_setting( 'resort_settings_group', 'resort_name' );
		register_setting( 'resort_settings_group', 'resort_currency' );
		register_setting( 'resort_settings_group', 'stripe_api_key' );

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

	}

	public function render_name_field() {
		$value = get_option( 'resort_name', '' );
		echo '<input type="text" name="resort_name" value="' . esc_attr( $value ) . '" class="regular-text">';
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
		<div class="wrap">
			<h1><?php _e( 'LuxeResort Settings', 'resort-manager' ); ?></h1>

			<div class="resort-settings-form">
				<form method="post" action="options.php">
					<?php
					settings_fields( 'resort_settings_group' );
					do_settings_sections( 'resort-settings' );
					submit_button();
					?>
				</form>
			</div>

			<hr>

			<div class="resort-maintenance-form">
				<h2><?php _e( 'Maintenance Tools', 'resort-manager' ); ?></h2>
				<p><?php _e( 'Caution: These actions cannot be undone.', 'resort-manager' ); ?></p>
				<form method="post" action="">
					<?php wp_nonce_field( 'resort_maintenance_nonce' ); ?>
					<input type="hidden" name="resort_maintenance_action" value="1">
					<button type="submit" name="resort_reset_data" class="button button-link-delete" onclick="return confirm('Are you sure? This will delete ALL rooms, bookings, and settings.');">
						<?php _e( 'Reset All Data', 'resort-manager' ); ?>
					</button>
					&nbsp;
					<button type="submit" name="resort_sample_data" class="button button-secondary">
						<?php _e( 'Install Sample Data', 'resort-manager' ); ?>
					</button>
					&nbsp;
					<button type="submit" name="resort_create_pages" class="button button-primary">
						<?php _e( 'Create Default Pages', 'resort-manager' ); ?>
					</button>
				</form>

				<div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2196F3;">
					<h3><?php _e( 'Documentation & Help', 'resort-manager' ); ?></h3>
					<p><?php _e( 'Need help? Check out our comprehensive user manual for instructions on shortcodes, setup, and management.', 'resort-manager' ); ?></p>
					<a href="<?php echo RESORT_MANAGER_URL . 'MANUAL.md'; ?>" target="_blank" class="button button-secondary"><?php _e( 'View Manual (MD)', 'resort-manager' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}
}
