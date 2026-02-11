<?php
namespace ResortManager\Frontend;

class CurrencySwitcher {
	public function __construct() {
		add_shortcode( 'resort_currency_switcher', [ $this, 'render_switcher' ] );
		add_action( 'wp_ajax_resort_set_currency', [ $this, 'set_currency' ] );
		add_action( 'wp_ajax_nopriv_resort_set_currency', [ $this, 'set_currency' ] );
	}

	public function render_switcher() {
		$current = $this->get_current_currency();
		$currencies = [
			'PHP' => '₱ PHP',
			'USD' => '$ USD',
			'EUR' => '€ EUR',
			'GBP' => '£ GBP'
		];

		ob_start();
		?>
		<div class="resort-currency-switcher">
			<select onchange="resortSetCurrency(this.value)">
				<?php foreach ( $currencies as $code => $label ) : ?>
					<option value="<?php echo $code; ?>" <?php selected( $current, $code ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<script>
		function resortSetCurrency(code) {
			jQuery.post('<?php echo admin_url("admin-ajax.php"); ?>', {
				action: 'resort_set_currency',
				currency: code,
				nonce: '<?php echo wp_create_nonce("resort_currency_nonce"); ?>'
			}, function() {
				location.reload();
			});
		}
		</script>
		<?php
		return ob_get_clean();
	}

	public function set_currency() {
		check_ajax_referer( 'resort_currency_nonce', 'nonce' );
		$code = sanitize_text_field( $_POST['currency'] );
		setcookie( 'resort_user_currency', $code, time() + ( 30 * DAY_IN_SECONDS ), COOKIEPATH, COOKIE_DOMAIN );
		wp_send_json_success();
	}

	public static function get_current_currency() {
		return $_COOKIE['resort_user_currency'] ?? get_option( 'resort_currency', 'PHP' );
	}
}
