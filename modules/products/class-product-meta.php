<?php
namespace Dispensary_WP\Modules\Products;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Meta {

	public static function get( $product_id, $key, $default = null ) {
		global $wpdb;

		$table = Database::table( 'product_meta' );

		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value
				FROM {$table}
				WHERE product_id = %d
				AND meta_key = %s
				LIMIT 1",
				absint( $product_id ),
				sanitize_key( $key )
			)
		);

		if ( null === $value ) {
			return $default;
		}

		$json = json_decode( $value, true );

		return null !== $json ? $json : $value;
	}

	public static function set( $product_id, $key, $value ) {
		global $wpdb;

		$table = Database::table( 'product_meta' );
		$now   = current_time( 'mysql', true );

		if ( is_array( $value ) || is_object( $value ) ) {
			$value = wp_json_encode( $value );
		}

		$key = sanitize_key( $key );

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table}
				WHERE product_id = %d
				AND meta_key = %s
				LIMIT 1",
				absint( $product_id ),
				$key
			)
		);

		if ( $existing ) {
			return $wpdb->update(
				$table,
				array(
					'meta_value' => (string) $value,
					'updated_at' => $now,
				),
				array(
					'id' => absint( $existing ),
				),
				array( '%s', '%s' ),
				array( '%d' )
			);
		}

		return $wpdb->insert(
			$table,
			array(
				'product_id' => absint( $product_id ),
				'meta_key'   => $key,
				'meta_value' => (string) $value,
				'created_at' => $now,
				'updated_at' => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	public static function delete( $product_id, $key ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Database::table( 'product_meta' ),
			array(
				'product_id' => absint( $product_id ),
				'meta_key'   => sanitize_key( $key ),
			),
			array( '%d', '%s' )
		);
	}
}
