<?php
/**
 * WooCommerce stock synchronization.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stock_Sync {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'woocommerce_product_set_stock',
			array( $this, 'stock_updated' ),
			20,
			1
		);

		add_action(
			'woocommerce_variation_set_stock',
			array( $this, 'stock_updated' ),
			20,
			1
		);
	}

	/**
	 * Handle stock update.
	 *
	 * @param \WC_Product $product Product.
	 */
	public function stock_updated( $product ) {

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$product_id = $product->get_id();
		$stock      = $product->get_stock_quantity();

		update_post_meta(
			$product_id,
			'_dispensary_wp_wc_stock',
			$stock
		);

		update_post_meta(
			$product_id,
			'_dispensary_wp_wc_stock_status',
			sanitize_key( $product->get_stock_status() )
		);

		do_action(
			'dispensary_wp_woocommerce_stock_synced',
			$product_id,
			$stock,
			$product
		);
	}
}
