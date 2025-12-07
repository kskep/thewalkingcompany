<?php
/**
 * Minicart Content Template Part
 * Used in header actions and AJAX fragments
 * 
 * @package E-Shop Theme
 */
if (!defined('ABSPATH')) {
    exit;
}

$cart_count = WC()->cart->get_cart_contents_count();
?>
<div class="eshop-minicart-inner p-6 bg-white text-left h-full flex flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 flex-shrink-0">
        <h3 class="text-xl font-bold text-gray-900 tracking-tight flex items-center">
            <?php _e('Shopping Cart', 'eshop-theme'); ?>
            <span class="ml-2 bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-0.5 rounded-full"><?php echo $cart_count; ?></span>
        </h3>
        <button class="minicart-close lg:hidden text-gray-400 hover:text-gray-900 transition-colors">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Items -->
    <div class="minicart-items flex-1 overflow-y-auto pr-2 -mr-2 min-h-[150px] custom-scrollbar">
        <?php if ($cart_count > 0) : ?>
            <div class="space-y-6">
                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                    $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                    
                    if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                        $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key);
                        ?>
                        <div class="minicart-item grid grid-cols-[80px_1fr_auto] gap-4 group" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                            <!-- Image -->
                            <div class="relative w-20 h-24 overflow-hidden rounded-md bg-gray-50 border border-gray-100">
                               <?php
                                $thumbnail = $_product->get_image('woocommerce_gallery_thumbnail', array('class' => 'w-full h-full object-cover object-center transform group-hover:scale-105 transition-transform duration-500'));
                                if (!$product_permalink) {
                                    echo $thumbnail;
                                } else {
                                    printf('<a href="%s" class="block h-full">%s</a>', esc_url($product_permalink), $thumbnail);
                                }
                               ?>
                            </div>

                            <!-- Details -->
                            <div class="flex flex-col justify-between py-0.5">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900 leading-snug line-clamp-2 mb-1">
                                        <?php if (!$product_permalink) : ?>
                                            <?php echo $_product->get_name(); ?>
                                        <?php else : ?>
                                            <a href="<?php echo esc_url($product_permalink); ?>" class="hover:text-primary transition-colors">
                                                <?php echo $_product->get_name(); ?>
                                            </a>
                                        <?php endif; ?>
                                    </h4>
                                    <!-- Attributes (Variation) -->
                                    <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                                    
                                    <!-- Price Single -->
                                    <div class="text-xs text-gray-500 font-medium">
                                        <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                                    </div>
                                </div>

                                <!-- Qty Controls -->
                                <div class="minicart-qty-controls flex items-center mt-2 bg-gray-50 border border-gray-200 rounded-lg w-max h-8">
                                    <button type="button" class="minicart-qty-btn qty-minus w-8 h-full flex items-center justify-center text-gray-500 hover:text-primary hover:bg-white rounded-l-lg transition-all focus:outline-none border-r border-transparent hover:border-gray-200" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Decrease quantity">
                                        <i class="fas fa-minus text-[0.6rem]"></i>
                                    </button>
                                    <span class="qty-value w-8 text-center text-xs font-semibold text-gray-900"><?php echo $cart_item['quantity']; ?></span>
                                    <button type="button" class="minicart-qty-btn qty-plus w-8 h-full flex items-center justify-center text-gray-500 hover:text-primary hover:bg-white rounded-r-lg transition-all focus:outline-none border-l border-transparent hover:border-gray-200" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Increase quantity">
                                        <i class="fas fa-plus text-[0.6rem]"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Remove -->
                            <div class="py-0.5">
                                <button class="remove-from-cart text-gray-300 hover:text-red-500 transition-colors p-1.5 rounded-full hover:bg-red-50" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Remove item">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                endforeach; ?>
            </div>
        <?php else : ?>
            <div class="flex flex-col items-center justify-center py-16 text-center h-full">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-6 text-gray-300">
                    <i class="fas fa-shopping-bag text-3xl opacity-50"></i>
                </div>
                <p class="text-gray-900 font-semibold text-lg mb-2"><?php _e('Your cart is empty', 'eshop-theme'); ?></p>
                <p class="text-gray-500 text-sm max-w-[200px] mx-auto"><?php _e('Looks like you haven\'t added any items yet.', 'eshop-theme'); ?></p>
                <div class="mt-8">
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark transition-colors duration-200 shadow-sm hover:shadow-md">
                        <?php _e('Start Shopping', 'eshop-theme'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer Actions -->
    <?php if ($cart_count > 0) : ?>
        <div class="mt-6 pt-6 border-t border-gray-100 space-y-4 flex-shrink-0 bg-white z-10">
             <div class="flex justify-between items-end mb-2">
                <span class="text-sm text-gray-500 font-medium"><?php _e('Subtotal', 'eshop-theme'); ?></span>
                <div class="text-right">
                    <span class="block text-2xl font-bold text-gray-900 leading-none"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                    <span class="text-xs text-gray-400 mt-1 block"><?php _e('Shipping & taxes calculated at checkout', 'eshop-theme'); ?></span>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3">
                <a href="<?php echo wc_get_cart_url(); ?>" class="flex items-center justify-center w-full bg-white border border-gray-300 text-gray-700 font-semibold py-3 px-4 rounded-md hover:border-gray-900 hover:text-gray-900 transition-colors duration-200 text-sm shadow-sm">
                    <?php _e('View Cart', 'eshop-theme'); ?>
                </a>
                <a href="<?php echo wc_get_checkout_url(); ?>" class="flex items-center justify-center w-full bg-primary text-white font-semibold py-3 px-4 rounded-md hover:bg-primary-dark transition-all duration-200 shadow-md hover:shadow-lg shadow-primary/30 text-sm">
                    <?php _e('Checkout', 'eshop-theme'); ?> <i class="fas fa-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
