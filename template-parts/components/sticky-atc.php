<?php
/**
 * Sticky Add To Cart - Magazine Style
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

if (!is_product()) { return; }

global $product;
if (!$product) { return; }

$price_html = $product->get_price_html();
?>
<div class="sticky-atc" id="sticky-atc-bar" aria-live="polite">
    <div class="sticky-atc__content">
        <div>
            <div class="sticky-atc__title"><?php echo esc_html( get_the_title() ); ?></div>
            <div class="sticky-atc__meta">
                <?php echo wp_kses_post($price_html); ?>
                <!-- Span to show selection -->
                <span class="sticky-atc__selection"></span>
            </div>
        </div>
        <button class="sticky-atc__button"><?php esc_html_e('Add to Cart', 'eshop-theme'); ?></button>
    </div>
</div>
