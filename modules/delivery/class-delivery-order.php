<?php
/**
 * Delivery order model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Delivery;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delivery_Order {

	protected $table;

	public function __construct() {
		$this->table = Database::table( 'delivery_orders' );
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

	public function find_by_order( $order_id ) {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table}
				WHERE order_id = %d
				LIMIT 1",
				absint( $order_id )
			)
		);
	}

	public function all( $args = array() ) {

		global $wpdb;

		$limit = isset( $args['limit'] )
			? min( 100, max( 1, absint( $args['limit'] ) ) )
			: 50;

		$status = isset( $args['status'] )
			? sanitize_key( $args['status'] )
			: '';

		if ( $status ) {

			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$this->table}
					WHERE status = %s
					ORDER BY id DESC
					LIMIT %d",
					$status,
					$limit
				)
			);
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table}
				ORDER BY id DESC
				LIMIT %d",
				$limit
			)
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
