<?php

namespace Dispensary_WP\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Loader {

    private static $instance = null;

    private $actions = array();

    private $filters = array();

    public static function instance() {

        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {}

    /**
     * Add an action.
     *
     * Supports:
     * - add_action( $hook, $callback, $priority, $accepted_args )
     * - add_action( $hook, $object, $method, $priority, $accepted_args )
     */
    public function add_action(
        $hook,
        $callback,
        $method = null,
        $priority = 10,
        $accepted_args = 1
    ) {

        if ( null !== $method ) {

            $callback = array(
                $callback,
                $method,
            );
        }

        $this->actions[] = array(
            'hook'          => $hook,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );
    }

    /**
     * Add a filter.
     *
     * Supports:
     * - add_filter( $hook, $callback, $priority, $accepted_args )
     * - add_filter( $hook, $object, $method, $priority, $accepted_args )
     */
    public function add_filter(
        $hook,
        $callback,
        $method = null,
        $priority = 10,
        $accepted_args = 1
    ) {

        if ( null !== $method ) {

            $callback = array(
                $callback,
                $method,
            );
        }

        $this->filters[] = array(
            'hook'          => $hook,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        );
    }

    /**
     * Register all hooks with WordPress.
     */
    public function run() {

        foreach ( $this->filters as $filter ) {

            add_filter(
                $filter['hook'],
                $filter['callback'],
                $filter['priority'],
                $filter['accepted_args']
            );
        }

        foreach ( $this->actions as $action ) {

            add_action(
                $action['hook'],
                $action['callback'],
                $action['priority'],
                $action['accepted_args']
            );
        }
    }
}
