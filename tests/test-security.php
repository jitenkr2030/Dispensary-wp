<?php
/**
 * Security test definitions.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dispensary_WP_Security_Test {

	/**
	 * Security components.
	 *
	 * @return array
	 */
	public static function required_components() {

		return array(
			'validator' => '\Dispensary_WP\Security\Validator',
			'sanitizer' => '\Dispensary_WP\Security\Sanitizer',
			'nonce'     => '\Dispensary_WP\Security\Nonce',
			'audit_log' => '\Dispensary_WP\Security\Audit_Log',
		);
	}

	/**
	 * Security rules to verify manually/automatically.
	 *
	 * @return array
	 */
	public static function rules() {

		return array(
			'capability_checks',
			'nonce_validation',
			'input_sanitization',
			'output_escaping',
			'prepared_sql',
			'ajax_nonce_validation',
			'rest_permission_callbacks',
			'audit_logging',
			'file_access_protection',
		);
	}
}
