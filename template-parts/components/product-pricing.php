<?php
/**
 * Product Pricing Component
 * 
 * Displays product pricing with sale price handling and magazine styling
 *
 * @package thewalkingtheme
 */

defined('ABSPATH') || exit;

global $product;

if (!$product) {
    return;
}

// Get prices safely
$regular_price = $product->get_regular_price();
$sale_price = $product->get_sale_price();
$is_on_sale = $product->is_on_sale();

// Convert to numbers safely
$regular_price_num = is_numeric($regular_price) ? floatval($regular_price) : 0;
$sale_price_num = is_numeric($sale_price) ? floatval($sale_price) : 0;

// Only show pricing if we have valid prices
if ($regular_price_num <= 0 && $sale_price_num <= 0) {
    return;
}
?>

<div class="product-pricing-container">
    <?php if ($is_on_sale && $sale_price_num > 0 && $regular_price_num > 0) : ?>
        <!-- Sale Price Display -->
        <div class="pricing-display sale-active">
            <div class="price-main">
                <span class="current-price">
                    <?php echo wc_price($sale_price_num); ?>
                </span>
            </div>
            <div class="price-original">
                <span class="original-label"><?php _e('Προτ. Λιαν. Τιμή:', 'thewalkingtheme'); ?></span>
                <span class="original-price"><?php echo wc_price($regular_price_num); ?></span>
            </div>
            <?php
            // Calculate percentage only if we have valid numbers
            $percentage = 0;
            if ($regular_price_num > 0 && $sale_price_num > 0) {
                $percentage = round((($regular_price_num - $sale_price_num) / $regular_price_num) * 100);
            }
            
            if ($percentage > 0) :
            ?>
                <div class="discount-badge">
                    <span class="discount-text">-<?php echo esc_html($percentage); ?>%</span>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif ($regular_price_num > 0) : ?>
        <!-- Regular Price Display -->
        <div class="pricing-display">
            <div class="price-main">
                <span class="current-price">
                    <?php echo wc_price($regular_price_num); ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
    
    <?php
    // Show price range for variable products
    if ($product->is_type('variable')) {
        $price_html = $product->get_price_html();
        if ($price_html && strpos($price_html, '–') !== false) {
            echo '<div class="price-range">';
            echo '<span class="range-label">' . __('Τιμές από:', 'thewalkingtheme') . '</span>';
            echo wp_kses_post($price_html);
            echo '</div>';
        }
    }
    ?>
</div>