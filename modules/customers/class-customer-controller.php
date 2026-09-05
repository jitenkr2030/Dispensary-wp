<?php
namespace Dispensary_WP\Modules\Customers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customer_Controller {

	public static function get( $id ) {

		$customer = Customer::find(
			$id
		);

		if ( ! $customer ) {
			return new \WP_Error(
				'customer_not_found',
				'Customer not found.',
				array(
					'status' => 404,
				)
			);
		}

		$customer['profile'] =
			Customer_Profile::get( $id );

		$customer['verifications'] =
			Customer_Verification::all( $id );

		$customer['history'] =
			Customer_History::all( $id );

		return $customer;
	}

	public static function list(
		$args = array()
	) {
		return Customer::all(
			$args
		);
	}

	public static function create(
		$data
	) {
		return Customers::create(
			$data
		);
	}

	public static function update(
		$id,
		$data
	) {
		return Customers::update(
			$id,
			$data
		);
	}

	public static function delete(
		$id
	) {
		return Customers::delete(
			$id
		);
	}

	public static function save_profile(
		$customer_id,
		$data
	) {
		return Customer_Profile::save(
			$customer_id,
			$data
		);
	}

	public static function history(
		$customer_id,
		$limit = 100
	) {
		return Customer_History::all(
			$customer_id,
			$limit
		);
	}
}
