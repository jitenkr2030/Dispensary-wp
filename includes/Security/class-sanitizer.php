<?php
namespace Dispensary_WP\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sanitizer {

	public static function text( $value ) {
		return sanitize_text_field( wp_unslash( $value ) );
	}

	public static function textarea( $value ) {
		return sanitize_textarea_field( wp_unslash( $value ) );
	}

	public static function key( $value ) {
		return sanitize_key( $value );
	}

	public static function email( $value ) {
		return sanitize_email( $value );
	}

	public static function url( $value ) {
		return esc_url_raw( $value );
	}

	public static function int( $value ) {
		return absint( $value );
	}

	public static function float( $value ) {
		return is_numeric( $value ) ? (float) $value : 0.0;
	}

	public static function money( $value ) {
		return number_format(
			max( 0, (float) $value ),
			2,
			'.',
			''
		);
	}

	public static function slug( $value ) {
		return sanitize_title( $value );
	}

	public static function html( $value, $allowed = array() ) {
		return wp_kses(
			wp_unslash( $value ),
			$allowed ?: wp_kses_allowed_html( 'post' )
		);
	}

	public static function array( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		return array_map(
			array( __CLASS__, 'recursive_scalar' ),
			$value
		);
	}

	private static function recursive_scalar( $value ) {

		if ( is_array( $value ) ) {
			return self::array( $value );
		}

		if ( is_scalar( $value ) ) {
			return self::text( $value );
		}

		return '';
	}
}
