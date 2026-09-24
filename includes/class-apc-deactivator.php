<?php
/**
 * Runs on deactivation. Keep this conservative — deactivation is NOT
 * uninstall, so we do not drop tables or delete options here. We only
 * flush rewrite rules and clear scheduled events.
 *
 * @package APC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class APC_Deactivator {

    public static function deactivate() {
        flush_rewrite_rules();
    }
}
