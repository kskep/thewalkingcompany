<?php
/**
 * Sticky Add To Cart (mobile)
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

if (!is_product()) { return; }

global $product;
if (!$product) { return; }

$price_html = $product->get_price_html();
?>
<div class="sticky-atc" aria-live="polite">
    <div class="sticky-atc__content">
        <div class="sticky-atc__info">
            <div class="sticky-atc__title"><?php echo esc_html( get_the_title() ); ?></div>
            <div class="sticky-atc__meta"><span class="sticky-atc__selection"><?php esc_html_e('Select options', 'eshop-theme'); ?></span> · <span class="sticky-atc__price"><?php echo wp_kses_post($price_html); ?></span></div>
        </div>
        <button class="sticky-atc__button" type="button"><?php esc_html_e('Add to Cart', 'eshop-theme'); ?></button>
    </div>
</div>
