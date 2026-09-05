<?php
/**
 * Plugin health check.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Health_Check {

	/**
	 * Run static health checks.
	 *
	 * @return array
	 */
	public static function run() {

		global $wpdb;

		$checks = array();

		$checks['wordpress'] = array(
			'status'  => version_compare( get_bloginfo( 'version' ), '6.5', '>=' ),
			'value'   => get_bloginfo( 'version' ),
			'message' => 'WordPress version check',
		);

		$checks['php'] = array(
			'status'  => version_compare( PHP_VERSION, '7.4', '>=' ),
			'value'   => PHP_VERSION,
			'message' => 'PHP version check',
		);

		$checks['database'] = array(
			'status'  => ! empty( $wpdb ),
			'value'   => $wpdb ? 'available' : 'unavailable',
			'message' => 'WordPress database connection',
		);

		$required_classes = array(
			'\Dispensary_WP\Database\Database',
			'\Dispensary_WP\Permissions\Permissions',
			'\Dispensary_WP\Security\Security',
			'\Dispensary_WP\Modules\Products\Products',
			'\Dispensary_WP\Modules\Inventory\Inventory',
			'\Dispensary_WP\Modules\Customers\Customers',
			'\Dispensary_WP\Modules\Orders\Orders',
			'\Dispensary_WP\Modules\POS\POS',
			'\Dispensary_WP\Modules\Delivery\Delivery',
			'\Dispensary_WP\Modules\Staff\Staff',
			'\Dispensary_WP\Modules\Loyalty\Loyalty',
			'\Dispensary_WP\Modules\Reports\Reports',
		);

		foreach ( $required_classes as $class ) {

			$checks[ 'class_' . md5( $class ) ] = array(
				'status'  => class_exists( $class ),
				'value'   => $class,
				'message' => 'Required class availability',
			);
		}

		$checks['rest_api'] = array(
			'status'  => class_exists( '\Dispensary_WP\REST\REST_API' ),
			'value'   => 'REST API',
			'message' => 'REST API availability',
		);

		$checks['admin'] = array(
			'status'  => class_exists( '\Dispensary_WP\Admin\Admin' ),
			'value'   => 'Admin',
			'message' => 'Admin interface availability',
		);

		$checks['public'] = array(
			'status'  => class_exists( '\Dispensary_WP\Public_Frontend\Public_Frontend' ),
			'value'   => 'Public frontend',
			'message' => 'Frontend availability',
		);

		return $checks;
	}

	/**
	 * Determine whether all checks passed.
	 *
	 * @return bool
	 */
	public static function is_healthy() {

		$checks = self::run();

		foreach ( $checks as $check ) {

			if ( empty( $check['status'] ) ) {
				return false;
			}
		}

		return true;
	}
}
