<?php
/**
 * Price Range Filter Component
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Get current price range from URL or defaults
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';

// Get price range from products
global $wpdb;
$min_price_range = 0;
$max_price_range = 1000;

// Try to get actual price range from products
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
?>

<div class="filter-section mb-6">
    <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
        <?php esc_html_e('Price Range', 'eshop-theme'); ?>
    </h4>
    
    <div class="price-filter">
        <!-- Price Input Fields -->
        <div class="price-inputs flex space-x-2 mb-3">
            <input 
                type="number" 
                id="min-price" 
                class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary rounded" 
                placeholder="<?php esc_attr_e('Min', 'eshop-theme'); ?>"
                value="<?php echo esc_attr($min_price); ?>"
                min="<?php echo esc_attr($min_price_range); ?>"
                max="<?php echo esc_attr($max_price_range); ?>"
            >
            <span class="flex items-center text-gray-400">-</span>
            <input 
                type="number" 
                id="max-price" 
                class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary rounded" 
                placeholder="<?php esc_attr_e('Max', 'eshop-theme'); ?>"
                value="<?php echo esc_attr($max_price); ?>"
                min="<?php echo esc_attr($min_price_range); ?>"
                max="<?php echo esc_attr($max_price_range); ?>"
            >
        </div>

        <!-- Price Range Slider -->
        <div class="price-slider-container mb-4">
            <div id="price-slider" class="price-slider"></div>
        </div>

        <!-- Price Range Display -->
        <div class="price-range-display text-xs text-gray-500 flex justify-between">
            <span><?php echo wc_price($min_price_range); ?></span>
            <span><?php echo wc_price($max_price_range); ?></span>
        </div>

        <!-- Apply Button -->
        <button class="apply-price-filter w-full mt-3 px-4 py-2 bg-primary text-white text-sm font-medium uppercase tracking-wide hover:bg-primary-dark transition-colors">
            <?php _e('Apply Price Filter', 'eshop-theme'); ?>
        </button>
    </div>
</div>
