<?php
/**
 * Order status template.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order = isset( $order ) ? $order : null;
?>

<div class="dispensary-wp-order-status">

	<?php if ( ! $order ) : ?>

		<div class="dispensary-wp-empty">
			<?php esc_html_e( 'Order information is not available.', 'dispensary-wp' ); ?>
		</div>

	<?php else : ?>

		<div class="dispensary-wp-account-card">

			<h3>
				<?php esc_html_e( 'Order Status', 'dispensary-wp' ); ?>
			</h3>

			<?php if ( isset( $order->order_number ) ) : ?>

				<p>
					<strong>
						<?php esc_html_e( 'Order:', 'dispensary-wp' ); ?>
					</strong>

					<?php echo esc_html( $order->order_number ); ?>
				</p>

			<?php endif; ?>

			<?php if ( isset( $order->status ) ) : ?>

				<p>
					<strong>
						<?php esc_html_e( 'Status:', 'dispensary-wp' ); ?>
					</strong>

					<?php
					echo esc_html(
						\Dispensary_WP\Templates\status_label(
							$order->status
						)
					);
					?>
				</p>

			<?php endif; ?>

		</div>

	<?php endif; ?>

</div>
