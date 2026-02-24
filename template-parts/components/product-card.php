<?php
/**
 * Product Card Component (Grid) - CORRECTED VERSION
 * This file no longer calls wc_product_class()
 */
defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$product_id = $product->get_id();
$is_on_sale = $product->is_on_sale();
$is_variable = $product->is_type('variable');
$is_simple_out_of_stock = !$is_variable && !$product->is_in_stock();
$date_created = get_the_date('c', $product_id);

// Get product colors from WooCommerce attributes
$product_colors = array();
$color_attribute_taxonomy = 'pa_color';

if ( taxonomy_exists( $color_attribute_taxonomy ) && function_exists( 'wp_get_post_terms' ) ) {
    $color_terms = wp_get_post_terms($product_id, $color_attribute_taxonomy);
    foreach ($color_terms as $term) {
        $color_data = get_term_meta($term->term_id, 'pa_color_color', true);
        if (!$color_data) {
            // Fallback color based on term name if no color data is set
            $color_data = eshop_get_color_from_name($term->name);
        }
        if ($color_data) {
            $product_colors[] = array(
                'name' => $term->name,
                'hex' => $color_data
            );
        }
    }
}

// NOTE: No default colors - each product should have its own unique colors
// If no colors are found, the color row simply won't display
// This preserves product diversity and prevents all products from looking identical

// Get variation stock info
$stock_info = array();
$has_size_variations = false;
$all_sizes_out_of_stock = false;
if ($is_variable) {
    $variation_ids = $product->get_children();
    $priority = array('out' => 0, 'available' => 1);

    foreach ($variation_ids as $variation_id) {
        $variation = wc_get_product($variation_id);
        if (!$variation) {
            continue;
        }

        // Support both pa_size and pa_select-size
        $size = $variation->get_attribute('pa_size');
        if (!$size) {
            $size = $variation->get_attribute('pa_select-size');
        }
        // Also support alternate size taxonomy used in this theme
        if (!$size) {
            $size = $variation->get_attribute('pa_size-selection');
        }
        if (!$size) {
            continue;
        }

        $has_size_variations = true;

        $qty = $variation->get_stock_quantity();
        $in_stock = $variation->is_in_stock();

        if (!$in_stock) {
            $status = 'out';
        } else {
            if ($qty === null) {
                $status = 'available';
            } elseif ($qty <= 0) {
                $status = 'out';
            } else {
                $status = 'available';
            }
        }

        // If multiple variations share same size (e.g., colors), keep the best status
        if (isset($stock_info[$size])) {
            $current = $stock_info[$size]['status'];
            if ($priority[$status] > $priority[$current]) {
                $stock_info[$size] = array(
                    'stock' => $qty,
                    'status' => $status,
                );
            }
        } else {
            $stock_info[$size] = array(
                'stock' => $qty,
                'status' => $status,
            );
        }
    }
    if (!empty($stock_info)) {
        $all_sizes_out_of_stock = true;
        foreach ($stock_info as $info) {
            if ($info['status'] !== 'out') {
                $all_sizes_out_of_stock = false;
                break;
            }
        }
    }
}

$force_size_overlay = $all_sizes_out_of_stock || $is_simple_out_of_stock;
$product_card_classes = 'product-card' . ($force_size_overlay ? ' show-sizes' : '');
?>
<article class="<?php echo esc_attr($product_card_classes); ?>"
         data-product-id="<?php echo esc_attr($product_id); ?>"
         data-variable="<?php echo $is_variable ? 'true' : 'false'; ?>"
         data-wishlist="true"
         data-date-added="<?php echo esc_attr($date_created); ?>"
         data-sale-price="<?php echo $product->get_sale_price(); ?>"
         data-regular-price="<?php echo $product->get_regular_price(); ?>">
    <div class="product-card__media">
        <a href="<?php echo esc_url( get_permalink() ); ?>">
            <?php echo woocommerce_get_product_thumbnail('full'); ?>
        </a>
        <div class="badge-stack">
            <?php if ($is_on_sale) :
                $regular_price = $product->get_regular_price();
                $sale_price = $product->get_sale_price();
                if ($regular_price && $sale_price && $regular_price > $sale_price) {
                    $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
                    if ($percentage > 0) {
                        echo '<span class="badge badge--sale">' . $percentage . '% Off</span>';
                    }
                }
            endif; ?>
            <?php
            // Add "New" badge for products created within the last 3 weeks
            $date_created_timestamp = strtotime($date_created);
            $three_weeks_ago = strtotime('-3 weeks');
            if ($date_created_timestamp > $three_weeks_ago) {
                echo '<span class="badge">New</span>';
            }
            ?>
        </div>
        <?php 
        $user_logged_in = is_user_logged_in();
        $is_in_wishlist = $user_logged_in ? eshop_is_in_wishlist($product_id) : false;
        $wishlist_class = $is_in_wishlist ? 'active in-wishlist' : '';
        $wishlist_aria_label = $user_logged_in
            ? ($is_in_wishlist ? __('Remove from wishlist', 'eshop-theme') : __('Add to wishlist', 'eshop-theme'))
            : __('Log in to save to wishlist', 'eshop-theme');
        $wishlist_fill = $is_in_wishlist ? 'currentColor' : 'none';
        ?>
        <button class="wishlist-button add-to-wishlist <?php echo esc_attr($wishlist_class); ?>"
                type="button"
                title="<?php echo esc_attr($wishlist_aria_label); ?>"
                aria-label="<?php echo esc_attr($wishlist_aria_label); ?>"
                data-product-id="<?php echo esc_attr($product_id); ?>"
                data-product-name="<?php echo esc_attr($product->get_name()); ?>"
                <?php echo $user_logged_in ? '' : 'data-requires-auth="true"'; ?>
                style="position:absolute;top:18px;right:18px;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.95);border:1px solid rgba(238,129,179,0.4);z-index:10;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo esc_attr($wishlist_fill); ?>" stroke="#ee81b3" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        </button>
        <div class="media-dot-row" aria-hidden="true">
            <!-- Media dots will be generated by JavaScript -->
        </div>
        <?php if ($force_size_overlay) : ?>
        <div class="size-overlay size-overlay--status" aria-hidden="true">
            <span class="size-chip is-out">
                <?php esc_html_e('Out of stock', 'eshop-theme'); ?>
            </span>
        </div>
        <?php elseif ($has_size_variations && !empty($stock_info)) : ?>
        <div class="size-overlay" aria-hidden="true">
            <?php foreach ($stock_info as $size => $info) : 
                $size_class = 'size-chip';
                if ($info['status'] === 'out') {
                    $size_class .= ' is-out';
                }
                // Transform size label for display (e.g., XSmall -> XS)
                $display_size = function_exists('eshop_transform_size_label') ? eshop_transform_size_label($size) : $size;
                $label = strtoupper($display_size);
            ?>
                <span class="<?php echo esc_attr($size_class); ?>" title="<?php echo esc_attr($info['status'] === 'out' ? __('Out of stock', 'eshop-theme') : __('In stock', 'eshop-theme')); ?>">
                    <?php echo esc_html($label); ?>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="product-card__info">
        <h2 class="product-card__title">
            <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h2>
        <span class="product-card__sku"><?php echo ( $sku = $product->get_sku() ) ? 'SKU: ' . esc_html( $sku ) : '&nbsp;'; ?></span>
        <div class="category-line">
            <?php
            $categories = wc_get_product_category_list( $product_id, ', ', '', '' );
            if ($categories) {
                echo $categories;
            }
            ?>
        </div>
        <?php if (!empty($product_colors)) : ?>
        <div class="color-row">
            <span>Palette</span>
            <div class="color-dots" aria-hidden="true">
                <?php foreach ($product_colors as $color) : ?>
                    <span class="color-dot"
                          data-color="<?php echo esc_attr($color['name']); ?>"
                          style="background: <?php echo esc_attr($color['hex']); ?>"
                          title="<?php echo esc_attr($color['name']); ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <div class="price-row">
            <?php
            echo $product->get_price_html();
            ?>
        </div>
    </div>
</article>
