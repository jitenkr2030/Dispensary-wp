<?php
namespace Dispensary_WP\Compliance;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Compliance_Reports {

	public static function get_events(
		$limit = 100,
		$offset = 0
	) {
		global $wpdb;

		$table = Database::table( 'audit_logs' );

		$limit  = max( 1, min( 500, absint( $limit ) ) );
		$offset = max( 0, absint( $offset ) );

		$sql = $wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE action LIKE %s
			ORDER BY id DESC
			LIMIT %d OFFSET %d",
			'age_%',
			$limit,
			$offset
		);

		return $wpdb->get_results(
			$sql,
			ARRAY_A
		);
	}

	public static function count_events() {
		global $wpdb;

		$table = Database::table( 'audit_logs' );

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$table}
				WHERE action LIKE %s",
				'age_%'
			)
		);
	}
}
