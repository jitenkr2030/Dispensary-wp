<?php
/**
 * Main plugin class.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Core;

use Dispensary_WP\Integration\Module_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Loader.
	 *
	 * @var Loader
	 */
	private $loader;

	/**
	 * Settings.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Hooks.
	 *
	 * @var Hooks
	 */
	private $hooks;

	/**
	 * I18n.
	 *
	 * @var I18n
	 */
	private $i18n;

	/**
	 * Module loader.
	 *
	 * @var Module_Loader
	 */
	private $module_loader;

	/**
	 * Get plugin instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {

		$this->loader   = Loader::instance();
		$this->settings = new Settings();
		$this->hooks    = new Hooks();
		$this->i18n     = new I18n();

		$this->register_core_hooks();

		add_action(
			'plugins_loaded',
			array( $this, 'initialize' ),
			20
		);
	}

	/**
	 * Register core hooks.
	 */
	private function register_core_hooks() {

		$this->loader->add_action(
			'init',
			$this->hooks,
			'register'
		);

		$this->loader->add_action(
			'plugins_loaded',
			$this->i18n,
			'load_textdomain',
			5
		);
	}

	/**
	 * Initialize plugin modules.
	 */
	public function initialize() {

		if ( ! class_exists( '\Dispensary_WP\Integration\Module_Loader' ) ) {
			return;
		}

		$this->module_loader = new Module_Loader();

		$modules = $this->module_loader->load();

		do_action(
			'dispensary_wp_plugin_initialized',
			$modules
		);
	}

	/**
	 * Get loader.
	 *
	 * @return Loader
	 */
	public function get_loader() {

		return $this->loader;
	}

	/**
	 * Get settings.
	 *
	 * @return Settings
	 */
	public function get_settings() {

		return $this->settings;
	}

	/**
	 * Get module loader.
	 *
	 * @return Module_Loader|null
	 */
	public function get_module_loader() {

		return $this->module_loader;
	}

	/**
	 * Run plugin.
	 */
	public function run() {

		$this->loader->run();
	}
}
