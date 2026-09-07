<?php

namespace Dispensary_WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Migrations {

	private $database;

	private $schema;

	public function __construct(
		Database $database,
		Schema $schema
	) {
		$this->database = $database;
		$this->schema   = $schema;
	}

	public function run() {

		$current_version = $this->database->get_version();

		if (
			version_compare(
				$current_version,
				Database::DB_VERSION,
				'<'
			)
		) {
			$this->schema->install();
		}
	}

	public function reset() {

		$this->schema->uninstall();

		$this->schema->install();
	}
}
