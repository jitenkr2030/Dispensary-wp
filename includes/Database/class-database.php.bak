<?php
/**
 * Database handler.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database {

	const DB_VERSION = '1.0.0';

	private static $instance = null;

	private $wpdb;

	private $prefix;

	private function __construct() {
		global $wpdb;

		$this->wpdb   = $wpdb;
		$this->prefix = $wpdb->prefix . 'disp_';
	}

	/**
	 * Get singleton instance.
	 *
	 * @return Database
	 */
	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get wpdb object.
	 *
	 * @return \wpdb
	 */
	public function get_wpdb() {
		return $this->wpdb;
	}

	/**
	 * Get plugin database prefix.
	 *
	 * @return string
	 */
	public function get_prefix() {
		return $this->prefix;
	}

	/**
	 * Get table name.
	 *
	 * This is static because the plugin architecture uses
	 * Database::table( 'table_name' ) throughout the codebase.
	 *
	 * @param string $name Table name without prefix.
	 * @return string
	 */
	public static function table( $name ) {
		global $wpdb;

		$prefix = $wpdb->prefix . 'disp_';

		return $prefix . sanitize_key( $name );
	}

	/**
	 * Get database version.
	 *
	 * @return string
	 */
	public function get_version() {
		return get_option(
			'dispensary_wp_db_version',
			'0.0.0'
		);
	}

	/**
	 * Set database version.
	 *
	 * @param string $version Database version.
	 * @return bool
	 */
	public function set_version( $version ) {
		return update_option(
			'dispensary_wp_db_version',
			sanitize_text_field( $version ),
			false
		);
	}
}
