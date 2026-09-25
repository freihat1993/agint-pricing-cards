<?php
/**
 * Admin: registers the settings page, enqueues admin assets, and handles
 * the AJAX save. The save handler is verified through APC_Security before
 * any option is written, and every field is sanitized on the way in.
 *
 * @package APC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class APC_Admin {

    const MENU_SLUG = 'agint-pricing-cards';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        // Logged-in AJAX only — settings are an admin action.
        add_action( 'wp_ajax_apc_save_settings', array( $this, 'ajax_save_settings' ) );
    }

    /**
     * Add the top-level menu page.
     */
    public function add_menu() {
        add_menu_page(
            __( 'Pricing Cards', 'agint-pricing-cards' ),
            __( 'Pricing Cards', 'agint-pricing-cards' ),
            'manage_options',
            self::MENU_SLUG,
            array( $this, 'render_settings_page' ),
            'dashicons-money-alt',
            58
        );
    }

    /**
     * Enqueue admin CSS/JS (plus the media uploader and color picker) only on
     * this plugin's screen.
     *
     * @param string $hook Current admin page hook suffix.
     */
    public function enqueue( $hook ) {
        if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
            return;
        }

        // Media library modal for per-plan icon selection.
        wp_enqueue_media();

        wp_enqueue_style(
            'agint-pricing-cards-admin',
            APC_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            APC_VERSION
        );

        wp_enqueue_script(
            'agint-pricing-cards-admin',
            APC_PLUGIN_URL . 'admin/js/admin.js',
            array( 'jquery' ),
            APC_VERSION,
            true
        );

        // Hand the script its endpoint, a fresh nonce, and i18n strings.
        wp_localize_script( 'agint-pricing-cards-admin', 'APC_Admin', array(
            'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
            'nonce'        => APC_Security::create_nonce(),
            'mediaTitle'   => __( 'Select or upload an icon', 'agint-pricing-cards' ),
            'mediaButton'  => __( 'Use this icon', 'agint-pricing-cards' ),
            'confirmRemove' => __( 'Remove this plan?', 'agint-pricing-cards' ),
            'saving'       => __( 'Saving…', 'agint-pricing-cards' ),
            'saveLabel'    => __( 'Save Changes', 'agint-pricing-cards' ),
            'newPlanTitle' => __( 'New Plan', 'agint-pricing-cards' ),
        ) );
    }

    /**
     * Render the settings page view.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $settings = wp_parse_args(
            get_option( 'apc_settings', array() ),
            APC_Plugin::default_settings()
        );

        $plans = get_option( 'apc_plans', array() );
        if ( ! is_array( $plans ) ) {
            $plans = array();
        }

        require APC_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    /**
     * AJAX: persist global settings + the plans list.
     */
    public function ajax_save_settings() {
        APC_Security::verify_ajax( 'manage_options' );

        // ---- Global settings -------------------------------------------------
        $raw_settings = isset( $_POST['settings'] ) && is_array( $_POST['settings'] )
            ? wp_unslash( $_POST['settings'] )
            : array();

        $layout = isset( $raw_settings['layout'] ) ? sanitize_key( $raw_settings['layout'] ) : 'grid';
        if ( ! in_array( $layout, array( 'grid', 'carousel' ), true ) ) {
            $layout = 'grid';
        }

        $settings = array(
            'accent_color'   => isset( $raw_settings['accent_color'] )
                ? ( sanitize_hex_color( $raw_settings['accent_color'] ) ?: '#d94645' )
                : '#d94645',
            'popular_label'  => isset( $raw_settings['popular_label'] )
                ? sanitize_text_field( $raw_settings['popular_label'] )
                : __( 'Popular', 'agint-pricing-cards' ),
            'title_width'    => isset( $raw_settings['title_width'] )
                ? min( 600, max( 40, absint( $raw_settings['title_width'] ) ) )
                : 200,
            'layout'         => $layout,
            'autoplay'       => empty( $raw_settings['autoplay'] ) ? 0 : 1,
            'autoplay_speed' => isset( $raw_settings['autoplay_speed'] )
                ? max( 1000, absint( $raw_settings['autoplay_speed'] ) )
                : 4000,
            'loop'           => empty( $raw_settings['loop'] ) ? 0 : 1,
            'arrows'         => empty( $raw_settings['arrows'] ) ? 0 : 1,
            'dots'           => empty( $raw_settings['dots'] ) ? 0 : 1,
        );

        update_option( 'apc_settings', $settings );

        // ---- Plans -----------------------------------------------------------
        $raw_plans = isset( $_POST['plans'] ) && is_array( $_POST['plans'] )
            ? wp_unslash( $_POST['plans'] )
            : array();

        $plans = $this->sanitize_plans( $raw_plans );
        update_option( 'apc_plans', $plans );

        wp_send_json_success( array(
            'message'    => __( 'Pricing cards saved.', 'agint-pricing-cards' ),
            'planCount'  => count( $plans ),
        ) );
    }

    /**
     * Sanitize the posted plans array into the canonical plan shape.
     *
     * @param array $raw_plans Raw, unslashed plans from the request.
     * @return array
     */
    private function sanitize_plans( array $raw_plans ) {
        $clean = array();

        foreach ( $raw_plans as $raw ) {
            if ( ! is_array( $raw ) ) {
                continue;
            }

            // Features may arrive as an array (one per row) — drop blanks.
            $features = array();
            if ( isset( $raw['features'] ) && is_array( $raw['features'] ) ) {
                foreach ( $raw['features'] as $feature ) {
                    $feature = sanitize_text_field( $feature );
                    if ( '' !== $feature ) {
                        $features[] = $feature;
                    }
                }
            }

            $plan = array(
                'title'       => isset( $raw['title'] ) ? sanitize_text_field( $raw['title'] ) : '',
                'currency'    => isset( $raw['currency'] ) ? sanitize_text_field( $raw['currency'] ) : '$',
                'price'       => isset( $raw['price'] ) ? sanitize_text_field( $raw['price'] ) : '',
                'period'      => isset( $raw['period'] ) ? sanitize_text_field( $raw['period'] ) : '',
                'icon_url'    => isset( $raw['icon_url'] ) ? esc_url_raw( $raw['icon_url'] ) : '',
                'show_icon'   => empty( $raw['show_icon'] ) ? 0 : 1,
                'description' => isset( $raw['description'] ) ? sanitize_textarea_field( $raw['description'] ) : '',
                'features'    => $features,
                'button_text' => isset( $raw['button_text'] ) ? sanitize_text_field( $raw['button_text'] ) : '',
                'button_url'  => isset( $raw['button_url'] ) ? esc_url_raw( $raw['button_url'] ) : '',
                'popular'     => empty( $raw['popular'] ) ? 0 : 1,
            );

            // Skip completely empty rows (e.g. an added-then-abandoned card).
            if ( '' === $plan['title'] && '' === $plan['price'] && empty( $plan['features'] ) ) {
                continue;
            }

            $clean[] = $plan;
        }

        return $clean;
    }
}
