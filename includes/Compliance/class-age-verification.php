<?php
namespace Dispensary_WP\Compliance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Age_Verification {

	const SESSION_KEY = 'dispensary_wp_age_verified';

	public static function required() {
		return (bool) apply_filters(
			'dispensary_wp_age_verification_required',
			true
		);
	}

	public static function minimum_age() {
		return absint(
			apply_filters(
				'dispensary_wp_minimum_age',
				18
			)
		);
	}

	public static function is_verified() {
		if ( ! self::required() ) {
			return true;
		}

		return isset( $_COOKIE[ self::SESSION_KEY ] )
			&& '1' === $_COOKIE[ self::SESSION_KEY ];
	}

	public static function verify( $age ) {

		$age = absint( $age );

		if ( $age < self::minimum_age() ) {
			return false;
		}

		self::set_cookie();

		return true;
	}

	public static function set_cookie() {

		$secure = is_ssl();

		setcookie(
			self::SESSION_KEY,
			'1',
			array(
				'expires'  => time() + DAY_IN_SECONDS,
				'path'     => COOKIEPATH ?: '/',
				'domain'   => COOKIE_DOMAIN ?: '',
				'secure'   => $secure,
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}

	public static function clear() {

		setcookie(
			self::SESSION_KEY,
			'',
			array(
				'expires'  => time() - HOUR_IN_SECONDS,
				'path'     => COOKIEPATH ?: '/',
				'domain'   => COOKIE_DOMAIN ?: '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}
}
