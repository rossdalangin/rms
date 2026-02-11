<?php
namespace ResortManager\Core;

class PWA {
	public function __construct() {
		add_action( 'init', [ $this, 'handle_pwa_requests' ] );
		add_action( 'wp_head', [ $this, 'add_pwa_tags' ] );
	}

	public function handle_pwa_requests() {
		if ( isset( $_GET['resort_manifest'] ) ) {
			$this->generate_manifest();
			exit;
		}

		if ( isset( $_GET['resort_sw'] ) ) {
			$this->generate_service_worker();
			exit;
		}
	}

	public function add_pwa_tags() {
		echo '<link rel="manifest" href="' . home_url( '/?resort_manifest=1' ) . '">';
		echo '<meta name="theme-color" content="#008080">';
		echo '<link rel="apple-touch-icon" href="' . RESORT_MANAGER_URL . 'assets/images/icon-192.png">';
		echo '<script>
			if ("serviceWorker" in navigator) {
				window.addEventListener("load", () => {
					navigator.serviceWorker.register("' . home_url( '/?resort_sw=1' ) . '").then(reg => {
						console.log("LuxeResort SW registered.");
					}).catch(err => {
						console.log("LuxeResort SW failed: ", err);
					});
				});
			}
		</script>';
	}

	private function generate_manifest() {
		$resort_name = get_option( 'resort_name', 'LuxeResort' );
		$manifest = [
			'name'             => $resort_name,
			'short_name'       => $resort_name,
			'start_url'        => home_url( '/guest-dashboard/' ),
			'display'          => 'standalone',
			'background_color' => '#ffffff',
			'theme_color'      => '#008080',
			'icons'            => [
				[
					'src'   => RESORT_MANAGER_URL . 'assets/images/icon-192.png',
					'sizes' => '192x192',
					'type'  => 'image/png'
				],
				[
					'src'   => RESORT_MANAGER_URL . 'assets/images/icon-512.png',
					'sizes' => '512x512',
					'type'  => 'image/png'
				]
			]
		];

		header( 'Content-Type: application/manifest+json' );
		echo json_encode( $manifest );
	}

	private function generate_service_worker() {
		header( 'Content-Type: application/javascript' );
		?>
		const CACHE_NAME = 'luxeresort-v1';
		const urlsToCache = [
			'<?php echo home_url( '/guest-dashboard/' ); ?>',
			'<?php echo RESORT_MANAGER_URL; ?>assets/css/booking.css',
			'<?php echo RESORT_MANAGER_URL; ?>assets/js/booking.js'
		];

		self.addEventListener('install', event => {
			event.waitUntil(
				caches.open(CACHE_NAME)
					.then(cache => cache.addAll(urlsToCache))
			);
		});

		self.addEventListener('fetch', event => {
			event.respondWith(
				caches.match(event.request)
					.then(response => response || fetch(event.request))
			);
		});
		<?php
	}
}
