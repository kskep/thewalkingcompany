<?php
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>
<div class="page-shell">
    <!-- Enhanced Filter System -->
    <?php get_template_part('template-parts/components/product-archive-filters'); ?>

    <!-- Active Filters Section -->
    <?php
    $active_filters = array();
    
    // Get price filters
    $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
    $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
    
    if ($min_price > 0 || $max_price > 0) {
        $price_text = 'Price: ';
        if ($min_price > 0) $price_text .= wc_price($min_price);
        if ($max_price > 0) $price_text .= ' - ' . wc_price($max_price);
        $active_filters[] = array('type' => 'price', 'text' => $price_text, 'param' => 'price');
    }
    
    // Get category filters
    $selected_categories = isset($_GET['product_cat']) ? explode(',', $_GET['product_cat']) : array();
    foreach ($selected_categories as $cat_id) {
        $term = get_term($cat_id, 'product_cat');
        if ($term && !is_wp_error($term)) {
            $active_filters[] = array('type' => 'category', 'text' => $term->name, 'param' => 'cat-' . $cat_id);
        }
    }
    
    // Get sale filter
    if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
        $active_filters[] = array('type' => 'sale', 'text' => 'On Sale', 'param' => 'on_sale');
    }
    
    // Get custom attribute filters
    $attributes = array('pa_box', 'pa_color', 'pa_pick-pattern', 'pa_select-size', 'pa_size-selection');
    foreach ($attributes as $attr_name) {
        $attr_values = isset($_GET[$attr_name]) ? explode(',', $_GET[$attr_name]) : array();
        foreach ($attr_values as $attr_value) {
            $active_filters[] = array('type' => 'attribute', 'text' => $attr_value, 'param' => $attr_name . '-' . $attr_value);
        }
    }
    
    // Display active filters if any exist
    if (!empty($active_filters)) : ?>
        <div class="active-filters">
            <?php foreach ($active_filters as $filter) : ?>
                <span class="filter-chip" data-param="<?php echo esc_attr($filter['param']); ?>">
                    <?php echo esc_html($filter['text']); ?>
                </span>
            <?php endforeach; ?>
            <button class="reset-filters" type="button" id="clear-all-filters">Clear Filters</button>
        </div>
    <?php endif; ?>

    <section class="cards-grid">
        <?php
        if ( woocommerce_product_loop() ) {
            woocommerce_product_loop_start();

            if ( wc_get_loop_prop( 'total' ) ) {
                while ( have_posts() ) {
                    the_post();
                    do_action( 'woocommerce_shop_loop' );
                    wc_get_template_part( 'content', 'product' );
                }
            }

            woocommerce_product_loop_end();
            do_action( 'woocommerce_after_shop_loop' );
        } else {
            do_action( 'woocommerce_no_products_found' );
        }
        ?>
    </section>
</div>
<?php
get_footer( 'shop' );