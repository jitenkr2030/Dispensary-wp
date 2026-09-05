<?php
/**
 * Template Loader.
 *
 * @package Dispensary_WP
 */

namespace Dispensary_WP\Templates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Template_Loader {

	/**
	 * Template directory.
	 *
	 * @var string
	 */
	private $template_dir;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->template_dir = trailingslashit( DISPENSARY_WP_DIR . 'templates' );
	}

	/**
	 * Locate a template.
	 *
	 * @param string $template Template name.
	 * @return string
	 */
	public function locate( $template ) {

		$template = ltrim( $template, '/' );

		$plugin_template = $this->template_dir . $template . '.php';

		$theme_template = locate_template(
			array(
				'dispensary-wp/' . $template . '.php',
			)
		);

		if ( $theme_template ) {
			return $theme_template;
		}

		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return '';
	}

	/**
	 * Render template.
	 *
	 * @param string $template Template name.
	 * @param array  $args Arguments.
	 * @return string
	 */
	public function render( $template, $args = array() ) {

		$file = $this->locate( $template );

		if ( ! $file ) {
			return '';
		}

		if ( ! is_array( $args ) ) {
			$args = array();
		}

		ob_start();

		extract( $args, EXTR_SKIP );

		include $file;

		return ob_get_clean();
	}

	/**
	 * Output template.
	 *
	 * @param string $template Template name.
	 * @param array  $args Arguments.
	 */
	public function display( $template, $args = array() ) {

		echo $this->render( $template, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
