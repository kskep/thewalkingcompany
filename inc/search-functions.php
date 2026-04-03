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

    // Search products by title/content AND SKU
    $query_args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        'orderby'        => 'relevance',
        'order'          => 'DESC',
        's'              => $keyword,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_sku',
                'value'   => $keyword,
                'compare' => 'LIKE',
            ),
        ),
    );

    $query = new WP_Query($query_args);
    $products = array();
    $seen_ids = array();

    while ($query->have_posts()) {
        $query->the_post();
        $product_id = get_the_ID();

        if (in_array($product_id, $seen_ids, true)) {
            continue;
        }

        $product = wc_get_product($product_id);
        if (!$product || !$product->is_visible()) {
            continue;
        }

        $seen_ids[] = $product_id;

        $image_url = '';
        $attachment_id = $product->get_image_id();
        if ($attachment_id) {
            $image_url = wp_get_attachment_image_url($attachment_id, 'woocommerce_thumbnail');
        }
        if (!$image_url) {
            $image_url = wc_placeholder_img_src('woocommerce_thumbnail');
        }

        $products[] = array(
            'id'       => $product_id,
            'title'    => $product->get_name(),
            'url'      => $product->get_permalink(),
            'price'    => $product->get_price_html(),
            'image'    => $image_url,
            'sku'      => $product->get_sku(),
        );
    }

    $total_found = $query->found_posts;
    wp_reset_postdata();

    wp_send_json_success(array(
        'products'    => $products,
        'total_count' => (int) $total_found,
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

/**
 * Also search product SKU on the search results page.
 */
function eshop_search_sku_filter($search, $wp_query) {
    if (is_admin() || !$wp_query->is_search() || !$wp_query->is_main_query()) {
        return $search;
    }

    global $wpdb;
    $keyword = $wp_query->get('s');
    if (empty($keyword)) {
        return $search;
    }

    $like = '%' . $wpdb->esc_like($keyword) . '%';
    $search .= $wpdb->prepare(
        " OR ({$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s))",
        $like
    );

    return $search;
}
add_filter('posts_search', 'eshop_search_sku_filter', 10, 2);
