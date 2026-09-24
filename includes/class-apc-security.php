<?php
/**
 * Centralised security helpers so every boundary is verified the same way.
 *
 * The point of routing nonce + capability checks through one class is that
 * AJAX handlers stay short and it is obvious at a glance that a request was
 * verified before any state changed.
 *
 * @package APC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class APC_Security {

    const NONCE_ACTION = 'apc_ajax';

    /**
     * Verify an AJAX request: valid nonce AND sufficient capability.
     * Sends a JSON error and dies if either check fails.
     *
     * @param string $capability Capability the user must have.
     */
    public static function verify_ajax( $capability = 'manage_options' ) {
        // check_ajax_referer dies with -1 on failure when $die is true.
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( $capability ) ) {
            wp_send_json_error(
                array( 'message' => __( 'You are not allowed to do this.', 'agint-pricing-cards' ) ),
                403
            );
        }
    }

    /**
     * Recursively sanitize an array of input (e.g. a posted settings form).
     * Scalars are run through sanitize_text_field; nest as needed.
     *
     * @param mixed $value Raw input.
     * @return mixed Sanitized value.
     */
    public static function sanitize_recursive( $value ) {
        if ( is_array( $value ) ) {
            return array_map( array( __CLASS__, 'sanitize_recursive' ), $value );
        }
        return sanitize_text_field( wp_unslash( $value ) );
    }

    /**
     * Create a nonce for the shared AJAX action.
     *
     * @return string
     */
    public static function create_nonce() {
        return wp_create_nonce( self::NONCE_ACTION );
    }
}
