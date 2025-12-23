<?php
/**
 * Checkout Form
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

    <div class="checkout-layout grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Checkout Fields -->
        <div class="checkout-fields-section">
            
            <?php if ($checkout->get_checkout_fields()) : ?>
                
                <!-- Billing Details -->
                <div class="checkout-section mb-8">
                    <h2 class="text-xl font-bold text-dark mb-4 pb-2 border-b border-gray-200"><?php _e('Billing Details', 'eshop-theme'); ?></h2>
                    
                    <?php do_action('woocommerce_checkout_before_customer_details'); ?>
                    
                    <div class="woocommerce-billing-fields">
                        <?php do_action('woocommerce_checkout_billing'); ?>
                    </div>
                </div>

                <!-- Shipping Details -->
                <?php if (WC()->cart->needs_shipping_address()) : ?>
                    <div class="checkout-section mb-8">
                        <h2 class="text-xl font-bold text-dark mb-4 pb-2 border-b border-gray-200"><?php _e('Shipping Details', 'eshop-theme'); ?></h2>
                        
                        <div class="woocommerce-shipping-fields">
                            <?php if (WC()->cart->needs_shipping_address()) : ?>
                                <div class="shipping-address-toggle mb-4">
                                    <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox inline-flex items-center">
                                        <input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox mr-2" <?php checked(apply_filters('woocommerce_ship_to_different_address_checked', 'shipping' === get_option('woocommerce_ship_to_destination') ? 1 : 0), 1); ?> type="checkbox" name="ship_to_different_address" value="1" />
                                        <span class="text-sm text-gray-700"><?php esc_html_e('Ship to a different address?', 'woocommerce'); ?></span>
                                    </label>
                                </div>
                            <?php endif; ?>

                            <div class="shipping_address">
                                <?php do_action('woocommerce_checkout_shipping'); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Additional Information -->
                <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
                
                <?php if (apply_filters('woocommerce_enable_order_notes_field', 'yes' === get_option('woocommerce_enable_order_comments', 'yes'))) : ?>
                    <div class="checkout-section mb-8">
                        <h2 class="text-xl font-bold text-dark mb-4 pb-2 border-b border-gray-200"><?php _e('Additional Information', 'eshop-theme'); ?></h2>
                        
                        <div class="woocommerce-additional-fields">
                            <?php do_action('woocommerce_checkout_before_order_review'); ?>
                            
                            <?php foreach ($checkout->get_checkout_fields('order') as $key => $field) : ?>
                                <?php woocommerce_form_field($key, $field, $checkout->get_value($key)); ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php do_action('woocommerce_checkout_after_customer_details'); ?>

            <?php endif; ?>
        </div>

        <!-- Order Review -->
        <div class="checkout-review-section">
            <div class="order-review-wrapper bg-gray-50 p-6 sticky top-4">
                
                <h2 class="text-xl font-bold text-dark mb-4 pb-2 border-b border-gray-200" id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h2>
                
                <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
                
                <div id="order_review" class="woocommerce-checkout-review-order">
                    <!-- Order Items -->
                    <div class="order-items mb-6">
                        <?php
                        do_action('woocommerce_review_order_before_cart_contents');

                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                        ?>
                                <div class="order-item flex items-center justify-between py-3 border-b border-gray-200 last:border-b-0 <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                                    <div class="item-details flex items-center space-x-3 flex-1">
                                        <div class="item-image w-12 h-12 flex-shrink-0">
                                            <?php echo apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(array(48, 48)), $cart_item, $cart_item_key); ?>
                                        </div>
                                        <div class="item-info flex-1 min-w-0">
                                            <h4 class="text-sm font-medium text-dark truncate">
                                                <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;'; ?>
                                            </h4>
                                            <div class="text-xs text-gray-500">
                                                <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); ?>
                                                <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-total text-sm font-semibold text-primary">
                                        <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                    </div>
                                </div>
                        <?php
                            }
                        }

                        do_action('woocommerce_review_order_after_cart_contents');
                        ?>
                    </div>

                    <!-- Order Totals -->
                    <div class="order-totals">
                        
                        <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                            <div class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?> flex justify-between items-center py-2 text-sm">
                                <span class="text-gray-600"><?php wc_cart_totals_coupon_label($coupon); ?></span>
                                <span class="font-semibold"><?php wc_cart_totals_coupon_html($coupon); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <div class="cart-subtotal flex justify-between items-center py-2 text-sm border-b border-gray-200">
                            <span class="text-gray-600"><?php esc_html_e('Subtotal', 'woocommerce'); ?></span>
                            <span class="font-semibold"><?php wc_cart_totals_subtotal_html(); ?></span>
                        </div>

                        <?php foreach (WC()->cart->get_fees() as $fee) : ?>
                            <div class="fee flex justify-between items-center py-2 text-sm border-b border-gray-200">
                                <span class="text-gray-600"><?php echo esc_html($fee->name); ?></span>
                                <span class="font-semibold"><?php wc_cart_totals_fee_html($fee); ?></span>
                            </div>
                        <?php endforeach; ?>

                        <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
                            <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                                <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
                                    <div class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?> flex justify-between items-center py-2 text-sm border-b border-gray-200">
                                        <span class="text-gray-600"><?php echo esc_html($tax->label); ?></span>
                                        <span class="font-semibold"><?php echo wp_kses_post($tax->formatted_amount); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <div class="tax-total flex justify-between items-center py-2 text-sm border-b border-gray-200">
                                    <span class="text-gray-600"><?php echo esc_html(WC()->countries->tax_or_vat()); ?></span>
                                    <span class="font-semibold"><?php wc_cart_totals_taxes_total_html(); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                            <?php do_action('woocommerce_review_order_before_shipping'); ?>
                            <?php wc_cart_totals_shipping_html(); ?>
                            <?php do_action('woocommerce_review_order_after_shipping'); ?>
                        <?php endif; ?>

                        <div class="order-total flex justify-between items-center py-4 border-t-2 border-gray-300 mt-4">
                            <span class="text-lg font-bold text-dark"><?php esc_html_e('Total', 'woocommerce'); ?></span>
                            <span class="text-xl font-bold text-primary"><?php wc_cart_totals_order_total_html(); ?></span>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="payment-methods mt-6">
                        <?php if (WC()->cart->needs_payment()) : ?>
                            <div class="woocommerce-checkout-payment" id="payment">
                                <?php if (WC()->cart->needs_payment()) : ?>
                                    <h3 class="text-lg font-semibold text-dark mb-4"><?php esc_html_e('Payment Method', 'woocommerce'); ?></h3>
                                    <ul class="wc_payment_methods payment_methods methods space-y-3">
                                        <?php
                                        if (!empty($available_gateways)) {
                                            foreach ($available_gateways as $gateway) {
                                                wc_get_template('checkout/payment-method.php', array('gateway' => $gateway), '', WC()->plugin_path() . '/templates/');
                                            }
                                        } else {
                                            echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters('woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__('Sorry, it seems that there are no available payment methods for your state. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce') : esc_html__('Please fill in your details above to see available payment methods.', 'woocommerce')) . '</li>';
                                        }
                                        ?>
                                    </ul>
                                <?php endif; ?>
                                
                                <div class="form-row place-order mt-6">
                                    <noscript>
                                        <?php esc_html_e('Since your browser does not support JavaScript, or it is disabled, please ensure you click the <em>Update Totals</em> button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce'); ?>
                                        <br/><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e('Update totals', 'woocommerce'); ?>"><?php esc_html_e('Update totals', 'woocommerce'); ?></button>
                                    </noscript>

                                    <?php wc_get_template('checkout/terms.php'); ?>

                                    <?php do_action('woocommerce_review_order_before_submit'); ?>

                                    <button type="submit" class="button alt w-full bg-primary text-white py-4 text-lg font-semibold hover:bg-primary-dark transition-colors" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e('Place order', 'woocommerce'); ?>" data-value="<?php esc_attr_e('Place order', 'woocommerce'); ?>"><?php esc_html_e('Place Order', 'woocommerce'); ?></button>

                                    <?php do_action('woocommerce_review_order_after_submit'); ?>

                                    <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
