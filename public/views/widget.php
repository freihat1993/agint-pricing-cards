<?php
/**
 * Frontend widget view. $atts, $settings, $plans, $columns and $is_carousel
 * are in scope.
 *
 * Markup mirrors the reference "Noile" pricing structure so the CSS can
 * reproduce the exact hover behaviour. The same card markup is reused for the
 * grid and the carousel layouts.
 *
 * @package APC
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$accent        = ! empty( $settings['accent_color'] ) ? $settings['accent_color'] : '#d94645';
$popular_label = ! empty( $settings['popular_label'] ) ? $settings['popular_label'] : __( 'Popular', 'agint-pricing-cards' );

if ( empty( $plans ) ) {
    if ( current_user_can( 'manage_options' ) ) {
        echo '<p class="apc-empty">' . esc_html__( 'No pricing plans yet — add some under “Pricing Cards” in the admin menu.', 'agint-pricing-cards' ) . '</p>';
    }
    return;
}

/**
 * Render a single pricing card. Guarded so multiple shortcodes on one page
 * don't redeclare it.
 *
 * @param array  $plan          Plan data merged onto plan_defaults().
 * @param string $popular_label Text for the "Popular" ribbon.
 */
if ( ! function_exists( 'apc_render_card' ) ) {
    function apc_render_card( $plan, $popular_label ) {
        $plan       = wp_parse_args( $plan, APC_Plugin::plan_defaults() );
        $is_popular = ! empty( $plan['popular'] );
        $show_icon  = ! empty( $plan['show_icon'] ) && ! empty( $plan['icon_url'] );
        $features   = is_array( $plan['features'] ) ? $plan['features'] : array();
        ?>
        <div class="apc-card<?php echo $is_popular ? ' is-popular' : ''; ?>">

            <?php
            $badge = ( '' !== $plan['badge_text'] ) ? $plan['badge_text'] : $popular_label;
            if ( $is_popular && '' !== $badge ) : ?>
                <span class="apc-card__ribbon"><?php echo esc_html( $badge ); ?></span>
            <?php endif; ?>

            <div class="apc-card__head">
                <div class="apc-card__title-box">
                    <?php if ( $show_icon ) : ?>
                        <span class="apc-card__icon">
                            <img src="<?php echo esc_url( $plan['icon_url'] ); ?>" alt="" />
                        </span>
                    <?php endif; ?>

                    <div class="apc-card__title-inner">
                        <h3 class="apc-card__title"><?php echo esc_html( $plan['title'] ); ?></h3>
                        <?php if ( '' !== $plan['period'] ) : ?>
                            <span class="apc-card__period"><?php echo esc_html( $plan['period'] ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ( '' !== $plan['price'] ) : ?>
                    <div class="apc-card__price">
                        <span class="apc-card__currency"><?php echo esc_html( $plan['currency'] ); ?></span><span class="apc-card__amount"><?php echo esc_html( $plan['price'] ); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="apc-card__divider"></div>

            <?php if ( '' !== $plan['description'] ) : ?>
                <p class="apc-card__desc"><?php echo esc_html( $plan['description'] ); ?></p>
            <?php endif; ?>

            <?php if ( ! empty( $features ) ) : ?>
                <ul class="apc-card__features">
                    <?php foreach ( $features as $feature ) : ?>
                        <li><?php echo esc_html( $feature ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if ( '' !== $plan['button_text'] ) : ?>
                <a class="apc-card__btn" href="<?php echo esc_url( $plan['button_url'] ? $plan['button_url'] : '#' ); ?>">
                    <?php echo esc_html( $plan['button_text'] ); ?> <span class="apc-card__btn-plus">+</span>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}

$title_width = isset( $settings['title_width'] ) ? absint( $settings['title_width'] ) : 200;
$root_style  = '--apc-accent: ' . esc_attr( $accent ) . '; --apc-title-width: ' . esc_attr( $title_width ) . 'px;';

if ( $is_carousel ) :
    $autoplay = ! empty( $settings['autoplay'] ) ? 1 : 0;
    $speed    = isset( $settings['autoplay_speed'] ) ? absint( $settings['autoplay_speed'] ) : 4000;
    $loop     = ! empty( $settings['loop'] ) ? 1 : 0;
    $arrows   = ! empty( $settings['arrows'] );
    $dots     = ! empty( $settings['dots'] );
    ?>
    <div class="apc-pricing apc-carousel apc-cols-<?php echo esc_attr( $columns ); ?>"
         style="<?php echo esc_attr( $root_style ); ?>"
         data-columns="<?php echo esc_attr( $columns ); ?>"
         data-autoplay="<?php echo esc_attr( $autoplay ); ?>"
         data-speed="<?php echo esc_attr( $speed ); ?>"
         data-loop="<?php echo esc_attr( $loop ); ?>">

        <div class="apc-carousel__viewport">
            <div class="apc-carousel__track">
                <?php foreach ( $plans as $plan ) : ?>
                    <div class="apc-slide"><?php apc_render_card( $plan, $popular_label ); ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ( $arrows || $dots ) : ?>
            <div class="apc-carousel__nav">
                <?php if ( $arrows ) : ?>
                    <button type="button" class="apc-carousel__arrow apc-carousel__prev" aria-label="<?php esc_attr_e( 'Previous', 'agint-pricing-cards' ); ?>">
                        <span aria-hidden="true">&#8249;</span>
                    </button>
                <?php endif; ?>

                <?php if ( $dots ) : ?>
                    <div class="apc-carousel__dots" aria-hidden="true"></div>
                <?php endif; ?>

                <?php if ( $arrows ) : ?>
                    <button type="button" class="apc-carousel__arrow apc-carousel__next" aria-label="<?php esc_attr_e( 'Next', 'agint-pricing-cards' ); ?>">
                        <span aria-hidden="true">&#8250;</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php else : ?>
    <div class="apc-pricing apc-cols-<?php echo esc_attr( $columns ); ?>" style="<?php echo esc_attr( $root_style ); ?>">
        <?php foreach ( $plans as $plan ) : ?>
            <?php apc_render_card( $plan, $popular_label ); ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
