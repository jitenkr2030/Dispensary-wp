<?php
namespace Dispensary_WP\Modules\Products;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Lab_Test {

	public static function all( $product_id ) {
		global $wpdb;

		$table = Database::table( 'product_lab_tests' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE product_id = %d
				ORDER BY test_date DESC, id DESC",
				absint( $product_id )
			),
			ARRAY_A
		);
	}

	public static function create( $product_id, $data ) {
		global $wpdb;

		$table = Database::table( 'product_lab_tests' );
		$now   = current_time( 'mysql', true );

		$result = $wpdb->insert(
			$table,
			array(
				'product_id' => absint( $product_id ),
				'lab_name'   => sanitize_text_field( $data['lab_name'] ?? '' ),
				'test_name'  => sanitize_text_field( $data['test_name'] ?? '' ),
				'test_date'  => sanitize_text_field( $data['test_date'] ?? '' ),
				'status'     => sanitize_key( $data['status'] ?? 'pending' ),
				'result'     => sanitize_textarea_field( $data['result'] ?? '' ),
				'document_id'=> absint( $data['document_id'] ?? 0 ),
				'created_at' => $now,
				'updated_at' => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
			)
		);

		return false === $result
			? new \WP_Error( 'lab_test_create_failed', $wpdb->last_error )
			: absint( $wpdb->insert_id );
	}

	public static function delete( $id ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Database::table( 'product_lab_tests' ),
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}
}
