<?php
/**
 * Staff controller/service facade.
 *
 * REST endpoints will be added in Module 15.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Staff;

use Dispensary_WP\Permissions\Permissions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Staff_Controller {

	/**
	 * Staff service.
	 *
	 * @return Staff
	 */
	public static function service() {
		return new Staff();
	}

	/**
	 * Create staff.
	 *
	 * @param array $data Staff data.
	 * @return int|\WP_Error
	 */
	public static function create_staff( $data ) {

		if ( ! Permissions::can_manage_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage staff.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::create_staff( $data );
	}

	/**
	 * Get staff.
	 *
	 * @param int $id Staff ID.
	 * @return object|\WP_Error
	 */
	public static function get_staff( $id ) {

		if ( ! Permissions::can_view_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to view staff.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		$staff = Staff::get_staff( $id );

		if ( ! $staff ) {
			return new \WP_Error(
				'staff_not_found',
				__( 'Staff member not found.', 'dispensary-wp' ),
				array( 'status' => 404 )
			);
		}

		return $staff;
	}

	/**
	 * List staff.
	 *
	 * @param array $args Arguments.
	 * @return array|\WP_Error
	 */
	public static function list_staff( $args = array() ) {

		if ( ! Permissions::can_view_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to view staff.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::list_staff( $args );
	}

	/**
	 * Update staff.
	 *
	 * @param int   $id Staff ID.
	 * @param array $data Data.
	 * @return bool|\WP_Error
	 */
	public static function update_staff( $id, $data ) {

		if ( ! Permissions::can_manage_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage staff.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::update_staff( $id, $data );
	}

	/**
	 * Delete staff.
	 *
	 * @param int $id Staff ID.
	 * @return bool|\WP_Error
	 */
	public static function delete_staff( $id ) {

		if ( ! Permissions::can_manage_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage staff.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::delete_staff( $id );
	}

	/**
	 * Create shift.
	 *
	 * @param array $data Shift data.
	 * @return int|\WP_Error
	 */
	public static function create_shift( $data ) {

		if ( ! Permissions::can_manage_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage staff shifts.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::create_shift( $data );
	}

	/**
	 * List shifts.
	 *
	 * @param array $args Arguments.
	 * @return array|\WP_Error
	 */
	public static function list_shifts( $args = array() ) {

		if ( ! Permissions::can_view_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to view staff shifts.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::list_shifts( $args );
	}

	/**
	 * Update shift.
	 *
	 * @param int   $id   Shift ID.
	 * @param array $data Data.
	 * @return bool|\WP_Error
	 */
	public static function update_shift( $id, $data ) {

		if ( ! Permissions::can_manage_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage staff shifts.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::update_shift( $id, $data );
	}

	/**
	 * Clock in.
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $data Data.
	 * @return int|\WP_Error
	 */
	public static function clock_in( $staff_id, $data = array() ) {

		if ( ! Permissions::can_manage_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage attendance.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::clock_in( $staff_id, $data );
	}

	/**
	 * Clock out.
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $data Data.
	 * @return bool|\WP_Error
	 */
	public static function clock_out( $staff_id, $data = array() ) {

		if ( ! Permissions::can_manage_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to manage attendance.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::clock_out( $staff_id, $data );
	}

	/**
	 * Get attendance.
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $date Date.
	 * @return object|\WP_Error
	 */
	public static function get_attendance( $staff_id, $date = '' ) {

		if ( ! Permissions::can_view_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to view attendance.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::get_attendance( $staff_id, $date );
	}

	/**
	 * List attendance.
	 *
	 * @param array $args Arguments.
	 * @return array|\WP_Error
	 */
	public static function list_attendance( $args = array() ) {

		if ( ! Permissions::can_view_staff() ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to view attendance.', 'dispensary-wp' ),
				array( 'status' => 403 )
			);
		}

		return Staff::list_attendance( $args );
	}
}
