<?php
/**
 * Security and Audit Log admin view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Security\Audit_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_manage_settings' ) ) {
	wp_die(
		esc_html__(
			'You do not have permission to view security settings.',
			'dispensary-wp'
		)
	);
}

global $wpdb;

$table = $wpdb->prefix . 'disp_audit_logs';

$limit = 100;

$logs = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT *
		FROM {$table}
		ORDER BY id DESC
		LIMIT %d",
		$limit
	),
	ARRAY_A
);

if ( ! is_array( $logs ) ) {
	$logs = array();
}

$total_logs = (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM {$table}"
);

$today_logs = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*)
		FROM {$table}
		WHERE created_at >= %s",
		current_time( 'Y-m-d 00:00:00' )
	)
);
?>

<div class="wrap dispensary-wp-admin">

	<h1><?php esc_html_e( 'Security & Audit Log', 'dispensary-wp' ); ?></h1>

	<div class="dispensary-wp-cards">

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Total Audit Events', 'dispensary-wp' ); ?></h3>
			<strong>
				<?php echo esc_html( number_format_i18n( $total_logs ) ); ?>
			</strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Events Today', 'dispensary-wp' ); ?></h3>
			<strong>
				<?php echo esc_html( number_format_i18n( $today_logs ) ); ?>
			</strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Security Status', 'dispensary-wp' ); ?></h3>
			<strong>
				<?php esc_html_e( 'Active', 'dispensary-wp' ); ?>
			</strong>
		</div>

	</div>

	<div class="dispensary-wp-panel">

		<h2><?php esc_html_e( 'Recent Audit Events', 'dispensary-wp' ); ?></h2>

		<table class="widefat striped">

			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'User', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Action', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Object', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'IP Address', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Date', 'dispensary-wp' ); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php if ( empty( $logs ) ) : ?>

					<tr>
						<td colspan="6">
							<?php
							esc_html_e(
								'No audit events found.',
								'dispensary-wp'
							);
							?>
						</td>
					</tr>

				<?php else : ?>

					<?php foreach ( $logs as $log ) : ?>

						<tr>

							<td>
								<?php echo esc_html( $log['id'] ?? '' ); ?>
							</td>

							<td>
								<?php echo esc_html( $log['user_id'] ?? '0' ); ?>
							</td>

							<td>
								<strong>
									<?php echo esc_html( $log['action'] ?? '' ); ?>
								</strong>
							</td>

							<td>
								<?php echo esc_html( $log['object_type'] ?? '' ); ?>

								<?php if ( ! empty( $log['object_id'] ) ) : ?>
									#<?php echo esc_html( $log['object_id'] ); ?>
								<?php endif; ?>
							</td>

							<td>
								<?php echo esc_html( $log['ip_address'] ?? '' ); ?>
							</td>

							<td>
								<?php echo esc_html( $log['created_at'] ?? '' ); ?>
							</td>

						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			</tbody>

		</table>

	</div>

</div>
