<?php
/**
 * POS service.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\POS;

use Dispensary_WP\Database\Database;
use Dispensary_WP\Permissions\Permissions;
use Dispensary_WP\Security\Audit_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS {

	/**
	 * Cart.
	 *
	 * @var POS_Cart
	 */
	protected $cart;

	/**
	 * Sale.
	 *
	 * @var POS_Sale
	 */
	protected $sale;

	/**
	 * Payment.
	 *
	 * @var POS_Payment
	 */
	protected $payment;

	/**
	 * Session.
	 *
	 * @var POS_Session
	 */
	protected $session;

	/**
	 * Register.
	 *
	 * @var POS_Register
	 */
	protected $register;

	/**
	 * Receipt.
	 *
	 * @var POS_Receipt
	 */
	protected $receipt;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->cart     = new POS_Cart();
		$this->sale     = new POS_Sale();
		$this->payment  = new POS_Payment();
		$this->session  = new POS_Session();
		$this->register = new POS_Register();
		$this->receipt  = new POS_Receipt();
	}

	/**
	 * Add product to cart.
	 *
	 * @param int   $product_id Product ID.
	 * @param float $quantity Quantity.
	 * @return bool|WP_Error
	 */
	public function add_to_cart( $product_id, $quantity = 1 ) {

		if ( ! Permissions::can_use_pos() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to use POS.', 'dispensary-wp' )
			);
		}

		$product = $this->get_product( $product_id );

		if ( ! $product ) {
			return new \WP_Error(
				'product_not_found',
				__( 'Product not found.', 'dispensary-wp' )
			);
		}

		if ( 'active' !== $product->status ) {
			return new \WP_Error(
				'product_inactive',
				__( 'This product is not active.', 'dispensary-wp' )
			);
		}

		return $this->cart->add_item(
			array(
				'product_id' => $product->id,
				'name'       => $product->name,
				'sku'        => $product->sku ?? '',
				'quantity'   => $quantity,
				'unit_price' => $product->price,
			)
		);
	}

	/**
	 * Remove cart item.
	 *
	 * @param int $product_id Product ID.
	 * @return bool
	 */
	public function remove_from_cart( $product_id ) {

		return $this->cart->remove_item( $product_id );
	}

	/**
	 * Get cart.
	 *
	 * @return array
	 */
	public function get_cart() {

		return array(
			'items'    => $this->cart->get_items(),
			'subtotal' => $this->cart->subtotal(),
			'tax'      => $this->cart->tax(),
			'total'    => $this->cart->total(),
		);
	}

	/**
	 * Complete POS sale.
	 *
	 * @param array $data Sale data.
	 * @return int|WP_Error
	 */
	public function complete_sale( $data = array() ) {

		if ( ! Permissions::can_use_pos() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to use POS.', 'dispensary-wp' )
			);
		}

		$items = $this->cart->get_items();

		if ( empty( $items ) ) {
			return new \WP_Error(
				'empty_cart',
				__( 'The POS cart is empty.', 'dispensary-wp' )
			);
		}

		$total = $this->cart->total();

		$payment_method = isset( $data['payment_method'] )
			? sanitize_key( $data['payment_method'] )
			: 'cash';

		$paid_amount = isset( $data['paid_amount'] )
			? (float) $data['paid_amount']
			: $total;

		if ( $paid_amount < $total ) {
			return new \WP_Error(
				'insufficient_payment',
				__( 'Payment amount is less than sale total.', 'dispensary-wp' )
			);
		}

		$now = current_time( 'mysql', true );

		$sale_id = $this->sale->create(
			array(
				'register_id'   => isset( $data['register_id'] )
					? absint( $data['register_id'] )
					: 0,
				'session_id'    => isset( $data['session_id'] )
					? absint( $data['session_id'] )
					: 0,
				'customer_id'   => isset( $data['customer_id'] )
					? absint( $data['customer_id'] )
					: 0,
				'receipt_number' => '',
				'subtotal'      => $this->cart->subtotal(),
				'tax_total'     => $this->cart->tax(),
				'discount_total' => 0,
				'total'         => $total,
				'status'        => 'completed',
				'created_by'    => get_current_user_id(),
				'created_at'    => $now,
			)
		);

		if ( ! $sale_id ) {
			return new \WP_Error(
				'sale_failed',
				__( 'Unable to create POS sale.', 'dispensary-wp' )
			);
		}

		$this->update_receipt_number( $sale_id );

		$this->save_sale_items( $sale_id, $items );

		$payment_id = $this->payment->create(
			array(
				'sale_id'       => $sale_id,
				'method'        => $payment_method,
				'amount'        => $total,
				'status'        => 'paid',
				'transaction_id' => isset( $data['transaction_id'] )
					? sanitize_text_field( $data['transaction_id'] )
					: '',
				'created_at'    => $now,
			)
		);

		if ( ! $payment_id ) {
			return new \WP_Error(
				'payment_failed',
				__( 'Sale created but payment could not be recorded.', 'dispensary-wp' )
			);
		}

		Audit_Log::log(
			'pos_sale_completed',
			'pos_sale',
			$sale_id,
			array(
				'total'         => $total,
				'payment_method' => $payment_method,
			)
		);

		$this->cart->clear();

		return $sale_id;
	}

	/**
	 * Open POS session.
	 *
	 * @param int   $register_id Register ID.
	 * @param float $opening_cash Opening cash.
	 * @return int|WP_Error
	 */
	public function open_session( $register_id, $opening_cash = 0 ) {

		if ( ! Permissions::can_use_pos() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to use POS.', 'dispensary-wp' )
			);
		}

		$register = $this->register->find( $register_id );

		if ( ! $register ) {
			return new \WP_Error(
				'register_not_found',
				__( 'POS register not found.', 'dispensary-wp' )
			);
		}

		$session_id = $this->session->open(
			$register_id,
			$opening_cash
		);

		return $session_id
			? $session_id
			: new \WP_Error(
				'session_failed',
				__( 'Unable to open POS session.', 'dispensary-wp' )
			);
	}

	/**
	 * Close POS session.
	 *
	 * @param int   $session_id Session ID.
	 * @param float $closing_cash Closing cash.
	 * @return bool|WP_Error
	 */
	public function close_session( $session_id, $closing_cash = 0 ) {

		if ( ! Permissions::can_use_pos() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to use POS.', 'dispensary-wp' )
			);
		}

		return $this->session->close(
			$session_id,
			$closing_cash
		);
	}

	/**
	 * Get active session.
	 *
	 * @return object|null
	 */
	public function active_session() {
		return $this->session->get_active();
	}

	/**
	 * Get registers.
	 *
	 * @return array
	 */
	public function registers() {
		return $this->register->active();
	}

	/**
	 * Get sale.
	 *
	 * @param int $sale_id Sale ID.
	 * @return array|WP_Error
	 */
	public function get_sale( $sale_id ) {

		if ( ! Permissions::can_use_pos() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view POS sales.', 'dispensary-wp' )
			);
		}

		$sale = $this->sale->find( $sale_id );

		if ( ! $sale ) {
			return new \WP_Error(
				'sale_not_found',
				__( 'POS sale not found.', 'dispensary-wp' )
			);
		}

		return array(
			'sale'     => $sale,
			'payments' => $this->payment->get_by_sale( $sale_id ),
		);
	}

	/**
	 * List sales.
	 *
	 * @param int $limit Limit.
	 * @return array|WP_Error
	 */
	public function sales( $limit = 50 ) {

		if ( ! Permissions::can_use_pos() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view POS sales.', 'dispensary-wp' )
			);
		}

		return $this->sale->all( $limit );
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
				"SELECT * FROM {$table}
				WHERE id = %d
				LIMIT 1",
				absint( $product_id )
			)
		);
	}

	/**
	 * Save sale items.
	 *
	 * @param int   $sale_id Sale ID.
	 * @param array $items Items.
	 * @return void
	 */
	protected function save_sale_items( $sale_id, $items ) {

		global $wpdb;

		$table = Database::table( 'pos_sale_items' );

		foreach ( $items as $item ) {

			$wpdb->insert(
				$table,
				array(
					'sale_id'    => absint( $sale_id ),
					'product_id' => absint( $item['product_id'] ),
					'variant_id' => absint( $item['variant_id'] ),
					'name'       => sanitize_text_field( $item['name'] ),
					'sku'        => sanitize_text_field( $item['sku'] ),
					'quantity'   => (float) $item['quantity'],
					'unit_price' => (float) $item['unit_price'],
					'discount'   => (float) $item['discount'],
					'tax'        => (float) $item['tax'],
					'total'      => (float) $item['total'],
					'created_at' => current_time( 'mysql', true ),
				)
			);
		}
	}

	/**
	 * Update receipt number.
	 *
	 * @param int $sale_id Sale ID.
	 * @return bool
	 */
	protected function update_receipt_number( $sale_id ) {

		global $wpdb;

		$table = Database::table( 'pos_sales' );

		return false !== $wpdb->update(
			$table,
			array(
				'receipt_number' => $this->receipt->number( $sale_id ),
			),
			array(
				'id' => absint( $sale_id ),
			)
		);
	}
}
