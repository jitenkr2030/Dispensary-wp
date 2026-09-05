<?php
namespace Dispensary_WP\Compliance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Jurisdiction {

	public static function get_country() {

		$country = '';

		if ( function_exists( 'WC' ) && WC()->customer ) {
			$country = WC()->customer->get_billing_country();
		}

		if ( ! $country && isset( $_REQUEST['country'] ) ) {
			$country = sanitize_text_field(
				wp_unslash( $_REQUEST['country'] )
			);
		}

		return strtoupper(
			sanitize_key( $country )
		);
	}

	public static function allowed_countries() {

		$countries = get_option(
			'dispensary_wp_allowed_countries',
			array()
		);

		return is_array( $countries )
			? array_map( 'strtoupper', $countries )
			: array();
	}

	public static function is_allowed( $country = '' ) {

		$country = $country ?: self::get_country();

		$allowed = self::allowed_countries();

		if ( empty( $allowed ) ) {
			return true;
		}

		return in_array(
			strtoupper( $country ),
			$allowed,
			true
		);
	}

	public static function validate( $country = '' ) {

		return array(
			'country' => strtoupper(
				$country ?: self::get_country()
			),
			'allowed' => self::is_allowed( $country ),
		);
	}
}
