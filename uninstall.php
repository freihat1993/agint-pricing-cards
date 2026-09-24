<?php
/**
 * Fires when the plugin is deleted from the Plugins screen. This is where
 * destructive cleanup belongs (NOT deactivation). Guard against direct
 * access by checking the WP_UNINSTALL_PLUGIN constant.
 *
 * @package APC
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove all options this plugin created.
delete_option( 'apc_settings' );
delete_option( 'apc_plans' );
delete_option( 'apc_db_version' );
