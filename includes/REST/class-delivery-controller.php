<?php
/**
 * Delivery REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\Delivery\Delivery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delivery_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/deliveries',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'index' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_delivery' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/deliveries',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_delivery' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/deliveries/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'show' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_delivery' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/deliveries/(?P<id>\d+)/assign',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'assign' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_delivery' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/deliveries/(?P<id>\d+)/status',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_delivery' ),
			)
		);
	}

	/**
	 * List deliveries.
	 */
	public function index( $request ) {

		$args = array(
			'status'    => sanitize_key( $request->get_param( 'status' ) ),
			'driver_id' => absint( $request->get_param( 'driver_id' ) ),
			'limit'     => absint( $request->get_param( 'limit' ) ),
			'offset'    => absint( $request->get_param( 'offset' ) ),
		);

		return REST_API::response(
			Delivery::list_deliveries( $args )
		);
	}

	/**
	 * Create delivery.
	 */
	public function create( $request ) {

		$data = $request->get_json_params();

		$result = Delivery::create( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Show delivery.
	 */
	public function show( $request ) {

		$result = Delivery::get(
			absint( $request['id'] )
		);

		if ( ! $result ) {
			return new \WP_Error(
				'delivery_not_found',
				__( 'Delivery not found.', 'dispensary-wp' ),
				array( 'status' => 404 )
			);
		}

		return REST_API::response( $result );
	}

	/**
	 * Assign driver.
	 */
	public function assign( $request ) {

		$data = $request->get_json_params();

		$result = Delivery::assign_driver(
			absint( $request['id'] ),
			absint( $data['driver_id'] ?? 0 )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result );
	}

	/**
	 * Update delivery status.
	 */
	public function status( $request ) {

		$data = $request->get_json_params();

		$result = Delivery::update_status(
			absint( $request['id'] ),
			sanitize_key( $data['status'] ?? '' ),
			sanitize_textarea_field( $data['note'] ?? '' )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result );
	}
}
