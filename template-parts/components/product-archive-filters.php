<?php
/**
 * Product Archive Filters Component
 * 
 * Displays the complete filter toolbar as designed in the concept
 * Includes breadcrumb, result meta, sort dropdown, and active filter chips
 */

defined('ABSPATH') || exit;

if (defined('ESHOP_PRODUCT_ARCHIVE_FILTERS_RENDERED')) {
    return;
}
define('ESHOP_PRODUCT_ARCHIVE_FILTERS_RENDERED', true);

// Get current query for metadata
global $wp_query;
$total_products = $wp_query->found_posts;
$current_page = max(1, get_query_var('paged'));
$per_page = wc_get_default_products_per_row() * wc_get_default_product_rows_per_page();
$start = ($current_page - 1) * $per_page + 1;
$end = min($current_page * $per_page, $total_products);

// Get current sort order
$orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'date';
$order = isset($_GET['order']) ? strtoupper(sanitize_text_field($_GET['order'])) : 'DESC';

// Get current filters from URL
$current_filters = [];

// Price filter
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
if ($min_price > 0 || $max_price > 0) {
    $price_label = 'Price: ';
    if ($min_price > 0 && $max_price > 0) {
        $price_label .= wc_price($min_price) . ' - ' . wc_price($max_price);
    } elseif ($min_price > 0) {
        $price_label .= 'From ' . wc_price($min_price);
    } elseif ($max_price > 0) {
        $price_label .= 'Up to ' . wc_price($max_price);
    }
    $current_filters[] = [
        'label' => $price_label,
        'param' => 'price'
    ];
}

// Sale filter
if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
    $current_filters[] = [
        'label' => 'On Sale',
        'param' => 'on_sale'
    ];
}

// Stock filter
if (isset($_GET['stock_status']) && $_GET['stock_status'] === 'instock') {
    $current_filters[] = [
        'label' => 'In Stock',
        'param' => 'stock_status'
    ];
}

// Category chip only on taxonomy archives (not on shop root)
if (function_exists('is_product_category') && (is_product_category() || is_product_tag())) {
    $current_cat = get_queried_object();
    if ($current_cat && !is_wp_error($current_cat) && isset($current_cat->name)) {
        $current_filters[] = [
            'label' => $current_cat->name,
            'param' => 'cat-' . $current_cat->term_id
        ];
    }
}

// Attribute filters (support comma-delimited or array formats)
$attribute_taxonomies = wc_get_attribute_taxonomies();
if ($attribute_taxonomies) {
    foreach ($attribute_taxonomies as $taxonomy) {
        $attr_name = 'pa_' . $taxonomy->attribute_name;
        $values = array();
        if (isset($_GET[$attr_name])) {
            if (is_array($_GET[$attr_name])) {
                $values = array_map('sanitize_text_field', (array) $_GET[$attr_name]);
            } else {
                $values = array_filter(array_map('trim', explode(',', sanitize_text_field($_GET[$attr_name]))));
            }
        }
        foreach ($values as $term_slug) {
            $term = get_term_by('slug', $term_slug, $attr_name);
            if ($term && !is_wp_error($term)) {
                $current_filters[] = [
                    'label' => $taxonomy->attribute_label . ': ' . $term->name,
                    'param' => $attr_name . '-' . $term_slug
                ];
            }
        }
    }
}

// Generate breadcrumb
$breadcrumbs = function_exists('eshop_get_product_archive_breadcrumbs')
    ? eshop_get_product_archive_breadcrumbs()
    : [];

// Sort options
$sort_options = [
    'menu_order' => 'Sort — Featured',
    'popularity' => 'Sort — Popularity',
    'date' => 'Sort — Newest',
    'price' => 'Sort — Price (Low)',
    'price-desc' => 'Sort — Price (High)'
];

// Modern slider icon shared by desktop + mobile filter buttons
$filter_icon_svg = <<<SVG
<svg viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg" role="presentation" focusable="false">
    <path d="M4 6h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
    <circle cx="15" cy="6" r="2.2" stroke="currentColor" stroke-width="1.6" fill="none" />
    <path d="M4 12h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
    <circle cx="9" cy="12" r="2.2" stroke="currentColor" stroke-width="1.6" fill="none" />
    <path d="M4 18h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
    <circle cx="13" cy="18" r="2.2" stroke="currentColor" stroke-width="1.6" fill="none" />
</svg>
SVG;
?>

<section class="toolbar" role="region" aria-label="Product filtering and sorting">
    <!-- Top Section: Breadcrumb, Meta, and Desktop Controls -->
    <div class="toolbar__top">
        <div class="toolbar__info">
            <!-- Breadcrumb Navigation -->
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                    <?php if ($index > 0): ?>
                        <span class="breadcrumb-separator"> / </span>
                    <?php endif; ?>
                    <?php if ($index < count($breadcrumbs) - 1): ?>
                        <a href="<?php echo esc_url($crumb['url']); ?>" class="breadcrumb-link">
                            <?php echo esc_html($crumb['label']); ?>
                        </a>
                    <?php else: ?>
                        <span class="breadcrumb-current" aria-current="page">
                            <?php echo esc_html($crumb['label']); ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>

            <!-- Result Metadata -->
            <div class="result-meta" role="status" aria-live="polite">
                <span class="result-description">
                    <?php
                    if ($total_products > 0) {
                        echo sprintf(
                            _n(
                                '%s style curated for you',
                                '%s styles curated for you',
                                $total_products,
                                'eshop-theme'
                            ),
                            '<strong>' . number_format_i18n($total_products) . '</strong>'
                        );
                    } else {
                        echo '<strong>0</strong> products found';
                    }
                    ?>
                </span>

                <span class="result-divider" aria-hidden="true">|</span>

                <span class="result-range">
                    <?php
                    if ($total_products > 0) {
                        printf(
                            __('Showing %d – %d', 'eshop-theme'),
                            $start,
                            $end
                        );
                    }
                    ?>
                </span>
            </div>
        </div>

        <!-- Desktop Controls -->
        <div class="toolbar__actions" role="group" aria-label="<?php esc_attr_e('Filter and sort products', 'eshop-theme'); ?>">
            <div class="sort-container">
                <label for="product-sort" class="visually-hidden">
                    <?php _e('Sort products', 'eshop-theme'); ?>
                </label>
                <select id="product-sort" class="sort-select" aria-label="<?php _e('Sort products', 'eshop-theme'); ?>">
                    <?php foreach ($sort_options as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" 
                                <?php selected($orderby, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="button"
                    id="filter-toggle-desktop"
                    class="filter-toggle filter-toggle--desktop"
                    aria-controls="filter-modal"
                    aria-expanded="false">
                <span class="filter-toggle-icon" aria-hidden="true"><?php echo $filter_icon_svg; ?></span>
                <span class="filter-toggle-text"><?php _e('Filters', 'eshop-theme'); ?></span>
            </button>
        </div>
    </div>

    <!-- Mobile Filter Toggle -->
    <div class="toolbar__mobile-toggle">
        <button type="button"
                id="filter-toggle"
                class="filter-toggle filter-toggle--mobile"
                aria-controls="filter-modal"
                aria-expanded="false">
            <span class="filter-toggle-icon" aria-hidden="true"><?php echo $filter_icon_svg; ?></span>
            <span class="filter-toggle-text"><?php _e('Filters', 'eshop-theme'); ?></span>
        </button>
    </div>


</section>

<!-- Filter Modal Overlay -->
<div id="filter-modal-overlay" class="filter-modal-overlay" aria-hidden="true"></div>

<!-- Filter Modal -->
<div id="filter-modal" class="filter-modal" role="dialog" aria-modal="true" aria-labelledby="filter-modal-title" aria-hidden="true">
    <!-- Filter Modal Header -->
    <div class="filter-modal-header">
        <h2 id="filter-modal-title" class="filter-modal-title">
            <?php _e('Filter Products', 'eshop-theme'); ?>
        </h2>
        <button type="button" 
                id="filter-modal-close" 
                class="filter-modal-close"
                aria-label="<?php _e('Close filter modal', 'eshop-theme'); ?>">
            <span aria-hidden="true">×</span>
        </button>
    </div>

    <!-- Filter Modal Content -->
    <div class="filter-modal-content">
        <?php
        // Include filter sections
        $filter_sections = [
            'category' => __('Category', 'eshop-theme'),
            'attributes' => __('Attributes', 'eshop-theme'),
            'availability' => __('Availability', 'eshop-theme'),
            'price' => __('Price', 'eshop-theme')
        ];

        foreach ($filter_sections as $section_key => $section_title):
            // Use output buffering to check if section has content
            ob_start();
            $section_has_content = false;
            
            // Render appropriate filter section
            switch ($section_key) {
                        case 'category':
                            // Context-aware category filter
                            // On a subcategory page: show siblings (other subcategories of same parent)
                            // On shop page: show top-level categories
                            
                            $available_categories = function_exists('eshop_get_available_categories')
                                ? eshop_get_available_categories()
                                : array();
                            $category_counts = array();
                            foreach ($available_categories as $row) {
                                if (!is_array($row)) {
                                    continue;
                                }
                                $term_id = isset($row['term_id']) ? (int) $row['term_id'] : 0;
                                $count = isset($row['count']) ? (int) $row['count'] : 0;
                                if ($term_id > 0) {
                                    $category_counts[$term_id] = $count;
                                }
                            }
                            $aggregate_count = function($term_id) use ($category_counts) {
                                $sum = isset($category_counts[$term_id]) ? $category_counts[$term_id] : 0;
                                $descendant_ids = get_term_children($term_id, 'product_cat');
                                if (!empty($descendant_ids) && !is_wp_error($descendant_ids)) {
                                    foreach ($descendant_ids as $did) {
                                        $did = (int) $did;
                                        if (isset($category_counts[$did])) {
                                            $sum += $category_counts[$did];
                                        }
                                    }
                                }
                                return (int) $sum;
                            };

                            $selected_cat_slugs = array();
                            if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
                                $raw = sanitize_text_field(wp_unslash($_GET['product_cat']));
                                $selected_cat_slugs = array_filter(array_map('trim', explode(',', $raw)));
                            } elseif (function_exists('is_product_category') && (is_product_category() || is_product_tag())) {
                                $current_cat = get_queried_object();
                                if ($current_cat && !is_wp_error($current_cat) && isset($current_cat->slug)) {
                                    $selected_cat_slugs = array($current_cat->slug);
                                }
                            }
                            $selected_terms = array();
                            foreach ($selected_cat_slugs as $slug) {
                                $t = get_term_by('slug', $slug, 'product_cat');
                                if ($t && !is_wp_error($t)) $selected_terms[] = $t;
                            }

                            // Categories to always hide
                            $excluded_ids = array(15, 323, 446); // Uncategorized, Giftwrapping, Mystery Box

                            // Determine what categories to show based on context
                            $is_on_category_page = function_exists('is_product_category') && is_product_category();
                            $parent_id_to_use = 0;
                            $current_category_obj = null;
                            
                            if ($is_on_category_page) {
                                $current_category_obj = get_queried_object();
                                if ($current_category_obj && !is_wp_error($current_category_obj)) {
                                    // Show children of current category (sub-subcategories)
                                    $children_of_current = get_terms(array(
                                        'taxonomy' => 'product_cat',
                                        'parent' => $current_category_obj->term_id,
                                        'hide_empty' => false,
                                        'exclude' => $excluded_ids,
                                    ));

                                    $children_with_stock = array_filter((array) $children_of_current, function($term) use ($aggregate_count) {
                                        if (!($term instanceof WP_Term)) {
                                            return false;
                                        }
                                        return $aggregate_count((int) $term->term_id) > 0;
                                    });

                                    if (!empty($children_with_stock) && !is_wp_error($children_of_current)) {
                                        // Current category has children, show them
                                        $parent_id_to_use = $current_category_obj->term_id;
                                    } else {
                                        // Current category has no children, don't show category filter at all
                                        // (we're at a leaf category)
                                        echo '<!-- No subcategories to filter -->';
                                        break;
                                    }
                                }
                            }

                            $top_level = get_terms(array(
                                'taxonomy' => 'product_cat',
                                'parent' => $parent_id_to_use,
                                'hide_empty' => false,
                                'exclude' => $excluded_ids,
                                'orderby' => 'menu_order',
                                'order' => 'ASC',
                            ));
                            if (!empty($top_level) && !is_wp_error($top_level)):
                                echo '<div class="category-tree">';

                                $has_instock_children = function($term_id) use ($aggregate_count, $excluded_ids) {
                                    $children = get_terms(array(
                                        'taxonomy' => 'product_cat',
                                        'parent' => $term_id,
                                        'hide_empty' => false,
                                        'exclude' => $excluded_ids,
                                        'fields' => 'ids',
                                    ));
                                    if (empty($children) || is_wp_error($children)) {
                                        return false;
                                    }
                                    foreach ($children as $child_id) {
                                        if ($aggregate_count((int) $child_id) > 0) {
                                            return true;
                                        }
                                    }
                                    return false;
                                };

                                $render_branch = function($parent_term, $level) use (&$render_branch, $selected_terms, $aggregate_count, $has_instock_children, $excluded_ids) {
                                    $parent_id = ($parent_term instanceof WP_Term) ? $parent_term->term_id : (int) $parent_term;
                                    $children = get_terms(array(
                                        'taxonomy' => 'product_cat',
                                        'parent' => $parent_id,
                                        'hide_empty' => false,
                                        'orderby' => 'menu_order',
                                        'order' => 'ASC',
                                    ));

                                    // UL wrapper for this level
                                    echo '<ul class="subcategory-list level-' . (int) $level . '">';

                                    // If called with a parent term (level >= 0), render that parent item first when level > 0
                                    if ($parent_term instanceof WP_Term && $level > 0) {
                                        // Already rendered by parent; skip duplicating at level > 0
                                    }

                                    // For top-level call, iterate through top-level terms as children of 0
                                    if (is_array($children) && empty($children) && $level === 0) {
                                        // No explicit children under 0 via hide_empty=true + parent=0, but we still need to render top-level
                                        // This branch won't normally hit; kept for safety.
                                    }

                                    // For level 0 we want to display passed $parent_term array ($top_level) like children
                                    if ($level === 0 && is_array($parent_term)) {
                                        $items = $parent_term;
                                    } else {
                                        $items = get_terms(array(
                                            'taxonomy' => 'product_cat',
                                            'parent' => $parent_id,
                                            'hide_empty' => false,
                                            'orderby' => 'menu_order',
                                            'order' => 'ASC',
                                        ));
                                    }

                                    // Filter out excluded IDs and ensure distinct items
                                    $items = array_filter($items, function($t) use ($excluded_ids) {
                                        return $t instanceof WP_Term && !in_array((int)$t->term_id, $excluded_ids, true);
                                    });

                                    foreach ($items as $term) {
                                        if (!($term instanceof WP_Term)) continue;
                                        
                                        // Skip excluded categories (double check)
                                        if (in_array((int)$term->term_id, $excluded_ids, true)) continue;

                                        $display_count = (int) $aggregate_count($term->term_id);
                                        
                                        // Skip if count is 0
                                        if ($display_count === 0) continue;

                                        $has_kids = $has_instock_children($term->term_id);
                                        $is_selected = false;
                                        $is_ancestor = false;
                                        if (!empty($selected_terms)) {
                                            foreach ($selected_terms as $sel) {
                                                if ($term->term_id === $sel->term_id) { $is_selected = true; }
                                                if (term_is_ancestor_of($term->term_id, $sel->term_id, 'product_cat')) { $is_ancestor = true; }
                                            }
                                        }
                                        $is_open = $has_kids && ($is_selected || $is_ancestor);

                                        echo '<li class="category-item">';
                                        if ($has_kids) {
                                            echo '<button type="button" class="category-toggle" aria-expanded="' . ($is_open ? 'true' : 'false') . '" aria-controls="cat-children-' . (int) $term->term_id . '">';
                                            echo '<span class="toggle-icon" aria-hidden="true"></span>';
                                            echo '</button>';
                                        } else {
                                            echo '<span class="toggle-spacer"></span>';
                                        }
                                        
                                        echo '<label class="filter-option category-option">';
                                        echo '<input type="checkbox" name="category" value="' . esc_attr($term->slug) . '" ' . checked($is_selected, true, false) . ' />';
                                        echo '<span class="filter-option-label">' . esc_html($term->name) . ' <span class="filter-option-count">(' . $display_count . ')</span></span>';
                                        echo '</label>';
                                        if ($has_kids) {
                                            echo '<div id="cat-children-' . (int) $term->term_id . '" class="subcategory-branch"' . ($is_open ? '' : ' hidden') . '>';
                                            // Render children list
                                            $grand_children = get_terms(array(
                                                'taxonomy' => 'product_cat',
                                                'parent' => $term->term_id,
                                                'hide_empty' => false,
                                                'exclude' => $excluded_ids,
                                                'orderby' => 'name',
                                            ));
                                            if (!empty($grand_children) && !is_wp_error($grand_children)) {
                                                echo '<ul class="subcategory-list level-' . (int) ($level + 1) . '">';
                                                foreach ($grand_children as $child) {
                                                    // Skip excluded and zero count
                                                    if (in_array((int)$child->term_id, $excluded_ids, true)) continue;
                                                    
                                                    $child_count = (int) $aggregate_count($child->term_id);
                                                    if ($child_count === 0) continue;

                                                    $child_has_children = $has_instock_children($child->term_id);
                                                    $child_selected = false; $child_ancestor = false;
                                                    if (!empty($selected_terms)) {
                                                        foreach ($selected_terms as $sel) {
                                                            if ($child->term_id === $sel->term_id) { $child_selected = true; }
                                                            if (term_is_ancestor_of($child->term_id, $sel->term_id, 'product_cat')) { $child_ancestor = true; }
                                                        }
                                                    }
                                                    $child_open = $child_has_children && ($child_selected || $child_ancestor);

                                                    echo '<li class="category-item">';
                                                    if ($child_has_children) {
                                                        echo '<button type="button" class="category-toggle" aria-expanded="' . ($child_open ? 'true' : 'false') . '" aria-controls="cat-children-' . (int) $child->term_id . '">';
                                                        echo '<span class="toggle-icon" aria-hidden="true"></span>';
                                                        echo '</button>';
                                                    } else {
                                                        echo '<span class="toggle-spacer"></span>';
                                                    }
                                                    
                                                    echo '<label class="filter-option category-option">';
                                                    echo '<input type="checkbox" name="category" value="' . esc_attr($child->slug) . '" ' . checked($child_selected, true, false) . ' />';
                                                    echo '<span class="filter-option-label">' . esc_html($child->name) . ' <span class="filter-option-count">(' . $child_count . ')</span></span>';
                                                    echo '</label>';
                                                    if ($child_has_children) {
                                                        echo '<div id="cat-children-' . (int) $child->term_id . '" class="subcategory-branch"' . ($child_open ? '' : ' hidden') . '>';
                                                        // Deep levels
                                                        $render_branch($child, $level + 2);
                                                        echo '</div>';
                                                    }
                                                    echo '</li>';
                                                }
                                                echo '</ul>';
                                            }
                                            echo '</div>';
                                        }
                                        echo '</li>';
                                    }

                                    echo '</ul>';
                                };

                                // Render initial tree (level 0 uses array of top-level terms)
                                $render_branch($top_level, 0);
                                echo '</div>';
                            endif;
                            break;

                        case 'price':
                            // Get price range from database
                            $price_min = 0;
                            $price_max = 500; // Default max
                            
                            // Try to get actual price range
                            global $wpdb;
                            $context_product_ids = function_exists('eshop_get_current_context_product_ids')
                                ? eshop_get_current_context_product_ids()
                                : array();
                            $price_data = null;
                            if (!empty($context_product_ids)) {
                                $placeholders = implode(',', array_fill(0, count($context_product_ids), '%d'));
                                $sql = "
                                    SELECT MIN(CAST(pm.meta_value AS DECIMAL(10,2))) as min_price, 
                                           MAX(CAST(pm.meta_value AS DECIMAL(10,2))) as max_price
                                    FROM {$wpdb->postmeta} pm
                                    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                                    WHERE pm.meta_key = '_price'
                                      AND pm.meta_value != ''
                                      AND p.post_type = 'product'
                                      AND p.post_status = 'publish'
                                      AND p.ID IN ($placeholders)
                                ";
                                $price_data = $wpdb->get_row($wpdb->prepare($sql, $context_product_ids));
                            } else {
                                $price_data = $wpdb->get_row("
                                    SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price, 
                                           MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price
                                    FROM {$wpdb->postmeta}
                                    WHERE meta_key = '_price' AND meta_value != ''
                                ");
                            }
                            if ($price_data) {
                                $price_min = floor(floatval($price_data->min_price));
                                $price_max = ceil(floatval($price_data->max_price));
                            }
                            
                            // Current selected values
                            $current_min = $min_price > 0 ? $min_price : $price_min;
                            $current_max = $max_price > 0 ? $max_price : $price_max;
                        ?>
                            <div class="price-filter-slider">
                                <div class="price-slider-labels">
                                    <span class="price-label-min">€<span id="price-display-min"><?php echo esc_html($current_min); ?></span></span>
                                    <span class="price-label-max">€<span id="price-display-max"><?php echo esc_html($current_max); ?></span></span>
                                </div>
                                <div class="price-slider-track">
                                    <div class="price-slider-range" id="price-slider-range"></div>
                                    <input type="range" 
                                           id="price-slider-min" 
                                           class="price-slider" 
                                           min="<?php echo esc_attr($price_min); ?>" 
                                           max="<?php echo esc_attr($price_max); ?>" 
                                           value="<?php echo esc_attr($current_min); ?>" 
                                           step="1">
                                    <input type="range" 
                                           id="price-slider-max" 
                                           class="price-slider" 
                                           min="<?php echo esc_attr($price_min); ?>" 
                                           max="<?php echo esc_attr($price_max); ?>" 
                                           value="<?php echo esc_attr($current_max); ?>" 
                                           step="1">
                                </div>
                                <input type="hidden" id="filter-min-price" value="<?php echo esc_attr($current_min); ?>">
                                <input type="hidden" id="filter-max-price" value="<?php echo esc_attr($current_max); ?>">
                            </div>
                            
                            <style>
                            .price-filter-slider {
                                padding: 1rem 0;
                            }
                            .price-slider-labels {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 0.75rem;
                                font-size: 0.875rem;
                                font-weight: 500;
                                color: var(--ink, #1a1a1a);
                            }
                            .price-slider-track {
                                position: relative;
                                height: 6px;
                                background: #e5e5e5;
                                border-radius: 3px;
                                margin: 1rem 0;
                            }
                            .price-slider-range {
                                position: absolute;
                                height: 100%;
                                background: var(--pink, #e91e63);
                                border-radius: 3px;
                            }
                            .price-slider {
                                position: absolute;
                                width: 100%;
                                height: 6px;
                                background: transparent;
                                pointer-events: none;
                                -webkit-appearance: none;
                                top: 0;
                                left: 0;
                            }
                            .price-slider::-webkit-slider-thumb {
                                -webkit-appearance: none;
                                width: 20px;
                                height: 20px;
                                background: var(--pink, #e91e63);
                                border-radius: 50%;
                                cursor: pointer;
                                pointer-events: auto;
                                border: 3px solid white;
                                box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                                margin-top: -7px;
                            }
                            .price-slider::-moz-range-thumb {
                                width: 20px;
                                height: 20px;
                                background: var(--pink, #e91e63);
                                border-radius: 50%;
                                cursor: pointer;
                                pointer-events: auto;
                                border: 3px solid white;
                                box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                            }
                            </style>
                            
                            <script>
                            (function() {
                                const minSlider = document.getElementById('price-slider-min');
                                const maxSlider = document.getElementById('price-slider-max');
                                const range = document.getElementById('price-slider-range');
                                const minDisplay = document.getElementById('price-display-min');
                                const maxDisplay = document.getElementById('price-display-max');
                                const minInput = document.getElementById('filter-min-price');
                                const maxInput = document.getElementById('filter-max-price');
                                
                                function updateSlider() {
                                    const min = parseInt(minSlider.value);
                                    const max = parseInt(maxSlider.value);
                                    const total = parseInt(minSlider.max) - parseInt(minSlider.min);
                                    
                                    // Prevent crossing
                                    if (min > max - 10) {
                                        if (this === minSlider) {
                                            minSlider.value = max - 10;
                                        } else {
                                            maxSlider.value = min + 10;
                                        }
                                    }
                                    
                                    const minVal = parseInt(minSlider.value);
                                    const maxVal = parseInt(maxSlider.value);
                                    
                                    // Update range bar
                                    const leftPercent = ((minVal - parseInt(minSlider.min)) / total) * 100;
                                    const rightPercent = ((maxVal - parseInt(minSlider.min)) / total) * 100;
                                    range.style.left = leftPercent + '%';
                                    range.style.width = (rightPercent - leftPercent) + '%';
                                    
                                    // Update displays
                                    minDisplay.textContent = minVal;
                                    maxDisplay.textContent = maxVal;
                                    
                                    // Update hidden inputs
                                    minInput.value = minVal;
                                    maxInput.value = maxVal;
                                }
                                
                                if (minSlider && maxSlider) {
                                    minSlider.addEventListener('input', updateSlider);
                                    maxSlider.addEventListener('input', updateSlider);
                                    updateSlider(); // Initial update
                                }
                            })();
                            </script>
                        <?php
                            break;

                        case 'attributes':
                            foreach ($attribute_taxonomies as $taxonomy):
                                $attr_name = 'pa_' . $taxonomy->attribute_name;
                                $terms = function_exists('eshop_get_available_attribute_terms') ? eshop_get_available_attribute_terms($attr_name) : array();
                                if (!empty($terms)):
                            ?>
                                <div class="filter-subsection">
                                    <h4 class="filter-subsection-title">
                                        <?php echo esc_html($taxonomy->attribute_label); ?>
                                    </h4>
                                    <?php foreach ($terms as $term): 
                                        $term_slug = is_array($term) ? $term['slug'] : $term->slug;
                                        $term_name = is_array($term) ? $term['name'] : $term->name;
                                        $term_count = is_array($term) ? intval($term['count']) : intval($term->count);
                                        
                                        // Skip if count is 0
                                        if ($term_count === 0) continue;
                                        
                                        $is_checked = false;
                                        if (isset($_GET[$attr_name])) {
                                            if (is_array($_GET[$attr_name])) {
                                                $is_checked = in_array($term_slug, array_map('sanitize_text_field', (array) $_GET[$attr_name]), true);
                                            } else {
                                                $vals = array_filter(array_map('trim', explode(',', sanitize_text_field($_GET[$attr_name]))));
                                                $is_checked = in_array($term_slug, $vals, true);
                                            }
                                        }
                                    ?>
                                        <label class="filter-option">
                                            <input type="checkbox" 
                                                   name="<?php echo esc_attr($attr_name); ?>"
                                                   value="<?php echo esc_attr($term_slug); ?>"
                                                   <?php checked($is_checked); ?>>
                                            <span class="filter-option-label">
                                                <?php echo esc_html($term_name); ?>
                                                <span class="filter-option-count">(<?php echo $term_count; ?>)</span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php 
                                endif;
                            endforeach;
                            break;

                        case 'availability':
                        ?>
                            <label class="filter-option">
                                <input type="checkbox" 
                                       name="on_sale" 
                                       value="1"
                                       <?php checked(isset($_GET['on_sale']) && $_GET['on_sale'] === '1'); ?>>
                                <span class="filter-option-label"><?php _e('On Sale', 'eshop-theme'); ?></span>
                            </label>
                            
                            <label class="filter-option">
                                <input type="checkbox" 
                                       name="stock_status" 
                                       value="instock"
                                       <?php checked(isset($_GET['stock_status']) && $_GET['stock_status'] === 'instock'); ?>>
                                <span class="filter-option-label"><?php _e('In Stock', 'eshop-theme'); ?></span>
                            </label>
                        <?php
                            break;
                    }
                    
            // Get buffered content
            $section_content = ob_get_clean();
            
            // Only render section if it has actual content (not just whitespace/comments)
            $clean_content = trim(preg_replace('/<!--.*?-->/s', '', $section_content));
            if (!empty($clean_content)):
            ?>
                <div class="filter-section" data-filter-section="<?php echo esc_attr($section_key); ?>">
                    <h3 class="filter-section-title"><?php echo esc_html($section_title); ?></h3>
                    <div class="filter-options">
                        <?php echo $section_content; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Filter Modal Footer -->
    <div class="filter-modal-footer">
        <button type="button" id="filter-clear-all" class="filter-clear-all">
            <?php _e('Clear All', 'eshop-theme'); ?>
        </button>
        <button type="button" id="filter-apply" class="filter-apply">
            <?php _e('Apply Filters', 'eshop-theme'); ?>
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sort dropdown functionality
    const sortSelect = document.getElementById('product-sort');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const url = new URL(window.location);
            url.searchParams.set('orderby', this.value);
            
            // Set default order for price sorting
            if (this.value === 'price') {
                url.searchParams.set('order', 'ASC');
            } else if (this.value === 'price-desc') {
                url.searchParams.set('orderby', 'price');
                url.searchParams.set('order', 'DESC');
            } else {
                url.searchParams.delete('order');
            }
            
            window.location.href = url.toString();
        });
    }
    
    // Add visual feedback for filter chips
    const filterChips = document.querySelectorAll('.filter-chip');
    filterChips.forEach(chip => {
        chip.addEventListener('mouseenter', function() {
            this.style.background = 'var(--pink)';
            this.style.color = 'white';
            this.style.borderColor = 'var(--pink)';
        });
        
        chip.addEventListener('mouseleave', function() {
            this.style.background = 'var(--tag)';
            this.style.color = 'var(--muted)';
            this.style.borderColor = 'transparent';
        });
    });
});
</script>

<?php
// Add some debug info for development
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<!-- Product Archive Filters Debug: ' . $total_products . ' products, Page ' . $current_page . ' -->';
}
?>
