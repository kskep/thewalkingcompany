<?php
/**
 * Test page for Size Variants functionality
 * 
 * This is a temporary test page to verify the size variants feature works correctly.
 * You can access this by going to yoursite.com/test-size-variants.php
 * 
 * To use this test:
 * 1. Make sure you have variable products with size attributes
 * 2. The size attribute should be named 'size-selection' or contain 'size'
 * 3. Create some variations with different sizes (36, 37, 38, 39, etc.)
 * 4. Set some variations as out of stock to test the disabled state
 */

// Include WordPress
require_once('../../wp-load.php');

// Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    die('WooCommerce is not active. Please install and activate WooCommerce first.');
}

get_header();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Size Variants Test Page</h1>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Testing Size Variants Function</h2>
        
        <?php
        // Get some variable products to test with
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 5,
            'meta_query' => array(
                array(
                    'key' => '_product_type',
                    'value' => 'variable'
                )
            )
        );
        
        $products = get_posts($args);
        
        if (empty($products)) {
            echo '<p class="text-red-600">No variable products found. Please create some variable products with size attributes to test this functionality.</p>';
        } else {
            echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
            
            foreach ($products as $post) {
                setup_postdata($post);
                $product = wc_get_product($post->ID);
                
                if (!$product || !$product->is_type('variable')) {
                    continue;
                }
                
                // Test our size variants function
                $size_variants = function_exists('eshop_get_product_size_variants') ? eshop_get_product_size_variants($product, 8) : array();
                
                echo '<div class="bg-white border border-gray-200 rounded-lg p-4">';
                echo '<h3 class="font-semibold mb-2">' . esc_html($product->get_name()) . '</h3>';
                echo '<p class="text-sm text-gray-600 mb-4">Product ID: ' . $product->get_id() . '</p>';
                
                if (!empty($size_variants)) {
                    echo '<div class="mb-4">';
                    echo '<h4 class="text-sm font-medium mb-2">Available Sizes:</h4>';
                    echo '<div class="size-variants flex flex-wrap gap-2">';
                    
                    foreach ($size_variants as $size) {
                        $classes = 'size-option w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-xs font-medium transition-all duration-200 hover:border-gray-400';
                        $classes .= !$size['in_stock'] ? ' opacity-50 cursor-not-allowed bg-gray-100 text-gray-400' : ' bg-white text-gray-700 hover:bg-gray-50';
                        
                        echo '<span class="' . $classes . '" ';
                        echo 'title="' . esc_attr($size['name'] . (!$size['in_stock'] ? ' - Out of Stock' : '')) . '" ';
                        echo 'data-size="' . esc_attr($size['slug']) . '" ';
                        echo 'data-variation-id="' . esc_attr($size['variation_id']) . '" ';
                        echo 'data-in-stock="' . ($size['in_stock'] ? 'true' : 'false') . '">';
                        echo esc_html($size['name']);
                        echo '</span>';
                    }
                    
                    echo '</div>';
                    echo '</div>';
                    
                    // Show debug info
                    echo '<div class="text-xs text-gray-500">';
                    echo '<strong>Debug Info:</strong><br>';
                    echo 'Total sizes found: ' . count($size_variants) . '<br>';
                    echo 'Product type: ' . $product->get_type() . '<br>';
                    echo 'Has variations: ' . (count($product->get_available_variations()) > 0 ? 'Yes' : 'No') . '<br>';
                    echo '</div>';
                } else {
                    echo '<p class="text-gray-500 text-sm">No size variants found for this product.</p>';
                    
                    // Show available attributes for debugging
                    $attributes = $product->get_variation_attributes();
                    if (!empty($attributes)) {
                        echo '<div class="text-xs text-gray-500 mt-2">';
                        echo '<strong>Available attributes:</strong><br>';
                        foreach ($attributes as $attr_name => $options) {
                            echo '- ' . $attr_name . ': ' . implode(', ', $options) . '<br>';
                        }
                        echo '</div>';
                    }
                }
                
                echo '</div>';
            }
            
            echo '</div>';
            wp_reset_postdata();
        }
        ?>
    </div>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Instructions for Setup</h2>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <ol class="list-decimal list-inside space-y-2 text-sm">
                <li>Create a variable product in WooCommerce</li>
                <li>Add a product attribute called "Size Selection" or "Size"</li>
                <li>Add size values like: 36, 37, 38, 39, 40, 41, 42</li>
                <li>Create variations for each size</li>
                <li>Set some variations as "Out of stock" to test the disabled state</li>
                <li>The size variants should now appear on product cards in the shop</li>
            </ol>
        </div>
    </div>
    
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Expected Behavior</h2>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <ul class="list-disc list-inside space-y-2 text-sm">
                <li>Size variants should appear as circular buttons below the product image</li>
                <li>In-stock sizes should be clickable and show hover effects</li>
                <li>Out-of-stock sizes should appear grayed out with a strikethrough line</li>
                <li>Clicking a size should select it (add active state)</li>
                <li>Sizes should be sorted numerically if they are numbers</li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Include the size variants styles for this test page */
.size-variants {
    border-top: 1px solid #f3f4f6;
    padding-top: 12px;
}

.size-option {
    min-width: 32px;
    height: 32px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    user-select: none;
}

.size-option:not(.opacity-50):hover {
    transform: scale(1.05);
    border-color: #6b7280;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.size-option.active {
    background-color: #1f2937;
    color: white;
    border-color: #1f2937;
    transform: scale(1.05);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.size-option.opacity-50 {
    position: relative;
}

.size-option.opacity-50::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 10%;
    right: 10%;
    height: 1px;
    background-color: #9ca3af;
    transform: translateY(-50%);
}
</style>

<script>
// Include the size selection JavaScript for this test page
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('size-option')) {
            e.preventDefault();
            
            var sizeOption = e.target;
            var isInStock = sizeOption.dataset.inStock === 'true';
            
            // Don't allow selection of out of stock sizes
            if (!isInStock) {
                return false;
            }
            
            // Remove active state from siblings
            var siblings = sizeOption.parentNode.querySelectorAll('.size-option');
            siblings.forEach(function(sibling) {
                sibling.classList.remove('active');
            });
            
            // Add active state to clicked size
            sizeOption.classList.add('active');
            
            console.log('Selected size:', sizeOption.dataset.size);
            console.log('Variation ID:', sizeOption.dataset.variationId);
        }
    });
});
</script>

<?php get_footer(); ?>
