<?php
/**
 * WooCommerce customer synchronization.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customer_Sync {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'user_register',
			array( $this, 'customer_registered' ),
			20,
			1
		);

		add_action(
			'profile_update',
			array( $this, 'customer_updated' ),
			20,
			2
		);
	}

	/**
	 * Handle customer registration.
	 *
	 * @param int $user_id User ID.
	 */
	public function customer_registered( $user_id ) {

		if ( ! $user_id ) {
			return;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		if ( ! $user->user_email ) {
			return;
		}

		update_user_meta(
			$user_id,
			'_dispensary_wp_customer_sync',
			'1'
		);

		update_user_meta(
			$user_id,
			'_dispensary_wp_customer_email',
			sanitize_email( $user->user_email )
		);
	}

	/**
	 * Handle customer profile update.
	 *
	 * @param int      $user_id User ID.
	 * @param \WP_User $old_user_data Old user.
	 */
	public function customer_updated( $user_id, $old_user_data ) {

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		update_user_meta(
			$user_id,
			'_dispensary_wp_customer_email',
			sanitize_email( $user->user_email )
		);
	}
}
