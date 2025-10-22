<?php
/**
 * Product Attribute Filter Component (supports attribute object OR explicit taxonomy/label)
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Preferred: explicit taxonomy/label via args
$taxonomy = isset($args['taxonomy']) ? sanitize_text_field($args['taxonomy']) : '';
$attribute_label = isset($args['label']) ? $args['label'] : '';
$data_attribute = '';

if (!$taxonomy) {
    // Back-compat: get attribute object from args
    $attribute = isset($args['attribute']) ? $args['attribute'] : null;
    if (!$attribute) {
        return;
    }
    $taxonomy = 'pa_' . $attribute->attribute_name;
    $attribute_label = $attribute->attribute_label;
    $data_attribute = $attribute->attribute_name;
} else {
    // Derive data-attribute for JS collector by stripping 'pa_'
    $data_attribute = preg_replace('/^pa_/', '', $taxonomy);
}

// Get terms from current context if helper exists, else fallback to global terms
if (function_exists('eshop_get_available_attribute_terms')) {
    $raw_terms = eshop_get_available_attribute_terms($taxonomy);
    // Normalize to WP_Term-like arrays {term_id, name, slug, count, color}
    $terms = array();
    foreach ($raw_terms as $row) {
        $terms[] = (object) array(
            'term_id' => isset($row['term_id']) ? intval($row['term_id']) : 0,
            'name' => isset($row['name']) ? $row['name'] : '',
            'slug' => isset($row['slug']) ? $row['slug'] : '',
            'count' => isset($row['count']) ? intval($row['count']) : 0,
            'color' => isset($row['color']) ? $row['color'] : '',
        );
    }
} else {
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => true,
    ));
}

if (empty($terms) || is_wp_error($terms)) {
    return;
}

// Get currently selected values from URL (slugs)
$selected_values = array();
if (isset($_GET[$taxonomy]) && !empty($_GET[$taxonomy])) {
    $raw = sanitize_text_field(wp_unslash($_GET[$taxonomy]));
    $selected_values = array_filter(array_map('trim', explode(',', $raw)));
}
?>

<div class="filter-section mb-6 attribute-filter" data-attribute="<?php echo esc_attr($data_attribute); ?>">
    <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
        <?php echo esc_html($attribute_label); ?>
    </h4>

    <div class="attribute-terms space-y-2">
        <?php foreach ($terms as $term) :
            $slug = isset($term->slug) ? $term->slug : '';
            $is_checked = in_array($slug, $selected_values, true);
            $product_count = isset($term->count) ? intval($term->count) : 0;

            // Support color meta from helper; else from term meta
            $color_value = isset($term->color) ? $term->color : '';
            if (!$color_value && isset($term->term_id)) {
                $meta = get_term_meta($term->term_id);
                $color_value = isset($meta['color'][0]) ? $meta['color'][0] : '';
                $image_id = isset($meta['image'][0]) ? $meta['image'][0] : '';
            } else {
                $image_id = '';
            }
        ?>
            <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">
                <div class="flex items-center space-x-2">
                    <input
                        type="checkbox"
                        name="<?php echo esc_attr($taxonomy); ?>[]"
                        value="<?php echo esc_attr($slug); ?>"
                        class="text-primary focus:ring-primary border-gray-300 rounded"
                        <?php checked($is_checked); ?>
                    >

                    <?php if ($color_value) : ?>
                        <span
                            class="w-4 h-4 rounded-full border border-gray-300 inline-block"
                            style="background-color: <?php echo esc_attr($color_value); ?>"
                            title="<?php echo esc_attr(isset($term->name) ? $term->name : ''); ?>"
                        ></span>
                    <?php elseif (!empty($image_id)) : ?>
                        <?php $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                        if ($image_url) : ?>
                            <img
                                src="<?php echo esc_url($image_url); ?>"
                                alt="<?php echo esc_attr(isset($term->name) ? $term->name : ''); ?>"
                                class="w-6 h-6 object-cover rounded border border-gray-300"
                            >
                        <?php endif; ?>
                    <?php endif; ?>

                    <span class="text-sm text-gray-700 group-hover:text-gray-900">
                        <?php echo esc_html(isset($term->name) ? $term->name : ''); ?>
                    </span>
                </div>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                    <?php echo esc_html((string)$product_count); ?>
                </span>
            </label>
        <?php endforeach; ?>
    </div>
</div>
