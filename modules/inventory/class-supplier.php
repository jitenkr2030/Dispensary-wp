<?php
namespace Dispensary_WP\Modules\Inventory;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Supplier {

	public static function create( $data ) {
		global $wpdb;

		$table = Database::table( 'suppliers' );
		$now   = current_time( 'mysql', true );

		$result = $wpdb->insert(
			$table,
			array(
				'name'         => sanitize_text_field( $data['name'] ?? '' ),
				'company_name' => sanitize_text_field( $data['company_name'] ?? '' ),
				'email'        => sanitize_email( $data['email'] ?? '' ),
				'phone'        => sanitize_text_field( $data['phone'] ?? '' ),
				'address'      => sanitize_textarea_field( $data['address'] ?? '' ),
				'tax_number'   => sanitize_text_field( $data['tax_number'] ?? '' ),
				'status'       => sanitize_key( $data['status'] ?? 'active' ),
				'notes'        => sanitize_textarea_field( $data['notes'] ?? '' ),
				'created_at'   => $now,
				'updated_at'   => $now,
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		return false === $result
			? new \WP_Error( 'supplier_create_failed', $wpdb->last_error )
			: absint( $wpdb->insert_id );
	}

	public static function find( $id ) {
		global $wpdb;

		$table = Database::table( 'suppliers' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE id = %d",
				absint( $id )
			),
			ARRAY_A
		);
	}

	public static function all() {
		global $wpdb;

		$table = Database::table( 'suppliers' );

		return $wpdb->get_results(
			"SELECT * FROM {$table}
			ORDER BY name ASC",
			ARRAY_A
		);
	}
}
