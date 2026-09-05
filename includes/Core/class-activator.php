<?php
namespace Dispensary_WP\Core;

use Dispensary_WP\Database\Database;
use Dispensary_WP\Database\Schema;
use Dispensary_WP\Permissions\Roles;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Activator {

    public static function activate() {

        try {

            // STEP 1
            if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
                wp_die(
                    esc_html__(
                        'Dispensary WP requires PHP 7.4 or newer.',
                        'dispensary-wp'
                    )
                );
            }

            // STEP 2
            if ( version_compare( get_bloginfo( 'version' ), '6.5', '<' ) ) {
                wp_die(
                    esc_html__(
                        'Dispensary WP requires WordPress 6.5 or newer.',
                        'dispensary-wp'
                    )
                );
            }

            // STEP 3
            if ( false === get_option( 'dispensary_wp_settings', false ) ) {
                add_option(
                    'dispensary_wp_settings',
                    array(
                        'enabled'  => true,
                        'currency' => 'USD',
                    ),
                    '',
                    false
                );
            }

            // STEP 4
            $database = Database::instance();

            // STEP 5
            Schema::install();

            // STEP 6
            $database->set_version( Database::DB_VERSION );

            // STEP 7
            Roles::install();

            // STEP 8
            update_option(
                'dispensary_wp_version',
                DISPENSARY_WP_VERSION,
                false
            );

            // STEP 9
            flush_rewrite_rules();

        } catch ( \Throwable $e ) {

            wp_die(
                esc_html(
                    sprintf(
                        'DISPENSARY WP ACTIVATION FAILED: %s | FILE: %s | LINE: %d',
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    )
                )
            );
        }
    }
}
