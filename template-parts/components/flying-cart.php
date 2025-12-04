<?php
/**
 * Flying Cart Component
 * 
 * A floating cart widget that appears on all pages
 * Features: cart count, total, mini cart preview, animations
 * 
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Only show if WooCommerce is active
if (!class_exists('WooCommerce')) {
    return;
}

// Get cart data
$cart_count = WC()->cart->get_cart_contents_count();
$cart_total = WC()->cart->get_cart_total();
$cart_items = WC()->cart->get_cart();
$is_cart_empty = WC()->cart->is_empty();

// Only render the flying cart when cart has items
if ($is_cart_empty) {
    return;
}
?>

<!-- Flying Cart Component -->
<div id="flying-cart" class="flying-cart cart-has-items">
    
    <!-- Cart Toggle Button - Compact icon with quantity -->
    <div class="flying-cart__toggle" role="button" tabindex="0" aria-label="Toggle Flying Cart">
        <div class="cart-icon-wrapper">
            <i class="fas fa-shopping-bag cart-icon"></i>
            <span class="cart-count-badge visible" data-count="<?php echo $cart_count; ?>">
                <?php echo $cart_count; ?>
            </span>
        </div>
    </div>

    <!-- Cart Content Panel -->
    <div class="flying-cart__panel">
        <div class="cart-panel-header">
            <h3 class="cart-title"><?php _e('Shopping Cart', 'eshop-theme'); ?></h3>
            <button class="cart-close-btn" aria-label="Close Cart">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="cart-panel-content">
            <?php if ($is_cart_empty) : ?>
                <!-- Empty Cart State -->
                <div class="empty-cart-state">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <p class="empty-cart-message"><?php _e('Your cart is empty', 'eshop-theme'); ?></p>
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="continue-shopping-btn">
                        <?php _e('Continue Shopping', 'eshop-theme'); ?>
                    </a>
                </div>
            <?php else : ?>
                <!-- Cart Items -->
                <div class="cart-items-list">
                    <?php foreach ($cart_items as $cart_item_key => $cart_item) :
                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                        
                        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key)) :
                            $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                            $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                            $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                    ?>
                        <div class="cart-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                            <div class="cart-item-image">
                                <?php if (empty($product_permalink)) : ?>
                                    <?php echo $thumbnail; ?>
                                <?php else : ?>
                                    <a href="<?php echo esc_url($product_permalink); ?>">
                                        <?php echo $thumbnail; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cart-item-details">
                                <div class="cart-item-name">
                                    <?php if (empty($product_permalink)) : ?>
                                        <?php echo wp_kses_post($product_name); ?>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url($product_permalink); ?>">
                                            <?php echo wp_kses_post($product_name); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="cart-item-meta">
                                    <span class="cart-item-quantity"><?php echo sprintf(__('Qty: %s', 'eshop-theme'), $cart_item['quantity']); ?></span>
                                    <span class="cart-item-price"><?php echo $product_price; ?></span>
                                </div>
                            </div>
                            
                            <div class="cart-item-actions">
                                <button class="remove-cart-item" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Remove item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Actions -->
                <div class="cart-panel-actions">
                    <?php
                    // Calculate shipping info
                    $cart_total = WC()->cart->get_cart_contents_total();
                    $free_shipping_threshold = eshop_get_free_shipping_threshold();
                    $remaining_for_free_shipping = max(0, $free_shipping_threshold - $cart_total);
                    ?>

                    <!-- Shipping Information -->
                    <?php if ($remaining_for_free_shipping > 0) : ?>
                        <div class="shipping-info">
                            <div class="shipping-message">
                                <i class="fas fa-truck shipping-icon"></i>
                                <span class="shipping-text">
                                    <?php echo sprintf(__('Add %s more for FREE shipping!', 'eshop-theme'), wc_price($remaining_for_free_shipping)); ?>
                                </span>
                            </div>
                            <div class="shipping-progress">
                                <div class="shipping-progress-bar">
                                    <div class="shipping-progress-fill" style="width: <?php echo min(100, ($cart_total / $free_shipping_threshold) * 100); ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="shipping-info free-shipping-achieved">
                            <div class="shipping-message">
                                <i class="fas fa-check-circle shipping-icon"></i>
                                <span class="shipping-text"><?php _e('Congratulations! You qualify for FREE shipping!', 'eshop-theme'); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="cart-total-summary">
                        <div class="cart-subtotal">
                            <span class="subtotal-label"><?php _e('Subtotal:', 'eshop-theme'); ?></span>
                            <span class="subtotal-amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                        </div>
                    </div>
                    
                    <div class="cart-action-buttons">
                        <a href="<?php echo wc_get_cart_url(); ?>" class="view-cart-btn">
                            <?php _e('View Cart', 'eshop-theme'); ?>
                        </a>
                        <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-btn">
                            <?php _e('Checkout', 'eshop-theme'); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cart Animation Overlay -->
    <div class="cart-animation-overlay"></div>
</div>
