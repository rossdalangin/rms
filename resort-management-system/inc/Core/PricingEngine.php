<?php
namespace ResortManager\Core;

class PricingEngine {
	public static function calculate_total( $room_id, $checkin, $checkout ) {
		$base_price = get_post_meta( $room_id, '_resort_price', true );
		$base_price = floatval( $base_price );

		$start = new \DateTime( $checkin );
		$end = new \DateTime( $checkout );
		$days = $start->diff( $end )->days;

		$total = 0;
		for ( $i = 0; $i < $days; $i++ ) {
			$current_date = clone $start;
			$current_date->modify( "+$i days" );
			$total += self::get_price_for_date( $room_id, $current_date->format( 'Y-m-d' ), $base_price );
		}

		return $total;
	}

	public static function get_price_for_date( $room_id, $date, $base_price ) {
		global $wpdb;
		$table_pricing = $wpdb->prefix . 'resort_pricing';

		$modifier = $wpdb->get_row( $wpdb->prepare(
			"SELECT price_modifier, modifier_type FROM $table_pricing
			 WHERE room_id = %d AND start_date <= %s AND end_date >= %s
			 ORDER BY priority DESC LIMIT 1",
			$room_id, $date, $date
		) );

		if ( $modifier ) {
			if ( 'fixed' === $modifier->modifier_type ) {
				return $base_price + $modifier->price_modifier;
			} elseif ( 'percentage' === $modifier->modifier_type ) {
				return $base_price * ( 1 + ( $modifier->price_modifier / 100 ) );
			}
		}

		return $base_price;
	}

	public static function format_price( $amount ) {
		$currency = class_exists('\ResortManager\Frontend\CurrencySwitcher') ? \ResortManager\Frontend\CurrencySwitcher::get_current_currency() : get_option( 'resort_currency', 'PHP' );
		$pos = get_option( 'resort_currency_symbol_pos', 'before' );

		// In a real implementation, we would have a conversion rate here.
		// For this elite release, we'll implement a simple fixed conversion factor for demonstration.
		$base_currency = get_option( 'resort_currency', 'PHP' );
		if ( $currency !== $base_currency ) {
			$rates = [ 'USD' => 0.018, 'EUR' => 0.016, 'GBP' => 0.014, 'PHP' => 1 ];
			$conversion = $rates[$currency] ?? 1;
			$amount = $amount * $conversion;
		}

		$formatted = number_format( $amount, 2 );

		if ( 'before' === $pos ) {
			return $currency . ' ' . $formatted;
		} else {
			return $formatted . ' ' . $currency;
		}
	}
}
