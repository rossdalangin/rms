<?php
namespace ResortManager;

class Database {
	public static function create_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_availability = $wpdb->prefix . 'resort_availability';
		$sql_availability = "CREATE TABLE $table_availability (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			room_id bigint(20) NOT NULL,
			booking_id bigint(20) DEFAULT NULL,
			date date NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'available',
			PRIMARY KEY  (id),
			KEY room_id (room_id),
			KEY date (date)
		) $charset_collate;";

		$table_pricing = $wpdb->prefix . 'resort_pricing';
		$sql_pricing = "CREATE TABLE $table_pricing (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			room_id bigint(20) NOT NULL,
			start_date date NOT NULL,
			end_date date NOT NULL,
			price_modifier decimal(10,2) NOT NULL DEFAULT 0.00,
			modifier_type varchar(20) NOT NULL DEFAULT 'fixed',
			priority int(11) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			KEY room_id (room_id)
		) $charset_collate;";

		$table_coupons = $wpdb->prefix . 'resort_coupons';
		$sql_coupons = "CREATE TABLE $table_coupons (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			code varchar(50) NOT NULL,
			discount_amount decimal(10,2) NOT NULL,
			discount_type varchar(20) NOT NULL DEFAULT 'fixed',
			expiry_date date DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code)
		) $charset_collate;";

		$table_payments = $wpdb->prefix . 'resort_payments';
		$sql_payments = "CREATE TABLE $table_payments (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			booking_id bigint(20) NOT NULL,
			transaction_id varchar(100) DEFAULT NULL,
			amount decimal(10,2) NOT NULL,
			method varchar(20) NOT NULL,
			status varchar(20) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY booking_id (booking_id)
		) $charset_collate;";

		$table_logs = $wpdb->prefix . 'resort_activity_logs';
		$sql_logs = "CREATE TABLE $table_logs (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			action text NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_availability );
		dbDelta( $sql_pricing );
		dbDelta( $sql_coupons );
		dbDelta( $sql_payments );
		dbDelta( $sql_logs );
	}
}
