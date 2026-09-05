<?php
/**
 * Delivery controller.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Delivery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delivery_Controller {

	protected $delivery;

	public function __construct() {
		$this->delivery = new Delivery();
	}

	public function service() {
		return $this->delivery;
	}

	public function create( $data ) {
		return $this->delivery->create( $data );
	}

	public function get( $id ) {
		return $this->delivery->get( $id );
	}

	public function list_deliveries( $args = array() ) {
		return $this->delivery->list_deliveries( $args );
	}

	public function assign_driver( $delivery_id, $driver_id ) {
		return $this->delivery->assign_driver(
			$delivery_id,
			$driver_id
		);
	}

	public function update_status( $delivery_id, $status, $note = '' ) {
		return $this->delivery->update_status(
			$delivery_id,
			$status,
			$note
		);
	}

	public function add_proof( $delivery_id, $data ) {
		return $this->delivery->add_proof(
			$delivery_id,
			$data
		);
	}

	public function create_driver( $data ) {
		return $this->delivery->create_driver( $data );
	}

	public function drivers() {
		return $this->delivery->drivers();
	}

	public function zones() {
		return $this->delivery->zones();
	}

	public function routes() {
		return $this->delivery->routes();
	}
}
