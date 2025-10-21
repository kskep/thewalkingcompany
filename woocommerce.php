<?php
/**
 * WooCommerce Template
 *
 * This template is used for all WooCommerce pages.
 * Note: When this file exists, it takes priority over archive-product.php.
 * To allow a custom archive layout, we delegate product archives explicitly
 * to our override template and return early.
 */

// If on Shop or Product Taxonomy archive, delegate to our magazine archive template
if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
    // Load the theme's Woo archive template which includes its own header/footer
    wc_get_template('archive-product.php');
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