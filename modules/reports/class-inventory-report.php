<?php
/**
 * Inventory reports.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Reports;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Inventory_Report {

	/**
	 * Stock summary.
	 *
	 * @return array
	 */
	public static function summary() {
		global $wpdb;

		$table = Database::table( 'stock_movements' );

		$sql = "SELECT
			product_id,
			variant_id,
			COALESCE(SUM(
				CASE
					WHEN type IN ('in', 'purchase', 'return', 'adjustment_in')
					THEN quantity
					WHEN type IN ('out', 'sale', 'damage', 'adjustment_out')
					THEN -quantity
					ELSE 0
				END
			), 0) AS stock_quantity
			FROM {$table}
			GROUP BY product_id, variant_id
			ORDER BY product_id ASC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Low stock report.
	 *
	 * @param int $threshold Threshold.
	 * @return array
	 */
	public static function low_stock( $threshold = 10 ) {
		global $wpdb;

		$table = Database::table( 'stock_movements' );

		$threshold = max( 0, absint( $threshold ) );

		$sql = $wpdb->prepare(
			"SELECT
				product_id,
				variant_id,
				COALESCE(SUM(
					CASE
						WHEN type IN ('in', 'purchase', 'return', 'adjustment_in')
						THEN quantity
						WHEN type IN ('out', 'sale', 'damage', 'adjustment_out')
						THEN -quantity
						ELSE 0
					END
				), 0) AS stock_quantity
			FROM {$table}
			GROUP BY product_id, variant_id
			HAVING stock_quantity <= %d
			ORDER BY stock_quantity ASC",
			$threshold
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Expiring batches.
	 *
	 * @param int $days Days.
	 * @return array
	 */
	public static function expiring_batches( $days = 30 ) {
		global $wpdb;

		$table = Database::table( 'batches' );
		$days  = max( 1, absint( $days ) );

		$sql = $wpdb->prepare(
			"SELECT *
			FROM {$table}
			WHERE expiry_date IS NOT NULL
			AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL %d DAY)
			AND expiry_date >= CURDATE()
			AND status = 'active'
			ORDER BY expiry_date ASC",
			$days
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Stock movements.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array
	 */
	public static function movements( $from_date = '', $to_date = '' ) {
		global $wpdb;

		$table  = Database::table( 'stock_movements' );
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

		$sql = "SELECT *
			FROM {$table}
			{$where}
			ORDER BY created_at DESC";

		if ( ! empty( $values ) ) {
			$sql = $wpdb->prepare( $sql, $values );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}
