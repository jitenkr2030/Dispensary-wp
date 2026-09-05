<?php
namespace Dispensary_WP\Modules\Customers;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customer {

	public static function find( $id ) {
		global $wpdb;

		$table = Database::table( 'customers' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE id = %d
				LIMIT 1",
				absint( $id )
			),
			ARRAY_A
		);
	}

	public static function find_by_email( $email ) {
		global $wpdb;

		$table = Database::table( 'customers' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE email = %s
				LIMIT 1",
				sanitize_email( $email )
			),
			ARRAY_A
		);
	}

	public static function find_by_user( $user_id ) {
		global $wpdb;

		$table = Database::table( 'customers' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE user_id = %d
				LIMIT 1",
				absint( $user_id )
			),
			ARRAY_A
		);
	}

	public static function all( $args = array() ) {
		global $wpdb;

		$table = Database::table( 'customers' );

		$defaults = array(
			'status' => '',
			'search' => '',
			'limit'  => 50,
			'offset' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = sanitize_key( $args['status'] );
		}

		if ( $args['search'] ) {
			$search = '%' . $wpdb->esc_like(
				sanitize_text_field( $args['search'] )
			) . '%';

			$where[] = '(first_name LIKE %s
				OR last_name LIKE %s
				OR email LIKE %s
				OR phone LIKE %s)';

			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
		}

		$limit = max(
			1,
			min( 200, absint( $args['limit'] ) )
		);

		$offset = max(
			0,
			absint( $args['offset'] )
		);

		$sql = "SELECT * FROM {$table}
			WHERE " . implode( ' AND ', $where ) .
			" ORDER BY id DESC LIMIT %d OFFSET %d";

		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results(
			$wpdb->prepare( $sql, $params ),
			ARRAY_A
		);
	}

	public static function create( $data ) {
		global $wpdb;

		$table = Database::table( 'customers' );
		$now   = current_time( 'mysql', true );

		$result = $wpdb->insert(
			$table,
			array(
				'user_id'    => absint( $data['user_id'] ?? 0 ),
				'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
				'last_name'  => sanitize_text_field( $data['last_name'] ?? '' ),
				'email'      => sanitize_email( $data['email'] ?? '' ),
				'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
				'status'     => sanitize_key( $data['status'] ?? 'active' ),
				'notes'      => sanitize_textarea_field( $data['notes'] ?? '' ),
				'created_at' => $now,
				'updated_at' => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'customer_create_failed',
				$wpdb->last_error
			);
		}

		return absint( $wpdb->insert_id );
	}

	public static function update( $id, $data ) {
		global $wpdb;

		$table = Database::table( 'customers' );

		$fields = array();

		if ( isset( $data['user_id'] ) ) {
			$fields['user_id'] = absint( $data['user_id'] );
		}

		if ( isset( $data['first_name'] ) ) {
			$fields['first_name'] = sanitize_text_field(
				$data['first_name']
			);
		}

		if ( isset( $data['last_name'] ) ) {
			$fields['last_name'] = sanitize_text_field(
				$data['last_name']
			);
		}

		if ( isset( $data['email'] ) ) {
			$fields['email'] = sanitize_email(
				$data['email']
			);
		}

		if ( isset( $data['phone'] ) ) {
			$fields['phone'] = sanitize_text_field(
				$data['phone']
			);
		}

		if ( isset( $data['status'] ) ) {
			$fields['status'] = sanitize_key(
				$data['status']
			);
		}

		if ( isset( $data['notes'] ) ) {
			$fields['notes'] = sanitize_textarea_field(
				$data['notes']
			);
		}

		$fields['updated_at'] = current_time(
			'mysql',
			true
		);

		$formats = array();

		foreach ( $fields as $key => $value ) {
			$formats[] = 'user_id' === $key
				? '%d'
				: '%s';
		}

		$result = $wpdb->update(
			$table,
			$fields,
			array( 'id' => absint( $id ) ),
			$formats,
			array( '%d' )
		);

		if ( false === $result ) {
			return new \WP_Error(
				'customer_update_failed',
				$wpdb->last_error
			);
		}

		return true;
	}

	public static function delete( $id ) {
		global $wpdb;

		return false !== $wpdb->delete(
			Database::table( 'customers' ),
			array(
				'id' => absint( $id ),
			),
			array( '%d' )
		);
	}
}
