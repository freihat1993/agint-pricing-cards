<?php
/**
 * Frontend: registers the pricing-cards shortcode and enqueues assets only
 * when the shortcode is actually present, so we don't load CSS site-wide.
 *
 * @package APC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class APC_Frontend {

    const SHORTCODE = 'agint_pricing_cards';

    public function __construct() {
        add_shortcode( self::SHORTCODE, array( $this, 'render_shortcode' ) );
        // Short alias for convenience.
        add_shortcode( 'apc_pricing_cards', array( $this, 'render_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    /**
     * Register (don't enqueue) assets up front; enqueue lazily in the
     * shortcode callback so they only load on pages that use it.
     */
    public function register_assets() {
        wp_register_style(
            'agint-pricing-cards-frontend',
            APC_PLUGIN_URL . 'public/css/frontend.css',
            array(),
            APC_VERSION
        );

        wp_register_script(
            'agint-pricing-cards-frontend',
            APC_PLUGIN_URL . 'public/js/frontend.js',
            array(),
            APC_VERSION,
            true
        );
    }

    /**
     * Shortcode handler. Returns markup (never echoes).
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'columns' => 3,  // cards per row/view on desktop (1–4)
                'layout'  => '', // '', 'grid' or 'carousel' — overrides the saved setting
            ),
            $atts,
            self::SHORTCODE
        );

        wp_enqueue_style( 'agint-pricing-cards-frontend' );

        $settings = wp_parse_args(
            get_option( 'apc_settings', array() ),
            APC_Plugin::default_settings()
        );

        $plans = get_option( 'apc_plans', array() );
        if ( ! is_array( $plans ) ) {
            $plans = array();
        }

        $columns = max( 1, min( 4, absint( $atts['columns'] ) ) );

        // Effective layout: shortcode attribute wins, else the saved setting.
        $layout = strtolower( (string) $atts['layout'] );
        if ( ! in_array( $layout, array( 'grid', 'carousel' ), true ) ) {
            $layout = ! empty( $settings['layout'] ) ? $settings['layout'] : 'grid';
        }
        $is_carousel = ( 'carousel' === $layout );

        // The carousel needs its script; the grid doesn't.
        if ( $is_carousel ) {
            wp_enqueue_script( 'agint-pricing-cards-frontend' );
        }

        ob_start();
        require APC_PLUGIN_DIR . 'public/views/widget.php';
        return ob_get_clean();
    }
}
