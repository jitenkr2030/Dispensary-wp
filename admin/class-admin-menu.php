<?php
/**
 * Admin menu.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Menu {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Register menus.
	 *
	 * @return void
	 */
	public function register_menu() {

		add_menu_page(
			__( 'Dispensary WP', 'dispensary-wp' ),
			__( 'Dispensary WP', 'dispensary-wp' ),
			'dispensary_view_dashboard',
			'dispensary-wp',
			array( $this, 'dashboard' ),
			'dashicons-store',
			25
		);

		$this->submenu(
			__( 'Dashboard', 'dispensary-wp' ),
			'dispensary_view_dashboard',
			'dispensary-wp',
			'dashboard'
		);

		$this->submenu(
			__( 'Products', 'dispensary-wp' ),
			'dispensary_view_products',
			'dispensary-wp-products',
			'products'
		);

		$this->submenu(
			__( 'Inventory', 'dispensary-wp' ),
			'dispensary_view_inventory',
			'dispensary-wp-inventory',
			'inventory'
		);

		$this->submenu(
			__( 'Customers', 'dispensary-wp' ),
			'dispensary_view_customers',
			'dispensary-wp-customers',
			'customers'
		);

		$this->submenu(
			__( 'Orders', 'dispensary-wp' ),
			'dispensary_view_orders',
			'dispensary-wp-orders',
			'orders'
		);

		$this->submenu(
			__( 'POS', 'dispensary-wp' ),
			'dispensary_view_dashboard',
			'dispensary-wp-pos',
			'pos'
		);

		$this->submenu(
			__( 'Delivery', 'dispensary-wp' ),
			'dispensary_view_delivery',
			'dispensary-wp-delivery',
			'delivery'
		);

		$this->submenu(
			__( 'Staff', 'dispensary-wp' ),
			'dispensary_view_staff',
			'dispensary-wp-staff',
			'staff'
		);

		$this->submenu(
			__( 'Loyalty', 'dispensary-wp' ),
			'dispensary_view_loyalty',
			'dispensary-wp-loyalty',
			'loyalty'
		);

		$this->submenu(
			__( 'Reports', 'dispensary-wp' ),
			'dispensary_view_reports',
			'dispensary-wp-reports',
			'reports'
		);

		$this->submenu(
			__( 'Compliance', 'dispensary-wp' ),
			'dispensary_view_compliance',
			'dispensary-wp-compliance',
			'compliance'
		);

		$this->submenu(
			__( 'Settings', 'dispensary-wp' ),
			'dispensary_manage_settings',
			'dispensary-wp-settings',
			'settings'
		);
	}

	/**
	 * Add submenu.
	 *
	 * @param string $title      Title.
	 * @param string $capability Capability.
	 * @param string $slug       Slug.
	 * @param string $view       View.
	 * @return void
	 */
	private function submenu( $title, $capability, $slug, $view ) {

		add_submenu_page(
			'dispensary-wp',
			$title,
			$title,
			$capability,
			$slug,
			function () use ( $view ) {
				$this->render_view( $view );
			}
		);
	}

	/**
	 * Dashboard callback.
	 *
	 * @return void
	 */
	public function dashboard() {
		$this->render_view( 'dashboard' );
	}

	/**
	 * Render view.
	 *
	 * @param string $view View name.
	 * @return void
	 */
	private function render_view( $view ) {

		$file = DISPENSARY_WP_DIR . 'admin/views/' . sanitize_file_name( $view ) . '.php';

		if ( file_exists( $file ) ) {
			include $file;
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Page not found.', 'dispensary-wp' ) . '</h1>';
		echo '</div>';
	}
}
