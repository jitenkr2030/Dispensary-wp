<?php
/**
 * Delivery route model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Delivery;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delivery_Route {

	protected $table;

	public function __construct() {
		$this->table = Database::table( 'delivery_routes' );
	}

	public function create( $data ) {

		global $wpdb;

		$result = $wpdb->insert(
			$this->table,
			$data
		);

		if ( false === $result ) {
			return false;
		}

		return absint( $wpdb->insert_id );
	}

	public function find( $id ) {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table}
				WHERE id = %d
				LIMIT 1",
				absint( $id )
			)
		);
	}

	public function all() {

		global $wpdb;

		return $wpdb->get_results(
			"SELECT * FROM {$this->table}
			ORDER BY id DESC"
		);
	}

	public function update( $id, $data ) {

		global $wpdb;

		return false !== $wpdb->update(
			$this->table,
			$data,
			array(
				'id' => absint( $id ),
			)
		);
	}
}
