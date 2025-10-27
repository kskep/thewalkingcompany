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

$regular_price = $product->get_regular_price();
$sale_price = $product->get_sale_price();
$is_on_sale = $product->is_on_sale();
?>

<div class="product-pricing-container">
    <?php if ($is_on_sale) : ?>
        <!-- Sale Price Display -->
        <div class="pricing-display sale-active">
            <div class="price-main">
                <span class="current-price">
                    <?php echo wc_price($sale_price); ?>
                </span>
            </div>
            <div class="price-original">
                <span class="original-label"><?php _e('Προτ. Λιαν. Τιμή:', 'thewalkingtheme'); ?></span>
                <span class="original-price"><?php echo wc_price($regular_price); ?></span>
            </div>
            <?php
            $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
            if ($percentage > 0) :
            ?>
                <div class="discount-badge">
                    <span class="discount-text">-<?php echo esc_html($percentage); ?>%</span>
                </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <!-- Regular Price Display -->
        <div class="pricing-display">
            <div class="price-main">
                <span class="current-price">
                    <?php echo wc_price($regular_price); ?>
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
            echo $price_html;
            echo '</div>';
        }
    }
    ?>
</div>