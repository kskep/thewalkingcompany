<?php
/**
 * WooCommerce Template
 * 
 * This template is used for all WooCommerce pages
 */

get_header(); ?>

<main class="main-content woocommerce-page">
    <div class="container mx-auto px-4 py-8">
        
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