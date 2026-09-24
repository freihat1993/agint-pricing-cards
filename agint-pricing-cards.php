<?php
/**
 * Plugin Name:       Agint Pricing Cards
 * Description:       Responsive pricing cards with an admin manager: add/edit/reorder plans, per-card icon toggle, description above the feature list, and a Popular badge. Render anywhere with [agint_pricing_cards].
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Mohammed Freihat
 * License:           GPL-2.0-or-later
 * Text Domain:       agint-pricing-cards
 * Domain Path:       /languages
 *
 * @package APC
 */

// Abort if this file is called directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'APC_VERSION', '1.0.0' );
define( 'APC_PLUGIN_FILE', __FILE__ );
define( 'APC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'APC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'APC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Core classes.
require_once APC_PLUGIN_DIR . 'includes/class-apc-security.php';
require_once APC_PLUGIN_DIR . 'includes/class-apc-activator.php';
require_once APC_PLUGIN_DIR . 'includes/class-apc-deactivator.php';
require_once APC_PLUGIN_DIR . 'includes/class-apc-plugin.php';
require_once APC_PLUGIN_DIR . 'admin/class-apc-admin.php';
require_once APC_PLUGIN_DIR . 'public/class-apc-frontend.php';

// Activation / deactivation must be registered in the main file.
register_activation_hook( __FILE__, array( 'APC_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'APC_Deactivator', 'deactivate' ) );

/**
 * Boot the plugin on plugins_loaded so all dependencies are available.
 */
function apc_run() {
    APC_Plugin::instance()->run();
}
add_action( 'plugins_loaded', 'apc_run' );
