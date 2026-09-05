<?php
/**
 * Loyalty service.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Loyalty;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loyalty {

	/**
	 * Create loyalty member.
	 *
	 * @param array $data Data.
	 * @return int|\WP_Error
	 */
	public static function create_member( $data ) {
		global $wpdb;

		$customer_id = isset( $data['customer_id'] )
			? absint( $data['customer_id'] )
			: 0;

		if ( ! $customer_id ) {
			return new \WP_Error(
				'invalid_customer',
				__( 'Customer ID is required.', 'dispensary-wp' )
			);
		}

		if ( Loyalty_Member::find_by_customer( $customer_id ) ) {
			return new \WP_Error(
				'already_member',
				__( 'Customer is already a loyalty member.', 'dispensary-wp' )
			);
		}

		$member_code = isset( $data['member_code'] )
			? sanitize_text_field( $data['member_code'] )
			: '';

		if ( ! $member_code ) {
			$member_code = 'LOY-' . strtoupper( wp_generate_password( 8, false, false ) );
		}

		$tier = isset( $data['tier'] )
			? sanitize_text_field( $data['tier'] )
			: 'standard';

		$points_balance = isset( $data['points_balance'] )
			? absint( $data['points_balance'] )
			: 0;

		$status = isset( $data['status'] )
			? sanitize_key( $data['status'] )
			: 'active';

		if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$status = 'active';
		}

		$now   = current_time( 'mysql', true );
		$table = \Dispensary_WP\Database\Database::table( 'loyalty_members' );

		$result = $wpdb->insert(
			$table,
			array(
				'customer_id'    => $customer_id,
				'member_code'    => $member_code,
				'tier'           => $tier,
				'points_balance' => $points_balance,
				'status'         => $status,
				'created_at'     => $now,
				'updated_at'     => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'loyalty_member_create_failed',
				__( 'Unable to create loyalty member.', 'dispensary-wp' )
			);
		}

		$id = (int) $wpdb->insert_id;

		if ( class_exists( '\Dispensary_WP\Security\Audit_Log' ) ) {
			\Dispensary_WP\Security\Audit_Log::log(
				'loyalty_member_created',
				'loyalty_member',
				$id,
				array(
					'customer_id' => $customer_id,
				)
			);
		}

		return $id;
	}

	/**
	 * Get member.
	 *
	 * @param int $id Member ID.
	 * @return object|null
	 */
	public static function get_member( $id ) {
		return Loyalty_Member::find( $id );
	}

	/**
	 * Find customer loyalty membership.
	 *
	 * @param int $customer_id Customer ID.
	 * @return object|null
	 */
	public static function get_customer_member( $customer_id ) {
		return Loyalty_Member::find_by_customer( $customer_id );
	}

	/**
	 * List members.
	 *
	 * @param array $args Arguments.
	 * @return array
	 */
	public static function list_members( $args = array() ) {
		return Loyalty_Member::all( $args );
	}

	/**
	 * Add points.
	 *
	 * @param int    $member_id Member ID.
	 * @param int    $points Points.
	 * @param string $reason Reason.
	 * @param int    $order_id Order ID.
	 * @return int|\WP_Error
	 */
	public static function add_points(
		$member_id,
		$points,
		$reason = '',
		$order_id = 0
	) {
		return Loyalty_Points::add(
			$member_id,
			$points,
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
	 * @param int    $order_id Order ID.
	 * @return int|\WP_Error
	 */
	public static function redeem_points(
		$member_id,
		$points,
		$reason = '',
		$order_id = 0
	) {
		return Loyalty_Points::redeem(
			$member_id,
			$points,
			$reason,
			$order_id
		);
	}

	/**
	 * Get points transactions.
	 *
	 * @param int   $member_id Member ID.
	 * @param array $args Arguments.
	 * @return array
	 */
	public static function point_history( $member_id, $args = array() ) {
		return Loyalty_Points::transactions( $member_id, $args );
	}

	/**
	 * Create reward.
	 *
	 * @param array $data Reward data.
	 * @return int|\WP_Error
	 */
	public static function create_reward( $data ) {
		return Loyalty_Rewards::create( $data );
	}

	/**
	 * Get reward.
	 *
	 * @param int $id Reward ID.
	 * @return object|null
	 */
	public static function get_reward( $id ) {
		return Loyalty_Rewards::find( $id );
	}

	/**
	 * List rewards.
	 *
	 * @param string $status Status.
	 * @return array
	 */
	public static function list_rewards( $status = 'active' ) {
		return Loyalty_Rewards::all( $status );
	}
}
