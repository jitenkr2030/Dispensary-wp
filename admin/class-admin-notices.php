<?php
/**
 * Admin notices.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Notices {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'display' ) );
	}

	/**
	 * Display notices.
	 *
	 * @return void
	 */
	public function display() {

		if ( ! current_user_can( 'dispensary_view_dashboard' ) ) {
			return;
		}

		if ( isset( $_GET['dispensary_wp_notice'] ) ) {
			$notice = sanitize_key( wp_unslash( $_GET['dispensary_wp_notice'] ) );

			if ( 'saved' === $notice ) {
				echo '<div class="notice notice-success is-dismissible"><p>';
				echo esc_html__( 'Settings saved successfully.', 'dispensary-wp' );
				echo '</p></div>';
			}
		}
	}
}
