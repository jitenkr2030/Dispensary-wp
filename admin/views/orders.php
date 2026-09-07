<?php
/**
 * Orders admin view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Modules\Orders\Order;
use Dispensary_WP\Modules\Orders\Orders;
use Dispensary_WP\Modules\Orders\Order_Status;
use Dispensary_WP\Modules\Customers\Customer;
use Dispensary_WP\Modules\Products\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_view_orders' ) ) {
	wp_die(
		esc_html__( 'You do not have permission to view orders.', 'dispensary-wp' )
	);
}

$order_service = new Orders();
$order_model   = new Order();

$notice = '';
$error  = '';

/*
 * Handle order actions.
 */
if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {

	$action = isset( $_POST['order_action'] )
		? sanitize_key( $_POST['order_action'] )
		: '';

	if (
		! isset( $_POST['dispensary_order_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['dispensary_order_nonce'] ) ),
			'dispensary_order_action'
		)
	) {
		$error = __( 'Security check failed.', 'dispensary-wp' );
	} elseif ( ! current_user_can( 'dispensary_manage_orders' ) ) {
		$error = __( 'You do not have permission to manage orders.', 'dispensary-wp' );
	} else {

		/*
		 * Create order.
		 */
		if ( 'create' === $action ) {

			$customer_id = isset( $_POST['customer_id'] )
				? absint( $_POST['customer_id'] )
				: 0;

			$product_ids = isset( $_POST['product_id'] )
				? array_map( 'absint', (array) $_POST['product_id'] )
				: array();

			$quantities = isset( $_POST['quantity'] )
				? array_map( 'absint', (array) $_POST['quantity'] )
				: array();

			$items = array();

			foreach ( $product_ids as $index => $product_id ) {

				$quantity = isset( $quantities[ $index ] )
					? absint( $quantities[ $index ] )
					: 0;

				if ( $product_id > 0 && $quantity > 0 ) {
					$items[] = array(
						'product_id' => $product_id,
						'quantity'   => $quantity,
					);
				}
			}

			$result = $order_service->create(
				array(
					'customer_id'    => $customer_id,
					'currency'       => 'USD',
					'discount_total' => isset( $_POST['discount_total'] )
						? (float) $_POST['discount_total']
						: 0,
					'tax_total'      => isset( $_POST['tax_total'] )
						? (float) $_POST['tax_total']
						: 0,
					'shipping_total' => isset( $_POST['shipping_total'] )
						? (float) $_POST['shipping_total']
						: 0,
					'customer_note'  => isset( $_POST['customer_note'] )
						? sanitize_textarea_field(
							wp_unslash( $_POST['customer_note'] )
						)
						: '',
					'admin_note'     => isset( $_POST['admin_note'] )
						? sanitize_textarea_field(
							wp_unslash( $_POST['admin_note'] )
						)
						: '',
				),
				$items
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = sprintf(
					/* translators: %d: order ID */
					__( 'Order #%d created successfully.', 'dispensary-wp' ),
					absint( $result )
				);
			}
		}

		/*
		 * Update order status.
		 */
		if ( 'status' === $action ) {

			$order_id = isset( $_POST['order_id'] )
				? absint( $_POST['order_id'] )
				: 0;

			$status = isset( $_POST['status'] )
				? sanitize_key( $_POST['status'] )
				: '';

			$result = $order_service->update_status(
				$order_id,
				$status,
				isset( $_POST['status_note'] )
					? sanitize_text_field(
						wp_unslash( $_POST['status_note'] )
					)
					: ''
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} elseif ( $result ) {
				$notice = __( 'Order status updated successfully.', 'dispensary-wp' );
			}
		}

		/*
		 * Cancel order.
		 */
		if ( 'cancel' === $action ) {

			$order_id = isset( $_POST['order_id'] )
				? absint( $_POST['order_id'] )
				: 0;

			$result = $order_service->cancel(
				$order_id,
				isset( $_POST['cancel_reason'] )
					? sanitize_text_field(
						wp_unslash( $_POST['cancel_reason'] )
					)
					: ''
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} elseif ( $result ) {
				$notice = __( 'Order cancelled successfully.', 'dispensary-wp' );
			}
		}

		/*
		 * Mark order paid.
		 */
		if ( 'paid' === $action ) {

			$order_id = isset( $_POST['order_id'] )
				? absint( $_POST['order_id'] )
				: 0;

			$result = $order_service->mark_paid(
				$order_id,
				array(
					'amount' => isset( $_POST['payment_amount'] )
						? (float) $_POST['payment_amount']
						: 0,
				)
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __( 'Payment recorded successfully.', 'dispensary-wp' );
			}
		}
	}
}

/*
 * Search/filter.
 */
$search = isset( $_GET['s'] )
	? sanitize_text_field( wp_unslash( $_GET['s'] ) )
	: '';

$status_filter = isset( $_GET['status'] )
	? sanitize_key( $_GET['status'] )
	: '';

$orders = $order_service->list_orders(
	array(
		'status' => $status_filter,
		'limit'  => 100,
	)
);

if ( is_wp_error( $orders ) ) {
	$orders = array();
	$error  = $orders->get_error_message();
}

$customers = ( new Customer() )->all(
	array(
		'limit' => 200,
	)
);

$products = ( new Product() )->all(
	array(
		'status' => 'active',
		'limit'  => 200,
	)
);

/*
 * Simple search on loaded orders.
 */
if ( $search && is_array( $orders ) ) {

	$orders = array_filter(
		$orders,
		function ( $order ) use ( $search ) {

			return (
				false !== stripos(
					(string) $order->order_number,
					$search
				)
				|| false !== stripos(
					(string) $order->customer_id,
					$search
				)
			);
		}
	);
}

/*
 * Dashboard counts.
 */
$total_orders = count( $orders );

$pending_orders = 0;
$completed_orders = 0;
$cancelled_orders = 0;
$total_sales = 0;

foreach ( $orders as $order ) {

	if ( 'pending' === $order->status ) {
		$pending_orders++;
	}

	if ( 'completed' === $order->status ) {
		$completed_orders++;
	}

	if ( 'cancelled' === $order->status ) {
		$cancelled_orders++;
	}

	$total_sales += (float) $order->total;
}
?>

<div class="wrap dispensary-wp-admin">

	<h1>
		<?php esc_html_e( 'Orders', 'dispensary-wp' ); ?>
	</h1>

	<?php if ( $notice ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $notice ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( $error ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php echo esc_html( $error ); ?></p>
		</div>
	<?php endif; ?>

	<div class="dispensary-wp-dashboard-cards">

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Total Orders', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $total_orders ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Pending', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $pending_orders ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Completed', 'dispensary-wp' ); ?></h3>
			<strong><?php echo esc_html( $completed_orders ); ?></strong>
		</div>

		<div class="dispensary-wp-card">
			<h3><?php esc_html_e( 'Sales Value', 'dispensary-wp' ); ?></h3>
			<strong>
				<?php echo esc_html( number_format_i18n( $total_sales, 2 ) ); ?>
			</strong>
		</div>

	</div>

	<?php if ( current_user_can( 'dispensary_manage_orders' ) ) : ?>

		<div class="dispensary-wp-panel">

			<h2>
				<?php esc_html_e( 'Create New Order', 'dispensary-wp' ); ?>
			</h2>

			<form method="post">

				<?php wp_nonce_field(
					'dispensary_order_action',
					'dispensary_order_nonce'
				); ?>

				<input type="hidden" name="order_action" value="create">

				<table class="form-table">

					<tr>
						<th>
							<label for="customer_id">
								<?php esc_html_e( 'Customer', 'dispensary-wp' ); ?>
							</label>
						</th>

						<td>
							<select name="customer_id" id="customer_id">

								<option value="0">
									<?php esc_html_e( 'Walk-in Customer', 'dispensary-wp' ); ?>
								</option>

								<?php foreach ( $customers as $customer ) : ?>

									<option value="<?php echo esc_attr( $customer->id ); ?>">
										<?php
										echo esc_html(
											trim(
												$customer->first_name . ' ' . $customer->last_name
											)
											. ' - '
											. $customer->email
										);
										?>
									</option>

								<?php endforeach; ?>

							</select>
						</td>
					</tr>

				</table>

				<h3><?php esc_html_e( 'Order Items', 'dispensary-wp' ); ?></h3>

				<table class="widefat striped">

					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'dispensary-wp' ); ?></th>
							<th><?php esc_html_e( 'Quantity', 'dispensary-wp' ); ?></th>
						</tr>
					</thead>

					<tbody id="dispensary-order-items">

						<tr>
							<td>
								<select name="product_id[]" required>

									<option value="">
										<?php esc_html_e( 'Select Product', 'dispensary-wp' ); ?>
									</option>

									<?php foreach ( $products as $product ) : ?>

										<option value="<?php echo esc_attr( $product->id ); ?>">
											<?php
											echo esc_html(
												$product->name
												. ' (' . $product->sku . ') - '
												. number_format_i18n(
													(float) $product->price,
													2
												)
											);
											?>
										</option>

									<?php endforeach; ?>

								</select>
							</td>

							<td>
								<input
									type="number"
									name="quantity[]"
									value="1"
									min="1"
									required
								>
							</td>
						</tr>

					</tbody>

				</table>

				<p>
					<button
						type="button"
						class="button"
						id="dispensary-add-order-item"
					>
						<?php esc_html_e( 'Add Another Item', 'dispensary-wp' ); ?>
					</button>
				</p>

				<table class="form-table">

					<tr>
						<th><?php esc_html_e( 'Discount', 'dispensary-wp' ); ?></th>
						<td>
							<input
								type="number"
								name="discount_total"
								value="0"
								min="0"
								step="0.01"
							>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Tax', 'dispensary-wp' ); ?></th>
						<td>
							<input
								type="number"
								name="tax_total"
								value="0"
								min="0"
								step="0.01"
							>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Shipping', 'dispensary-wp' ); ?></th>
						<td>
							<input
								type="number"
								name="shipping_total"
								value="0"
								min="0"
								step="0.01"
							>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Customer Note', 'dispensary-wp' ); ?></th>
						<td>
							<textarea name="customer_note" rows="3"></textarea>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Admin Note', 'dispensary-wp' ); ?></th>
						<td>
							<textarea name="admin_note" rows="3"></textarea>
						</td>
					</tr>

				</table>

				<?php submit_button( __( 'Create Order', 'dispensary-wp' ) ); ?>

			</form>

		</div>

	<?php endif; ?>

	<div class="dispensary-wp-panel">

		<h2><?php esc_html_e( 'Order List', 'dispensary-wp' ); ?></h2>

		<form method="get">

			<input type="hidden" name="page" value="dispensary-wp-orders">

			<input
				type="search"
				name="s"
				value="<?php echo esc_attr( $search ); ?>"
				placeholder="<?php esc_attr_e( 'Order number or customer ID', 'dispensary-wp' ); ?>"
			>

			<select name="status">

				<option value="">
					<?php esc_html_e( 'All Statuses', 'dispensary-wp' ); ?>
				</option>

				<?php foreach ( Order_Status::all() as $status ) : ?>

					<option
						value="<?php echo esc_attr( $status ); ?>"
						<?php selected( $status_filter, $status ); ?>
					>
						<?php echo esc_html( ucfirst( $status ) ); ?>
					</option>

				<?php endforeach; ?>

			</select>

			<?php submit_button(
				__( 'Filter', 'dispensary-wp' ),
				'secondary',
				'',
				false
			); ?>

		</form>

		<br>

		<table class="widefat striped">

			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Order', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Status', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Payment', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Total', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Date', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'dispensary-wp' ); ?></th>
				</tr>
			</thead>

			<tbody>

				<?php if ( empty( $orders ) ) : ?>

					<tr>
						<td colspan="8">
							<?php esc_html_e( 'No orders found.', 'dispensary-wp' ); ?>
						</td>
					</tr>

				<?php else : ?>

					<?php foreach ( $orders as $order ) : ?>

						<?php
						$customer_name = __( 'Walk-in Customer', 'dispensary-wp' );

						if ( ! empty( $order->customer_id ) ) {
							$customer = ( new Customer() )->find(
								$order->customer_id
							);

							if ( $customer ) {
								$customer_name = trim(
									$customer->first_name . ' ' . $customer->last_name
								);
							}
						}
						?>

						<tr>

							<td>
								<?php echo esc_html( $order->id ); ?>
							</td>

							<td>
								<strong>
									<?php echo esc_html( $order->order_number ); ?>
								</strong>
							</td>

							<td>
								<?php echo esc_html( $customer_name ); ?>
							</td>

							<td>
								<strong>
									<?php echo esc_html( ucfirst( $order->status ) ); ?>
								</strong>
							</td>

							<td>
								<?php echo esc_html( ucfirst( $order->payment_status ) ); ?>
							</td>

							<td>
								<?php
								echo esc_html(
									number_format_i18n(
										(float) $order->total,
										2
									)
								);
								?>
							</td>

							<td>
								<?php
								echo esc_html(
									mysql2date(
										get_option( 'date_format' ),
										$order->created_at
									)
								);
								?>
							</td>

							<td>

								<?php if ( current_user_can( 'dispensary_manage_orders' ) ) : ?>

									<form method="post" style="display:inline-block;">

										<?php wp_nonce_field(
											'dispensary_order_action',
											'dispensary_order_nonce'
										); ?>

										<input
											type="hidden"
											name="order_action"
											value="status"
										>

										<input
											type="hidden"
											name="order_id"
											value="<?php echo esc_attr( $order->id ); ?>"
										>

										<select name="status">

											<?php foreach ( Order_Status::all() as $status ) : ?>

												<option
													value="<?php echo esc_attr( $status ); ?>"
													<?php selected( $order->status, $status ); ?>
												>
													<?php echo esc_html( ucfirst( $status ) ); ?>
												</option>

											<?php endforeach; ?>

										</select>

										<button
											type="submit"
											class="button"
										>
											<?php esc_html_e( 'Update', 'dispensary-wp' ); ?>
										</button>

									</form>

									<?php if ( 'pending' === $order->payment_status ) : ?>

										<form
											method="post"
											style="display:inline-block;"
										>

											<?php wp_nonce_field(
												'dispensary_order_action',
												'dispensary_order_nonce'
											); ?>

											<input
												type="hidden"
												name="order_action"
												value="paid"
											>

											<input
												type="hidden"
												name="order_id"
												value="<?php echo esc_attr( $order->id ); ?>"
											>

											<input
												type="hidden"
												name="payment_amount"
												value="<?php echo esc_attr( $order->total ); ?>"
											>

											<button
												type="submit"
												class="button"
											>
												<?php esc_html_e( 'Mark Paid', 'dispensary-wp' ); ?>
											</button>

										</form>

									<?php endif; ?>

									<?php if ( ! in_array(
										$order->status,
										array( 'cancelled', 'refunded' ),
										true
									) ) : ?>

										<form
											method="post"
											style="display:inline-block;"
											onsubmit="return confirm('<?php echo esc_js( __( 'Cancel this order?', 'dispensary-wp' ) ); ?>');"
										>

											<?php wp_nonce_field(
												'dispensary_order_action',
												'dispensary_order_nonce'
											); ?>

											<input
												type="hidden"
												name="order_action"
												value="cancel"
											>

											<input
												type="hidden"
												name="order_id"
												value="<?php echo esc_attr( $order->id ); ?>"
											>

											<button
												type="submit"
												class="button button-link-delete"
											>
												<?php esc_html_e( 'Cancel', 'dispensary-wp' ); ?>
											</button>

										</form>

									<?php endif; ?>

								<?php endif; ?>

							</td>

						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			</tbody>

		</table>

	</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

	const addButton = document.getElementById(
		'dispensary-add-order-item'
	);

	const itemsTable = document.getElementById(
		'dispensary-order-items'
	);

	if (!addButton || !itemsTable) {
		return;
	}

	addButton.addEventListener('click', function () {

		const firstRow = itemsTable.querySelector('tr');

		if (!firstRow) {
			return;
		}

		const newRow = firstRow.cloneNode(true);

		const quantity = newRow.querySelector(
			'input[name="quantity[]"]'
		);

		if (quantity) {
			quantity.value = 1;
		}

		const product = newRow.querySelector(
			'select[name="product_id[]"]'
		);

		if (product) {
			product.selectedIndex = 0;
		}

		itemsTable.appendChild(newRow);
	});
});
</script>
