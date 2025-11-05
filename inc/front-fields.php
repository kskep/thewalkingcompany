<?php
/**
 * Front page fields without ACF
 * - Theme settings for Hero Slider (desktop/mobile)
 * - Front Category Grid (4 tiles)
 *
 * Stores everything in a single option: eshop_front_page_settings
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Option name and defaults
 */
function eshop_front_settings_option_name() { return 'eshop_front_page_settings'; }

function eshop_front_settings_defaults() {
    return array(
        'hero_desktop_slides' => array(), // [ [url, alt], ... ]
        'hero_mobile_slides'  => array(),
        'category_tiles'      => array(
            // index-aligned with default titles
            array('image_url' => '', 'alt' => '', 'title' => 'Shoes',       'link' => ''),
            array('image_url' => '', 'alt' => '', 'title' => 'Clothes',     'link' => ''),
            array('image_url' => '', 'alt' => '', 'title' => 'Accessories', 'link' => ''),
            array('image_url' => '', 'alt' => '', 'title' => 'Bags',        'link' => ''),
        ),
    );
}

/**
 * Get merged settings (option + defaults)
 */
function eshop_get_front_settings() {
    $defaults = eshop_front_settings_defaults();
    // Prefer front page post meta; fallback to options for backward compat
    $front_id = (int) get_option('page_on_front');
    $meta = array();
    if ($front_id) {
        $meta = array(
            'hero_desktop_slides' => get_post_meta($front_id, 'eshop_hero_desktop_slides', true),
            'hero_mobile_slides'  => get_post_meta($front_id, 'eshop_hero_mobile_slides', true),
            'category_tiles'      => get_post_meta($front_id, 'eshop_category_tiles', true),
        );
    }
    $opts = is_array($meta) && !empty(array_filter($meta)) ? $meta : get_option(eshop_front_settings_option_name(), array());
    if (!is_array($opts)) { $opts = array(); }
    // Deep merge defaults for expected keys
    $opts['hero_desktop_slides'] = isset($opts['hero_desktop_slides']) && is_array($opts['hero_desktop_slides']) ? $opts['hero_desktop_slides'] : array();
    $opts['hero_mobile_slides']  = isset($opts['hero_mobile_slides']) && is_array($opts['hero_mobile_slides']) ? $opts['hero_mobile_slides'] : array();
    $opts['category_tiles']      = isset($opts['category_tiles']) && is_array($opts['category_tiles']) ? $opts['category_tiles'] : $defaults['category_tiles'];
    // Ensure exactly 4 tiles
    $tiles = array();
    $fallback_titles = array('Shoes', 'Clothes', 'Accessories', 'Bags');
    for ($i=0; $i<4; $i++) {
        $row = isset($opts['category_tiles'][$i]) && is_array($opts['category_tiles'][$i]) ? $opts['category_tiles'][$i] : array();
        $tiles[$i] = array(
            'image_url' => isset($row['image_url']) ? (string)$row['image_url'] : '',
            'alt'       => isset($row['alt']) ? (string)$row['alt'] : '',
            'title'     => isset($row['title']) && $row['title'] !== '' ? (string)$row['title'] : $fallback_titles[$i],
            'link'      => isset($row['link']) ? (string)$row['link'] : '',
        );
    }
    $opts['category_tiles'] = $tiles;
    return $opts;
}

/**
 * Public getters used by templates
 */
function eshop_get_hero_slides($device = 'desktop') {
    $settings = eshop_get_front_settings();
    $slides = ($device === 'mobile') ? $settings['hero_mobile_slides'] : $settings['hero_desktop_slides'];
    // Normalize and sanitize
    $out = array();
    if (is_array($slides)) {
        foreach ($slides as $s) {
            $url = isset($s['url']) ? esc_url($s['url']) : '';
            if (!$url) { continue; }
            $out[] = array(
                'url' => $url,
                'alt' => esc_attr(isset($s['alt']) ? $s['alt'] : ''),
            );
        }
    }
    return $out;
}

function eshop_get_category_tiles() {
    $settings = eshop_get_front_settings();
    $tiles = array();
    foreach ($settings['category_tiles'] as $i => $row) {
        $img = isset($row['image_url']) ? esc_url($row['image_url']) : '';
        $title = isset($row['title']) && $row['title'] !== '' ? sanitize_text_field($row['title']) : '';
        $link = isset($row['link']) ? esc_url($row['link']) : '';
        $alt  = isset($row['alt']) ? sanitize_text_field($row['alt']) : '';
        if (!$img || !$link) { continue; }
        if ($title === '') {
            $fallback = array('Shoes', 'Clothes', 'Accessories', 'Bags');
            $title = $fallback[$i] ?? 'Category';
        }
        $tiles[] = array(
            'image' => array('url' => $img, 'alt' => $alt),
            'title' => $title,
            'link'  => $link,
            'index' => $i + 1,
            'category' => $title,
        );
    }
    return $tiles;
}

// Remove admin Settings page in favor of per-front-page meta UI
