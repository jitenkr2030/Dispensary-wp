<?php

namespace Dispensary_WP\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hooks {

	private $loader;

	public function __construct( Loader $loader ) {

		$this->loader = $loader;
	}

	public function register() {

		$this->loader->add_action(
			'init',
			array( $this, 'initialize' ),
			10,
			0
		);

		$this->loader->add_filter(
			'plugin_action_links_' . DISPENSARY_WP_BASENAME,
			array( $this, 'plugin_action_links' ),
			10,
			1
		);
	}

	public function initialize() {

		do_action( 'dispensary_wp_loaded' );
	}

	public function plugin_action_links( $links ) {

		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url(
				admin_url(
					'admin.php?page=dispensary-wp-settings'
				)
			),
			esc_html__(
				'Settings',
				'dispensary-wp'
			)
		);

		array_unshift(
			$links,
			$settings_link
		);

		return $links;
	}
}
