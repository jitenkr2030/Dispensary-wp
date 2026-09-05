<?php
/**
 * WooCommerce integration bootstrap.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooCommerce {

	/**
	 * Constructor.
	 */
	public function __construct() {

		if ( ! $this->is_available() ) {
			return;
		}

		new Product_Sync();
		new Order_Sync();
		new Customer_Sync();
		new Stock_Sync();
		new WooCommerce_Hooks();
	}

	/**
	 * Check WooCommerce availability.
	 *
	 * @return bool
	 */
	public function is_available() {

		return class_exists( 'WooCommerce' );
	}
}
