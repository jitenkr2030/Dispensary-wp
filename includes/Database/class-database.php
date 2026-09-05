<?php

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

	public static function instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_wpdb() {
		return $this->wpdb;
	}

	public function get_prefix() {
		return $this->prefix;
	}

	public function table( $name ) {
		return $this->prefix . sanitize_key( $name );
	}

	public function get_version() {
		return get_option(
			'dispensary_wp_db_version',
			'0.0.0'
		);
	}

	public function set_version( $version ) {
		return update_option(
			'dispensary_wp_db_version',
			sanitize_text_field( $version ),
			false
		);
	}
}
