<?php
/**
 * Filters Component: toolbar button, active bar, and off-canvas panel
 */
defined('ABSPATH') || exit;

global $wp;

$query_params = array();
foreach ($_GET as $key => $value) {
    if (is_array($value)) {
        $query_params[$key] = array_map('sanitize_text_field', wp_unslash($value));
    } else {
        $query_params[$key] = sanitize_text_field(wp_unslash($value));
    }
}

$current_url = esc_url_raw(add_query_arg(array())) ?: home_url(add_query_arg(array(), $wp->request ?? ''));
$base_url = remove_query_arg(array_keys($query_params), $current_url);

$build_remove_url = static function (string $key, ?string $value = null) use ($query_params, $base_url) {
    $params = $query_params;

    if (!isset($params[$key])) {
        return esc_url(add_query_arg($params, $base_url));
    }

    $existing = $params[$key];

    if (null === $value) {
        unset($params[$key]);
    } else {
        if (is_array($existing)) {
            $values = $existing;
        } else {
            $values = array_filter(array_map('trim', explode(',', (string) $existing)));
        }

        $values = array_values(array_diff($values, array($value)));

        if (empty($values)) {
            unset($params[$key]);
        } else {
            $params[$key] = is_array($existing) ? $values : implode(',', $values);
        }
    }

    return esc_url(add_query_arg($params, $base_url));
};

$active_filters = array();

$min_price = isset($query_params['min_price']) && $query_params['min_price'] !== '' ? floatval($query_params['min_price']) : null;
$max_price = isset($query_params['max_price']) && $query_params['max_price'] !== '' ? floatval($query_params['max_price']) : null;

if (null !== $min_price || null !== $max_price) {
    $min_label = $min_price !== null ? wc_price($min_price) : __('Any', 'eshop-theme');
    $max_label = $max_price !== null ? wc_price($max_price) : __('Any', 'eshop-theme');
    $price_remove_url = esc_url(remove_query_arg(array('min_price', 'max_price'), $current_url));
    $active_filters[] = array(
        'label'      => sprintf(__('Price: %1$s – %2$s', 'eshop-theme'), wp_strip_all_tags($min_label), wp_strip_all_tags($max_label)),
        'remove_url' => $price_remove_url,
        'aria'       => __('Remove price filter', 'eshop-theme'),
    );
}

if (!empty($query_params['product_cat'])) {
    $raw_categories = is_array($query_params['product_cat']) ? $query_params['product_cat'] : explode(',', $query_params['product_cat']);
    foreach ($raw_categories as $slug_or_id) {
        $slug_or_id = trim((string) $slug_or_id);
        if ($slug_or_id === '') {
            continue;
        }

        $term = is_numeric($slug_or_id)
            ? get_term((int) $slug_or_id, 'product_cat')
            : get_term_by('slug', $slug_or_id, 'product_cat');

        $label = $term && !is_wp_error($term) ? $term->name : $slug_or_id;

        $active_filters[] = array(
            'label'      => sprintf(__('Category: %s', 'eshop-theme'), $label),
            'remove_url' => $build_remove_url('product_cat', $slug_or_id),
            'aria'       => sprintf(__('Remove category %s', 'eshop-theme'), $label),
        );
    }
}

if (!empty($query_params['on_sale'])) {
    $active_filters[] = array(
        'label'      => __('On Sale', 'eshop-theme'),
        'remove_url' => esc_url(remove_query_arg('on_sale', $current_url)),
        'aria'       => __('Remove on-sale filter', 'eshop-theme'),
    );
}

if (!empty($query_params['stock_status'])) {
    $stock_values = is_array($query_params['stock_status']) ? $query_params['stock_status'] : explode(',', $query_params['stock_status']);
    foreach ($stock_values as $status) {
        $status = trim((string) $status);
        if ($status === '') {
            continue;
        }
        $label = ucfirst(str_replace('_', ' ', $status));
        $active_filters[] = array(
            'label'      => sprintf(__('Stock: %s', 'eshop-theme'), $label),
            'remove_url' => $build_remove_url('stock_status', $status),
            'aria'       => sprintf(__('Remove stock filter %s', 'eshop-theme'), $label),
        );
    }
}

foreach ($query_params as $key => $value) {
    if (strpos($key, 'pa_') !== 0 || empty($value)) {
        continue;
    }

    $attribute_values = is_array($value) ? $value : explode(',', $value);
    foreach ($attribute_values as $attribute_value) {
        $attribute_value = trim((string) $attribute_value);
        if ($attribute_value === '') {
            continue;
        }

        $term = get_term_by('slug', $attribute_value, $key);
        if (!$term) {
            $term = get_term_by('name', $attribute_value, $key);
        }

        $attribute_label = $term && !is_wp_error($term) ? $term->name : $attribute_value;
        $taxonomy_obj    = get_taxonomy($key);
        $chip_label      = $taxonomy_obj && !is_wp_error($taxonomy_obj)
            ? sprintf('%s: %s', $taxonomy_obj->labels->singular_name ?? $key, $attribute_label)
            : sprintf('%s: %s', $key, $attribute_label);

        $active_filters[] = array(
            'label'      => $chip_label,
            'remove_url' => $build_remove_url($key, $attribute_value),
            'aria'       => sprintf(__('Remove filter %s', 'eshop-theme'), $attribute_label),
        );
    }
}

$result_count_html = '';
if (function_exists('woocommerce_result_count')) {
    ob_start();
    woocommerce_result_count();
    $result_count_html = trim(ob_get_clean());
}

$ordering_html = '';
if (function_exists('woocommerce_catalog_ordering')) {
    ob_start();
    woocommerce_catalog_ordering();
    $ordering_html = trim(ob_get_clean());
}

$context_intro = '';
if (is_product_category() || is_product_tag()) {
    $term = get_queried_object();
    if ($term && !empty($term->description)) {
        $context_intro = wp_trim_words(wp_strip_all_tags($term->description), 20, '…');
    }
}

$clear_filters_url = esc_url($base_url);
?>

<section class="archive-toolbar" aria-label="<?php esc_attr_e('Product filters', 'eshop-theme'); ?>">
  <div class="archive-toolbar__top">
    <div class="archive-toolbar__breadcrumbs">
      <?php get_template_part('template-parts/components/breadcrumbs'); ?>
    </div>

    <div class="archive-toolbar__controls">
      <button id="open-filters" class="archive-toolbar__filters-btn" aria-label="<?php esc_attr_e('Open filters', 'eshop-theme'); ?>">
        <span class="material-icons" aria-hidden="true">tune</span>
        <span><?php esc_html_e('Filters', 'eshop-theme'); ?></span>
      </button>

      <?php if ($result_count_html) : ?>
        <div class="archive-toolbar__meta">
          <?php if ($context_intro) : ?>
            <p class="archive-toolbar__subtitle"><?php echo esc_html($context_intro); ?></p>
          <?php endif; ?>
          <div class="archive-toolbar__result-count"><?php echo wp_kses_post($result_count_html); ?></div>
        </div>
      <?php endif; ?>

            <?php if ($ordering_html) : ?>
                <div class="archive-toolbar__ordering"><?php echo wp_kses_post($ordering_html); ?></div>
            <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($active_filters)) : ?>
    <div class="archive-toolbar__active" data-active-filters="true">
      <span class="archive-toolbar__active-label"><?php esc_html_e('Active Filters', 'eshop-theme'); ?></span>
      <ul class="archive-toolbar__chips" role="list">
        <?php foreach ($active_filters as $chip) : ?>
          <li class="archive-toolbar__chip">
            <span><?php echo esc_html($chip['label']); ?></span>
            <a href="<?php echo esc_url($chip['remove_url']); ?>" class="archive-toolbar__chip-remove" aria-label="<?php echo esc_attr($chip['aria']); ?>">
              <span aria-hidden="true">&times;</span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <a class="archive-toolbar__clear" href="<?php echo esc_url($clear_filters_url); ?>"><?php esc_html_e('Clear All', 'eshop-theme'); ?></a>
    </div>
  <?php endif; ?>
</section>

<?php
// Filter drawer markup is rendered in template-parts/components/filter-modal.php via wp_footer hook.
?>
