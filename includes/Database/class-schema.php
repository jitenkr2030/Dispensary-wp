<?php
/**
 * Database schema.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Schema {

	/**
	 * Install all plugin tables.
	 *
	 * @return void
	 */
	public static function install() {

		self::create_core_tables();
		self::create_inventory_tables();
		self::create_customer_tables();
		self::create_order_tables();
		self::create_pos_tables();
		self::create_delivery_tables();
		self::create_staff_tables();
		self::create_loyalty_tables();
	}

	/**
	 * Create core tables.
	 *
	 * @return void
	 */
	public static function create_core_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$settings = Database::table( 'settings' );
		$audit    = Database::table( 'audit_logs' );

		$sql = array();

		$sql[] = "CREATE TABLE {$settings} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			setting_key varchar(191) NOT NULL,
			setting_value longtext NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY setting_key (setting_key)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$audit} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			action varchar(191) NOT NULL,
			object_type varchar(100) NOT NULL DEFAULT '',
			object_id bigint(20) unsigned NOT NULL DEFAULT 0,
			ip_address varchar(100) NOT NULL DEFAULT '',
			user_agent text NULL,
			data longtext NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY action (action),
			KEY object_type (object_type),
			KEY object_id (object_id),
			KEY created_at (created_at)
		) {$charset_collate};";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}

	/**
	 * Create inventory tables.
	 *
	 * @return void
	 */
	public static function create_inventory_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$stock_movements = Database::table( 'stock_movements' );
		$batches         = Database::table( 'batches' );
		$lots            = Database::table( 'lots' );
		$suppliers       = Database::table( 'suppliers' );
		$purchases       = Database::table( 'purchases' );
		$purchase_items  = Database::table( 'purchase_items' );

		$sql = array();

		$sql[] = "CREATE TABLE {$stock_movements} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			product_id bigint(20) unsigned NOT NULL,
			variant_id bigint(20) unsigned NOT NULL DEFAULT 0,
			batch_id bigint(20) unsigned NOT NULL DEFAULT 0,
			lot_id bigint(20) unsigned NOT NULL DEFAULT 0,
			type varchar(50) NOT NULL,
			quantity decimal(18,4) NOT NULL DEFAULT 0,
			reference_type varchar(100) NOT NULL DEFAULT '',
			reference_id bigint(20) unsigned NOT NULL DEFAULT 0,
			note text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY product_id (product_id),
			KEY variant_id (variant_id),
			KEY batch_id (batch_id),
			KEY lot_id (lot_id),
			KEY type (type),
			KEY reference_id (reference_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$batches} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			product_id bigint(20) unsigned NOT NULL,
			variant_id bigint(20) unsigned NOT NULL DEFAULT 0,
			batch_number varchar(100) NOT NULL,
			expiry_date date NULL,
			manufacturing_date date NULL,
			quantity decimal(18,4) NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'active',
			notes text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY product_id (product_id),
			KEY batch_number (batch_number),
			KEY expiry_date (expiry_date),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$lots} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			product_id bigint(20) unsigned NOT NULL,
			batch_id bigint(20) unsigned NOT NULL DEFAULT 0,
			lot_number varchar(100) NOT NULL,
			quantity decimal(18,4) NOT NULL DEFAULT 0,
			expiry_date date NULL,
			status varchar(30) NOT NULL DEFAULT 'active',
			notes text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY product_id (product_id),
			KEY batch_id (batch_id),
			KEY lot_number (lot_number),
			KEY expiry_date (expiry_date)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$suppliers} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL,
			email varchar(191) NOT NULL DEFAULT '',
			phone varchar(50) NOT NULL DEFAULT '',
			address text NULL,
			status varchar(30) NOT NULL DEFAULT 'active',
			notes text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY name (name),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$purchases} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			supplier_id bigint(20) unsigned NOT NULL DEFAULT 0,
			purchase_number varchar(100) NOT NULL,
			status varchar(30) NOT NULL DEFAULT 'draft',
			subtotal decimal(18,2) NOT NULL DEFAULT 0,
			tax_total decimal(18,2) NOT NULL DEFAULT 0,
			total decimal(18,2) NOT NULL DEFAULT 0,
			notes text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY purchase_number (purchase_number),
			KEY supplier_id (supplier_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$purchase_items} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			purchase_id bigint(20) unsigned NOT NULL,
			product_id bigint(20) unsigned NOT NULL,
			variant_id bigint(20) unsigned NOT NULL DEFAULT 0,
			quantity decimal(18,4) NOT NULL DEFAULT 0,
			unit_cost decimal(18,2) NOT NULL DEFAULT 0,
			total decimal(18,2) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY purchase_id (purchase_id),
			KEY product_id (product_id)
		) {$charset_collate};";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}

	/**
	 * Create customer tables.
	 *
	 * @return void
	 */
	public static function create_customer_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$customers      = Database::table( 'customers' );
		$profiles       = Database::table( 'customer_profiles' );
		$verifications  = Database::table( 'customer_verifications' );
		$history        = Database::table( 'customer_history' );

		$sql = array();

		$sql[] = "CREATE TABLE {$customers} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			first_name varchar(100) NOT NULL DEFAULT '',
			last_name varchar(100) NOT NULL DEFAULT '',
			email varchar(191) NOT NULL DEFAULT '',
			phone varchar(50) NOT NULL DEFAULT '',
			status varchar(30) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY email (email),
			KEY phone (phone),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$profiles} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned NOT NULL,
			date_of_birth date NULL,
			address text NULL,
			city varchar(100) NOT NULL DEFAULT '',
			state varchar(100) NOT NULL DEFAULT '',
			country varchar(100) NOT NULL DEFAULT '',
			postal_code varchar(30) NOT NULL DEFAULT '',
			emergency_contact varchar(191) NOT NULL DEFAULT '',
			notes text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY customer_id (customer_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$verifications} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned NOT NULL,
			type varchar(50) NOT NULL,
			status varchar(30) NOT NULL DEFAULT 'pending',
			verified_by bigint(20) unsigned NOT NULL DEFAULT 0,
			notes text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY customer_id (customer_id),
			KEY type (type),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$history} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned NOT NULL,
			action varchar(100) NOT NULL,
			description text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY customer_id (customer_id),
			KEY action (action),
			KEY created_at (created_at)
		) {$charset_collate};";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}

	/**
	 * Create order tables.
	 *
	 * @return void
	 */
	public static function create_order_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$orders         = Database::table( 'orders' );
		$items          = Database::table( 'order_items' );
		$payments       = Database::table( 'order_payments' );
		$refunds        = Database::table( 'order_refunds' );
		$status_history = Database::table( 'order_status_history' );

		$sql = array();

		$sql[] = "CREATE TABLE {$orders} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned NOT NULL DEFAULT 0,
			order_number varchar(100) NOT NULL,
			status varchar(30) NOT NULL DEFAULT 'pending',
			payment_status varchar(30) NOT NULL DEFAULT 'pending',
			fulfillment_status varchar(30) NOT NULL DEFAULT 'unfulfilled',
			currency varchar(10) NOT NULL DEFAULT 'USD',
			subtotal decimal(18,2) NOT NULL DEFAULT 0,
			discount_total decimal(18,2) NOT NULL DEFAULT 0,
			tax_total decimal(18,2) NOT NULL DEFAULT 0,
			shipping_total decimal(18,2) NOT NULL DEFAULT 0,
			total decimal(18,2) NOT NULL DEFAULT 0,
			customer_note text NULL,
			admin_note text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY order_number (order_number),
			KEY customer_id (customer_id),
			KEY status (status),
			KEY payment_status (payment_status),
			KEY fulfillment_status (fulfillment_status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$items} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id bigint(20) unsigned NOT NULL,
			product_id bigint(20) unsigned NOT NULL DEFAULT 0,
			variant_id bigint(20) unsigned NOT NULL DEFAULT 0,
			product_name varchar(191) NOT NULL,
			sku varchar(100) NOT NULL DEFAULT '',
			quantity decimal(18,4) NOT NULL DEFAULT 0,
			unit_price decimal(18,2) NOT NULL DEFAULT 0,
			discount decimal(18,2) NOT NULL DEFAULT 0,
			tax decimal(18,2) NOT NULL DEFAULT 0,
			total decimal(18,2) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY product_id (product_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$payments} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id bigint(20) unsigned NOT NULL,
			transaction_id varchar(191) NOT NULL DEFAULT '',
			method varchar(50) NOT NULL DEFAULT '',
			status varchar(30) NOT NULL DEFAULT 'pending',
			amount decimal(18,2) NOT NULL DEFAULT 0,
			currency varchar(10) NOT NULL DEFAULT 'USD',
			paid_at datetime NULL,
			metadata longtext NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY transaction_id (transaction_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$refunds} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id bigint(20) unsigned NOT NULL,
			payment_id bigint(20) unsigned NOT NULL DEFAULT 0,
			amount decimal(18,2) NOT NULL DEFAULT 0,
			reason text NULL,
			status varchar(30) NOT NULL DEFAULT 'pending',
			refund_reference varchar(191) NOT NULL DEFAULT '',
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY payment_id (payment_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$status_history} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id bigint(20) unsigned NOT NULL,
			old_status varchar(30) NOT NULL DEFAULT '',
			new_status varchar(30) NOT NULL,
			note text NULL,
			changed_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY new_status (new_status)
		) {$charset_collate};";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}

	/**
	 * Create POS tables.
	 *
	 * @return void
	 */
	public static function create_pos_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$registers = Database::table( 'pos_registers' );
		$sessions  = Database::table( 'pos_sessions' );
		$sales     = Database::table( 'pos_sales' );
		$items     = Database::table( 'pos_sale_items' );
		$payments  = Database::table( 'pos_payments' );

		$sql = array();

		$sql[] = "CREATE TABLE {$registers} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL,
			location varchar(191) NOT NULL DEFAULT '',
			status varchar(30) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$sessions} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			register_id bigint(20) unsigned NOT NULL,
			user_id bigint(20) unsigned NOT NULL,
			status varchar(30) NOT NULL DEFAULT 'open',
			opening_balance decimal(18,2) NOT NULL DEFAULT 0,
			closing_balance decimal(18,2) NOT NULL DEFAULT 0,
			opened_at datetime NOT NULL,
			closed_at datetime NULL,
			notes text NULL,
			PRIMARY KEY (id),
			KEY register_id (register_id),
			KEY user_id (user_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$sales} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_id bigint(20) unsigned NOT NULL DEFAULT 0,
			customer_id bigint(20) unsigned NOT NULL DEFAULT 0,
			sale_number varchar(100) NOT NULL,
			subtotal decimal(18,2) NOT NULL DEFAULT 0,
			discount_total decimal(18,2) NOT NULL DEFAULT 0,
			tax_total decimal(18,2) NOT NULL DEFAULT 0,
			total decimal(18,2) NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'completed',
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY sale_number (sale_number),
			KEY session_id (session_id),
			KEY customer_id (customer_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$items} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			sale_id bigint(20) unsigned NOT NULL,
			product_id bigint(20) unsigned NOT NULL DEFAULT 0,
			variant_id bigint(20) unsigned NOT NULL DEFAULT 0,
			product_name varchar(191) NOT NULL,
			sku varchar(100) NOT NULL DEFAULT '',
			quantity decimal(18,4) NOT NULL DEFAULT 0,
			unit_price decimal(18,2) NOT NULL DEFAULT 0,
			discount decimal(18,2) NOT NULL DEFAULT 0,
			tax decimal(18,2) NOT NULL DEFAULT 0,
			total decimal(18,2) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY sale_id (sale_id),
			KEY product_id (product_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$payments} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			sale_id bigint(20) unsigned NOT NULL,
			method varchar(50) NOT NULL,
			transaction_id varchar(191) NOT NULL DEFAULT '',
			amount decimal(18,2) NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'paid',
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY sale_id (sale_id),
			KEY transaction_id (transaction_id),
			KEY status (status)
		) {$charset_collate};";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}

	/**
	 * Create delivery tables.
	 *
	 * @return void
	 */
	public static function create_delivery_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$drivers    = Database::table( 'drivers' );
		$zones      = Database::table( 'delivery_zones' );
		$routes     = Database::table( 'delivery_routes' );
		$deliveries = Database::table( 'delivery_orders' );
		$proof      = Database::table( 'proof_of_delivery' );

		$sql = array();

		$sql[] = "CREATE TABLE {$drivers} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			name varchar(191) NOT NULL,
			phone varchar(50) NOT NULL DEFAULT '',
			vehicle_number varchar(100) NOT NULL DEFAULT '',
			status varchar(30) NOT NULL DEFAULT 'active',
			notes text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$zones} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL,
			description text NULL,
			delivery_fee decimal(18,2) NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$routes} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL,
			driver_id bigint(20) unsigned NOT NULL DEFAULT 0,
			route_date date NULL,
			status varchar(30) NOT NULL DEFAULT 'planned',
			notes text NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY driver_id (driver_id),
			KEY route_date (route_date),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$deliveries} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			order_id bigint(20) unsigned NOT NULL,
			driver_id bigint(20) unsigned NOT NULL DEFAULT 0,
			zone_id bigint(20) unsigned NOT NULL DEFAULT 0,
			route_id bigint(20) unsigned NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'pending',
			address text NOT NULL,
			city varchar(100) NOT NULL DEFAULT '',
			state varchar(100) NOT NULL DEFAULT '',
			postal_code varchar(30) NOT NULL DEFAULT '',
			latitude decimal(12,8) NOT NULL DEFAULT 0,
			longitude decimal(12,8) NOT NULL DEFAULT 0,
			scheduled_at datetime NULL,
			delivered_at datetime NULL,
			notes text NULL,
			status_note text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY order_id (order_id),
			KEY driver_id (driver_id),
			KEY zone_id (zone_id),
			KEY route_id (route_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$proof} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			delivery_id bigint(20) unsigned NOT NULL,
			type varchar(50) NOT NULL DEFAULT 'photo',
			file_url text NULL,
			signature longtext NULL,
			recipient_name varchar(191) NOT NULL DEFAULT '',
			notes text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY delivery_id (delivery_id),
			KEY type (type)
		) {$charset_collate};";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}

	/**
	 * Create staff tables.
	 *
	 * @return void
	 */
	public static function create_staff_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$staff_members = Database::table( 'staff_members' );
		$staff_shifts  = Database::table( 'staff_shifts' );
		$attendance    = Database::table( 'staff_attendance' );

		$sql = array();

		$sql[] = "CREATE TABLE {$staff_members} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			employee_id varchar(100) NOT NULL DEFAULT '',
			first_name varchar(100) NOT NULL,
			last_name varchar(100) NOT NULL DEFAULT '',
			email varchar(191) NOT NULL DEFAULT '',
			phone varchar(50) NOT NULL DEFAULT '',
			job_title varchar(191) NOT NULL DEFAULT '',
			department varchar(100) NOT NULL DEFAULT '',
			status varchar(30) NOT NULL DEFAULT 'active',
			hire_date date NULL,
			manager_id bigint(20) unsigned NOT NULL DEFAULT 0,
			notes text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY employee_id (employee_id),
			KEY status (status),
			KEY department (department),
			KEY manager_id (manager_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$staff_shifts} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			staff_id bigint(20) unsigned NOT NULL,
			shift_date date NOT NULL,
			start_time time NOT NULL,
			end_time time NOT NULL,
			break_minutes int(11) unsigned NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'scheduled',
			notes text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY staff_id (staff_id),
			KEY shift_date (shift_date),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$attendance} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			staff_id bigint(20) unsigned NOT NULL,
			attendance_date date NOT NULL,
			clock_in datetime NULL,
			clock_out datetime NULL,
			status varchar(30) NOT NULL DEFAULT 'present',
			clock_in_latitude decimal(12,8) NOT NULL DEFAULT 0,
			clock_in_longitude decimal(12,8) NOT NULL DEFAULT 0,
			clock_out_latitude decimal(12,8) NOT NULL DEFAULT 0,
			clock_out_longitude decimal(12,8) NOT NULL DEFAULT 0,
			notes text NULL,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY staff_id (staff_id),
			KEY attendance_date (attendance_date),
			KEY status (status)
		) {$charset_collate};";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}

	/**
	 * Create loyalty tables.
	 *
	 * @return void
	 */
	public static function create_loyalty_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$members     = Database::table( 'loyalty_members' );
		$points      = Database::table( 'loyalty_points' );
		$rewards     = Database::table( 'loyalty_rewards' );
		$redemptions = Database::table( 'loyalty_redemptions' );

		$sql = array();

		$sql[] = "CREATE TABLE {$members} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			customer_id bigint(20) unsigned NOT NULL,
			member_code varchar(100) NOT NULL,
			tier varchar(50) NOT NULL DEFAULT 'standard',
			points_balance bigint(20) NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY customer_id (customer_id),
			UNIQUE KEY member_code (member_code),
			KEY status (status),
			KEY tier (tier)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$points} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			member_id bigint(20) unsigned NOT NULL,
			order_id bigint(20) unsigned NOT NULL DEFAULT 0,
			points bigint(20) NOT NULL,
			type varchar(30) NOT NULL,
			reason varchar(255) NOT NULL DEFAULT '',
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY member_id (member_id),
			KEY order_id (order_id),
			KEY type (type),
			KEY created_at (created_at)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$rewards} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(191) NOT NULL,
			description text NULL,
			points_required bigint(20) unsigned NOT NULL DEFAULT 0,
			reward_type varchar(50) NOT NULL DEFAULT 'discount',
			value decimal(18,2) NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'active',
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY points_required (points_required),
			KEY reward_type (reward_type),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$redemptions} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			member_id bigint(20) unsigned NOT NULL,
			reward_id bigint(20) unsigned NOT NULL,
			order_id bigint(20) unsigned NOT NULL DEFAULT 0,
			points_used bigint(20) unsigned NOT NULL DEFAULT 0,
			value decimal(18,2) NOT NULL DEFAULT 0,
			status varchar(30) NOT NULL DEFAULT 'completed',
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY member_id (member_id),
			KEY reward_id (reward_id),
			KEY order_id (order_id),
			KEY status (status)
		) {$charset_collate};";

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}
}
