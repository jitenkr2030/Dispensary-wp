<?php
namespace Dispensary_WP\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nonce {

	const ADMIN_ACTION = 'dispensary_wp_admin';
	const REST_ACTION  = 'wp_rest';

	public static function create( $action = self::ADMIN_ACTION ) {
		return wp_create_nonce( $action );
	}

	public static function field(
		$action = self::ADMIN_ACTION,
		$name = '_dispensary_nonce',
		$referer = true
	) {
		return wp_nonce_field(
			$action,
			$name,
			$referer,
			false
		);
	}

	public static function verify(
		$nonce,
		$action = self::ADMIN_ACTION
	) {
		$nonce = sanitize_text_field(
			wp_unslash( $nonce )
		);

		return (bool) wp_verify_nonce(
			$nonce,
			$action
		);
	}

	public static function verify_request(
		$action = self::ADMIN_ACTION,
		$name = '_dispensary_nonce'
	) {
		$nonce = isset( $_REQUEST[ $name ] )
			? $_REQUEST[ $name ]
			: '';

		return self::verify(
			$nonce,
			$action
		);
	}

	public static function require_request(
		$action = self::ADMIN_ACTION,
		$name = '_dispensary_nonce'
	) {
		if ( ! self::verify_request( $action, $name ) ) {

			wp_die(
				esc_html__(
					'Security check failed.',
					'dispensary-wp'
				),
				esc_html__(
					'Security error',
					'dispensary-wp'
				),
				array(
					'response' => 403,
				)
			);
		}

		return true;
	}
}
