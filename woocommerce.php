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
    
    // Debug output
    echo '<!-- SINGLE PRODUCT TEMPLATE LOADING -->';
    
    ?>
    <div class="product-main-container grid-boundary">
        <?php while (have_posts()) : the_post(); ?>
            <?php global $product; ?>
            
            <?php echo '<!-- LEFT COLUMN START -->'; ?>
            <!-- Left Column: Product Image Gallery -->
            <div class="product-gallery-column">
                <?php 
                echo '<!-- Loading product-gallery component -->';
                get_template_part('template-parts/components/product-gallery'); 
                echo '<!-- product-gallery component loaded -->';
                ?>
            </div>
            <?php echo '<!-- LEFT COLUMN END -->'; ?>
            
            <?php echo '<!-- RIGHT COLUMN START -->'; ?>
            <!-- Right Column: Product Details & Actions -->
            <div class="product-details-column">
                <div class="product-details-wrapper">
                    <?php 
                    echo '<!-- Loading breadcrumbs -->';
                    get_template_part('template-parts/components/breadcrumbs'); 
                    echo '<!-- breadcrumbs loaded -->';
                    ?>
                    
                    <!-- Product Header: Title, Rating, Price -->
                    <div class="product-header">
                        <?php
                        echo '<!-- Loading title/rating/price -->';
                        woocommerce_template_single_title();
                        woocommerce_template_single_rating();
                        woocommerce_template_single_price();
                        woocommerce_template_single_excerpt();
                        echo '<!-- title/rating/price loaded -->';
                        ?>
                    </div>
                    
                    <!-- Product Actions (Variations, Add to Cart + Wishlist) -->
                    <div class="product-actions">
                        <?php 
                        echo '<!-- Loading add to cart -->';
                        
                        // Use output buffering to catch any errors
                        ob_start();
                        woocommerce_template_single_add_to_cart();
                        $add_to_cart_output = ob_get_clean();
                        
                        if (!empty($add_to_cart_output)) {
                            echo $add_to_cart_output;
                        } else {
                            echo '<p style="color: #999;">Add to cart form could not be loaded.</p>';
                        }
                        
                        echo '<!-- add to cart loaded -->';
                        ?>
                    </div>
                    
                    <!-- Trust Badges -->
                    <?php 
                    echo '<!-- Loading trust badges -->';
                    get_template_part('template-parts/components/trust-badges'); 
                    echo '<!-- trust badges loaded -->';
                    ?>
                    
                    <!-- Product Accordions -->
                    <?php 
                    echo '<!-- Loading accordions -->';
                    get_template_part('template-parts/components/product-accordions'); 
                    echo '<!-- accordions loaded -->';
                    ?>
                    
                    <!-- Product Meta -->
                    <div class="product-meta">
                        <?php 
                        echo '<!-- Loading meta -->';
                        woocommerce_template_single_meta(); 
                        echo '<!-- meta loaded -->';
                        ?>
                    </div>
                </div>
            </div>
            <?php echo '<!-- RIGHT COLUMN END -->'; ?>
        <?php endwhile; ?>
    </div>
    <?php echo '<!-- PRODUCT MAIN CONTAINER END -->'; ?>

    <?php
    echo '<!-- AFTER SINGLE PRODUCT SUMMARY HOOKS START -->';
    do_action('woocommerce_after_single_product_summary');
    echo '<!-- AFTER SINGLE PRODUCT SUMMARY HOOKS END -->';
    ?>
    
    <!-- Sticky Add to Cart -->
    <?php 
    echo '<!-- Loading sticky ATC -->';
    get_template_part('template-parts/components/sticky-atc'); 
    echo '<!-- sticky ATC loaded -->';
    ?>
    
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