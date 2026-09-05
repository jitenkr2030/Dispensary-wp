<?php
/**
 * Template helper functions.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template loader.
 *
 * @return Template_Loader
 */
function loader() {

	static $instance = null;

	if ( null === $instance ) {
		$instance = new Template_Loader();
	}

	return $instance;
}

/**
 * Render template.
 *
 * @param string $template Template.
 * @param array  $args Arguments.
 * @return string
 */
function render( $template, $args = array() ) {

	return loader()->render( $template, $args );
}

/**
 * Display template.
 *
 * @param string $template Template.
 * @param array  $args Arguments.
 */
function display( $template, $args = array() ) {

	loader()->display( $template, $args );
}

/**
 * Format money.
 *
 * @param float $amount Amount.
 * @param string $currency Currency.
 * @return string
 */
function money( $amount, $currency = '' ) {

	if ( '' === $currency ) {
		$currency = 'USD';
	}

	return esc_html(
		$currency . ' ' . number_format_i18n( (float) $amount, 2 )
	);
}

/**
 * Format status.
 *
 * @param string $status Status.
 * @return string
 */
function status_label( $status ) {

	$status = sanitize_key( $status );

	return ucwords(
		str_replace( '_', ' ', $status )
	);
}
