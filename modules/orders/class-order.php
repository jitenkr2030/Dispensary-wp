<?php
/**
 * Order model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Orders;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order {

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
		$this->table = Database::table( 'orders' );
	}

	/**
	 * Find order.
	 *
	 * @param int $id Order ID.
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
	 * Find by order number.
	 *
	 * @param string $order_number Order number.
	 * @return object|null
	 */
	public function find_by_number( $order_number ) {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE order_number = %s LIMIT 1",
				sanitize_text_field( $order_number )
			)
		);
	}

	/**
	 * Get orders.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function all( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'customer_id' => 0,
			'status'      => '',
			'limit'       => 20,
			'offset'      => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['customer_id'] ) ) {
			$where[]  = 'customer_id = %d';
			$params[] = absint( $args['customer_id'] );
		}

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = sanitize_key( $args['status'] );
		}

		$limit  = min( 100, max( 1, absint( $args['limit'] ) ) );
		$offset = max( 0, absint( $args['offset'] ) );

		$sql = "SELECT * FROM {$this->table} WHERE "
			. implode( ' AND ', $where )
			. ' ORDER BY id DESC LIMIT %d OFFSET %d';

		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results(
			$wpdb->prepare( $sql, $params )
		);
	}

	/**
	 * Insert order.
	 *
	 * @param array $data Order data.
	 * @return int|false
	 */
	public function create( $data ) {

		global $wpdb;

		$result = $wpdb->insert(
			$this->table,
			$data,
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%f',
				'%f',
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

		if ( false === $result ) {
			return false;
		}

		return absint( $wpdb->insert_id );
	}

	/**
	 * Update order.
	 *
	 * @param int   $id   Order ID.
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

	/**
	 * Delete order.
	 *
	 * @param int $id Order ID.
	 * @return bool
	 */
	public function delete( $id ) {

		global $wpdb;

		return false !== $wpdb->delete(
			$this->table,
			array(
				'id' => absint( $id ),
			),
			array( '%d' )
		);
	}
}
