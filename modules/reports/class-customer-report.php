<?php
/**
 * Customer reports.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Reports;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customer_Report {

	/**
	 * Customer summary.
	 *
	 * @return array
	 */
	public static function summary() {
		global $wpdb;

		$customers = Database::table( 'customers' );

		$sql = "SELECT
			COUNT(*) AS total_customers,
			SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_customers,
			SUM(CASE WHEN status != 'active' THEN 1 ELSE 0 END) AS inactive_customers
			FROM {$customers}";

		$result = $wpdb->get_row( $sql, ARRAY_A );

		return is_array( $result ) ? $result : array();
	}

	/**
	 * New customers.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array
	 */
	public static function new_customers( $from_date = '', $to_date = '' ) {
		global $wpdb;

		$table  = Database::table( 'customers' );
		$where  = 'WHERE 1=1';
		$values = array();

		if ( $from_date ) {
			$where   .= ' AND DATE(created_at) >= %s';
			$values[] = $from_date;
		}

		if ( $to_date ) {
			$where   .= ' AND DATE(created_at) <= %s';
			$values[] = $to_date;
		}

		$sql = "SELECT
			DATE(created_at) AS customer_date,
			COUNT(*) AS customer_count
			FROM {$table}
			{$where}
			GROUP BY DATE(created_at)
			ORDER BY customer_date ASC";

		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Top customers by order value.
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public static function top_customers( $limit = 10 ) {
		global $wpdb;

		$customers = Database::table( 'customers' );
		$orders    = Database::table( 'orders' );

		$limit = max( 1, absint( $limit ) );

		$sql = $wpdb->prepare(
			"SELECT
				c.id,
				c.first_name,
				c.last_name,
				c.email,
				COUNT(o.id) AS order_count,
				COALESCE(SUM(o.total), 0) AS total_spent
			FROM {$customers} c
			INNER JOIN {$orders} o ON o.customer_id = c.id
			WHERE o.status NOT IN ('cancelled', 'failed')
			GROUP BY c.id, c.first_name, c.last_name, c.email
			ORDER BY total_spent DESC
			LIMIT %d",
			$limit
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}
