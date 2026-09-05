<?php
/**
 * Database integration test definitions.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dispensary_WP_Database_Test {

	/**
	 * Required tables.
	 *
	 * @return array
	 */
	public static function required_tables() {

		return array(
			'settings',
			'audit_logs',
			'stock_movements',
			'batches',
			'lots',
			'suppliers',
			'purchases',
			'purchase_items',
			'customers',
			'customer_profiles',
			'customer_verifications',
			'customer_history',
			'orders',
			'order_items',
			'order_payments',
			'order_refunds',
			'order_status_history',
			'pos_registers',
			'pos_sessions',
			'pos_sales',
			'pos_sale_items',
			'pos_payments',
			'drivers',
			'delivery_zones',
			'delivery_routes',
			'delivery_orders',
			'proof_of_delivery',
			'staff_members',
			'staff_shifts',
			'staff_attendance',
			'loyalty_members',
			'loyalty_points',
			'loyalty_rewards',
			'loyalty_redemptions',
		);
	}
}
