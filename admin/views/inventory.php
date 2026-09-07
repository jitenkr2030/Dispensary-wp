<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dispensary_WP\Modules\Products\Product;
use Dispensary_WP\Modules\Inventory\Inventory;
use Dispensary_WP\Modules\Inventory\Stock;
use Dispensary_WP\Modules\Inventory\Stock_Movement;

if ( ! current_user_can( 'dispensary_view_inventory' ) ) {
	wp_die( esc_html__( 'You do not have permission to view inventory.', 'dispensary-wp' ) );
}

$message = '';
$error   = '';

if ( isset( $_POST['dispensary_inventory_action'] ) ) {

	if (
		! isset( $_POST['dispensary_inventory_nonce'] ) ||
		! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['dispensary_inventory_nonce'] ) ),
			'dispensary_inventory_action'
		)
	) {
		$error = 'Security check failed.';
	} elseif ( ! current_user_can( 'dispensary_manage_inventory' ) ) {
		$error = 'You do not have permission to manage inventory.';
	} else {

		$action     = sanitize_key( $_POST['dispensary_inventory_action'] );
		$product_id = absint( $_POST['product_id'] ?? 0 );
		$quantity   = (float) ( $_POST['quantity'] ?? 0 );
		$reason     = sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) );

		if ( ! $product_id || $quantity <= 0 ) {
			$error = 'Please select a product and enter a valid quantity.';
		} else {

			if ( 'stock_in' === $action ) {

				$result = Inventory::add_stock(
					$product_id,
					$quantity,
					array(
						'reason' => $reason,
					)
				);

			} elseif ( 'stock_out' === $action ) {

				$result = Inventory::remove_stock(
					$product_id,
					$quantity,
					array(
						'reason' => $reason,
					)
				);

			} else {
				$result = new WP_Error(
					'invalid_action',
					'Invalid inventory action.'
				);
			}

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$message = 'Inventory updated successfully.';
			}
		}
	}
}

$products = Product::all(
	array(
		'status' => 'active',
	)
);

$total_stock     = 0;
$low_stock       = 0;
$out_of_stock    = 0;
$stock_value     = 0;
$product_rows    = array();

foreach ( $products as $product ) {

	$stock = Stock::available( $product['id'] );

	$total_stock += $stock;
	$stock_value += $stock * (float) $product['cost_price'];

	if ( $stock <= 0 ) {
		$out_of_stock++;
	} elseif ( $stock <= 10 ) {
		$low_stock++;
	}

	$product_rows[] = array(
		'product' => $product,
		'stock'   => $stock,
	);
}

$currency = 'USD';

if ( class_exists( '\Dispensary_WP\Core\Settings' ) ) {
	$settings = new \Dispensary_WP\Core\Settings();
	$currency = $settings->get( 'currency' ) ?: 'USD';
}
?>

<div class="wrap dispensary-wp-admin">

	<h1><?php esc_html_e( 'Inventory', 'dispensary-wp' ); ?></h1>

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

	<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin:20px 0;">

		<div class="card" style="padding:20px;">
			<h3>Total Stock</h3>
			<strong style="font-size:28px;">
				<?php echo esc_html( number_format_i18n( $total_stock, 2 ) ); ?>
			</strong>
		</div>

		<div class="card" style="padding:20px;">
			<h3>Low Stock</h3>
			<strong style="font-size:28px;">
				<?php echo esc_html( $low_stock ); ?>
			</strong>
		</div>

		<div class="card" style="padding:20px;">
			<h3>Out of Stock</h3>
			<strong style="font-size:28px;">
				<?php echo esc_html( $out_of_stock ); ?>
			</strong>
		</div>

		<div class="card" style="padding:20px;">
			<h3>Stock Value</h3>
			<strong style="font-size:28px;">
				<?php echo esc_html( $currency . ' ' . number_format_i18n( $stock_value, 2 ) ); ?>
			</strong>
		</div>

	</div>

	<?php if ( current_user_can( 'dispensary_manage_inventory' ) ) : ?>

	<div class="dispensary-wp-panel" style="padding:20px;margin-bottom:20px;">

		<h2>Stock In / Stock Out</h2>

		<form method="post">

			<?php wp_nonce_field( 'dispensary_inventory_action', 'dispensary_inventory_nonce' ); ?>

			<p>
				<label><strong>Product</strong></label><br>
				<select name="product_id" required style="min-width:300px;">
					<option value="">Select Product</option>

					<?php foreach ( $products as $product ) : ?>
						<option value="<?php echo esc_attr( $product['id'] ); ?>">
							<?php echo esc_html( $product['name'] . ' — ' . $product['sku'] ); ?>
						</option>
					<?php endforeach; ?>

				</select>
			</p>

			<p>
				<label><strong>Quantity</strong></label><br>
				<input
					type="number"
					name="quantity"
					min="0.01"
					step="0.01"
					required
				>
			</p>

			<p>
				<label><strong>Reason / Note</strong></label><br>
				<input
					type="text"
					name="reason"
					class="regular-text"
					placeholder="Purchase, damage, adjustment, etc."
				>
			</p>

			<p>
				<button
					type="submit"
					name="dispensary_inventory_action"
					value="stock_in"
					class="button button-primary"
				>
					+ Stock In
				</button>

				<button
					type="submit"
					name="dispensary_inventory_action"
					value="stock_out"
					class="button"
				>
					− Stock Out
				</button>
			</p>

		</form>

	</div>

	<?php endif; ?>

	<div class="dispensary-wp-panel">

		<h2>Current Inventory</h2>

		<table class="widefat striped">

			<thead>
				<tr>
					<th>Product</th>
					<th>SKU</th>
					<th>Category</th>
					<th>Cost Price</th>
					<th>Current Stock</th>
					<th>Stock Value</th>
					<th>Status</th>
					<th>History</th>
				</tr>
			</thead>

			<tbody>

			<?php if ( empty( $product_rows ) ) : ?>

				<tr>
					<td colspan="8">No products found.</td>
				</tr>

			<?php else : ?>

				<?php foreach ( $product_rows as $row ) : ?>

					<?php
					$product = $row['product'];
					$stock   = $row['stock'];

					if ( $stock <= 0 ) {
						$status = 'Out of Stock';
					} elseif ( $stock <= 10 ) {
						$status = 'Low Stock';
					} else {
						$status = 'In Stock';
					}
					?>

					<tr>

						<td>
							<strong>
								<?php echo esc_html( $product['name'] ); ?>
							</strong>
						</td>

						<td>
							<?php echo esc_html( $product['sku'] ); ?>
						</td>

						<td>
							<?php echo esc_html( $product['category_id'] ); ?>
						</td>

						<td>
							<?php echo esc_html( $currency . ' ' . number_format_i18n( (float) $product['cost_price'], 2 ) ); ?>
						</td>

						<td>
							<strong>
								<?php echo esc_html( number_format_i18n( $stock, 2 ) ); ?>
							</strong>
						</td>

						<td>
							<?php
							echo esc_html(
								$currency . ' ' .
								number_format_i18n(
									$stock * (float) $product['cost_price'],
									2
								)
							);
							?>
						</td>

						<td>
							<?php echo esc_html( $status ); ?>
						</td>

						<td>
							<?php
							$history = Stock_Movement::all(
								$product['id'],
								10
							);
							?>

							<details>
								<summary>View History</summary>

								<?php if ( empty( $history ) ) : ?>

									<p>No stock movements.</p>

								<?php else : ?>

									<table class="widefat">

										<thead>
											<tr>
												<th>Type</th>
												<th>Qty</th>
												<th>Reason</th>
												<th>Date</th>
											</tr>
										</thead>

										<tbody>

										<?php foreach ( $history as $movement ) : ?>

											<tr>
												<td>
													<?php echo esc_html( strtoupper( $movement['type'] ) ); ?>
												</td>

												<td>
													<?php echo esc_html( number_format_i18n( (float) $movement['quantity'], 2 ) ); ?>
												</td>

												<td>
													<?php echo esc_html( $movement['note'] ?? '' ); ?>
												</td>

												<td>
													<?php echo esc_html( $movement['created_at'] ); ?>
												</td>
											</tr>

										<?php endforeach; ?>

										</tbody>

									</table>

								<?php endif; ?>

							</details>

						</td>

					</tr>

				<?php endforeach; ?>

			<?php endif; ?>

			</tbody>

		</table>

	</div>

</div>
