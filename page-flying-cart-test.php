<?php
/**
 * Template Name: Flying Cart Test Page
 * 
 * A test page to verify flying cart functionality in WordPress
 * 
 * @package E-Shop Theme
 */

get_header(); ?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        
        <h1 class="text-3xl font-bold text-center mb-8">Flying Cart Test Page</h1>
        
        <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
            <h2 class="text-xl font-semibold mb-4">WordPress Integration Status</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full <?php echo class_exists('WooCommerce') ? 'bg-green-500' : 'bg-red-500'; ?> mr-3"></span>
                        <span>WooCommerce: <?php echo class_exists('WooCommerce') ? 'Active' : 'Inactive'; ?></span>
                    </div>
                    
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full <?php echo file_exists(get_template_directory() . '/template-parts/components/flying-cart.php') ? 'bg-green-500' : 'bg-red-500'; ?> mr-3"></span>
                        <span>Flying Cart Template: <?php echo file_exists(get_template_directory() . '/template-parts/components/flying-cart.php') ? 'Found' : 'Missing'; ?></span>
                    </div>
                    
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full <?php echo file_exists(get_template_directory() . '/css/components/flying-cart.css') ? 'bg-green-500' : 'bg-red-500'; ?> mr-3"></span>
                        <span>Flying Cart CSS: <?php echo file_exists(get_template_directory() . '/css/components/flying-cart.css') ? 'Found' : 'Missing'; ?></span>
                    </div>
                    
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full <?php echo file_exists(get_template_directory() . '/js/components/flying-cart.js') ? 'bg-green-500' : 'bg-red-500'; ?> mr-3"></span>
                        <span>Flying Cart JS: <?php echo file_exists(get_template_directory() . '/js/components/flying-cart.js') ? 'Found' : 'Missing'; ?></span>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center">
                        <span class="w-4 h-4 rounded-full <?php echo function_exists('eshop_get_free_shipping_threshold') ? 'bg-green-500' : 'bg-red-500'; ?> mr-3"></span>
                        <span>Helper Functions: <?php echo function_exists('eshop_get_free_shipping_threshold') ? 'Loaded' : 'Missing'; ?></span>
                    </div>
                    
                    <?php if (class_exists('WooCommerce')) : ?>
                        <div class="flex items-center">
                            <span class="w-4 h-4 rounded-full bg-blue-500 mr-3"></span>
                            <span>Cart Items: <?php echo WC()->cart->get_cart_contents_count(); ?></span>
                        </div>
                        
                        <div class="flex items-center">
                            <span class="w-4 h-4 rounded-full bg-blue-500 mr-3"></span>
                            <span>Cart Total: <?php echo WC()->cart->get_cart_total(); ?></span>
                        </div>
                        
                        <div class="flex items-center">
                            <span class="w-4 h-4 rounded-full bg-blue-500 mr-3"></span>
                            <span>Free Shipping Threshold: <?php echo function_exists('eshop_get_free_shipping_threshold') ? wc_price(eshop_get_free_shipping_threshold()) : 'N/A'; ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php if (class_exists('WooCommerce')) : ?>
            <div class="bg-white p-6 rounded-lg shadow-sm mb-8">
                <h2 class="text-xl font-semibold mb-4">Test Products</h2>
                <p class="text-gray-600 mb-4">Add these test products to your cart to test the flying cart functionality:</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php
                    // Get some products for testing
                    $products = wc_get_products(array(
                        'limit' => 3,
                        'status' => 'publish'
                    ));
                    
                    if ($products) :
                        foreach ($products as $product) : ?>
                            <div class="border border-gray-200 p-4">
                                <div class="mb-3">
                                    <?php echo $product->get_image('medium'); ?>
                                </div>
                                <h3 class="font-semibold mb-2"><?php echo $product->get_name(); ?></h3>
                                <p class="text-lg font-bold text-pink-600 mb-3"><?php echo $product->get_price_html(); ?></p>
                                
                                <?php if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) : ?>
                                    <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
                                        <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="single_add_to_cart_button button alt bg-pink-600 text-white px-4 py-2 w-full hover:bg-pink-700 transition-colors">
                                            <?php echo esc_html($product->single_add_to_cart_text()); ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach;
                    else : ?>
                        <div class="col-span-3 text-center py-8">
                            <p class="text-gray-500">No products found. Please add some products to test the flying cart.</p>
                            <a href="<?php echo admin_url('post-new.php?post_type=product'); ?>" class="inline-block mt-4 bg-pink-600 text-white px-4 py-2 hover:bg-pink-700 transition-colors">
                                Add Products
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <h2 class="text-xl font-semibold mb-4">Flying Cart Features</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold mb-2">Visual Features</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                        <li>Solid color background (no gradients)</li>
                        <li>Larger shopping cart icon (24px)</li>
                        <li>Properly sized counter badge</li>
                        <li>Sharp edges for edgy design</li>
                        <li>Responsive design</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-2">Functional Features</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                        <li>Real-time cart updates</li>
                        <li>Free shipping progress bar</li>
                        <li>AJAX item removal</li>
                        <li>Auto-hide functionality</li>
                        <li>Keyboard accessibility</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-center">
            <p class="text-gray-600">
                The flying cart should appear in the bottom-right corner of your screen.
                <br>
                Try adding products to cart to test the functionality!
            </p>
        </div>
        
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Test flying cart functionality
    console.log('Flying Cart Test Page Loaded');
    
    // Check if flying cart exists
    if ($('#flying-cart').length) {
        console.log('✅ Flying cart element found');
    } else {
        console.log('❌ Flying cart element not found');
    }
    
    // Check if AJAX variables are available
    if (typeof eshop_ajax !== 'undefined') {
        console.log('✅ AJAX variables loaded:', eshop_ajax);
    } else {
        console.log('❌ AJAX variables not loaded');
    }
    
    // Check if flying cart instance is available
    if (typeof window.FlyingCartInstance !== 'undefined') {
        console.log('✅ Flying cart instance available');
    } else {
        console.log('❌ Flying cart instance not available');
    }
});
</script>

<?php get_footer(); ?>
