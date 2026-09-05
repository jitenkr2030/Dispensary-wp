<?php
/**
 * Staff reports.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Reports;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Staff_Report {

	/**
	 * Staff summary.
	 *
	 * @return array
	 */
	public static function summary() {
		global $wpdb;

		$table = Database::table( 'staff_members' );

		$sql = "SELECT
			COUNT(*) AS total_staff,
			SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_staff,
			SUM(CASE WHEN status != 'active' THEN 1 ELSE 0 END) AS inactive_staff
			FROM {$table}";

		$result = $wpdb->get_row( $sql, ARRAY_A );

		return is_array( $result ) ? $result : array();
	}

	/**
	 * Attendance summary.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array
	 */
	public static function attendance( $from_date = '', $to_date = '' ) {
		global $wpdb;

		$table  = Database::table( 'staff_attendance' );
		$where  = 'WHERE 1=1';
		$values = array();

		if ( $from_date ) {
			$where   .= ' AND attendance_date >= %s';
			$values[] = $from_date;
		}

		if ( $to_date ) {
			$where   .= ' AND attendance_date <= %s';
			$values[] = $to_date;
		}

		$sql = "SELECT
			status,
			COUNT(*) AS attendance_count
			FROM {$table}
			{$where}
			GROUP BY status
			ORDER BY attendance_count DESC";

		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Staff attendance details.
	 *
	 * @param string $date Attendance date.
	 * @return array
	 */
	public static function attendance_by_date( $date ) {
		global $wpdb;

		$table = Database::table( 'staff_attendance' );

		$sql = $wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE attendance_date = %s
			ORDER BY staff_id ASC",
			$date
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}
