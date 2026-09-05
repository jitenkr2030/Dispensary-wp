<?php
/**
 * Empty state template.
 *
 * @package Dispensary_WP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$message = isset( $message )
	? $message
	: __( 'No records found.', 'dispensary-wp' );
?>

<div class="dispensary-wp-empty">
	<?php echo esc_html( $message ); ?>
</div>
