<?php
/**
 * Stock Status Filter Component
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Get currently selected stock statuses
$selected_stock = isset($_GET['stock_status']) ? (array) $_GET['stock_status'] : array();

// Stock status options
$stock_options = array(
    'instock' => __('In Stock', 'eshop-theme'),
    'outofstock' => __('Out of Stock', 'eshop-theme'),
    'onbackorder' => __('On Backorder', 'eshop-theme'),
);
?>

<div class="filter-section mb-6">
    <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
        <?php esc_html_e('Stock Status', 'eshop-theme'); ?>
    </h4>
    
    <div class="stock-filter space-y-2">
        <?php foreach ($stock_options as $value => $label) :
            $is_checked = in_array($value, $selected_stock);

            // Get count of products with this stock status
            $count = get_stock_status_count($value);

            // Skip if no products with this status
            if ($count <= 0) continue;
        ?>
            <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">
                <div class="flex items-center space-x-2">
                    <input 
                        type="checkbox" 
                        name="stock_status[]" 
                        value="<?php echo esc_attr($value); ?>"
                        class="text-primary focus:ring-primary border-gray-300 rounded"
                        <?php checked($is_checked); ?>
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">
                        <?php echo esc_html($label); ?>
                    </span>
                    
                    <?php if ($value === 'instock') : ?>
                        <i class="fas fa-check-circle text-green-500 text-xs" title="<?php esc_attr_e('Available', 'eshop-theme'); ?>"></i>
                    <?php elseif ($value === 'outofstock') : ?>
                        <i class="fas fa-times-circle text-red-500 text-xs" title="<?php esc_attr_e('Not Available', 'eshop-theme'); ?>"></i>
                    <?php elseif ($value === 'onbackorder') : ?>
                        <i class="fas fa-clock text-yellow-500 text-xs" title="<?php esc_attr_e('Available Soon', 'eshop-theme'); ?>"></i>
                    <?php endif; ?>
                </div>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                    <?php echo esc_html($count); ?>
                </span>
            </label>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Helper function to get stock status count
if (!function_exists('get_stock_status_count')) {
    function get_stock_status_count($status) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT p.ID) 
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_stock_status'
            AND pm.meta_value = %s
        ", $status));
        
        return intval($count);
    }
}
?>
