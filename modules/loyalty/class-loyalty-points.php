<?php
/**
 * Loyalty points management.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Loyalty;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loyalty_Points {

	/**
	 * Add points.
	 *
	 * @param int    $member_id Member ID.
	 * @param int    $points Points.
	 * @param string $reason Reason.
	 * @param int    $order_id Optional order ID.
	 * @return int|\WP_Error
	 */
	public static function add( $member_id, $points, $reason = '', $order_id = 0 ) {
		return self::change(
			$member_id,
			absint( $points ),
			'earned',
			$reason,
			$order_id
		);
	}

	/**
	 * Redeem points.
	 *
	 * @param int    $member_id Member ID.
	 * @param int    $points Points.
	 * @param string $reason Reason.
	 * @param int    $order_id Optional order ID.
	 * @return int|\WP_Error
	 */
	public static function redeem( $member_id, $points, $reason = '', $order_id = 0 ) {
		$points = absint( $points );

		$member = Loyalty_Member::find( $member_id );

		if ( ! $member ) {
			return new \WP_Error(
				'loyalty_member_not_found',
				__( 'Loyalty member not found.', 'dispensary-wp' )
			);
		}

		if ( (int) $member->points_balance < $points ) {
			return new \WP_Error(
				'insufficient_points',
				__( 'Insufficient loyalty points.', 'dispensary-wp' )
			);
		}

		return self::change(
			$member_id,
			-$points,
			'redeemed',
			$reason,
			$order_id
		);
	}

	/**
	 * Change points.
	 *
	 * @param int    $member_id Member ID.
	 * @param int    $points Amount.
	 * @param string $type Transaction type.
	 * @param string $reason Reason.
	 * @param int    $order_id Order ID.
	 * @return int|\WP_Error
	 */
	private static function change( $member_id, $points, $type, $reason, $order_id ) {
		global $wpdb;

		$member_id = absint( $member_id );

		if ( ! Loyalty_Member::find( $member_id ) ) {
			return new \WP_Error(
				'loyalty_member_not_found',
				__( 'Loyalty member not found.', 'dispensary-wp' )
			);
		}

		if ( 0 === $points ) {
			return new \WP_Error(
				'invalid_points',
				__( 'Points must be greater than zero.', 'dispensary-wp' )
			);
		}

		$reason  = sanitize_text_field( $reason );
		$order_id = absint( $order_id );
		$now     = current_time( 'mysql', true );

		$transactions = \Dispensary_WP\Database\Database::table( 'loyalty_points' );
		$members      = \Dispensary_WP\Database\Database::table( 'loyalty_members' );

		$result = $wpdb->insert(
			$transactions,
			array(
				'member_id' => $member_id,
				'order_id'  => $order_id,
				'points'    => $points,
				'type'      => sanitize_key( $type ),
				'reason'    => $reason,
				'created_by'=> get_current_user_id(),
				'created_at'=> $now,
			),
			array(
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'points_transaction_failed',
				__( 'Unable to create points transaction.', 'dispensary-wp' )
			);
		}

		$transaction_id = (int) $wpdb->insert_id;

		$balance_result = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$members}
				SET points_balance = points_balance + %d,
					updated_at = %s
				WHERE id = %d",
				$points,
				$now,
				$member_id
			)
		);

		if ( false === $balance_result ) {
			return new \WP_Error(
				'points_balance_failed',
				__( 'Unable to update points balance.', 'dispensary-wp' )
			);
		}

		if ( class_exists( '\Dispensary_WP\Security\Audit_Log' ) ) {
			\Dispensary_WP\Security\Audit_Log::log(
				'loyalty_points_changed',
				'loyalty_member',
				$member_id,
				array(
					'points' => $points,
					'type'   => $type,
					'order_id' => $order_id,
				)
			);
		}

		return $transaction_id;
	}

	/**
	 * Get transactions.
	 *
	 * @param int   $member_id Member ID.
	 * @param array $args Arguments.
	 * @return array
	 */
	public static function transactions( $member_id, $args = array() ) {
		global $wpdb;

		$member_id = absint( $member_id );
		$limit     = isset( $args['limit'] )
			? min( 500, max( 1, absint( $args['limit'] ) ) )
			: 100;

		$table = \Dispensary_WP\Database\Database::table( 'loyalty_points' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE member_id = %d
				ORDER BY created_at DESC
				LIMIT %d",
				$member_id,
				$limit
			)
		);
	}
}
