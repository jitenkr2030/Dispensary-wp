<?php

namespace Dispensary_WP\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Roles {

	const MANAGER = 'dispensary_manager';
	const STAFF   = 'dispensary_staff';
	const CASHIER = 'dispensary_cashier';
	const DRIVER  = 'dispensary_driver';

	/**
	 * Install plugin roles and capabilities.
	 *
	 * @return void
	 */
	public static function install() {

		$administrator = get_role( 'administrator' );

		if ( $administrator ) {
			foreach ( Capabilities::get_all() as $capability ) {
				$administrator->add_cap( $capability );
			}
		}

		self::create_manager_role();
		self::create_staff_role();
		self::create_cashier_role();
		self::create_driver_role();

		update_option(
			'dispensary_wp_roles_version',
			DISPENSARY_WP_VERSION,
			false
		);
	}

	/**
	 * Create manager role.
	 *
	 * @return void
	 */
	private static function create_manager_role() {

		$role = get_role( self::MANAGER );

		if ( ! $role ) {
			$role = add_role(
				self::MANAGER,
				__( 'Dispensary Manager', 'dispensary-wp' ),
				array(
					'read' => true,
				)
			);
		}

		if ( ! $role ) {
			return;
		}

		foreach ( Capabilities::get_all() as $capability ) {
			$role->add_cap( $capability );
		}
	}

	/**
	 * Create staff role.
	 *
	 * @return void
	 */
	private static function create_staff_role() {

		$role = get_role( self::STAFF );

		if ( ! $role ) {
			$role = add_role(
				self::STAFF,
				__( 'Dispensary Staff', 'dispensary-wp' ),
				array(
					'read' => true,
				)
			);
		}

		if ( ! $role ) {
			return;
		}

		$capabilities = array(
			'dispensary_view_dashboard',

			'dispensary_view_products',

			'dispensary_view_inventory',

			'dispensary_view_customers',
			'dispensary_manage_customers',

			'dispensary_view_orders',
			'dispensary_manage_orders',

			'dispensary_use_pos',

			'dispensary_view_delivery',

			'dispensary_view_loyalty',
			'dispensary_manage_loyalty',

			'dispensary_view_compliance',
		);

		foreach ( $capabilities as $capability ) {
			$role->add_cap( $capability );
		}
	}

	/**
	 * Create cashier role.
	 *
	 * @return void
	 */
	private static function create_cashier_role() {

		$role = get_role( self::CASHIER );

		if ( ! $role ) {
			$role = add_role(
				self::CASHIER,
				__( 'Dispensary Cashier', 'dispensary-wp' ),
				array(
					'read' => true,
				)
			);
		}

		if ( ! $role ) {
			return;
		}

		$capabilities = array(
			'dispensary_view_dashboard',

			'dispensary_view_products',

			'dispensary_view_customers',

			'dispensary_view_orders',
			'dispensary_manage_orders',

			'dispensary_use_pos',

			'dispensary_view_loyalty',

			'dispensary_view_compliance',
		);

		foreach ( $capabilities as $capability ) {
			$role->add_cap( $capability );
		}
	}

	/**
	 * Create delivery driver role.
	 *
	 * @return void
	 */
	private static function create_driver_role() {

		$role = get_role( self::DRIVER );

		if ( ! $role ) {
			$role = add_role(
				self::DRIVER,
				__( 'Dispensary Driver', 'dispensary-wp' ),
				array(
					'read' => true,
				)
			);
		}

		if ( ! $role ) {
			return;
		}

		$capabilities = array(
			'dispensary_view_dashboard',
			'dispensary_view_orders',
			'dispensary_view_delivery',
			'dispensary_manage_delivery',
		);

		foreach ( $capabilities as $capability ) {
			$role->add_cap( $capability );
		}
	}

	/**
	 * Remove plugin roles.
	 *
	 * Administrator capabilities are also removed.
	 *
	 * @return void
	 */
	public static function uninstall() {

		remove_role( self::MANAGER );
		remove_role( self::STAFF );
		remove_role( self::CASHIER );
		remove_role( self::DRIVER );

		$administrator = get_role( 'administrator' );

		if ( $administrator ) {
			foreach ( Capabilities::get_all() as $capability ) {
				$administrator->remove_cap( $capability );
			}
		}

		delete_option( 'dispensary_wp_roles_version' );
	}
}
