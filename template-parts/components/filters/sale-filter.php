<?php
/**
 * Sale/Special Offers Filter Component
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Get currently selected sale status
$on_sale_selected = isset($_GET['on_sale']) && $_GET['on_sale'] == '1';

// Get count of products on sale
$sale_count = get_sale_products_count();

// Don't show if no products on sale
if ($sale_count <= 0) {
    return;
}
?>

<div class="filter-section mb-6">
    <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
        <?php esc_html_e('Special Offers', 'eshop-theme'); ?>
    </h4>
    
    <div class="sale-filter space-y-2">
        <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">
            <div class="flex items-center space-x-2">
                <input 
                    type="checkbox" 
                    name="on_sale" 
                    value="1"
                    class="text-primary focus:ring-primary border-gray-300 rounded"
                    <?php checked($on_sale_selected); ?>
                >
                <span class="text-sm text-gray-700 group-hover:text-gray-900">
                    <?php esc_html_e('On Sale', 'eshop-theme'); ?>
                </span>
                <i class="fas fa-tag text-red-500 text-xs" title="<?php esc_attr_e('Sale Items', 'eshop-theme'); ?>"></i>
            </div>
            <span class="text-xs text-gray-400 bg-red-100 text-red-600 px-2 py-1 rounded-full font-medium">
                <?php echo esc_html($sale_count); ?>
            </span>
        </label>

        <!-- Additional sale-related filters could go here -->
        <?php
        // Check if we have featured products
        $featured_count = get_featured_products_count();
        if ($featured_count > 0) :
            $featured_selected = isset($_GET['featured']) && $_GET['featured'] == '1';
        ?>
            <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">
                <div class="flex items-center space-x-2">
                    <input 
                        type="checkbox" 
                        name="featured" 
                        value="1"
                        class="text-primary focus:ring-primary border-gray-300 rounded"
                        <?php checked($featured_selected); ?>
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">
                        <?php esc_html_e('Featured Products', 'eshop-theme'); ?>
                    </span>
                    <i class="fas fa-star text-yellow-500 text-xs" title="<?php esc_attr_e('Featured Items', 'eshop-theme'); ?>"></i>
                </div>
                <span class="text-xs text-gray-400 bg-yellow-100 text-yellow-600 px-2 py-1 rounded-full font-medium">
                    <?php echo esc_html($featured_count); ?>
                </span>
            </label>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper function to get sale products count
if (!function_exists('get_sale_products_count')) {
    function get_sale_products_count() {
        $sale_products = wc_get_product_ids_on_sale();
        return count($sale_products);
    }
}

// Helper function to get featured products count
if (!function_exists('get_featured_products_count')) {
    function get_featured_products_count() {
        $featured_products = wc_get_featured_product_ids();
        return count($featured_products);
    }
}
?>
