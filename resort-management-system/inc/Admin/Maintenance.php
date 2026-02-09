<?php
namespace ResortManager\Admin;

class Maintenance {
	public static function reset_data() {
		global $wpdb;

		// 1. Delete all posts of our types
		$post_types = [ 'accommodation', 'booking', 'review', 'service' ];
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

		// Add sample services
		$services = [
			[ 'title' => 'Airport Transfer', 'price' => 50 ],
			[ 'title' => 'Daily Breakfast Buffet', 'price' => 30 ],
			[ 'title' => 'Spa Treatment (60 min)', 'price' => 120 ],
			[ 'title' => 'Private Dinner on the Beach', 'price' => 200 ]
		];

		foreach ( $services as $service ) {
			$s_id = wp_insert_post( [
				'post_type'   => 'service',
				'post_title'  => $service['title'],
				'post_status' => 'publish',
			] );
			update_post_meta( $s_id, '_resort_service_price', $service['price'] );
		}

		// Update settings
		update_option( 'resort_name', 'LuxeResort & Spa' );
		update_option( 'resort_currency', 'USD' );
	}

	public static function create_default_pages() {
		$pages = [
			'resort_booking_page' => [
				'title'   => 'Book Your Stay',
				'content' => '[resort_booking]',
			],
			'resort_rooms_page' => [
				'title'   => 'Our Accommodations',
				'content' => 'Explore our world-class villas and suites.' . "\n\n" . '[resort_rooms_grid]',
			],
			'resort_dashboard_page' => [
				'title'   => 'Guest Dashboard',
				'content' => '[resort_guest_dashboard]',
			],
			'resort_reviews_page' => [
				'title'   => 'Guest Experiences',
				'content' => 'See what our guests have to say about their stay.' . "\n\n" . '[resort_reviews]',
			],
		];

		foreach ( $pages as $option_key => $page_data ) {
			// Check if page already exists via option
			$existing_id = get_option( $option_key );
			if ( $existing_id && get_post( $existing_id ) ) {
				continue;
			}

			$page_id = wp_insert_post( [
				'post_title'   => $page_data['title'],
				'post_content' => $page_data['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			] );

			if ( $page_id ) {
				update_option( $option_key, $page_id );
			}
		}
	}

	public static function cleanup_abandoned_bookings() {
		$abandoned_bookings = get_posts( [
			'post_type'    => 'booking',
			'post_status'  => 'publish',
			'numberposts'  => -1,
			'meta_query'   => [
				[
					'key'     => '_resort_status',
					'value'   => 'pending',
				],
			],
			'date_query'   => [
				[
					'column' => 'post_date_gmt',
					'before' => '30 minutes ago',
				],
			],
		] );

		$count = 0;
		global $wpdb;
		$table_availability = $wpdb->prefix . 'resort_availability';

		foreach ( $abandoned_bookings as $booking ) {
			// Release availability
			$wpdb->delete( $table_availability, [ 'booking_id' => $booking->ID ] );
			// Mark as abandoned
			update_post_meta( $booking->ID, '_resort_status', 'abandoned' );
			$count++;
		}

		return $count;
	}

	public static function send_reminders() {
		$pending_bookings = get_posts( [
			'post_type'    => 'booking',
			'post_status'  => 'publish',
			'numberposts'  => -1,
			'meta_query'   => [
				[ 'key' => '_resort_status', 'value' => 'pending' ],
				[ 'key' => '_resort_reminder_sent', 'compare' => 'NOT EXISTS' ]
			],
			'date_query'   => [
				[ 'column' => 'post_date_gmt', 'before' => '15 minutes ago' ]
			]
		] );

		foreach ( $pending_bookings as $booking ) {
			\ResortManager\Core\Notifications::send_abandoned_reminder( $booking->ID );
		}

		return count( $pending_bookings );
	}
}
