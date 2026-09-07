<?php
/**
 * Point of Sale admin view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Modules\POS\POS;
use Dispensary_WP\Modules\Customers\Customer;
use Dispensary_WP\Modules\Products\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'dispensary_use_pos' ) ) {
	wp_die(
		esc_html__(
			'You do not have permission to use the POS.',
			'dispensary-wp'
		)
	);
}

$pos = new POS();

$notice = '';
$error  = '';

/*
 * Handle POS actions.
 */
if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {

	$action = isset( $_POST['pos_action'] )
		? sanitize_key( $_POST['pos_action'] )
		: '';

	if (
		! isset( $_POST['dispensary_pos_nonce'] )
		|| ! wp_verify_nonce(
			sanitize_text_field(
				wp_unslash( $_POST['dispensary_pos_nonce'] )
			),
			'dispensary_pos_action'
		)
	) {
		$error = __( 'Security check failed.', 'dispensary-wp' );
	} else {

		/*
		 * Add product to cart.
		 */
		if ( 'add_to_cart' === $action ) {

			$product_id = isset( $_POST['product_id'] )
				? absint( $_POST['product_id'] )
				: 0;

			$quantity = isset( $_POST['quantity'] )
				? max( 1, (float) $_POST['quantity'] )
				: 1;

			$result = $pos->add_to_cart(
				$product_id,
				$quantity
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __( 'Product added to cart.', 'dispensary-wp' );
			}
		}

		/*
		 * Remove product from cart.
		 */
		if ( 'remove_from_cart' === $action ) {

			$product_id = isset( $_POST['product_id'] )
				? absint( $_POST['product_id'] )
				: 0;

			if ( $pos->remove_from_cart( $product_id ) ) {
				$notice = __( 'Product removed from cart.', 'dispensary-wp' );
			}
		}

		/*
		 * Complete sale.
		 */
		if ( 'complete_sale' === $action ) {

			$payment_method = isset( $_POST['payment_method'] )
				? sanitize_key( $_POST['payment_method'] )
				: 'cash';

			$paid_amount = isset( $_POST['paid_amount'] )
				? (float) $_POST['paid_amount']
				: 0;

			$result = $pos->complete_sale(
				array(
					'customer_id' => isset( $_POST['customer_id'] )
						? absint( $_POST['customer_id'] )
						: 0,
					'register_id' => isset( $_POST['register_id'] )
						? absint( $_POST['register_id'] )
						: 0,
					'session_id'  => isset( $_POST['session_id'] )
						? absint( $_POST['session_id'] )
						: 0,
					'payment_method' => $payment_method,
					'paid_amount'   => $paid_amount,
					'transaction_id' => isset( $_POST['transaction_id'] )
						? sanitize_text_field(
							wp_unslash( $_POST['transaction_id'] )
						)
						: '',
				)
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = sprintf(
					/* translators: %d: sale ID */
					__(
						'POS sale #%d completed successfully.',
						'dispensary-wp'
					),
					absint( $result )
				);
			}
		}

		/*
		 * Open session.
		 */
		if ( 'open_session' === $action ) {

			$register_id = isset( $_POST['register_id'] )
				? absint( $_POST['register_id'] )
				: 0;

			$opening_cash = isset( $_POST['opening_cash'] )
				? (float) $_POST['opening_cash']
				: 0;

			$result = $pos->open_session(
				$register_id,
				$opening_cash
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$notice = __(
					'POS session opened successfully.',
					'dispensary-wp'
				);
			}
		}

		/*
		 * Close session.
		 */
		if ( 'close_session' === $action ) {

			$session_id = isset( $_POST['session_id'] )
				? absint( $_POST['session_id'] )
				: 0;

			$closing_cash = isset( $_POST['closing_cash'] )
				? (float) $_POST['closing_cash']
				: 0;

			$result = $pos->close_session(
				$session_id,
				$closing_cash
			);

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} elseif ( $result ) {
				$notice = __(
					'POS session closed successfully.',
					'dispensary-wp'
				);
			}
		}
	}
}

/*
 * Load POS data.
 */
$cart = $pos->get_cart();

$registers = $pos->registers();

$active_session = $pos->active_session();

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

$sales = $pos->sales( 20 );

if ( is_wp_error( $sales ) ) {
	$sales = array();
}

$total_sales = 0;

foreach ( $sales as $sale ) {
	$total_sales += isset( $sale->total )
		? (float) $sale->total
		: 0;
}
?>

<div class="wrap dispensary-wp-admin">

	<h1>
		<?php esc_html_e( 'Point of Sale', 'dispensary-wp' ); ?>
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
			<h3>
				<?php esc_html_e( 'Cart Items', 'dispensary-wp' ); ?>
			</h3>

			<strong>
				<?php echo esc_html( count( $cart['items'] ) ); ?>
			</strong>
		</div>

		<div class="dispensary-wp-card">
			<h3>
				<?php esc_html_e( 'Cart Total', 'dispensary-wp' ); ?>
			</h3>

			<strong>
				<?php
				echo esc_html(
					number_format_i18n(
						(float) $cart['total'],
						2
					)
				);
				?>
			</strong>
		</div>

		<div class="dispensary-wp-card">
			<h3>
				<?php esc_html_e( 'Recent Sales', 'dispensary-wp' ); ?>
			</h3>

			<strong>
				<?php echo esc_html( count( $sales ) ); ?>
			</strong>
		</div>

		<div class="dispensary-wp-card">
			<h3>
				<?php esc_html_e( 'Recent Sales Value', 'dispensary-wp' ); ?>
			</h3>

			<strong>
				<?php
				echo esc_html(
					number_format_i18n(
						$total_sales,
						2
					)
				);
				?>
			</strong>
		</div>

	</div>

	<div class="dispensary-wp-panel">

		<h2>
			<?php esc_html_e( 'POS Session', 'dispensary-wp' ); ?>
		</h2>

		<?php if ( $active_session ) : ?>

			<p>
				<strong>
					<?php esc_html_e( 'Session Active', 'dispensary-wp' ); ?>
				</strong>
			</p>

			<form method="post">

				<?php wp_nonce_field(
					'dispensary_pos_action',
					'dispensary_pos_nonce'
				); ?>

				<input
					type="hidden"
					name="pos_action"
					value="close_session"
				>

				<input
					type="hidden"
					name="session_id"
					value="<?php echo esc_attr( $active_session->id ); ?>"
				>

				<label>
					<?php esc_html_e( 'Closing Cash', 'dispensary-wp' ); ?>
				</label>

				<input
					type="number"
					name="closing_cash"
					value="0"
					min="0"
					step="0.01"
				>

				<button type="submit" class="button">
					<?php esc_html_e( 'Close Session', 'dispensary-wp' ); ?>
				</button>

			</form>

		<?php else : ?>

			<form method="post">

				<?php wp_nonce_field(
					'dispensary_pos_action',
					'dispensary_pos_nonce'
				); ?>

				<input
					type="hidden"
					name="pos_action"
					value="open_session"
				>

				<label>
					<?php esc_html_e( 'Register', 'dispensary-wp' ); ?>
				</label>

				<select name="register_id" required>

					<option value="">
						<?php esc_html_e(
							'Select Register',
							'dispensary-wp'
						); ?>
					</option>

					<?php foreach ( $registers as $register ) : ?>

						<option
							value="<?php echo esc_attr( $register->id ); ?>"
						>
							<?php
							echo esc_html(
								$register->name
							);
							?>
						</option>

					<?php endforeach; ?>

				</select>

				<label>
					<?php esc_html_e( 'Opening Cash', 'dispensary-wp' ); ?>
				</label>

				<input
					type="number"
					name="opening_cash"
					value="0"
					min="0"
					step="0.01"
				>

				<button type="submit" class="button button-primary">
					<?php esc_html_e(
						'Open POS Session',
						'dispensary-wp'
					); ?>
				</button>

			</form>

		<?php endif; ?>

	</div>

	<div class="dispensary-wp-panel">

		<h2>
			<?php esc_html_e( 'Add Product', 'dispensary-wp' ); ?>
		</h2>

		<form method="post">

			<?php wp_nonce_field(
				'dispensary_pos_action',
				'dispensary_pos_nonce'
			); ?>

			<input
				type="hidden"
				name="pos_action"
				value="add_to_cart"
			>

			<select name="product_id" required>

				<option value="">
					<?php esc_html_e(
						'Select Product',
						'dispensary-wp'
					); ?>
				</option>

				<?php foreach ( $products as $product ) : ?>

					<option
						value="<?php echo esc_attr( $product->id ); ?>"
					>
						<?php
						echo esc_html(
							$product->name
							. ' - '
							. number_format_i18n(
								(float) $product->price,
								2
							)
						);
						?>
					</option>

				<?php endforeach; ?>

			</select>

			<input
				type="number"
				name="quantity"
				value="1"
				min="1"
				step="1"
				required
			>

			<button type="submit" class="button button-primary">
				<?php esc_html_e(
					'Add to Cart',
					'dispensary-wp'
				); ?>
			</button>

		</form>

	</div>

	<div class="dispensary-wp-panel">

		<h2>
			<?php esc_html_e( 'Current Cart', 'dispensary-wp' ); ?>
		</h2>

		<?php if ( empty( $cart['items'] ) ) : ?>

			<p>
				<?php esc_html_e(
					'Cart is empty.',
					'dispensary-wp'
				); ?>
			</p>

		<?php else : ?>

			<table class="widefat striped">

				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'SKU', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Qty', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Price', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Total', 'dispensary-wp' ); ?></th>
						<th><?php esc_html_e( 'Action', 'dispensary-wp' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php foreach ( $cart['items'] as $item ) : ?>

						<tr>

							<td>
								<?php echo esc_html( $item['name'] ); ?>
							</td>

							<td>
								<?php echo esc_html( $item['sku'] ); ?>
							</td>

							<td>
								<?php echo esc_html( $item['quantity'] ); ?>
							</td>

							<td>
								<?php
								echo esc_html(
									number_format_i18n(
										(float) $item['unit_price'],
										2
									)
								);
								?>
							</td>

							<td>
								<?php
								echo esc_html(
									number_format_i18n(
										(float) $item['total'],
										2
									)
								);
								?>
							</td>

							<td>

								<form method="post">

									<?php wp_nonce_field(
										'dispensary_pos_action',
										'dispensary_pos_nonce'
									); ?>

									<input
										type="hidden"
										name="pos_action"
										value="remove_from_cart"
									>

									<input
										type="hidden"
										name="product_id"
										value="<?php echo esc_attr( $item['product_id'] ); ?>"
									>

									<button
										type="submit"
										class="button button-link-delete"
									>
										<?php esc_html_e(
											'Remove',
											'dispensary-wp'
										); ?>
									</button>

								</form>

							</td>

						</tr>

					<?php endforeach; ?>

				</tbody>

				<tfoot>

					<tr>
						<th colspan="4">
							<?php esc_html_e( 'Subtotal', 'dispensary-wp' ); ?>
						</th>

						<th colspan="2">
							<?php
							echo esc_html(
								number_format_i18n(
									(float) $cart['subtotal'],
									2
								)
							);
							?>
						</th>
					</tr>

					<tr>
						<th colspan="4">
							<?php esc_html_e( 'Tax', 'dispensary-wp' ); ?>
						</th>

						<th colspan="2">
							<?php
							echo esc_html(
								number_format_i18n(
									(float) $cart['tax'],
									2
								)
							);
							?>
						</th>
					</tr>

					<tr>
						<th colspan="4">
							<?php esc_html_e( 'Grand Total', 'dispensary-wp' ); ?>
						</th>

						<th colspan="2">
							<strong>
								<?php
								echo esc_html(
									number_format_i18n(
										(float) $cart['total'],
										2
									)
								);
								?>
							</strong>
						</th>
					</tr>

				</tfoot>

			</table>

		<?php endif; ?>

	</div>

	<?php if ( ! empty( $cart['items'] ) ) : ?>

		<div class="dispensary-wp-panel">

			<h2>
				<?php esc_html_e(
					'Complete Sale',
					'dispensary-wp'
				); ?>
			</h2>

			<form method="post">

				<?php wp_nonce_field(
					'dispensary_pos_action',
					'dispensary_pos_nonce'
				); ?>

				<input
					type="hidden"
					name="pos_action"
					value="complete_sale"
				>

				<?php if ( $active_session ) : ?>

					<input
						type="hidden"
						name="session_id"
						value="<?php echo esc_attr( $active_session->id ); ?>"
					>

					<input
						type="hidden"
						name="register_id"
						value="<?php echo esc_attr( $active_session->register_id ); ?>"
					>

				<?php endif; ?>

				<p>

					<label>
						<?php esc_html_e(
							'Customer',
							'dispensary-wp'
						); ?>
					</label>

					<select name="customer_id">

						<option value="0">
							<?php esc_html_e(
								'Walk-in Customer',
								'dispensary-wp'
							); ?>
						</option>

						<?php foreach ( $customers as $customer ) : ?>

							<option
								value="<?php echo esc_attr( $customer->id ); ?>"
							>
								<?php
								echo esc_html(
									trim(
										$customer->first_name
										. ' '
										. $customer->last_name
									)
								);
								?>
							</option>

						<?php endforeach; ?>

					</select>

				</p>

				<p>

					<label>
						<?php esc_html_e(
							'Payment Method',
							'dispensary-wp'
						); ?>
					</label>

					<select name="payment_method">

						<option value="cash">
							<?php esc_html_e(
								'Cash',
								'dispensary-wp'
							); ?>
						</option>

						<option value="card">
							<?php esc_html_e(
								'Card',
								'dispensary-wp'
							); ?>
						</option>

						<option value="upi">
							<?php esc_html_e(
								'UPI',
								'dispensary-wp'
							); ?>
						</option>

						<option value="bank">
							<?php esc_html_e(
								'Bank Transfer',
								'dispensary-wp'
							); ?>
						</option>

					</select>

				</p>

				<p>

					<label>
						<?php esc_html_e(
							'Paid Amount',
							'dispensary-wp'
						); ?>
					</label>

					<input
						type="number"
						name="paid_amount"
						value="<?php echo esc_attr( $cart['total'] ); ?>"
						min="0"
						step="0.01"
						required
					>

				</p>

				<p>

					<label>
						<?php esc_html_e(
							'Transaction ID',
							'dispensary-wp'
						); ?>
					</label>

					<input
						type="text"
						name="transaction_id"
						value=""
					>

				</p>

				<?php submit_button(
					__(
						'Complete Sale',
						'dispensary-wp'
					),
					'primary'
				); ?>

			</form>

		</div>

	<?php endif; ?>

	<div class="dispensary-wp-panel">

		<h2>
			<?php esc_html_e(
				'Recent POS Sales',
				'dispensary-wp'
			); ?>
		</h2>

		<table class="widefat striped">

			<thead>

				<tr>
					<th><?php esc_html_e( 'ID', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Receipt', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Customer', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Total', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Status', 'dispensary-wp' ); ?></th>
					<th><?php esc_html_e( 'Date', 'dispensary-wp' ); ?></th>
				</tr>

			</thead>

			<tbody>

				<?php if ( empty( $sales ) ) : ?>

					<tr>
						<td colspan="6">
							<?php esc_html_e(
								'No POS sales found.',
								'dispensary-wp'
							); ?>
						</td>
					</tr>

				<?php else : ?>

					<?php foreach ( $sales as $sale ) : ?>

						<tr>

							<td>
								<?php echo esc_html( $sale->id ); ?>
							</td>

							<td>
								<?php echo esc_html( $sale->receipt_number ); ?>
							</td>

							<td>
								<?php
								echo $sale->customer_id
									? esc_html( '#' . $sale->customer_id )
									: esc_html__(
										'Walk-in',
										'dispensary-wp'
									);
								?>
							</td>

							<td>
								<?php
								echo esc_html(
									number_format_i18n(
										(float) $sale->total,
										2
									)
								);
								?>
							</td>

							<td>
								<?php
								echo esc_html(
									ucfirst(
										(string) $sale->status
									)
								);
								?>
							</td>

							<td>
								<?php
								echo esc_html(
									mysql2date(
										get_option( 'date_format' ),
										$sale->created_at
									)
								);
								?>
							</td>

						</tr>

					<?php endforeach; ?>

				<?php endif; ?>

			</tbody>

		</table>

	</div>

</div>
