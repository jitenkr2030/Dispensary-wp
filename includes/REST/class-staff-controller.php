<?php
/**
 * Staff REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\Staff\Staff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Staff_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/staff',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'index' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_staff' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/staff',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_staff' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/staff/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'show' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_staff' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/staff/(?P<id>\d+)/clock-in',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'clock_in' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_staff' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/staff/(?P<id>\d+)/clock-out',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'clock_out' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_staff' ),
			)
		);
	}

	/**
	 * List staff.
	 */
	public function index( $request ) {

		$args = array(
			'status'     => sanitize_key( $request->get_param( 'status' ) ),
			'department' => sanitize_text_field( $request->get_param( 'department' ) ),
			'limit'      => absint( $request->get_param( 'limit' ) ),
			'offset'     => absint( $request->get_param( 'offset' ) ),
		);

		return REST_API::response(
			Staff::list_staff( $args )
		);
	}

	/**
	 * Create staff.
	 */
	public function create( $request ) {

		$data = $request->get_json_params();

		$result = Staff::create_staff( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Show staff.
	 */
	public function show( $request ) {

		$result = Staff::get_staff(
			absint( $request['id'] )
		);

		if ( ! $result ) {
			return new \WP_Error(
				'staff_not_found',
				__( 'Staff member not found.', 'dispensary-wp' ),
				array( 'status' => 404 )
			);
		}

		return REST_API::response( $result );
	}

	/**
	 * Clock in.
	 */
	public function clock_in( $request ) {

		$data = $request->get_json_params();

		$result = Staff::clock_in(
			absint( $request['id'] ),
			$data
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Clock out.
	 */
	public function clock_out( $request ) {

		$data = $request->get_json_params();

		$result = Staff::clock_out(
			absint( $request['id'] ),
			$data
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result );
	}
}
