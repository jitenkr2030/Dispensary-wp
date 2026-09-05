<?php
/**
 * POS session management.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\POS;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS_Session {

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
		$this->table = Database::table( 'pos_sessions' );
	}

	/**
	 * Open session.
	 *
	 * @param int   $register_id Register ID.
	 * @param float $opening_cash Opening cash.
	 * @return int|false
	 */
	public function open( $register_id, $opening_cash = 0 ) {

		global $wpdb;

		$now = current_time( 'mysql', true );

		$result = $wpdb->insert(
			$this->table,
			array(
				'register_id'  => absint( $register_id ),
				'user_id'      => get_current_user_id(),
				'status'       => 'open',
				'opening_cash' => max( 0, (float) $opening_cash ),
				'opened_at'    => $now,
				'created_at'   => $now,
			)
		);

		if ( false === $result ) {
			return false;
		}

		return absint( $wpdb->insert_id );
	}

	/**
	 * Close session.
	 *
	 * @param int   $session_id Session ID.
	 * @param float $closing_cash Closing cash.
	 * @return bool
	 */
	public function close( $session_id, $closing_cash = 0 ) {

		global $wpdb;

		return false !== $wpdb->update(
			$this->table,
			array(
				'status'       => 'closed',
				'closing_cash' => max( 0, (float) $closing_cash ),
				'closed_at'    => current_time( 'mysql', true ),
			),
			array(
				'id' => absint( $session_id ),
			)
		);
	}

	/**
	 * Get active session.
	 *
	 * @param int $user_id User ID.
	 * @return object|null
	 */
	public function get_active( $user_id = 0 ) {

		global $wpdb;

		$user_id = $user_id
			? absint( $user_id )
			: get_current_user_id();

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$this->table}
				WHERE user_id = %d
				AND status = 'open'
				ORDER BY id DESC
				LIMIT 1",
				$user_id
			)
		);
	}
}
