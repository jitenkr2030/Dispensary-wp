<?php
/**
 * Compliance admin view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Compliance\Compliance_Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_view_compliance' ) ) {
	wp_die(
		esc_html__(
			'You do not have permission to view compliance reports.',
			'dispensary-wp'
		)
	);
}

$limit  = 100;
$offset = 0;

$events = Compliance_Reports::get_events( $limit, $offset );
$total  = Compliance_Reports::count_events();

if ( ! is_array( $events ) ) {
	$events = array();
}
?>

<div class="wrap dispensary-wp-admin">

	<h1><?php esc_html_e( 'Compliance', 'dispensary-wp' ); ?></h1>

	<div class="dispensary-wp-cards">

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Compliance Events', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( number_format_i18n( $total ) ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Events Shown', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( count( $events ) ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Monitoring', 'dispensary-wp' ); ?></h3>
			<strong><?php esc_html_e( 'Active', 'dispensary-wp' ); ?></strong>
		</div>

	</div>

	<div class="dispensary-wp-panel">

		<h2><?php esc_html_e( 'Age Verification Events', 'dispensary-wp' ); ?></h2>

		<p>
			<?php
			esc_html_e(
				'Recent age-verification related audit events recorded by the system.',
				'dispensary-wp'
			);
			?>
		</p>

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

				<?php if ( empty( $events ) ) : ?>

					<tr>
						<td colspan="6">
							<?php
							esc_html_e(
								'No compliance events found.',
								'dispensary-wp'
							);
							?>
						</td>
					</tr>

				<?php else : ?>

					<?php foreach ( $events as $event ) : ?>

						<tr>

							<td>
								<?php echo esc_html( $event['id'] ?? '' ); ?>
							</td>

							<td>
								<?php echo esc_html( $event['user_id'] ?? '0' ); ?>
							</td>

							<td>
								<strong>
									<?php echo esc_html( $event['action'] ?? '' ); ?>
								</strong>
							</td>

							<td>
								<?php
								echo esc_html(
									$event['object_type'] ?? ''
								);
								?>

								<?php if ( ! empty( $event['object_id'] ) ) : ?>
									#<?php echo esc_html( $event['object_id'] ); ?>
								<?php endif; ?>
							</td>

							<td>
								<?php echo esc_html( $event['ip_address'] ?? '' ); ?>
							</td>

							<td>
								<?php echo esc_html( $event['created_at'] ?? '' ); ?>
							</td>

						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			</tbody>

		</table>

	</div>

	<div class="dispensary-wp-panel">

		<h2><?php esc_html_e( 'Compliance Information', 'dispensary-wp' ); ?></h2>

		<ul>
			<li>
				<?php esc_html_e( 'Age verification events are recorded in the audit log.', 'dispensary-wp' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Compliance activity should be reviewed regularly.', 'dispensary-wp' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Audit records should not be deleted without proper authorization.', 'dispensary-wp' ); ?>
			</li>
		</ul>

	</div>

</div>
