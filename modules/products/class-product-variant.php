<?php
namespace Dispensary_WP\Modules\Products;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Variant {

	public static function all( $product_id ) {
		global $wpdb;

		$table = Database::table( 'product_variants' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE product_id = %d
				ORDER BY id ASC",
				absint( $product_id )
			),
			ARRAY_A
		);
	}

	public static function find( $id ) {
		global $wpdb;

		$table = Database::table( 'product_variants' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				absint( $id )
			),
			ARRAY_A
		);
	}

	public static function create( $product_id, $data ) {
		global $wpdb;

		$table = Database::table( 'product_variants' );
		$now   = current_time( 'mysql', true );

		$attributes = $data['attributes'] ?? array();

		if ( is_array( $attributes ) ) {
			$attributes = wp_json_encode( $attributes );
		}

		$result = $wpdb->insert(
			$table,
			array(
				'product_id' => absint( $product_id ),
				'name'       => sanitize_text_field( $data['name'] ?? '' ),
				'sku'        => sanitize_text_field( $data['sku'] ?? '' ),
				'price'      => max( 0, (float) ( $data['price'] ?? 0 ) ),
				'cost_price' => max( 0, (float) ( $data['cost_price'] ?? 0 ) ),
				'status'     => sanitize_key( $data['status'] ?? 'active' ),
				'attributes' => $attributes,
				'created_at' => $now,
				'updated_at' => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%f',
				'%f',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		return false === $result
			? new \WP_Error( 'variant_create_failed', $wpdb->last_error )
			: absint( $wpdb->insert_id );
	}

	public static function delete( $id ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Database::table( 'product_variants' ),
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}
}
