<?php
/**
 * Staff Member model.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Modules\Staff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Staff_Member {

	/**
	 * Get staff member.
	 *
	 * @param int $staff_id Staff ID.
	 * @return object|null
	 */
	public static function find( $staff_id ) {
		global $wpdb;

		$staff_id = absint( $staff_id );

		if ( ! $staff_id ) {
			return null;
		}

		$table = \Dispensary_WP\Database\Database::table( 'staff_members' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				$staff_id
			)
		);
	}

	/**
	 * Find staff member by WordPress user ID.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return object|null
	 */
	public static function find_by_user( $user_id ) {
		global $wpdb;

		$user_id = absint( $user_id );

		if ( ! $user_id ) {
			return null;
		}

		$table = \Dispensary_WP\Database\Database::table( 'staff_members' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE user_id = %d LIMIT 1",
				$user_id
			)
		);
	}

	/**
	 * Get linked WordPress user.
	 *
	 * @param object|int $staff Staff object or ID.
	 * @return \WP_User|false
	 */
	public static function get_wp_user( $staff ) {

		if ( is_object( $staff ) && isset( $staff->user_id ) ) {
			$user_id = absint( $staff->user_id );
		} else {
			$staff = self::find( $staff );
			$user_id = $staff ? absint( $staff->user_id ) : 0;
		}

		if ( ! $user_id ) {
			return false;
		}

		return get_userdata( $user_id );
	}

	/**
	 * Get display name.
	 *
	 * @param object|int $staff Staff object or ID.
	 * @return string
	 */
	public static function display_name( $staff ) {

		if ( ! is_object( $staff ) ) {
			$staff = self::find( $staff );
		}

		if ( ! $staff ) {
			return '';
		}

		return trim(
			( isset( $staff->first_name ) ? $staff->first_name : '' ) .
			' ' .
			( isset( $staff->last_name ) ? $staff->last_name : '' )
		);
	}
}
