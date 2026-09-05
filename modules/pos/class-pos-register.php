<?php
/**
 * POS register model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\POS;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS_Register {

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
		$this->table = Database::table( 'pos_registers' );
	}

	/**
	 * Create register.
	 *
	 * @param array $data Register data.
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
	 * Find register.
	 *
	 * @param int $id Register ID.
	 * @return object|null
	 */
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

	/**
	 * List active registers.
	 *
	 * @return array
	 */
	public function active() {

		global $wpdb;

		return $wpdb->get_results(
			"SELECT * FROM {$this->table}
			WHERE status = 'active'
			ORDER BY name ASC"
		);
	}
}
