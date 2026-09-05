<?php

namespace Dispensary_WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Repository {

	protected $database;

	protected $wpdb;

	protected $table;

	public function __construct(
		Database $database,
		$table
	) {
		$this->database = $database;
		$this->wpdb     = $database->get_wpdb();
		$this->table    = $database->table( $table );
	}

	public function find( $id ) {

		$id = absint( $id );

		if ( ! $id ) {
			return null;
		}

		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d",
				$id
			)
		);
	}

	public function delete( $id ) {

		$id = absint( $id );

		if ( ! $id ) {
			return false;
		}

		return false !== $this->wpdb->delete(
			$this->table,
			array(
				'id' => $id,
			),
			array(
				'%d',
			)
		);
	}

	protected function insert( $data, $format = array() ) {

		$result = $this->wpdb->insert(
			$this->table,
			$data,
			$format
		);

		if ( false === $result ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	protected function update(
		$data,
		$where,
		$data_format = array(),
		$where_format = array()
	) {

		return $this->wpdb->update(
			$this->table,
			$data,
			$where,
			$data_format,
			$where_format
		);
	}

	public function count() {

		return (int) $this->wpdb->get_var(
			"SELECT COUNT(*) FROM {$this->table}"
		);
	}
}
