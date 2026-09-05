<?php
/**
 * Financial reports.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Reports;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Financial_Report {

	/**
	 * Revenue summary.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array
	 */
	public static function revenue( $from_date = '', $to_date = '' ) {
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
			COALESCE(SUM(subtotal), 0) AS subtotal,
			COALESCE(SUM(discount_total), 0) AS discount_total,
			COALESCE(SUM(tax_total), 0) AS tax_total,
			COALESCE(SUM(shipping_total), 0) AS shipping_total,
			COALESCE(SUM(total), 0) AS revenue
			FROM {$table}
			{$where}";

		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}

		$result = $wpdb->get_row( $sql, ARRAY_A );

		return is_array( $result ) ? $result : array();
	}

	/**
	 * Payment totals.
	 *
	 * @return array
	 */
	public static function payments() {
		global $wpdb;

		$table = Database::table( 'order_payments' );

		$sql = "SELECT
			method,
			status,
			COUNT(*) AS payment_count,
			COALESCE(SUM(amount), 0) AS total
			FROM {$table}
			GROUP BY method, status
			ORDER BY total DESC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Refund totals.
	 *
	 * @return array
	 */
	public static function refunds() {
		global $wpdb;

		$table = Database::table( 'order_refunds' );

		$sql = "SELECT
			status,
			COUNT(*) AS refund_count,
			COALESCE(SUM(amount), 0) AS total
			FROM {$table}
			GROUP BY status
			ORDER BY total DESC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}
