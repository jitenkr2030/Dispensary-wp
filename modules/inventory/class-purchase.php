<?php
namespace Dispensary_WP\Modules\Inventory;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Purchase {

	public static function create( $data ) {
		global $wpdb;

		$table = Database::table( 'purchases' );
		$now   = current_time( 'mysql', true );

		$number = sanitize_text_field(
			$data['purchase_number'] ?? ''
		);

		if ( ! $number ) {
			$number = 'PO-' . gmdate( 'YmdHis' ) . '-' . wp_rand( 100, 999 );
		}

		$result = $wpdb->insert(
			$table,
			array(
				'supplier_id'    => absint( $data['supplier_id'] ?? 0 ),
				'purchase_number'=> $number,
				'status'         => sanitize_key( $data['status'] ?? 'draft' ),
				'subtotal'       => max( 0, (float) ( $data['subtotal'] ?? 0 ) ),
				'tax'            => max( 0, (float) ( $data['tax'] ?? 0 ) ),
				'total'          => max( 0, (float) ( $data['total'] ?? 0 ) ),
				'purchase_date'  => sanitize_text_field( $data['purchase_date'] ?? '' ),
				'notes'          => sanitize_textarea_field( $data['notes'] ?? '' ),
				'created_by'     => get_current_user_id(),
				'created_at'     => $now,
				'updated_at'     => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%f',
				'%f',
				'%f',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
			)
		);

		return false === $result
			? new \WP_Error( 'purchase_create_failed', $wpdb->last_error )
			: absint( $wpdb->insert_id );
	}

	public static function find( $id ) {
		global $wpdb;

		$table = Database::table( 'purchases' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE id = %d",
				absint( $id )
			),
			ARRAY_A
		);
	}

	public static function all( $limit = 100 ) {
		global $wpdb;

		$table = Database::table( 'purchases' );

		$limit = max(
			1,
			min( 500, absint( $limit ) )
		);

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				ORDER BY id DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);
	}
}
