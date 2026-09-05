<?php
/**
 * POS controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\POS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS_Controller {

	/**
	 * POS service.
	 *
	 * @var POS
	 */
	protected $pos;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->pos = new POS();
	}

	/**
	 * Get POS service.
	 *
	 * @return POS
	 */
	public function service() {
		return $this->pos;
	}

	/**
	 * Add to cart.
	 *
	 * @param int   $product_id Product ID.
	 * @param float $quantity Quantity.
	 * @return bool|WP_Error
	 */
	public function add_to_cart( $product_id, $quantity = 1 ) {
		return $this->pos->add_to_cart(
			$product_id,
			$quantity
		);
	}

	/**
	 * Get cart.
	 *
	 * @return array
	 */
	public function cart() {
		return $this->pos->get_cart();
	}

	/**
	 * Complete sale.
	 *
	 * @param array $data Sale data.
	 * @return int|WP_Error
	 */
	public function complete_sale( $data = array() ) {
		return $this->pos->complete_sale( $data );
	}

	/**
	 * Open session.
	 *
	 * @param int   $register_id Register ID.
	 * @param float $opening_cash Opening cash.
	 * @return int|WP_Error
	 */
	public function open_session( $register_id, $opening_cash = 0 ) {
		return $this->pos->open_session(
			$register_id,
			$opening_cash
		);
	}

	/**
	 * Close session.
	 *
	 * @param int   $session_id Session ID.
	 * @param float $closing_cash Closing cash.
	 * @return bool|WP_Error
	 */
	public function close_session( $session_id, $closing_cash = 0 ) {
		return $this->pos->close_session(
			$session_id,
			$closing_cash
		);
	}

	/**
	 * Active session.
	 *
	 * @return object|null
	 */
	public function active_session() {
		return $this->pos->active_session();
	}

	/**
	 * Registers.
	 *
	 * @return array
	 */
	public function registers() {
		return $this->pos->registers();
	}

	/**
	 * Get sale.
	 *
	 * @param int $sale_id Sale ID.
	 * @return array|WP_Error
	 */
	public function sale( $sale_id ) {
		return $this->pos->get_sale( $sale_id );
	}

	/**
	 * Sales.
	 *
	 * @param int $limit Limit.
	 * @return array|WP_Error
	 */
	public function sales( $limit = 50 ) {
		return $this->pos->sales( $limit );
	}
}
