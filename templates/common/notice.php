<?php
/**
 * Notice template.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = isset( $message )
	? $message
	: '';

$type = isset( $type )
	? sanitize_key( $type )
	: 'info';

$allowed_types = array(
	'info',
	'success',
	'warning',
	'error',
);

if ( ! in_array( $type, $allowed_types, true ) ) {
	$type = 'info';
}
?>

<div class="dispensary-wp-notice dispensary-wp-notice-<?php echo esc_attr( $type ); ?>">
	<?php echo esc_html( $message ); ?>
</div>
