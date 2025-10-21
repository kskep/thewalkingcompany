<?php
/**
 * Root Product Archive – Delegate to WooCommerce override
 * Ensures WordPress uses our magazine archive template for shop archives.
 */
defined('ABSPATH') || exit;

// Reuse the WooCommerce archive template in the theme override folder
wc_get_template('archive-product.php');
