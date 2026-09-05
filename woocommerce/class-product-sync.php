<?php
/**
 * WooCommerce product synchronization.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Sync {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'save_post_product',
			array( $this, 'sync_product' ),
			20,
			3
		);
	}

	/**
	 * Sync WooCommerce product into Dispensary product system.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @param bool     $update Whether updating.
	 */
	public function sync_product( $post_id, $post, $update ) {

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( 'product' !== $post->post_type ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		$product = wc_get_product( $post_id );

		if ( ! $product ) {
			return;
		}

		$sku   = $product->get_sku();
		$name  = $product->get_name();
		$price = $product->get_price();

		/**
		 * Store mapping between WooCommerce and Dispensary WP.
		 */
		update_post_meta(
			$post_id,
			'_dispensary_wp_product_sync',
			'1'
		);

		update_post_meta(
			$post_id,
			'_dispensary_wp_product_sku',
			sanitize_text_field( $sku )
		);

		update_post_meta(
			$post_id,
			'_dispensary_wp_product_name',
			sanitize_text_field( $name )
		);

		update_post_meta(
			$post_id,
			'_dispensary_wp_product_price',
			wc_format_decimal( $price )
		);
	}
}
