<?php
/**
 * Test file to verify pagination consistency
 * Access via: yoursite.com/wp-content/themes/thewalkingcompany/test-pagination.php
 * DELETE THIS FILE after testing
 */

require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator.');
}

echo '<h1>Pagination Test</h1>';
echo '<style>body { font-family: Arial; padding: 20px; } table { border-collapse: collapse; width: 100%; margin: 20px 0; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background: #f4f4f4; }</style>';

// Test different categories
$test_categories = array(
    'shoes' => 'Shoes',
    'clothing' => 'Clothing'
);

foreach ($test_categories as $slug => $name) {
    $term = get_term_by('slug', $slug, 'product_cat');
    
    if (!$term) {
        echo "<p>Category '$name' not found. Skipping...</p>";
        continue;
    }
    
    echo "<h2>$name Category (ID: {$term->term_id})</h2>";
    
    // Test first 3 pages
    for ($page = 1; $page <= 3; $page++) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'paged' => $page,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $term->term_id,
                    'include_children' => true
                )
            ),
            'meta_query' => array(
                array(
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '='
                )
            )
        );
        
        $query = new WP_Query($args);
        
        echo "<h3>Page $page</h3>";
        echo "<table>";
        echo "<tr><th>Found Posts</th><th>Posts Returned</th><th>Status</th></tr>";
        
        $status = ($query->post_count == 12 || ($query->post_count < 12 && $page == $query->max_num_pages)) 
            ? '<span style="color: green;">✓ OK</span>' 
            : '<span style="color: red;">✗ ISSUE</span>';
        
        echo "<tr>";
        echo "<td>{$query->found_posts} total in category</td>";
        echo "<td><strong>{$query->post_count}</strong> products on page $page</td>";
        echo "<td>$status</td>";
        echo "</tr>";
        echo "</table>";
        
        if ($query->have_posts()) {
            echo "<p><small>Products on this page: ";
            $product_names = array();
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());
                $stock_status = $product->is_in_stock() ? 'in-stock' : 'out-of-stock';
                $product_names[] = get_the_title() . " ($stock_status)";
            }
            echo implode(', ', $product_names);
            echo "</small></p>";
        }
        
        wp_reset_postdata();
    }
}

echo '<p style="margin-top: 40px; padding: 15px; background: #fffbcc; border-left: 4px solid #ffeb3b;"><strong>Done!</strong> If all pages show 12 products (or less on the last page), the fix is working. Now <strong>delete this file</strong> for security.</p>';
