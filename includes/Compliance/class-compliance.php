<?php
namespace Dispensary_WP\Compliance;

use Dispensary_WP\Security\Security;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Compliance {

	public function __construct() {

		add_action(
			'init',
			array( $this, 'register_hooks' )
		);
	}

	public function register_hooks() {

		add_filter(
			'dispensary_wp_compliance_product',
			array( $this, 'validate_product' )
		);

		add_filter(
			'dispensary_wp_compliance_order',
			array( $this, 'validate_order' )
		);
	}

	public function validate_product( $product ) {
		return Compliance_Rules::validate_product(
			$product
		);
	}

	public function validate_order( $order ) {
		return Compliance_Rules::validate_order(
			$order
		);
	}

	public static function can_manage() {

		return current_user_can(
			'dispensary_manage_compliance'
		);
	}

	public static function require_manage() {

		return Security::require_capability(
			'dispensary_manage_compliance'
		);
	}

	public static function verify_age( $age ) {

		$result = Age_Verification::verify(
			$age
		);

		if ( $result ) {
			Compliance_Logger::age_verified(
				get_current_user_id()
			);
		}

		return $result;
	}

	public static function validate_jurisdiction(
		$country = ''
	) {

		$result = Jurisdiction::validate(
			$country
		);

		if ( ! $result['allowed'] ) {
			Compliance_Logger::jurisdiction_blocked(
				$result['country']
			);
		}

		return $result;
	}
}
