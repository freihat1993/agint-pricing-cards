<?php
/**
 * Settings page view — master/detail admin. $settings and $plans are provided
 * by the render method.
 *
 * @package APC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * A left-hand list row (summary) for one plan.
 *
 * @param array      $plan Plan data.
 * @param int|string $uid  Unique id shared with the matching editor panel.
 */
function apc_render_plan_item( $plan, $uid ) {
    $plan    = wp_parse_args( $plan, APC_Plugin::plan_defaults() );
    $name    = $plan['title'] ? $plan['title'] : __( 'New Plan', 'agint-pricing-cards' );
    $meta    = trim( $plan['currency'] . $plan['price'] . ' ' . $plan['period'] );
    $popular = ! empty( $plan['popular'] );
    ?>
    <li class="apc-plan-item" data-uid="<?php echo esc_attr( $uid ); ?>" tabindex="0">
        <span class="apc-plan-item__handle dashicons dashicons-menu" title="<?php esc_attr_e( 'Drag to reorder', 'agint-pricing-cards' ); ?>"></span>
        <span class="apc-plan-item__body">
            <span class="apc-plan-item__name"><?php echo esc_html( $name ); ?></span>
            <span class="apc-plan-item__meta"><?php echo esc_html( $meta ); ?></span>
        </span>
        <span class="apc-plan-item__star dashicons dashicons-star-filled" title="<?php esc_attr_e( 'Popular', 'agint-pricing-cards' ); ?>" <?php echo $popular ? '' : 'hidden'; ?>></span>
    </li>
    <?php
}

/**
 * The right-hand editor panel for one plan.
 *
 * @param array      $plan Plan data.
 * @param int|string $uid  Unique id shared with the matching list row.
 */
function apc_render_plan_panel( $plan, $uid ) {
    $plan      = wp_parse_args( $plan, APC_Plugin::plan_defaults() );
    $features  = is_array( $plan['features'] ) ? implode( "\n", $plan['features'] ) : '';
    $show_icon = ! empty( $plan['show_icon'] );
    $popular   = ! empty( $plan['popular'] );
    $has_icon  = ! empty( $plan['icon_url'] );
    ?>
    <div class="apc-plan" data-uid="<?php echo esc_attr( $uid ); ?>">
        <div class="apc-editor__head">
            <h2 class="apc-editor__title"><?php esc_html_e( 'Edit plan', 'agint-pricing-cards' ); ?></h2>
            <div class="apc-editor__actions">
                <button type="button" class="apc-btn apc-btn--icon apc-move-up" title="<?php esc_attr_e( 'Move up', 'agint-pricing-cards' ); ?>"><span class="dashicons dashicons-arrow-up-alt2"></span></button>
                <button type="button" class="apc-btn apc-btn--icon apc-move-down" title="<?php esc_attr_e( 'Move down', 'agint-pricing-cards' ); ?>"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                <button type="button" class="apc-btn apc-btn--ghost apc-duplicate"><span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Duplicate', 'agint-pricing-cards' ); ?></button>
                <button type="button" class="apc-btn apc-btn--danger apc-remove-plan"><span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Delete', 'agint-pricing-cards' ); ?></button>
            </div>
        </div>

        <!-- BASICS -->
        <div class="apc-section">
            <div class="apc-section__label"><?php esc_html_e( 'Basics', 'agint-pricing-cards' ); ?></div>

            <div class="apc-field">
                <label><?php esc_html_e( 'Plan title', 'agint-pricing-cards' ); ?></label>
                <input type="text" class="apc-input" data-field="title" value="<?php echo esc_attr( $plan['title'] ); ?>" placeholder="<?php esc_attr_e( 'Basic Plan', 'agint-pricing-cards' ); ?>" />
            </div>

            <div class="apc-grid-3">
                <div class="apc-field">
                    <label><?php esc_html_e( 'Currency', 'agint-pricing-cards' ); ?></label>
                    <input type="text" class="apc-input" data-field="currency" value="<?php echo esc_attr( $plan['currency'] ); ?>" placeholder="$" />
                </div>
                <div class="apc-field">
                    <label><?php esc_html_e( 'Price', 'agint-pricing-cards' ); ?></label>
                    <input type="text" class="apc-input" data-field="price" value="<?php echo esc_attr( $plan['price'] ); ?>" placeholder="1.99" />
                </div>
                <div class="apc-field">
                    <label><?php esc_html_e( 'Period', 'agint-pricing-cards' ); ?></label>
                    <input type="text" class="apc-input" data-field="period" value="<?php echo esc_attr( $plan['period'] ); ?>" placeholder="<?php esc_attr_e( '/Per Month', 'agint-pricing-cards' ); ?>" />
                </div>
            </div>

            <label class="apc-check">
                <input type="checkbox" data-field="popular" <?php checked( $popular ); ?> />
                <span><strong><?php esc_html_e( 'Mark as Popular', 'agint-pricing-cards' ); ?></strong> <span class="apc-check__hint"><?php esc_html_e( '— highlights the card and shows the badge', 'agint-pricing-cards' ); ?></span></span>
            </label>
        </div>

        <!-- CONTENT -->
        <div class="apc-section">
            <div class="apc-section__label"><?php esc_html_e( 'Content', 'agint-pricing-cards' ); ?></div>

            <div class="apc-field">
                <div class="apc-icon-row">
                    <span class="apc-icon-preview<?php echo $has_icon ? '' : ' is-empty'; ?>">
                        <?php if ( $has_icon ) : ?><img src="<?php echo esc_url( $plan['icon_url'] ); ?>" alt="" /><?php endif; ?>
                    </span>
                    <input type="hidden" data-field="icon_url" value="<?php echo esc_attr( $plan['icon_url'] ); ?>" />
                    <button type="button" class="apc-btn apc-btn--ghost apc-pick-icon"><?php esc_html_e( 'Select icon', 'agint-pricing-cards' ); ?></button>
                    <button type="button" class="apc-link apc-clear-icon"><?php esc_html_e( 'Remove', 'agint-pricing-cards' ); ?></button>
                    <label class="apc-check apc-check--inline">
                        <input type="checkbox" data-field="show_icon" <?php checked( $show_icon ); ?> />
                        <span><?php esc_html_e( 'Show icon on card', 'agint-pricing-cards' ); ?></span>
                    </label>
                </div>
            </div>

            <div class="apc-field">
                <label><?php esc_html_e( 'Description', 'agint-pricing-cards' ); ?></label>
                <span class="apc-sublabel"><?php esc_html_e( 'Shown above the feature list', 'agint-pricing-cards' ); ?></span>
                <textarea class="apc-input" rows="2" data-field="description" placeholder="<?php esc_attr_e( 'A short sentence describing who this plan is for.', 'agint-pricing-cards' ); ?>"><?php echo esc_textarea( $plan['description'] ); ?></textarea>
            </div>

            <div class="apc-field">
                <label><?php esc_html_e( 'Features', 'agint-pricing-cards' ); ?></label>
                <span class="apc-sublabel"><?php esc_html_e( 'One per line', 'agint-pricing-cards' ); ?></span>
                <textarea class="apc-input apc-mono" rows="6" data-field="features" placeholder="<?php esc_attr_e( "Unlimited paid ticket\nGoogle analytics integration\nEvent reminders", 'agint-pricing-cards' ); ?>"><?php echo esc_textarea( $features ); ?></textarea>
            </div>
        </div>

        <!-- BUTTON -->
        <div class="apc-section">
            <div class="apc-section__label"><?php esc_html_e( 'Button', 'agint-pricing-cards' ); ?></div>
            <div class="apc-grid-2">
                <div class="apc-field">
                    <label><?php esc_html_e( 'Button text', 'agint-pricing-cards' ); ?></label>
                    <input type="text" class="apc-input" data-field="button_text" value="<?php echo esc_attr( $plan['button_text'] ); ?>" placeholder="<?php esc_attr_e( 'Sign Up For Free', 'agint-pricing-cards' ); ?>" />
                </div>
                <div class="apc-field">
                    <label><?php esc_html_e( 'Button link (URL)', 'agint-pricing-cards' ); ?></label>
                    <input type="url" class="apc-input" data-field="button_url" value="<?php echo esc_attr( $plan['button_url'] ); ?>" placeholder="https://" />
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Accent-colour presets for the swatch picker.
$apc_presets = array( '#d94645', '#2271b1', '#2e9e5b', '#e0a935', '#1d1d1d' );
?>
<div class="wrap apc-admin">

    <div class="apc-topbar">
        <div class="apc-brand">
            <span class="apc-brand__mark dashicons dashicons-money-alt"></span>
            <div>
                <h1 class="apc-brand__title"><?php esc_html_e( 'Pricing Cards', 'agint-pricing-cards' ); ?></h1>
                <p class="apc-brand__sub"><?php esc_html_e( 'Design and manage your pricing plans.', 'agint-pricing-cards' ); ?></p>
            </div>
        </div>
        <div class="apc-shortcode">
            <span><?php esc_html_e( 'Shortcode', 'agint-pricing-cards' ); ?></span>
            <code id="apc-shortcode-code">[agint_pricing_cards]</code>
            <button type="button" class="apc-btn apc-btn--ghost" id="apc-copy-shortcode"><?php esc_html_e( 'Copy', 'agint-pricing-cards' ); ?></button>
        </div>
    </div>

    <div id="apc-notice" class="apc-notice" style="display:none;"></div>

    <nav class="apc-tabs">
        <button type="button" class="apc-tab is-active" data-tab="plans"><?php esc_html_e( 'Plans', 'agint-pricing-cards' ); ?></button>
        <button type="button" class="apc-tab" data-tab="design"><?php esc_html_e( 'Design & Display', 'agint-pricing-cards' ); ?></button>
    </nav>

    <form id="apc-settings-form">

        <!-- ============ PLANS TAB ============ -->
        <div class="apc-tab-panel is-active" data-panel="plans">
            <div class="apc-plans-layout">

                <aside class="apc-list-card">
                    <div class="apc-list-head">
                        <strong><?php esc_html_e( 'Plans', 'agint-pricing-cards' ); ?> <span class="apc-list-count"></span></strong>
                        <button type="button" class="apc-btn apc-btn--primary apc-btn--sm" id="apc-add-plan">
                            <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add plan', 'agint-pricing-cards' ); ?>
                        </button>
                    </div>
                    <ul id="apc-plan-list" class="apc-plan-list">
                        <?php
                        if ( ! empty( $plans ) ) {
                            foreach ( $plans as $i => $plan ) {
                                apc_render_plan_item( $plan, 'p' . $i );
                            }
                        }
                        ?>
                    </ul>
                </aside>

                <section class="apc-editor-card">
                    <div id="apc-plans" class="apc-editor-stack">
                        <?php
                        if ( ! empty( $plans ) ) {
                            foreach ( $plans as $i => $plan ) {
                                apc_render_plan_panel( $plan, 'p' . $i );
                            }
                        }
                        ?>
                    </div>
                    <div class="apc-editor-empty" <?php echo empty( $plans ) ? '' : 'hidden'; ?>>
                        <span class="dashicons dashicons-money-alt"></span>
                        <p><?php esc_html_e( 'No plan selected. Add a plan or pick one from the list.', 'agint-pricing-cards' ); ?></p>
                    </div>
                </section>
            </div>
        </div>

        <!-- ============ DESIGN & DISPLAY TAB ============ -->
        <div class="apc-tab-panel" data-panel="design">
            <div class="apc-design-grid">

                <div class="apc-card-box">
                    <h2 class="apc-box-title"><?php esc_html_e( 'Style', 'agint-pricing-cards' ); ?></h2>
                    <div class="apc-field">
                        <label><?php esc_html_e( 'Accent color', 'agint-pricing-cards' ); ?></label>
                        <div class="apc-swatches">
                            <input type="hidden" data-field="accent_color" value="<?php echo esc_attr( $settings['accent_color'] ); ?>" />
                            <?php foreach ( $apc_presets as $c ) : ?>
                                <button type="button" class="apc-swatch" data-color="<?php echo esc_attr( $c ); ?>" style="--sw: <?php echo esc_attr( $c ); ?>;" aria-label="<?php echo esc_attr( $c ); ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <p class="apc-help"><?php esc_html_e( 'Used for price, bullets, badge, hover border and button.', 'agint-pricing-cards' ); ?></p>
                    </div>
                    <div class="apc-field">
                        <label for="apc-popular-label"><?php esc_html_e( 'Popular badge text', 'agint-pricing-cards' ); ?></label>
                        <input type="text" id="apc-popular-label" class="apc-input" data-field="popular_label" value="<?php echo esc_attr( $settings['popular_label'] ); ?>" />
                    </div>
                    <div class="apc-field apc-field--narrow">
                        <label for="apc-title-width"><?php esc_html_e( 'Card title width (px)', 'agint-pricing-cards' ); ?></label>
                        <input type="number" id="apc-title-width" class="apc-input" data-field="title_width" min="40" max="600" step="10" value="<?php echo esc_attr( $settings['title_width'] ); ?>" />
                        <p class="apc-help"><?php esc_html_e( 'Fixed width of the plan title on the card. Default 200.', 'agint-pricing-cards' ); ?></p>
                    </div>
                </div>

                <div class="apc-card-box">
                    <h2 class="apc-box-title"><?php esc_html_e( 'Layout', 'agint-pricing-cards' ); ?></h2>
                    <div class="apc-field">
                        <div class="apc-segmented">
                            <label class="apc-seg">
                                <input type="radio" name="apc-layout" data-field="layout" value="grid" <?php checked( $settings['layout'], 'grid' ); ?> />
                                <span><?php esc_html_e( 'Grid', 'agint-pricing-cards' ); ?></span>
                            </label>
                            <label class="apc-seg">
                                <input type="radio" name="apc-layout" data-field="layout" value="carousel" <?php checked( $settings['layout'], 'carousel' ); ?> />
                                <span><?php esc_html_e( 'Carousel', 'agint-pricing-cards' ); ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="apc-carousel-opts" <?php echo ( 'carousel' === $settings['layout'] ) ? '' : 'hidden'; ?>>
                        <label class="apc-check">
                            <input type="checkbox" data-field="arrows" <?php checked( ! empty( $settings['arrows'] ) ); ?> />
                            <span><?php esc_html_e( 'Show prev / next arrows', 'agint-pricing-cards' ); ?></span>
                        </label>
                        <label class="apc-check">
                            <input type="checkbox" data-field="dots" <?php checked( ! empty( $settings['dots'] ) ); ?> />
                            <span><?php esc_html_e( 'Show pagination dots', 'agint-pricing-cards' ); ?></span>
                        </label>
                        <label class="apc-check">
                            <input type="checkbox" data-field="loop" <?php checked( ! empty( $settings['loop'] ) ); ?> />
                            <span><?php esc_html_e( 'Loop around at the ends', 'agint-pricing-cards' ); ?></span>
                        </label>
                        <label class="apc-check">
                            <input type="checkbox" data-field="autoplay" <?php checked( ! empty( $settings['autoplay'] ) ); ?> />
                            <span><?php esc_html_e( 'Autoplay', 'agint-pricing-cards' ); ?></span>
                        </label>
                        <div class="apc-field apc-field--narrow" style="margin-top:12px;">
                            <label for="apc-autoplay-speed"><?php esc_html_e( 'Autoplay speed (ms)', 'agint-pricing-cards' ); ?></label>
                            <input type="number" id="apc-autoplay-speed" class="apc-input" data-field="autoplay_speed" min="1000" step="500" value="<?php echo esc_attr( $settings['autoplay_speed'] ); ?>" />
                            <p class="apc-help"><?php esc_html_e( 'Time each slide stays before advancing. Minimum 1000 ms.', 'agint-pricing-cards' ); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ STICKY SAVE BAR ============ -->
        <div class="apc-savebar">
            <span class="apc-savebar__status" id="apc-status"><?php esc_html_e( 'All changes saved', 'agint-pricing-cards' ); ?></span>
            <button type="submit" class="apc-btn apc-btn--primary" id="apc-save"><?php esc_html_e( 'Save changes', 'agint-pricing-cards' ); ?></button>
        </div>
    </form>

    <?php // Hidden templates for JS add/duplicate. ?>
    <script type="text/html" id="apc-item-template"><?php apc_render_plan_item( APC_Plugin::plan_defaults(), '__UID__' ); ?></script>
    <script type="text/html" id="apc-panel-template"><?php apc_render_plan_panel( APC_Plugin::plan_defaults(), '__UID__' ); ?></script>
</div>
