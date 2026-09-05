<?php
/**
 * Order payment model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Orders;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order_Payment {

	/**
	 * Database table.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->table = Database::table( 'order_payments' );
	}

	/**
	 * Create payment.
	 *
	 * @param array $data Payment data.
	 * @return int|false
	 */
	public function create( $data ) {

		global $wpdb;

		$result = $wpdb->insert(
			$this->table,
			$data,
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%f',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( false === $result ) {
			return false;
		}

		return absint( $wpdb->insert_id );
	}

	/**
	 * Get payment.
	 *
	 * @param int $id Payment ID.
	 * @return object|null
	 */
	public function find( $id ) {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d LIMIT 1",
				absint( $id )
			)
		);
	}

	/**
	 * Get payments by order.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function get_by_order( $order_id ) {

		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE order_id = %d ORDER BY id DESC",
				absint( $order_id )
			)
		);
	}

	/**
	 * Update payment.
	 *
	 * @param int   $id   Payment ID.
	 * @param array $data Data.
	 * @return bool
	 */
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
