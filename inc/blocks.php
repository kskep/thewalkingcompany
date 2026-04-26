<?php
/**
 * Register Gutenberg blocks.
 */

function twc_register_media_hero_block() {
    $dir = get_template_directory() . '/blocks/media-hero';
    $uri = get_template_directory_uri() . '/blocks/media-hero';

    wp_register_script(
        'twc-media-hero-editor',
        $uri . '/editor.js',
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
        filemtime( $dir . '/editor.js' )
    );

    wp_register_style(
        'twc-media-hero-editor',
        $uri . '/editor.css',
        array(),
        filemtime( $dir . '/editor.css' )
    );

    wp_register_style(
        'twc-media-hero',
        $uri . '/style.css',
        array(),
        filemtime( $dir . '/style.css' )
    );

    register_block_type( $dir, array(
        'editor_script' => 'twc-media-hero-editor',
        'editor_style'  => 'twc-media-hero-editor',
        'style'         => 'twc-media-hero',
        'render_callback' => 'twc_render_media_hero_block',
    ) );
}
add_action( 'init', 'twc_register_media_hero_block' );

function twc_render_media_hero_block( $attributes ) {
    ob_start();
    include get_template_directory() . '/blocks/media-hero/render.php';
    return ob_get_clean();
}
