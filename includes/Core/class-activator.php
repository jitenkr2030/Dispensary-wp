<?php

namespace Dispensary_WP\Core;

use Dispensary_WP\Database\Database;
use Dispensary_WP\Database\Schema;
use Dispensary_WP\Database\Migrations;
use Dispensary_WP\Permissions\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Activator {

	public static function activate() {

		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			wp_die(
				esc_html__(
					'Dispensary WP requires PHP 7.4 or newer.',
					'dispensary-wp'
				)
			);
		}

		if ( version_compare( get_bloginfo( 'version' ), '6.5', '<' ) ) {
			wp_die(
				esc_html__(
					'Dispensary WP requires WordPress 6.5 or newer.',
					'dispensary-wp'
				)
			);
		}

		if ( false === get_option( 'dispensary_wp_settings', false ) ) {
			add_option(
				'dispensary_wp_settings',
				array(
					'enabled'  => true,
					'currency' => 'USD',
				),
				'',
				false
			);
		}

		require_once DISPENSARY_WP_DIR . 'includes/Database/class-database.php';
		require_once DISPENSARY_WP_DIR . 'includes/Database/class-schema.php';
		require_once DISPENSARY_WP_DIR . 'includes/Database/class-migrations.php';

		require_once DISPENSARY_WP_DIR . 'includes/Permissions/class-capabilities.php';
		require_once DISPENSARY_WP_DIR . 'includes/Permissions/class-roles.php';

		$database = Database::instance();

		$schema = new Schema(
			$database
		);

		$migrations = new Migrations(
			$database,
			$schema
		);

		$migrations->run();

		Roles::install();

		update_option(
			'dispensary_wp_version',
			DISPENSARY_WP_VERSION,
			false
		);

		flush_rewrite_rules();
	}
}
