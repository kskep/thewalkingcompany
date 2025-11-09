<?php
/**
 * Test file for refactored filter modal
 * This file simulates a WooCommerce shop environment to test the filter modal
 */

// Simulate WordPress environment
define('ABSPATH', dirname(__FILE__) . '/');
define('WPINC', 'wp-includes');

// Mock WordPress functions if they don't exist
if (!function_exists('get_template_part')) {
    function get_template_part($slug) {
        $file = $slug . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<!-- Template part not found: $file -->";
        }
    }
}

if (!function_exists('set_query_var')) {
    function set_query_var($var, $value) {
        global $wp_query;
        if (!isset($wp_query)) {
            $wp_query = new stdClass();
        }
        $wp_query->$var = $value;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('_e')) {
    function _e($text, $domain = 'default') {
        echo $text;
    }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {
        echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('checked')) {
    function checked($checked, $current = true, $echo = true) {
        return (bool) $checked === (bool) $current ? ' checked="checked"' : '';
    }
}

// Mock WooCommerce functions
if (!function_exists('wc_price')) {
    function wc_price($price) {
        return '€' . number_format($price, 2);
    }
}

if (!function_exists('wc_get_attribute_taxonomies')) {
    function wc_get_attribute_taxonomies() {
        return array();
    }
}

// Mock global $wpdb
global $wpdb;
$wpdb = new stdClass();
$wpdb->postmeta = 'wp_postmeta';
$wpdb->posts = 'wp_posts';
$wpdb->get_row = function($query) {
    // Mock price range data
    return (object) array(
        'min_price' => 10,
        'max_price' => 500
    );
};

// Mock $_GET with some test filter values
$_GET = array(
    'min_price' => '50',
    'max_price' => '200',
    'product_cat' => array('clothing', 'accessories'),
    'on_sale' => '1'
);

// Mock WordPress functions
function is_active_sidebar($index) {
    return false; // Don't use dynamic sidebar for testing
}

function is_shop() {
    return true;
}

function is_product_category() {
    return false;
}

function is_product_tag() {
    return false;
}

function class_exists($class) {
    return $class === 'WooCommerce';
}

// Mock helper functions
function eshop_get_available_categories() {
    return array(
        array('slug' => 'clothing', 'name' => 'Clothing', 'count' => 25),
        array('slug' => 'accessories', 'name' => 'Accessories', 'count' => 15),
        array('slug' => 'shoes', 'name' => 'Shoes', 'count' => 10)
    );
}

function eshop_get_available_attribute_terms($taxonomy) {
    if ($taxonomy === 'pa_color') {
        return array(
            array('term_id' => 1, 'name' => 'Red', 'slug' => 'red', 'count' => 5, 'color' => '#ff0000'),
            array('term_id' => 2, 'name' => 'Blue', 'slug' => 'blue', 'count' => 8, 'color' => '#0000ff'),
            array('term_id' => 3, 'name' => 'Green', 'slug' => 'green', 'count' => 3, 'color' => '#00ff00')
        );
    } elseif ($taxonomy === 'pa_size-selection') {
        return array(
            array('term_id' => 4, 'name' => 'Small', 'slug' => 'small', 'count' => 10),
            array('term_id' => 5, 'name' => 'Medium', 'slug' => 'medium', 'count' => 15),
            array('term_id' => 6, 'name' => 'Large', 'slug' => 'large', 'count' => 8)
        );
    }
    return array();
}

function eshop_get_sale_products_count() {
    return 12;
}

function eshop_get_featured_products_count() {
    return 5;
}

function twc_transform_size_label($size) {
    $transformations = array(
        'Small' => 'S',
        'Medium' => 'M', 
        'Large' => 'L'
    );
    return isset($transformations[$size]) ? $transformations[$size] : $size;
}

// Include the refactored filter modal
include 'template-parts/components/filter-modal.php';
?>