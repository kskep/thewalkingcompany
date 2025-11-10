<?php
defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
    return;
}

$product_id = $product->get_id();
$is_on_sale = $product->is_on_sale();
?>
<article <?php wc_product_class( 'product-card', $product ); ?>>
    <div class="product-card__media">
        <a href="<?php echo esc_url( get_permalink() ); ?>">
            <?php echo woocommerce_get_product_thumbnail(); ?>
        </a>
        <div class="badge-stack">
            <?php if ($is_on_sale) : ?>
                <span class="badge badge--sale">Sale</span>
            <?php endif; ?>
            <!-- Other badges like "New" can be added here -->
        </div>
        <button class="wishlist-button" type="button" aria-label="Add to wishlist">♡</button>
        <!-- Image indicator dots can be added for galleries -->
        <div class="size-overlay" aria-hidden="true">
            <!-- Size chips can be dynamically generated here -->
        </div>
    </div>
    <div class="product-card__info">
        <h2 class="product-card__title">
            <a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
        </h2>
        <span class="product-card__sku"><?php echo ( $sku = $product->get_sku() ) ? 'SKU: ' . esc_html( $sku ) : ''; ?></span>
        <div class="category-line">
            <?php echo wc_get_product_category_list( $product_id, ', ', '<span class="posted_in">', '</span>' ); ?>
        </div>
        <div class="color-row">
            <!-- Color swatches can be added here -->
        </div>
        <div class="price-row">
            <?php echo $product->get_price_html(); ?>
            <?php if ($product->is_in_stock() && $product->get_stock_quantity() < 5 && $product->get_stock_quantity() > 0) : ?>
                <span class="stock-flag">Low stock</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-footer">
        <?php woocommerce_template_loop_add_to_cart(); ?>
    </div>
</article>