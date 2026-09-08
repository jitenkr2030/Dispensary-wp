<?php
/**
 * Loyalty management admin view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Modules\Loyalty\Loyalty;
use Dispensary_WP\Modules\Customers\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_view_loyalty' ) ) {
	wp_die(
		esc_html__( 'You do not have permission to view loyalty management.', 'dispensary-wp' )
	);
}

$can_manage = current_user_can( 'dispensary_manage_loyalty' );

$notice       = '';
$error        = '';
$selected_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'members';

if ( ! in_array( $selected_tab, array( 'members', 'points', 'rewards' ), true ) ) {
	$selected_tab = 'members';
}

/*
 * Handle loyalty actions.
 */
if ( 'POST' === $_SERVER['REQUEST_METHOD'] && $can_manage ) {

	if (
		! isset( $_POST['dispensary_loyalty_nonce'] ) ||
		! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['dispensary_loyalty_nonce'] ) ),
			'dispensary_loyalty_action'
		)
	) {
		$error = __( 'Security check failed.', 'dispensary-wp' );
	} else {

		$action = isset( $_POST['loyalty_action'] )
			? sanitize_key( wp_unslash( $_POST['loyalty_action'] ) )
			: '';

		switch ( $action ) {

			case 'create_member':
				$customer_id = isset( $_POST['customer_id'] )
					? absint( $_POST['customer_id'] )
					: 0;

				$result = Loyalty::create_member(
					array(
						'customer_id'    => $customer_id,
						'member_code'    => isset( $_POST['member_code'] )
							? sanitize_text_field( wp_unslash( $_POST['member_code'] ) )
							: '',
						'tier'           => isset( $_POST['tier'] )
							? sanitize_text_field( wp_unslash( $_POST['tier'] ) )
							: 'standard',
						'points_balance' => isset( $_POST['points_balance'] )
							? absint( $_POST['points_balance'] )
							: 0,
						'status'         => isset( $_POST['status'] )
							? sanitize_key( wp_unslash( $_POST['status'] ) )
							: 'active',
					)
				);

				if ( is_wp_error( $result ) ) {
					$error = $result->get_error_message();
				} else {
					$notice = __( 'Loyalty member created successfully.', 'dispensary-wp' );
				}
				break;

			case 'add_points':
				$result = Loyalty::add_points(
					isset( $_POST['member_id'] ) ? absint( $_POST['member_id'] ) : 0,
					isset( $_POST['points'] ) ? absint( $_POST['points'] ) : 0,
					isset( $_POST['reason'] )
						? sanitize_text_field( wp_unslash( $_POST['reason'] ) )
						: '',
					isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0
				);

				if ( is_wp_error( $result ) ) {
					$error = $result->get_error_message();
				} else {
					$notice = __( 'Points added successfully.', 'dispensary-wp' );
				}
				break;

			case 'redeem_points':
				$result = Loyalty::redeem_points(
					isset( $_POST['member_id'] ) ? absint( $_POST['member_id'] ) : 0,
					isset( $_POST['points'] ) ? absint( $_POST['points'] ) : 0,
					isset( $_POST['reason'] )
						? sanitize_text_field( wp_unslash( $_POST['reason'] ) )
						: '',
					isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0
				);

				if ( is_wp_error( $result ) ) {
					$error = $result->get_error_message();
				} else {
					$notice = __( 'Points redeemed successfully.', 'dispensary-wp' );
				}
				break;

			case 'create_reward':
				$result = Loyalty::create_reward(
					array(
						'name'            => isset( $_POST['name'] )
							? sanitize_text_field( wp_unslash( $_POST['name'] ) )
							: '',
						'description'     => isset( $_POST['description'] )
							? sanitize_textarea_field( wp_unslash( $_POST['description'] ) )
							: '',
						'points_required' => isset( $_POST['points_required'] )
							? absint( $_POST['points_required'] )
							: 0,
						'reward_type'     => isset( $_POST['reward_type'] )
							? sanitize_key( wp_unslash( $_POST['reward_type'] ) )
							: 'discount',
						'value'           => isset( $_POST['value'] )
							? (float) $_POST['value']
							: 0,
						'status'          => isset( $_POST['reward_status'] )
							? sanitize_key( wp_unslash( $_POST['reward_status'] ) )
							: 'active',
					)
				);

				if ( is_wp_error( $result ) ) {
					$error = $result->get_error_message();
				} else {
					$notice = __( 'Reward created successfully.', 'dispensary-wp' );
				}
				break;
		}
	}
}

$members  = Loyalty::list_members( array( 'limit' => 200 ) );
$rewards  = Loyalty::list_rewards( '' );
$customers = array();

if ( class_exists( Customer::class ) && method_exists( Customer::class, 'all' ) ) {
	$customers = Customer::all(
		array(
			'limit' => 200,
		)
	);
}

$total_members   = count( $members );
$active_members  = 0;
$total_points    = 0;

foreach ( $members as $member ) {
	if ( 'active' === $member->status ) {
		$active_members++;
	}

	$total_points += (int) $member->points_balance;
}
?>

<div class="wrap dispensary-wp-admin">

	<h1><?php esc_html_e( 'Loyalty Management', 'dispensary-wp' ); ?></h1>

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

	<div class="dispensary-wp-cards">

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Total Members', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $total_members ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Active Members', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $active_members ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Points Outstanding', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( number_format_i18n( $total_points ) ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Active Rewards', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( count( $rewards ) ); ?></strong>
		</div>

	</div>

	<nav class="nav-tab-wrapper">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-loyalty&tab=members' ) ); ?>"
			class="nav-tab <?php echo 'members' === $selected_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Members', 'dispensary-wp' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-loyalty&tab=points' ) ); ?>"
			class="nav-tab <?php echo 'points' === $selected_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Points', 'dispensary-wp' ); ?>
		</a>

		<a href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-loyalty&tab=rewards' ) ); ?>"
			class="nav-tab <?php echo 'rewards' === $selected_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Rewards', 'dispensary-wp' ); ?>
		</a>
	</nav>

	<?php if ( 'members' === $selected_tab ) : ?>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Create Loyalty Member', 'dispensary-wp' ); ?></h2>

			<?php if ( $can_manage ) : ?>
				<form method="post">
					<?php wp_nonce_field( 'dispensary_loyalty_action', 'dispensary_loyalty_nonce' ); ?>
					<input type="hidden" name="loyalty_action" value="create_member">

					<table class="form-table">
						<tr>
							<th><label for="customer_id"><?php esc_html_e( 'Customer', 'dispensary-wp' ); ?></label></th>
							<td>
								<select name="customer_id" id="customer_id" required>
									<option value=""><?php esc_html_e( 'Select customer', 'dispensary-wp' ); ?></option>
									<?php foreach ( $customers as $customer ) : ?>
										<option value="<?php echo esc_attr( $customer->id ); ?>">
											<?php
											echo esc_html(
												trim(
													( $customer->first_name ?? '' ) . ' ' .
													( $customer->last_name ?? '' )
												)
											);
											?>
											<?php if ( ! empty( $customer->email ) ) : ?>
												— <?php echo esc_html( $customer->email ); ?>
											<?php endif; ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>

						<tr>
							<th><label for="member_code"><?php esc_html_e( 'Member Code', 'dispensary-wp' ); ?></label></th>
							<td>
								<input type="text" name="member_code" id="member_code" class="regular-text"
									placeholder="LOY-XXXXXXX">
							</td>
						</tr>

						<tr>
							<th><label for="tier"><?php esc_html_e( 'Tier', 'dispensary-wp' ); ?></label></th>
							<td>
								<select name="tier" id="tier">
									<option value="standard">Standard</option>
									<option value="silver">Silver</option>
									<option value="gold">Gold</option>
									<option value="platinum">Platinum</option>
								</select>
							</td>
						</tr>

						<tr>
							<th><label for="points_balance"><?php esc_html_e( 'Initial Points', 'dispensary-wp' ); ?></label></th>
							<td>
								<input type="number" name="points_balance" id="points_balance"
									value="0" min="0">
							</td>
						</tr>

						<tr>
							<th><label for="status"><?php esc_html_e( 'Status', 'dispensary-wp' ); ?></label></th>
							<td>
								<select name="status" id="status">
									<option value="active">Active</option>
									<option value="inactive">Inactive</option>
								</select>
							</td>
						</tr>
					</table>

					<?php submit_button( __( 'Create Member', 'dispensary-wp' ) ); ?>
				</form>
			<?php endif; ?>

		</div>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Loyalty Members', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th>ID</th>
						<th><?php esc_html_e( 'Member Code', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Customer ID', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Tier', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Points', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Status', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Created', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php if ( empty( $members ) ) : ?>
						<tr>
							<td colspan="7">
								<?php esc_html_e( 'No loyalty members found.', 'dispensary-wp' ); ?>
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $members as $member ) : ?>
							<tr>
								<td><?php echo esc_html( $member->id ); ?></td>
								<td><strong><?php echo esc_html( $member->member_code ); ?></strong></td>
								<td><?php echo esc_html( $member->customer_id ); ?></td>
								<td><?php echo esc_html( ucfirst( $member->tier ) ); ?></td>
								<td><?php echo esc_html( number_format_i18n( (int) $member->points_balance ) ); ?></td>
								<td><?php echo esc_html( ucfirst( $member->status ) ); ?></td>
								<td><?php echo esc_html( $member->created_at ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>

			</table>

		</div>

	<?php elseif ( 'points' === $selected_tab ) : ?>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Manage Points', 'dispensary-wp' ); ?></h2>

			<?php if ( $can_manage ) : ?>

				<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;">

					<div>
						<h3><?php esc_html_e( 'Add / Earn Points', 'dispensary-wp' ); ?></h3>

						<form method="post">
							<?php wp_nonce_field( 'dispensary_loyalty_action', 'dispensary_loyalty_nonce' ); ?>
							<input type="hidden" name="loyalty_action" value="add_points">

							<p>
								<label>Member</label><br>
								<select name="member_id" required>
									<option value="">Select member</option>
									<?php foreach ( $members as $member ) : ?>
										<option value="<?php echo esc_attr( $member->id ); ?>">
											<?php echo esc_html( $member->member_code ); ?>
											— <?php echo esc_html( $member->points_balance ); ?> points
										</option>
									<?php endforeach; ?>
								</select>
							</p>

							<p>
								<label>Points</label><br>
								<input type="number" name="points" min="1" required>
							</p>

							<p>
								<label>Reason</label><br>
								<input type="text" name="reason" class="regular-text">
							</p>

							<p>
								<label>Order ID</label><br>
								<input type="number" name="order_id" min="0">
							</p>

							<?php submit_button( __( 'Add Points', 'dispensary-wp' ) ); ?>
						</form>
					</div>

					<div>
						<h3><?php esc_html_e( 'Redeem Points', 'dispensary-wp' ); ?></h3>

						<form method="post">
							<?php wp_nonce_field( 'dispensary_loyalty_action', 'dispensary_loyalty_nonce' ); ?>
							<input type="hidden" name="loyalty_action" value="redeem_points">

							<p>
								<label>Member</label><br>
								<select name="member_id" required>
									<option value="">Select member</option>
									<?php foreach ( $members as $member ) : ?>
										<option value="<?php echo esc_attr( $member->id ); ?>">
											<?php echo esc_html( $member->member_code ); ?>
											— <?php echo esc_html( $member->points_balance ); ?> points
										</option>
									<?php endforeach; ?>
								</select>
							</p>

							<p>
								<label>Points</label><br>
								<input type="number" name="points" min="1" required>
							</p>

							<p>
								<label>Reason</label><br>
								<input type="text" name="reason" class="regular-text">
							</p>

							<p>
								<label>Order ID</label><br>
								<input type="number" name="order_id" min="0">
							</p>

							<?php submit_button( __( 'Redeem Points', 'dispensary-wp' ) ); ?>
						</form>
					</div>

				</div>

			<?php endif; ?>

		</div>

	<?php else : ?>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Create Reward', 'dispensary-wp' ); ?></h2>

			<?php if ( $can_manage ) : ?>

				<form method="post">

					<?php wp_nonce_field( 'dispensary_loyalty_action', 'dispensary_loyalty_nonce' ); ?>
					<input type="hidden" name="loyalty_action" value="create_reward">

					<table class="form-table">

						<tr>
							<th><label for="reward_name">Name</label></th>
							<td>
								<input type="text" name="name" id="reward_name"
									class="regular-text" required>
							</td>
						</tr>

						<tr>
							<th><label for="reward_description">Description</label></th>
							<td>
								<textarea name="description" id="reward_description"
									rows="4" class="large-text"></textarea>
							</td>
						</tr>

						<tr>
							<th><label for="points_required">Points Required</label></th>
							<td>
								<input type="number" name="points_required"
									id="points_required" min="1" required>
							</td>
						</tr>

						<tr>
							<th><label for="reward_type">Reward Type</label></th>
							<td>
								<select name="reward_type" id="reward_type">
									<option value="discount">Discount</option>
									<option value="free_product">Free Product</option>
									<option value="cash_value">Cash Value</option>
									<option value="custom">Custom</option>
								</select>
							</td>
						</tr>

						<tr>
							<th><label for="reward_value">Value</label></th>
							<td>
								<input type="number" step="0.01" min="0"
									name="value" id="reward_value" value="0">
							</td>
						</tr>

						<tr>
							<th><label for="reward_status">Status</label></th>
							<td>
								<select name="reward_status" id="reward_status">
									<option value="active">Active</option>
									<option value="inactive">Inactive</option>
								</select>
							</td>
						</tr>

					</table>

					<?php submit_button( __( 'Create Reward', 'dispensary-wp' ) ); ?>

				</form>

			<?php endif; ?>

		</div>

		<div class="dispensary-wp-panel">

			<h2><?php esc_html_e( 'Rewards', 'dispensary-wp' ); ?></h2>

			<table class="widefat striped">

				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Points Required</th>
						<th>Type</th>
						<th>Value</th>
						<th>Status</th>
						<th>Created</th>
					</tr>
				</thead>

				<tbody>

					<?php if ( empty( $rewards ) ) : ?>

						<tr>
							<td colspan="7">No rewards found.</td>
						</tr>

					<?php else : ?>

						<?php foreach ( $rewards as $reward ) : ?>

							<tr>
								<td><?php echo esc_html( $reward->id ); ?></td>
								<td><strong><?php echo esc_html( $reward->name ); ?></strong></td>
								<td><?php echo esc_html( number_format_i18n( $reward->points_required ) ); ?></td>
								<td><?php echo esc_html( ucfirst( str_replace( '_', ' ', $reward->reward_type ) ) ); ?></td>
								<td><?php echo esc_html( $reward->value ); ?></td>
								<td><?php echo esc_html( ucfirst( $reward->status ) ); ?></td>
								<td><?php echo esc_html( $reward->created_at ); ?></td>
							</tr>

						<?php endforeach; ?>

					<?php endif; ?>

				</tbody>

			</table>

		</div>

	<?php endif; ?>

</div>
