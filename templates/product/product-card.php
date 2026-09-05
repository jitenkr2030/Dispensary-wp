<?php
/**
 * Product card template.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = isset( $product ) ? $product : null;

if ( ! $product ) {
	return;
}

$product_id = isset( $product->id ) ? absint( $product->id ) : 0;
$name       = isset( $product->name ) ? $product->name : '';
$sku        = isset( $product->sku ) ? $product->sku : '';
$price      = isset( $product->price ) ? $product->price : 0;
?>

<article class="dispensary-wp-product-card">

	<div class="dispensary-wp-product-content">

		<h3 class="dispensary-wp-product-title">
			<?php echo esc_html( $name ); ?>
		</h3>

		<?php if ( $sku ) : ?>

			<div class="dispensary-wp-product-sku">
				<?php
				printf(
					/* translators: %s: SKU */
					esc_html__( 'SKU: %s', 'dispensary-wp' ),
					esc_html( $sku )
				);
				?>
			</div>

		<?php endif; ?>

		<div class="dispensary-wp-product-price">
			<?php echo esc_html( \Dispensary_WP\Templates\money( $price ) ); ?>
		</div>

		<button
			type="button"
			class="dispensary-wp-button dispensary-wp-add-to-cart"
			data-product-id="<?php echo esc_attr( $product_id ); ?>"
		>
			<?php esc_html_e( 'Add to Cart', 'dispensary-wp' ); ?>
		</button>

	</div>

</article>
