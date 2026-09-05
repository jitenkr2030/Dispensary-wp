<?php
namespace Dispensary_WP\Modules\Inventory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Inventory_Controller {

	public static function stock(
		$product_id,
		$variant_id = 0
	) {
		return Inventory::get_stock(
			$product_id,
			$variant_id
		);
	}

	public static function movements(
		$product_id,
		$limit = 100
	) {
		return Stock_Movement::all(
			$product_id,
			$limit
		);
	}

	public static function batches(
		$product_id = 0
	) {
		return Batch::all(
			$product_id
		);
	}

	public static function lots(
		$product_id = 0
	) {
		return Lot::all(
			$product_id
		);
	}

	public static function suppliers() {
		return Supplier::all();
	}

	public static function purchases(
		$limit = 100
	) {
		return Purchase::all(
			$limit
		);
	}

	public static function low_stock(
		$threshold = 10
	) {
		return Low_Stock::get_products(
			$threshold
		);
	}
}
