<?php
/**
 * WooCommerce Functions
 * 
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Cart Functions
 */

// Get cart fragment for AJAX updates
function eshop_cart_fragment($fragments) {
    ob_start();
    ?>
    <span class="cart-count <?php echo WC()->cart->get_cart_contents_count() > 0 ? '' : 'hidden'; ?>">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['.cart-count'] = ob_get_clean();
    
    ob_start();
    ?>
    <span class="cart-total">
        <?php echo WC()->cart->get_cart_total(); ?>
    </span>
    <?php
    $fragments['.cart-total'] = ob_get_clean();
    
    // Update entire minicart content
    ob_start();
    ?>
    <div class="minicart-items max-h-64 overflow-y-auto">
        <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
            <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                $product = $cart_item['data'];
                $product_id = $cart_item['product_id'];
                $quantity = $cart_item['quantity'];
            ?>
                <div class="minicart-item flex items-center space-x-3 py-3 border-b border-gray-100 last:border-b-0">
                    <div class="w-12 h-12 flex-shrink-0">
                        <?php echo $product->get_image(array(48, 48)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-dark truncate"><?php echo $product->get_name(); ?></h4>
                        <p class="text-xs text-gray-500"><?php echo sprintf('%s × %s', $quantity, wc_price($product->get_price())); ?></p>
                        <p class="text-sm text-primary font-semibold"><?php echo wc_price($product->get_price() * $quantity); ?></p>
                    </div>
                    <a href="<?php echo wc_get_cart_remove_url($cart_item_key); ?>" class="remove-from-cart text-gray-400 hover:text-red-500 transition-colors" data-cart-item-key="<?php echo $cart_item_key; ?>">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-gray-500 text-center py-8"><?php _e('Your cart is empty', 'eshop-theme'); ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
        <div class="mt-4 pt-3 border-t border-gray-200 space-y-2">
            <a href="<?php echo wc_get_cart_url(); ?>" class="block w-full text-center bg-gray-100 text-dark py-2 hover:bg-gray-200 transition-colors duration-200">
                <?php _e('View Cart', 'eshop-theme'); ?>
            </a>
            <a href="<?php echo wc_get_checkout_url(); ?>" class="block w-full text-center bg-primary text-white py-2 hover:bg-primary-dark transition-colors duration-200">
                <?php _e('Checkout', 'eshop-theme'); ?>
            </a>
        </div>
    <?php endif; ?>
    <?php
    $fragments['.minicart-dropdown .p-4'] = ob_get_clean();
    
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'eshop_cart_fragment');

/**
 * Override WooCommerce product loop structure
 */

// Remove default WooCommerce product loop start/end
remove_action('woocommerce_output_content_wrapper', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_output_content_wrapper_end', 'woocommerce_output_content_wrapper_end', 10);

// Override product loop start to output our custom grid container
function eshop_woocommerce_product_loop_start($html) {
    return '<div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="products-grid">';
}
add_filter('woocommerce_product_loop_start', 'eshop_woocommerce_product_loop_start');

// Override product loop end to close our custom grid container
function eshop_woocommerce_product_loop_end($html) {
    return '</div>';
}
add_filter('woocommerce_product_loop_end', 'eshop_woocommerce_product_loop_end');

// Set default shop columns to 4
function eshop_woocommerce_loop_columns() {
    return 4;
}
add_filter('loop_shop_columns', 'eshop_woocommerce_loop_columns');

// Remove default WooCommerce styles that conflict with our grid
function eshop_dequeue_woocommerce_styles() {
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce-general');
}
add_action('wp_enqueue_scripts', 'eshop_dequeue_woocommerce_styles', 100);

/**
 * Override WooCommerce image sizes for better quality
 */
function eshop_woocommerce_image_dimensions() {
    global $pagenow;

    if (!isset($_GET['activated']) || $pagenow != 'themes.php') {
        return;
    }

    // Set WooCommerce image sizes to higher resolution
    update_option('woocommerce_thumbnail_image_width', 400);
    update_option('woocommerce_thumbnail_image_height', 400);
    update_option('woocommerce_thumbnail_cropping', '1:1');

    update_option('woocommerce_single_image_width', 800);
    update_option('woocommerce_single_image_height', 800);

    update_option('woocommerce_gallery_thumbnail_image_width', 150);
    update_option('woocommerce_gallery_thumbnail_image_height', 150);
}
add_action('after_switch_theme', 'eshop_woocommerce_image_dimensions', 1);

/**
 * Use higher quality images in product loops
 */
function eshop_woocommerce_single_product_image_thumbnail_html($html, $post_thumbnail_id) {
    global $product;

    if (!$product) {
        return $html;
    }

    // Use our custom high-quality image size
    $image = wp_get_attachment_image($post_thumbnail_id, 'product-thumbnail-hq', false, array(
        'class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
        'alt' => $product->get_name()
    ));

    return $image;
}
add_filter('woocommerce_single_product_image_thumbnail_html', 'eshop_woocommerce_single_product_image_thumbnail_html', 10, 2);

/**
 * AJAX handler for product filtering
 */
function eshop_filter_products() {
    check_ajax_referer('eshop_nonce', 'nonce');

    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';

    // Build WP_Query args
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => wc_get_default_products_per_row() * wc_get_default_product_rows_per_page(),
        'paged' => $paged,
        'orderby' => $orderby,
        'meta_query' => array(),
        'tax_query' => array(),
    );

    // Handle ordering
    switch ($orderby) {
        case 'price':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'popularity':
            $args['meta_key'] = 'total_sales';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'date':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'menu_order';
            $args['order'] = 'ASC';
    }

    // Price filter
    if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
        $price_query = array('key' => '_price', 'type' => 'NUMERIC');

        if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
            $price_query['value'] = array(floatval($filters['min_price']), floatval($filters['max_price']));
            $price_query['compare'] = 'BETWEEN';
        } elseif (!empty($filters['min_price'])) {
            $price_query['value'] = floatval($filters['min_price']);
            $price_query['compare'] = '>=';
        } elseif (!empty($filters['max_price'])) {
            $price_query['value'] = floatval($filters['max_price']);
            $price_query['compare'] = '<=';
        }

        $args['meta_query'][] = $price_query;
    }

    // Category filter
    if (!empty($filters['product_cat'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => array_map('intval', $filters['product_cat']),
            'operator' => 'IN',
        );
    }

    // Attribute filters
    foreach ($filters as $key => $values) {
        if (strpos($key, 'pa_') === 0 && !empty($values)) {
            $args['tax_query'][] = array(
                'taxonomy' => $key,
                'field' => 'slug',
                'terms' => $values,
                'operator' => 'IN',
            );
        }
    }

    // Stock status filter
    if (!empty($filters['stock_status'])) {
        $args['meta_query'][] = array(
            'key' => '_stock_status',
            'value' => $filters['stock_status'],
            'compare' => 'IN',
        );
    }

    // On sale filter
    if (!empty($filters['on_sale'])) {
        $args['meta_query'][] = array(
            'key' => '_sale_price',
            'value' => '',
            'compare' => '!=',
        );
    }

    // Ensure products are visible
    $args['meta_query'][] = array(
        'key' => '_visibility',
        'value' => array('catalog', 'visible'),
        'compare' => 'IN',
    );

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        // Start the products grid container
        echo '<div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="products-grid">';

        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }

        echo '</div>'; // Close products-grid

        // Pagination
        if ($query->max_num_pages > 1) {
            echo '<div class="pagination-wrapper mt-8">';
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'prev_text' => '<i class="fas fa-chevron-left"></i>',
                'next_text' => '<i class="fas fa-chevron-right"></i>',
            ));
            echo '</div>';
        }
    } else {
        echo '<div class="no-products-found text-center py-12">';
        echo '<div class="mb-6"><i class="fas fa-search text-6xl text-gray-300"></i></div>';
        echo '<h3 class="text-2xl font-semibold text-gray-900 mb-4">' . __('No products found', 'eshop-theme') . '</h3>';
        echo '<p class="text-gray-600 mb-6">' . __('Try adjusting your filters or search terms', 'eshop-theme') . '</p>';
        echo '</div>';
    }

    $products_html = ob_get_clean();

    // Generate result count
    $total_products = $query->found_posts;
    $products_per_page = $args['posts_per_page'];
    $current_page = $paged;

    $first = ($current_page - 1) * $products_per_page + 1;
    $last = min($current_page * $products_per_page, $total_products);

    if ($total_products == 1) {
        $result_count = __('Showing the single result', 'eshop-theme');
    } elseif ($total_products <= $products_per_page || -1 === $products_per_page) {
        $result_count = sprintf(__('Showing all %d results', 'eshop-theme'), $total_products);
    } else {
        $result_count = sprintf(__('Showing %1$d–%2$d of %3$d results', 'eshop-theme'), $first, $last, $total_products);
    }

    wp_reset_postdata();

    wp_send_json_success(array(
        'products' => $products_html,
        'result_count' => $result_count,
        'found_posts' => $total_products,
    ));
}
add_action('wp_ajax_filter_products', 'eshop_filter_products');
add_action('wp_ajax_nopriv_filter_products', 'eshop_filter_products');

/**
 * Product Color Variants Helper
 */
function eshop_get_product_color_variants($product, $limit = 4) {
    if (!$product->is_type('variable')) {
        return array();
    }
    
    $available_variations = $product->get_available_variations();
    $color_attribute = null;
    $colors = array();
    
    // Find color attribute
    foreach ($product->get_variation_attributes() as $attribute_name => $options) {
        $attr_lower = strtolower($attribute_name);
        if (strpos($attr_lower, 'color') !== false || strpos($attr_lower, 'colour') !== false) {
            $color_attribute = $attribute_name;
            break;
        }
    }
    
    if (!$color_attribute || empty($available_variations)) {
        return array();
    }
    
    $colors_shown = array();
    $color_count = 0;
    
    foreach ($available_variations as $variation) {
        if ($color_count >= $limit) break;
        
        $color_value = $variation['attributes']['attribute_' . strtolower(str_replace('pa_', '', $color_attribute))];
        
        if (!in_array($color_value, $colors_shown) && $color_value) {
            $colors_shown[] = $color_value;
            
            // Get color hex value
            $color_hex = '#ccc'; // Default
            if (taxonomy_exists($color_attribute)) {
                $term = get_term_by('slug', $color_value, $color_attribute);
                if ($term) {
                    $term_color = get_term_meta($term->term_id, 'color', true);
                    if ($term_color) {
                        $color_hex = $term_color;
                    } else {
                        // Fallback color mapping
                        $color_map = array(
                            'black' => '#000000',
                            'white' => '#ffffff',
                            'red' => '#dc2626',
                            'blue' => '#2563eb',
                            'green' => '#16a34a',
                            'yellow' => '#eab308',
                            'pink' => '#ec4899',
                            'purple' => '#9333ea',
                            'gray' => '#6b7280',
                            'brown' => '#92400e',
                            'beige' => '#f5f5dc',
                            'navy' => '#1e3a8a'
                        );
                        
                        $color_lower = strtolower($color_value);
                        foreach ($color_map as $name => $hex) {
                            if (strpos($color_lower, $name) !== false) {
                                $color_hex = $hex;
                                break;
                            }
                        }
                    }
                }
            }
            
            $colors[] = array(
                'name' => $color_value,
                'hex' => $color_hex
            );
            
            $color_count++;
        }
    }
    
    return $colors;
}

/**
 * Product Size Variants Helper
 */
function eshop_get_product_size_variants($product, $limit = 8) {
    if (!$product->is_type('variable')) {
        return array();
    }

    $available_variations = $product->get_available_variations();
    $size_attribute = null;
    $sizes = array();

    // Find size attribute - look for 'size-selection' first, then fallback to 'size'
    foreach ($product->get_variation_attributes() as $attribute_name => $options) {
        $attr_lower = strtolower($attribute_name);
        // Priority order: size-selection, size_selection, size
        if (strpos($attr_lower, 'size-selection') !== false || strpos($attr_lower, 'size_selection') !== false) {
            $size_attribute = $attribute_name;
            break;
        } elseif (strpos($attr_lower, 'size') !== false && !$size_attribute) {
            $size_attribute = $attribute_name;
        }
    }

    if (!$size_attribute || empty($available_variations)) {
        return array();
    }

    $sizes_data = array();

    // Collect all size variations with their stock status
    foreach ($available_variations as $variation) {
        $variation_obj = wc_get_product($variation['variation_id']);
        if (!$variation_obj) continue;

        $size_value = $variation['attributes']['attribute_' . strtolower(str_replace('pa_', '', $size_attribute))];

        if ($size_value && !isset($sizes_data[$size_value])) {
            $sizes_data[$size_value] = array(
                'name' => $size_value,
                'slug' => $size_value,
                'in_stock' => $variation_obj->is_in_stock(),
                'variation_id' => $variation['variation_id']
            );
        }
    }

    // Sort sizes with smart sorting (numeric first, then alphabetic)
    uksort($sizes_data, function($a, $b) {
        $a_is_numeric = is_numeric($a);
        $b_is_numeric = is_numeric($b);

        // Both numeric: sort numerically
        if ($a_is_numeric && $b_is_numeric) {
            return (float)$a - (float)$b;
        }

        // One numeric, one not: numeric comes first
        if ($a_is_numeric && !$b_is_numeric) {
            return -1;
        }
        if (!$a_is_numeric && $b_is_numeric) {
            return 1;
        }

        // Both non-numeric: sort alphabetically
        return strcmp($a, $b);
    });

    // Limit the results
    $sizes = array_slice($sizes_data, 0, $limit, true);

    return $sizes;
}

/**
 * Product Badges Helper
 */
function eshop_get_product_badges($product) {
    $badges = array();

    // Sale Badge
    if ($product->is_on_sale()) {
        $badges[] = array(
            'text' => __('SALE', 'eshop-theme'),
            'class' => 'badge-sale',
            'style' => 'background-color: #dc2626; color: white;'
        );
    }

    // Out of Stock Badge
    if (!$product->is_in_stock()) {
        $badges[] = array(
            'text' => __('OUT OF STOCK', 'eshop-theme'),
            'class' => 'badge-out-of-stock',
            'style' => 'background-color: #6b7280; color: white;'
        );
    }

    // New/Hot Badge (products created in last 30 days)
    $created_date = get_the_date('U', $product->get_id());
    $thirty_days_ago = strtotime('-30 days');
    if ($created_date > $thirty_days_ago) {
        $badges[] = array(
            'text' => __('NEW', 'eshop-theme'),
            'class' => 'badge-new',
            'style' => 'background-color: #16a34a; color: white;'
        );
    }

    // Featured Badge
    if ($product->is_featured()) {
        $badges[] = array(
            'text' => __('HOT', 'eshop-theme'),
            'class' => 'badge-hot',
            'style' => 'background-color: #ea580c; color: white;'
        );
    }

    // Low Stock Badge (if stock is less than 5)
    if ($product->managing_stock() && $product->get_stock_quantity() <= 5 && $product->get_stock_quantity() > 0) {
        $badges[] = array(
            'text' => __('LOW STOCK', 'eshop-theme'),
            'class' => 'badge-low-stock',
            'style' => 'background-color: #f59e0b; color: white;'
        );
    }

    return $badges;
}

/**
 * Custom Single Product Variation Display
 */
function eshop_custom_variation_display() {
    global $product;

    if (!$product->is_type('variable')) {
        return;
    }

    $attributes = $product->get_variation_attributes();
    $available_variations = $product->get_available_variations();

    if (empty($attributes)) {
        return;
    }

    echo '<div class="custom-variations-wrapper">';

    foreach ($attributes as $attribute_name => $options) {
        $attribute_label = wc_attribute_label($attribute_name);
        $attribute_slug = str_replace('pa_', '', $attribute_name);

        echo '<div class="variation-attribute mb-4" data-attribute="' . esc_attr($attribute_slug) . '">';
        echo '<h4 class="variation-label text-sm font-semibold text-gray-900 mb-3">' . esc_html($attribute_label) . '</h4>';

        // Check if this is a size attribute
        $is_size_attribute = (strpos(strtolower($attribute_name), 'size') !== false);

        if ($is_size_attribute) {
            // Display sizes as circular buttons
            echo '<div class="size-options flex flex-wrap gap-2">';

            // Get size data with stock status
            $size_data = array();
            foreach ($available_variations as $variation) {
                $variation_obj = wc_get_product($variation['variation_id']);
                $attr_key = 'attribute_' . strtolower($attribute_name);
                $attr_value = $variation['attributes'][$attr_key];

                if ($attr_value && !isset($size_data[$attr_value])) {
                    $size_data[$attr_value] = array(
                        'name' => $attr_value,
                        'in_stock' => $variation_obj && $variation_obj->is_in_stock(),
                        'variation_id' => $variation['variation_id']
                    );
                }
            }

            // Sort sizes
            uksort($size_data, function($a, $b) {
                if (is_numeric($a) && is_numeric($b)) {
                    return (float)$a - (float)$b;
                }
                return strcmp($a, $b);
            });

            foreach ($size_data as $size_value => $size_info) {
                $classes = 'size-option-single w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center text-sm font-semibold transition-all duration-200 cursor-pointer';
                $classes .= !$size_info['in_stock'] ? ' opacity-50 cursor-not-allowed bg-gray-100 text-gray-400' : ' bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400';

                echo '<span class="' . $classes . '" ';
                echo 'data-value="' . esc_attr($size_value) . '" ';
                echo 'data-attribute="' . esc_attr($attribute_slug) . '" ';
                echo 'data-in-stock="' . ($size_info['in_stock'] ? 'true' : 'false') . '" ';
                echo 'title="' . esc_attr($size_value . (!$size_info['in_stock'] ? ' - Out of Stock' : '')) . '">';
                echo esc_html($size_value);
                echo '</span>';
            }

            echo '</div>';
        } else {
            // Display other attributes as buttons or swatches
            echo '<div class="attribute-options flex flex-wrap gap-2">';

            foreach ($options as $option) {
                if (empty($option)) continue;

                $classes = 'attribute-option px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400';

                echo '<span class="' . $classes . '" ';
                echo 'data-value="' . esc_attr($option) . '" ';
                echo 'data-attribute="' . esc_attr($attribute_slug) . '">';
                echo esc_html($option);
                echo '</span>';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    echo '</div>';
}

/**
 * Custom Single Product Badges Display
 */
function eshop_custom_single_product_badges() {
    global $product;

    $badges = eshop_get_product_badges($product);
    if (empty($badges)) {
        return;
    }

    echo '<div class="single-product-badges absolute top-4 left-4 flex flex-col gap-2 z-10">';
    foreach ($badges as $badge) {
        echo '<span class="badge ' . esc_attr($badge['class']) . ' text-xs px-3 py-1 font-semibold rounded text-center" style="' . esc_attr($badge['style']) . '">';
        echo esc_html($badge['text']);
        echo '</span>';
    }
    echo '</div>';
}

// Remove default sale flash and add our custom badges
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
add_action('woocommerce_before_single_product_summary', 'eshop_custom_single_product_badges', 10);

/**
 * Account Menu Functions
 */

// Get account menu items
function eshop_get_account_menu_items() {
    $items = array();
    
    if (is_user_logged_in()) {
        $items['dashboard'] = array(
            'title' => __('Dashboard', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('dashboard')
        );
        $items['orders'] = array(
            'title' => __('Orders', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('orders')
        );
        $items['downloads'] = array(
            'title' => __('Downloads', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('downloads')
        );
        $items['edit-address'] = array(
            'title' => __('Addresses', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('edit-address')
        );
        $items['edit-account'] = array(
            'title' => __('Account Details', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('edit-account')
        );
        $items['customer-logout'] = array(
            'title' => __('Logout', 'eshop-theme'),
            'url' => wc_logout_url()
        );
    } else {
        $items['login'] = array(
            'title' => __('Login', 'eshop-theme'),
            'url' => wc_get_page_permalink('myaccount')
        );
        $items['register'] = array(
            'title' => __('Register', 'eshop-theme'),
            'url' => wc_get_page_permalink('myaccount')
        );
    }
    
    return $items;
}