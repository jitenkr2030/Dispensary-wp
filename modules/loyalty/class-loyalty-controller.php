<?php
/**
 * Loyalty controller/service facade.
 *
 * REST endpoints will be added in Module 15.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Loyalty;

use Dispensary_WP\Permissions\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loyalty_Controller {

	/**
	 * Create member.
	 *
	 * @param array $data Data.
	 * @return int|\WP_Error
	 */
	public static function create_member( $data ) {

		if ( ! Permissions::can_manage_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to manage loyalty.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Loyalty::create_member( $data );
	}

	/**
	 * Get member.
	 *
	 * @param int $id Member ID.
	 * @return object|\WP_Error
	 */
	public static function get_member( $id ) {

		if ( ! Permissions::can_view_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to view loyalty.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		$member = Loyalty::get_member( $id );

		if ( ! $member ) {
			return new \WP_Error(
				'not_found',
				__( 'Loyalty member not found.', 'dispensary-wp' ),
				array( 'status' => 404 )
			);
		}

		return $member;
	}

	/**
	 * List members.
	 *
	 * @param array $args Arguments.
	 * @return array|\WP_Error
	 */
	public static function list_members( $args = array() ) {

		if ( ! Permissions::can_view_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to view loyalty.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Loyalty::list_members( $args );
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

		if ( ! Permissions::can_manage_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to manage loyalty points.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Loyalty::add_points(
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

		if ( ! Permissions::can_manage_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to redeem loyalty points.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Loyalty::redeem_points(
			$member_id,
			$points,
			$reason,
			$order_id
		);
	}

	/**
	 * Point history.
	 *
	 * @param int   $member_id Member ID.
	 * @param array $args Arguments.
	 * @return array|\WP_Error
	 */
	public static function point_history( $member_id, $args = array() ) {

		if ( ! Permissions::can_view_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to view loyalty history.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Loyalty::point_history( $member_id, $args );
	}

	/**
	 * Create reward.
	 *
	 * @param array $data Reward data.
	 * @return int|\WP_Error
	 */
	public static function create_reward( $data ) {

		if ( ! Permissions::can_manage_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to manage rewards.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Loyalty::create_reward( $data );
	}

	/**
	 * Get reward.
	 *
	 * @param int $id Reward ID.
	 * @return object|\WP_Error
	 */
	public static function get_reward( $id ) {

		if ( ! Permissions::can_view_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to view rewards.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Loyalty::get_reward( $id );
	}

	/**
	 * List rewards.
	 *
	 * @param string $status Status.
	 * @return array|\WP_Error
	 */
	public static function list_rewards( $status = 'active' ) {

		if ( ! Permissions::can_view_loyalty() ) {
			return new \WP_Error(
				'forbidden',
				__( 'You do not have permission to view rewards.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Loyalty::list_rewards( $status );
	}
}
