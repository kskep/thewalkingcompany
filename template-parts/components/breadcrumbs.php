<?php
/**
 * Breadcrumbs Component
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

if (function_exists('woocommerce_breadcrumb')) {
    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    woocommerce_breadcrumb(array(
        'delimiter'   => '<span aria-hidden="true">/</span>',
        'wrap_before' => '',
        'wrap_after'  => '',
        'before'      => '',
        'after'       => ''
    ));
    echo '</nav>';
} else {
    echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
    echo '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'eshop-theme') . '</a> <span aria-hidden="true">/</span> ';
    if (is_singular('product')) {
        $terms = get_the_terms(get_the_ID(), 'product_cat');
        if (!empty($terms) && !is_wp_error($terms)) {
            $term = array_shift($terms);
            echo '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a> <span aria-hidden="true">/</span> ';
        }
        the_title('<span aria-current="page">', '</span>');
    }
    echo '</nav>';
}
