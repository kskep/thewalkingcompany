<?php
/**
 * WooCommerce Template
 *
 * This template is used for all WooCommerce pages.
 * Note: When this file exists, it takes priority over archive-product.php and single-product.php.
 * To allow custom layouts, we delegate specific page types to their override templates.
 */

// If on Shop or Product Taxonomy archive, delegate to our magazine archive template
if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
    // Load the theme's Woo archive template which includes its own header/footer
    wc_get_template('archive-product.php');
    return;
}

// If on Single Product page, use the default single product content
if (function_exists('is_product') && is_product()) {
    get_header('shop');
    
    ?>
    <div class="product-main-container grid-boundary">
        <?php while (have_posts()) : the_post(); ?>
            <?php global $product; ?>
            
            <!-- Left Column: Product Image Gallery -->
            <div class="product-gallery-column">
                <?php get_template_part('template-parts/components/product-gallery'); ?>
            </div>
            
            <!-- Right Column: Product Details & Actions -->
            <div class="product-details-column">
                <div class="product-details-wrapper">
                    <?php get_template_part('template-parts/components/breadcrumbs'); ?>
                    
                    <!-- Product Header: Title, Rating, Price -->
                    <div class="product-header">
                        <?php
                        woocommerce_template_single_title();
                        woocommerce_template_single_rating();
                        woocommerce_template_single_price();
                        ?>
                    </div>
                    
                    <!-- Product Actions (Variations, Add to Cart + Wishlist) -->
                    <div class="product-actions">
                        <?php woocommerce_template_single_add_to_cart(); ?>
                    </div>
                    
                    <!-- Trust Badges -->
                    <?php get_template_part('template-parts/components/trust-badges'); ?>
                    
                    <!-- Product Accordions -->
                    <?php get_template_part('template-parts/components/product-accordions'); ?>
                    
                    <!-- Product Meta -->
                    <div class="product-meta">
                        <?php woocommerce_template_single_meta(); ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
    <!-- Sticky Add to Cart -->
    <?php get_template_part('template-parts/components/sticky-atc'); ?>
    
    <?php
    get_footer('shop');
    return;
}

get_header(); ?>

<main class="main-content woocommerce-page">
    <div class="mx-auto px-16 py-8">
        
        <!-- Breadcrumbs -->
        <?php if (function_exists('woocommerce_breadcrumb')) : ?>
            <div class="woocommerce-breadcrumb mb-6">
                <?php woocommerce_breadcrumb(array(
                    'delimiter' => ' <i class="fas fa-chevron-right text-gray-400 mx-2"></i> ',
                    'wrap_before' => '<nav class="breadcrumb text-sm text-gray-600">',
                    'wrap_after' => '</nav>',
                    'before' => '<span>',
                    'after' => '</span>',
                    'home' => '<i class="fas fa-home mr-1"></i>Home',
                )); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-4">
                <?php woocommerce_content(); ?>
            </div>
            
        </div>
    </div>
</main>

<?php get_footer(); ?>