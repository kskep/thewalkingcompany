<?php
/**
 * Product Filter Modal Component
 * 
 * Renders the off-canvas filter drawer for product archives
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Only show on WooCommerce archive pages
if (!class_exists('WooCommerce') || !(is_shop() || is_product_category() || is_product_tag())) {
    return;
}
?>

<!-- Filter Backdrop -->
<div id="filter-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>

<!-- Off-Canvas Filter Drawer -->
<div id="filter-drawer" class="fixed inset-y-0 right-0 w-full max-w-md bg-white z-50 transform translate-x-full transition-transform duration-300 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="filter-drawer-title">

    <!-- Drawer Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white">
        <h2 id="filter-drawer-title" class="text-lg font-semibold text-gray-900 uppercase tracking-wide">
            <?php _e('Filters', 'eshop-theme'); ?>
        </h2>
        <button id="close-filters" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" aria-label="<?php esc_attr_e('Close Filters', 'eshop-theme'); ?>">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Drawer Content -->
    <div class="flex-1 overflow-y-auto px-6 py-4 h-[calc(100vh-140px)]">
        <?php
        // Use dynamic widgets if available
        if (is_active_sidebar('shop-filters')) {
            dynamic_sidebar('shop-filters');
        } else {
            // Load real filter components

            // Price Filter with Slider
            global $wpdb;
            $min_price_range = 0;
            $max_price_range = 1000;

            // Get actual price range from products
            $prices = $wpdb->get_row("
                SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price,
                       MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_price'
                AND p.post_type = 'product'
                AND p.post_status = 'publish'
                AND pm.meta_value != ''
            ");

            if ($prices && $prices->max_price > 0) {
                $min_price_range = floor($prices->min_price);
                $max_price_range = ceil($prices->max_price);
            }

            $current_min = isset($_GET['min_price']) ? floatval($_GET['min_price']) : $min_price_range;
            $current_max = isset($_GET['max_price']) ? floatval($_GET['max_price']) : $max_price_range;

            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Price Range', 'eshop-theme') . '</h4>';
            echo '<div class="price-filter">';

            // Price display
            echo '<div class="price-display flex justify-between items-center mb-4">';
            echo '<span class="price-min text-sm font-medium text-gray-700">' . wc_price($current_min) . '</span>';
            echo '<span class="price-max text-sm font-medium text-gray-700">' . wc_price($current_max) . '</span>';
            echo '</div>';

            // Price slider
            echo '<div class="price-slider-container mb-4">';
            echo '<div id="price-slider" class="price-slider" data-min="' . esc_attr($min_price_range) . '" data-max="' . esc_attr($max_price_range) . '" data-current-min="' . esc_attr($current_min) . '" data-current-max="' . esc_attr($current_max) . '"></div>';
            echo '</div>';

            // Hidden inputs for form submission
            echo '<input type="hidden" id="min-price" value="' . esc_attr($current_min) . '">';
            echo '<input type="hidden" id="max-price" value="' . esc_attr($current_max) . '">';

            echo '</div>';
            echo '</div>';

            // Product Categories Filter
            $product_categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => 0, // Only top-level categories
                'number' => 10
            ));

            if (!empty($product_categories) && !is_wp_error($product_categories)) {
                echo '<div class="filter-section mb-6">';
                echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Categories', 'eshop-theme') . '</h4>';
                echo '<div class="category-filter space-y-2 max-h-48 overflow-y-auto">';

                foreach ($product_categories as $category) {
                    $is_selected = isset($_GET['product_cat']) && in_array($category->slug, (array)$_GET['product_cat']);
                    echo '<label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">';
                    echo '<div class="flex items-center space-x-2">';
                    echo '<input type="checkbox" name="product_cat[]" value="' . esc_attr($category->slug) . '" class="text-primary focus:ring-primary border-gray-300 rounded"' . checked($is_selected, true, false) . '>';
                    echo '<span class="text-sm text-gray-700 group-hover:text-gray-900">' . esc_html($category->name) . '</span>';
                    echo '</div>';
                    echo '<span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">' . esc_html($category->count) . '</span>';
                    echo '</label>';
                }

                echo '</div>';
                echo '</div>';
            }

            // On Sale Filter
            $on_sale_selected = isset($_GET['on_sale']) && $_GET['on_sale'] === '1';
            $sale_count = count(wc_get_product_ids_on_sale());

            if ($sale_count > 0) {
                echo '<div class="filter-section mb-6">';
                echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Special Offers', 'eshop-theme') . '</h4>';
                echo '<div class="sale-filter space-y-2">';
                echo '<label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">';
                echo '<div class="flex items-center space-x-2">';
                echo '<input type="checkbox" name="on_sale" value="1" class="text-primary focus:ring-primary border-gray-300 rounded"' . checked($on_sale_selected, true, false) . '>';
                echo '<span class="text-sm text-gray-700 group-hover:text-gray-900">' . esc_html__('On Sale', 'eshop-theme') . '</span>';
                echo '<i class="fas fa-tag text-red-500 text-xs" title="' . esc_attr__('Sale Items', 'eshop-theme') . '"></i>';
                echo '</div>';
                echo '<span class="text-xs text-gray-400 bg-red-100 text-red-600 px-2 py-1 rounded-full font-medium">' . esc_html($sale_count) . '</span>';
                echo '</label>';
                echo '</div>';
                echo '</div>';
            }

            // Debug: Show available product attributes
            echo '<!-- DEBUG: Available Product Attributes -->';
            $all_attributes = wc_get_attribute_taxonomies();
            if (!empty($all_attributes)) {
                echo '<!-- Available attributes: ';
                foreach ($all_attributes as $attr) {
                    echo $attr->attribute_name . ' (pa_' . $attr->attribute_name . '), ';
                }
                echo '-->';
            } else {
                echo '<!-- No product attributes found in WooCommerce -->';
            }

            // Your Custom Attributes
            $your_attributes = array(
                'pa_box' => array(
                    'label' => 'Box',
                    'type' => 'checkbox'
                ),
                'pa_color' => array(
                    'label' => 'Color',
                    'type' => 'checkbox'
                ),
                'pa_pick-pattern' => array(
                    'label' => 'Pick Pattern',
                    'type' => 'checkbox'
                ),
                'pa_select-size' => array(
                    'label' => 'Select Size',
                    'type' => 'size_grid'
                ),
                'pa_size-selection' => array(
                    'label' => 'Size Selection',
                    'type' => 'size_grid'
                )
            );

            foreach ($your_attributes as $taxonomy => $attr_config) {
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => true,
                    'orderby' => 'menu_order',
                    'order' => 'ASC'
                ));

                echo '<!-- DEBUG: ' . $taxonomy . ' terms found: ' . count($terms) . ' -->';
                if (is_wp_error($terms)) {
                    echo '<!-- DEBUG: ' . $taxonomy . ' error: ' . $terms->get_error_message() . ' -->';
                    continue;
                }

                if (!empty($terms)) {
                    $selected_values = isset($_GET[$taxonomy]) ? (array)$_GET[$taxonomy] : array();

                    echo '<div class="filter-section mb-6">';
                    echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html($attr_config['label']) . '</h4>';

                    if ($attr_config['type'] === 'size_grid') {
                        // Size grid layout for size attributes
                        echo '<div class="size-filter">';
                        echo '<div class="size-grid grid grid-cols-4 gap-2">';

                        foreach ($terms as $term) {
                            $is_selected = in_array($term->slug, $selected_values);
                            $selected_class = $is_selected ? 'bg-primary text-white border-primary' : 'bg-white text-gray-700 border-gray-300 hover:border-primary';

                            echo '<label class="size-option cursor-pointer">';
                            echo '<input type="checkbox" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr($term->slug) . '" class="hidden"' . checked($is_selected, true, false) . '>';
                            echo '<span class="size-label block text-center py-2 px-3 border text-sm font-medium transition-all duration-200 ' . $selected_class . '">';
                            echo esc_html($term->name);
                            echo '</span>';
                            echo '</label>';
                        }

                        echo '</div>';
                        echo '</div>';
                    } else {
                        // Regular checkbox layout for other attributes
                        echo '<div class="attribute-filter space-y-2 max-h-48 overflow-y-auto">';

                        foreach ($terms as $term) {
                            $is_selected = in_array($term->slug, $selected_values);

                            echo '<label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">';
                            echo '<div class="flex items-center space-x-2">';
                            echo '<input type="checkbox" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr($term->slug) . '" class="text-primary focus:ring-primary border-gray-300 rounded"' . checked($is_selected, true, false) . '>';

                            // Special handling for color attribute
                            if ($taxonomy === 'pa_color') {
                                $color_value = get_term_meta($term->term_id, 'color', true);
                                if ($color_value) {
                                    echo '<span class="w-4 h-4 rounded-full border border-gray-300 inline-block" style="background-color: ' . esc_attr($color_value) . ';" title="' . esc_attr($term->name) . '"></span>';
                                }
                            }

                            echo '<span class="text-sm text-gray-700 group-hover:text-gray-900">' . esc_html($term->name) . '</span>';
                            echo '</div>';
                            echo '<span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">' . esc_html($term->count) . '</span>';
                            echo '</label>';
                        }

                        echo '</div>';
                    }

                    echo '</div>';
                }
            }


        }
        ?>
    </div>

    <!-- Drawer Footer -->
    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
        <button id="clear-filters" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
            <i class="fas fa-times mr-2"></i>
            <?php _e('Clear All', 'eshop-theme'); ?>
        </button>
        <button id="apply-filters" class="px-6 py-2 bg-primary text-white text-sm font-medium uppercase tracking-wide hover:bg-primary-dark transition-colors">
            <?php _e('Apply Filters', 'eshop-theme'); ?>
        </button>
    </div>
</div>

<!-- Filter Modal CSS -->
<style>
#filter-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 40;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

#filter-backdrop.show {
    opacity: 1 !important;
    visibility: visible !important;
}

#filter-drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    max-width: 400px;
    background: white;
    z-index: 50;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    box-shadow: -4px 0 20px rgba(0, 0, 0, 0.1);
}

#filter-drawer.open {
    transform: translateX(0) !important;
}

body.overflow-hidden {
    overflow: hidden !important;
}

.hidden {
    display: none !important;
}

/* Filter section hover effects */
.filter-section label:hover {
    background-color: #f9fafb;
}

/* Price Slider Styles */
.price-slider {
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
    position: relative;
    margin: 10px 0;
}

.price-slider .noUi-connect {
    background: #ee81b3;
}

.price-slider .noUi-handle {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ee81b3;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    cursor: pointer;
}

.price-slider .noUi-handle:before,
.price-slider .noUi-handle:after {
    display: none;
}

/* Size Filter Styles */
.size-option input:checked + .size-label {
    background-color: #ee81b3 !important;
    color: white !important;
    border-color: #ee81b3 !important;
}

.size-label:hover {
    border-color: #ee81b3 !important;
}

/* Color Swatch Styles */
.color-filter .color-swatch {
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 1px solid #d1d5db;
    display: inline-block;
    position: relative;
}

/* Button hover effects */
.apply-price-filter:hover,
#apply-filters:hover {
    background-color: #d946a0 !important;
}

#close-filters:hover {
    color: #6b7280 !important;
}

#clear-filters:hover {
    color: #374151 !important;
}
</style>

<!-- Active Filters Bar -->
<div class="active-filters-bar bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 p-4 mb-6" style="display: none;">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                <?php _e('Active Filters:', 'eshop-theme'); ?>
            </span>
            <div class="active-filters-list flex flex-wrap gap-2"></div>
        </div>
        <button class="clear-all-filters text-sm text-red-600 hover:text-red-800 font-semibold uppercase tracking-wide transition-colors">
            <i class="fas fa-times mr-1"></i>
            <?php _e('Clear All', 'eshop-theme'); ?>
        </button>
    </div>
</div>

<!-- Loading Overlay -->
<div class="products-loading hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
    <div class="flex items-center space-x-2">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
        <span class="text-sm text-gray-600"><?php _e('Loading...', 'eshop-theme'); ?></span>
    </div>
</div>
