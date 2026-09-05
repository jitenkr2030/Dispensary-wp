<?php
/**
 * Loyalty card template.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$member = isset( $member ) ? $member : null;
?>

<div class="dispensary-wp-loyalty">

	<div class="dispensary-wp-loyalty-card">

		<h3>
			<?php esc_html_e( 'Loyalty Program', 'dispensary-wp' ); ?>
		</h3>

		<?php if ( ! $member ) : ?>

			<p>
				<?php esc_html_e( 'No loyalty membership found.', 'dispensary-wp' ); ?>
			</p>

		<?php else : ?>

			<?php if ( isset( $member->points ) ) : ?>

				<p>
					<strong>
						<?php esc_html_e( 'Points:', 'dispensary-wp' ); ?>
					</strong>

					<?php echo esc_html( absint( $member->points ) ); ?>
				</p>

			<?php endif; ?>

			<?php if ( isset( $member->tier ) ) : ?>

				<p>
					<strong>
						<?php esc_html_e( 'Tier:', 'dispensary-wp' ); ?>
					</strong>

					<?php echo esc_html( $member->tier ); ?>
				</p>

			<?php endif; ?>

		<?php endif; ?>

	</div>

</div>
