<?php
/**
 * Orders REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\Orders\Orders;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Orders_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/orders',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'index' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_view_orders' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create' ),
					'permission_callback' => REST_API::permission_callback( 'dispensary_manage_orders' ),
				),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/orders/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'show' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_orders' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/orders/(?P<id>\d+)/status',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_orders' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/orders/(?P<id>\d+)/payment',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'payment' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_orders' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/orders/(?P<id>\d+)/refund',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'refund' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_orders' ),
			)
		);
	}

	/**
	 * List orders.
	 */
	public function index( $request ) {

		$args = array(
			'customer_id' => absint( $request->get_param( 'customer_id' ) ),
			'status'      => sanitize_key( $request->get_param( 'status' ) ),
			'limit'       => absint( $request->get_param( 'limit' ) ),
			'offset'      => absint( $request->get_param( 'offset' ) ),
		);

		$result = Orders::list( $args );

		return REST_API::response( $result );
	}

	/**
	 * Create order.
	 */
	public function create( $request ) {

		$data = $request->get_json_params();

		$result = Orders::create( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Show order.
	 */
	public function show( $request ) {

		$result = Orders::get(
			absint( $request['id'] )
		);

		if ( ! $result ) {
			return new \WP_Error(
				'order_not_found',
				__( 'Order not found.', 'dispensary-wp' ),
				array( 'status' => 404 )
			);
		}

		return REST_API::response( $result );
	}

	/**
	 * Update status.
	 */
	public function status( $request ) {

		$data = $request->get_json_params();

		$result = Orders::update_status(
			absint( $request['id'] ),
			sanitize_key( $data['status'] ?? '' ),
			sanitize_textarea_field( $data['note'] ?? '' )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result );
	}

	/**
	 * Add payment.
	 */
	public function payment( $request ) {

		$data = $request->get_json_params();

		$result = Orders::add_payment(
			absint( $request['id'] ),
			$data
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Add refund.
	 */
	public function refund( $request ) {

		$data = $request->get_json_params();

		$result = Orders::refund(
			absint( $request['id'] ),
			$data
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}
}
