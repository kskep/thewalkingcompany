<?php
/**
 * Product Category Archive – Delegate to archive-product.php
 */
defined('ABSPATH') || exit;

// Reuse the magazine archive template for categories
wc_get_template('archive-product.php');
