<?php
/**
 * Main orchestrator. Loads textdomain and wires up admin + frontend.
 *
 * @package APC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class APC_Plugin {

    /**
     * @var APC_Plugin|null
     */
    private static $instance = null;

    /**
     * Singleton accessor.
     *
     * @return APC_Plugin
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Register hooks and instantiate the major subsystems.
     */
    public function run() {
        add_action( 'init', array( $this, 'load_textdomain' ) );

        if ( is_admin() ) {
            new APC_Admin();
        }

        new APC_Frontend();
    }

    /**
     * Load translations from /languages.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'agint-pricing-cards',
            false,
            dirname( APC_PLUGIN_BASENAME ) . '/languages'
        );
    }

    /**
     * Global (non per-plan) settings and their defaults.
     *
     * @return array
     */
    public static function default_settings() {
        return array(
            'accent_color'   => '#d94645',
            'popular_label'  => __( 'Popular', 'agint-pricing-cards' ),
            'title_width'    => 200,      // px width of the plan title
            // Display / carousel.
            'layout'         => 'grid',   // 'grid' | 'carousel'
            'autoplay'       => 0,
            'autoplay_speed' => 4000,     // ms
            'loop'           => 1,
            'arrows'         => 1,
            'dots'           => 1,
        );
    }

    /**
     * The shape of a single plan, with sensible empty defaults. Any saved
     * plan is merged onto this so views can read every key without isset().
     *
     * @return array
     */
    public static function plan_defaults() {
        return array(
            'title'       => '',
            'currency'    => '$',
            'price'       => '',
            'period'      => __( '/Per Month', 'agint-pricing-cards' ),
            'icon_url'    => '',
            'show_icon'   => 1,
            'description' => '',
            'features'    => array(),
            'button_text' => __( 'Sign Up For Free', 'agint-pricing-cards' ),
            'button_url'  => '#',
            'popular'     => 0,
        );
    }

    /**
     * Demo plans seeded on activation so the shortcode shows something
     * immediately. Mirrors the three-column reference layout.
     *
     * @return array
     */
    public static function default_plans() {
        $features = array(
            __( 'Unlimited paid ticket', 'agint-pricing-cards' ),
            __( 'Google analytics integration', 'agint-pricing-cards' ),
            __( 'Customizable registration', 'agint-pricing-cards' ),
            __( '500 Email invitations event', 'agint-pricing-cards' ),
            __( 'Event reminders', 'agint-pricing-cards' ),
            __( 'Registration form', 'agint-pricing-cards' ),
        );

        $base = self::plan_defaults();

        return array(
            array_merge( $base, array(
                'title'       => __( 'Basic Plan', 'agint-pricing-cards' ),
                'price'       => '1.99',
                'description' => __( 'For individuals getting started with their first event.', 'agint-pricing-cards' ),
                'features'    => $features,
                'popular'     => 0,
            ) ),
            array_merge( $base, array(
                'title'       => __( 'Starter Plan', 'agint-pricing-cards' ),
                'price'       => '2.99',
                'description' => __( 'The most popular choice for growing teams and events.', 'agint-pricing-cards' ),
                'features'    => $features,
                'popular'     => 1,
            ) ),
            array_merge( $base, array(
                'title'       => __( 'Premium Plan', 'agint-pricing-cards' ),
                'price'       => '3.99',
                'description' => __( 'Everything you need to run large, unlimited events.', 'agint-pricing-cards' ),
                'features'    => $features,
                'popular'     => 0,
            ) ),
        );
    }
}
