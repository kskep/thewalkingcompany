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

// If on Single Product page, delegate to our custom single product template
if (function_exists('is_product') && is_product()) {
    // Load the theme's single product template which includes its own header/footer
    wc_get_template('single-product.php');
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