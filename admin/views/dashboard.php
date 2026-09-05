<?php
/**
 * Dashboard view.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stats = Admin_Dashboard::statistics();
?>

<div class="wrap dispensary-wp-admin">

	<div class="dispensary-wp-header">
		<h1><?php esc_html_e( 'Dispensary WP Dashboard', 'dispensary-wp' ); ?></h1>
	</div>

	<div class="dispensary-wp-cards">

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Customers', 'dispensary-wp' ); ?></h3>
			<div class="number">
				<?php echo esc_html( number_format_i18n( $stats['customers'] ) ); ?>
			</div>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Orders', 'dispensary-wp' ); ?></h3>
			<div class="number">
				<?php echo esc_html( number_format_i18n( $stats['orders'] ) ); ?>
			</div>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Products', 'dispensary-wp' ); ?></h3>
			<div class="number">
				<?php echo esc_html( number_format_i18n( $stats['products'] ) ); ?>
			</div>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Revenue', 'dispensary-wp' ); ?></h3>
			<div class="number">
				<?php echo esc_html( number_format_i18n( $stats['revenue'], 2 ) ); ?>
			</div>
		</div>

	</div>

	<div class="dispensary-wp-grid">

		<div class="dispensary-wp-panel">
			<h2><?php esc_html_e( 'Quick Access', 'dispensary-wp' ); ?></h2>

			<p>
				<a class="button button-primary"
					href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-wp-pos' ) ); ?>">
					<?php esc_html_e( 'Open POS', 'dispensary-wp' ); ?>
				</a>
			</p>

			<p>
				<a class="button"
					href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-wp-orders' ) ); ?>">
					<?php esc_html_e( 'View Orders', 'dispensary-wp' ); ?>
				</a>
			</p>

			<p>
				<a class="button"
					href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-wp-reports' ) ); ?>">
					<?php esc_html_e( 'View Reports', 'dispensary-wp' ); ?>
				</a>
			</p>
		</div>

		<div class="dispensary-wp-panel">
			<h2><?php esc_html_e( 'System', 'dispensary-wp' ); ?></h2>

			<table class="dispensary-wp-table">
				<tr>
					<th><?php esc_html_e( 'Plugin Version', 'dispensary-wp' ); ?></th>
					<td><?php echo esc_html( DISPENSARY_WP_VERSION ); ?></td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'WordPress', 'dispensary-wp' ); ?></th>
					<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'PHP', 'dispensary-wp' ); ?></th>
					<td><?php echo esc_html( PHP_VERSION ); ?></td>
				</tr>
			</table>
		</div>

	</div>

</div>
