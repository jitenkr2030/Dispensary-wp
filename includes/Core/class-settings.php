<?php

namespace Dispensary_WP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	const OPTION_NAME = 'dispensary_wp_settings';

	private $defaults = array(
		'enabled'  => true,
		'currency' => 'USD',
	);

	public function get( $key = null ) {

		$settings = get_option(
			self::OPTION_NAME,
			$this->defaults
		);

		$settings = wp_parse_args(
			$settings,
			$this->defaults
		);

		if ( null === $key ) {
			return $settings;
		}

		return isset( $settings[ $key ] )
			? $settings[ $key ]
			: null;
	}

	public function update( $key, $value ) {

		$settings = $this->get();

		$settings[ $key ] = sanitize_text_field( $value );

		return update_option(
			self::OPTION_NAME,
			$settings,
			false
		);
	}

	public function reset() {

		return update_option(
			self::OPTION_NAME,
			$this->defaults,
			false
		);
	}
}
