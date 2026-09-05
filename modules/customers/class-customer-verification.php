<?php
namespace Dispensary_WP\Modules\Customers;

use Dispensary_WP\Database\Database;
use Dispensary_WP\Security\Audit_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customer_Verification {

	public static function create(
		$customer_id,
		$data
	) {
		global $wpdb;

		$table = Database::table(
			'customer_verifications'
		);

		$now = current_time(
			'mysql',
			true
		);

		$result = $wpdb->insert(
			$table,
			array(
				'customer_id'      => absint( $customer_id ),
				'verification_type'=> sanitize_key( $data['verification_type'] ?? 'general' ),
				'status'           => sanitize_key( $data['status'] ?? 'pending' ),
				'reference'        => sanitize_text_field( $data['reference'] ?? '' ),
				'verified_by'      => absint( $data['verified_by'] ?? 0 ),
				'verified_at'      => ! empty( $data['verified_at'] )
					? sanitize_text_field( $data['verified_at'] )
					: null,
				'expires_at'       => ! empty( $data['expires_at'] )
					? sanitize_text_field( $data['expires_at'] )
					: null,
				'notes'            => sanitize_textarea_field( $data['notes'] ?? '' ),
				'created_at'       => $now,
				'updated_at'       => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'verification_create_failed',
				$wpdb->last_error
			);
		}

		$id = absint(
			$wpdb->insert_id
		);

		Audit_Log::log(
			'customer_verification_created',
			'customer_verification',
			$id,
			array(
				'customer_id' => absint( $customer_id ),
				'type'        => sanitize_key(
					$data['verification_type'] ?? 'general'
				),
			)
		);

		return $id;
	}

	public static function all( $customer_id ) {
		global $wpdb;

		$table = Database::table(
			'customer_verifications'
		);

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE customer_id = %d
				ORDER BY id DESC",
				absint( $customer_id )
			),
			ARRAY_A
		);
	}

	public static function latest(
		$customer_id,
		$type = ''
	) {
		global $wpdb;

		$table = Database::table(
			'customer_verifications'
		);

		if ( $type ) {
			return $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$table}
					WHERE customer_id = %d
					AND verification_type = %s
					ORDER BY id DESC
					LIMIT 1",
					absint( $customer_id ),
					sanitize_key( $type )
				),
				ARRAY_A
			);
		}

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE customer_id = %d
				ORDER BY id DESC
				LIMIT 1",
				absint( $customer_id )
			),
			ARRAY_A
		);
	}
}
