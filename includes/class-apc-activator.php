<?php
/**
 * Runs on activation: seed default options.
 *
 * This plugin stores its data in two options (global settings + a list of
 * plans), so there are no custom tables to create — activation only seeds
 * defaults, and only when they are absent so a reactivation never clobbers
 * the user's saved plans.
 *
 * @package APC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class APC_Activator {

    /**
     * Seed default options.
     */
    public static function activate() {
        if ( false === get_option( 'apc_settings' ) ) {
            add_option( 'apc_settings', APC_Plugin::default_settings() );
        }

        if ( false === get_option( 'apc_plans' ) ) {
            add_option( 'apc_plans', APC_Plugin::default_plans() );
        }

        add_option( 'apc_db_version', APC_VERSION );

        flush_rewrite_rules();
    }
}
