<?php
namespace Dispensary_WP\Modules\Customers;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customer_History {

	public static function add(
		$customer_id,
		$event_type,
		$description = '',
		$object_type = '',
		$object_id = 0
	) {
		global $wpdb;

		$table = Database::table(
			'customer_history'
		);

		return $wpdb->insert(
			$table,
			array(
				'customer_id' => absint( $customer_id ),
				'event_type'  => sanitize_key( $event_type ),
				'object_type' => sanitize_key( $object_type ),
				'object_id'   => absint( $object_id ),
				'description' => sanitize_textarea_field( $description ),
				'created_by'  => get_current_user_id(),
				'created_at'  => current_time( 'mysql', true ),
			),
			array(
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
			)
		);
	}

	public static function all(
		$customer_id,
		$limit = 100
	) {
		global $wpdb;

		$table = Database::table(
			'customer_history'
		);

		$limit = max(
			1,
			min( 500, absint( $limit ) )
		);

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE customer_id = %d
				ORDER BY id DESC
				LIMIT %d",
				absint( $customer_id ),
				$limit
			),
			ARRAY_A
		);
	}
}
