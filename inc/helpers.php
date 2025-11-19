<?php
/**
 * Theme Helper Functions
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get color hex value from color name (fallback when ACF is not available)
 */
function eshop_get_color_from_name($color_name) {
    // Convert color name to lowercase for comparison
    $color_name = strtolower(trim($color_name));
    
    // Load color mapping from config file
    $color_map = require get_template_directory() . '/inc/config-colors.php';
    
    // Check if exact match exists
    if (isset($color_map[$color_name])) {
        return $color_map[$color_name];
    }
    
    // Check for partial matches for composite colors
    foreach ($color_map as $color_key => $hex_value) {
        if (strpos($color_name, $color_key) !== false || strpos($color_key, $color_name) !== false) {
            return $hex_value;
        }
    }
    
    // Default fallback color - a neutral pink/rose
    return '#f8c5d8';
}
