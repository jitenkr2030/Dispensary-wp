<?php
/**
 * Module integration test definitions.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dispensary_WP_Module_Test {

	/**
	 * Required modules.
	 *
	 * @return array
	 */
	public static function required_modules() {

		return array(
			'products'  => '\Dispensary_WP\Modules\Products\Products',
			'inventory' => '\Dispensary_WP\Modules\Inventory\Inventory',
			'customers' => '\Dispensary_WP\Modules\Customers\Customers',
			'orders'    => '\Dispensary_WP\Modules\Orders\Orders',
			'pos'       => '\Dispensary_WP\Modules\POS\POS',
			'delivery'  => '\Dispensary_WP\Modules\Delivery\Delivery',
			'staff'     => '\Dispensary_WP\Modules\Staff\Staff',
			'loyalty'   => '\Dispensary_WP\Modules\Loyalty\Loyalty',
			'reports'   => '\Dispensary_WP\Modules\Reports\Reports',
		);
	}
}
