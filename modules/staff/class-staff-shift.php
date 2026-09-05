<?php
/**
 * Staff shift management.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Staff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Staff_Shift {

	/**
	 * Allowed shift statuses.
	 *
	 * @return array
	 */
	public static function statuses() {
		return array(
			'scheduled',
			'started',
			'completed',
			'cancelled',
		);
	}

	/**
	 * Create shift.
	 *
	 * @param array $data Shift data.
	 * @return int|\WP_Error
	 */
	public static function create( $data ) {
		global $wpdb;

		$staff_id = isset( $data['staff_id'] ) ? absint( $data['staff_id'] ) : 0;

		if ( ! $staff_id ) {
			return new \WP_Error(
				'invalid_staff',
				__( 'Valid staff member is required.', 'dispensary-wp' )
			);
		}

		if ( ! Staff_Member::find( $staff_id ) ) {
			return new \WP_Error(
				'staff_not_found',
				__( 'Staff member not found.', 'dispensary-wp' )
			);
		}

		$shift_date = isset( $data['shift_date'] )
			? sanitize_text_field( $data['shift_date'] )
			: '';

		$start_time = isset( $data['start_time'] )
			? sanitize_text_field( $data['start_time'] )
			: '';

		$end_time = isset( $data['end_time'] )
			? sanitize_text_field( $data['end_time'] )
			: '';

		if ( ! $shift_date || ! $start_time || ! $end_time ) {
			return new \WP_Error(
				'invalid_shift',
				__( 'Shift date, start time and end time are required.', 'dispensary-wp' )
			);
		}

		$status = isset( $data['status'] )
			? sanitize_key( $data['status'] )
			: 'scheduled';

		if ( ! in_array( $status, self::statuses(), true ) ) {
			return new \WP_Error(
				'invalid_status',
				__( 'Invalid shift status.', 'dispensary-wp' )
			);
		}

		$break_minutes = isset( $data['break_minutes'] )
			? absint( $data['break_minutes'] )
			: 0;

		$notes = isset( $data['notes'] )
			? sanitize_textarea_field( $data['notes'] )
			: '';

		$created_by = get_current_user_id();
		$now        = current_time( 'mysql', true );

		$table = \Dispensary_WP\Database\Database::table( 'staff_shifts' );

		$result = $wpdb->insert(
			$table,
			array(
				'staff_id'     => $staff_id,
				'shift_date'   => $shift_date,
				'start_time'   => $start_time,
				'end_time'     => $end_time,
				'break_minutes'=> $break_minutes,
				'status'       => $status,
				'notes'        => $notes,
				'created_by'   => $created_by,
				'created_at'   => $now,
				'updated_at'   => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'shift_create_failed',
				__( 'Unable to create shift.', 'dispensary-wp' )
			);
		}

		$id = (int) $wpdb->insert_id;

		if ( class_exists( '\Dispensary_WP\Security\Audit_Log' ) ) {
			\Dispensary_WP\Security\Audit_Log::log(
				'staff_shift_created',
				'staff_shift',
				$id,
				array(
					'staff_id' => $staff_id,
				)
			);
		}

		return $id;
	}

	/**
	 * Find shift.
	 *
	 * @param int $shift_id Shift ID.
	 * @return object|null
	 */
	public static function find( $shift_id ) {
		global $wpdb;

		$shift_id = absint( $shift_id );

		if ( ! $shift_id ) {
			return null;
		}

		$table = \Dispensary_WP\Database\Database::table( 'staff_shifts' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				$shift_id
			)
		);
	}

	/**
	 * List shifts.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public static function all( $args = array() ) {
		global $wpdb;

		$table = \Dispensary_WP\Database\Database::table( 'staff_shifts' );

		$staff_id = isset( $args['staff_id'] ) ? absint( $args['staff_id'] ) : 0;
		$date     = isset( $args['shift_date'] )
			? sanitize_text_field( $args['shift_date'] )
			: '';

		$limit = isset( $args['limit'] )
			? min( 500, max( 1, absint( $args['limit'] ) ) )
			: 100;

		$where  = '1=1';
		$params = array();

		if ( $staff_id ) {
			$where   .= ' AND staff_id = %d';
			$params[] = $staff_id;
		}

		if ( $date ) {
			$where   .= ' AND shift_date = %s';
			$params[] = $date;
		}

		$params[] = $limit;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE {$where}
				ORDER BY shift_date DESC, start_time ASC
				LIMIT %d",
				$params
			)
		);
	}

	/**
	 * Update shift.
	 *
	 * @param int   $shift_id Shift ID.
	 * @param array $data Data.
	 * @return bool|\WP_Error
	 */
	public static function update( $shift_id, $data ) {
		global $wpdb;

		$shift_id = absint( $shift_id );

		if ( ! self::find( $shift_id ) ) {
			return new \WP_Error(
				'shift_not_found',
				__( 'Shift not found.', 'dispensary-wp' )
			);
		}

		$update = array();
		$formats = array();

		if ( isset( $data['shift_date'] ) ) {
			$update['shift_date'] = sanitize_text_field( $data['shift_date'] );
			$formats[]            = '%s';
		}

		if ( isset( $data['start_time'] ) ) {
			$update['start_time'] = sanitize_text_field( $data['start_time'] );
			$formats[]            = '%s';
		}

		if ( isset( $data['end_time'] ) ) {
			$update['end_time'] = sanitize_text_field( $data['end_time'] );
			$formats[]          = '%s';
		}

		if ( isset( $data['break_minutes'] ) ) {
			$update['break_minutes'] = absint( $data['break_minutes'] );
			$formats[]               = '%d';
		}

		if ( isset( $data['status'] ) ) {
			$status = sanitize_key( $data['status'] );

			if ( ! in_array( $status, self::statuses(), true ) ) {
				return new \WP_Error(
					'invalid_status',
					__( 'Invalid shift status.', 'dispensary-wp' )
				);
			}

			$update['status'] = $status;
			$formats[]        = '%s';
		}

		if ( isset( $data['notes'] ) ) {
			$update['notes'] = sanitize_textarea_field( $data['notes'] );
			$formats[]       = '%s';
		}

		$update['updated_at'] = current_time( 'mysql', true );
		$formats[]            = '%s';

		if ( count( $update ) === 1 ) {
			return true;
		}

		$table = \Dispensary_WP\Database\Database::table( 'staff_shifts' );

		$result = $wpdb->update(
			$table,
			$update,
			array( 'id' => $shift_id ),
			$formats,
			array( '%d' )
		);

		if ( false === $result ) {
			return new \WP_Error(
				'shift_update_failed',
				__( 'Unable to update shift.', 'dispensary-wp' )
			);
		}

		return true;
	}
}
