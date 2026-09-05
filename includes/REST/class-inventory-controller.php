<?php
/**
 * Inventory REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\Inventory\Inventory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Inventory_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/inventory/stock/(?P<product_id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'stock' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_inventory' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/inventory/add',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_stock' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_inventory' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/inventory/remove',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'remove_stock' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_inventory' ),
			)
		);
	}

	/**
	 * Get stock.
	 */
	public function stock( $request ) {

		return REST_API::response(
			Inventory::get_stock(
				absint( $request['product_id'] )
			)
		);
	}

	/**
	 * Add stock.
	 */
	public function add_stock( $request ) {

		$data = $request->get_json_params();

		$result = Inventory::add_stock(
			absint( $data['product_id'] ?? 0 ),
			(float) ( $data['quantity'] ?? 0 ),
			$data
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Remove stock.
	 */
	public function remove_stock( $request ) {

		$data = $request->get_json_params();

		$result = Inventory::remove_stock(
			absint( $data['product_id'] ?? 0 ),
			(float) ( $data['quantity'] ?? 0 ),
			$data
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}
}
