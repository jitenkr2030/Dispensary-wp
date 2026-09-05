<?php
namespace Dispensary_WP\Compliance;

use Dispensary_WP\Security\Audit_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Compliance_Logger {

	public static function log(
		$action,
		$object_type = '',
		$object_id = 0,
		$details = array()
	) {

		$details = is_array( $details )
			? $details
			: array(
				'message' => (string) $details,
			);

		$details['compliance_event'] = true;

		return Audit_Log::log(
			$action,
			$object_type,
			$object_id,
			$details
		);
	}

	public static function age_verified(
		$user_id = 0
	) {

		return self::log(
			'age_verified',
			'user',
			absint( $user_id ),
			array(
				'minimum_age' => Age_Verification::minimum_age(),
			)
		);
	}

	public static function jurisdiction_blocked(
		$country = ''
	) {

		return self::log(
			'jurisdiction_blocked',
			'jurisdiction',
			0,
			array(
				'country' => sanitize_text_field( $country ),
			)
		);
	}
}
