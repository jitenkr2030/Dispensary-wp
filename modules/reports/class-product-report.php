<?php
/**
 * Product reports.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Reports;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Report {

	/**
	 * Product sales.
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public static function sales( $limit = 50 ) {
		global $wpdb;

		$items = Database::table( 'order_items' );

		$limit = max( 1, absint( $limit ) );

		$sql = $wpdb->prepare(
			"SELECT
				product_id,
				product_name,
				sku,
				SUM(quantity) AS quantity_sold,
				SUM(total) AS sales_total
			FROM {$items}
			GROUP BY product_id, product_name, sku
			ORDER BY sales_total DESC
			LIMIT %d",
			$limit
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Product stock.
	 *
	 * @return array
	 */
	public static function stock() {
		global $wpdb;

		$movements = Database::table( 'stock_movements' );

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
			FROM {$movements}
			GROUP BY product_id, variant_id
			ORDER BY stock_quantity DESC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Product order count.
	 *
	 * @return array
	 */
	public static function order_count() {
		global $wpdb;

		$items  = Database::table( 'order_items' );
		$orders = Database::table( 'orders' );

		$sql = "SELECT
			i.product_id,
			i.product_name,
			COUNT(DISTINCT i.order_id) AS order_count
			FROM {$items} i
			INNER JOIN {$orders} o ON o.id = i.order_id
			WHERE o.status NOT IN ('cancelled', 'failed')
			GROUP BY i.product_id, i.product_name
			ORDER BY order_count DESC";

		return $wpdb->get_results( $sql, ARRAY_A );
	}
}
