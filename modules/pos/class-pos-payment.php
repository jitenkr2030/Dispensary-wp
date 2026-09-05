<?php
/**
 * POS payment model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\POS;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS_Payment {

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
		$this->table = Database::table( 'pos_payments' );
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
			$data
		);

		if ( false === $result ) {
			return false;
		}

		return absint( $wpdb->insert_id );
	}

	/**
	 * Get payments for sale.
	 *
	 * @param int $sale_id Sale ID.
	 * @return array
	 */
	public function get_by_sale( $sale_id ) {

		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$this->table}
				WHERE sale_id = %d
				ORDER BY id ASC",
				absint( $sale_id )
			)
		);
	}
}
