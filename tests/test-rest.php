<?php
/**
 * REST API test definitions.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dispensary_WP_REST_Test {

	/**
	 * Required API namespace.
	 *
	 * @return string
	 */
	public static function namespace() {

		return 'dispensary-wp/v1';
	}

	/**
	 * Required endpoints.
	 *
	 * @return array
	 */
	public static function endpoints() {

		return array(
			'/auth/me',
			'/auth/status',
			'/products',
			'/inventory/stock/{product_id}',
			'/customers',
			'/orders',
			'/pos/registers',
			'/deliveries',
			'/staff',
			'/loyalty/members',
			'/loyalty/rewards',
			'/reports/sales',
			'/reports/inventory',
			'/reports/customers',
			'/reports/staff',
			'/reports/financial',
		);
	}
}
