<?php
/**
 * POS cart management.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\POS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS_Cart {

	/**
	 * Cart items.
	 *
	 * @var array
	 */
	protected $items = array();

	/**
	 * Add item.
	 *
	 * @param array $item Cart item.
	 * @return bool
	 */
	public function add_item( $item ) {

		$product_id = isset( $item['product_id'] )
			? absint( $item['product_id'] )
			: 0;

		$quantity = isset( $item['quantity'] )
			? (float) $item['quantity']
			: 0;

		if ( $product_id <= 0 || $quantity <= 0 ) {
			return false;
		}

		$key = $product_id . ':' . absint( $item['variant_id'] ?? 0 );

		if ( isset( $this->items[ $key ] ) ) {
			$this->items[ $key ]['quantity'] += $quantity;
			$this->items[ $key ]['total'] =
				$this->items[ $key ]['quantity'] *
				$this->items[ $key ]['unit_price'];
		} else {
			$unit_price = max(
				0,
				(float) ( $item['unit_price'] ?? 0 )
			);

			$this->items[ $key ] = array(
				'product_id' => $product_id,
				'variant_id' => absint( $item['variant_id'] ?? 0 ),
				'name'       => sanitize_text_field( $item['name'] ?? '' ),
				'sku'        => sanitize_text_field( $item['sku'] ?? '' ),
				'quantity'   => $quantity,
				'unit_price' => $unit_price,
				'discount'   => max( 0, (float) ( $item['discount'] ?? 0 ) ),
				'tax'        => max( 0, (float) ( $item['tax'] ?? 0 ) ),
				'total'      => $quantity * $unit_price,
			);
		}

		return true;
	}

	/**
	 * Remove item.
	 *
	 * @param int $product_id Product ID.
	 * @param int $variant_id Variant ID.
	 * @return bool
	 */
	public function remove_item( $product_id, $variant_id = 0 ) {

		$key = absint( $product_id ) . ':' . absint( $variant_id );

		if ( ! isset( $this->items[ $key ] ) ) {
			return false;
		}

		unset( $this->items[ $key ] );

		return true;
	}

	/**
	 * Update quantity.
	 *
	 * @param int   $product_id Product ID.
	 * @param float $quantity   Quantity.
	 * @param int   $variant_id Variant ID.
	 * @return bool
	 */
	public function update_quantity( $product_id, $quantity, $variant_id = 0 ) {

		$key = absint( $product_id ) . ':' . absint( $variant_id );

		if ( ! isset( $this->items[ $key ] ) ) {
			return false;
		}

		$quantity = (float) $quantity;

		if ( $quantity <= 0 ) {
			return $this->remove_item(
				$product_id,
				$variant_id
			);
		}

		$this->items[ $key ]['quantity'] = $quantity;

		$this->items[ $key ]['total'] =
			$quantity * $this->items[ $key ]['unit_price'];

		return true;
	}

	/**
	 * Get items.
	 *
	 * @return array
	 */
	public function get_items() {
		return array_values( $this->items );
	}

	/**
	 * Calculate subtotal.
	 *
	 * @return float
	 */
	public function subtotal() {

		$total = 0;

		foreach ( $this->items as $item ) {
			$total +=
				( $item['unit_price'] * $item['quantity'] )
				- $item['discount'];
		}

		return max( 0, $total );
	}

	/**
	 * Calculate tax.
	 *
	 * @return float
	 */
	public function tax() {

		$total = 0;

		foreach ( $this->items as $item ) {
			$total += (float) $item['tax'];
		}

		return max( 0, $total );
	}

	/**
	 * Calculate total.
	 *
	 * @param float $shipping Shipping.
	 * @return float
	 */
	public function total( $shipping = 0 ) {

		return max(
			0,
			$this->subtotal()
			+ $this->tax()
			+ max( 0, (float) $shipping )
		);
	}

	/**
	 * Clear cart.
	 *
	 * @return void
	 */
	public function clear() {
		$this->items = array();
	}
}
