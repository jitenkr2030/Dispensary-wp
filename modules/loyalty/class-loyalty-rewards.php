<?php
/**
 * Loyalty rewards.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Loyalty;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loyalty_Rewards {

	/**
	 * Create reward.
	 *
	 * @param array $data Reward data.
	 * @return int|\WP_Error
	 */
	public static function create( $data ) {
		global $wpdb;

		$name = isset( $data['name'] )
			? sanitize_text_field( $data['name'] )
			: '';

		if ( ! $name ) {
			return new \WP_Error(
				'invalid_reward_name',
				__( 'Reward name is required.', 'dispensary-wp' )
			);
		}

		$points_required = isset( $data['points_required'] )
			? absint( $data['points_required'] )
			: 0;

		if ( $points_required <= 0 ) {
			return new \WP_Error(
				'invalid_reward_points',
				__( 'Points required must be greater than zero.', 'dispensary-wp' )
			);
		}

		$description = isset( $data['description'] )
			? sanitize_textarea_field( $data['description'] )
			: '';

		$reward_type = isset( $data['reward_type'] )
			? sanitize_key( $data['reward_type'] )
			: 'discount';

		$allowed_types = array(
			'discount',
			'free_product',
			'cash_value',
			'custom',
		);

		if ( ! in_array( $reward_type, $allowed_types, true ) ) {
			return new \WP_Error(
				'invalid_reward_type',
				__( 'Invalid reward type.', 'dispensary-wp' )
			);
		}

		$value = isset( $data['value'] )
			? (float) $data['value']
			: 0;

		$status = isset( $data['status'] )
			? sanitize_key( $data['status'] )
			: 'active';

		if ( ! in_array( $status, array( 'active', 'inactive' ), true ) ) {
			$status = 'active';
		}

		$now   = current_time( 'mysql', true );
		$table = \Dispensary_WP\Database\Database::table( 'loyalty_rewards' );

		$result = $wpdb->insert(
			$table,
			array(
				'name'            => $name,
				'description'     => $description,
				'points_required' => $points_required,
				'reward_type'     => $reward_type,
				'value'           => $value,
				'status'          => $status,
				'created_by'      => get_current_user_id(),
				'created_at'      => $now,
				'updated_at'      => $now,
			),
			array(
				'%s',
				'%s',
				'%d',
				'%s',
				'%f',
				'%s',
				'%d',
				'%s',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'reward_create_failed',
				__( 'Unable to create reward.', 'dispensary-wp' )
			);
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Get reward.
	 *
	 * @param int $id Reward ID.
	 * @return object|null
	 */
	public static function find( $id ) {
		global $wpdb;

		$table = \Dispensary_WP\Database\Database::table( 'loyalty_rewards' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				absint( $id )
			)
		);
	}

	/**
	 * List rewards.
	 *
	 * @param string $status Status.
	 * @return array
	 */
	public static function all( $status = 'active' ) {
		global $wpdb;

		$table = \Dispensary_WP\Database\Database::table( 'loyalty_rewards' );

		if ( $status ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table}
					WHERE status = %s
					ORDER BY points_required ASC",
					sanitize_key( $status )
				)
			);
		}

		return $wpdb->get_results(
			"SELECT * FROM {$table}
			ORDER BY points_required ASC"
		);
	}
}
