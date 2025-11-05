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
        'eshop_front_categories',
        __('Front Page: Category Grid', 'eshop-theme'),
        'eshop_render_front_categories_metabox',
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
    $tiles = get_post_meta($post->ID, 'eshop_category_tiles', true);
    $tiles = is_array($tiles) ? $tiles : array();
    $fallback = array('Shoes','Clothes','Accessories','Bags');
    ?>
    <div class="eshop-meta tiles">
        <p class="description"><?php echo esc_html__('Configure the 4 tiles. Title is optional; defaults to a sensible label.', 'eshop-theme'); ?></p>
        <table class="widefat striped eshop-tiles-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?php esc_html_e('Image', 'eshop-theme'); ?></th>
                    <th><?php esc_html_e('Alt', 'eshop-theme'); ?></th>
                    <th><?php esc_html_e('Title', 'eshop-theme'); ?></th>
                    <th><?php esc_html_e('Link URL', 'eshop-theme'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i=0; $i<4; $i++) :
                    $row = isset($tiles[$i]) && is_array($tiles[$i]) ? $tiles[$i] : array();
                    $img = isset($row['image_url']) ? esc_url($row['image_url']) : '';
                    $alt = isset($row['alt']) ? esc_attr($row['alt']) : '';
                    $title = isset($row['title']) && $row['title'] !== '' ? esc_attr($row['title']) : $fallback[$i];
                    $link = isset($row['link']) ? esc_url($row['link']) : '';
                ?>
                <tr>
                    <td><?php echo (int) ($i+1); ?></td>
                    <td class="image-cell">
                        <div class="thumb"><img src="<?php echo $img ? $img : esc_url(includes_url('images/media/default.png')); ?>" alt="" /></div>
                        <input type="hidden" class="img-url" name="eshop_category_tiles[<?php echo (int)$i; ?>][image_url]" value="<?php echo $img; ?>" />
                        <button type="button" class="button select-image"><?php esc_html_e('Select Image', 'eshop-theme'); ?></button>
                    </td>
                    <td><input type="text" name="eshop_category_tiles[<?php echo (int)$i; ?>][alt]" value="<?php echo $alt; ?>" /></td>
                    <td><input type="text" name="eshop_category_tiles[<?php echo (int)$i; ?>][title]" value="<?php echo $title; ?>" /></td>
                    <td><input type="url" class="regular-text" placeholder="https://example.com/shop" name="eshop_category_tiles[<?php echo (int)$i; ?>][link]" value="<?php echo $link; ?>" /></td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
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

    // Tiles
    if (isset($_POST['eshop_category_tiles']) && is_array($_POST['eshop_category_tiles'])) {
        $tiles = array();
        for ($i=0; $i<4; $i++) {
            $row = isset($_POST['eshop_category_tiles'][$i]) ? $_POST['eshop_category_tiles'][$i] : array();
            $tiles[$i] = array(
                'image_url' => isset($row['image_url']) ? esc_url_raw($row['image_url']) : '',
                'alt'       => isset($row['alt']) ? sanitize_text_field($row['alt']) : '',
                'title'     => isset($row['title']) ? sanitize_text_field($row['title']) : '',
                'link'      => isset($row['link']) ? esc_url_raw($row['link']) : '',
            );
        }
        update_post_meta($post_id, 'eshop_category_tiles', $tiles);
    }
}, 10, 3);

// Admin assets only on the front page edit screen
add_action('admin_enqueue_scripts', function($hook) {
    $screen = get_current_screen();
    if (!eshop_is_editing_front_page_screen($screen)) return;

    wp_enqueue_style('eshop-admin-meta', get_template_directory_uri() . '/css/admin.meta.css', array(), '1.0.0');
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('eshop-admin-slider-meta', get_template_directory_uri() . '/js/admin-slider-meta.js', array('jquery','jquery-ui-sortable'), '1.0.0', true);
});
