<?php

namespace Dispensary_WP\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Permissions {

	/**
	 * Check whether the current user can perform an action.
	 *
	 * @param string $capability Capability.
	 * @return bool
	 */
	public static function can( $capability ) {

		return Capabilities::can( $capability );
	}

	/**
	 * Require capability.
	 *
	 * Stops execution with a WordPress error when permission is missing.
	 *
	 * @param string $capability Capability.
	 * @return true
	 */
	public static function require_capability( $capability ) {

		if ( ! self::can( $capability ) ) {
			wp_die(
				esc_html__(
					'You do not have permission to perform this action.',
					'dispensary-wp'
				),
				esc_html__(
					'Permission Denied',
					'dispensary-wp'
				),
				array(
					'response' => 403,
				)
			);
		}

		return true;
	}

	/**
	 * Check whether the current user is a dispensary manager.
	 *
	 * @return bool
	 */
	public static function is_manager() {

		return current_user_can( 'dispensary_manage' );
	}

	/**
	 * Check whether the current user can manage products.
	 *
	 * @return bool
	 */
	public static function can_manage_products() {

		return self::can( 'dispensary_manage_products' );
	}

	/**
	 * Check whether the current user can manage inventory.
	 *
	 * @return bool
	 */
	public static function can_manage_inventory() {

		return self::can( 'dispensary_manage_inventory' );
	}

	/**
	 * Check whether the current user can manage customers.
	 *
	 * @return bool
	 */
	public static function can_manage_customers() {

		return self::can( 'dispensary_manage_customers' );
	}

	/**
	 * Check whether the current user can manage orders.
	 *
	 * @return bool
	 */
	public static function can_manage_orders() {

		return self::can( 'dispensary_manage_orders' );
	}

	/**
	 * Check whether the current user can use POS.
	 *
	 * @return bool
	 */
	public static function can_use_pos() {

		return self::can( 'dispensary_use_pos' );
	}

	/**
	 * Check whether the current user can manage delivery.
	 *
	 * @return bool
	 */
	public static function can_manage_delivery() {

		return self::can( 'dispensary_manage_delivery' );
	}

	/**
	 * Check whether the current user can manage staff.
	 *
	 * @return bool
	 */
	public static function can_manage_staff() {

		return self::can( 'dispensary_manage_staff' );
	}

	/**
	 * Check whether the current user can manage compliance.
	 *
	 * @return bool
	 */
	public static function can_manage_compliance() {

		return self::can( 'dispensary_manage_compliance' );
	}

	/**
	 * Get current user's WordPress roles.
	 *
	 * @return array
	 */
	public static function get_current_user_roles() {

		$user = wp_get_current_user();

		if ( ! $user || empty( $user->roles ) ) {
			return array();
		}

		return array_map(
			'sanitize_key',
			$user->roles
		);
	}
}
