<?php
/**
 * Component: Hero Slider
 * Usage:
 * get_template_part('template-parts/components/hero-slider', null, [
 *   'desktop_repeater' => 'desktop_slider',
 *   'desktop_field'    => 'desktop_image',
 *   'mobile_repeater'  => 'mobile_slider',
 *   'mobile_field'     => 'mobile_image',
 *   'show_nav'         => true,
 *   'wrapper_classes'  => ''
 * ]);
 *
 * Supports either:
 * - Two repeaters (desktop_slider/mobile_slider) with image fields per slide
 * - A single desktop_slider repeater that also includes a mobile_image subfield
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('have_rows') || !function_exists('get_sub_field')) {
    return; // ACF required
}

// Extract args with defaults
$args = isset($args) && is_array($args) ? $args : array();
$post_id          = $args['post_id'] ?? get_the_ID();
$desktop_repeater = $args['desktop_repeater'] ?? 'desktop_slider';
$desktop_field    = $args['desktop_field'] ?? 'desktop_image';
$mobile_repeater  = $args['mobile_repeater'] ?? 'mobile_slider';
$mobile_field     = $args['mobile_field'] ?? 'mobile_image';
$show_nav         = isset($args['show_nav']) ? (bool) $args['show_nav'] : true;
$wrapper_classes  = isset($args['wrapper_classes']) ? trim($args['wrapper_classes']) : '';

if (!function_exists('eshop_component_get_slider_images')) {
    function eshop_component_get_slider_images($repeater_name, $image_field, $post_id = null) {
        $slides = array();
        if (have_rows($repeater_name, $post_id)) {
            while (have_rows($repeater_name, $post_id)) { the_row();
                $img = get_sub_field($image_field);
                if ($img && !empty($img['url'])) {
                    $slides[] = array(
                        'url' => esc_url($img['url']),
                        'alt' => esc_attr($img['alt'] ?? ''),
                    );
                }
            }
            if (function_exists('reset_rows')) {
                reset_rows();
            }
        }
        return $slides;
    }
}

$desktop_slides = eshop_component_get_slider_images($desktop_repeater, $desktop_field, $post_id);
$mobile_slides  = eshop_component_get_slider_images($mobile_repeater, $mobile_field, $post_id);

// If no separate mobile repeater, try mobile_image subfield within desktop_repeater
if (empty($mobile_slides) && have_rows($desktop_repeater, $post_id)) {
    $tmp = array();
    while (have_rows($desktop_repeater, $post_id)) { the_row();
        $mimg = get_sub_field($mobile_field);
        if ($mimg && !empty($mimg['url'])) {
            $tmp[] = array(
                'url' => esc_url($mimg['url']),
                'alt' => esc_attr($mimg['alt'] ?? ''),
            );
        }
    }
    if (function_exists('reset_rows')) {
        reset_rows();
    }
    $mobile_slides = $tmp;
}

// Fallback: if mobile is still empty, reuse desktop
if (empty($mobile_slides)) {
    $mobile_slides = $desktop_slides;
}

if (empty($desktop_slides) && empty($mobile_slides)) {
    return; // Nothing to render
}
?>

<section class="hero-slider-wrapper js-hero-slider <?php echo esc_attr($wrapper_classes); ?>">
    <?php if (!empty($desktop_slides)) : ?>
        <div class="hero-slider-desktop swiper">
            <div class="swiper-wrapper">
                <?php foreach ($desktop_slides as $slide) : ?>
                    <div class="swiper-slide">
                        <div class="hero-slide relative overflow-hidden">
                            <img class="w-full h-auto block" src="<?php echo $slide['url']; ?>" alt="<?php echo $slide['alt']; ?>" />
                            <div class="slide-overlay absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="slide-caption text-white text-center opacity-0 translate-y-4"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <?php if ($show_nav) : ?>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mobile_slides)) : ?>
        <div class="hero-slider-mobile swiper">
            <div class="swiper-wrapper">
                <?php foreach ($mobile_slides as $slide) : ?>
                    <div class="swiper-slide">
                        <div class="hero-slide relative overflow-hidden">
                            <img class="w-full h-auto block" src="<?php echo $slide['url']; ?>" alt="<?php echo $slide['alt']; ?>" />
                            <div class="slide-overlay absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div class="slide-caption text-white text-center opacity-0 translate-y-4"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    <?php endif; ?>
</section>
