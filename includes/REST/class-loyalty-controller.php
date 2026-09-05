<?php
/**
 * Loyalty REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\Loyalty\Loyalty;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Loyalty_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/loyalty/members',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'members' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_loyalty' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/loyalty/members',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_member' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_loyalty' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/loyalty/members/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'member' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_loyalty' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/loyalty/members/(?P<id>\d+)/points/add',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_points' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_loyalty' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/loyalty/members/(?P<id>\d+)/points/redeem',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'redeem_points' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_manage_loyalty' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/loyalty/rewards',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'rewards' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_loyalty' ),
			)
		);
	}

	/**
	 * Members.
	 */
	public function members( $request ) {

		return REST_API::response(
			Loyalty::list_members(
				array(
					'status' => sanitize_key( $request->get_param( 'status' ) ),
					'limit'  => absint( $request->get_param( 'limit' ) ),
					'offset' => absint( $request->get_param( 'offset' ) ),
				)
			)
		);
	}

	/**
	 * Create member.
	 */
	public function create_member( $request ) {

		$data = $request->get_json_params();

		$result = Loyalty::create_member( $data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Get member.
	 */
	public function member( $request ) {

		$result = Loyalty::get_member(
			absint( $request['id'] )
		);

		if ( ! $result ) {
			return new \WP_Error(
				'loyalty_member_not_found',
				__( 'Loyalty member not found.', 'dispensary-wp' ),
				array( 'status' => 404 )
			);
		}

		return REST_API::response( $result );
	}

	/**
	 * Add points.
	 */
	public function add_points( $request ) {

		$data = $request->get_json_params();

		$result = Loyalty::add_points(
			absint( $request['id'] ),
			absint( $data['points'] ?? 0 ),
			sanitize_text_field( $data['reason'] ?? '' ),
			absint( $data['order_id'] ?? 0 )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Redeem points.
	 */
	public function redeem_points( $request ) {

		$data = $request->get_json_params();

		$result = Loyalty::redeem_points(
			absint( $request['id'] ),
			absint( $data['points'] ?? 0 ),
			sanitize_text_field( $data['reason'] ?? '' ),
			absint( $data['order_id'] ?? 0 )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return REST_API::response( $result, 201 );
	}

	/**
	 * Rewards.
	 */
	public function rewards() {
		return REST_API::response(
			Loyalty::list_rewards()
		);
	}
}
