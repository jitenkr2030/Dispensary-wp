<?php
/**
 * Admin dashboard.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Admin;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Dashboard {

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Get dashboard statistics.
	 *
	 * @return array
	 */
	public static function statistics() {
		global $wpdb;

		$customers = Database::table( 'customers' );
		$orders    = Database::table( 'orders' );
		$products  = Database::table( 'products' );

		$data = array(
			'customers' => 0,
			'orders'    => 0,
			'products'  => 0,
			'revenue'   => 0,
		);

		$data['customers'] = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$customers}"
		);

		$data['orders'] = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$orders}"
		);

		$data['products'] = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$products}"
		);

		$data['revenue'] = (float) $wpdb->get_var(
			"SELECT COALESCE(SUM(total), 0)
			FROM {$orders}
			WHERE status NOT IN ('cancelled', 'failed')"
		);

		return $data;
	}
}
