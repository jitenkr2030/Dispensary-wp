<?php
/**
 * Plugin Name: Dispensary WP
 * Plugin URI: https://example.com/dispensary-wp
 * Description: Modular dispensary management platform for WordPress.
 * Version: 1.0.0
 * Author: Jitender Kumar
 * Text Domain: dispensary-wp
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Plugin constants.
 */
define( 'DISPENSARY_WP_VERSION', '1.0.0' );
define( 'DISPENSARY_WP_FILE', __FILE__ );
define( 'DISPENSARY_WP_DIR', plugin_dir_path( __FILE__ ) );
define( 'DISPENSARY_WP_URL', plugin_dir_url( __FILE__ ) );
define( 'DISPENSARY_WP_BASENAME', plugin_basename( __FILE__ ) );

/*
 * Core.
 */
require_once DISPENSARY_WP_DIR . 'includes/Core/class-loader.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-activator.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-deactivator.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-settings.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-i18n.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-hooks.php';

/*
 * Database.
 */
require_once DISPENSARY_WP_DIR . 'includes/Database/class-database.php';
require_once DISPENSARY_WP_DIR . 'includes/Database/class-schema.php';
require_once DISPENSARY_WP_DIR . 'includes/Database/class-migrations.php';
require_once DISPENSARY_WP_DIR . 'includes/Database/class-repository.php';

/*
 * Permissions.
 */
require_once DISPENSARY_WP_DIR . 'includes/Permissions/class-capabilities.php';
require_once DISPENSARY_WP_DIR . 'includes/Permissions/class-roles.php';
require_once DISPENSARY_WP_DIR . 'includes/Permissions/class-permissions.php';

/*
 * Security.
 */
require_once DISPENSARY_WP_DIR . 'includes/Security/class-security.php';
require_once DISPENSARY_WP_DIR . 'includes/Security/class-validator.php';
require_once DISPENSARY_WP_DIR . 'includes/Security/class-sanitizer.php';
require_once DISPENSARY_WP_DIR . 'includes/Security/class-nonce.php';
require_once DISPENSARY_WP_DIR . 'includes/Security/class-audit-log.php';

/*
 * Compliance.
 */
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-compliance.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-age-verification.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-jurisdiction.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-compliance-rules.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-compliance-logger.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-compliance-reports.php';

/*
 * Integration.
 */
require_once DISPENSARY_WP_DIR . 'includes/Integration/class-module-loader.php';
require_once DISPENSARY_WP_DIR . 'includes/Integration/class-health-check.php';

/*
 * Products.
 */
require_once DISPENSARY_WP_DIR . 'modules/products/class-product.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-products.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-category.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-variant.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-meta.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-lab-test.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-controller.php';

/*
 * Inventory.
 */
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-inventory.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-stock.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-stock-movement.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-batch.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-lot.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-supplier.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-purchase.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-low-stock.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-inventory-controller.php';

/*
 * Customers.
 */
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customers.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer-profile.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer-verification.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer-history.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer-controller.php';

/*
 * Orders.
 */
require_once DISPENSARY_WP_DIR . 'modules/orders/class-orders.php';
require_once DISPENSARY_WP_DIR . 'modules/orders/class-order.php';
require_once DISPENSARY_WP_DIR . 'modules/orders/class-order-item.php';
require_once DISPENSARY_WP_DIR . 'modules/orders/class-order-status.php';
require_once DISPENSARY_WP_DIR . 'modules/orders/class-order-payment.php';
require_once DISPENSARY_WP_DIR . 'modules/orders/class-order-refund.php';
require_once DISPENSARY_WP_DIR . 'modules/orders/class-order-controller.php';

/*
 * POS.
 */
require_once DISPENSARY_WP_DIR . 'modules/pos/class-pos.php';
require_once DISPENSARY_WP_DIR . 'modules/pos/class-pos-cart.php';
require_once DISPENSARY_WP_DIR . 'modules/pos/class-pos-sale.php';
require_once DISPENSARY_WP_DIR . 'modules/pos/class-pos-payment.php';
require_once DISPENSARY_WP_DIR . 'modules/pos/class-pos-receipt.php';
require_once DISPENSARY_WP_DIR . 'modules/pos/class-pos-session.php';
require_once DISPENSARY_WP_DIR . 'modules/pos/class-pos-register.php';
require_once DISPENSARY_WP_DIR . 'modules/pos/class-pos-controller.php';

/*
 * Delivery.
 */
require_once DISPENSARY_WP_DIR . 'modules/delivery/class-delivery.php';
require_once DISPENSARY_WP_DIR . 'modules/delivery/class-driver.php';
require_once DISPENSARY_WP_DIR . 'modules/delivery/class-delivery-zone.php';
require_once DISPENSARY_WP_DIR . 'modules/delivery/class-delivery-order.php';
require_once DISPENSARY_WP_DIR . 'modules/delivery/class-delivery-route.php';
require_once DISPENSARY_WP_DIR . 'modules/delivery/class-proof-of-delivery.php';
require_once DISPENSARY_WP_DIR . 'modules/delivery/class-delivery-controller.php';

/*
 * Staff.
 */
require_once DISPENSARY_WP_DIR . 'modules/staff/class-staff.php';
require_once DISPENSARY_WP_DIR . 'modules/staff/class-staff-member.php';
require_once DISPENSARY_WP_DIR . 'modules/staff/class-staff-shift.php';
require_once DISPENSARY_WP_DIR . 'modules/staff/class-staff-attendance.php';
require_once DISPENSARY_WP_DIR . 'modules/staff/class-staff-controller.php';

/*
 * Loyalty.
 */
require_once DISPENSARY_WP_DIR . 'modules/loyalty/class-loyalty.php';
require_once DISPENSARY_WP_DIR . 'modules/loyalty/class-loyalty-points.php';
require_once DISPENSARY_WP_DIR . 'modules/loyalty/class-loyalty-member.php';
require_once DISPENSARY_WP_DIR . 'modules/loyalty/class-loyalty-rewards.php';
require_once DISPENSARY_WP_DIR . 'modules/loyalty/class-loyalty-controller.php';

/*
 * Reports.
 */
require_once DISPENSARY_WP_DIR . 'modules/reports/class-reports.php';
require_once DISPENSARY_WP_DIR . 'modules/reports/class-sales-report.php';
require_once DISPENSARY_WP_DIR . 'modules/reports/class-inventory-report.php';
require_once DISPENSARY_WP_DIR . 'modules/reports/class-customer-report.php';
require_once DISPENSARY_WP_DIR . 'modules/reports/class-staff-report.php';
require_once DISPENSARY_WP_DIR . 'modules/reports/class-product-report.php';
require_once DISPENSARY_WP_DIR . 'modules/reports/class-financial-report.php';
require_once DISPENSARY_WP_DIR . 'modules/reports/class-report-controller.php';

/*
 * Admin.
 */
require_once DISPENSARY_WP_DIR . 'admin/class-admin.php';
require_once DISPENSARY_WP_DIR . 'admin/class-admin-menu.php';
require_once DISPENSARY_WP_DIR . 'admin/class-admin-notices.php';
require_once DISPENSARY_WP_DIR . 'admin/class-admin-dashboard.php';

/*
 * Public frontend.
 */
require_once DISPENSARY_WP_DIR . 'public/class-public.php';
require_once DISPENSARY_WP_DIR . 'public/class-shortcodes.php';
require_once DISPENSARY_WP_DIR . 'public/class-frontend-controller.php';

/*
 * Templates.
 */
require_once DISPENSARY_WP_DIR . 'templates/class-template-loader.php';
require_once DISPENSARY_WP_DIR . 'templates/template-functions.php';

/*
 * WooCommerce integration.
 *
 * These classes are safe to load even when WooCommerce
 * itself is not installed because the integration checks
 * for WooCommerce before registering its hooks.
 */
require_once DISPENSARY_WP_DIR . 'woocommerce/class-woocommerce.php';
require_once DISPENSARY_WP_DIR . 'woocommerce/class-product-sync.php';
require_once DISPENSARY_WP_DIR . 'woocommerce/class-order-sync.php';
require_once DISPENSARY_WP_DIR . 'woocommerce/class-customer-sync.php';
require_once DISPENSARY_WP_DIR . 'woocommerce/class-stock-sync.php';
require_once DISPENSARY_WP_DIR . 'woocommerce/class-woocommerce-hooks.php';

/*
 * Main plugin class.
 */
require_once DISPENSARY_WP_DIR . 'includes/Core/class-plugin.php';

/*
 * Activation.
 */
register_activation_hook(
    __FILE__,
    array( 'Dispensary_WP\Core\Activator', 'activate' )
);

/*
 * Deactivation.
 */
register_deactivation_hook(
    __FILE__,
    array( 'Dispensary_WP\Core\Deactivator', 'deactivate' )
);

/*
 * Start plugin.
 */
\Dispensary_WP\Core\Plugin::instance()->run();
