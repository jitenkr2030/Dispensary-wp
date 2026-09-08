<?php
/**
 * Staff management admin view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Modules\Staff\Staff;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_view_staff' ) ) {
	wp_die(
		esc_html__( 'You do not have permission to view staff.', 'dispensary-wp' ),
		esc_html__( 'Permission Denied', 'dispensary-wp' ),
		array( 'response' => 403 )
	);
}

$can_manage = current_user_can( 'dispensary_manage_staff' );
$notice      = '';
$error       = '';

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {

	check_admin_referer( 'dispensary_staff_action', 'dispensary_staff_nonce' );

	if ( ! $can_manage ) {
		$error = __( 'You do not have permission to manage staff.', 'dispensary-wp' );
	} else {

		$action = isset( $_POST['staff_action'] )
			? sanitize_key( wp_unslash( $_POST['staff_action'] ) )
			: '';

		if ( 'create' === $action ) {

			$result = Staff::create_staff(
				array(
					'user_id'     => absint( $_POST['user_id'] ?? 0 ),
					'employee_id' => sanitize_text_field( wp_unslash( $_POST['employee_id'] ?? '' ) ),
					'first_name'  => sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) ),
					'last_name'   => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
					'email'       => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
					'phone'       => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
					'job_title'   => sanitize_text_field( wp_unslash( $_POST['job_title'] ?? '' ) ),
					'department'  => sanitize_text_field( wp_unslash( $_POST['department'] ?? '' ) ),
					'status'      => sanitize_key( wp_unslash( $_POST['status'] ?? 'active' ) ),
					'hire_date'   => sanitize_text_field( wp_unslash( $_POST['hire_date'] ?? '' ) ),
					'manager_id'  => absint( $_POST['manager_id'] ?? 0 ),
					'notes'       => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ),
				)
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __( 'Staff member created successfully.', 'dispensary-wp' );
			}
		}

		if ( 'update' === $action ) {

			$result = Staff::update_staff(
				absint( $_POST['staff_id'] ?? 0 ),
				array(
					'user_id'     => absint( $_POST['user_id'] ?? 0 ),
					'employee_id' => sanitize_text_field( wp_unslash( $_POST['employee_id'] ?? '' ) ),
					'first_name'  => sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) ),
					'last_name'   => sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) ),
					'email'       => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
					'phone'       => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
					'job_title'   => sanitize_text_field( wp_unslash( $_POST['job_title'] ?? '' ) ),
					'department'  => sanitize_text_field( wp_unslash( $_POST['department'] ?? '' ) ),
					'status'      => sanitize_key( wp_unslash( $_POST['status'] ?? 'active' ) ),
					'hire_date'   => sanitize_text_field( wp_unslash( $_POST['hire_date'] ?? '' ) ),
					'manager_id'  => absint( $_POST['manager_id'] ?? 0 ),
					'notes'       => sanitize_textarea_field( wp_unslash( $_POST['notes'] ?? '' ) ),
				)
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __( 'Staff member updated successfully.', 'dispensary-wp' );
			}
		}

		if ( 'delete' === $action ) {

			$result = Staff::delete_staff(
				absint( $_POST['staff_id'] ?? 0 )
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __( 'Staff member deleted.', 'dispensary-wp' );
			}
		}
	}
}

$staff = Staff::list_staff(
	array(
		'limit' => 500,
	)
);

$total_staff      = is_array( $staff ) ? count( $staff ) : 0;
$active_staff     = 0;
$inactive_staff   = 0;
$suspended_staff  = 0;

if ( is_array( $staff ) ) {
	foreach ( $staff as $member ) {
		if ( 'active' === $member->status ) {
			$active_staff++;
		} elseif ( 'inactive' === $member->status ) {
			$inactive_staff++;
		} elseif ( 'suspended' === $member->status ) {
			$suspended_staff++;
		}
	}
}

?>

<div class="wrap dispensary-wp-admin">

	<h1><?php esc_html_e( 'Staff Management', 'dispensary-wp' ); ?></h1>

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
			<h3><?php esc_html_e( 'Total Staff', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $total_staff ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Active', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $active_staff ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Inactive', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $inactive_staff ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Suspended', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $suspended_staff ); ?></strong>
		</div>

	</div>

	<?php if ( $can_manage ) : ?>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Add Staff Member', 'dispensary-wp' ); ?></h2>

			<form method="post">

				<input type="hidden" name="staff_action" value="create">

				<?php wp_nonce_field( 'dispensary_staff_action', 'dispensary_staff_nonce' ); ?>

				<table class="form-table">

					<tr>
						<th><label for="employee_id"><?php esc_html_e( 'Employee ID', 'dispensary-wp' ); ?></label></th>
						<td><input type="text" name="employee_id" id="employee_id" class="regular-text"></td>
					</tr>

					<tr>
						<th><label for="first_name"><?php esc_html_e( 'First Name', 'dispensary-wp' ); ?></label></th>
						<td><input type="text" name="first_name" id="first_name" class="regular-text" required></td>
					</tr>

					<tr>
						<th><label for="last_name"><?php esc_html_e( 'Last Name', 'dispensary-wp' ); ?></label></th>
						<td><input type="text" name="last_name" id="last_name" class="regular-text"></td>
					</tr>

					<tr>
						<th><label for="email"><?php esc_html_e( 'Email', 'dispensary-wp' ); ?></label></th>
						<td><input type="email" name="email" id="email" class="regular-text"></td>
					</tr>

					<tr>
						<th><label for="phone"><?php esc_html_e( 'Phone', 'dispensary-wp' ); ?></label></th>
						<td><input type="text" name="phone" id="phone" class="regular-text"></td>
					</tr>

					<tr>
						<th><label for="job_title"><?php esc_html_e( 'Job Title', 'dispensary-wp' ); ?></label></th>
						<td><input type="text" name="job_title" id="job_title" class="regular-text"></td>
					</tr>

					<tr>
						<th><label for="department"><?php esc_html_e( 'Department', 'dispensary-wp' ); ?></label></th>
						<td><input type="text" name="department" id="department" class="regular-text"></td>
					</tr>

					<tr>
						<th><label for="status"><?php esc_html_e( 'Status', 'dispensary-wp' ); ?></label></th>
						<td>
							<select name="status" id="status">
								<option value="active"><?php esc_html_e( 'Active', 'dispensary-wp' ); ?></option>
								<option value="inactive"><?php esc_html_e( 'Inactive', 'dispensary-wp' ); ?></option>
								<option value="suspended"><?php esc_html_e( 'Suspended', 'dispensary-wp' ); ?></option>
								<option value="terminated"><?php esc_html_e( 'Terminated', 'dispensary-wp' ); ?></option>
							</select>
						</td>
					</tr>

					<tr>
						<th><label for="hire_date"><?php esc_html_e( 'Hire Date', 'dispensary-wp' ); ?></label></th>
						<td><input type="date" name="hire_date" id="hire_date"></td>
					</tr>

					<tr>
						<th><label for="notes"><?php esc_html_e( 'Notes', 'dispensary-wp' ); ?></label></th>
						<td>
							<textarea name="notes" id="notes" rows="4" class="large-text"></textarea>
						</td>
					</tr>

				</table>

				<?php submit_button( __( 'Add Staff Member', 'dispensary-wp' ) ); ?>

			</form>

		</div>

	<?php endif; ?>

	<div class="dispensary-wp-panel">

		<h2><?php esc_html_e( 'Staff Members', 'dispensary-wp' ); ?></h2>

		<table class="widefat striped">

			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Employee ID', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Staff', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Contact', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Job Title', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Department', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Status', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Hire Date', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'dispensary-wp' ); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php if ( empty( $staff ) ) : ?>

					<tr>
						<td colspan="9">
							<?php esc_html_e( 'No staff members found.', 'dispensary-wp' ); ?>
						</td>
					</tr>

				<?php else : ?>

					<?php foreach ( $staff as $member ) : ?>

						<tr>

							<td><?php echo esc_html( $member->id ); ?></td>

							<td><?php echo esc_html( $member->employee_id ?: '—' ); ?></td>

							<td>
								<strong>
									<?php
									echo esc_html(
										trim(
											$member->first_name . ' ' .
											$member->last_name
										)
									);
									?>
								</strong>
							</td>

							<td>
								<?php echo esc_html( $member->email ?: '—' ); ?><br>
								<?php echo esc_html( $member->phone ?: '—' ); ?>
							</td>

							<td><?php echo esc_html( $member->job_title ?: '—' ); ?></td>

							<td><?php echo esc_html( $member->department ?: '—' ); ?></td>

							<td>
								<?php echo esc_html( ucfirst( $member->status ) ); ?>
							</td>

							<td><?php echo esc_html( $member->hire_date ?: '—' ); ?></td>

							<td>

								<?php if ( $can_manage ) : ?>

									<details>
										<summary class="button">
											<?php esc_html_e( 'Edit', 'dispensary-wp' ); ?>
										</summary>

										<form method="post" style="margin-top:10px;">

											<input type="hidden" name="staff_action" value="update">

											<input
												type="hidden"
												name="staff_id"
												value="<?php echo esc_attr( $member->id ); ?>"
											>

											<?php wp_nonce_field( 'dispensary_staff_action', 'dispensary_staff_nonce' ); ?>

											<p>
												<input
													type="text"
													name="employee_id"
													value="<?php echo esc_attr( $member->employee_id ); ?>"
													placeholder="<?php esc_attr_e( 'Employee ID', 'dispensary-wp' ); ?>"
												>
											</p>

											<p>
												<input
													type="text"
													name="first_name"
													value="<?php echo esc_attr( $member->first_name ); ?>"
													placeholder="<?php esc_attr_e( 'First Name', 'dispensary-wp' ); ?>"
													required
												>
											</p>

											<p>
												<input
													type="text"
													name="last_name"
													value="<?php echo esc_attr( $member->last_name ); ?>"
													placeholder="<?php esc_attr_e( 'Last Name', 'dispensary-wp' ); ?>"
												>
											</p>

											<p>
												<input
													type="email"
													name="email"
													value="<?php echo esc_attr( $member->email ); ?>"
													placeholder="<?php esc_attr_e( 'Email', 'dispensary-wp' ); ?>"
												>
											</p>

											<p>
												<input
													type="text"
													name="phone"
													value="<?php echo esc_attr( $member->phone ); ?>"
													placeholder="<?php esc_attr_e( 'Phone', 'dispensary-wp' ); ?>"
												>
											</p>

											<p>
												<input
													type="text"
													name="job_title"
													value="<?php echo esc_attr( $member->job_title ); ?>"
													placeholder="<?php esc_attr_e( 'Job Title', 'dispensary-wp' ); ?>"
												>
											</p>

											<p>
												<input
													type="text"
													name="department"
													value="<?php echo esc_attr( $member->department ); ?>"
													placeholder="<?php esc_attr_e( 'Department', 'dispensary-wp' ); ?>"
												>
											</p>

											<p>
												<select name="status">
													<?php
													foreach (
														array(
															'active',
															'inactive',
															'suspended',
															'terminated',
														) as $status
													) :
														?>
														<option
															value="<?php echo esc_attr( $status ); ?>"
															<?php selected( $member->status, $status ); ?>
														>
															<?php echo esc_html( ucfirst( $status ) ); ?>
														</option>
													<?php endforeach; ?>
												</select>
											</p>

											<p>
												<input
													type="date"
													name="hire_date"
													value="<?php echo esc_attr( $member->hire_date ); ?>"
												>
											</p>

											<p>
												<textarea
													name="notes"
													rows="3"
													placeholder="<?php esc_attr_e( 'Notes', 'dispensary-wp' ); ?>"
												><?php echo esc_textarea( $member->notes ); ?></textarea>
											</p>

											<p>
												<button type="submit" class="button button-primary">
													<?php esc_html_e( 'Save Changes', 'dispensary-wp' ); ?>
												</button>
											</p>

										</form>

									</details>

									<form
										method="post"
										style="display:inline-block;margin-top:8px;"
										onsubmit="return confirm('<?php echo esc_js( __( 'Delete this staff member?', 'dispensary-wp' ) ); ?>');"
									>

										<input type="hidden" name="staff_action" value="delete">

										<input
											type="hidden"
											name="staff_id"
											value="<?php echo esc_attr( $member->id ); ?>"
										>

										<?php wp_nonce_field( 'dispensary_staff_action', 'dispensary_staff_nonce' ); ?>

										<button type="submit" class="button button-link-delete">
											<?php esc_html_e( 'Delete', 'dispensary-wp' ); ?>
										</button>

									</form>

								<?php else : ?>

									—

								<?php endif; ?>

							</td>

						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			</tbody>

		</table>

	</div>

</div>
