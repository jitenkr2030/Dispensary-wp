<?php
/**
 * Order refund model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Orders;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order_Refund {

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
		$this->table = Database::table( 'order_refunds' );
	}

	/**
	 * Create refund.
	 *
	 * @param array $data Refund data.
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
				'%d',
				'%f',
				'%s',
				'%s',
				'%s',
				'%d',
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
	 * Get refund.
	 *
	 * @param int $id Refund ID.
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
	 * Get refunds by order.
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
}
