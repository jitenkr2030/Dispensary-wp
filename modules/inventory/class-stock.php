<?php
namespace Dispensary_WP\Modules\Inventory;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stock {

	public static function get( $product_id, $variant_id = 0 ) {
		global $wpdb;

		$table = Database::table( 'stock_movements' );

		return (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(
					CASE
						WHEN type IN ('in','return','adjustment_in') THEN quantity
						WHEN type IN ('out','sale','adjustment_out','damage','expired') THEN -quantity
						ELSE 0
					END
				), 0)
				FROM {$table}
				WHERE product_id = %d
				AND variant_id = %d",
				absint( $product_id ),
				absint( $variant_id )
			)
		);
	}

	public static function available( $product_id, $variant_id = 0 ) {
		return max(
			0,
			self::get( $product_id, $variant_id )
		);
	}

	public static function low_stock(
		$product_id,
		$threshold = 10,
		$variant_id = 0
	) {
		return self::available(
			$product_id,
			$variant_id
		) <= max( 0, (float) $threshold );
	}
}
