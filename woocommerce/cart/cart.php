<?php
/**
 * Cart Page
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<div class="woocommerce-cart-form__contents-wrapper">
    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <?php do_action('woocommerce_before_cart_table'); ?>

        <div class="cart-layout grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Cart Items -->
            <div class="cart-items-section lg:col-span-2">
                <div class="cart-header flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-dark"><?php _e('Shopping Cart', 'eshop-theme'); ?></h2>
                    <span class="text-sm text-gray-500"><?php echo sprintf(_n('%d item', '%d items', WC()->cart->get_cart_contents_count(), 'eshop-theme'), WC()->cart->get_cart_contents_count()); ?></span>
                </div>

                <?php if (!WC()->cart->is_empty()) : ?>
                    <div class="cart-items space-y-4">
                        <?php
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                                $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                        ?>
                                <div class="cart-item bg-white border border-gray-100 p-6 <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                                    <div class="flex items-start space-x-4">
                                        
                                        <!-- Product Image -->
                                        <div class="product-thumbnail flex-shrink-0">
                                            <?php
                                            $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(array(120, 120)), $cart_item, $cart_item_key);
                                            if (!$product_permalink) {
                                                echo $thumbnail;
                                            } else {
                                                printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail);
                                            }
                                            ?>
                                        </div>

                                        <!-- Product Details -->
                                        <div class="product-details flex-1 min-w-0">
                                            <div class="product-name mb-2">
                                                <?php
                                                if (!$product_permalink) {
                                                    echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key) . '&nbsp;');
                                                } else {
                                                    echo wp_kses_post(apply_filters('woocommerce_cart_item_name', sprintf('<a href="%s" class="text-lg font-semibold text-dark hover:text-primary transition-colors">%s</a>', esc_url($product_permalink), $_product->get_name()), $cart_item, $cart_item_key));
                                                }

                                                do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

                                                // Meta data.
                                                echo wc_get_formatted_cart_item_data($cart_item);

                                                // Backorder notification.
                                                if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                                    echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
                                                }
                                                ?>
                                            </div>

                                            <div class="product-price-mobile lg:hidden mb-3">
                                                <span class="text-lg font-bold text-primary">
                                                    <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                                                </span>
                                            </div>

                                            <!-- Quantity and Actions -->
                                            <div class="flex items-center justify-between">
                                                <div class="quantity-wrapper">
                                                    <?php
                                                    if ($_product->is_sold_individually()) {
                                                        $product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
                                                    } else {
                                                        $product_quantity = woocommerce_quantity_input(
                                                            array(
                                                                'input_name'   => "cart[{$cart_item_key}][qty]",
                                                                'input_value'  => $cart_item['quantity'],
                                                                'max_value'    => $_product->get_max_purchase_quantity(),
                                                                'min_value'    => '0',
                                                                'product_name' => $_product->get_name(),
                                                            ),
                                                            $_product,
                                                            false
                                                        );
                                                    }
                                                    echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item);
                                                    ?>
                                                </div>

                                                <div class="item-actions">
                                                    <?php
                                                    echo apply_filters(
                                                        'woocommerce_cart_item_remove_link',
                                                        sprintf(
                                                            '<a href="%s" class="remove text-gray-400 hover:text-red-500 transition-colors p-2" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="fas fa-trash text-sm"></i></a>',
                                                            esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                            esc_html__('Remove this item', 'woocommerce'),
                                                            esc_attr($product_id),
                                                            esc_attr($_product->get_sku())
                                                        ),
                                                        $cart_item_key
                                                    );
                                                    ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Product Price (Desktop) -->
                                        <div class="product-price hidden lg:block text-right">
                                            <div class="text-lg font-bold text-primary mb-2">
                                                <?php echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); ?>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>

                    <div class="cart-actions mt-6 flex flex-col sm:flex-row gap-4">
                        <button type="submit" class="button bg-gray-100 text-dark px-6 py-3 hover:bg-gray-200 transition-colors" name="update_cart" value="<?php esc_attr_e('Update cart', 'woocommerce'); ?>"><?php esc_html_e('Update cart', 'woocommerce'); ?></button>
                        
                        <?php if (wc_coupons_enabled()) : ?>
                            <div class="coupon-wrapper flex-1">
                                <div class="flex">
                                    <input type="text" name="coupon_code" class="input-text flex-1 px-4 py-3 border border-gray-300 focus:outline-none focus:border-primary" id="coupon_code" value="" placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>" />
                                    <button type="submit" class="button bg-primary text-white px-6 py-3 hover:bg-primary-dark transition-colors" name="apply_coupon" value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_html_e('Apply', 'woocommerce'); ?></button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else : ?>
                    <div class="cart-empty text-center py-12">
                        <div class="mb-6">
                            <i class="fas fa-shopping-cart text-6xl text-gray-300"></i>
                        </div>
                        <h3 class="text-2xl font-semibold text-dark mb-4"><?php _e('Your cart is empty', 'eshop-theme'); ?></h3>
                        <p class="text-gray-600 mb-6"><?php _e('Looks like you haven\'t added anything to your cart yet', 'eshop-theme'); ?></p>
                        <a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>" class="button bg-primary text-white px-8 py-3 hover:bg-primary-dark transition-colors inline-flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>
                            <?php esc_html_e('Return to shop', 'woocommerce'); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php do_action('woocommerce_after_cart_table'); ?>
            </div>

            <!-- Cart Totals -->
            <?php if (!WC()->cart->is_empty()) : ?>
                <div class="cart-totals-section">
                    <div class="cart-totals bg-gray-50 p-6 sticky top-4">
                        <h3 class="text-xl font-bold text-dark mb-4"><?php _e('Order Summary', 'eshop-theme'); ?></h3>
                        
                        <?php do_action('woocommerce_before_cart_totals'); ?>
                        
                        <div class="cart_totals <?php echo (WC()->customer->has_calculated_shipping()) ? 'calculated_shipping' : ''; ?>">
                            <?php do_action('woocommerce_before_cart_totals'); ?>

                            <div class="shop_table shop_table_responsive">
                                <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                                    <div class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?> flex justify-between items-center py-2 border-b border-gray-200">
                                        <span class="text-sm text-gray-600"><?php wc_cart_totals_coupon_label($coupon); ?></span>
                                        <span class="text-sm font-semibold"><?php wc_cart_totals_coupon_html($coupon); ?></span>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                                    <?php do_action('woocommerce_cart_totals_before_shipping'); ?>
                                    <?php wc_cart_totals_shipping_html(); ?>
                                    <?php do_action('woocommerce_cart_totals_after_shipping'); ?>
                                <?php elseif (WC()->cart->needs_shipping() && 'yes' === get_option('woocommerce_enable_shipping_calc')) : ?>
                                    <div class="shipping py-2 border-b border-gray-200">
                                        <span class="text-sm text-gray-600"><?php esc_html_e('Shipping', 'woocommerce'); ?></span>
                                        <span class="text-sm"><?php woocommerce_shipping_calculator(); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php foreach (WC()->cart->get_fees() as $fee) : ?>
                                    <div class="fee flex justify-between items-center py-2 border-b border-gray-200">
                                        <span class="text-sm text-gray-600"><?php echo esc_html($fee->name); ?></span>
                                        <span class="text-sm font-semibold"><?php wc_cart_totals_fee_html($fee); ?></span>
                                    </div>
                                <?php endforeach; ?>

                                <?php
                                if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) {
                                    $taxable_address = WC()->customer->get_taxable_address();
                                    $estimated_text  = '';

                                    if (WC()->customer->is_customer_outside_base() && !WC()->customer->has_calculated_shipping()) {
                                        $estimated_text = sprintf(' <small>' . esc_html__('(estimated for %s)', 'woocommerce') . '</small>', WC()->countries->estimated_for_prefix($taxable_address[0]) . WC()->countries->countries[$taxable_address[0]]);
                                    }

                                    if ('itemized' === get_option('woocommerce_tax_total_display')) {
                                        foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
                                            <div class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?> flex justify-between items-center py-2 border-b border-gray-200">
                                                <span class="text-sm text-gray-600"><?php echo esc_html($tax->label) . $estimated_text; ?></span>
                                                <span class="text-sm font-semibold"><?php echo wp_kses_post($tax->formatted_amount); ?></span>
                                            </div>
                                        <?php endforeach;
                                    } else : ?>
                                        <div class="tax-total flex justify-between items-center py-2 border-b border-gray-200">
                                            <span class="text-sm text-gray-600"><?php echo esc_html(WC()->countries->tax_or_vat()) . $estimated_text; ?></span>
                                            <span class="text-sm font-semibold"><?php wc_cart_totals_taxes_total_html(); ?></span>
                                        </div>
                                    <?php endif;
                                }
                                ?>

                                <?php do_action('woocommerce_cart_totals_before_order_total'); ?>

                                <div class="order-total flex justify-between items-center py-4 border-t-2 border-gray-300 mt-4">
                                    <span class="text-lg font-bold text-dark"><?php esc_html_e('Total', 'woocommerce'); ?></span>
                                    <span class="text-xl font-bold text-primary"><?php wc_cart_totals_order_total_html(); ?></span>
                                </div>

                                <?php do_action('woocommerce_cart_totals_after_order_total'); ?>
                            </div>

                            <div class="wc-proceed-to-checkout mt-6">
                                <?php do_action('woocommerce_proceed_to_checkout'); ?>
                            </div>

                            <?php do_action('woocommerce_after_cart_totals'); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
    </form>
</div>

<?php do_action('woocommerce_after_cart'); ?>