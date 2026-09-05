<?php
/**
 * Products REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\Products\Products;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Products_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/products',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'index' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_view_products' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_manage_products' ),
				),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/products/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'show' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_view_products' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_manage_products' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_manage_products' ),
				),
			)
		);
	}

	/**
	 * List products.
	 */
	public function index( $request ) {

		$args = array(
			'status' => sanitize_key( $request->get_param( 'status' ) ),
			'search' => sanitize_text_field( $request->get_param( 'search' ) ),
			'limit'  => absint( $request->get_param( 'limit' ) ),
			'offset' => absint( $request->get_param( 'offset' ) ),
		);

		$args = array_filter(
			$args,
			function ( $value ) {
				return '' !== $value && null !== $value;
			}
		);

		$result = Products::list( $args );

		return REST_API::response( $result );
	}

	/**
	 * Create product.
	 */
	public function create( $request ) {

		$data = $request->get_json_params();

		if ( ! is_array( $data ) ) {
			$data = $request->get_params();
		}

		$result = Products::create( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Show product.
	 */
	public function show( $request ) {

		$result = Products::get( absint( $request['id'] ) );

		if ( ! $result ) {
			return new \WP_Error(
				'product_not_found',
				__( 'Product not found.', 'dispensary-wp' ),
				array( 'status' => 404 )
			);
		}

		return REST_API::response( $result );
	}

	/**
	 * Update product.
	 */
	public function update( $request ) {

		$data = $request->get_json_params();

		if ( ! is_array( $data ) ) {
			$data = $request->get_params();
		}

		unset( $data['id'] );

		$result = Products::update(
			absint( $request['id'] ),
			$data
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result );
	}

	/**
	 * Delete product.
	 */
	public function delete( $request ) {

		$result = Products::delete( absint( $request['id'] ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response(
			array(
				'deleted' => true,
			)
		);
	}
}
