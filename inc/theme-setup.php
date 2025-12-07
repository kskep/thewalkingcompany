<?php
/**
 * Theme Setup Functions
 * 
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Start session for wishlist functionality (frontend only)
// We hook as early as possible on 'init' (priority 0) and perform multiple guards to
// avoid "headers already sent" warnings. This prevents session_start from running
// after output begins (common if a plugin echoes early).
if (!function_exists('eshop_maybe_start_session')) {
    add_action('init', 'eshop_maybe_start_session', 0);
    function eshop_maybe_start_session() {
        // Already active? bail.
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        // Skip in CLI, cron, or if headers already sent (would trigger warning).
        if ((defined('WP_CLI') && WP_CLI) || (defined('DOING_CRON') && DOING_CRON)) {
            return;
        }
        if (headers_sent()) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('eshop-theme: headers already sent before session_start; skipping session initialization.');
            }
            return;
        }
        // Avoid starting a session in the admin unless it's an AJAX request that needs it.
        if (is_admin() && !(defined('DOING_AJAX') && DOING_AJAX)) {
            return;
        }
        // Secure/httponly cookie params.
        $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        try {
            @session_start([
                'cookie_secure' => $secure,
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        } catch (Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('eshop-theme: session_start failed: ' . $e->getMessage());
            }
        }
    }
}

/**
 * Compile MO files early if missing
 * This runs before load_theme_textdomain to ensure translations work on first load
 */
function eshop_compile_translations_early() {
    $languages_dir = get_template_directory() . '/languages';
    $po_files = glob($languages_dir . '/*.po');
    
    if (empty($po_files)) {
        return;
    }
    
    foreach ($po_files as $po_file) {
        $mo_file = str_replace('.po', '.mo', $po_file);
        
        // Skip if MO already exists and is newer than PO
        if (file_exists($mo_file) && filemtime($mo_file) >= filemtime($po_file)) {
            continue;
        }
        
        // Skip if PO file is not readable
        if (!is_readable($po_file)) {
            continue;
        }
        
        // Load WordPress POMO classes
        if (!class_exists('PO')) {
            require_once ABSPATH . 'wp-includes/pomo/po.php';
        }
        if (!class_exists('MO')) {
            require_once ABSPATH . 'wp-includes/pomo/mo.php';
        }
        
        // Compile PO to MO
        $po = new PO();
        if ($po->import_from_file($po_file)) {
            $mo = new MO();
            $mo->set_header('Project-Id-Version', $po->get_header('Project-Id-Version'));
            foreach ($po->entries as $entry) {
                $mo->add_entry($entry);
            }
            $mo->export_to_file($mo_file);
        }
    }
}

/**
 * Theme Setup
 */
function eshop_theme_setup() {
    // Compile MO files early if missing (before loading textdomain)
    eshop_compile_translations_early();
    
    // Make theme available for translation
    // Translations can be filed in the /languages/ directory
    load_theme_textdomain('eshop-theme', get_template_directory() . '/languages');
    
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

    // Add custom image sizes for better quality
    add_image_size('product-thumbnail-hq', 400, 400, true); // High quality product thumbnails
    add_image_size('product-medium-hq', 600, 600, true);    // Medium high quality
    add_image_size('product-large-hq', 800, 800, true);     // Large high quality
    add_image_size('category-thumb', 120, 80, true);        // Category thumbnails for mega menu
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'eshop-theme'),
        'footer-main' => __('Footer Main Menu', 'eshop-theme'),
        'footer-account' => __('Footer Account Menu', 'eshop-theme'),
    ));
}
add_action('after_setup_theme', 'eshop_theme_setup');

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
        'name' => __('Footer Widget Area', 'eshop-theme'),
        'id' => 'footer-widgets',
        'description' => __('Add footer widgets here.', 'eshop-theme'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));

    // Shop Filters widget area for reusable filters modal/component
    register_sidebar(array(
        'name' => __('Shop Filters', 'eshop-theme'),
        'id' => 'shop-filters',
        'description' => __('Widgets shown inside the shop filters modal.', 'eshop-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title text-sm font-semibold mb-3">',
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
 * Admin notice for missing translation compilation
 */
function eshop_translation_admin_notice() {
    // Only show to admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if we have .po files but missing .mo files
    $languages_dir = get_template_directory() . '/languages';
    $po_files = glob($languages_dir . '/*.po');
    
    if (!empty($po_files)) {
        eshop_ensure_mo_files_exist($po_files);
        foreach ($po_files as $po_file) {
            $mo_file = str_replace('.po', '.mo', $po_file);
            if (!file_exists($mo_file)) {
                $locale = basename($po_file, '.po');
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong><?php esc_html_e('Translation Compilation Required', 'eshop-theme'); ?></strong></p>
                    <p>
                        <?php
                        /* translators: %s: locale code (e.g., el_GR) */
                        printf(
                            esc_html__('Translation file for %s needs to be compiled. Please install and use Poedit, Loco Translate plugin, or WP-CLI to compile the .po file to .mo format.', 'eshop-theme'),
                            '<code>' . esc_html($locale) . '</code>'
                        );
                        ?>
                    </p>
                    <p>
                        <a href="https://poedit.net/" target="_blank" class="button button-primary"><?php esc_html_e('Download Poedit', 'eshop-theme'); ?></a>
                        <a href="<?php echo esc_url(admin_url('plugin-install.php?s=loco%20translate&tab=search&type=term')); ?>" class="button"><?php esc_html_e('Install Loco Translate Plugin', 'eshop-theme'); ?></a>
                        <a href="<?php echo esc_url(get_template_directory_uri() . '/languages/GREEK_SETUP_INSTRUCTIONS.txt'); ?>" target="_blank" class="button"><?php esc_html_e('View Instructions', 'eshop-theme'); ?></a>
                    </p>
                </div>
                <?php
                break; // Only show one notice
            }
        }
    }
}
add_action('admin_notices', 'eshop_translation_admin_notice');

/**
 * Compile missing MO files from existing PO sources.
 * Tries to auto-generate translations so Loco/Poedit are optional.
 *
 * @param array $po_files List of PO file paths.
 * @return void
 */
function eshop_ensure_mo_files_exist($po_files) {
    if (empty($po_files) || !is_array($po_files)) {
        return;
    }

    if (!class_exists('PO')) {
        require_once ABSPATH . 'wp-includes/pomo/po.php';
    }

    if (!class_exists('MO')) {
        require_once ABSPATH . 'wp-includes/pomo/mo.php';
    }

    foreach ($po_files as $po_file) {
        $mo_file = str_replace('.po', '.mo', $po_file);

        if (file_exists($mo_file) || !is_readable($po_file)) {
            continue;
        }

        $po = new PO();
        if (!$po->import_from_file($po_file)) {
            continue;
        }

        $mo = new MO();
        $mo->strings = $po->strings;
        $mo->headers = $po->headers;

        if (!$mo->export_to_file($mo_file)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf('eshop-theme: Failed to compile MO file for %s', $po_file));
            }
        }
    }
}

if (!function_exists('eshop_estimated_reading_time')) {
    /**
     * Calculate an estimated reading time for long-form pages.
     * Roughly based on 200 words per minute which aligns with WP handbook guidance.
     *
     * @param int|null $post_id Optional post ID. Defaults to current post.
     * @return int Estimated minutes rounded up. Minimum value is 0 when content is empty.
     */
    function eshop_estimated_reading_time($post_id = null) {
        $post_id = $post_id ? absint($post_id) : get_the_ID();

        if (!$post_id) {
            return 0;
        }

        $content = get_post_field('post_content', $post_id);

        if (empty($content)) {
            return 0;
        }

        $stripped = wp_strip_all_tags(strip_shortcodes($content));
        $word_count = str_word_count($stripped);

        if ($word_count <= 0) {
            return 0;
        }

        return (int) max(1, ceil($word_count / 200));
    }
}