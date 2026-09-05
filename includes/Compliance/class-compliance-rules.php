<?php
namespace Dispensary_WP\Compliance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Compliance_Rules {

	public static function validate_product( $product ) {

		$errors = array();

		if ( ! is_array( $product ) ) {
			return array(
				'valid'  => false,
				'errors' => array( 'Invalid product data.' ),
			);
		}

		if (
			isset( $product['age_restricted'] )
			&& $product['age_restricted']
			&& Age_Verification::required()
			&& ! Age_Verification::is_verified()
		) {
			$errors[] = 'Age verification is required.';
		}

		if (
			isset( $product['restricted_countries'] )
			&& is_array( $product['restricted_countries'] )
		) {
			$country = Jurisdiction::get_country();

			if (
				$country
				&& in_array(
					strtoupper( $country ),
					array_map(
						'strtoupper',
						$product['restricted_countries']
					),
					true
				)
			) {
				$errors[] = 'This product is restricted in your jurisdiction.';
			}
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}

	public static function validate_order( $order ) {

		$errors = array();

		if ( ! is_array( $order ) ) {
			return array(
				'valid'  => false,
				'errors' => array( 'Invalid order data.' ),
			);
		}

		if (
			isset( $order['requires_age_verification'] )
			&& $order['requires_age_verification']
			&& ! Age_Verification::is_verified()
		) {
			$errors[] = 'Age verification is required before ordering.';
		}

		$country = isset( $order['country'] )
			? sanitize_text_field( $order['country'] )
			: '';

		if ( ! Jurisdiction::is_allowed( $country ) ) {
			$errors[] = 'Orders are not permitted in this jurisdiction.';
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}
}
