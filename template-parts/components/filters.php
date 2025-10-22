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
<!-- Backdrop for filter drawer -->
<div id="filter-backdrop" class="fixed inset-0 bg-black bg-opacity-40 hidden z-40" style="display: none;"></div>

<aside id="filter-drawer" class="fixed inset-y-0 left-0 w-80 max-w-[90vw] bg-white shadow-xl z-50" style="transform: translateX(-100%);">
  <div class="p-4 border-b border-gray-200 flex items-center justify-between flex-shrink-0">
    <h3 class="text-base font-semibold"><?php _e('Filters', 'eshop-theme'); ?></h3>
    <button id="close-filters" class="p-2" aria-label="<?php esc_attr_e('Close Filters', 'eshop-theme'); ?>"><span class="material-icons">close</span></button>
  </div>
  <div class="p-4 space-y-4 overflow-y-auto" style="height: calc(100% - 60px);">
    <?php
    // Price, categories, and attributes using existing helper endpoints
    get_template_part('template-parts/components/filters/price-filter');
    get_template_part('template-parts/components/filters/category-filter');

    // Attributes: dynamically include all registered product attributes that have available terms
    if (function_exists('wc_get_attribute_taxonomies')) {
        $attribute_taxonomies = wc_get_attribute_taxonomies();
        if (!empty($attribute_taxonomies)) {
            foreach ($attribute_taxonomies as $attr) {
                // $attr is stdClass with properties attribute_name, attribute_label, etc.
                $taxonomy = 'pa_' . $attr->attribute_name;

                // Optionally skip attributes you never want to show
                $skip = apply_filters('eshop_filters_skip_attribute', false, $taxonomy, $attr);
                if ($skip) { continue; }

                // Render attribute filter; component will early-return if there are no terms in current context
                get_template_part('template-parts/components/filters/attribute-filter', null, array(
                    'taxonomy' => $taxonomy,
                    'label'    => $attr->attribute_label,
                ));
            }
        }
    }

    // Sale filter
    get_template_part('template-parts/components/filters/sale-filter');
    ?>
  </div>
</aside>

<script>
  (function(){
    // Inline minimal open/close behavior as a progressive enhancement
    const openBtn = document.getElementById('open-filters');
    const closeBtn = document.getElementById('close-filters');
    const drawer = document.getElementById('filter-drawer');
    const backdrop = document.getElementById('filter-backdrop');
    function open(){
      if(backdrop){ backdrop.classList.remove('hidden'); backdrop.classList.add('show'); }
      if(drawer){ drawer.classList.add('open'); }
      document.body.classList.add('overflow-hidden');
    }
    function close(){
      if(backdrop){ backdrop.classList.remove('show'); backdrop.classList.add('hidden'); }
      if(drawer){ drawer.classList.remove('open'); }
      document.body.classList.remove('overflow-hidden');
    }
    if(openBtn) openBtn.addEventListener('click', function(e){ e.preventDefault(); open(); });
    if(closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); close(); });
    if(backdrop) backdrop.addEventListener('click', close);
  })();
</script>
