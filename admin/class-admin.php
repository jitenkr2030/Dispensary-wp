<?php
/**
 * Admin bootstrap.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		new Admin_Menu();
		new Admin_Notices();
		new Admin_Dashboard();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {

		if ( false === strpos( $hook, 'dispensary-wp' ) ) {
			return;
		}

		wp_enqueue_style(
			'dispensary-wp-admin',
			DISPENSARY_WP_URL . 'admin/css/admin.css',
			array(),
			DISPENSARY_WP_VERSION
		);

		wp_enqueue_script(
			'dispensary-wp-admin',
			DISPENSARY_WP_URL . 'admin/js/admin.js',
			array( 'jquery' ),
			DISPENSARY_WP_VERSION,
			true
		);

		wp_localize_script(
			'dispensary-wp-admin',
			'DispensaryWPAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'dispensary_wp_admin' ),
			)
		);
	}
}
