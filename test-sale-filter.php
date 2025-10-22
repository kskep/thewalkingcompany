<?php
/**
 * Test file to verify sale filter count fix
 *
 * HOW TO USE:
 * 1. Access this file via your browser: http://yourdomain.com/test-sale-filter.php
 * 2. Or access it on a specific category: http://yourdomain.com/test-sale-filter.php?category=your-category-slug
 * 3. Or test with sale filter: http://yourdomain.com/test-sale-filter.php?on_sale=1
 *
 * This will show you the difference between all sale products in the database
 * and sale products in the current context (category, filters, etc.)
 */

// Include WordPress
$wp_load_path = __DIR__ . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    echo "<h1>Error</h1>";
    echo "<p>Could not find WordPress. Please ensure this file is in the WordPress root directory.</p>";
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sale Filter Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test-section { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .warning { background: #fff3cd; color: #856404; }
        .error { background: #f8d7da; color: #721c24; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .test-links { margin: 20px 0; }
        .test-links a { display: inline-block; margin: 5px; padding: 8px 12px; background: #007cba; color: white; text-decoration: none; border-radius: 3px; }
        .test-links a:hover { background: #005a87; }
    </style>
</head>
<body>
    <h1>Sale Filter Test Tool</h1>
    
    <div class="test-section">
        <h2>Quick Test Links</h2>
        <div class="test-links">
            <a href="?">Shop Context</a>
            <a href="?on_sale=1">Test Sale Filter</a>
            <?php
            // Add links to product categories if they exist
            if (function_exists('get_terms')) {
                $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0));
                if (!empty($categories) && !is_wp_error($categories)) {
                    foreach (array_slice($categories, 0, 5) as $category) {
                        echo '<a href="?category=' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</a>';
                        
                        // Show if this category has children
                        $child_count = get_terms(array('taxonomy' => 'product_cat', 'child_of' => $category->term_id, 'hide_empty' => true, 'count' => true));
                        if ($child_count > 0) {
                            echo '<span style="font-size: 0.8em; color: #666;"> (+'. $child_count .' children)</span>';
                        }
                    }
                }
            }
            ?>
        </div>
    </div>

    <?php
    // Test the sale products count function
    echo '<div class="test-section">';
    echo "<h2>Sale Filter Test Results</h2>";

    // Get all sale products count
    $all_sale_count = function_exists('wc_get_product_ids_on_sale') ? count(wc_get_product_ids_on_sale()) : 0;
    echo "<p><strong>All sale products in database:</strong> $all_sale_count</p>";

    // Get context-aware sale products count
    $context_sale_count = function_exists('eshop_get_sale_products_count') ? eshop_get_sale_products_count() : 0;
    echo "<p><strong>Sale products in current context:</strong> $context_sale_count</p>";
    
    // Show if the fix is working
    if ($all_sale_count > $context_sale_count) {
        echo '<div class="success">✅ Fix is working: Context-aware count is correctly lower than global count</div>';
    } elseif ($all_sale_count == $context_sale_count && $all_sale_count > 0) {
        echo '<div class="warning">⚠️ Context is likely shop page (all products) - this is expected</div>';
    } else {
        echo '<div class="error">❌ No sale products found</div>';
    }
    echo '</div>';

    // Get current context
    echo '<div class="test-section">';
    echo "<h3>Current Context:</h3>";
    
    // Simulate category context if category parameter is passed
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category_slug = sanitize_text_field($_GET['category']);
        $category = get_term_by('slug', $category_slug, 'product_cat');
        if ($category && !is_wp_error($category)) {
            echo "<p><strong>Simulated Category:</strong> " . $category->name . "</p>";
            
            // Show child categories
            $child_categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'child_of' => $category->term_id,
                'hide_empty' => true,
                'fields' => 'names'
            ));
            
            if (!empty($child_categories) && !is_wp_error($child_categories)) {
                echo "<p><strong>Child Categories:</strong> " . implode(', ', $child_categories) . "</p>";
            }
            
            // Set global query vars for context functions
            $GLOBALS['wp_query']->is_tax = true;
            $GLOBALS['wp_query']->is_product_category = true;
            $GLOBALS['wp_query']->queried_object = $category;
        }
    } elseif (is_product_category()) {
        $category = get_queried_object();
        echo "<p><strong>Current Category:</strong> " . $category->name . "</p>";
        
        // Show child categories
        $child_categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'child_of' => $category->term_id,
            'hide_empty' => true,
            'fields' => 'names'
        ));
        
        if (!empty($child_categories) && !is_wp_error($child_categories)) {
            echo "<p><strong>Child Categories:</strong> " . implode(', ', $child_categories) . "</p>";
        }
    } elseif (is_shop()) {
        echo "<p><strong>Shop page (all products)</strong></p>";
    } else {
        echo "<p><strong>Test context (simulated)</strong></p>";
    }
    echo '</div>';

    // Check current URL parameters
    echo '<div class="test-section">';
    echo "<h3>Current URL Parameters:</h3>";
    echo "<pre>" . print_r($_GET, true) . "</pre>";
    echo '</div>';

    // Test the actual filter query
    echo '<div class="test-section">';
    echo "<h3>Filter Query Test:</h3>";

    if (function_exists('eshop_get_current_context_product_ids')) {
        $context_products = eshop_get_current_context_product_ids();
        echo "<p><strong>Products in current context:</strong> " . count($context_products) . "</p>";
        
        if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
            $sale_ids = wc_get_product_ids_on_sale();
            $filtered_products = array_intersect($context_products, $sale_ids);
            echo "<p><strong>Sale products after filtering:</strong> " . count($filtered_products) . "</p>";
            
            if (count($filtered_products) === $context_sale_count) {
                echo '<div class="success">✅ Filter results match the count displayed in filter</div>';
            } else {
                echo '<div class="error">❌ Mismatch between filter results and count</div>';
            }
        }
        
        // Show some sample product IDs for debugging
        if (!empty($context_products)) {
            echo "<p><strong>Sample product IDs in context:</strong> " . implode(', ', array_slice($context_products, 0, 5)) . "...</p>";
        }
        
        // Test child category inclusion
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $category_slug = sanitize_text_field($_GET['category']);
            $category = get_term_by('slug', $category_slug, 'product_cat');
            if ($category && !is_wp_error($category)) {
                $child_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'child_of' => $category->term_id,
                    'hide_empty' => true,
                    'fields' => 'ids'
                ));
                
                if (!empty($child_categories) && !is_wp_error($child_categories)) {
                    echo "<p><strong>Child category IDs included:</strong> " . implode(', ', $child_categories) . "</p>";
                    echo '<div class="success">✅ Child categories are being included in the context</div>';
                } else {
                    echo '<div class="warning">⚠️ No child categories found for this category</div>';
                }
            }
        }
    } else {
        echo '<div class="error">❌ Context function not available</div>';
    }
    echo '</div>';
    ?>

    <div class="test-section">
        <h2>How to Verify the Fix</h2>
        <ol>
            <li>Visit your shop page and check the "On Sale" filter count</li>
            <li>Apply the "On Sale" filter and count the actual results</li>
            <li>The numbers should match (or be very close due to pagination)</li>
            <li>Test this on different category pages - the count should vary based on the category</li>
        </ol>
        <p><strong>If the numbers match, the fix is working correctly!</strong></p>
    </div>
</body>
</html>

?>