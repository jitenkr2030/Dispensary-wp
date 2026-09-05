<?php
/**
 * Proof of delivery model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Delivery;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Proof_Of_Delivery {

	protected $table;

	public function __construct() {
		$this->table = Database::table( 'proof_of_delivery' );
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

	public function find_by_delivery( $delivery_id ) {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table}
				WHERE delivery_id = %d
				ORDER BY id DESC
				LIMIT 1",
				absint( $delivery_id )
			)
		);
	}
}
