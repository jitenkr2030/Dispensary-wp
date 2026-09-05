<?php
namespace Dispensary_WP\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Validator {

	public static function required( $value ) {
		return ! ( null === $value || '' === trim( (string) $value ) );
	}

	public static function email( $value ) {
		return is_email( $value );
	}

	public static function url( $value ) {
		return (bool) filter_var( $value, FILTER_VALIDATE_URL );
	}

	public static function positive_int( $value ) {
		return filter_var( $value, FILTER_VALIDATE_INT ) !== false
			&& (int) $value > 0;
	}

	public static function non_negative_number( $value ) {
		return is_numeric( $value ) && (float) $value >= 0;
	}

	public static function enum( $value, array $allowed ) {
		return in_array( $value, $allowed, true );
	}

	public static function max_length( $value, $max ) {
		return mb_strlen( (string) $value ) <= (int) $max;
	}

	public static function date( $value ) {
		$date = \DateTime::createFromFormat(
			'Y-m-d',
			(string) $value
		);

		return $date && $date->format( 'Y-m-d' ) === $value;
	}

	public static function datetime( $value ) {
		$date = \DateTime::createFromFormat(
			'Y-m-d H:i:s',
			(string) $value
		);

		return $date && $date->format( 'Y-m-d H:i:s' ) === $value;
	}

	public static function array_of_strings( $value ) {
		if ( ! is_array( $value ) ) {
			return false;
		}

		foreach ( $value as $item ) {
			if ( ! is_scalar( $item ) ) {
				return false;
			}
		}

		return true;
	}
}
