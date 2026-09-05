<?php

namespace Dispensary_WP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class I18n {

	public function load_textdomain() {

		load_plugin_textdomain(
			'dispensary-wp',
			false,
			dirname( DISPENSARY_WP_BASENAME ) . '/languages'
		);
	}
}
