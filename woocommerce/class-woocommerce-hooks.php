<?php
/**
 * WooCommerce integration hooks.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooCommerce_Hooks {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_filter(
			'woocommerce_product_is_visible',
			array( $this, 'product_visibility' ),
			20,
			2
		);

		add_filter(
			'woocommerce_checkout_fields',
			array( $this, 'checkout_fields' ),
			20
		);
	}

	/**
	 * Product visibility hook.
	 *
	 * This currently preserves WooCommerce behaviour.
	 *
	 * @param bool $visible Visibility.
	 * @param int  $product_id Product ID.
	 * @return bool
	 */
	public function product_visibility( $visible, $product_id ) {

		return $visible;
	}

	/**
	 * Add optional dispensary checkout fields.
	 *
	 * @param array $fields Checkout fields.
	 * @return array
	 */
	public function checkout_fields( $fields ) {

		if ( isset( $fields['billing'] ) ) {

			$fields['billing']['dispensary_customer_note'] = array(
				'type'        => 'textarea',
				'label'       => __( 'Additional Order Note', 'dispensary-wp' ),
				'placeholder' => __( 'Enter any additional information.', 'dispensary-wp' ),
				'required'    => false,
				'class'       => array( 'form-row-wide' ),
				'priority'    => 120,
			);
		}

		return $fields;
	}
}
