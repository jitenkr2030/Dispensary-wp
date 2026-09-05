<?php
namespace Dispensary_WP\Modules\Inventory;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Lot {

	public static function create( $data ) {
		global $wpdb;

		$table = Database::table( 'lots' );
		$now   = current_time( 'mysql', true );

		$result = $wpdb->insert(
			$table,
			array(
				'product_id' => absint( $data['product_id'] ?? 0 ),
				'batch_id'   => absint( $data['batch_id'] ?? 0 ),
				'lot_number' => sanitize_text_field( $data['lot_number'] ?? '' ),
				'quantity'   => max( 0, (float) ( $data['quantity'] ?? 0 ) ),
				'status'     => sanitize_key( $data['status'] ?? 'active' ),
				'created_at' => $now,
				'updated_at' => $now,
			),
			array(
				'%d',
				'%d',
				'%s',
				'%f',
				'%s',
				'%s',
				'%s',
			)
		);

		return false === $result
			? new \WP_Error( 'lot_create_failed', $wpdb->last_error )
			: absint( $wpdb->insert_id );
	}

	public static function all( $product_id = 0 ) {
		global $wpdb;

		$table = Database::table( 'lots' );

		if ( $product_id ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table}
					WHERE product_id = %d
					ORDER BY id DESC",
					absint( $product_id )
				),
				ARRAY_A
			);
		}

		return $wpdb->get_results(
			"SELECT * FROM {$table}
			ORDER BY id DESC",
			ARRAY_A
		);
	}
}
