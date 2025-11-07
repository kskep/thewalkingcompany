<?php
/**
 * Temporary file to clear WooCommerce and WordPress caches
 * Access this file once via: yoursite.com/wp-content/themes/thewalkingcompany/clear-cache-once.php
 * Then delete this file
 */

// Load WordPress
require_once('../../../wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. You must be logged in as an administrator.');
}

echo '<h1>Clearing Caches...</h1>';
echo '<style>body { font-family: Arial; padding: 20px; }</style>';

// Clear WooCommerce transients
if (function_exists('wc_delete_product_transients')) {
    echo '<p>✓ Clearing WooCommerce product transients...</p>';
    wc_delete_product_transients();
}

if (function_exists('wc_delete_shop_order_transients')) {
    echo '<p>✓ Clearing WooCommerce shop transients...</p>';
    wc_delete_shop_order_transients();
}

// Delete all WooCommerce related transients
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%wc%' OR option_name LIKE '_transient_timeout_%wc%'");
echo '<p>✓ Cleared WooCommerce transients from database...</p>';

// Clear term counts
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_term_counts%'");
echo '<p>✓ Cleared term count transients...</p>';

// Clear product query cache
delete_transient('wc_products_onsale');
delete_transient('wc_featured_products');
echo '<p>✓ Cleared product query cache...</p>';

// Clear object cache
wp_cache_flush();
echo '<p>✓ Flushed object cache...</p>';

// Clear rewrite rules
flush_rewrite_rules();
echo '<p>✓ Flushed rewrite rules...</p>';

// Recount product terms
if (function_exists('wc_recount_all_terms')) {
    echo '<p>⏳ Recounting product terms (this may take a moment)...</p>';
    flush();
    wc_recount_all_terms();
    echo '<p>✓ Recounted all product terms...</p>';
}

echo '<h2 style="color: green; margin-top: 30px;">✓ All caches cleared!</h2>';
echo '<p><strong>Important next steps:</strong></p>';
echo '<ol>';
echo '<li><strong>Delete this file (clear-cache-once.php)</strong> for security</li>';
echo '<li>Hard-refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)</li>';
echo '<li>Test the product archive pages (shoes, clothing, etc.)</li>';
echo '<li>Optional: Run the test at <a href="test-pagination.php">test-pagination.php</a> (then delete it too)</li>';
echo '</ol>';
