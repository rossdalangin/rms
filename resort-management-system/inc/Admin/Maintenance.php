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
			$wpdb->prefix . 'resort_coupons',
			$wpdb->prefix . 'resort_payments'
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
		update_option( 'resort_currency', 'PHP' );
	}

	public static function create_default_pages() {
		$pages = [
			'resort_booking_page' => [
				'title'   => 'Book Your Stay',
				'content' => '<!-- wp:heading {"textAlign":"center","style":{"typography":{"lineHeight":"1.2"}}} -->
<h2 class="has-text-align-center" style="line-height:1.2">Your Journey to Paradise Begins Here</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textAlign":"center"} -->
<p class="has-text-align-center">Embark on a journey of luxury and tranquility. Select your dates below to begin your reservation at our world-class resort.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[resort_booking]
<!-- /wp:shortcode -->',
			],
			'resort_rooms_page' => [
				'title'   => 'Our Accommodations',
				'content' => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">Choose Your Sanctuary</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textAlign":"center"} -->
<p class="has-text-align-center">From intimate garden suites to expansive oceanfront villas, discover the perfect setting for your next escape. Each space is designed with breathable "Tropical Modern" principles to ensure absolute peace of mind.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[resort_rooms_grid]
<!-- /wp:shortcode -->',
			],
			'resort_dashboard_page' => [
				'title'   => 'Guest Dashboard',
				'content' => '<!-- wp:paragraph -->
<p>Welcome back to your private resort portal. Here you can manage your upcoming stays, request in-room services, and view your exclusive loyalty perks.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[resort_guest_dashboard]
<!-- /wp:shortcode -->',
			],
			'resort_reviews_page' => [
				'title'   => 'Guest Experiences',
				'content' => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">Memories That Last a Lifetime</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textAlign":"center"} -->
<p class="has-text-align-center">Read honest reflections from our guests about their time at LuxeResort. We pride ourselves on creating 5-star memories for every visitor.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[resort_reviews featured="1"]
<!-- /wp:shortcode -->',
			],
			'resort_services_page' => [
				'title'   => 'Bespoke Experiences',
				'content' => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">Elevate Your Stay</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textAlign":"center"} -->
<p class="has-text-align-center">Not staying overnight? You can still enjoy our world-class spa, guided tours, and private beach dinners. Reserve your experience below.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[resort_service_booking]
<!-- /wp:shortcode -->',
			],
			'resort_club_page' => [
				'title'   => 'Join the Island Club',
				'content' => '<!-- wp:heading {"textAlign":"center"} -->
<h2 class="has-text-align-center">Exclusive Access Awaits</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textAlign":"center"} -->
<p class="has-text-align-center">Join our elite guest list to receive seasonal invitations, secret villa deals, and priority booking status.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[resort_lead_form]
<!-- /wp:shortcode -->',
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

				// Apply custom templates
				if ( 'resort_booking_page' === $option_key ) {
					update_post_meta( $page_id, '_wp_page_template', 'resort-full-width.php' );
				}
				if ( 'resort_dashboard_page' === $option_key ) {
					update_post_meta( $page_id, '_wp_page_template', 'resort-dashboard.php' );
				}
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
		foreach ( $abandoned_bookings as $booking ) {
			\ResortManager\Core\BookingManager::sync_status( $booking->ID, 'abandoned' );
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
