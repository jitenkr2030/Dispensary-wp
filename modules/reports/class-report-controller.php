<?php
/**
 * Report controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Report_Controller {

	/**
	 * Reports service.
	 *
	 * @var Reports
	 */
	private $reports;

	/**
	 * Constructor.
	 *
	 * @param Reports|null $reports Reports service.
	 */
	public function __construct( $reports = null ) {
		$this->reports = $reports ? $reports : new Reports();
	}

	/**
	 * Sales summary.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array|WP_Error
	 */
	public function sales_summary( $from_date = '', $to_date = '' ) {
		return $this->reports->sales_summary( $from_date, $to_date );
	}

	/**
	 * Daily sales.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array|WP_Error
	 */
	public function daily_sales( $from_date = '', $to_date = '' ) {
		return $this->reports->daily_sales( $from_date, $to_date );
	}

	/**
	 * Top products.
	 *
	 * @param int $limit Limit.
	 * @return array|WP_Error
	 */
	public function top_products( $limit = 10 ) {
		return $this->reports->top_products( $limit );
	}

	/**
	 * Inventory summary.
	 *
	 * @return array|WP_Error
	 */
	public function inventory_summary() {
		return $this->reports->inventory_summary();
	}

	/**
	 * Low stock.
	 *
	 * @param int $threshold Threshold.
	 * @return array|WP_Error
	 */
	public function low_stock( $threshold = 10 ) {
		return $this->reports->low_stock( $threshold );
	}

	/**
	 * Expiring batches.
	 *
	 * @param int $days Days.
	 * @return array|WP_Error
	 */
	public function expiring_batches( $days = 30 ) {
		return $this->reports->expiring_batches( $days );
	}

	/**
	 * Customer summary.
	 *
	 * @return array|WP_Error
	 */
	public function customer_summary() {
		return $this->reports->customer_summary();
	}

	/**
	 * Top customers.
	 *
	 * @param int $limit Limit.
	 * @return array|WP_Error
	 */
	public function top_customers( $limit = 10 ) {
		return $this->reports->top_customers( $limit );
	}

	/**
	 * Staff summary.
	 *
	 * @return array|WP_Error
	 */
	public function staff_summary() {
		return $this->reports->staff_summary();
	}

	/**
	 * Staff attendance.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array|WP_Error
	 */
	public function staff_attendance( $from_date = '', $to_date = '' ) {
		return $this->reports->staff_attendance( $from_date, $to_date );
	}

	/**
	 * Product sales.
	 *
	 * @param int $limit Limit.
	 * @return array|WP_Error
	 */
	public function product_sales( $limit = 50 ) {
		return $this->reports->product_sales( $limit );
	}

	/**
	 * Revenue.
	 *
	 * @param string $from_date From date.
	 * @param string $to_date   To date.
	 * @return array|WP_Error
	 */
	public function revenue( $from_date = '', $to_date = '' ) {
		return $this->reports->revenue( $from_date, $to_date );
	}

	/**
	 * Payments.
	 *
	 * @return array|WP_Error
	 */
	public function payments() {
		return $this->reports->payments();
	}

	/**
	 * Refunds.
	 *
	 * @return array|WP_Error
	 */
	public function refunds() {
		return $this->reports->refunds();
	}
}
