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
// Off-canvas filter panel
?>
<aside id="filters-panel" class="fixed inset-y-0 left-0 w-80 max-w-[90vw] bg-white shadow-xl z-50 translate-x-[-100%] transition-transform">
  <div class="p-4 border-b border-gray-200 flex items-center justify-between">
    <h3 class="text-base font-semibold"><?php _e('Filters', 'eshop-theme'); ?></h3>
    <button id="close-filters" class="p-2" aria-label="<?php esc_attr_e('Close Filters', 'eshop-theme'); ?>"><span class="material-icons">close</span></button>
  </div>
  <div class="p-4 space-y-6 overflow-y-auto h-full">
    <?php
    // Price, categories, and attributes using existing helper endpoints
    get_template_part('template-parts/components/filters/price-filter');
    get_template_part('template-parts/components/filters/category-filter');
    // Attributes: color, size, sale
    get_template_part('template-parts/components/filters/attribute-filter', null, array('taxonomy' => 'pa_color', 'label' => __('Color', 'eshop-theme')));
    get_template_part('template-parts/components/filters/attribute-filter', null, array('taxonomy' => 'pa_select-size', 'label' => __('Size', 'eshop-theme')));
    get_template_part('template-parts/components/filters/sale-filter');
    ?>
  </div>
</aside>

<script>
  (function(){
    const openBtn = document.getElementById('open-filters');
    const closeBtn = document.getElementById('close-filters');
    const panel = document.getElementById('filters-panel');
    function open(){ panel.style.transform = 'translateX(0)'; }
    function close(){ panel.style.transform = 'translateX(-100%)'; }
    if(openBtn) openBtn.addEventListener('click', open);
    if(closeBtn) closeBtn.addEventListener('click', close);
  })();
</script>
