<?php
/**
 * Dispensary WP module loader.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module_Loader {

	/**
	 * Loaded modules.
	 *
	 * @var array
	 */
	private $modules = array();

	/**
	 * Load all application modules.
	 */
	public function load() {

		$this->load_module(
			'products',
			'\Dispensary_WP\Modules\Products\Products'
		);

		$this->load_module(
			'inventory',
			'\Dispensary_WP\Modules\Inventory\Inventory'
		);

		$this->load_module(
			'customers',
			'\Dispensary_WP\Modules\Customers\Customers'
		);

		$this->load_module(
			'orders',
			'\Dispensary_WP\Modules\Orders\Orders'
		);

		$this->load_module(
			'pos',
			'\Dispensary_WP\Modules\POS\POS'
		);

		$this->load_module(
			'delivery',
			'\Dispensary_WP\Modules\Delivery\Delivery'
		);

		$this->load_module(
			'staff',
			'\Dispensary_WP\Modules\Staff\Staff'
		);

		$this->load_module(
			'loyalty',
			'\Dispensary_WP\Modules\Loyalty\Loyalty'
		);

		$this->load_module(
			'reports',
			'\Dispensary_WP\Modules\Reports\Reports'
		);

		/**
		 * Security.
		 */
		$this->load_module(
			'security',
			'\Dispensary_WP\Security\Security'
		);

		/**
		 * Public frontend.
		 */
		$this->load_module(
			'public',
			'\Dispensary_WP\Public_Frontend\Public_Frontend'
		);

		/**
		 * Admin.
		 */
		$this->load_module(
			'admin',
			'\Dispensary_WP\Admin\Admin'
		);

		/**
		 * WooCommerce.
		 */
		$this->load_module(
			'woocommerce',
			'\Dispensary_WP\WooCommerce\WooCommerce'
		);

		/**
		 * REST API.
		 */
		$this->load_module(
			'rest',
			'\Dispensary_WP\REST\REST_API'
		);

		/**
		 * Templates.
		 */
		$this->load_template_system();

		do_action(
			'dispensary_wp_modules_loaded',
			$this->modules
		);

		return $this->modules;
	}

	/**
	 * Load one module.
	 *
	 * @param string $name Module name.
	 * @param string $class Class name.
	 */
	private function load_module( $name, $class ) {

		if ( ! class_exists( $class ) ) {
			$this->modules[ $name ] = false;
			return;
		}

		try {

			$this->modules[ $name ] = new $class();

		} catch ( \Throwable $exception ) {

			$this->modules[ $name ] = false;

			do_action(
				'dispensary_wp_module_load_error',
				$name,
				$exception
			);
		}
	}

	/**
	 * Load template helper functions.
	 */
	private function load_template_system() {

		$file = DISPENSARY_WP_DIR . 'templates/template-functions.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Get loaded modules.
	 *
	 * @return array
	 */
	public function get_modules() {

		return $this->modules;
	}
}
