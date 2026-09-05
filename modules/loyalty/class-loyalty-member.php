<?php
/**
 * Loyalty member management.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Loyalty;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loyalty_Member {

	/**
	 * Find member by ID.
	 *
	 * @param int $id Member ID.
	 * @return object|null
	 */
	public static function find( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( ! $id ) {
			return null;
		}

		$table = \Dispensary_WP\Database\Database::table( 'loyalty_members' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				$id
			)
		);
	}

	/**
	 * Find by customer.
	 *
	 * @param int $customer_id Customer ID.
	 * @return object|null
	 */
	public static function find_by_customer( $customer_id ) {
		global $wpdb;

		$customer_id = absint( $customer_id );

		if ( ! $customer_id ) {
			return null;
		}

		$table = \Dispensary_WP\Database\Database::table( 'loyalty_members' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE customer_id = %d LIMIT 1",
				$customer_id
			)
		);
	}

	/**
	 * List members.
	 *
	 * @param array $args Arguments.
	 * @return array
	 */
	public static function all( $args = array() ) {
		global $wpdb;

		$table = \Dispensary_WP\Database\Database::table( 'loyalty_members' );

		$limit = isset( $args['limit'] )
			? min( 500, max( 1, absint( $args['limit'] ) ) )
			: 100;

		$offset = isset( $args['offset'] )
			? max( 0, absint( $args['offset'] ) )
			: 0;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				ORDER BY created_at DESC
				LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
	}
}
