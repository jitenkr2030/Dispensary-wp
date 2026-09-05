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

define( 'DISPENSARY_WP_VERSION', '1.0.0' );
define( 'DISPENSARY_WP_FILE', __FILE__ );
define( 'DISPENSARY_WP_DIR', plugin_dir_path( __FILE__ ) );
define( 'DISPENSARY_WP_URL', plugin_dir_url( __FILE__ ) );
define( 'DISPENSARY_WP_BASENAME', plugin_basename( __FILE__ ) );

require_once DISPENSARY_WP_DIR . 'includes/Core/class-loader.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-activator.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-deactivator.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-settings.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-i18n.php';
require_once DISPENSARY_WP_DIR . 'includes/Core/class-hooks.php';

require_once DISPENSARY_WP_DIR . 'includes/Database/class-database.php';
require_once DISPENSARY_WP_DIR . 'includes/Database/class-schema.php';
require_once DISPENSARY_WP_DIR . 'includes/Database/class-migrations.php';
require_once DISPENSARY_WP_DIR . 'includes/Database/class-repository.php';

require_once DISPENSARY_WP_DIR . 'includes/Permissions/class-capabilities.php';
require_once DISPENSARY_WP_DIR . 'includes/Permissions/class-roles.php';
require_once DISPENSARY_WP_DIR . 'includes/Permissions/class-permissions.php';

require_once DISPENSARY_WP_DIR . 'includes/Security/class-security.php';
require_once DISPENSARY_WP_DIR . 'includes/Security/class-validator.php';
require_once DISPENSARY_WP_DIR . 'includes/Security/class-sanitizer.php';
require_once DISPENSARY_WP_DIR . 'includes/Security/class-nonce.php';
require_once DISPENSARY_WP_DIR . 'includes/Security/class-audit-log.php';

require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-compliance.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-age-verification.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-jurisdiction.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-compliance-rules.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-compliance-logger.php';
require_once DISPENSARY_WP_DIR . 'includes/Compliance/class-compliance-reports.php';

require_once DISPENSARY_WP_DIR . 'modules/products/class-product.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-products.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-category.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-variant.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-meta.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-lab-test.php';
require_once DISPENSARY_WP_DIR . 'modules/products/class-product-controller.php';

require_once DISPENSARY_WP_DIR . 'modules/inventory/class-inventory.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-stock.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-stock-movement.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-batch.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-lot.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-supplier.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-purchase.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-low-stock.php';
require_once DISPENSARY_WP_DIR . 'modules/inventory/class-inventory-controller.php';

require_once DISPENSARY_WP_DIR . 'modules/customers/class-customers.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer-profile.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer-verification.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer-history.php';
require_once DISPENSARY_WP_DIR . 'modules/customers/class-customer-controller.php';






require_once DISPENSARY_WP_DIR . 'includes/Core/class-plugin.php';

register_activation_hook(
	__FILE__,
	array( 'Dispensary_WP\Core\Activator', 'activate' )
);

register_deactivation_hook(
	__FILE__,
	array( 'Dispensary_WP\Core\Deactivator', 'deactivate' )
);

\Dispensary_WP\Core\Plugin::instance()->run();
