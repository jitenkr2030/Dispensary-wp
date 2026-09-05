<?php
namespace Dispensary_WP\Modules\Inventory;

use Dispensary_WP\Permissions\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Inventory {

	public function __construct() {

		add_action(
			'init',
			array( $this, 'register' )
		);
	}

	public function register() {

		do_action(
			'dispensary_wp_inventory_loaded'
		);
	}

	public static function add_stock(
		$product_id,
		$quantity,
		$args = array()
	) {

		Permissions::require_capability(
			'dispensary_manage_inventory'
		);

		$args['product_id'] = absint( $product_id );
		$args['quantity']   = abs( (float) $quantity );
		$args['type']       = 'in';

		return Stock_Movement::create(
			$args
		);
	}

	public static function remove_stock(
		$product_id,
		$quantity,
		$args = array()
	) {

		Permissions::require_capability(
			'dispensary_manage_inventory'
		);

		$current = Stock::available(
			$product_id,
			$args['variant_id'] ?? 0
		);

		if ( $current < (float) $quantity ) {
			return new \WP_Error(
				'insufficient_stock',
				'Insufficient stock available.'
			);
		}

		$args['product_id'] = absint( $product_id );
		$args['quantity']   = abs( (float) $quantity );
		$args['type']       = $args['type'] ?? 'out';

		return Stock_Movement::create(
			$args
		);
	}

	public static function get_stock(
		$product_id,
		$variant_id = 0
	) {

		Permissions::require_capability(
			'dispensary_view_inventory'
		);

		return Stock::available(
			$product_id,
			$variant_id
		);
	}
}
