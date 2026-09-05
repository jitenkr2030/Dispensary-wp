<?php
namespace Dispensary_WP\Security;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Audit_Log {

	public static function log(
		$action,
		$object_type = '',
		$object_id = 0,
		$details = array()
	) {
		global $wpdb;

		$table = Database::table( 'audit_logs' );

		$user_id = get_current_user_id();

		if ( is_array( $details ) || is_object( $details ) ) {
			$details = wp_json_encode( $details );
		} else {
			$details = (string) $details;
		}

		$details = wp_check_invalid_utf8( $details );
		$details = substr( $details, 0, 100000 );

		$ip = self::get_ip();

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] )
			? sanitize_text_field(
				wp_unslash( $_SERVER['HTTP_USER_AGENT'] )
			)
			: '';

		$user_agent = substr( $user_agent, 0, 1000 );

		return $wpdb->insert(
			$table,
			array(
				'user_id'     => absint( $user_id ),
				'action'      => sanitize_key( $action ),
				'object_type' => sanitize_key( $object_type ),
				'object_id'   => absint( $object_id ),
				'ip_address'  => $ip,
				'user_agent'  => $user_agent,
				'details'     => $details,
				'created_at'  => current_time( 'mysql', true ),
			),
			array(
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	public static function get_ip() {

		$ip = isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field(
				wp_unslash( $_SERVER['REMOTE_ADDR'] )
			)
			: '';

		return filter_var(
			$ip,
			FILTER_VALIDATE_IP
		) ? $ip : '';
	}
}
