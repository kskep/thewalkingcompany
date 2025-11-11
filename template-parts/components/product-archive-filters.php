<?php
/**
 * Product Archive Filters Component
 * 
 * Displays the complete filter toolbar as designed in the concept
 * Includes breadcrumb, result meta, sort dropdown, and active filter chips
 */

defined('ABSPATH') || exit;

// Get current query for metadata
global $wp_query;
$total_products = $wp_query->found_posts;
$current_page = max(1, get_query_var('paged'));
$per_page = wc_get_default_products_per_row() * wc_get_default_product_rows_per_page();
$start = ($current_page - 1) * $per_page + 1;
$end = min($current_page * $per_page, $total_products);

// Get current sort order
$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'menu_order';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

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

// Category filter
$current_cat = get_queried_object();
if ($current_cat && !is_wp_error($current_cat) && isset($current_cat->name)) {
    $current_filters[] = [
        'label' => $current_cat->name,
        'param' => 'cat-' . $current_cat->term_id
    ];
}

// Attribute filters
$attribute_taxonomies = wc_get_attribute_taxonomies();
if ($attribute_taxonomies) {
    foreach ($attribute_taxonomies as $taxonomy) {
        $attr_name = 'pa_' . $taxonomy->attribute_name;
        if (isset($_GET[$attr_name]) && is_array($_GET[$attr_name])) {
            foreach ($_GET[$attr_name] as $term_slug) {
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
}

// Generate breadcrumb
function get_product_archive_breadcrumb() {
    $breadcrumbs = [];
    
    // Home
    $breadcrumbs[] = [
        'label' => 'Home',
        'url' => home_url('/')
    ];
    
    // Shop
    $breadcrumbs[] = [
        'label' => 'Shop',
        'url' => get_permalink(wc_get_page_id('shop'))
    ];
    
    // Current category/taxonomy
    $current_term = get_queried_object();
    if ($current_term && !is_wp_error($current_term) && isset($current_term->term_id)) {
        // Get parent categories for hierarchy
        $parents = [];
        $parent = get_term($current_term->parent, $current_term->taxonomy);
        while ($parent && !is_wp_error($parent) && $parent->term_id) {
            array_unshift($parents, $parent);
            $parent = get_term($parent->parent, $current_term->taxonomy);
        }
        
        foreach ($parents as $parent_term) {
            $breadcrumbs[] = [
                'label' => $parent_term->name,
                'url' => get_term_link($parent_term)
            ];
        }
        
        // Current term
        $breadcrumbs[] = [
            'label' => $current_term->name,
            'url' => get_term_link($current_term)
        ];
    }
    
    return $breadcrumbs;
}

$breadcrumbs = get_product_archive_breadcrumb();

// Sort options
$sort_options = [
    'menu_order' => 'Sort — Featured',
    'popularity' => 'Sort — Popularity',
    'rating' => 'Sort — Rating',
    'date' => 'Sort — Newest',
    'price' => 'Sort — Price (Low)',
    'price-desc' => 'Sort — Price (High)'
];
?>

<section class="toolbar" role="region" aria-label="Product filtering and sorting">
    <!-- Top Section: Breadcrumb and Result Meta -->
    <div class="toolbar__top">
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

        <!-- Result Metadata and Sort -->
        <div class="result-meta" role="status" aria-live="polite">
            <!-- Product Count Description -->
            <span class="result-description">
                <?php
                if ($total_products > 0) {
                    echo sprintf(
                        _n(
                            '%s style curated for you',
                            '%s styles curated for you',
                            $total_products,
                            'your-textdomain'
                        ),
                        '<strong>' . number_format_i18n($total_products) . '</strong>'
                    );
                } else {
                    echo '<strong>0</strong> products found';
                }
                ?>
            </span>

            <span class="result-divider" aria-hidden="true">|</span>

            <!-- Page Range -->
            <span class="result-range">
                <?php
                if ($total_products > 0) {
                    printf(
                        __('Showing %d – %d', 'your-textdomain'),
                        $start,
                        $end
                    );
                }
                ?>
            </span>

            <!-- Sort Dropdown -->
            <div class="sort-container">
                <label for="product-sort" class="visually-hidden">
                    <?php _e('Sort products', 'your-textdomain'); ?>
                </label>
                <select id="product-sort" class="sort-select" aria-label="<?php _e('Sort products', 'your-textdomain'); ?>">
                    <?php foreach ($sort_options as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" 
                                <?php selected($orderby, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Active Filters Section -->
    <?php if (!empty($current_filters)): ?>
        <div class="active-filters" role="region" aria-label="<?php _e('Active filters', 'your-textdomain'); ?>">
            <?php foreach ($current_filters as $filter): ?>
                <button type="button" 
                        class="filter-chip" 
                        data-param="<?php echo esc_attr($filter['param']); ?>"
                        title="<?php printf(__('Remove %s filter', 'your-textdomain'), esc_attr($filter['label'])); ?>">
                    <span class="filter-chip-text"><?php echo esc_html($filter['label']); ?></span>
                    <span class="filter-chip-remove" aria-hidden="true">×</span>
                </button>
            <?php endforeach; ?>

            <!-- Clear All Filters -->
            <button type="button" 
                    id="clear-all-filters" 
                    class="reset-filters"
                    title="<?php _e('Clear all filters', 'your-textdomain'); ?>">
                <?php _e('Clear Filters', 'your-textdomain'); ?>
            </button>
        </div>
    <?php endif; ?>

    <!-- Filter Toggle Button (Mobile) -->
    <button type="button" 
            id="filter-toggle" 
            class="filter-toggle"
            aria-controls="filter-modal"
            aria-expanded="false">
        <span class="filter-toggle-icon" aria-hidden="true">⚙</span>
        <span class="filter-toggle-text"><?php _e('Filters', 'your-textdomain'); ?></span>
    </button>
</section>

<!-- Filter Modal Overlay -->
<div id="filter-modal-overlay" class="filter-modal-overlay" aria-hidden="true"></div>

<!-- Filter Modal -->
<div id="filter-modal" class="filter-modal" role="dialog" aria-modal="true" aria-labelledby="filter-modal-title" aria-hidden="true">
    <!-- Filter Modal Header -->
    <div class="filter-modal-header">
        <h2 id="filter-modal-title" class="filter-modal-title">
            <?php _e('Filter Products', 'your-textdomain'); ?>
        </h2>
        <button type="button" 
                id="filter-modal-close" 
                class="filter-modal-close"
                aria-label="<?php _e('Close filter modal', 'your-textdomain'); ?>">
            <span aria-hidden="true">×</span>
        </button>
    </div>

    <!-- Filter Modal Content -->
    <div class="filter-modal-content">
        <?php
        // Include filter sections
        $filter_sections = [
            'category' => __('Category', 'your-textdomain'),
            'price' => __('Price', 'your-textdomain'),
            'attributes' => __('Attributes', 'your-textdomain'),
            'availability' => __('Availability', 'your-textdomain')
        ];

        foreach ($filter_sections as $section_key => $section_title):
        ?>
            <div class="filter-section" data-filter-section="<?php echo esc_attr($section_key); ?>">
                <h3 class="filter-section-title"><?php echo esc_html($section_title); ?></h3>
                
                <div class="filter-options">
                    <?php
                    // Render appropriate filter section
                    switch ($section_key) {
                        case 'category':
                            $categories = get_terms([
                                'taxonomy' => 'product_cat',
                                'hide_empty' => true,
                            ]);
                            if (!empty($categories) && !is_wp_error($categories)):
                                $current_cat_id = $current_cat->term_id ?? 0;
                                foreach ($categories as $category):
                            ?>
                                <label class="filter-option">
                                    <input type="radio" 
                                           name="category" 
                                           value="<?php echo esc_attr($category->slug); ?>"
                                           <?php checked($current_cat_id, $category->term_id); ?>>
                                    <span class="filter-option-label">
                                        <?php echo esc_html($category->name); ?>
                                        <span class="filter-option-count">(<?php echo $category->count; ?>)</span>
                                    </span>
                                </label>
                            <?php 
                                endforeach;
                            endif;
                            break;

                        case 'price':
                            $price_ranges = [
                                ['min' => 0, 'max' => 50, 'label' => 'Under €50'],
                                ['min' => 50, 'max' => 100, 'label' => '€50 - €100'],
                                ['min' => 100, 'max' => 200, 'label' => '€100 - €200'],
                                ['min' => 200, 'max' => 0, 'label' => 'Over €200']
                            ];
                        ?>
                            <div class="price-filter">
                                <div class="price-inputs">
                                    <input type="number" 
                                           id="filter-min-price"
                                           class="price-input" 
                                           placeholder="€ Min" 
                                           min="0" 
                                           step="0.01"
                                           value="<?php echo esc_attr($min_price); ?>">
                                    <span class="price-separator">–</span>
                                    <input type="number" 
                                           id="filter-max-price"
                                           class="price-input" 
                                           placeholder="€ Max" 
                                           min="0" 
                                           step="0.01"
                                           value="<?php echo esc_attr($max_price); ?>">
                                </div>
                                <button type="button" id="price-filter-apply" class="price-filter-apply">
                                    <?php _e('Apply Price', 'your-textdomain'); ?>
                                </button>
                            </div>
                        <?php
                            break;

                        case 'attributes':
                            foreach ($attribute_taxonomies as $taxonomy):
                                $attr_name = 'pa_' . $taxonomy->attribute_name;
                                $terms = get_terms([
                                    'taxonomy' => $attr_name,
                                    'hide_empty' => true,
                                ]);
                                if (!empty($terms) && !is_wp_error($terms)):
                            ?>
                                <div class="filter-subsection">
                                    <h4 class="filter-subsection-title">
                                        <?php echo esc_html($taxonomy->attribute_label); ?>
                                    </h4>
                                    <?php foreach ($terms as $term): ?>
                                        <label class="filter-option">
                                            <input type="checkbox" 
                                                   name="<?php echo esc_attr($attr_name); ?>[]"
                                                   value="<?php echo esc_attr($term->slug); ?>"
                                                   <?php checked(in_array($term->slug, $_GET[$attr_name] ?? [])); ?>>
                                            <span class="filter-option-label">
                                                <?php echo esc_html($term->name); ?>
                                                <span class="filter-option-count">(<?php echo $term->count; ?>)</span>
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
                                <span class="filter-option-label"><?php _e('On Sale', 'your-textdomain'); ?></span>
                            </label>
                            
                            <label class="filter-option">
                                <input type="checkbox" 
                                       name="stock_status" 
                                       value="instock"
                                       <?php checked(isset($_GET['stock_status']) && $_GET['stock_status'] === 'instock'); ?>>
                                <span class="filter-option-label"><?php _e('In Stock', 'your-textdomain'); ?></span>
                            </label>
                        <?php
                            break;
                    }
                    ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Filter Modal Footer -->
    <div class="filter-modal-footer">
        <button type="button" id="filter-clear-all" class="filter-clear-all">
            <?php _e('Clear All', 'your-textdomain'); ?>
        </button>
        <button type="button" id="filter-apply" class="filter-apply">
            <?php _e('Apply Filters', 'your-textdomain'); ?>
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