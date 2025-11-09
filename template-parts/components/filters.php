<?php
/**
 * Filters Component: toolbar button, active bar, and off-canvas panel
 */
defined('ABSPATH') || exit;

// Active filters bar placeholder
?>
<div class="shop-toolbar flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
  <button id="open-filters" class="filter-toggle-btn-flat flex items-center gap-2 px-4 py-2 bg-transparent" aria-label="<?php esc_attr_e('Open Filters', 'eshop-theme'); ?>">
    <span class="material-icons text-base">tune</span>
    <span class="text-sm font-medium uppercase tracking-wide"><?php _e('Filters', 'eshop-theme'); ?></span>
  </button>
  <div class="shop-ordering">
    <?php woocommerce_catalog_ordering(); ?>
  </div>
  </div>

<div class="active-filters-bar mb-6" style="display:none">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-2">
      <span class="text-sm font-semibold uppercase tracking-wide" style="color: var(--ink-soft);"><?php _e('Active Filters:', 'eshop-theme'); ?></span>
      <div class="active-filters-list flex flex-wrap gap-2"></div>
    </div>
    <button class="clear-all-filters text-sm font-semibold uppercase tracking-wide" style="color: var(--ink);"><?php _e('Clear All', 'eshop-theme'); ?></button>
  </div>
</div>

<?php
// Filter drawer markup is rendered in template-parts/components/filter-modal.php via wp_footer hook.
?>
