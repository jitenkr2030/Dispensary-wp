<?php
/**
 * Order status management.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Orders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order_Status {

	/**
	 * Valid order statuses.
	 *
	 * @return array
	 */
	public static function all() {
		return array(
			'pending',
			'confirmed',
			'processing',
			'ready',
			'completed',
			'cancelled',
			'refunded',
			'failed',
		);
	}

	/**
	 * Valid payment statuses.
	 *
	 * @return array
	 */
	public static function payment_statuses() {
		return array(
			'pending',
			'authorized',
			'paid',
			'failed',
			'partially_refunded',
			'refunded',
		);
	}

	/**
	 * Valid fulfillment statuses.
	 *
	 * @return array
	 */
	public static function fulfillment_statuses() {
		return array(
			'unfulfilled',
			'processing',
			'ready',
			'out_for_delivery',
			'completed',
			'cancelled',
		);
	}

	/**
	 * Check order status.
	 *
	 * @param string $status Status.
	 * @return bool
	 */
	public static function is_valid( $status ) {
		return in_array( $status, self::all(), true );
	}

	/**
	 * Check payment status.
	 *
	 * @param string $status Status.
	 * @return bool
	 */
	public static function is_valid_payment_status( $status ) {
		return in_array( $status, self::payment_statuses(), true );
	}

	/**
	 * Check fulfillment status.
	 *
	 * @param string $status Status.
	 * @return bool
	 */
	public static function is_valid_fulfillment_status( $status ) {
		return in_array( $status, self::fulfillment_statuses(), true );
	}

	/**
	 * Check whether a status transition is allowed.
	 *
	 * @param string $from Current status.
	 * @param string $to New status.
	 * @return bool
	 */
	public static function can_transition( $from, $to ) {

		if ( $from === $to ) {
			return true;
		}

		$transitions = array(
			'pending' => array(
				'confirmed',
				'processing',
				'cancelled',
				'failed',
			),
			'confirmed' => array(
				'processing',
				'cancelled',
			),
			'processing' => array(
				'ready',
				'completed',
				'cancelled',
			),
			'ready' => array(
				'completed',
				'cancelled',
			),
			'completed' => array(
				'refunded',
			),
			'cancelled' => array(),
			'refunded' => array(),
			'failed' => array(
				'pending',
				'cancelled',
			),
		);

		return isset( $transitions[ $from ] )
			&& in_array( $to, $transitions[ $from ], true );
	}
}
