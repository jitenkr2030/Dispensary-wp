<?php
/**
 * POS receipt service.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\POS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS_Receipt {

	/**
	 * Generate receipt data.
	 *
	 * @param object $sale     Sale.
	 * @param array  $items    Items.
	 * @param array  $payments Payments.
	 * @return array
	 */
	public function generate( $sale, $items = array(), $payments = array() ) {

		return array(
			'sale'     => $sale,
			'items'    => $items,
			'payments' => $payments,
			'generated_at' => current_time( 'mysql', true ),
		);
	}

	/**
	 * Generate receipt number.
	 *
	 * @param int $sale_id Sale ID.
	 * @return string
	 */
	public function number( $sale_id ) {

		return 'POS-' . gmdate( 'Ymd' ) . '-' . absint( $sale_id );
	}
}
