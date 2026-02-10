<?php
namespace ResortManager\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class ElementorWidget extends \Elementor\Widget_Base {
	private $config;

	public function __construct( $config = [], $data = [] ) {
		$this->config = $config;
		parent::__construct( $data );
	}

	public function get_name() {
		return $this->config['name'];
	}

	public function get_title() {
		return $this->config['title'];
	}

	public function get_icon() {
		return $this->config['icon'];
	}

	public function get_categories() {
		return [ 'general' ];
	}

	protected function render() {
		echo do_shortcode( $this->config['shortcode'] );
	}
}
