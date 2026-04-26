<?php
/**
 * Register Gutenberg blocks.
 */

function twc_register_media_hero_block() {
    register_block_type( __DIR__ . '/../build/blocks/media-hero' );
}
add_action( 'init', 'twc_register_media_hero_block' );
