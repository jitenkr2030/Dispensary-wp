<?php

use Dispensary_WP\Modules\Customers\Customer;
use Dispensary_WP\Modules\Customers\Customers;
use Dispensary_WP\Modules\Customers\Customer_History;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_view_customers' ) ) {
	wp_die(
		esc_html__( 'You do not have permission to view customers.', 'dispensary-wp' )
	);
}

$message = '';
$error   = '';

if ( isset( $_POST['dispensary_customer_action'] ) ) {

	if (
		! isset( $_POST['dispensary_customer_nonce'] ) ||
		! wp_verify_nonce(
			sanitize_text_field(
				wp_unslash( $_POST['dispensary_customer_nonce'] )
			),
			'dispensary_customer_action'
		)
	) {
		$error = __( 'Security check failed.', 'dispensary-wp' );
	} elseif ( ! current_user_can( 'dispensary_manage_customers' ) ) {
		$error = __( 'You do not have permission to manage customers.', 'dispensary-wp' );
	} else {

		$action = sanitize_key(
			wp_unslash( $_POST['dispensary_customer_action'] )
		);

		$data = array(
			'first_name' => sanitize_text_field(
				wp_unslash( $_POST['first_name'] ?? '' )
			),
			'last_name'  => sanitize_text_field(
				wp_unslash( $_POST['last_name'] ?? '' )
			),
			'email'      => sanitize_email(
				wp_unslash( $_POST['email'] ?? '' )
			),
			'phone'      => sanitize_text_field(
				wp_unslash( $_POST['phone'] ?? '' )
			),
			'status'     => sanitize_key(
				wp_unslash( $_POST['status'] ?? 'active' )
			),
			'notes'      => sanitize_textarea_field(
				wp_unslash( $_POST['notes'] ?? '' )
			),
		);

		if ( empty( $data['first_name'] ) ) {
			$error = __( 'First name is required.', 'dispensary-wp' );
		} elseif ( 'create' === $action ) {

			$result = Customers::create( $data );

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$message = __( 'Customer created successfully.', 'dispensary-wp' );
			}
		} elseif ( 'update' === $action ) {

			$id = absint(
				$_POST['customer_id'] ?? 0
			);

			if ( ! $id ) {
				$error = __( 'Invalid customer.', 'dispensary-wp' );
			} else {
				$result = Customers::update( $id, $data );

				if ( is_wp_error( $result ) ) {
					$error = $result->get_error_message();
				} else {
					$message = __( 'Customer updated successfully.', 'dispensary-wp' );
				}
			}
		} elseif ( 'delete' === $action ) {

			$id = absint(
				$_POST['customer_id'] ?? 0
			);

			if ( ! $id ) {
				$error = __( 'Invalid customer.', 'dispensary-wp' );
			} else {
				$result = Customers::delete( $id );

				if ( ! $result ) {
					$error = __( 'Unable to delete customer.', 'dispensary-wp' );
				} else {
					$message = __( 'Customer deleted successfully.', 'dispensary-wp' );
				}
			}
		}
	}
}

$edit_id = isset( $_GET['edit'] )
	? absint( $_GET['edit'] )
	: 0;

$edit_customer = $edit_id
	? Customer::find( $edit_id )
	: null;

$search = isset( $_GET['s'] )
	? sanitize_text_field(
		wp_unslash( $_GET['s'] )
	)
	: '';

$customers = Customer::all(
	array(
		'search' => $search,
		'limit'  => 200,
	)
);

$active_count   = 0;
$inactive_count = 0;

foreach ( $customers as $customer ) {
	if ( 'active' === $customer['status'] ) {
		$active_count++;
	} else {
		$inactive_count++;
	}
}
?>

<div class="wrap dispensary-wp-admin">

	<h1>
		<?php esc_html_e( 'Customers', 'dispensary-wp' ); ?>

		<?php if ( current_user_can( 'dispensary_manage_customers' ) ) : ?>
			<a
				href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-customers' ) ); ?>"
				class="page-title-action"
			>
				<?php esc_html_e( 'Add New', 'dispensary-wp' ); ?>
			</a>
		<?php endif; ?>
	</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( $error ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html( $error ); ?></p>
		</div>
	<?php endif; ?>

	<div
		style="
			display:grid;
			grid-template-columns:repeat(3,minmax(0,1fr));
			gap:15px;
			margin:20px 0;
		"
	>

		<div class="dispensary-wp-panel">
			<h2><?php echo esc_html( count( $customers ) ); ?></h2>
			<p><?php esc_html_e( 'Total Customers', 'dispensary-wp' ); ?></p>
		</div>

		<div class="dispensary-wp-panel">
			<h2><?php echo esc_html( $active_count ); ?></h2>
			<p><?php esc_html_e( 'Active Customers', 'dispensary-wp' ); ?></p>
		</div>

		<div class="dispensary-wp-panel">
			<h2><?php echo esc_html( $inactive_count ); ?></h2>
			<p><?php esc_html_e( 'Inactive Customers', 'dispensary-wp' ); ?></p>
		</div>

	</div>

	<?php if ( current_user_can( 'dispensary_manage_customers' ) ) : ?>

		<div class="dispensary-wp-panel">

			<h2>
				<?php
				echo $edit_customer
					? esc_html__( 'Edit Customer', 'dispensary-wp' )
					: esc_html__( 'Add Customer', 'dispensary-wp' );
				?>
			</h2>

			<form method="post">

				<?php
				wp_nonce_field(
					'dispensary_customer_action',
					'dispensary_customer_nonce'
				);
				?>

				<input
					type="hidden"
					name="dispensary_customer_action"
					value="<?php echo $edit_customer ? 'update' : 'create'; ?>"
				>

				<?php if ( $edit_customer ) : ?>
					<input
						type="hidden"
						name="customer_id"
						value="<?php echo esc_attr( $edit_customer['id'] ); ?>"
					>
				<?php endif; ?>

				<table class="form-table">

					<tr>
						<th>
							<label for="first_name">
								<?php esc_html_e( 'First Name', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input
								type="text"
								id="first_name"
								name="first_name"
								class="regular-text"
								required
								value="<?php echo esc_attr( $edit_customer['first_name'] ?? '' ); ?>"
							>
						</td>
					</tr>

					<tr>
						<th>
							<label for="last_name">
								<?php esc_html_e( 'Last Name', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input
								type="text"
								id="last_name"
								name="last_name"
								class="regular-text"
								value="<?php echo esc_attr( $edit_customer['last_name'] ?? '' ); ?>"
							>
						</td>
					</tr>

					<tr>
						<th>
							<label for="email">
								<?php esc_html_e( 'Email', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input
								type="email"
								id="email"
								name="email"
								class="regular-text"
								value="<?php echo esc_attr( $edit_customer['email'] ?? '' ); ?>"
							>
						</td>
					</tr>

					<tr>
						<th>
							<label for="phone">
								<?php esc_html_e( 'Phone', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<input
								type="text"
								id="phone"
								name="phone"
								class="regular-text"
								value="<?php echo esc_attr( $edit_customer['phone'] ?? '' ); ?>"
							>
						</td>
					</tr>

					<tr>
						<th>
							<label for="status">
								<?php esc_html_e( 'Status', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<select id="status" name="status">
								<option
									value="active"
									<?php selected( $edit_customer['status'] ?? 'active', 'active' ); ?>
								>
									<?php esc_html_e( 'Active', 'dispensary-wp' ); ?>
								</option>

								<option
									value="inactive"
									<?php selected( $edit_customer['status'] ?? '', 'inactive' ); ?>
								>
									<?php esc_html_e( 'Inactive', 'dispensary-wp' ); ?>
								</option>
							</select>
						</td>
					</tr>

					<tr>
						<th>
							<label for="notes">
								<?php esc_html_e( 'Notes', 'dispensary-wp' ); ?>
							</label>
						</th>
						<td>
							<textarea
								id="notes"
								name="notes"
								rows="5"
								class="large-text"
							><?php echo esc_textarea( $edit_customer['notes'] ?? '' ); ?></textarea>
						</td>
					</tr>

				</table>

				<?php submit_button(
					$edit_customer
						? __( 'Update Customer', 'dispensary-wp' )
						: __( 'Add Customer', 'dispensary-wp' )
				); ?>

			</form>

		</div>

	<?php endif; ?>

	<div class="dispensary-wp-panel">

		<h2><?php esc_html_e( 'Customer List', 'dispensary-wp' ); ?></h2>

		<form method="get" style="margin-bottom:15px;">

			<input
				type="hidden"
				name="page"
				value="dispensary-customers"
			>

			<input
				type="search"
				name="s"
				value="<?php echo esc_attr( $search ); ?>"
				placeholder="<?php esc_attr_e( 'Search name, email or phone...', 'dispensary-wp' ); ?>"
				class="regular-text"
			>

			<?php submit_button(
				__( 'Search', 'dispensary-wp' ),
				'secondary',
				'',
				false
			); ?>

			<?php if ( $search ) : ?>
				<a
					class="button"
					href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-customers' ) ); ?>"
				>
					<?php esc_html_e( 'Clear', 'dispensary-wp' ); ?>
				</a>
			<?php endif; ?>

		</form>

		<table class="widefat striped">

			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Email', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Phone', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Status', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Created', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'dispensary-wp' ); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php if ( empty( $customers ) ) : ?>

					<tr>
						<td colspan="7">
							<?php esc_html_e( 'No customers found.', 'dispensary-wp' ); ?>
						</td>
					</tr>

				<?php else : ?>

					<?php foreach ( $customers as $customer ) : ?>

						<tr>

							<td>
								<?php echo esc_html( $customer['id'] ); ?>
							</td>

							<td>
								<strong>
									<?php
									echo esc_html(
										trim(
											$customer['first_name'] . ' ' .
											$customer['last_name']
										)
									);
									?>
								</strong>
							</td>

							<td>
								<?php echo esc_html( $customer['email'] ); ?>
							</td>

							<td>
								<?php echo esc_html( $customer['phone'] ); ?>
							</td>

							<td>
								<?php
								echo 'active' === $customer['status']
									? '<span style="color:green;font-weight:600;">Active</span>'
									: '<span style="color:#777;">Inactive</span>';
								?>
							</td>

							<td>
								<?php echo esc_html( $customer['created_at'] ); ?>
							</td>

							<td>

								<?php if ( current_user_can( 'dispensary_manage_customers' ) ) : ?>

									<a
										class="button button-small"
										href="<?php echo esc_url(
											add_query_arg(
												array(
													'page' => 'dispensary-customers',
													'edit' => $customer['id'],
												),
												admin_url( 'admin.php' )
											)
										); ?>"
									>
										<?php esc_html_e( 'Edit', 'dispensary-wp' ); ?>
									</a>

									<form
										method="post"
										style="display:inline-block;"
										onsubmit="return confirm('<?php echo esc_js( __( 'Delete this customer?', 'dispensary-wp' ) ); ?>');"
									>

										<?php
										wp_nonce_field(
											'dispensary_customer_action',
											'dispensary_customer_nonce'
										);
										?>

										<input
											type="hidden"
											name="dispensary_customer_action"
											value="delete"
										>

										<input
											type="hidden"
											name="customer_id"
											value="<?php echo esc_attr( $customer['id'] ); ?>"
										>

										<button
											type="submit"
											class="button button-small"
										>
											<?php esc_html_e( 'Delete', 'dispensary-wp' ); ?>
										</button>

									</form>

								<?php endif; ?>

								<?php
								$history = Customer_History::all(
									$customer['id'],
									20
								);
								?>

								<?php if ( ! empty( $history ) ) : ?>

									<details style="margin-top:8px;">

										<summary>
											<?php esc_html_e( 'History', 'dispensary-wp' ); ?>
										</summary>

										<ul style="margin-left:15px;">

											<?php foreach ( $history as $event ) : ?>

												<li>
													<strong>
														<?php echo esc_html( $event['event_type'] ); ?>
													</strong>
													-
													<?php echo esc_html( $event['description'] ); ?>
													<small>
														(<?php echo esc_html( $event['created_at'] ); ?>)
													</small>
												</li>

											<?php endforeach; ?>

										</ul>

									</details>

								<?php endif; ?>

							</td>

						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			</tbody>

		</table>

	</div>

</div>
