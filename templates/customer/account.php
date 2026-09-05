<?php
/**
 * Customer account template.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user = wp_get_current_user();
?>

<div class="dispensary-wp-account">

	<h2>
		<?php esc_html_e( 'Customer Account', 'dispensary-wp' ); ?>
	</h2>

	<?php if ( is_user_logged_in() ) : ?>

		<div class="dispensary-wp-account-card">

			<p>
				<?php
				printf(
					/* translators: %s: display name */
					esc_html__( 'Welcome, %s.', 'dispensary-wp' ),
					esc_html( $user->display_name )
				);
				?>
			</p>

			<p>
				<strong><?php esc_html_e( 'Email:', 'dispensary-wp' ); ?></strong>
				<?php echo esc_html( $user->user_email ); ?>
			</p>

			<a
				class="dispensary-wp-button"
				href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"
			>
				<?php esc_html_e( 'Logout', 'dispensary-wp' ); ?>
			</a>

		</div>

	<?php else : ?>

		<div class="dispensary-wp-account-card">

			<p>
				<?php esc_html_e( 'Please log in to access your account.', 'dispensary-wp' ); ?>
			</p>

			<a
				class="dispensary-wp-button"
				href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>"
			>
				<?php esc_html_e( 'Login', 'dispensary-wp' ); ?>
			</a>

		</div>

	<?php endif; ?>

</div>
