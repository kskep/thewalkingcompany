<?php
/**
 * E-Shop Theme Functions
 * 
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 */
function eshop_theme_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Add WooCommerce support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'eshop-theme'),
        'footer' => __('Footer Menu', 'eshop-theme'),
    ));
}
add_action('after_setup_theme', 'eshop_theme_setup');

/**
 * Enqueue Scripts and Styles
 */
function eshop_theme_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('eshop-theme-style', get_stylesheet_uri(), array(), '1.0.0');
    // Modular CSS
    wp_enqueue_style('eshop-base', get_template_directory_uri() . '/css/base.css', array('eshop-theme-style'), '1.0.0');
    wp_enqueue_style('eshop-component-hero', get_template_directory_uri() . '/css/components.hero-slider.css', array('eshop-theme-style'), '1.0.0');
    if (is_front_page()) {
        wp_enqueue_style('eshop-page-front', get_template_directory_uri() . '/css/pages.front.css', array('eshop-theme-style'), '1.0.0');
    }
    
    // Enqueue Font Awesome for icons
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    
    // Enqueue Feather Icons
    wp_enqueue_style('feather-icons', 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.css', array(), '4.29.0');

    // Swiper slider CSS (modern slider)
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), '10.3.1');
    
    // Enqueue Heroicons (via CDN)
    wp_enqueue_script('heroicons', 'https://cdn.jsdelivr.net/npm/heroicons@2.0.18/24/outline/index.js', array(), '2.0.18', true);

    // GSAP for animations
    wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js', array(), '3.12.5', true);
    
    // Swiper slider JS
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), '10.3.1', true);
    
    // Enqueue custom JavaScript
    wp_enqueue_script('eshop-theme-script', get_template_directory_uri() . '/js/theme.js', array('jquery', 'gsap', 'swiper'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('eshop-theme-script', 'eshop_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eshop_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'eshop_theme_scripts');

/**
 * Admin (wp-admin) styles for ACF usability
 */
function eshop_admin_styles($hook_suffix) {
    // Load on all admin screens; adjust if needed to specific post types
    wp_enqueue_style('eshop-admin-acf', get_template_directory_uri() . '/css/admin.acf.css', array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'eshop_admin_styles');

/**
 * Add custom colors to Gutenberg
 */
function eshop_theme_gutenberg_colors() {
    add_theme_support('editor-color-palette', array(
        array(
            'name' => __('Primary Pink', 'eshop-theme'),
            'slug' => 'primary-pink',
            'color' => '#EE81B3',
        ),
        array(
            'name' => __('Primary Pink Light', 'eshop-theme'),
            'slug' => 'primary-pink-light',
            'color' => '#F2A1C7',
        ),
        array(
            'name' => __('Primary Pink Dark', 'eshop-theme'),
            'slug' => 'primary-pink-dark',
            'color' => '#E55A9F',
        ),
        array(
            'name' => __('Dark Gray', 'eshop-theme'),
            'slug' => 'dark-gray',
            'color' => '#2D3748',
        ),
        array(
            'name' => __('Darker Gray', 'eshop-theme'),
            'slug' => 'darker-gray',
            'color' => '#1A202C',
        ),
        array(
            'name' => __('White', 'eshop-theme'),
            'slug' => 'white',
            'color' => '#FFFFFF',
        ),
    ));
}
add_action('after_setup_theme', 'eshop_theme_gutenberg_colors');

/**
 * Register Widget Areas
 */
function eshop_theme_widgets_init() {
    register_sidebar(array(
        'name' => __('Sidebar', 'eshop-theme'),
        'id' => 'sidebar-1',
        'description' => __('Add widgets here.', 'eshop-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
    
    register_sidebar(array(
        'name' => __('Footer Widget Area', 'eshop-theme'),
        'id' => 'footer-widgets',
        'description' => __('Add footer widgets here.', 'eshop-theme'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'eshop_theme_widgets_init');

/**
 * Custom excerpt length
 */
function eshop_theme_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'eshop_theme_excerpt_length');

/**
 * Custom excerpt more
 */
function eshop_theme_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'eshop_theme_excerpt_more');

/**
 * Add custom body classes
 */
function eshop_theme_body_classes($classes) {
    if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
        $classes[] = 'woocommerce-page';
    }
    return $classes;
}
add_filter('body_class', 'eshop_theme_body_classes');

/**
 * Remove page titles
 */
function eshop_remove_page_titles($title) {
    if (is_page() && !is_front_page()) {
        return '';
    }
    return $title;
}
add_filter('the_title', 'eshop_remove_page_titles');

/**
 * Wishlist Functionality
 */

// Initialize wishlist session
function eshop_init_wishlist() {
    if (!session_id()) {
        session_start();
    }
    if (!isset($_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'] = array();
    }
}
add_action('init', 'eshop_init_wishlist');

// Add to wishlist AJAX handler
function eshop_add_to_wishlist() {
    check_ajax_referer('eshop_nonce', 'nonce');
    
    $product_id = intval($_POST['product_id']);
    if (!$product_id) {
        wp_die();
    }
    
    if (!isset($_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'] = array();
    }
    
    if (!in_array($product_id, $_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'][] = $product_id;
        $action = 'added';
    } else {
        $_SESSION['eshop_wishlist'] = array_diff($_SESSION['eshop_wishlist'], array($product_id));
        $action = 'removed';
    }
    
    wp_send_json_success(array(
        'action' => $action,
        'count' => count($_SESSION['eshop_wishlist'])
    ));
}
add_action('wp_ajax_add_to_wishlist', 'eshop_add_to_wishlist');
add_action('wp_ajax_nopriv_add_to_wishlist', 'eshop_add_to_wishlist');

// Get wishlist count
function eshop_get_wishlist_count() {
    if (!isset($_SESSION['eshop_wishlist'])) {
        return 0;
    }
    return count($_SESSION['eshop_wishlist']);
}

// Check if product is in wishlist
function eshop_is_in_wishlist($product_id) {
    if (!isset($_SESSION['eshop_wishlist'])) {
        return false;
    }
    return in_array($product_id, $_SESSION['eshop_wishlist']);
}

// Get wishlist products
function eshop_get_wishlist_products() {
    if (!isset($_SESSION['eshop_wishlist']) || empty($_SESSION['eshop_wishlist'])) {
        return array();
    }
    return $_SESSION['eshop_wishlist'];
}

/**
 * Enhanced Cart Functions
 */

// Get cart fragment for AJAX updates
function eshop_cart_fragment($fragments) {
    ob_start();
    ?>
    <span class="cart-count <?php echo WC()->cart->get_cart_contents_count() > 0 ? '' : 'hidden'; ?>">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['.cart-count'] = ob_get_clean();
    
    ob_start();
    ?>
    <span class="cart-total">
        <?php echo WC()->cart->get_cart_total(); ?>
    </span>
    <?php
    $fragments['.cart-total'] = ob_get_clean();
    
    // Update entire minicart content
    ob_start();
    ?>
    <div class="minicart-items max-h-64 overflow-y-auto">
        <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
            <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                $product = $cart_item['data'];
                $product_id = $cart_item['product_id'];
                $quantity = $cart_item['quantity'];
            ?>
                <div class="minicart-item flex items-center space-x-3 py-3 border-b border-gray-100 last:border-b-0">
                    <div class="w-12 h-12 flex-shrink-0">
                        <?php echo $product->get_image(array(48, 48)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-dark truncate"><?php echo $product->get_name(); ?></h4>
                        <p class="text-xs text-gray-500"><?php echo sprintf('%s × %s', $quantity, wc_price($product->get_price())); ?></p>
                        <p class="text-sm text-primary font-semibold"><?php echo wc_price($product->get_price() * $quantity); ?></p>
                    </div>
                    <a href="<?php echo wc_get_cart_remove_url($cart_item_key); ?>" class="remove-from-cart text-gray-400 hover:text-red-500 transition-colors" data-cart-item-key="<?php echo $cart_item_key; ?>">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-gray-500 text-center py-8"><?php _e('Your cart is empty', 'eshop-theme'); ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
        <div class="mt-4 pt-3 border-t border-gray-200 space-y-2">
            <a href="<?php echo wc_get_cart_url(); ?>" class="block w-full text-center bg-gray-100 text-dark py-2 rounded-md hover:bg-gray-200 transition-colors duration-200">
                <?php _e('View Cart', 'eshop-theme'); ?>
            </a>
            <a href="<?php echo wc_get_checkout_url(); ?>" class="block w-full text-center bg-primary text-white py-2 rounded-md hover:bg-primary-dark transition-colors duration-200">
                <?php _e('Checkout', 'eshop-theme'); ?>
            </a>
        </div>
    <?php endif; ?>
    <?php
    $fragments['.minicart-dropdown .p-4'] = ob_get_clean();
    
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'eshop_cart_fragment');

/**
 * Account Menu Functions
 */

// Get account menu items
function eshop_get_account_menu_items() {
    $items = array();
    
    if (is_user_logged_in()) {
        $items['dashboard'] = array(
            'title' => __('Dashboard', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('dashboard')
        );
        $items['orders'] = array(
            'title' => __('Orders', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('orders')
        );
        $items['downloads'] = array(
            'title' => __('Downloads', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('downloads')
        );
        $items['edit-address'] = array(
            'title' => __('Addresses', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('edit-address')
        );
        $items['edit-account'] = array(
            'title' => __('Account Details', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('edit-account')
        );
        $items['customer-logout'] = array(
            'title' => __('Logout', 'eshop-theme'),
            'url' => wc_logout_url()
        );
    } else {
        $items['login'] = array(
            'title' => __('Login', 'eshop-theme'),
            'url' => wc_get_page_permalink('myaccount')
        );
        $items['register'] = array(
            'title' => __('Register', 'eshop-theme'),
            'url' => wc_get_page_permalink('myaccount')
        );
    }
    
    return $items;
}

/**
 * Display wishlist button
 */
function eshop_wishlist_button($product_id = null) {
    if (!$product_id) {
        global $product;
        $product_id = $product->get_id();
    }
    
    $is_in_wishlist = eshop_is_in_wishlist($product_id);
    $icon_class = $is_in_wishlist ? 'fas fa-heart' : 'far fa-heart';
    $button_class = $is_in_wishlist ? 'add-to-wishlist in-wishlist' : 'add-to-wishlist';
    
    ?>
    <button class="<?php echo $button_class; ?> p-2 text-gray-400 hover:text-primary transition-colors duration-200" data-product-id="<?php echo $product_id; ?>" title="<?php _e('Add to Wishlist', 'eshop-theme'); ?>">
        <i class="<?php echo $icon_class; ?>"></i>
    </button>
    <?php
}

// Add wishlist button to product loops
add_action('woocommerce_after_shop_loop_item', 'eshop_add_wishlist_to_loop', 15);
function eshop_add_wishlist_to_loop() {
    global $product;
    echo '<div class="product-actions flex items-center justify-between mt-2">';
    echo '<div class="flex-1"></div>';
    eshop_wishlist_button($product->get_id());
    echo '</div>';
}

// Add wishlist button to single product page
add_action('woocommerce_single_product_summary', 'eshop_add_wishlist_to_single', 35);
function eshop_add_wishlist_to_single() {
    global $product;
    echo '<div class="single-product-wishlist mt-4">';
    echo '<button class="add-to-wishlist inline-flex items-center text-gray-600 hover:text-primary transition-colors duration-200" data-product-id="' . $product->get_id() . '">';
    echo '<i class="' . (eshop_is_in_wishlist($product->get_id()) ? 'fas' : 'far') . ' fa-heart mr-2"></i>';
    echo __('Add to Wishlist', 'eshop-theme');
    echo '</button>';
    echo '</div>';
}

/**
 * Product Archive & Filter Functions
 */

// AJAX Product Filter Handler
function eshop_filter_products() {
    check_ajax_referer('eshop_nonce', 'nonce');
    
    $filters = $_POST['filters'];
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';
    
    // Build query args
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => wc_get_default_products_per_row() * wc_get_default_product_rows_per_page(),
        'paged' => $paged,
        'orderby' => $orderby,
        'meta_query' => array(),
        'tax_query' => array('relation' => 'AND')
    );
    
    // Price filter
    if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
        $price_meta_query = array(
            'key' => '_price',
            'type' => 'NUMERIC',
            'compare' => 'BETWEEN'
        );
        
        $min_price = !empty($filters['min_price']) ? floatval($filters['min_price']) : 0;
        $max_price = !empty($filters['max_price']) ? floatval($filters['max_price']) : PHP_INT_MAX;
        
        $price_meta_query['value'] = array($min_price, $max_price);
        $args['meta_query'][] = $price_meta_query;
    }
    
    // Category filter
    if (!empty($filters['product_cat'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $filters['product_cat'],
            'operator' => 'IN'
        );
    }
    
    // Attribute filters
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    foreach ($attribute_taxonomies as $attribute) {
        $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);
        if (!empty($filters[$taxonomy])) {
            $args['tax_query'][] = array(
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $filters[$taxonomy],
                'operator' => 'IN'
            );
        }
    }
    
    // Stock status filter
    if (!empty($filters['stock_status'])) {
        $args['meta_query'][] = array(
            'key' => '_stock_status',
            'value' => $filters['stock_status'],
            'compare' => 'IN'
        );
    }
    
    // On sale filter
    if (!empty($filters['on_sale'])) {
        $args['meta_query'][] = array(
            'key' => '_sale_price',
            'value' => '',
            'compare' => '!='
        );
    }
    
    // Execute query
    $query = new WP_Query($args);
    
    ob_start();
    
    if ($query->have_posts()) {
        echo '<div class="products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">';
        
        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }
        
        echo '</div>';
        
        // Pagination
        if ($query->max_num_pages > 1) {
            echo '<div class="pagination-wrapper mt-8">';
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'show_all' => false,
                'end_size' => 1,
                'mid_size' => 2,
                'prev_next' => true,
                'prev_text' => '<i class="fas fa-chevron-left"></i>',
                'next_text' => '<i class="fas fa-chevron-right"></i>',
                'type' => 'list',
                'class' => 'pagination'
            ));
            echo '</div>';
        }
    } else {
        echo '<div class="no-products-found text-center py-12">';
        echo '<div class="mb-6"><i class="fas fa-search text-6xl text-gray-300"></i></div>';
        echo '<h3 class="text-2xl font-semibold text-dark mb-4">' . __('No products found', 'eshop-theme') . '</h3>';
        echo '<p class="text-gray-600 mb-6">' . __('Try adjusting your filters or search terms', 'eshop-theme') . '</p>';
        echo '</div>';
    }
    
    $products_html = ob_get_clean();
    
    // Get result count
    $result_count = sprintf(
        _n('Showing %d result', 'Showing %d results', $query->found_posts, 'eshop-theme'),
        $query->found_posts
    );
    
    wp_reset_postdata();
    
    wp_send_json_success(array(
        'products' => $products_html,
        'result_count' => $result_count,
        'found_posts' => $query->found_posts,
        'max_pages' => $query->max_num_pages
    ));
}
add_action('wp_ajax_filter_products', 'eshop_filter_products');
add_action('wp_ajax_nopriv_filter_products', 'eshop_filter_products');

// Quick Add to Cart AJAX
function eshop_quick_add_to_cart() {
    check_ajax_referer('eshop_nonce', 'nonce');
    
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']) ?: 1;
    
    $result = WC()->cart->add_to_cart($product_id, $quantity);
    
    if ($result) {
        wp_send_json_success(array(
            'message' => __('Product added to cart!', 'eshop-theme'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total()
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Failed to add product to cart.', 'eshop-theme')
        ));
    }
}
add_action('wp_ajax_quick_add_to_cart', 'eshop_quick_add_to_cart');
add_action('wp_ajax_nopriv_quick_add_to_cart', 'eshop_quick_add_to_cart');

// Customize WooCommerce ordering options
function eshop_custom_woocommerce_catalog_orderby($orderby_options) {
    $orderby_options = array(
        'menu_order' => __('Default sorting', 'eshop-theme'),
        'popularity' => __('Sort by popularity', 'eshop-theme'),
        'rating' => __('Sort by average rating', 'eshop-theme'),
        'date' => __('Sort by latest', 'eshop-theme'),
        'price' => __('Sort by price: low to high', 'eshop-theme'),
        'price-desc' => __('Sort by price: high to low', 'eshop-theme')
    );
    return $orderby_options;
}
add_filter('woocommerce_default_catalog_orderby_options', 'eshop_custom_woocommerce_catalog_orderby');
add_filter('woocommerce_catalog_orderby', 'eshop_custom_woocommerce_catalog_orderby');

// Remove default WooCommerce styles for custom styling
function eshop_dequeue_woocommerce_styles($enqueue_styles) {
    unset($enqueue_styles['woocommerce-general']);
    unset($enqueue_styles['woocommerce-layout']);
    unset($enqueue_styles['woocommerce-smallscreen']);
    return $enqueue_styles;
}
add_filter('woocommerce_enqueue_styles', 'eshop_dequeue_woocommerce_styles');

// Custom product loop add to cart button
function eshop_custom_loop_add_to_cart() {
    global $product;
    
    if (!$product->is_purchasable() || !$product->is_in_stock()) {
        return;
    }
    
    $button_text = $product->is_type('simple') ? __('Add to Cart', 'eshop-theme') : __('Select Options', 'eshop-theme');
    $button_class = $product->is_type('simple') ? 'add-to-cart-simple' : 'select-options';
    
    echo sprintf(
        '<a href="%s" class="button %s w-full bg-primary text-white py-2 px-4 text-sm font-medium hover:bg-primary-dark transition-colors text-center" data-product-id="%s">%s</a>',
        $product->is_type('simple') ? '#' : get_permalink(),
        $button_class,
        $product->get_id(),
        $button_text
    );
}

// Replace default add to cart button
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
add_action('woocommerce_after_shop_loop_item', 'eshop_custom_loop_add_to_cart', 10);