<?php
/**
 * POS REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\POS\POS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class POS_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/pos/registers',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'registers' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_use_pos' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/pos/sessions/open',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'open_session' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_use_pos' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/pos/sessions/(?P<id>\d+)/close',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'close_session' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_use_pos' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/pos/sales',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'sale' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_use_pos' ),
			)
		);
	}

	/**
	 * Registers.
	 */
	public function registers() {
		return REST_API::response( POS::registers() );
	}

	/**
	 * Open session.
	 */
	public function open_session( $request ) {

		$data = $request->get_json_params();

		$result = POS::open_session( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Close session.
	 */
	public function close_session( $request ) {

		$data = $request->get_json_params();

		$result = POS::close_session(
			absint( $request['id'] ),
			$data
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result );
	}

	/**
	 * Create sale.
	 */
	public function sale( $request ) {

		$data = $request->get_json_params();

		$result = POS::create_sale( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}
}
