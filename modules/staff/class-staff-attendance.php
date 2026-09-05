<?php
/**
 * Staff attendance management.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Staff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Staff_Attendance {

	/**
	 * Attendance statuses.
	 *
	 * @return array
	 */
	public static function statuses() {
		return array(
			'present',
			'late',
			'absent',
			'leave',
			'half_day',
		);
	}

	/**
	 * Clock in.
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $data Attendance data.
	 * @return int|\WP_Error
	 */
	public static function clock_in( $staff_id, $data = array() ) {
		global $wpdb;

		$staff_id = absint( $staff_id );

		if ( ! Staff_Member::find( $staff_id ) ) {
			return new \WP_Error(
				'staff_not_found',
				__( 'Staff member not found.', 'dispensary-wp' )
			);
		}

		$date = current_time( 'Y-m-d' );
		$now  = current_time( 'mysql', true );

		$existing = self::get_by_date( $staff_id, $date );

		if ( $existing && ! empty( $existing->clock_in ) ) {
			return new \WP_Error(
				'already_clocked_in',
				__( 'Staff member has already clocked in today.', 'dispensary-wp' )
			);
		}

		$status = isset( $data['status'] )
			? sanitize_key( $data['status'] )
			: 'present';

		if ( ! in_array( $status, self::statuses(), true ) ) {
			$status = 'present';
		}

		$latitude  = isset( $data['latitude'] ) ? (float) $data['latitude'] : 0;
		$longitude = isset( $data['longitude'] ) ? (float) $data['longitude'] : 0;
		$notes     = isset( $data['notes'] )
			? sanitize_textarea_field( $data['notes'] )
			: '';

		$table = \Dispensary_WP\Database\Database::table( 'staff_attendance' );

		if ( $existing ) {

			$result = $wpdb->update(
				$table,
				array(
					'clock_in'          => $now,
					'status'             => $status,
					'clock_in_latitude' => $latitude,
					'clock_in_longitude'=> $longitude,
					'notes'              => $notes,
					'updated_at'         => $now,
				),
				array( 'id' => (int) $existing->id ),
				array(
					'%s',
					'%s',
					'%f',
					'%f',
					'%s',
					'%s',
				),
				array( '%d' )
			);

			if ( false === $result ) {
				return new \WP_Error(
					'clock_in_failed',
					__( 'Unable to clock in.', 'dispensary-wp' )
				);
			}

			return (int) $existing->id;
		}

		$result = $wpdb->insert(
			$table,
			array(
				'staff_id'           => $staff_id,
				'attendance_date'    => $date,
				'clock_in'           => $now,
				'clock_out'          => null,
				'status'             => $status,
				'clock_in_latitude'  => $latitude,
				'clock_in_longitude' => $longitude,
				'clock_out_latitude' => 0,
				'clock_out_longitude'=> 0,
				'notes'              => $notes,
				'created_by'         => get_current_user_id(),
				'created_at'         => $now,
				'updated_at'         => $now,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%f',
				'%f',
				'%f',
				'%f',
				'%s',
				'%d',
				'%s',
				'%s',
			)
		);

		if ( false === $result ) {
			return new \WP_Error(
				'clock_in_failed',
				__( 'Unable to clock in.', 'dispensary-wp' )
			);
		}

		$id = (int) $wpdb->insert_id;

		if ( class_exists( '\Dispensary_WP\Security\Audit_Log' ) ) {
			\Dispensary_WP\Security\Audit_Log::log(
				'staff_clock_in',
				'staff_attendance',
				$id,
				array(
					'staff_id' => $staff_id,
				)
			);
		}

		return $id;
	}

	/**
	 * Clock out.
	 *
	 * @param int   $staff_id Staff ID.
	 * @param array $data Attendance data.
	 * @return bool|\WP_Error
	 */
	public static function clock_out( $staff_id, $data = array() ) {
		global $wpdb;

		$staff_id = absint( $staff_id );

		$date = current_time( 'Y-m-d' );

		$attendance = self::get_by_date( $staff_id, $date );

		if ( ! $attendance ) {
			return new \WP_Error(
				'attendance_not_found',
				__( 'No attendance record found for today.', 'dispensary-wp' )
			);
		}

		if ( ! empty( $attendance->clock_out ) ) {
			return new \WP_Error(
				'already_clocked_out',
				__( 'Staff member has already clocked out today.', 'dispensary-wp' )
			);
		}

		$now       = current_time( 'mysql', true );
		$latitude  = isset( $data['latitude'] ) ? (float) $data['latitude'] : 0;
		$longitude = isset( $data['longitude'] ) ? (float) $data['longitude'] : 0;

		$table = \Dispensary_WP\Database\Database::table( 'staff_attendance' );

		$result = $wpdb->update(
			$table,
			array(
				'clock_out'           => $now,
				'clock_out_latitude'  => $latitude,
				'clock_out_longitude' => $longitude,
				'updated_at'          => $now,
			),
			array( 'id' => (int) $attendance->id ),
			array(
				'%s',
				'%f',
				'%f',
				'%s',
			),
			array( '%d' )
		);

		if ( false === $result ) {
			return new \WP_Error(
				'clock_out_failed',
				__( 'Unable to clock out.', 'dispensary-wp' )
			);
		}

		if ( class_exists( '\Dispensary_WP\Security\Audit_Log' ) ) {
			\Dispensary_WP\Security\Audit_Log::log(
				'staff_clock_out',
				'staff_attendance',
				(int) $attendance->id,
				array(
					'staff_id' => $staff_id,
				)
			);
		}

		return true;
	}

	/**
	 * Get attendance by date.
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $date Date.
	 * @return object|null
	 */
	public static function get_by_date( $staff_id, $date ) {
		global $wpdb;

		$table = \Dispensary_WP\Database\Database::table( 'staff_attendance' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE staff_id = %d
				AND attendance_date = %s
				LIMIT 1",
				absint( $staff_id ),
				sanitize_text_field( $date )
			)
		);
	}

	/**
	 * Get attendance.
	 *
	 * @param int    $staff_id Staff ID.
	 * @param string $date Date.
	 * @return object|null
	 */
	public static function get( $staff_id, $date = '' ) {

		if ( ! $date ) {
			$date = current_time( 'Y-m-d' );
		}

		return self::get_by_date( $staff_id, $date );
	}

	/**
	 * List attendance.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public static function all( $args = array() ) {
		global $wpdb;

		$table = \Dispensary_WP\Database\Database::table( 'staff_attendance' );

		$staff_id = isset( $args['staff_id'] )
			? absint( $args['staff_id'] )
			: 0;

		$date = isset( $args['attendance_date'] )
			? sanitize_text_field( $args['attendance_date'] )
			: '';

		$limit = isset( $args['limit'] )
			? min( 1000, max( 1, absint( $args['limit'] ) ) )
			: 100;

		$where  = '1=1';
		$params = array();

		if ( $staff_id ) {
			$where   .= ' AND staff_id = %d';
			$params[] = $staff_id;
		}

		if ( $date ) {
			$where   .= ' AND attendance_date = %s';
			$params[] = $date;
		}

		$params[] = $limit;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE {$where}
				ORDER BY attendance_date DESC, clock_in DESC
				LIMIT %d",
				$params
			)
		);
	}
}
