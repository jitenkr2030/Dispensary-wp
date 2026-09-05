<?php
/**
 * PHPUnit bootstrap placeholder.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'DISPENSARY_WP_DIR' ) ) {
	define(
		'DISPENSARY_WP_DIR',
		dirname( __DIR__ ) . '/'
	);
}

/**
 * This file intentionally does not execute tests.
 *
 * WordPress integration testing will be configured when
 * the test environment is installed.
 */
