<?php
/**
 * Settings view.
 *
 * @package Dispensary_WP
 */

use Dispensary_WP\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_POST['dispensary_wp_save_settings'] ) ) {

	check_admin_referer( 'dispensary_wp_settings' );

	if ( current_user_can( 'dispensary_manage_settings' ) ) {

		$enabled  = isset( $_POST['enabled'] ) ? '1' : '0';
		$currency = isset( $_POST['currency'] )
			? sanitize_text_field( wp_unslash( $_POST['currency'] ) )
			: 'USD';

		Settings::update(
			array(
				'enabled'  => $enabled,
				'currency' => $currency,
			)
		);

		wp_safe_redirect(
			add_query_arg(
				'dispensary_wp_notice',
				'saved',
				admin_url( 'admin.php?page=dispensary-wp-settings' )
			)
		);

		exit;
	}
}

$settings = Settings::all();
?>

<div class="wrap dispensary-wp-admin">

	<h1><?php esc_html_e( 'Dispensary WP Settings', 'dispensary-wp' ); ?></h1>

	<div class="dispensary-wp-panel">

		<form method="post">

			<?php wp_nonce_field( 'dispensary_wp_settings' ); ?>

			<div class="dispensary-wp-form-row">

				<label for="dispensary-wp-enabled">
					<?php esc_html_e( 'Plugin Enabled', 'dispensary-wp' ); ?>
				</label>

				<label>
					<input
						type="checkbox"
						id="dispensary-wp-enabled"
						name="enabled"
						value="1"
						<?php checked( ! empty( $settings['enabled'] ) ); ?>
					>
					<?php esc_html_e( 'Enable Dispensary WP', 'dispensary-wp' ); ?>
				</label>

			</div>

			<div class="dispensary-wp-form-row">

				<label for="dispensary-wp-currency">
					<?php esc_html_e( 'Currency', 'dispensary-wp' ); ?>
				</label>

				<input
					type="text"
					id="dispensary-wp-currency"
					name="currency"
					value="<?php echo esc_attr( $settings['currency'] ?? 'USD' ); ?>"
					maxlength="10"
				>

			</div>

			<p>
				<button
					type="submit"
					name="dispensary_wp_save_settings"
					class="button button-primary"
				>
					<?php esc_html_e( 'Save Settings', 'dispensary-wp' ); ?>
				</button>
			</p>

		</form>

	</div>

</div>
