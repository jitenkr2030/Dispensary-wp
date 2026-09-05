<?php
/**
 * POS sale model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\POS;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS_Sale {

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
		$this->table = Database::table( 'pos_sales' );
	}

	/**
	 * Create sale.
	 *
	 * @param array $data Sale data.
	 * @return int|false
	 */
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

	/**
	 * Find sale.
	 *
	 * @param int $id Sale ID.
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
	 * List sales.
	 *
	 * @param int $limit Limit.
	 * @return array
	 */
	public function all( $limit = 50 ) {

		global $wpdb;

		$limit = min( 100, max( 1, absint( $limit ) ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table}
				ORDER BY id DESC
				LIMIT %d",
				$limit
			)
		);
	}
}
