<?php
/**
 * Front: Hero Section (Slider)
 */

if (!defined('ABSPATH')) { exit; }

// Forward to the modular component, allowing overrides via filter if needed
$component_args = apply_filters('eshop/front/hero_slider_args', array(
    'desktop_repeater' => 'desktop_slider',
    'desktop_field'    => 'desktop_image',
    'mobile_repeater'  => 'mobile_slider',
    'mobile_field'     => 'mobile_image',
    'show_nav'         => true,
    'wrapper_classes'  => 'mb-8 full-bleed'
));

get_template_part('template-parts/components/hero-slider', null, $component_args);
