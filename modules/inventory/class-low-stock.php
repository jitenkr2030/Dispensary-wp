<?php
namespace Dispensary_WP\Modules\Inventory;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Low_Stock {

	public static function get_products(
		$threshold = 10
	) {
		global $wpdb;

		$products = Database::table( 'products' );

		$threshold = max(
			0,
			(float) $threshold
		);

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT
					p.id,
					p.name,
					p.sku,
					p.price,
					COALESCE(
						(
							SELECT SUM(
								CASE
									WHEN sm.type IN ('in','return','adjustment_in')
										THEN sm.quantity
									WHEN sm.type IN ('out','sale','adjustment_out','damage','expired')
										THEN -sm.quantity
									ELSE 0
								END
							)
							FROM " . Database::table( 'stock_movements' ) . " sm
							WHERE sm.product_id = p.id
						),
						0
					) AS stock_quantity
				FROM {$products} p
				WHERE p.status = 'active'
				HAVING stock_quantity <= %f
				ORDER BY stock_quantity ASC",
				$threshold
			),
			ARRAY_A
		);
	}
}
