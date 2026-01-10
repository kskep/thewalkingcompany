<?php
/**
 * Front Page Meta Boxes (no ACF)
 * Adds rich UI on the page assigned as "Front page" only
 */

if (!defined('ABSPATH')) { exit; }

function eshop_is_editing_front_page_screen($screen = null) {
    if (!is_admin()) return false;
    if (!$screen) { $screen = get_current_screen(); }
    if (!$screen || $screen->id !== 'page') return false;

    $front_id = (int) get_option('page_on_front');
    if (!$front_id) return false;

    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : (isset($_POST['post_ID']) ? (int) $_POST['post_ID'] : 0);
    return $post_id === $front_id;
}

add_action('add_meta_boxes', function() {
    $front_id = (int) get_option('page_on_front');
    if (!$front_id) return;

    $post_id = isset($_GET['post']) ? (int) $_GET['post'] : (isset($_POST['post_ID']) ? (int) $_POST['post_ID'] : 0);
    if ($post_id !== $front_id) return;

    add_meta_box(
        'eshop_front_hero',
        __('Front Page: Hero Slider', 'eshop-theme'),
        'eshop_render_front_hero_metabox',
        'page',
        'normal',
        'high'
    );

    add_meta_box(
        'eshop_front_banners',
        __('Front Page: Banner Rows', 'eshop-theme'),
        'eshop_render_front_banners_metabox',
        'page',
        'normal',
        'default'
    );
});

function eshop_render_front_hero_metabox($post) {
    wp_nonce_field('eshop_front_meta_save', 'eshop_front_meta_nonce');
    $desktop = get_post_meta($post->ID, 'eshop_hero_desktop_slides', true);
    $mobile  = get_post_meta($post->ID, 'eshop_hero_mobile_slides', true);
    $desktop = is_array($desktop) ? $desktop : array();
    $mobile  = is_array($mobile) ? $mobile : array();
    ?>
    <div class="eshop-meta hero">
        <p class="description"><?php echo esc_html__('Add hero slides. You can drag to reorder. If Mobile is empty, Desktop slides will be used on mobile.', 'eshop-theme'); ?></p>
        <div class="eshop-repeater" data-name="eshop_hero_desktop_slides">
            <h4><?php esc_html_e('Desktop slides', 'eshop-theme'); ?></h4>
            <ul class="eshop-repeater-list" data-type="slide">
                <?php foreach ($desktop as $idx => $s) :
                    $url = isset($s['url']) ? esc_url($s['url']) : '';
                    $alt = isset($s['alt']) ? esc_attr($s['alt']) : '';
                ?>
                <li class="eshop-repeater-item">
                    <div class="thumb">
                        <img src="<?php echo $url ? $url : esc_url(includes_url('images/media/default.png')); ?>" alt="" />
                    </div>
                    <div class="fields">
                        <input type="hidden" class="img-url" name="eshop_hero_desktop_slides[<?php echo (int)$idx; ?>][url]" value="<?php echo $url; ?>" />
                        <button type="button" class="button select-image"><?php esc_html_e('Select Image', 'eshop-theme'); ?></button>
                        <input type="text" class="regular-text alt" placeholder="<?php esc_attr_e('Alt text (optional)', 'eshop-theme'); ?>" name="eshop_hero_desktop_slides[<?php echo (int)$idx; ?>][alt]" value="<?php echo $alt; ?>" />
                        <button type="button" class="button link-remove">&times;</button>
                    </div>
                    <span class="dashicons dashicons-move handle"></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <p><button type="button" class="button button-primary eshop-add-item" data-type="slide" data-target="eshop_hero_desktop_slides"><?php esc_html_e('Add Desktop Slide', 'eshop-theme'); ?></button></p>
        </div>
        <hr />
        <div class="eshop-repeater" data-name="eshop_hero_mobile_slides">
            <h4><?php esc_html_e('Mobile slides (optional)', 'eshop-theme'); ?></h4>
            <ul class="eshop-repeater-list" data-type="slide">
                <?php foreach ($mobile as $idx => $s) :
                    $url = isset($s['url']) ? esc_url($s['url']) : '';
                    $alt = isset($s['alt']) ? esc_attr($s['alt']) : '';
                ?>
                <li class="eshop-repeater-item">
                    <div class="thumb">
                        <img src="<?php echo $url ? $url : esc_url(includes_url('images/media/default.png')); ?>" alt="" />
                    </div>
                    <div class="fields">
                        <input type="hidden" class="img-url" name="eshop_hero_mobile_slides[<?php echo (int)$idx; ?>][url]" value="<?php echo $url; ?>" />
                        <button type="button" class="button select-image"><?php esc_html_e('Select Image', 'eshop-theme'); ?></button>
                        <input type="text" class="regular-text alt" placeholder="<?php esc_attr_e('Alt text (optional)', 'eshop-theme'); ?>" name="eshop_hero_mobile_slides[<?php echo (int)$idx; ?>][alt]" value="<?php echo $alt; ?>" />
                        <button type="button" class="button link-remove">&times;</button>
                    </div>
                    <span class="dashicons dashicons-move handle"></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <p><button type="button" class="button eshop-add-item" data-type="slide" data-target="eshop_hero_mobile_slides"><?php esc_html_e('Add Mobile Slide', 'eshop-theme'); ?></button></p>
        </div>
    </div>
    <?php
}

function eshop_render_front_categories_metabox($post) {
    // Legacy function - no longer used but kept for backward compatibility
    echo '<p>' . esc_html__('This section has been replaced by the new Banner Rows system.', 'eshop-theme') . '</p>';
}

/**
 * Render flexible banner rows metabox
 */
function eshop_render_front_banners_metabox($post) {
    $banner_rows = get_post_meta($post->ID, 'eshop_banner_rows', true);
    $banner_rows = is_array($banner_rows) ? $banner_rows : array();
    
    // Migrate old category tiles if no banner rows exist
    if (empty($banner_rows)) {
        $old_tiles = get_post_meta($post->ID, 'eshop_category_tiles', true);
        if (!empty($old_tiles) && is_array($old_tiles)) {
            $migrated_banners = array();
            foreach ($old_tiles as $tile) {
                if (!empty($tile['image_url'])) {
                    $migrated_banners[] = array(
                        'image_url' => $tile['image_url'] ?? '',
                        'alt'       => $tile['alt'] ?? '',
                        'title'     => $tile['title'] ?? '',
                        'link'      => $tile['link'] ?? '',
                    );
                }
            }
            if (!empty($migrated_banners)) {
                $banner_rows = array(array('banners' => $migrated_banners));
            }
        }
    }
    ?>
    <div class="eshop-meta banner-rows">
        <p class="description">
            <?php echo esc_html__('Create flexible banner layouts. Each row can have 1-4 banners.', 'eshop-theme'); ?><br>
            <strong><?php echo esc_html__('1 banner = full width, 2 banners = 50/50, 3 banners = thirds, 4 banners = quarters', 'eshop-theme'); ?></strong>
        </p>
        
        <div id="eshop-banner-rows-container">
            <?php 
            if (!empty($banner_rows)) :
                foreach ($banner_rows as $row_idx => $row) :
                    $banners = isset($row['banners']) && is_array($row['banners']) ? $row['banners'] : array();
                    ?>
                    <div class="eshop-banner-row" data-row-index="<?php echo (int)$row_idx; ?>">
                        <div class="eshop-banner-row-header">
                            <span class="dashicons dashicons-move row-handle"></span>
                            <span class="row-label"><?php printf(esc_html__('Row %d', 'eshop-theme'), $row_idx + 1); ?> 
                                <em class="banner-count">(<?php echo count($banners); ?> <?php echo count($banners) === 1 ? esc_html__('banner', 'eshop-theme') : esc_html__('banners', 'eshop-theme'); ?>)</em>
                            </span>
                            <div class="row-actions">
                                <button type="button" class="button button-small add-banner-to-row" <?php echo count($banners) >= 4 ? 'disabled' : ''; ?>>
                                    <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Banner', 'eshop-theme'); ?>
                                </button>
                                <button type="button" class="button button-small button-link-delete remove-row">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                                <button type="button" class="button button-small toggle-row">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>
                            </div>
                        </div>
                        <div class="eshop-banner-row-content">
                            <div class="eshop-banners-grid columns-<?php echo count($banners); ?>">
                                <?php foreach ($banners as $banner_idx => $banner) :
                                    $img = isset($banner['image_url']) ? esc_url($banner['image_url']) : '';
                                    $alt = isset($banner['alt']) ? esc_attr($banner['alt']) : '';
                                    $title = isset($banner['title']) ? esc_attr($banner['title']) : '';
                                    $link = isset($banner['link']) ? esc_url($banner['link']) : '';
                                ?>
                                <div class="eshop-banner-item" data-banner-index="<?php echo (int)$banner_idx; ?>">
                                    <div class="banner-preview">
                                        <img src="<?php echo $img ? $img : esc_url(includes_url('images/media/default.png')); ?>" alt="" />
                                        <button type="button" class="button-link remove-banner" title="<?php esc_attr_e('Remove Banner', 'eshop-theme'); ?>">
                                            <span class="dashicons dashicons-no-alt"></span>
                                        </button>
                                    </div>
                                    <div class="banner-fields">
                                        <input type="hidden" class="banner-image-url" 
                                               name="eshop_banner_rows[<?php echo (int)$row_idx; ?>][banners][<?php echo (int)$banner_idx; ?>][image_url]" 
                                               value="<?php echo $img; ?>" />
                                        <button type="button" class="button select-banner-image"><?php esc_html_e('Select Image', 'eshop-theme'); ?></button>
                                        <input type="text" class="regular-text banner-alt" 
                                               placeholder="<?php esc_attr_e('Alt text', 'eshop-theme'); ?>" 
                                               name="eshop_banner_rows[<?php echo (int)$row_idx; ?>][banners][<?php echo (int)$banner_idx; ?>][alt]" 
                                               value="<?php echo $alt; ?>" />
                                        <input type="text" class="regular-text banner-title" 
                                               placeholder="<?php esc_attr_e('Title (hover text)', 'eshop-theme'); ?>" 
                                               name="eshop_banner_rows[<?php echo (int)$row_idx; ?>][banners][<?php echo (int)$banner_idx; ?>][title]" 
                                               value="<?php echo $title; ?>" />
                                        <input type="url" class="regular-text banner-link" 
                                               placeholder="<?php esc_attr_e('Link URL', 'eshop-theme'); ?>" 
                                               name="eshop_banner_rows[<?php echo (int)$row_idx; ?>][banners][<?php echo (int)$banner_idx; ?>][link]" 
                                               value="<?php echo $link; ?>" />
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                endforeach;
            endif;
            ?>
        </div>
        
        <p style="margin-top: 16px;">
            <button type="button" class="button button-primary" id="eshop-add-banner-row">
                <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                <?php esc_html_e('Add New Row', 'eshop-theme'); ?>
            </button>
        </p>
        
        <!-- Template for new row -->
        <script type="text/html" id="tmpl-eshop-banner-row">
            <div class="eshop-banner-row" data-row-index="{{data.rowIndex}}">
                <div class="eshop-banner-row-header">
                    <span class="dashicons dashicons-move row-handle"></span>
                    <span class="row-label"><?php esc_html_e('Row', 'eshop-theme'); ?> {{data.rowNumber}} 
                        <em class="banner-count">(0 <?php esc_html_e('banners', 'eshop-theme'); ?>)</em>
                    </span>
                    <div class="row-actions">
                        <button type="button" class="button button-small add-banner-to-row">
                            <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Banner', 'eshop-theme'); ?>
                        </button>
                        <button type="button" class="button button-small button-link-delete remove-row">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                        <button type="button" class="button button-small toggle-row">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </div>
                </div>
                <div class="eshop-banner-row-content">
                    <div class="eshop-banners-grid columns-0">
                        <p class="no-banners-message"><?php esc_html_e('Click "Add Banner" to add banners to this row.', 'eshop-theme'); ?></p>
                    </div>
                </div>
            </div>
        </script>
        
        <!-- Template for new banner -->
        <script type="text/html" id="tmpl-eshop-banner-item">
            <div class="eshop-banner-item" data-banner-index="{{data.bannerIndex}}">
                <div class="banner-preview">
                    <img src="<?php echo esc_url(includes_url('images/media/default.png')); ?>" alt="" />
                    <button type="button" class="button-link remove-banner" title="<?php esc_attr_e('Remove Banner', 'eshop-theme'); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="banner-fields">
                    <input type="hidden" class="banner-image-url" 
                           name="eshop_banner_rows[{{data.rowIndex}}][banners][{{data.bannerIndex}}][image_url]" 
                           value="" />
                    <button type="button" class="button select-banner-image"><?php esc_html_e('Select Image', 'eshop-theme'); ?></button>
                    <input type="text" class="regular-text banner-alt" 
                           placeholder="<?php esc_attr_e('Alt text', 'eshop-theme'); ?>" 
                           name="eshop_banner_rows[{{data.rowIndex}}][banners][{{data.bannerIndex}}][alt]" 
                           value="" />
                    <input type="text" class="regular-text banner-title" 
                           placeholder="<?php esc_attr_e('Title (hover text)', 'eshop-theme'); ?>" 
                           name="eshop_banner_rows[{{data.rowIndex}}][banners][{{data.bannerIndex}}][title]" 
                           value="" />
                    <input type="url" class="regular-text banner-link" 
                           placeholder="<?php esc_attr_e('Link URL', 'eshop-theme'); ?>" 
                           name="eshop_banner_rows[{{data.rowIndex}}][banners][{{data.bannerIndex}}][link]" 
                           value="" />
                </div>
            </div>
        </script>
    </div>
    <?php
}

add_action('save_post_page', function($post_id, $post, $update) {
    // Only front page
    $front_id = (int) get_option('page_on_front');
    if ($post_id !== $front_id) return;
    if (!isset($_POST['eshop_front_meta_nonce']) || !wp_verify_nonce($_POST['eshop_front_meta_nonce'], 'eshop_front_meta_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Slides (desktop/mobile)
    $sanitize_slides = function($key) use ($post_id) {
        if (!isset($_POST[$key]) || !is_array($_POST[$key])) { update_post_meta($post_id, $key, array()); return; }
        $slides = array();
        foreach ($_POST[$key] as $row) {
            $url = isset($row['url']) ? esc_url_raw($row['url']) : '';
            if (!$url) continue;
            $alt = isset($row['alt']) ? sanitize_text_field($row['alt']) : '';
            $slides[] = array('url' => $url, 'alt' => $alt);
        }
        update_post_meta($post_id, $key, $slides);
    };
    $sanitize_slides('eshop_hero_desktop_slides');
    $sanitize_slides('eshop_hero_mobile_slides');

    // Banner Rows
    if (isset($_POST['eshop_banner_rows']) && is_array($_POST['eshop_banner_rows'])) {
        $banner_rows = array();
        foreach ($_POST['eshop_banner_rows'] as $row_data) {
            if (!isset($row_data['banners']) || !is_array($row_data['banners'])) continue;
            
            $banners = array();
            foreach ($row_data['banners'] as $banner) {
                $img = isset($banner['image_url']) ? esc_url_raw($banner['image_url']) : '';
                if (!$img) continue; // Skip empty banners
                
                $banners[] = array(
                    'image_url' => $img,
                    'alt'       => isset($banner['alt']) ? sanitize_text_field($banner['alt']) : '',
                    'title'     => isset($banner['title']) ? sanitize_text_field($banner['title']) : '',
                    'link'      => isset($banner['link']) ? esc_url_raw($banner['link']) : '',
                );
            }
            
            if (!empty($banners)) {
                $banner_rows[] = array('banners' => $banners);
            }
        }
        update_post_meta($post_id, 'eshop_banner_rows', $banner_rows);
    } else {
        update_post_meta($post_id, 'eshop_banner_rows', array());
    }
}, 10, 3);

// Admin assets only on the front page edit screen
add_action('admin_enqueue_scripts', function($hook) {
    $screen = get_current_screen();
    if (!eshop_is_editing_front_page_screen($screen)) return;

    wp_enqueue_style('eshop-admin-meta', get_template_directory_uri() . '/css/admin.meta.css', array(), filemtime(get_template_directory() . '/css/admin.meta.css'));
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('eshop-admin-slider-meta', get_template_directory_uri() . '/js/admin-slider-meta.js', array('jquery','jquery-ui-sortable'), filemtime(get_template_directory() . '/js/admin-slider-meta.js'), true);
});
