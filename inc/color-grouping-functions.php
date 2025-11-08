<?php
/**
 * Color Grouping Functions
 * 
 * Handles product color linking via grouped-sku custom field
 *
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Core Color Grouping Functions
 */

/**
 * Get all products in the same color group
 *
 * @param string $grouped_sku The grouped SKU identifier
 * @return array Array of WC_Product objects
 */
function eshop_get_color_group_products($grouped_sku) {
    if (empty($grouped_sku)) {
        return array();
    }

    return wc_get_products(array(
        'meta_key' => 'grouped-sku',
        'meta_value' => $grouped_sku,
        'status' => 'publish',
        'limit' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'eshop_bypass_stock_filter' => true,
    ));
}

/**
 * Get color variants for display on single product page
 *
 * @param int $product_id Current product ID
 * @return array Array of color variant data
 */
function eshop_get_product_color_group_variants($product_id) {
    $grouped_sku = get_post_meta($product_id, 'grouped-sku', true);
    
    if (!$grouped_sku) {
        return array();
    }
    
    $products = eshop_get_color_group_products($grouped_sku);
    $variants = array();
    
    foreach ($products as $product) {
        $variant_id = $product->get_id();
        $color_name = get_post_meta($variant_id, 'color-name', true);
        $color_hex = get_post_meta($variant_id, 'color-hex', true);
        $featured_image = get_the_post_thumbnail_url($variant_id, 'thumbnail');
        
        // Fallback to product name if no color name specified
        if (!$color_name) {
            $color_name = $product->get_name();
        }
        
        $variants[] = array(
            'id' => $variant_id,
            'name' => $color_name,
            'hex' => $color_hex,
            'image' => $featured_image,
            'url' => get_permalink($variant_id),
            'price' => $product->get_price_html(),
            'in_stock' => $product->is_in_stock(),
            'is_current' => $variant_id == $product_id
        );
    }
    
    return $variants;
}

/**
 * Check if a product has color variants
 *
 * @param int $product_id Product ID to check
 * @return bool True if product has color variants
 */
function eshop_product_has_color_variants($product_id) {
    $variants = eshop_get_product_color_group_variants($product_id);
    return count($variants) > 1;
}

/**
 * Get grouped SKU for a product
 *
 * @param int $product_id Product ID
 * @return string|false Grouped SKU or false if not set
 */
function eshop_get_product_grouped_sku($product_id) {
    return get_post_meta($product_id, 'grouped-sku', true);
}

/**
 * Set grouped SKU for a product
 *
 * @param int $product_id Product ID
 * @param string $grouped_sku Grouped SKU value
 * @return bool True on success, false on failure
 */
function eshop_set_product_grouped_sku($product_id, $grouped_sku) {
    return update_post_meta($product_id, 'grouped-sku', sanitize_text_field($grouped_sku));
}

/**
 * Get color information for a product
 *
 * @param int $product_id Product ID
 * @return array Array with 'name' and 'hex' keys
 */
function eshop_get_product_color_info($product_id) {
    return array(
        'name' => get_post_meta($product_id, 'color-name', true),
        'hex' => get_post_meta($product_id, 'color-hex', true)
    );
}

/**
 * Set color information for a product
 *
 * @param int $product_id Product ID
 * @param string $color_name Color display name
 * @param string $color_hex Hex color code
 * @return bool True on success, false on failure
 */
function eshop_set_product_color_info($product_id, $color_name = '', $color_hex = '') {
    $success = true;
    
    if ($color_name !== '') {
        $success = $success && update_post_meta($product_id, 'color-name', sanitize_text_field($color_name));
    }
    
    if ($color_hex !== '') {
        // Validate hex color
        $color_hex = sanitize_hex_color($color_hex);
        if ($color_hex) {
            $success = $success && update_post_meta($product_id, 'color-hex', $color_hex);
        } else {
            $success = false;
        }
    }
    
    return $success;
}

/**
 * Admin Meta Box Functions
 */

/**
 * Add color grouping meta box to product edit screen
 */
function eshop_add_color_grouping_meta_box() {
    add_meta_box(
        'eshop-color-grouping',
        __('Color Grouping', 'eshop-theme'),
        'eshop_render_color_grouping_meta_box',
        'product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'eshop_add_color_grouping_meta_box');

/**
 * Render the color grouping meta box
 *
 * @param WP_Post $post Current post object
 */
function eshop_render_color_grouping_meta_box($post) {
    // Add nonce for security
    wp_nonce_field('eshop_color_grouping_nonce', 'eshop_color_grouping_nonce_field');
    
    // Get current values
    $grouped_sku = get_post_meta($post->ID, 'grouped-sku', true);
    $color_name = get_post_meta($post->ID, 'color-name', true);
    $color_hex = get_post_meta($post->ID, 'color-hex', true);
    
    // Get related products if grouped SKU exists
    $related_products = array();
    if ($grouped_sku) {
        $products = eshop_get_color_group_products($grouped_sku);
        foreach ($products as $product) {
            if ($product->get_id() != $post->ID) {
                $related_products[] = $product;
            }
        }
    }
    ?>
    <div class="eshop-color-grouping-fields">
        <style>
            .eshop-color-grouping-fields .field-group {
                margin-bottom: 15px;
            }
            .eshop-color-grouping-fields label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .eshop-color-grouping-fields input[type="text"] {
                width: 100%;
                max-width: 400px;
            }
            .eshop-color-grouping-fields .color-preview {
                display: inline-block;
                width: 30px;
                height: 30px;
                border: 2px solid #ddd;
                border-radius: 50%;
                margin-left: 10px;
                vertical-align: middle;
            }
            .related-products {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 4px;
                margin-top: 15px;
            }
            .related-products .product-item {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
                padding: 8px;
                background: white;
                border-radius: 3px;
            }
            .related-products .product-item img {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                margin-right: 10px;
            }
        </style>
        
        <div class="field-group">
            <label for="grouped-sku"><?php _e('Grouped SKU', 'eshop-theme'); ?></label>
            <input type="text" 
                   id="grouped-sku" 
                   name="grouped-sku" 
                   value="<?php echo esc_attr($grouped_sku); ?>" 
                   placeholder="<?php _e('Enter common identifier for color variants', 'eshop-theme'); ?>" />
            <p class="description">
                <?php _e('Products with the same grouped SKU will be displayed as color variants. Use a unique identifier like "shoe-model-abc".', 'eshop-theme'); ?>
            </p>
        </div>
        
        <div class="field-group">
            <label for="color-name"><?php _e('Color Name', 'eshop-theme'); ?></label>
            <input type="text" 
                   id="color-name" 
                   name="color-name" 
                   value="<?php echo esc_attr($color_name); ?>" 
                   placeholder="<?php _e('e.g. Crimson Red', 'eshop-theme'); ?>" />
            <p class="description">
                <?php _e('Display name for this color variant.', 'eshop-theme'); ?>
            </p>
        </div>
        
        <div class="field-group">
            <label for="color-hex"><?php _e('Color Hex Code', 'eshop-theme'); ?></label>
            <input type="color" 
                   id="color-hex" 
                   name="color-hex" 
                   value="<?php echo esc_attr($color_hex ?: '#000000'); ?>" />
            <input type="text" 
                   id="color-hex-text" 
                   name="color-hex-text" 
                   value="<?php echo esc_attr($color_hex); ?>" 
                   placeholder="#FF0000" 
                   style="width: 100px; margin-left: 10px;" />
            <div class="color-preview" style="background-color: <?php echo esc_attr($color_hex ?: '#ffffff'); ?>"></div>
            <p class="description">
                <?php _e('Used as fallback when no featured image is available. Will display as a circular color swatch.', 'eshop-theme'); ?>
            </p>
        </div>
        
        <?php if (!empty($related_products)) : ?>
            <div class="related-products">
                <h4><?php _e('Related Color Variants', 'eshop-theme'); ?> (<?php echo count($related_products); ?>)</h4>
                <?php foreach ($related_products as $product) : 
                    $variant_color_name = get_post_meta($product->get_id(), 'color-name', true);
                    $variant_color_hex = get_post_meta($product->get_id(), 'color-hex', true);
                    $variant_image = get_the_post_thumbnail_url($product->get_id(), 'thumbnail');
                ?>
                    <div class="product-item">
                        <?php if ($variant_image) : ?>
                            <img src="<?php echo esc_url($variant_image); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" />
                        <?php elseif ($variant_color_hex) : ?>
                            <div style="width: 40px; height: 40px; background-color: <?php echo esc_attr($variant_color_hex); ?>; border-radius: 50%; margin-right: 10px;"></div>
                        <?php else : ?>
                            <div style="width: 40px; height: 40px; background-color: #f0f0f0; border-radius: 50%; margin-right: 10px;"></div>
                        <?php endif; ?>
                        <div>
                            <strong><?php echo esc_html($product->get_name()); ?></strong>
                            <?php if ($variant_color_name) : ?>
                                <br><small><?php echo esc_html($variant_color_name); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <script>
        jQuery(document).ready(function($) {
            // Sync color picker with text input
            $('#color-hex').on('change', function() {
                $('#color-hex-text').val($(this).val());
                $('.color-preview').css('background-color', $(this).val());
            });
            
            $('#color-hex-text').on('input', function() {
                var color = $(this).val();
                if (color.match(/^#[0-9A-F]{6}$/i)) {
                    $('#color-hex').val(color);
                    $('.color-preview').css('background-color', color);
                }
            });
        });
        </script>
    </div>
    <?php
}

/**
 * Save color grouping meta box data
 *
 * @param int $post_id Post ID
 */
function eshop_save_color_grouping_meta($post_id) {
    // Verify nonce
    if (!isset($_POST['eshop_color_grouping_nonce_field']) || 
        !wp_verify_nonce($_POST['eshop_color_grouping_nonce_field'], 'eshop_color_grouping_nonce')) {
        return;
    }

    // Check if user has permission to edit post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Don't save on autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Only save for products
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // Save grouped SKU
    if (isset($_POST['grouped-sku'])) {
        $grouped_sku = sanitize_text_field($_POST['grouped-sku']);
        if (!empty($grouped_sku)) {
            update_post_meta($post_id, 'grouped-sku', $grouped_sku);
        } else {
            delete_post_meta($post_id, 'grouped-sku');
        }
    }

    // Save color name
    if (isset($_POST['color-name'])) {
        $color_name = sanitize_text_field($_POST['color-name']);
        if (!empty($color_name)) {
            update_post_meta($post_id, 'color-name', $color_name);
        } else {
            delete_post_meta($post_id, 'color-name');
        }
    }

    // Save color hex - use the color picker value, fallback to text input
    $color_hex = '';
    if (isset($_POST['color-hex']) && !empty($_POST['color-hex'])) {
        $color_hex = sanitize_hex_color($_POST['color-hex']);
    } elseif (isset($_POST['color-hex-text']) && !empty($_POST['color-hex-text'])) {
        $color_hex = sanitize_hex_color($_POST['color-hex-text']);
    }
    
    if ($color_hex) {
        update_post_meta($post_id, 'color-hex', $color_hex);
    } else {
        delete_post_meta($post_id, 'color-hex');
    }
}
add_action('save_post', 'eshop_save_color_grouping_meta');

/**
 * AJAX Functions for Color Variants
 */

/**
 * AJAX handler for fetching color variants
 */
function eshop_ajax_get_color_variants() {
    // Verify nonce
    if (!check_ajax_referer('eshop_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => __('Security check failed', 'eshop-theme')
        ));
    }

    $product_id = intval($_POST['product_id']);
    
    if (!$product_id) {
        wp_send_json_error(array(
            'message' => __('Invalid product ID', 'eshop-theme')
        ));
    }
    
    $variants = eshop_get_product_color_group_variants($product_id);
    
    wp_send_json_success(array(
        'variants' => $variants,
        'current_id' => $product_id,
        'count' => count($variants)
    ));
}
add_action('wp_ajax_get_color_variants', 'eshop_ajax_get_color_variants');
add_action('wp_ajax_nopriv_get_color_variants', 'eshop_ajax_get_color_variants');

/**
 * Display Functions
 */

/**
 * Display color variants on single product page
 * Called via woocommerce_single_product_summary hook
 */
function eshop_display_color_variants() {
    global $product;
    
    if (!$product) {
        return;
    }
    
    $product_id = $product->get_id();
    $variants = eshop_get_product_color_group_variants($product_id);
    
    // Only display if there are multiple variants
    if (count($variants) <= 1) {
        return;
    }
    
    // Load the template part
    get_template_part('template-parts/components/color-variants', null, array(
        'variants' => $variants,
        'current_id' => $product_id
    ));
}

// Hook color variants just before the add-to-cart form so it appears with sizing controls
add_action('woocommerce_before_add_to_cart_form', 'eshop_display_color_variants', 5);

/**
 * Utility Functions
 */

/**
 * Sanitize hex color with # prefix
 *
 * @param string $color Color string to sanitize
 * @return string|false Sanitized color or false if invalid
 */
function eshop_sanitize_hex_color($color) {
    $color = ltrim($color, '#');
    
    if (ctype_xdigit($color) && (strlen($color) == 6 || strlen($color) == 3)) {
        return '#' . $color;
    }
    
    return false;
}

/**
 * Get all unique grouped SKUs for admin reference
 *
 * @return array Array of grouped SKUs with product counts
 */
function eshop_get_all_grouped_skus() {
    global $wpdb;
    
    $results = $wpdb->get_results("
        SELECT pm.meta_value as grouped_sku, COUNT(pm.post_id) as product_count
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'grouped-sku' 
        AND pm.meta_value != ''
        AND p.post_type = 'product'
        AND p.post_status = 'publish'
        GROUP BY pm.meta_value
        ORDER BY pm.meta_value ASC
    ");
    
    return $results ? $results : array();
}