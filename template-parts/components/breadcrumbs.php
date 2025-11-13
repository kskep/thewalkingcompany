<?php
/**
 * Breadcrumbs Component
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

$should_use_wc = function_exists('woocommerce_breadcrumb') && (
    (function_exists('is_woocommerce') && is_woocommerce()) ||
    is_singular('product') ||
    (function_exists('is_cart') && is_cart()) ||
    (function_exists('is_checkout') && is_checkout())
);

if ($should_use_wc) {
    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    woocommerce_breadcrumb(array(
        'delimiter'   => '<span aria-hidden="true">/</span>',
        'wrap_before' => '',
        'wrap_after'  => '',
        'before'      => '',
        'after'       => ''
    ));
    echo '</nav>';
    return;
}

$breadcrumbs = array();
$breadcrumbs[] = array(
    'label' => esc_html__('Home', 'eshop-theme'),
    'url' => home_url('/'),
    'current' => is_front_page()
);

if (is_page() && !is_front_page()) {
    $ancestors = array_reverse(get_post_ancestors(get_the_ID()));
    foreach ($ancestors as $ancestor_id) {
        $breadcrumbs[] = array(
            'label' => get_the_title($ancestor_id),
            'url' => get_permalink($ancestor_id),
            'current' => false,
        );
    }

    $breadcrumbs[] = array(
        'label' => get_post_field('post_title', get_the_ID()),
        'url' => '',
        'current' => true,
    );
} elseif (is_singular()) {
    $post_type_obj = get_post_type_object(get_post_type());
    if ($post_type_obj && !empty($post_type_obj->has_archive) && !empty($post_type_obj->labels->name)) {
        $archive_link = get_post_type_archive_link(get_post_type());
        if ($archive_link) {
            $breadcrumbs[] = array(
                'label' => $post_type_obj->labels->name,
                'url' => $archive_link,
                'current' => false,
            );
        }
    }

    $breadcrumbs[] = array(
        'label' => get_post_field('post_title', get_the_ID()),
        'url' => '',
        'current' => true,
    );
} elseif (is_archive()) {
    $breadcrumbs[] = array(
        'label' => get_the_archive_title(),
        'url' => '',
        'current' => true,
    );
} elseif (is_search()) {
    $breadcrumbs[] = array(
        'label' => sprintf(esc_html__('Search: %s', 'eshop-theme'), get_search_query()),
        'url' => '',
        'current' => true,
    );
} elseif (is_404()) {
    $breadcrumbs[] = array(
        'label' => esc_html__('Not Found', 'eshop-theme'),
        'url' => '',
        'current' => true,
    );
}

echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
foreach ($breadcrumbs as $index => $crumb) {
    if ($index > 0) {
        echo '<span class="breadcrumb-separator" aria-hidden="true">/</span>';
    }

    if (!empty($crumb['current'])) {
        echo '<span class="breadcrumb-current" aria-current="page">' . esc_html($crumb['label']) . '</span>';
    } else {
        echo '<a class="breadcrumb-link" href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['label']) . '</a>';
    }
}
echo '</nav>';
