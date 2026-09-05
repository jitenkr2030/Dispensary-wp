<?php
/**
 * Frontend controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Public_Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend_Controller {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action(
			'wp_ajax_dispensary_wp_order_status',
			array( $this, 'order_status' )
		);

		add_action(
			'wp_ajax_nopriv_dispensary_wp_order_status',
			array( $this, 'order_status' )
		);

		add_action(
			'wp_ajax_dispensary_wp_delivery_status',
			array( $this, 'delivery_status' )
		);

		add_action(
			'wp_ajax_nopriv_dispensary_wp_delivery_status',
			array( $this, 'delivery_status' )
		);
	}

	/**
	 * Verify public nonce.
	 *
	 * @return bool
	 */
	private function verify_nonce() {

		$nonce = isset( $_POST['nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['nonce'] ) )
			: '';

		return wp_verify_nonce(
			$nonce,
			'dispensary_wp_public'
		);
	}

	/**
	 * Order status AJAX endpoint.
	 */
	public function order_status() {

		if ( ! $this->verify_nonce() ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed.', 'dispensary-wp' ),
				),
				403
			);
		}

		$order_number = isset( $_POST['order_number'] )
			? sanitize_text_field( wp_unslash( $_POST['order_number'] ) )
			: '';

		if ( '' === $order_number ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please enter an order number.', 'dispensary-wp' ),
				),
				400
			);
		}

		$order = null;

		if ( class_exists( '\Dispensary_WP\Modules\Orders\Orders' ) ) {

			if ( method_exists( '\Dispensary_WP\Modules\Orders\Orders', 'find_by_number' ) ) {
				$order = \Dispensary_WP\Modules\Orders\Orders::find_by_number(
					$order_number
				);
			}
		}

		if ( ! $order ) {
			wp_send_json_error(
				array(
					'message' => __( 'Order not found.', 'dispensary-wp' ),
				),
				404
			);
		}

		$status = isset( $order->status )
			? $order->status
			: 'unknown';

		wp_send_json_success(
			array(
				'order_number' => $order_number,
				'status'       => sanitize_text_field( $status ),
			)
		);
	}

	/**
	 * Delivery status AJAX endpoint.
	 */
	public function delivery_status() {

		if ( ! $this->verify_nonce() ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed.', 'dispensary-wp' ),
				),
				403
			);
		}

		$delivery_number = isset( $_POST['delivery_number'] )
			? sanitize_text_field( wp_unslash( $_POST['delivery_number'] ) )
			: '';

		if ( '' === $delivery_number ) {
			wp_send_json_error(
				array(
					'message' => __( 'Please enter a delivery number.', 'dispensary-wp' ),
				),
				400
			);
		}

		$delivery = null;

		if ( class_exists( '\Dispensary_WP\Modules\Delivery\Delivery' ) ) {

			if ( method_exists( '\Dispensary_WP\Modules\Delivery\Delivery', 'get_by_number' ) ) {
				$delivery = \Dispensary_WP\Modules\Delivery\Delivery::get_by_number(
					$delivery_number
				);
			}
		}

		if ( ! $delivery ) {
			wp_send_json_error(
				array(
					'message' => __( 'Delivery not found.', 'dispensary-wp' ),
				),
				404
			);
		}

		$status = isset( $delivery->status )
			? $delivery->status
			: 'unknown';

		wp_send_json_success(
			array(
				'delivery_number' => $delivery_number,
				'status'          => sanitize_text_field( $status ),
			)
		);
	}
}
