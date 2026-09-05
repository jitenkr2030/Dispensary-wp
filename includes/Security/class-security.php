<?php
namespace Dispensary_WP\Security;

use Dispensary_WP\Permissions\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Security {

	public function __construct() {
		add_filter(
			'query_vars',
			array( $this, 'query_vars' )
		);
	}

	public function query_vars( $vars ) {
		$vars[] = 'dispensary_action';

		return $vars;
	}

	public static function require_capability( $capability ) {
		return Permissions::require_capability(
			$capability
		);
	}

	public static function current_user_can( $capability ) {
		return current_user_can( $capability );
	}

	public static function json_body() {

		$raw = file_get_contents(
			'php://input'
		);

		if ( ! $raw ) {
			return array();
		}

		$data = json_decode(
			$raw,
			true
		);

		return is_array( $data )
			? $data
			: array();
	}

	public static function secure_compare(
		$known,
		$user
	) {
		return hash_equals(
			(string) $known,
			(string) $user
		);
	}

	public static function rate_limit_key(
		$action,
		$identifier = ''
	) {
		$identifier = $identifier
			?: self::get_client_identifier();

		return 'disp_wp_rl_' . md5(
			sanitize_key( $action )
			. '|'
			. $identifier
		);
	}

	public static function check_rate_limit(
		$action,
		$limit = 30,
		$window = 60,
		$identifier = ''
	) {
		$key = self::rate_limit_key(
			$action,
			$identifier
		);

		$count = (int) get_transient( $key );

		if ( $count >= (int) $limit ) {
			return false;
		}

		set_transient(
			$key,
			$count + 1,
			max( 1, (int) $window )
		);

		return true;
	}

	private static function get_client_identifier() {

		$user_id = get_current_user_id();

		if ( $user_id ) {
			return 'user:' . $user_id;
		}

		$ip = Audit_Log::get_ip();

		return 'ip:' . (
			$ip ?: 'unknown'
		);
	}
}
