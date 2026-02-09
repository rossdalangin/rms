<?php
namespace ResortManager\Core;

class ActivityLogger {
	public static function log( $action ) {
		global $wpdb;
		$table_logs = $wpdb->prefix . 'resort_activity_logs';

		$wpdb->insert( $table_logs, [
			'user_id' => get_current_user_id(),
			'action'  => $action,
		] );
	}
}
