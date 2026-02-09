<?php
namespace ResortManager\Admin;

class Maintenance {
	public static function reset_data() {
		global $wpdb;

		// 1. Delete all posts of our types
		$post_types = [ 'accommodation', 'booking', 'review' ];
		foreach ( $post_types as $type ) {
			$posts = get_posts( [ 'post_type' => $type, 'numberposts' => -1, 'post_status' => 'any' ] );
			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}

		// 2. Truncate custom tables
		$tables = [
			$wpdb->prefix . 'resort_availability',
			$wpdb->prefix . 'resort_pricing',
			$wpdb->prefix . 'resort_coupons'
		];

		foreach ( $tables as $table ) {
			$wpdb->query( "TRUNCATE TABLE $table" );
		}

		// 3. Clear settings
		delete_option( 'resort_name' );
		delete_option( 'resort_currency' );
		delete_option( 'stripe_api_key' );
	}

	public static function install_sample_data() {
		self::reset_data();

		// Add sample accommodations
		$samples = [
			[
				'title'    => 'Luxury Ocean Villa',
				'price'    => 450,
				'capacity' => 4,
				'excerpt'  => 'A stunning villa overlooking the turquoise waters of the Atlantic.',
				'image'    => 'https://images.unsplash.com/photo-1540518614846-7eded433c457?auto=format&fit=crop&w=800&q=60'
			],
			[
				'title'    => 'Garden Suite',
				'price'    => 250,
				'capacity' => 2,
				'excerpt'  => 'Nestled in tropical gardens, perfect for couples.',
				'image'    => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=60'
			],
			[
				'title'    => 'Family Penthouse',
				'price'    => 800,
				'capacity' => 6,
				'excerpt'  => 'Spacious multi-bedroom suite with a private terrace.',
				'image'    => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=60'
			]
		];

		foreach ( $samples as $sample ) {
			$post_id = wp_insert_post( [
				'post_type'    => 'accommodation',
				'post_title'   => $sample['title'],
				'post_excerpt' => $sample['excerpt'],
				'post_status'  => 'publish',
			] );

			update_post_meta( $post_id, '_resort_price', $sample['price'] );
			update_post_meta( $post_id, '_resort_capacity', $sample['capacity'] );
			update_post_meta( $post_id, '_resort_amenities', 'WiFi, Mini Bar, Private Pool, AC' );

			// Note: In a real environment, we'd sideload the image.
			// Here we just save the URL in a meta for simplicity in the grid.
			update_post_meta( $post_id, '_resort_sample_image', $sample['image'] );
		}

		// Update settings
		update_option( 'resort_name', 'LuxeResort & Spa' );
		update_option( 'resort_currency', 'USD' );
	}
}
