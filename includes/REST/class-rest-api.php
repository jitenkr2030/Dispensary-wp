<?php
/**
 * REST API bootstrap.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API {

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'dispensary-wp/v1';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		$auth       = new Auth_Controller();
		$products   = new Products_Controller();
		$inventory  = new Inventory_Controller();
		$customers  = new Customers_Controller();
		$orders     = new Orders_Controller();
		$pos        = new POS_Controller();
		$delivery   = new Delivery_Controller();
		$staff      = new Staff_Controller();
		$loyalty    = new Loyalty_Controller();
		$reports    = new Reports_Controller();

		$auth->register_routes();
		$products->register_routes();
		$inventory->register_routes();
		$customers->register_routes();
		$orders->register_routes();
		$pos->register_routes();
		$delivery->register_routes();
		$staff->register_routes();
		$loyalty->register_routes();
		$reports->register_routes();
	}

	/**
	 * Permission callback.
	 *
	 * @param string $capability Capability.
	 * @return callable
	 */
	public static function permission_callback( $capability = 'dispensary_view_dashboard' ) {
		return function () use ( $capability ) {
			return current_user_can( $capability );
		};
	}

	/**
	 * JSON response.
	 *
	 * @param mixed $data    Data.
	 * @param int   $status  HTTP status.
	 * @return \WP_REST_Response
	 */
	public static function response( $data, $status = 200 ) {
		return new \WP_REST_Response( $data, $status );
	}
}
