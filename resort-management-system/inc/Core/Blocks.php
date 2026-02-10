<?php
namespace ResortManager\Core;

class Blocks {
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
	}

	public function register_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type( RESORT_MANAGER_PATH . 'blocks/booking' );
		register_block_type( RESORT_MANAGER_PATH . 'blocks/rooms-grid' );
		register_block_type( RESORT_MANAGER_PATH . 'blocks/reviews' );
	}
}
