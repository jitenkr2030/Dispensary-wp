<?php
/**
 * REST authentication controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Auth_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/auth/me',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'me' ),
				'permission_callback' => function () {
					return is_user_logged_in();
				},
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/auth/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Current user.
	 *
	 * @return \WP_REST_Response
	 */
	public function me() {

		$user = wp_get_current_user();

		return REST_API::response(
			array(
				'id'           => $user->ID,
				'login'        => $user->user_login,
				'email'        => $user->user_email,
				'display_name' => $user->display_name,
				'roles'        => $user->roles,
			)
		);
	}

	/**
	 * Authentication status.
	 *
	 * @return \WP_REST_Response
	 */
	public function status() {

		return REST_API::response(
			array(
				'authenticated' => is_user_logged_in(),
				'user_id'       => get_current_user_id(),
			)
		);
	}
}
