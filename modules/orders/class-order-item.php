<?php
/**
 * Order item model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Orders;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order_Item {

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
		$this->table = Database::table( 'order_items' );
	}

	/**
	 * Create item.
	 *
	 * @param array $data Item data.
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
				'%d',
				'%s',
				'%s',
				'%d',
				'%f',
				'%f',
				'%f',
				'%f',
				'%s',
			)
		);

		if ( false === $result ) {
			return false;
		}

		return absint( $wpdb->insert_id );
	}

	/**
	 * Get items for order.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function get_by_order( $order_id ) {

		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE order_id = %d ORDER BY id ASC",
				absint( $order_id )
			)
		);
	}

	/**
	 * Delete order items.
	 *
	 * @param int $order_id Order ID.
	 * @return bool
	 */
	public function delete_by_order( $order_id ) {

		global $wpdb;

		return false !== $wpdb->delete(
			$this->table,
			array(
				'order_id' => absint( $order_id ),
			),
			array( '%d' )
		);
	}
}
