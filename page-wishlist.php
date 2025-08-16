<?php
/**
 * Template Name: Wishlist Page
 * 
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        
        <header class="page-header mb-8">
            <h1 class="text-3xl font-bold text-dark mb-2"><?php _e('My Wishlist', 'eshop-theme'); ?></h1>
            <p class="text-gray-600"><?php _e('Keep track of your favorite products', 'eshop-theme'); ?></p>
        </header>

        <div class="wishlist-content">
            <?php
            $wishlist_products = eshop_get_wishlist_products();
            
            if (!empty($wishlist_products)) :
            ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($wishlist_products as $product_id) :
                        $product = wc_get_product($product_id);
                        if ($product) :
                    ?>
                        <div class="wishlist-product-card bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200">
                            <div class="relative">
                                <a href="<?php echo get_permalink($product_id); ?>">
                                    <?php echo $product->get_image('medium', array('class' => 'w-full h-48 object-cover')); ?>
                                </a>
                                <button class="remove-from-wishlist absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center text-gray-400 hover:text-red-500 transition-colors" data-product-id="<?php echo $product_id; ?>">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
                            
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-dark mb-2">
                                    <a href="<?php echo get_permalink($product_id); ?>" class="hover:text-primary transition-colors">
                                        <?php echo $product->get_name(); ?>
                                    </a>
                                </h3>
                                
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xl font-bold text-primary"><?php echo $product->get_price_html(); ?></span>
                                    <?php if ($product->is_on_sale()) : ?>
                                        <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full"><?php _e('Sale', 'eshop-theme'); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($product->is_in_stock()) : ?>
                                    <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
                                        <?php do_action('woocommerce_before_add_to_cart_button'); ?>
                                        
                                        <?php if ($product->is_type('simple')) : ?>
                                            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="w-full bg-primary text-white py-2 px-4 rounded-md hover:bg-primary-dark transition-colors duration-200 single_add_to_cart_button button alt">
                                                <?php echo esc_html($product->single_add_to_cart_text()); ?>
                                            </button>
                                        <?php else : ?>
                                            <a href="<?php echo get_permalink($product_id); ?>" class="block w-full text-center bg-primary text-white py-2 px-4 rounded-md hover:bg-primary-dark transition-colors duration-200">
                                                <?php _e('Select Options', 'eshop-theme'); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
                                    </form>
                                <?php else : ?>
                                    <button class="w-full bg-gray-300 text-gray-500 py-2 px-4 rounded-md cursor-not-allowed" disabled>
                                        <?php _e('Out of Stock', 'eshop-theme'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
                
                <div class="mt-8 text-center">
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="inline-flex items-center bg-gray-100 text-dark px-6 py-3 rounded-md hover:bg-gray-200 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <?php _e('Continue Shopping', 'eshop-theme'); ?>
                    </a>
                </div>
                
            <?php else : ?>
                <div class="text-center py-12">
                    <div class="mb-6">
                        <i class="far fa-heart text-6xl text-gray-300"></i>
                    </div>
                    <h2 class="text-2xl font-semibold text-dark mb-4"><?php _e('Your wishlist is empty', 'eshop-theme'); ?></h2>
                    <p class="text-gray-600 mb-6"><?php _e('Start adding products you love to your wishlist', 'eshop-theme'); ?></p>
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="inline-flex items-center bg-primary text-white px-6 py-3 rounded-md hover:bg-primary-dark transition-colors duration-200">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        <?php _e('Start Shopping', 'eshop-theme'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>