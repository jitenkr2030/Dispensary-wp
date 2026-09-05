<?php
/**
 * Sales reports.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Reports;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sales_Report {

	/**
	 * Get sales summary.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array
	 */
	public static function summary( $from_date = '', $to_date = '' ) {
		global $wpdb;

		$table = Database::table( 'orders' );

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
			COUNT(*) AS order_count,
			COALESCE(SUM(subtotal), 0) AS subtotal,
			COALESCE(SUM(discount_total), 0) AS discount_total,
			COALESCE(SUM(tax_total), 0) AS tax_total,
			COALESCE(SUM(shipping_total), 0) AS shipping_total,
			COALESCE(SUM(total), 0) AS total
			FROM {$table}
			{$where}
			AND status NOT IN ('cancelled', 'failed')";

		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}

		$result = $wpdb->get_row( $sql, ARRAY_A );

		return is_array( $result ) ? $result : array();
	}

	/**
	 * Get daily sales.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array
	 */
	public static function daily( $from_date = '', $to_date = '' ) {
		global $wpdb;

		$table  = Database::table( 'orders' );
		$where  = "WHERE status NOT IN ('cancelled', 'failed')";
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
			DATE(created_at) AS sale_date,
			COUNT(*) AS order_count,
			COALESCE(SUM(total), 0) AS total
			FROM {$table}
			{$where}
			GROUP BY DATE(created_at)
			ORDER BY sale_date ASC";

		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get sales by payment status.
	 *
	 * @return array
	 */
	public static function by_payment_status() {
		global $wpdb;

		$table = Database::table( 'orders' );

		$sql = "SELECT
			payment_status,
			COUNT(*) AS order_count,
			COALESCE(SUM(total), 0) AS total
			FROM {$table}
			GROUP BY payment_status
			ORDER BY order_count DESC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Get top products by sales.
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public static function top_products( $limit = 10 ) {
		global $wpdb;

		$orders = Database::table( 'orders' );
		$items  = Database::table( 'order_items' );

		$limit = max( 1, absint( $limit ) );

		$sql = $wpdb->prepare(
			"SELECT
				i.product_id,
				i.product_name,
				i.sku,
				SUM(i.quantity) AS quantity,
				SUM(i.total) AS total
			FROM {$items} i
			INNER JOIN {$orders} o ON o.id = i.order_id
			WHERE o.status NOT IN ('cancelled', 'failed')
			GROUP BY i.product_id, i.product_name, i.sku
			ORDER BY total DESC
			LIMIT %d",
			$limit
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}
