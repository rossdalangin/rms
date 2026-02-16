<?php
namespace ResortManager\Core;

class TemplateLoader {
	public function __construct() {
		add_filter( 'theme_page_templates', [ $this, 'add_templates_to_dropdown' ] );
		add_filter( 'template_include', [ $this, 'load_plugin_templates' ] );
	}

	public function add_templates_to_dropdown( $templates ) {
		$templates['resort-full-width.php'] = __( 'LuxeResort Full Width Canvas', 'resort-manager' );
		$templates['resort-dashboard.php'] = __( 'LuxeResort Dashboard Template', 'resort-manager' );
		return $templates;
	}

	public function load_plugin_templates( $template ) {
		if ( is_page() ) {
			$page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

			if ( 'resort-full-width.php' === $page_template ) {
				$file = RESORT_MANAGER_PATH . 'templates/page-full-width.php';
				if ( file_exists( $file ) ) {
					return $file;
				}
			}

			if ( 'resort-dashboard.php' === $page_template ) {
				$file = RESORT_MANAGER_PATH . 'templates/page-dashboard.php';
				if ( file_exists( $file ) ) {
					return $file;
				}
			}
		}
		return $template;
	}
}
