<?php
namespace Dispensary_WP\Modules\Products;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Category {

	public static function all() {
		global $wpdb;

		$table = Database::table( 'product_categories' );

		return $wpdb->get_results(
			"SELECT * FROM {$table}
			ORDER BY name ASC",
			ARRAY_A
		);
	}

	public static function find( $id ) {
		global $wpdb;

		$table = Database::table( 'product_categories' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				absint( $id )
			),
			ARRAY_A
		);
	}

	public static function create( $data ) {
		global $wpdb;

		$table = Database::table( 'product_categories' );
		$now   = current_time( 'mysql', true );

		$name = sanitize_text_field(
			$data['name'] ?? ''
		);

		$result = $wpdb->insert(
			$table,
			array(
				'name'        => $name,
				'slug'        => sanitize_title( $data['slug'] ?? $name ),
				'description' => sanitize_textarea_field( $data['description'] ?? '' ),
				'status'      => sanitize_key( $data['status'] ?? 'active' ),
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		return false === $result
			? new \WP_Error( 'category_create_failed', $wpdb->last_error )
			: absint( $wpdb->insert_id );
	}

	public static function delete( $id ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Database::table( 'product_categories' ),
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}
}
