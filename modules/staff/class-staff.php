<?php
/**
 * Staff management service.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Staff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Staff {

	/**
	 * Create staff member.
	 *
	 * @param array $data Staff data.
	 * @return int|\WP_Error
	 */
	public static function create_staff( $data ) {
		global $wpdb;

		$first_name = isset( $data['first_name'] )
			? sanitize_text_field( $data['first_name'] )
			: '';

		$last_name = isset( $data['last_name'] )
			? sanitize_text_field( $data['last_name'] )
			: '';

		if ( ! $first_name ) {
			return new \WP_Error(
				'invalid_name',
				__( 'First name is required.', 'dispensary-wp' )
			);
		}

		$user_id = isset( $data['user_id'] )
			? absint( $data['user_id'] )
			: 0;

		if ( $user_id && ! get_userdata( $user_id ) ) {
			return new \WP_Error(
				'invalid_user',
				__( 'WordPress user not found.', 'dispensary-wp' )
			);
		}

		if ( $user_id && Staff_Member::find_by_user( $user_id ) ) {
			return new \WP_Error(
				'user_already_staff',
				__( 'This WordPress user is already linked to a staff member.', 'dispensary-wp' )
			);
		}

		$employee_id = isset( $data['employee_id'] )
			? sanitize_text_field( $data['employee_id'] )
			: '';

		$email = isset( $data['email'] )
			? sanitize_email( $data['email'] )
			: '';

		if ( $email && ! is_email( $email ) ) {
			return new \WP_Error(
				'invalid_email',
				__( 'Invalid email address.', 'dispensary-wp' )
			);
		}

		$phone = isset( $data['phone'] )
			? sanitize_text_field( $data['phone'] )
			: '';

		$job_title = isset( $data['job_title'] )
			? sanitize_text_field( $data['job_title'] )
			: '';

		$department = isset( $data['department'] )
			? sanitize_text_field( $data['department'] )
			: '';

		$status = isset( $data['status'] )
			? sanitize_key( $data['status'] )
			: 'active';

		$allowed_statuses = array(
			'active',
			'inactive',
			'suspended',
			'terminated',
		);

		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			$status = 'active';
		}

		$hire_date = isset( $data['hire_date'] )
			? sanitize_text_field( $data['hire_date'] )
			: null;

		$manager_id = isset( $data['manager_id'] )
			? absint( $data['manager_id'] )
			: 0;

		$notes = isset( $data['notes'] )
			? sanitize_textarea_field( $data['notes'] )
			: '';

		$now = current_time( 'mysql', true );

		$table = \Dispensary_WP\Database\Database::table( 'staff_members' );

		$result = $wpdb->insert(
			$table,
			array(
				'user_id'     => $user_id,
				'employee_id' => $employee_id,
				'first_name'  => $first_name,
				'last_name'   => $last_name,
				'email'       => $email,
				'phone'       => $phone,
				'job_title'   => $job_title,
				'department'  => $department,
				'status'      => $status,
				'hire_date'   => $hire_date,
				'manager_id'  => $manager_id,
				'notes'       => $notes,
				'created_by'  => get_current_user_id(),
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'staff_create_failed',
				__( 'Unable to create staff member.', 'dispensary-wp' )
			);
		}

		$id = (int) $wpdb->insert_id;

		if ( class_exists( '\Dispensary_WP\Security\Audit_Log' ) ) {
			\Dispensary_WP\Security\Audit_Log::log(
				'staff_created',
				'staff_member',
				$id,
				array(
					'user_id' => $user_id,
				)
			);
		}

		return $id;
	}

	/**
	 * Get staff member.
	 *
	 * @param int $staff_id Staff ID.
	 * @return object|null
	 */
	public static function get_staff( $staff_id ) {
		return Staff_Member::find( $staff_id );
	}

	/**
	 * List staff.
	 *
	 * @param array $args Arguments.
	 * @return array
	 */
	public static function list_staff( $args = array() ) {
		global $wpdb;

		$table = \Dispensary_WP\Database\Database::table( 'staff_members' );

		$status = isset( $args['status'] )
			? sanitize_key( $args['status'] )
			: '';

		$department = isset( $args['department'] )
			? sanitize_text_field( $args['department'] )
			: '';

		$limit = isset( $args['limit'] )
			? min( 500, max( 1, absint( $args['limit'] ) ) )
			: 100;

		$offset = isset( $args['offset'] )
			? max( 0, absint( $args['offset'] ) )
			: 0;

		$where  = '1=1';
		$params = array();

		if ( $status ) {
			$where   .= ' AND status = %s';
			$params[] = $status;
		}

		if ( $department ) {
			$where   .= ' AND department = %s';
			$params[] = $department;
		}

		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE {$where}
				ORDER BY first_name ASC, last_name ASC
				LIMIT %d OFFSET %d",
				$params
			)
		);
	}

	/**
	 * Update staff.
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $data Data.
	 * @return bool|\WP_Error
	 */
	public static function update_staff( $staff_id, $data ) {
		global $wpdb;

		$staff = Staff_Member::find( $staff_id );

		if ( ! $staff ) {
			return new \WP_Error(
				'staff_not_found',
				__( 'Staff member not found.', 'dispensary-wp' )
			);
		}

		$update  = array();
		$formats = array();

		$text_fields = array(
			'employee_id',
			'first_name',
			'last_name',
			'phone',
			'job_title',
			'department',
		);

		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$update[ $field ] = sanitize_text_field( $data[ $field ] );
				$formats[]        = '%s';
			}
		}

		if ( isset( $data['email'] ) ) {
			$email = sanitize_email( $data['email'] );

			if ( $email && ! is_email( $email ) ) {
				return new \WP_Error(
					'invalid_email',
					__( 'Invalid email address.', 'dispensary-wp' )
				);
			}

			$update['email'] = $email;
			$formats[]       = '%s';
		}

		if ( isset( $data['status'] ) ) {
			$status = sanitize_key( $data['status'] );

			if (
				! in_array(
					$status,
					array(
						'active',
						'inactive',
						'suspended',
						'terminated',
					),
					true
				)
			) {
				return new \WP_Error(
					'invalid_status',
					__( 'Invalid staff status.', 'dispensary-wp' )
				);
			}

			$update['status'] = $status;
			$formats[]        = '%s';
		}

		if ( isset( $data['hire_date'] ) ) {
			$update['hire_date'] = sanitize_text_field( $data['hire_date'] );
			$formats[]           = '%s';
		}

		if ( isset( $data['manager_id'] ) ) {
			$update['manager_id'] = absint( $data['manager_id'] );
			$formats[]            = '%d';
		}

		if ( isset( $data['notes'] ) ) {
			$update['notes'] = sanitize_textarea_field( $data['notes'] );
			$formats[]       = '%s';
		}

		if ( isset( $data['user_id'] ) ) {
			$user_id = absint( $data['user_id'] );

			if ( $user_id && ! get_userdata( $user_id ) ) {
				return new \WP_Error(
					'invalid_user',
					__( 'WordPress user not found.', 'dispensary-wp' )
				);
			}

			$existing = $user_id ? Staff_Member::find_by_user( $user_id ) : null;

			if ( $existing && (int) $existing->id !== (int) $staff_id ) {
				return new \WP_Error(
					'user_already_staff',
					__( 'This WordPress user is already linked to another staff member.', 'dispensary-wp' )
				);
			}

			$update['user_id'] = $user_id;
			$formats[]        = '%d';
		}

		$update['updated_at'] = current_time( 'mysql', true );
		$formats[]            = '%s';

		if ( count( $update ) === 1 ) {
			return true;
		}

		$table = \Dispensary_WP\Database\Database::table( 'staff_members' );

		$result = $wpdb->update(
			$table,
			$update,
			array( 'id' => absint( $staff_id ) ),
			$formats,
			array( '%d' )
		);

		if ( false === $result ) {
			return new \WP_Error(
				'staff_update_failed',
				__( 'Unable to update staff member.', 'dispensary-wp' )
			);
		}

		if ( class_exists( '\Dispensary_WP\Security\Audit_Log' ) ) {
			\Dispensary_WP\Security\Audit_Log::log(
				'staff_updated',
				'staff_member',
				(int) $staff_id
			);
		}

		return true;
	}

	/**
	 * Delete staff member.
	 *
	 * @param int $staff_id Staff ID.
	 * @return bool|\WP_Error
	 */
	public static function delete_staff( $staff_id ) {
		global $wpdb;

		$staff_id = absint( $staff_id );

		if ( ! Staff_Member::find( $staff_id ) ) {
			return new \WP_Error(
				'staff_not_found',
				__( 'Staff member not found.', 'dispensary-wp' )
			);
		}

		$table = \Dispensary_WP\Database\Database::table( 'staff_members' );

		$result = $wpdb->delete(
			$table,
			array( 'id' => $staff_id ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new \WP_Error(
				'staff_delete_failed',
				__( 'Unable to delete staff member.', 'dispensary-wp' )
			);
		}

		if ( class_exists( '\Dispensary_WP\Security\Audit_Log' ) ) {
			\Dispensary_WP\Security\Audit_Log::log(
				'staff_deleted',
				'staff_member',
				$staff_id
			);
		}

		return true;
	}

	/**
	 * Create shift.
	 *
	 * @param array $data Shift data.
	 * @return int|\WP_Error
	 */
	public static function create_shift( $data ) {
		return Staff_Shift::create( $data );
	}

	/**
	 * Get shift.
	 *
	 * @param int $shift_id Shift ID.
	 * @return object|null
	 */
	public static function get_shift( $shift_id ) {
		return Staff_Shift::find( $shift_id );
	}

	/**
	 * List shifts.
	 *
	 * @param array $args Arguments.
	 * @return array
	 */
	public static function list_shifts( $args = array() ) {
		return Staff_Shift::all( $args );
	}

	/**
	 * Update shift.
	 *
	 * @param int   $shift_id Shift ID.
	 * @param array $data Data.
	 * @return bool|\WP_Error
	 */
	public static function update_shift( $shift_id, $data ) {
		return Staff_Shift::update( $shift_id, $data );
	}

	/**
	 * Clock in.
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $data Data.
	 * @return int|\WP_Error
	 */
	public static function clock_in( $staff_id, $data = array() ) {
		return Staff_Attendance::clock_in( $staff_id, $data );
	}

	/**
	 * Clock out.
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $data Data.
	 * @return bool|\WP_Error
	 */
	public static function clock_out( $staff_id, $data = array() ) {
		return Staff_Attendance::clock_out( $staff_id, $data );
	}

	/**
	 * Get attendance.
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $date Date.
	 * @return object|null
	 */
	public static function get_attendance( $staff_id, $date = '' ) {
		return Staff_Attendance::get( $staff_id, $date );
	}

	/**
	 * List attendance.
	 *
	 * @param array $args Arguments.
	 * @return array
	 */
	public static function list_attendance( $args = array() ) {
		return Staff_Attendance::all( $args );
	}
}
