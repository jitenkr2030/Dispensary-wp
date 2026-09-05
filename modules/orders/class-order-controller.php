<?php
/**
 * Orders controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Orders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Order_Controller {

	/**
	 * Orders service.
	 *
	 * @var Orders
	 */
	protected $orders;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->orders = new Orders();
	}

	/**
	 * Get service.
	 *
	 * @return Orders
	 */
	public function service() {
		return $this->orders;
	}

	/**
	 * Get order.
	 *
	 * @param int $order_id Order ID.
	 * @return array|WP_Error
	 */
	public function get( $order_id ) {
		return $this->orders->get( $order_id );
	}

	/**
	 * List orders.
	 *
	 * @param array $args Arguments.
	 * @return array|WP_Error
	 */
	public function list_orders( $args = array() ) {
		return $this->orders->list_orders( $args );
	}

	/**
	 * Create order.
	 *
	 * @param array $data  Order data.
	 * @param array $items Items.
	 * @return int|WP_Error
	 */
	public function create( $data, $items ) {
		return $this->orders->create( $data, $items );
	}

	/**
	 * Update status.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $status   Status.
	 * @param string $note     Note.
	 * @return bool|WP_Error
	 */
	public function update_status( $order_id, $status, $note = '' ) {
		return $this->orders->update_status(
			$order_id,
			$status,
			$note
		);
	}

	/**
	 * Cancel order.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $reason  Reason.
	 * @return bool|WP_Error
	 */
	public function cancel( $order_id, $reason = '' ) {
		return $this->orders->cancel(
			$order_id,
			$reason
		);
	}

	/**
	 * Mark paid.
	 *
	 * @param int   $order_id Order ID.
	 * @param array $data     Payment data.
	 * @return int|WP_Error
	 */
	public function payment( $order_id, $data = array() ) {
		return $this->orders->mark_paid(
			$order_id,
			$data
		);
	}

	/**
	 * Refund.
	 *
	 * @param int   $order_id Order ID.
	 * @param float $amount   Amount.
	 * @param array $data     Refund data.
	 * @return int|WP_Error
	 */
	public function refund( $order_id, $amount, $data = array() ) {
		return $this->orders->refund(
			$order_id,
			$amount,
			$data
		);
	}
}
