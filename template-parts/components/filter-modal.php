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
<div id="filter-drawer" data-position="right" class="fixed inset-y-0 right-0 w-full max-w-md bg-white z-50 transform translate-x-full transition-transform duration-300 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="filter-drawer-title">

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
            // Load modular filter components using the same pattern as filters.php
            
            // Price Filter
            get_template_part('template-parts/components/filters/price-filter');
            
            // Category Filter
            get_template_part('template-parts/components/filters/category-filter');

            // Attributes: dynamically include all registered product attributes that have available terms
            if (function_exists('wc_get_attribute_taxonomies')) {
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                if (!empty($attribute_taxonomies)) {
                    foreach ($attribute_taxonomies as $attr) {
                        // $attr is stdClass with properties attribute_name, attribute_label, etc.
                        $taxonomy = 'pa_' . $attr->attribute_name;

                        // Optionally skip attributes you never want to show
                        $skip = apply_filters('eshop_filters_skip_attribute', false, $taxonomy, $attr);
                        if ($skip) { continue; }

                        // Render attribute filter; component will early-return if there are no terms in current context
                        get_template_part('template-parts/components/filters/attribute-filter', null, array(
                            'taxonomy' => $taxonomy,
                            'label'    => $attr->attribute_label,
                        ));
                    }
                }
            }

            // Sale Filter
            get_template_part('template-parts/components/filters/sale-filter');
            
            // Stock Filter
            get_template_part('template-parts/components/filters/stock-filter');
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
