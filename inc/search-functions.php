<?php
/**
 * Search Functions
 *
 * AJAX live search endpoint and search results page customization.
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler: live product search for the search modal dropdown.
 */
function eshop_live_search_ajax() {
    if (!check_ajax_referer('eshop_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed', 'eshop-theme')));
    }

    $keyword = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';

    if (mb_strlen($keyword) < 2) {
        wp_send_json_success(array('products' => array(), 'total_count' => 0));
    }

    if (!class_exists('WooCommerce')) {
        wp_send_json_error(array('message' => __('WooCommerce is not active', 'eshop-theme')));
    }

    $args = array(
        'status'         => 'publish',
        'limit'          => 8,
        's'              => $keyword,
        'orderby'        => 'relevance',
        'order'          => 'DESC',
        'return'         => 'ids',
    );

    $product_ids = wc_get_products($args);
    $products = array();

    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_visible()) {
            continue;
        }

        $image_url = '';
        $attachment_id = $product->get_image_id();
        if ($attachment_id) {
            $image_url = wp_get_attachment_image_url($attachment_id, 'woocommerce_thumbnail');
        }
        if (!$image_url) {
            $image_url = wc_placeholder_img_src('woocommerce_thumbnail');
        }

        $price_html = $product->get_price_html();
        if (!$price_html) {
            $price_html = '';
        }

        $products[] = array(
            'id'       => $product_id,
            'title'    => $product->get_name(),
            'url'      => $product->get_permalink(),
            'price'    => $price_html,
            'image'    => $image_url,
            'sku'      => $product->get_sku(),
        );
    }

    $total_query = new WP_Query(array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        's'              => $keyword,
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ));

    wp_send_json_success(array(
        'products'    => $products,
        'total_count' => (int) $total_query->found_posts,
        'keyword'     => $keyword,
    ));
}
add_action('wp_ajax_eshop_live_search', 'eshop_live_search_ajax');
add_action('wp_ajax_nopriv_eshop_live_search', 'eshop_live_search_ajax');

/**
 * Force the main search query to only return products.
 */
function eshop_search_filter_products_only($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_search()) {
        $query->set('post_type', 'product');
        $query->set('posts_per_page', 15);
    }
}
add_action('pre_get_posts', 'eshop_search_filter_products_only');
