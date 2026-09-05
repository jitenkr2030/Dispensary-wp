<?php
/**
 * Customers REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\Customers\Customers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customers_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/customers',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'index' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_view_customers' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_manage_customers' ),
				),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/customers/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'show' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_customers' ),
			)
		);
	}

	/**
	 * List customers.
	 */
	public function index( $request ) {

		$args = array(
			'status' => sanitize_key( $request->get_param( 'status' ) ),
			'search' => sanitize_text_field( $request->get_param( 'search' ) ),
			'limit'  => absint( $request->get_param( 'limit' ) ),
			'offset' => absint( $request->get_param( 'offset' ) ),
		);

		$result = Customers::list( $args );

		return REST_API::response( $result );
	}

	/**
	 * Create customer.
	 */
	public function create( $request ) {

		$data = $request->get_json_params();

		if ( ! is_array( $data ) ) {
			$data = $request->get_params();
		}

		$result = Customers::create( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Show customer.
	 */
	public function show( $request ) {

		$result = Customers::get(
			absint( $request['id'] )
		);

		if ( ! $result ) {
			return new \WP_Error(
				'customer_not_found',
				__( 'Customer not found.', 'dispensary-wp' ),
				array( 'status' => 404 )
			);
		}

		return REST_API::response( $result );
	}
}
