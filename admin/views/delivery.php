<?php
/**
 * Delivery management admin view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Modules\Delivery\Delivery;
use Dispensary_WP\Modules\Orders\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_view_delivery' ) ) {
	wp_die(
		esc_html__( 'You do not have permission to view deliveries.', 'dispensary-wp' ),
		esc_html__( 'Permission Denied', 'dispensary-wp' ),
		array( 'response' => 403 )
	);
}

$delivery_service = new Delivery();
$can_manage       = current_user_can( 'dispensary_manage_delivery' );
$notice           = '';
$error            = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {

	check_admin_referer( 'dispensary_delivery_action', 'dispensary_delivery_nonce' );

	if ( ! $can_manage ) {
		$error = __( 'You do not have permission to manage deliveries.', 'dispensary-wp' );
	} else {

		$action = isset( $_POST['delivery_action'] )
			? sanitize_key( wp_unslash( $_POST['delivery_action'] ) )
			: '';

		if ( 'create' === $action ) {

			$result = $delivery_service->create(
				array(
					'order_id'       => absint( $_POST['order_id'] ?? 0 ),
					'driver_id'      => absint( $_POST['driver_id'] ?? 0 ),
					'zone_id'        => absint( $_POST['zone_id'] ?? 0 ),
					'route_id'       => absint( $_POST['route_id'] ?? 0 ),
					'address_line_1' => sanitize_text_field( wp_unslash( $_POST['address_line_1'] ?? '' ) ),
					'address_line_2' => sanitize_text_field( wp_unslash( $_POST['address_line_2'] ?? '' ) ),
					'city'           => sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) ),
					'state'          => sanitize_text_field( wp_unslash( $_POST['state'] ?? '' ) ),
					'postal_code'    => sanitize_text_field( wp_unslash( $_POST['postal_code'] ?? '' ) ),
					'country'        => sanitize_text_field( wp_unslash( $_POST['country'] ?? '' ) ),
					'delivery_note'  => sanitize_textarea_field( wp_unslash( $_POST['delivery_note'] ?? '' ) ),
					'scheduled_at'   => sanitize_text_field( wp_unslash( $_POST['scheduled_at'] ?? '' ) ),
				)
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __( 'Delivery created successfully.', 'dispensary-wp' );
			}
		}

		if ( 'status' === $action ) {

			$result = $delivery_service->update_status(
				absint( $_POST['delivery_id'] ?? 0 ),
				sanitize_key( wp_unslash( $_POST['status'] ?? '' ) ),
				sanitize_textarea_field( wp_unslash( $_POST['status_note'] ?? '' ) )
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __( 'Delivery status updated.', 'dispensary-wp' );
			}
		}

		if ( 'assign_driver' === $action ) {

			$result = $delivery_service->assign_driver(
				absint( $_POST['delivery_id'] ?? 0 ),
				absint( $_POST['driver_id'] ?? 0 )
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __( 'Driver assigned successfully.', 'dispensary-wp' );
			}
		}
	}
}

$deliveries = $delivery_service->list_deliveries(
	array(
		'limit' => 100,
	)
);

$drivers = $delivery_service->drivers();

$order_model = new Order();
$orders = $order_model->all(
	array(
		'limit' => 100,
	)
);

$status_counts = array(
	'pending'          => 0,
	'assigned'         => 0,
	'ready'            => 0,
	'out_for_delivery' => 0,
	'delivered'        => 0,
	'failed'           => 0,
	'cancelled'        => 0,
);

$total_deliveries = is_array( $deliveries ) ? count( $deliveries ) : 0;

if ( is_array( $deliveries ) ) {
	foreach ( $deliveries as $delivery ) {
		if ( isset( $status_counts[ $delivery->status ] ) ) {
			$status_counts[ $delivery->status ]++;
		}
	}
}

?>

<div class="wrap dispensary-wp-admin">

	<h1><?php esc_html_e( 'Delivery Management', 'dispensary-wp' ); ?></h1>

	<?php if ( $notice ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $notice ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( $error ) : ?>
		<div class="notice notice-error">
			<p><?php echo esc_html( $error ); ?></p>
		</div>
	<?php endif; ?>

	<div class="dispensary-wp-dashboard-cards">

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Total Deliveries', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $total_deliveries ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Pending', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $status_counts['pending'] ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Out for Delivery', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $status_counts['out_for_delivery'] ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Delivered', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $status_counts['delivered'] ); ?></strong>
		</div>

	</div>

	<?php if ( $can_manage ) : ?>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Create Delivery', 'dispensary-wp' ); ?></h2>

			<form method="post">

				<input type="hidden" name="delivery_action" value="create">

				<?php wp_nonce_field( 'dispensary_delivery_action', 'dispensary_delivery_nonce' ); ?>

				<table class="form-table">

					<tr>
						<th>
							<label for="order_id">
								<?php esc_html_e( 'Order', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<select name="order_id" id="order_id" required>
								<option value="">
									<?php esc_html_e( 'Select Order', 'dispensary-wp' ); ?>
								</option>

								<?php if ( is_array( $orders ) ) : ?>
									<?php foreach ( $orders as $order ) : ?>
										<option value="<?php echo esc_attr( $order->id ); ?>">
											<?php
											echo esc_html(
												'#' . $order->id .
												' — ' .
												( isset( $order->total ) ? $order->total : '' )
											);
											?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>

							</select>
						</td>
					</tr>

					<tr>
						<th>
							<label for="driver_id">
								<?php esc_html_e( 'Driver', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<select name="driver_id" id="driver_id">
								<option value="0">
									<?php esc_html_e( 'Unassigned', 'dispensary-wp' ); ?>
								</option>

								<?php if ( is_array( $drivers ) ) : ?>
									<?php foreach ( $drivers as $driver ) : ?>
										<option value="<?php echo esc_attr( $driver->id ); ?>">
											<?php echo esc_html( $driver->name ); ?>
											<?php if ( ! empty( $driver->phone ) ) : ?>
												— <?php echo esc_html( $driver->phone ); ?>
											<?php endif; ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>

							</select>
						</td>
					</tr>

					<tr>
						<th>
							<label for="address_line_1">
								<?php esc_html_e( 'Address', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input
								type="text"
								class="regular-text"
								name="address_line_1"
								id="address_line_1"
								required
							>
						</td>
					</tr>

					<tr>
						<th>
							<label for="address_line_2">
								<?php esc_html_e( 'Address Line 2', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input
								type="text"
								class="regular-text"
								name="address_line_2"
								id="address_line_2"
							>
						</td>
					</tr>

					<tr>
						<th>
							<label for="city">
								<?php esc_html_e( 'City', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" name="city" id="city">
						</td>
					</tr>

					<tr>
						<th>
							<label for="state">
								<?php esc_html_e( 'State', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" name="state" id="state">
						</td>
					</tr>

					<tr>
						<th>
							<label for="postal_code">
								<?php esc_html_e( 'Postal Code', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input type="text" class="regular-text" name="postal_code" id="postal_code">
						</td>
					</tr>

					<tr>
						<th>
							<label for="country">
								<?php esc_html_e( 'Country', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input
								type="text"
								class="regular-text"
								name="country"
								id="country"
								value="India"
							>
						</td>
					</tr>

					<tr>
						<th>
							<label for="scheduled_at">
								<?php esc_html_e( 'Scheduled At', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input
								type="datetime-local"
								name="scheduled_at"
								id="scheduled_at"
							>
						</td>
					</tr>

					<tr>
						<th>
							<label for="delivery_note">
								<?php esc_html_e( 'Delivery Note', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<textarea
								name="delivery_note"
								id="delivery_note"
								rows="4"
								class="large-text"
							></textarea>
						</td>
					</tr>

				</table>

				<?php submit_button( __( 'Create Delivery', 'dispensary-wp' ) ); ?>

			</form>

		</div>

	<?php endif; ?>

	<div class="dispensary-wp-panel">

		<h2><?php esc_html_e( 'Deliveries', 'dispensary-wp' ); ?></h2>

		<table class="widefat striped">

			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Order', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Driver', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Address', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Status', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Scheduled', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'dispensary-wp' ); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php if ( empty( $deliveries ) || is_wp_error( $deliveries ) ) : ?>

					<tr>
						<td colspan="7">
							<?php esc_html_e( 'No deliveries found.', 'dispensary-wp' ); ?>
						</td>
					</tr>

				<?php else : ?>

					<?php foreach ( $deliveries as $delivery ) : ?>

						<?php
						$driver_name = __( 'Unassigned', 'dispensary-wp' );

						if ( ! empty( $delivery->driver_id ) ) {
							$driver = $delivery_service->get( $delivery->id );

							if (
								is_array( $driver ) &&
								! empty( $driver['driver'] ) &&
								! empty( $driver['driver']->name )
							) {
								$driver_name = $driver['driver']->name;
							}
						}
						?>

						<tr>

							<td><?php echo esc_html( $delivery->id ); ?></td>

							<td>
								<strong>
									#<?php echo esc_html( $delivery->order_id ); ?>
								</strong>
							</td>

							<td>
								<?php echo esc_html( $driver_name ); ?>
							</td>

							<td>
								<?php echo esc_html( $delivery->address_line_1 ); ?><br>

								<?php if ( ! empty( $delivery->address_line_2 ) ) : ?>
									<?php echo esc_html( $delivery->address_line_2 ); ?><br>
								<?php endif; ?>

								<?php
								echo esc_html(
									trim(
										$delivery->city . ', ' .
										$delivery->state . ' ' .
										$delivery->postal_code
									)
								);
								?>
							</td>

							<td>
								<strong>
									<?php echo esc_html( ucwords( str_replace( '_', ' ', $delivery->status ) ) ); ?>
								</strong>
							</td>

							<td>
								<?php
								echo ! empty( $delivery->scheduled_at )
									? esc_html( $delivery->scheduled_at )
									: '—';
								?>
							</td>

							<td>

								<?php if ( $can_manage ) : ?>

									<form method="post" style="margin-bottom:8px;">

										<input type="hidden" name="delivery_action" value="status">

										<input
											type="hidden"
											name="delivery_id"
											value="<?php echo esc_attr( $delivery->id ); ?>"
										>

										<?php wp_nonce_field( 'dispensary_delivery_action', 'dispensary_delivery_nonce' ); ?>

										<select name="status">

											<?php
											$statuses = array(
												'pending',
												'assigned',
												'ready',
												'out_for_delivery',
												'delivered',
												'failed',
												'cancelled',
											);
											?>

											<?php foreach ( $statuses as $status ) : ?>

												<option
													value="<?php echo esc_attr( $status ); ?>"
													<?php selected( $delivery->status, $status ); ?>
												>
													<?php echo esc_html( ucwords( str_replace( '_', ' ', $status ) ) ); ?>
												</option>

											<?php endforeach; ?>

										</select>

										<input
											type="text"
											name="status_note"
											placeholder="<?php esc_attr_e( 'Status note', 'dispensary-wp' ); ?>"
										>

										<button type="submit" class="button">
											<?php esc_html_e( 'Update', 'dispensary-wp' ); ?>
										</button>

									</form>

									<?php if ( ! empty( $drivers ) ) : ?>

										<form method="post">

											<input type="hidden" name="delivery_action" value="assign_driver">

											<input
												type="hidden"
												name="delivery_id"
												value="<?php echo esc_attr( $delivery->id ); ?>"
											>

											<?php wp_nonce_field( 'dispensary_delivery_action', 'dispensary_delivery_nonce' ); ?>

											<select name="driver_id">

												<?php foreach ( $drivers as $driver ) : ?>

													<option
														value="<?php echo esc_attr( $driver->id ); ?>"
														<?php selected( $delivery->driver_id, $driver->id ); ?>
													>
														<?php echo esc_html( $driver->name ); ?>
													</option>

												<?php endforeach; ?>

											</select>

											<button type="submit" class="button">
												<?php esc_html_e( 'Assign', 'dispensary-wp' ); ?>
											</button>

										</form>

									<?php endif; ?>

								<?php else : ?>

									<span>—</span>

								<?php endif; ?>

							</td>

						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			</tbody>

		</table>

	</div>

</div>
