<?php
/**
 * Product Tag Archive – Delegate to archive-product.php
 */
defined('ABSPATH') || exit;

// Reuse the magazine archive template for tags
wc_get_template('archive-product.php');
