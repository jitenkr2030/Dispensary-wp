<?php
/**
 * WooCommerce order synchronization.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order_Sync {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'woocommerce_checkout_order_processed',
			array( $this, 'order_created' ),
			20,
			3
		);

		add_action(
			'woocommerce_order_status_changed',
			array( $this, 'order_status_changed' ),
			20,
			4
		);
	}

	/**
	 * Handle new WooCommerce order.
	 *
	 * @param int      $order_id Order ID.
	 * @param array    $posted_data Checkout data.
	 * @param \WC_Order $order Order object.
	 */
	public function order_created( $order_id, $posted_data, $order ) {

		if ( ! $order instanceof \WC_Order ) {
			return;
		}

		update_post_meta(
			$order_id,
			'_dispensary_wp_order_sync',
			'1'
		);

		update_post_meta(
			$order_id,
			'_dispensary_wp_order_status',
			sanitize_key( $order->get_status() )
		);
	}

	/**
	 * Sync order status.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $old_status Old status.
	 * @param string $new_status New status.
	 * @param object $order Order.
	 */
	public function order_status_changed(
		$order_id,
		$old_status,
		$new_status,
		$order
	) {

		update_post_meta(
			$order_id,
			'_dispensary_wp_order_status',
			sanitize_key( $new_status )
		);

		do_action(
			'dispensary_wp_woocommerce_order_status_synced',
			$order_id,
			$old_status,
			$new_status,
			$order
		);
	}
}
