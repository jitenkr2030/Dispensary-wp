<?php
/**
 * Delivery tracking template.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$delivery = isset( $delivery ) ? $delivery : null;
?>

<div class="dispensary-wp-delivery-tracking">

	<?php if ( ! $delivery ) : ?>

		<div class="dispensary-wp-empty">
			<?php esc_html_e( 'Delivery information is not available.', 'dispensary-wp' ); ?>
		</div>

	<?php else : ?>

		<div class="dispensary-wp-account-card">

			<h3>
				<?php esc_html_e( 'Delivery Tracking', 'dispensary-wp' ); ?>
			</h3>

			<?php if ( isset( $delivery->delivery_number ) ) : ?>

				<p>
					<strong>
						<?php esc_html_e( 'Delivery:', 'dispensary-wp' ); ?>
					</strong>

					<?php echo esc_html( $delivery->delivery_number ); ?>
				</p>

			<?php endif; ?>

			<?php if ( isset( $delivery->status ) ) : ?>

				<p>
					<strong>
						<?php esc_html_e( 'Status:', 'dispensary-wp' ); ?>
					</strong>

					<?php
					echo esc_html(
						\Dispensary_WP\Templates\status_label(
							$delivery->status
						)
					);
					?>
				</p>

			<?php endif; ?>

		</div>

	<?php endif; ?>

</div>
