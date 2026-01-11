<?php
require_once('wp-load.php');

$args = array(
    'post_type' => 'product',
    'posts_per_page' => 1,
    'tax_query' => array(
        array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'variable',
        ),
    ),
);
$products = get_posts($args);

if (empty($products)) {
    echo "No variable products found.\n";
    exit;
}

$product = wc_get_product($products[0]->ID);
echo "Checking Product ID: " . $product->get_id() . "\n";
echo "Name: " . $product->get_name() . "\n";
echo "Stock Status: " . $product->get_stock_status() . "\n";

$variations = $product->get_children();
echo "Found " . count($variations) . " variations.\n";

foreach ($variations as $var_id) {
    $variation = wc_get_product($var_id);
    echo "Variation ID: " . $var_id . "\n";
    echo "  Stock Status: " . $variation->get_stock_status() . "\n";
    
    // Check specific meta keys for attributes
    $attributes = $variation->get_attributes();
    echo "  Attributes (Object): " . print_r($attributes, true) . "\n";

    // Direct post meta
    $meta = get_post_meta($var_id);
    echo "  Meta Keys related to attributes:\n";
    foreach ($meta as $key => $val) {
        if (strpos($key, 'attribute_') === 0) {
            echo "    $key: " . print_r($val, true) . "\n";
        }
    }
}
