<?php
/**
 * Reports REST controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\REST;

use Dispensary_WP\Modules\Reports\Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Reports_Controller {

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			REST_API::NAMESPACE,
			'/reports/sales',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'sales' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_reports' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/reports/inventory',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'inventory' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_reports' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/reports/customers',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'customers' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_reports' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/reports/staff',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'staff' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_reports' ),
			)
		);

		register_rest_route(
			REST_API::NAMESPACE,
			'/reports/financial',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'financial' ),
				'permission_callback' => REST_API::permission_callback( 'dispensary_view_reports' ),
			)
		);
	}

	/**
	 * Sales report.
	 */
	public function sales( $request ) {

		$reports = new Reports();

		return REST_API::response(
			array(
				'summary' => $reports->sales_summary(
					sanitize_text_field( $request->get_param( 'from' ) ),
					sanitize_text_field( $request->get_param( 'to' ) )
				),
				'daily'   => $reports->daily_sales(
					sanitize_text_field( $request->get_param( 'from' ) ),
					sanitize_text_field( $request->get_param( 'to' ) )
				),
				'top_products' => $reports->top_products(
					absint( $request->get_param( 'limit' ) ) ?: 10
				),
			)
		);
	}

	/**
	 * Inventory report.
	 */
	public function inventory( $request ) {

		$reports = new Reports();

		return REST_API::response(
			array(
				'stock'          => $reports->inventory_summary(),
				'low_stock'      => $reports->low_stock(
					absint( $request->get_param( 'threshold' ) ) ?: 10
				),
				'expiring_batch' => $reports->expiring_batches(
					absint( $request->get_param( 'days' ) ) ?: 30
				),
			)
		);
	}

	/**
	 * Customer report.
	 */
	public function customers() {

		$reports = new Reports();

		return REST_API::response(
			array(
				'summary'      => $reports->customer_summary(),
				'top_customers' => $reports->top_customers( 10 ),
			)
		);
	}

	/**
	 * Staff report.
	 */
	public function staff( $request ) {

		$reports = new Reports();

		return REST_API::response(
			array(
				'summary'    => $reports->staff_summary(),
				'attendance' => $reports->staff_attendance(
					sanitize_text_field( $request->get_param( 'from' ) ),
					sanitize_text_field( $request->get_param( 'to' ) )
				),
			)
		);
	}

	/**
	 * Financial report.
	 */
	public function financial( $request ) {

		$reports = new Reports();

		return REST_API::response(
			array(
				'revenue'  => $reports->revenue(
					sanitize_text_field( $request->get_param( 'from' ) ),
					sanitize_text_field( $request->get_param( 'to' ) )
				),
				'payments' => $reports->payments(),
				'refunds'  => $reports->refunds(),
			)
		);
	}
}
