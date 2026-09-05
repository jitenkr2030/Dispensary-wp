<?php
namespace Dispensary_WP\Modules\Products;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product_Controller {

	public static function get( $id ) {

		$product = Product::find( $id );

		if ( ! $product ) {
			return new \WP_Error(
				'product_not_found',
				'Product not found.',
				array( 'status' => 404 )
			);
		}

		$product['variants'] = Product_Variant::all(
			$id
		);

		$product['lab_tests'] = Product_Lab_Test::all(
			$id
		);

		return $product;
	}

	public static function list( $args = array() ) {
		return Product::all( $args );
	}

	public static function create( $data ) {
		return Products::create( $data );
	}

	public static function update( $id, $data ) {
		return Products::update(
			$id,
			$data
		);
	}

	public static function delete( $id ) {
		return Products::delete( $id );
	}
}
