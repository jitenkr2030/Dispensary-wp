<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dispensary_WP\Modules\Products\Product;
use Dispensary_WP\Modules\Products\Products;
use Dispensary_WP\Modules\Products\Product_Category;

if ( ! current_user_can( 'dispensary_manage_products' ) ) {
	wp_die( esc_html__( 'You do not have permission to manage products.', 'dispensary-wp' ) );
}

/*
 * Handle product actions before rendering HTML.
 */
$message = '';
$error   = '';

if ( isset( $_POST['dispensary_product_action'] ) ) {

	check_admin_referer( 'dispensary_product_action', 'dispensary_product_nonce' );

	$action = sanitize_key( wp_unslash( $_POST['dispensary_product_action'] ) );

	$data = array(
		'name'                 => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
		'sku'                  => sanitize_text_field( wp_unslash( $_POST['sku'] ?? '' ) ),
		'description'          => wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) ),
		'category_id'          => absint( $_POST['category_id'] ?? 0 ),
		'product_type'         => sanitize_key( wp_unslash( $_POST['product_type'] ?? 'product' ) ),
		'status'               => sanitize_key( wp_unslash( $_POST['status'] ?? 'active' ) ),
		'price'                => (float) ( $_POST['price'] ?? 0 ),
		'cost_price'           => (float) ( $_POST['cost_price'] ?? 0 ),
		'tax_rate'             => (float) ( $_POST['tax_rate'] ?? 0 ),
		'age_restricted'       => ! empty( $_POST['age_restricted'] ) ? 1 : 0,
		'requires_prescription'=> ! empty( $_POST['requires_prescription'] ) ? 1 : 0,
		'restricted_countries' => sanitize_textarea_field( wp_unslash( $_POST['restricted_countries'] ?? '' ) ),
		'image_id'             => absint( $_POST['image_id'] ?? 0 ),
	);

	try {

		if ( 'create' === $action ) {

			if ( '' === $data['name'] || '' === $data['sku'] ) {
				throw new Exception( 'Product name and SKU are required.' );
			}

			$result = Products::create( $data );

			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}

			$message = 'Product created successfully.';

		} elseif ( 'update' === $action ) {

			$id = absint( $_POST['product_id'] ?? 0 );

			if ( ! $id ) {
				throw new Exception( 'Invalid product ID.' );
			}

			$result = Products::update( $id, $data );

			if ( is_wp_error( $result ) ) {
				throw new Exception( $result->get_error_message() );
			}

			$message = 'Product updated successfully.';
		}

	} catch ( Throwable $e ) {
		$error = $e->getMessage();
	}
}

if ( isset( $_POST['dispensary_delete_product'] ) ) {

	check_admin_referer( 'dispensary_delete_product', 'dispensary_delete_nonce' );

	$id = absint( $_POST['product_id'] ?? 0 );

	if ( $id ) {
		try {
			$result = Products::delete( $id );

			if ( is_wp_error( $result ) ) {
				$error = $result->get_error_message();
			} else {
				$message = 'Product deleted successfully.';
			}
		} catch ( Throwable $e ) {
			$error = $e->getMessage();
		}
	}
}

/*
 * Edit mode.
 */
$edit_product = null;

if ( isset( $_GET['edit'] ) ) {
	$edit_id = absint( $_GET['edit'] );

	if ( $edit_id ) {
		$edit_product = Product::find( $edit_id );
	}
}

/*
 * Categories.
 */
$categories = Product_Category::all();

/*
 * Product list.
 */
$products = Product::all(
	array(
		'limit' => 200,
		'offset' => 0,
	)
);

if ( ! is_array( $products ) ) {
	$products = array();
}

$editing = is_array( $edit_product );

?>

<div class="wrap dispensary-wp-admin">

	<h1 class="wp-heading-inline">
		<?php echo $editing ? esc_html__( 'Edit Product', 'dispensary-wp' ) : esc_html__( 'Products', 'dispensary-wp' ); ?>
	</h1>

	<?php if ( $message ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( $error ) : ?>
		<div class="notice notice-error">
			<p><?php echo esc_html( $error ); ?></p>
		</div>
	<?php endif; ?>

	<div class="dispensary-wp-panel" style="margin-top:20px;padding:20px;">

		<h2>
			<?php echo $editing ? esc_html__( 'Edit Product', 'dispensary-wp' ) : esc_html__( 'Add New Product', 'dispensary-wp' ); ?>
		</h2>

		<form method="post">

			<?php wp_nonce_field( 'dispensary_product_action', 'dispensary_product_nonce' ); ?>

			<input
				type="hidden"
				name="dispensary_product_action"
				value="<?php echo $editing ? 'update' : 'create'; ?>"
			>

			<?php if ( $editing ) : ?>
				<input
					type="hidden"
					name="product_id"
					value="<?php echo esc_attr( $edit_product['id'] ); ?>"
				>
			<?php endif; ?>

			<table class="form-table">

				<tr>
					<th><label for="product_name">Product Name *</label></th>
					<td>
						<input
							type="text"
							id="product_name"
							name="name"
							class="regular-text"
							required
							value="<?php echo esc_attr( $edit_product['name'] ?? '' ); ?>"
						>
					</td>
				</tr>

				<tr>
					<th><label for="product_sku">SKU *</label></th>
					<td>
						<input
							type="text"
							id="product_sku"
							name="sku"
							class="regular-text"
							required
							value="<?php echo esc_attr( $edit_product['sku'] ?? '' ); ?>"
						>
					</td>
				</tr>

				<tr>
					<th><label for="product_category">Category</label></th>
					<td>
						<select name="category_id" id="product_category">
							<option value="0">— Select Category —</option>

							<?php foreach ( $categories as $category ) : ?>
								<option
									value="<?php echo esc_attr( $category['id'] ); ?>"
									<?php selected( $edit_product['category_id'] ?? 0, $category['id'] ); ?>
								>
									<?php echo esc_html( $category['name'] ); ?>
								</option>
							<?php endforeach; ?>

						</select>
					</td>
				</tr>

				<tr>
					<th><label for="product_type">Product Type</label></th>
					<td>
						<select name="product_type" id="product_type">
							<option value="product" <?php selected( $edit_product['product_type'] ?? 'product', 'product' ); ?>>Product</option>
							<option value="service" <?php selected( $edit_product['product_type'] ?? '', 'service' ); ?>>Service</option>
							<option value="bundle" <?php selected( $edit_product['product_type'] ?? '', 'bundle' ); ?>>Bundle</option>
						</select>
					</td>
				</tr>

				<tr>
					<th><label for="product_price">Selling Price</label></th>
					<td>
						<input
							type="number"
							step="0.01"
							min="0"
							id="product_price"
							name="price"
							value="<?php echo esc_attr( $edit_product['price'] ?? '0' ); ?>"
						>
					</td>
				</tr>

				<tr>
					<th><label for="product_cost">Cost Price</label></th>
					<td>
						<input
							type="number"
							step="0.01"
							min="0"
							id="product_cost"
							name="cost_price"
							value="<?php echo esc_attr( $edit_product['cost_price'] ?? '0' ); ?>"
						>
					</td>
				</tr>

				<tr>
					<th><label for="product_tax">Tax Rate (%)</label></th>
					<td>
						<input
							type="number"
							step="0.01"
							min="0"
							id="product_tax"
							name="tax_rate"
							value="<?php echo esc_attr( $edit_product['tax_rate'] ?? '0' ); ?>"
						>
					</td>
				</tr>

				<tr>
					<th><label for="product_status">Status</label></th>
					<td>
						<select name="status" id="product_status">
							<option value="active" <?php selected( $edit_product['status'] ?? 'active', 'active' ); ?>>Active</option>
							<option value="inactive" <?php selected( $edit_product['status'] ?? '', 'inactive' ); ?>>Inactive</option>
							<option value="draft" <?php selected( $edit_product['status'] ?? '', 'draft' ); ?>>Draft</option>
						</select>
					</td>
				</tr>

				<tr>
					<th>Restrictions</th>
					<td>
						<label>
							<input
								type="checkbox"
								name="age_restricted"
								value="1"
								<?php checked( $edit_product['age_restricted'] ?? 0, 1 ); ?>
							>
							Age Restricted
						</label>
						<br>

						<label>
							<input
								type="checkbox"
								name="requires_prescription"
								value="1"
								<?php checked( $edit_product['requires_prescription'] ?? 0, 1 ); ?>
							>
							Requires Prescription
						</label>
					</td>
				</tr>

				<tr>
					<th><label for="restricted_countries">Restricted Countries</label></th>
					<td>
						<textarea
							id="restricted_countries"
							name="restricted_countries"
							rows="3"
							class="large-text"
						><?php echo esc_textarea( $edit_product['restricted_countries'] ?? '' ); ?></textarea>
					</td>
				</tr>

				<tr>
					<th><label for="product_description">Description</label></th>
					<td>
						<?php
						wp_editor(
							$edit_product['description'] ?? '',
							'dispensary_product_description',
							array(
								'textarea_name' => 'description',
								'textarea_rows' => 6,
								'media_buttons' => false,
							)
						);
						?>
					</td>
				</tr>

			</table>

			<p>
				<button type="submit" class="button button-primary">
					<?php echo $editing ? esc_html__( 'Update Product', 'dispensary-wp' ) : esc_html__( 'Add Product', 'dispensary-wp' ); ?>
				</button>

				<?php if ( $editing ) : ?>
					<a
						href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-wp-products' ) ); ?>"
						class="button"
					>
						Cancel
					</a>
				<?php endif; ?>
			</p>

		</form>

	</div>

	<hr>

	<h2>Product List</h2>

	<table class="wp-list-table widefat fixed striped">

		<thead>
			<tr>
				<th>ID</th>
				<th>Product</th>
				<th>SKU</th>
				<th>Category</th>
				<th>Price</th>
				<th>Status</th>
				<th>Restrictions</th>
				<th>Actions</th>
			</tr>
		</thead>

		<tbody>

			<?php if ( empty( $products ) ) : ?>

				<tr>
					<td colspan="8">
						No products found.
					</td>
				</tr>

			<?php else : ?>

				<?php foreach ( $products as $product ) : ?>

					<?php
					$category_name = '';

					foreach ( $categories as $category ) {
						if ( (int) $category['id'] === (int) $product['category_id'] ) {
							$category_name = $category['name'];
							break;
						}
					}
					?>

					<tr>

						<td>
							<?php echo esc_html( $product['id'] ); ?>
						</td>

						<td>
							<strong>
								<?php echo esc_html( $product['name'] ); ?>
							</strong>
						</td>

						<td>
							<?php echo esc_html( $product['sku'] ); ?>
						</td>

						<td>
							<?php echo esc_html( $category_name ?: '—' ); ?>
						</td>

						<td>
							<?php echo esc_html( number_format_i18n( (float) $product['price'], 2 ) ); ?>
						</td>

						<td>
							<?php echo esc_html( ucfirst( $product['status'] ) ); ?>
						</td>

						<td>
							<?php
							$restrictions = array();

							if ( ! empty( $product['age_restricted'] ) ) {
								$restrictions[] = 'Age';
							}

							if ( ! empty( $product['requires_prescription'] ) ) {
								$restrictions[] = 'Prescription';
							}

							echo esc_html(
								empty( $restrictions )
									? 'None'
									: implode( ', ', $restrictions )
							);
							?>
						</td>

						<td>

							<a
								class="button button-small"
								href="<?php echo esc_url( admin_url( 'admin.php?page=dispensary-wp-products&edit=' . absint( $product['id'] ) ) ); ?>"
							>
								Edit
							</a>

							<form
								method="post"
								style="display:inline-block;"
								onsubmit="return confirm('Delete this product?');"
							>

								<?php wp_nonce_field( 'dispensary_delete_product', 'dispensary_delete_nonce' ); ?>

								<input
									type="hidden"
									name="product_id"
									value="<?php echo esc_attr( $product['id'] ); ?>"
								>

								<button
									type="submit"
									name="dispensary_delete_product"
									class="button button-small"
								>
									Delete
								</button>

							</form>

						</td>

					</tr>

				<?php endforeach; ?>

			<?php endif; ?>

		</tbody>

	</table>

</div>
