<?php
namespace Dispensary_WP\Modules\Customers;

use Dispensary_WP\Permissions\Permissions;
use Dispensary_WP\Security\Audit_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customers {

	public function __construct() {

		add_action(
			'init',
			array( $this, 'register' )
		);
	}

	public function register() {

		do_action(
			'dispensary_wp_customers_loaded'
		);
	}

	public static function create( $data ) {

		Permissions::require_capability(
			'dispensary_manage_customers'
		);

		$id = Customer::create( $data );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		Customer_History::add(
			$id,
			'created',
			'Customer account created.',
			'customer',
			$id
		);

		Audit_Log::log(
			'customer_created',
			'customer',
			$id
		);

		return $id;
	}

	public static function update(
		$id,
		$data
	) {

		Permissions::require_capability(
			'dispensary_manage_customers'
		);

		$result = Customer::update(
			$id,
			$data
		);

		if ( ! is_wp_error( $result ) ) {

			Customer_History::add(
				$id,
				'updated',
				'Customer information updated.',
				'customer',
				$id
			);

			Audit_Log::log(
				'customer_updated',
				'customer',
				$id
			);
		}

		return $result;
	}

	public static function delete( $id ) {

		Permissions::require_capability(
			'dispensary_manage_customers'
		);

		$result = Customer::delete(
			$id
		);

		if ( $result ) {
			Audit_Log::log(
				'customer_deleted',
				'customer',
				$id
			);
		}

		return $result;
	}
}
