<?php
/**
 * Public frontend bootstrap.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Public_Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Public_Frontend {

	/**
	 * Shortcodes instance.
	 *
	 * @var Shortcodes
	 */
	private $shortcodes;

	/**
	 * Frontend controller.
	 *
	 * @var Frontend_Controller
	 */
	private $controller;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->shortcodes  = new Shortcodes();
		$this->controller  = new Frontend_Controller();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_assets() {

		if ( is_admin() ) {
			return;
		}

		wp_enqueue_style(
			'dispensary-wp-public',
			DISPENSARY_WP_URL . 'public/css/public.css',
			array(),
			DISPENSARY_WP_VERSION
		);

		wp_enqueue_script(
			'dispensary-wp-public',
			DISPENSARY_WP_URL . 'public/js/public.js',
			array( 'jquery' ),
			DISPENSARY_WP_VERSION,
			true
		);

		wp_localize_script(
			'dispensary-wp-public',
			'DispensaryWP',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dispensary_wp_public' ),
			)
		);
	}
}
