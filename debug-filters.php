<?php
/**
 * Debug Filter Logic
 * Access: yoursite.com/wp-content/themes/thewalkingcompany/debug-filters.php?taxonomy=pa_select-size
 */

// Load WordPress
$wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
if (!file_exists($wp_load_path)) {
    die('Cannot find wp-load.php');
}
require_once($wp_load_path);

// Security check
if (!current_user_can('manage_options')) {
    die('Unauthorized');
}

header('Content-Type: text/html; charset=utf-8');

$taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : 'pa_select-size';

echo "<h1>Filter Debug for: {$taxonomy}</h1>";

// Check if class exists
if (!class_exists('Eshop_Product_Filters')) {
    echo "<p style='color:red;'>ERROR: Eshop_Product_Filters class not found!</p>";
    exit;
}

echo "<h2>1. Base Context Product IDs</h2>";
$context_ids = Eshop_Product_Filters::get_base_context_product_ids();
echo "<p>Found " . count($context_ids) . " products in context</p>";
echo "<pre>" . print_r(array_slice($context_ids, 0, 20), true) . "</pre>";

echo "<h2>2. Available Attribute Terms (should only show in-stock)</h2>";
$terms = Eshop_Product_Filters::get_available_attribute_terms($taxonomy);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Term ID</th><th>Name</th><th>Slug</th><th>Count (products with in-stock variation)</th></tr>";
foreach ($terms as $term) {
    echo "<tr>";
    echo "<td>" . esc_html($term['term_id']) . "</td>";
    echo "<td>" . esc_html($term['name']) . "</td>";
    echo "<td>" . esc_html($term['slug']) . "</td>";
    echo "<td>" . esc_html($term['count']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>3. Direct Database Check - Variation Stock Status for a sample product</h2>";

global $wpdb;

// Get first variable product
$sample_product_id = $wpdb->get_var("
    SELECT p.ID 
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
    WHERE p.post_type = 'product' 
    AND p.post_status = 'publish'
    AND tt.taxonomy = 'product_type'
    AND t.slug = 'variable'
    LIMIT 1
");

if ($sample_product_id) {
    echo "<p>Sample Variable Product ID: {$sample_product_id}</p>";
    
    $variations = $wpdb->get_results($wpdb->prepare("
        SELECT 
            v.ID as variation_id,
            pm_stock.meta_value as stock_status,
            pm_size.meta_value as size_value
        FROM {$wpdb->posts} v
        LEFT JOIN {$wpdb->postmeta} pm_stock ON v.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'
        LEFT JOIN {$wpdb->postmeta} pm_size ON v.ID = pm_size.post_id AND pm_size.meta_key = %s
        WHERE v.post_type = 'product_variation'
        AND v.post_parent = %d
        AND v.post_status = 'publish'
    ", 'attribute_' . $taxonomy, $sample_product_id), ARRAY_A);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Variation ID</th><th>Stock Status</th><th>Size Value</th></tr>";
    foreach ($variations as $var) {
        $color = $var['stock_status'] === 'instock' ? 'green' : 'red';
        echo "<tr style='color:{$color}'>";
        echo "<td>" . esc_html($var['variation_id']) . "</td>";
        echo "<td>" . esc_html($var['stock_status']) . "</td>";
        echo "<td>" . esc_html($var['size_value']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>4. Test: Products with in-stock variations for size '38' (or first available size)</h2>";

$first_size = !empty($terms) ? $terms[0]['slug'] : '38';
echo "<p>Testing with size slug: {$first_size}</p>";

$test_filters = array($taxonomy => array($first_size));
$filtered_ids = Eshop_Product_Filters::get_products_with_instock_variations($test_filters);
echo "<p>Found " . count($filtered_ids) . " products with in-stock {$first_size} variations</p>";
echo "<pre>" . print_r(array_slice($filtered_ids, 0, 10), true) . "</pre>";

echo "<hr><p>Debug complete.</p>";
