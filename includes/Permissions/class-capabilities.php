<?php

namespace Dispensary_WP\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Capabilities {

	/**
	 * Plugin capabilities.
	 *
	 * @return array
	 */
	public static function get_all() {

		return array(
			'dispensary_manage',
			'dispensary_manage_settings',

			'dispensary_view_dashboard',

			'dispensary_manage_products',
			'dispensary_view_products',

			'dispensary_manage_inventory',
			'dispensary_view_inventory',

			'dispensary_manage_customers',
			'dispensary_view_customers',

			'dispensary_manage_orders',
			'dispensary_view_orders',

			'dispensary_manage_pos',
			'dispensary_use_pos',

			'dispensary_manage_delivery',
			'dispensary_view_delivery',

			'dispensary_manage_staff',
			'dispensary_view_staff',

			'dispensary_manage_loyalty',
			'dispensary_view_loyalty',

			'dispensary_view_reports',
			'dispensary_export_reports',

			'dispensary_manage_compliance',
			'dispensary_view_compliance',

			'dispensary_view_audit_logs',
		);
	}

	/**
	 * Check capability.
	 *
	 * @param string $capability Capability.
	 * @param int|null $user_id User ID.
	 * @return bool
	 */
	public static function can( $capability, $user_id = null ) {

		$capability = sanitize_key( $capability );

		if ( empty( $capability ) ) {
			return false;
		}

		if ( null === $user_id ) {
			return current_user_can( $capability );
		}

		return user_can( $user_id, $capability );
	}
}
