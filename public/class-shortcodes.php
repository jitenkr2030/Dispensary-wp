<?php
/**
 * Public shortcodes.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Public_Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_shortcode(
			'dispensary_products',
			array( $this, 'products' )
		);

		add_shortcode(
			'dispensary_customer_account',
			array( $this, 'customer_account' )
		);

		add_shortcode(
			'dispensary_order_status',
			array( $this, 'order_status' )
		);

		add_shortcode(
			'dispensary_loyalty',
			array( $this, 'loyalty' )
		);

		add_shortcode(
			'dispensary_delivery_tracking',
			array( $this, 'delivery_tracking' )
		);
	}

	/**
	 * Product listing shortcode.
	 *
	 * Usage:
	 * [dispensary_products]
	 */
	public function products( $atts ) {

		$atts = shortcode_atts(
			array(
				'limit' => 12,
			),
			$atts,
			'dispensary_products'
		);

		$limit = absint( $atts['limit'] );

		if ( $limit < 1 ) {
			$limit = 12;
		}

		if ( $limit > 100 ) {
			$limit = 100;
		}

		$products = array();

		if ( class_exists( '\Dispensary_WP\Modules\Products\Products' ) ) {
			$products = \Dispensary_WP\Modules\Products\Products::list(
				array(
					'status' => 'active',
					'limit'  => $limit,
				)
			);
		}

		ob_start();
		?>

		<div class="dispensary-wp-products">

			<div class="dispensary-wp-section-header">
				<h2><?php esc_html_e( 'Products', 'dispensary-wp' ); ?></h2>
			</div>

			<?php if ( empty( $products ) ) : ?>

				<div class="dispensary-wp-empty">
					<?php esc_html_e( 'No products available.', 'dispensary-wp' ); ?>
				</div>

			<?php else : ?>

				<div class="dispensary-wp-product-grid">

					<?php foreach ( $products as $product ) : ?>

						<?php
						$product_id = isset( $product->id )
							? absint( $product->id )
							: 0;

						$name = isset( $product->name )
							? $product->name
							: '';

						$sku = isset( $product->sku )
							? $product->sku
							: '';

						$price = isset( $product->price )
							? $product->price
							: 0;
						?>

						<article class="dispensary-wp-product-card">

							<div class="dispensary-wp-product-content">

								<h3>
									<?php echo esc_html( $name ); ?>
								</h3>

								<?php if ( $sku ) : ?>
									<div class="dispensary-wp-product-sku">
										<?php
										echo esc_html(
											sprintf(
												/* translators: %s: SKU */
												__( 'SKU: %s', 'dispensary-wp' ),
												$sku
											)
										);
										?>
									</div>
								<?php endif; ?>

								<div class="dispensary-wp-product-price">
									<?php echo esc_html( number_format_i18n( (float) $price, 2 ) ); ?>
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

					<?php endforeach; ?>

				</div>

			<?php endif; ?>

		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Customer account shortcode.
	 *
	 * Usage:
	 * [dispensary_customer_account]
	 */
	public function customer_account() {

		ob_start();
		?>

		<div class="dispensary-wp-account">

			<div class="dispensary-wp-section-header">
				<h2><?php esc_html_e( 'Customer Account', 'dispensary-wp' ); ?></h2>
			</div>

			<?php if ( is_user_logged_in() ) : ?>

				<div class="dispensary-wp-account-card">

					<p>
						<?php
						printf(
							/* translators: %s: user display name */
							esc_html__( 'Welcome, %s.', 'dispensary-wp' ),
							esc_html( wp_get_current_user()->display_name )
						);
						?>
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

		<?php
		return ob_get_clean();
	}

	/**
	 * Order status shortcode.
	 *
	 * Usage:
	 * [dispensary_order_status]
	 */
	public function order_status() {

		ob_start();
		?>

		<div class="dispensary-wp-order-status">

			<div class="dispensary-wp-section-header">
				<h2><?php esc_html_e( 'Track Order', 'dispensary-wp' ); ?></h2>
			</div>

			<form class="dispensary-wp-order-status-form">

				<label for="dispensary-order-number">
					<?php esc_html_e( 'Order Number', 'dispensary-wp' ); ?>
				</label>

				<input
					type="text"
					id="dispensary-order-number"
					name="order_number"
					required
				>

				<button
					type="submit"
					class="dispensary-wp-button"
				>
					<?php esc_html_e( 'Check Status', 'dispensary-wp' ); ?>
				</button>

			</form>

			<div
				class="dispensary-wp-order-result"
				aria-live="polite"
			></div>

		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Loyalty shortcode.
	 *
	 * Usage:
	 * [dispensary_loyalty]
	 */
	public function loyalty() {

		ob_start();
		?>

		<div class="dispensary-wp-loyalty">

			<div class="dispensary-wp-section-header">
				<h2><?php esc_html_e( 'Loyalty Program', 'dispensary-wp' ); ?></h2>
			</div>

			<div class="dispensary-wp-loyalty-card">

				<?php if ( is_user_logged_in() ) : ?>

					<p>
						<?php esc_html_e( 'Your loyalty information will appear here.', 'dispensary-wp' ); ?>
					</p>

				<?php else : ?>

					<p>
						<?php esc_html_e( 'Please log in to view your loyalty points.', 'dispensary-wp' ); ?>
					</p>

				<?php endif; ?>

			</div>

		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Delivery tracking shortcode.
	 *
	 * Usage:
	 * [dispensary_delivery_tracking]
	 */
	public function delivery_tracking() {

		ob_start();
		?>

		<div class="dispensary-wp-delivery-tracking">

			<div class="dispensary-wp-section-header">
				<h2><?php esc_html_e( 'Delivery Tracking', 'dispensary-wp' ); ?></h2>
			</div>

			<form class="dispensary-wp-delivery-form">

				<label for="dispensary-delivery-number">
					<?php esc_html_e( 'Delivery Number', 'dispensary-wp' ); ?>
				</label>

				<input
					type="text"
					id="dispensary-delivery-number"
					name="delivery_number"
					required
				>

				<button
					type="submit"
					class="dispensary-wp-button"
				>
					<?php esc_html_e( 'Track Delivery', 'dispensary-wp' ); ?>
				</button>

			</form>

			<div
				class="dispensary-wp-delivery-result"
				aria-live="polite"
			></div>

		</div>

		<?php
		return ob_get_clean();
	}
}
