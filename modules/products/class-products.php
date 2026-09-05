<?php
namespace Dispensary_WP\Modules\Products;

use Dispensary_WP\Security\Audit_Log;
use Dispensary_WP\Permissions\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Products {

	public function __construct() {
		add_action(
			'init',
			array( $this, 'register' )
		);
	}

	public function register() {
		// Product module initialization hook.
		do_action( 'dispensary_wp_products_loaded' );
	}

	public static function create( $data ) {

		Permissions::require_capability(
			'dispensary_manage_products'
		);

		$id = Product::create( $data );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		Audit_Log::log(
			'product_created',
			'product',
			$id,
			array(
				'name' => sanitize_text_field( $data['name'] ?? '' ),
			)
		);

		return $id;
	}

	public static function update( $id, $data ) {

		Permissions::require_capability(
			'dispensary_manage_products'
		);

		$result = Product::update(
			$id,
			$data
		);

		if ( ! is_wp_error( $result ) ) {
			Audit_Log::log(
				'product_updated',
				'product',
				$id
			);
		}

		return $result;
	}

	public static function delete( $id ) {

		Permissions::require_capability(
			'dispensary_manage_products'
		);

		$result = Product::delete( $id );

		if ( $result ) {
			Audit_Log::log(
				'product_deleted',
				'product',
				$id
			);
		}

		return $result;
	}
}
