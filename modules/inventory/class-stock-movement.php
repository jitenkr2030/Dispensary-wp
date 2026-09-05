<?php
namespace Dispensary_WP\Modules\Inventory;

use Dispensary_WP\Database\Database;
use Dispensary_WP\Security\Audit_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stock_Movement {

	public static function create( $data ) {
		global $wpdb;

		$table = Database::table( 'stock_movements' );

		$product_id = absint(
			$data['product_id'] ?? 0
		);

		$quantity = (float) (
			$data['quantity'] ?? 0
		);

		if ( ! $product_id || $quantity <= 0 ) {
			return new \WP_Error(
				'invalid_stock_movement',
				'Product and positive quantity are required.'
			);
		}

		$type = sanitize_key(
			$data['type'] ?? 'in'
		);

		$allowed = array(
			'in',
			'out',
			'sale',
			'return',
			'adjustment_in',
			'adjustment_out',
			'damage',
			'expired',
		);

		if ( ! in_array( $type, $allowed, true ) ) {
			return new \WP_Error(
				'invalid_stock_type',
				'Invalid stock movement type.'
			);
		}

		$result = $wpdb->insert(
			$table,
			array(
				'product_id'      => $product_id,
				'variant_id'      => absint( $data['variant_id'] ?? 0 ),
				'batch_id'        => absint( $data['batch_id'] ?? 0 ),
				'lot_id'          => absint( $data['lot_id'] ?? 0 ),
				'type'            => $type,
				'quantity'        => $quantity,
				'reference_type'  => sanitize_key( $data['reference_type'] ?? '' ),
				'reference_id'    => absint( $data['reference_id'] ?? 0 ),
				'reason'          => sanitize_text_field( $data['reason'] ?? '' ),
				'created_by'      => get_current_user_id(),
				'created_at'      => current_time( 'mysql', true ),
			),
			array(
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%f',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'stock_movement_failed',
				$wpdb->last_error
			);
		}

		$id = absint( $wpdb->insert_id );

		Audit_Log::log(
			'stock_movement_created',
			'stock_movement',
			$id,
			array(
				'product_id' => $product_id,
				'type'       => $type,
				'quantity'   => $quantity,
			)
		);

		return $id;
	}

	public static function all( $product_id, $limit = 100 ) {
		global $wpdb;

		$table = Database::table( 'stock_movements' );

		$limit = max(
			1,
			min( 500, absint( $limit ) )
		);

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE product_id = %d
				ORDER BY id DESC
				LIMIT %d",
				absint( $product_id ),
				$limit
			),
			ARRAY_A
		);
	}
}
