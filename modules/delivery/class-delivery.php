<?php
/**
 * Delivery management service.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Delivery;

use Dispensary_WP\Permissions\Permissions;
use Dispensary_WP\Security\Audit_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Delivery {

	protected $driver;
	protected $zone;
	protected $delivery_order;
	protected $route;
	protected $proof;

	public function __construct() {

		$this->driver         = new Driver();
		$this->zone           = new Delivery_Zone();
		$this->delivery_order = new Delivery_Order();
		$this->route          = new Delivery_Route();
		$this->proof          = new Proof_Of_Delivery();
	}

	/**
	 * Create delivery.
	 */
	public function create( $data ) {

		if ( ! Permissions::can_manage_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to manage deliveries.', 'dispensary-wp' )
			);
		}

		$order_id = absint( $data['order_id'] ?? 0 );

		if ( ! $order_id ) {
			return new \WP_Error(
				'invalid_order',
				__( 'A valid order is required.', 'dispensary-wp' )
			);
		}

		$existing = $this->delivery_order->find_by_order( $order_id );

		if ( $existing ) {
			return new \WP_Error(
				'delivery_exists',
				__( 'A delivery already exists for this order.', 'dispensary-wp' )
			);
		}

		$now = current_time( 'mysql', true );

		$delivery_id = $this->delivery_order->create(
			array(
				'order_id'          => $order_id,
				'driver_id'         => absint( $data['driver_id'] ?? 0 ),
				'zone_id'           => absint( $data['zone_id'] ?? 0 ),
				'route_id'          => absint( $data['route_id'] ?? 0 ),
				'status'            => 'pending',
				'address_line_1'    => sanitize_text_field( $data['address_line_1'] ?? '' ),
				'address_line_2'    => sanitize_text_field( $data['address_line_2'] ?? '' ),
				'city'              => sanitize_text_field( $data['city'] ?? '' ),
				'state'             => sanitize_text_field( $data['state'] ?? '' ),
				'postal_code'       => sanitize_text_field( $data['postal_code'] ?? '' ),
				'country'           => sanitize_text_field( $data['country'] ?? '' ),
				'latitude'          => isset( $data['latitude'] ) ? (float) $data['latitude'] : 0,
				'longitude'         => isset( $data['longitude'] ) ? (float) $data['longitude'] : 0,
				'delivery_note'     => sanitize_textarea_field( $data['delivery_note'] ?? '' ),
				'scheduled_at'      => ! empty( $data['scheduled_at'] )
					? sanitize_text_field( $data['scheduled_at'] )
					: null,
				'created_by'        => get_current_user_id(),
				'created_at'        => $now,
				'updated_at'        => $now,
			)
		);

		if ( ! $delivery_id ) {
			return new \WP_Error(
				'delivery_create_failed',
				__( 'Unable to create delivery.', 'dispensary-wp' )
			);
		}

		Audit_Log::log(
			'delivery_created',
			'delivery',
			$delivery_id,
			array(
				'order_id' => $order_id,
			)
		);

		return $delivery_id;
	}

	/**
	 * Get delivery.
	 */
	public function get( $delivery_id ) {

		if ( ! Permissions::can_view_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view deliveries.', 'dispensary-wp' )
			);
		}

		$delivery = $this->delivery_order->find( $delivery_id );

		if ( ! $delivery ) {
			return new \WP_Error(
				'delivery_not_found',
				__( 'Delivery not found.', 'dispensary-wp' )
			);
		}

		return array(
			'delivery' => $delivery,
			'driver'   => $delivery->driver_id
				? $this->driver->find( $delivery->driver_id )
				: null,
			'zone'     => $delivery->zone_id
				? $this->zone->find( $delivery->zone_id )
				: null,
			'route'    => $delivery->route_id
				? $this->route->find( $delivery->route_id )
				: null,
			'proof'    => $this->proof->find_by_delivery( $delivery_id ),
		);
	}

	/**
	 * List deliveries.
	 */
	public function list_deliveries( $args = array() ) {

		if ( ! Permissions::can_view_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view deliveries.', 'dispensary-wp' )
			);
		}

		return $this->delivery_order->all( $args );
	}

	/**
	 * Assign driver.
	 */
	public function assign_driver( $delivery_id, $driver_id ) {

		if ( ! Permissions::can_manage_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to assign drivers.', 'dispensary-wp' )
			);
		}

		$driver = $this->driver->find( $driver_id );

		if ( ! $driver ) {
			return new \WP_Error(
				'driver_not_found',
				__( 'Driver not found.', 'dispensary-wp' )
			);
		}

		return $this->delivery_order->update(
			$delivery_id,
			array(
				'driver_id'  => absint( $driver_id ),
				'updated_at' => current_time( 'mysql', true ),
			)
		);
	}

	/**
	 * Update delivery status.
	 */
	public function update_status( $delivery_id, $status, $note = '' ) {

		if ( ! Permissions::can_manage_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to update deliveries.', 'dispensary-wp' )
			);
		}

		$allowed = array(
			'pending',
			'assigned',
			'ready',
			'out_for_delivery',
			'delivered',
			'failed',
			'cancelled',
		);

		$status = sanitize_key( $status );

		if ( ! in_array( $status, $allowed, true ) ) {
			return new \WP_Error(
				'invalid_status',
				__( 'Invalid delivery status.', 'dispensary-wp' )
			);
		}

		$delivery = $this->delivery_order->find( $delivery_id );

		if ( ! $delivery ) {
			return new \WP_Error(
				'delivery_not_found',
				__( 'Delivery not found.', 'dispensary-wp' )
			);
		}

		$updated = $this->delivery_order->update(
			$delivery_id,
			array(
				'status'     => $status,
				'status_note' => sanitize_textarea_field( $note ),
				'updated_at' => current_time( 'mysql', true ),
			)
		);

		if ( $updated ) {
			Audit_Log::log(
				'delivery_status_updated',
				'delivery',
				$delivery_id,
				array(
					'old_status' => $delivery->status,
					'new_status' => $status,
					'note'       => $note,
				)
			);
		}

		return $updated;
	}

	/**
	 * Add proof of delivery.
	 */
	public function add_proof( $delivery_id, $data ) {

		if ( ! Permissions::can_manage_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to add proof of delivery.', 'dispensary-wp' )
			);
		}

		$delivery = $this->delivery_order->find( $delivery_id );

		if ( ! $delivery ) {
			return new \WP_Error(
				'delivery_not_found',
				__( 'Delivery not found.', 'dispensary-wp' )
			);
		}

		$proof_id = $this->proof->create(
			array(
				'delivery_id' => $delivery_id,
				'type'        => sanitize_key( $data['type'] ?? 'note' ),
				'file_url'    => esc_url_raw( $data['file_url'] ?? '' ),
				'signature'   => sanitize_text_field( $data['signature'] ?? '' ),
				'recipient_name' => sanitize_text_field(
					$data['recipient_name'] ?? ''
				),
				'notes'       => sanitize_textarea_field(
					$data['notes'] ?? ''
				),
				'created_by'  => get_current_user_id(),
				'created_at'  => current_time( 'mysql', true ),
			)
		);

		if ( $proof_id ) {
			$this->delivery_order->update(
				$delivery_id,
				array(
					'status'     => 'delivered',
					'updated_at' => current_time( 'mysql', true ),
				)
			);

			Audit_Log::log(
				'proof_of_delivery_added',
				'delivery',
				$delivery_id,
				array(
					'proof_id' => $proof_id,
				)
			);
		}

		return $proof_id
			? $proof_id
			: new \WP_Error(
				'proof_failed',
				__( 'Unable to save proof of delivery.', 'dispensary-wp' )
			);
	}

	/**
	 * Create driver.
	 */
	public function create_driver( $data ) {

		if ( ! Permissions::can_manage_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to manage drivers.', 'dispensary-wp' )
			);
		}

		$now = current_time( 'mysql', true );

		return $this->driver->create(
			array(
				'user_id'     => absint( $data['user_id'] ?? 0 ),
				'name'        => sanitize_text_field( $data['name'] ?? '' ),
				'phone'       => sanitize_text_field( $data['phone'] ?? '' ),
				'vehicle_type' => sanitize_text_field( $data['vehicle_type'] ?? '' ),
				'vehicle_number' => sanitize_text_field(
					$data['vehicle_number'] ?? ''
				),
				'status'      => 'active',
				'created_at'  => $now,
				'updated_at'  => $now,
			)
		);
	}

	/**
	 * Get drivers.
	 */
	public function drivers() {

		if ( ! Permissions::can_view_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view drivers.', 'dispensary-wp' )
			);
		}

		return $this->driver->all();
	}

	/**
	 * Get zones.
	 */
	public function zones() {

		if ( ! Permissions::can_view_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view delivery zones.', 'dispensary-wp' )
			);
		}

		return $this->zone->all();
	}

	/**
	 * Get routes.
	 */
	public function routes() {

		if ( ! Permissions::can_view_delivery() ) {
			return new \WP_Error(
				'permission_denied',
				__( 'You do not have permission to view delivery routes.', 'dispensary-wp' )
			);
		}

		return $this->route->all();
	}
}
