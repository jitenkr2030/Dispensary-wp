<?php
/**
 * Reports service.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Reports;

use Dispensary_WP\Permissions\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Reports {

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Sales summary.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array|WP_Error
	 */
	public function sales_summary( $from_date = '', $to_date = '' ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Sales_Report::summary( $from_date, $to_date );
	}

	/**
	 * Daily sales.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array|WP_Error
	 */
	public function daily_sales( $from_date = '', $to_date = '' ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Sales_Report::daily( $from_date, $to_date );
	}

	/**
	 * Sales by payment status.
	 *
	 * @return array|WP_Error
	 */
	public function sales_by_payment_status() {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Sales_Report::by_payment_status();
	}

	/**
	 * Top products.
	 *
	 * @param int $limit Limit.
	 * @return array|WP_Error
	 */
	public function top_products( $limit = 10 ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Sales_Report::top_products( $limit );
	}

	/**
	 * Inventory summary.
	 *
	 * @return array|WP_Error
	 */
	public function inventory_summary() {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Inventory_Report::summary();
	}

	/**
	 * Low stock.
	 *
	 * @param int $threshold Threshold.
	 * @return array|WP_Error
	 */
	public function low_stock( $threshold = 10 ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Inventory_Report::low_stock( $threshold );
	}

	/**
	 * Expiring batches.
	 *
	 * @param int $days Days.
	 * @return array|WP_Error
	 */
	public function expiring_batches( $days = 30 ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Inventory_Report::expiring_batches( $days );
	}

	/**
	 * Customer summary.
	 *
	 * @return array|WP_Error
	 */
	public function customer_summary() {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Customer_Report::summary();
	}

	/**
	 * Top customers.
	 *
	 * @param int $limit Limit.
	 * @return array|WP_Error
	 */
	public function top_customers( $limit = 10 ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Customer_Report::top_customers( $limit );
	}

	/**
	 * Staff summary.
	 *
	 * @return array|WP_Error
	 */
	public function staff_summary() {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Staff_Report::summary();
	}

	/**
	 * Staff attendance.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array|WP_Error
	 */
	public function staff_attendance( $from_date = '', $to_date = '' ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Staff_Report::attendance( $from_date, $to_date );
	}

	/**
	 * Product sales.
	 *
	 * @param int $limit Limit.
	 * @return array|WP_Error
	 */
	public function product_sales( $limit = 50 ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Product_Report::sales( $limit );
	}

	/**
	 * Financial revenue.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array|WP_Error
	 */
	public function revenue( $from_date = '', $to_date = '' ) {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Financial_Report::revenue( $from_date, $to_date );
	}

	/**
	 * Payment report.
	 *
	 * @return array|WP_Error
	 */
	public function payments() {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Financial_Report::payments();
	}

	/**
	 * Refund report.
	 *
	 * @return array|WP_Error
	 */
	public function refunds() {
		$permission = $this->permission();

		if ( is_wp_error( $permission ) ) {
			return $permission;
		}

		return Financial_Report::refunds();
	}

	/**
	 * Check report permission.
	 *
	 * @return true|WP_Error
	 */
	private function permission() {
		if ( Permissions::can( 'dispensary_view_reports' ) ) {
			return true;
		}

		return new \WP_Error(
			'dispensary_reports_forbidden',
			__( 'You do not have permission to view reports.', 'dispensary-wp' ),
			array( 'status' => 403 )
		);
	}
}
