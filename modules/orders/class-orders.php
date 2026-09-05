<?php
/**
 * Orders service.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Orders;

use Dispensary_WP\Database\Database;
use Dispensary_WP\Permissions\Permissions;
use Dispensary_WP\Security\Audit_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Orders {

	/**
	 * Order model.
	 *
	 * @var Order
	 */
	protected $order;

	/**
	 * Item model.
	 *
	 * @var Order_Item
	 */
	protected $item;

	/**
	 * Payment model.
	 *
	 * @var Order_Payment
	 */
	protected $payment;

	/**
	 * Refund model.
	 *
	 * @var Order_Refund
	 */
	protected $refund;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->order   = new Order();
		$this->item    = new Order_Item();
		$this->payment = new Order_Payment();
		$this->refund  = new Order_Refund();
	}

	/**
	 * Create order.
	 *
	 * @param array $data  Order data.
	 * @param array $items Order items.
	 * @return int|WP_Error
	 */
	public function create( $data, $items ) {

		if ( ! Permissions::can_manage_orders() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to create orders.', 'dispensary-wp' )
			);
		}

		if ( empty( $items ) || ! is_array( $items ) ) {
			return new \WP_Error(
				'invalid_items',
				__( 'At least one order item is required.', 'dispensary-wp' )
			);
		}

		$subtotal = 0;

		$prepared_items = array();

		foreach ( $items as $item ) {

			$product_id = isset( $item['product_id'] )
				? absint( $item['product_id'] )
				: 0;

			$quantity = isset( $item['quantity'] )
				? absint( $item['quantity'] )
				: 0;

			if ( $product_id <= 0 || $quantity <= 0 ) {
				return new \WP_Error(
					'invalid_item',
					__( 'Each item requires a valid product and positive quantity.', 'dispensary-wp' )
				);
			}

			$product = $this->get_product( $product_id );

			if ( ! $product ) {
				return new \WP_Error(
					'product_not_found',
					__( 'One of the selected products could not be found.', 'dispensary-wp' )
				);
			}

			$unit_price = isset( $item['unit_price'] )
				? (float) $item['unit_price']
				: (float) $product->price;

			if ( $unit_price < 0 ) {
				return new \WP_Error(
					'invalid_price',
					__( 'Product price cannot be negative.', 'dispensary-wp' )
				);
			}

			$discount = isset( $item['discount'] )
				? max( 0, (float) $item['discount'] )
				: 0;

			$tax = isset( $item['tax'] )
				? max( 0, (float) $item['tax'] )
				: 0;

			$line_subtotal = $unit_price * $quantity;

			$line_total = max(
				0,
				$line_subtotal - $discount + $tax
			);

			$subtotal += $line_subtotal;

			$prepared_items[] = array(
				'product_id'   => $product_id,
				'variant_id'   => isset( $item['variant_id'] )
					? absint( $item['variant_id'] )
					: 0,
				'product_name' => sanitize_text_field( $product->name ),
				'sku'          => isset( $product->sku )
					? sanitize_text_field( $product->sku )
					: '',
				'quantity'     => $quantity,
				'unit_price'   => $unit_price,
				'discount'     => $discount,
				'tax'          => $tax,
				'total'        => $line_total,
			);
		}

		$discount_total = isset( $data['discount_total'] )
			? max( 0, (float) $data['discount_total'] )
			: 0;

		$tax_total = isset( $data['tax_total'] )
			? max( 0, (float) $data['tax_total'] )
			: 0;

		$shipping_total = isset( $data['shipping_total'] )
			? max( 0, (float) $data['shipping_total'] )
			: 0;

		$total = max(
			0,
			$subtotal
			- $discount_total
			+ $tax_total
			+ $shipping_total
		);

		$now = current_time( 'mysql', true );

		$order_data = array(
			'customer_id'     => isset( $data['customer_id'] )
				? absint( $data['customer_id'] )
				: 0,
			'order_number'    => $this->generate_order_number(),
			'status'          => 'pending',
			'payment_status'  => 'pending',
			'fulfillment_status' => 'unfulfilled',
			'currency'        => isset( $data['currency'] )
				? sanitize_text_field( $data['currency'] )
				: 'USD',
			'subtotal'        => $subtotal,
			'discount_total'  => $discount_total,
			'tax_total'       => $tax_total,
			'shipping_total'  => $shipping_total,
			'total'           => $total,
			'customer_note'   => isset( $data['customer_note'] )
				? sanitize_textarea_field( $data['customer_note'] )
				: '',
			'admin_note'      => isset( $data['admin_note'] )
				? sanitize_textarea_field( $data['admin_note'] )
				: '',
			'created_by'      => get_current_user_id(),
			'created_at'      => $now,
			'updated_at'      => $now,
		);

		$order_id = $this->order->create( $order_data );

		if ( ! $order_id ) {
			return new \WP_Error(
				'order_create_failed',
				__( 'Unable to create order.', 'dispensary-wp' )
			);
		}

		foreach ( $prepared_items as $prepared_item ) {

			$item_data = array(
				'order_id'     => $order_id,
				'product_id'   => $prepared_item['product_id'],
				'variant_id'   => $prepared_item['variant_id'],
				'product_name' => $prepared_item['product_name'],
				'sku'          => $prepared_item['sku'],
				'quantity'     => $prepared_item['quantity'],
				'unit_price'   => $prepared_item['unit_price'],
				'discount'     => $prepared_item['discount'],
				'tax'          => $prepared_item['tax'],
				'total'        => $prepared_item['total'],
				'created_at'   => $now,
			);

			if ( ! $this->item->create( $item_data ) ) {

				$this->order->delete( $order_id );

				return new \WP_Error(
					'order_item_failed',
					__( 'Unable to create order item.', 'dispensary-wp' )
				);
			}
		}

		$this->add_status_history(
			$order_id,
			'',
			'pending',
			'Order created.'
		);

		Audit_Log::log(
			'order_created',
			'order',
			$order_id,
			array(
				'order_number' => $order_data['order_number'],
				'total'        => $total,
			)
		);

		return $order_id;
	}

	/**
	 * Get order.
	 *
	 * @param int $order_id Order ID.
	 * @return array|WP_Error
	 */
	public function get( $order_id ) {

		if ( ! Permissions::can_view_orders() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view orders.', 'dispensary-wp' )
			);
		}

		$order = $this->order->find( $order_id );

		if ( ! $order ) {
			return new \WP_Error(
				'order_not_found',
				__( 'Order not found.', 'dispensary-wp' )
			);
		}

		return array(
			'order'    => $order,
			'items'    => $this->item->get_by_order( $order_id ),
			'payments' => $this->payment->get_by_order( $order_id ),
			'refunds'  => $this->refund->get_by_order( $order_id ),
			'history'  => $this->get_status_history( $order_id ),
		);
	}

	/**
	 * List orders.
	 *
	 * @param array $args Arguments.
	 * @return array|WP_Error
	 */
	public function list_orders( $args = array() ) {

		if ( ! Permissions::can_view_orders() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view orders.', 'dispensary-wp' )
			);
		}

		return $this->order->all( $args );
	}

	/**
	 * Update order status.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $status   New status.
	 * @param string $note     Note.
	 * @return bool|WP_Error
	 */
	public function update_status( $order_id, $status, $note = '' ) {

		if ( ! Permissions::can_manage_orders() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to update orders.', 'dispensary-wp' )
			);
		}

		$status = sanitize_key( $status );

		if ( ! Order_Status::is_valid( $status ) ) {
			return new \WP_Error(
				'invalid_status',
				__( 'Invalid order status.', 'dispensary-wp' )
			);
		}

		$order = $this->order->find( $order_id );

		if ( ! $order ) {
			return new \WP_Error(
				'order_not_found',
				__( 'Order not found.', 'dispensary-wp' )
			);
		}

		if ( ! Order_Status::can_transition( $order->status, $status ) ) {
			return new \WP_Error(
				'invalid_transition',
				__( 'This order status transition is not allowed.', 'dispensary-wp' )
			);
		}

		$updated = $this->order->update(
			$order_id,
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql', true ),
			)
		);

		if ( ! $updated ) {
			return false;
		}

		$this->add_status_history(
			$order_id,
			$order->status,
			$status,
			$note
		);

		Audit_Log::log(
			'order_status_updated',
			'order',
			$order_id,
			array(
				'old_status' => $order->status,
				'new_status' => $status,
				'note'       => $note,
			)
		);

		return true;
	}

	/**
	 * Cancel order.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $reason  Cancellation reason.
	 * @return bool|WP_Error
	 */
	public function cancel( $order_id, $reason = '' ) {

		return $this->update_status(
			$order_id,
			'cancelled',
			$reason
		);
	}

	/**
	 * Mark order as paid.
	 *
	 * @param int   $order_id     Order ID.
	 * @param array $payment_data Payment data.
	 * @return int|WP_Error
	 */
	public function mark_paid( $order_id, $payment_data = array() ) {

		if ( ! Permissions::can_manage_orders() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to process payments.', 'dispensary-wp' )
			);
		}

		$order = $this->order->find( $order_id );

		if ( ! $order ) {
			return new \WP_Error(
				'order_not_found',
				__( 'Order not found.', 'dispensary-wp' )
			);
		}

		$amount = isset( $payment_data['amount'] )
			? (float) $payment_data['amount']
			: (float) $order->total;

		if ( $amount <= 0 ) {
			return new \WP_Error(
				'invalid_payment_amount',
				__( 'Payment amount must be greater than zero.', 'dispensary-wp' )
			);
		}

		$now = current_time( 'mysql', true );

		$payment_id = $this->payment->create(
			array(
				'order_id'       => $order_id,
				'transaction_id' => isset( $payment_data['transaction_id'] )
					? sanitize_text_field( $payment_data['transaction_id'] )
					: '',
				'method'         => isset( $payment_data['method'] )
					? sanitize_key( $payment_data['method'] )
					: 'cash',
				'status'         => 'paid',
				'amount'         => $amount,
				'currency'       => $order->currency,
				'paid_at'        => $now,
				'metadata'       => isset( $payment_data['metadata'] )
					? wp_json_encode( $payment_data['metadata'] )
					: '',
				'created_at'     => $now,
				'updated_at'     => $now,
			)
		);

		if ( ! $payment_id ) {
			return new \WP_Error(
				'payment_failed',
				__( 'Unable to create payment record.', 'dispensary-wp' )
			);
		}

		$payment_status = $amount >= (float) $order->total
			? 'paid'
			: 'pending';

		$this->order->update(
			$order_id,
			array(
				'payment_status' => $payment_status,
				'updated_at'     => $now,
			)
		);

		Audit_Log::log(
			'order_payment_created',
			'order',
			$order_id,
			array(
				'payment_id' => $payment_id,
				'amount'     => $amount,
				'method'     => $payment_data['method'] ?? 'cash',
			)
		);

		return $payment_id;
	}

	/**
	 * Create refund.
	 *
	 * @param int   $order_id Order ID.
	 * @param float $amount   Refund amount.
	 * @param array $data     Refund data.
	 * @return int|WP_Error
	 */
	public function refund( $order_id, $amount, $data = array() ) {

		if ( ! Permissions::can_manage_orders() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to refund orders.', 'dispensary-wp' )
			);
		}

		$order = $this->order->find( $order_id );

		if ( ! $order ) {
			return new \WP_Error(
				'order_not_found',
				__( 'Order not found.', 'dispensary-wp' )
			);
		}

		$amount = (float) $amount;

		if ( $amount <= 0 || $amount > (float) $order->total ) {
			return new \WP_Error(
				'invalid_refund_amount',
				__( 'Invalid refund amount.', 'dispensary-wp' )
			);
		}

		$now = current_time( 'mysql', true );

		$refund_id = $this->refund->create(
			array(
				'order_id'        => $order_id,
				'payment_id'      => isset( $data['payment_id'] )
					? absint( $data['payment_id'] )
					: 0,
				'amount'          => $amount,
				'reason'          => isset( $data['reason'] )
					? sanitize_textarea_field( $data['reason'] )
					: '',
				'status'          => 'completed',
				'refund_reference' => isset( $data['refund_reference'] )
					? sanitize_text_field( $data['refund_reference'] )
					: '',
				'created_by'      => get_current_user_id(),
				'created_at'      => $now,
				'updated_at'      => $now,
			)
		);

		if ( ! $refund_id ) {
			return new \WP_Error(
				'refund_failed',
				__( 'Unable to create refund.', 'dispensary-wp' )
			);
		}

		$refunds = $this->refund->get_by_order( $order_id );

		$total_refunded = 0;

		foreach ( $refunds as $refund ) {
			if ( 'completed' === $refund->status ) {
				$total_refunded += (float) $refund->amount;
			}
		}

		$new_payment_status = $total_refunded >= (float) $order->total
			? 'refunded'
			: 'partially_refunded';

		$this->order->update(
			$order_id,
			array(
				'payment_status' => $new_payment_status,
				'status'         => $total_refunded >= (float) $order->total
					? 'refunded'
					: $order->status,
				'updated_at'     => $now,
			)
		);

		Audit_Log::log(
			'order_refunded',
			'order',
			$order_id,
			array(
				'refund_id' => $refund_id,
				'amount'    => $amount,
			)
		);

		return $refund_id;
	}

	/**
	 * Generate order number.
	 *
	 * @return string
	 */
	protected function generate_order_number() {

		do {
			$order_number = 'ORD-' . gmdate( 'Ymd-His' ) . '-' . wp_rand( 1000, 9999 );
		} while ( $this->order->find_by_number( $order_number ) );

		return $order_number;
	}

	/**
	 * Get product.
	 *
	 * @param int $product_id Product ID.
	 * @return object|null
	 */
	protected function get_product( $product_id ) {

		global $wpdb;

		$table = Database::table( 'products' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				absint( $product_id )
			)
		);
	}

	/**
	 * Add status history.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $old      Old status.
	 * @param string $new      New status.
	 * @param string $note     Note.
	 * @return int|false
	 */
	protected function add_status_history( $order_id, $old, $new, $note = '' ) {

		global $wpdb;

		$table = Database::table( 'order_status_history' );

		$result = $wpdb->insert(
			$table,
			array(
				'order_id'   => absint( $order_id ),
				'old_status' => sanitize_key( $old ),
				'new_status' => sanitize_key( $new ),
				'note'       => sanitize_textarea_field( $note ),
				'changed_by' => get_current_user_id(),
				'created_at' => current_time( 'mysql', true ),
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
			)
		);

		if ( false === $result ) {
			return false;
		}

		return absint( $wpdb->insert_id );
	}

	/**
	 * Get status history.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	protected function get_status_history( $order_id ) {

		global $wpdb;

		$table = Database::table( 'order_status_history' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY id ASC",
				absint( $order_id )
			)
		);
	}
}
