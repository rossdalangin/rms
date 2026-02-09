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
		?>
		<div class="wrap">
			<h1><?php _e( 'LuxeResort Settings', 'resort-manager' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'resort_settings_group' );
				do_settings_sections( 'resort-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
