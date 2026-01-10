<?php
/**
 * Front page fields without ACF
 * - Theme settings for Hero Slider (desktop/mobile)
 * - Flexible Banner Rows (1-4 banners per row)
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
        'banner_rows'         => array(), // [ [ banners: [ [image_url, alt, title, link], ... ], columns: 1-4 ], ... ]
        // Legacy support
        'category_tiles'      => array(),
    );
}

/**
 * Get merged settings (option + defaults)
 */
function eshop_get_front_settings() {
    $defaults = eshop_front_settings_defaults();
    $front_id = (int) get_option('page_on_front');
    $meta = array();
    if ($front_id) {
        $meta = array(
            'hero_desktop_slides' => get_post_meta($front_id, 'eshop_hero_desktop_slides', true),
            'hero_mobile_slides'  => get_post_meta($front_id, 'eshop_hero_mobile_slides', true),
            'banner_rows'         => get_post_meta($front_id, 'eshop_banner_rows', true),
            'category_tiles'      => get_post_meta($front_id, 'eshop_category_tiles', true),
        );
    }
    $opts = is_array($meta) && !empty(array_filter($meta)) ? $meta : get_option(eshop_front_settings_option_name(), array());
    if (!is_array($opts)) { $opts = array(); }
    
    // Deep merge defaults for expected keys
    $opts['hero_desktop_slides'] = isset($opts['hero_desktop_slides']) && is_array($opts['hero_desktop_slides']) ? $opts['hero_desktop_slides'] : array();
    $opts['hero_mobile_slides']  = isset($opts['hero_mobile_slides']) && is_array($opts['hero_mobile_slides']) ? $opts['hero_mobile_slides'] : array();
    $opts['banner_rows']         = isset($opts['banner_rows']) && is_array($opts['banner_rows']) ? $opts['banner_rows'] : array();
    $opts['category_tiles']      = isset($opts['category_tiles']) && is_array($opts['category_tiles']) ? $opts['category_tiles'] : array();
    
    return $opts;
}

/**
 * Public getters used by templates
 */
function eshop_get_hero_slides($device = 'desktop') {
    $settings = eshop_get_front_settings();
    $slides = ($device === 'mobile') ? $settings['hero_mobile_slides'] : $settings['hero_desktop_slides'];
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

/**
 * Get flexible banner rows
 * Returns array of rows, each row has 'columns' (1-4) and 'banners' array
 */
function eshop_get_banner_rows() {
    $settings = eshop_get_front_settings();
    $rows = array();
    
    // First check for new banner_rows data
    if (!empty($settings['banner_rows'])) {
        foreach ($settings['banner_rows'] as $row) {
            if (!isset($row['banners']) || !is_array($row['banners'])) continue;
            
            $banners = array();
            foreach ($row['banners'] as $banner) {
                $img = isset($banner['image_url']) ? esc_url($banner['image_url']) : '';
                if (!$img) continue;
                
                $banners[] = array(
                    'image_url' => $img,
                    'alt'       => isset($banner['alt']) ? sanitize_text_field($banner['alt']) : '',
                    'title'     => isset($banner['title']) ? sanitize_text_field($banner['title']) : '',
                    'link'      => isset($banner['link']) ? esc_url($banner['link']) : '',
                );
            }
            
            if (!empty($banners)) {
                $rows[] = array(
                    'columns' => count($banners),
                    'banners' => $banners,
                );
            }
        }
    }
    
    // Fallback: migrate old category_tiles to a single 4-column row
    if (empty($rows) && !empty($settings['category_tiles'])) {
        $banners = array();
        $fallback_titles = array('Shoes', 'Clothes', 'Accessories', 'Bags');
        foreach ($settings['category_tiles'] as $i => $tile) {
            $img = isset($tile['image_url']) ? esc_url($tile['image_url']) : '';
            $link = isset($tile['link']) ? esc_url($tile['link']) : '';
            if (!$img || !$link) continue;
            
            $banners[] = array(
                'image_url' => $img,
                'alt'       => isset($tile['alt']) ? sanitize_text_field($tile['alt']) : '',
                'title'     => isset($tile['title']) && $tile['title'] !== '' ? sanitize_text_field($tile['title']) : ($fallback_titles[$i] ?? 'Category'),
                'link'      => $link,
            );
        }
        if (!empty($banners)) {
            $rows[] = array(
                'columns' => count($banners),
                'banners' => $banners,
            );
        }
    }
    
    return $rows;
}

/**
 * Legacy: Get category tiles (for backward compatibility)
 * This now returns data from the first banner row if it has 4 items
 */
function eshop_get_category_tiles() {
    $rows = eshop_get_banner_rows();
    $tiles = array();
    
    // Find first row with banners and convert to old format
    if (!empty($rows)) {
        $first_row = $rows[0];
        foreach ($first_row['banners'] as $i => $banner) {
            $tiles[] = array(
                'image'    => array('url' => $banner['image_url'], 'alt' => $banner['alt']),
                'title'    => $banner['title'],
                'link'     => $banner['link'],
                'index'    => $i + 1,
                'category' => $banner['title'],
            );
        }
    }
    
    return $tiles;
}

// Remove admin Settings page in favor of per-front-page meta UI
