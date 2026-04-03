<?php
/**
 * Search Modal Component
 * Desktop fullscreen search overlay with live AJAX results.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="search-modal" class="search-modal hidden" role="dialog" aria-label="<?php esc_attr_e('Search', 'eshop-theme'); ?>">
    <div class="search-modal__overlay"></div>
    <div class="search-modal__container">
        <div class="search-modal__header">
            <form class="search-modal__form" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
                <i class="fas fa-search search-modal__icon"></i>
                <input type="search"
                       name="s"
                       class="search-modal__input"
                       placeholder="<?php esc_attr_e('Search products, brands, categories...', 'eshop-theme'); ?>"
                       autocomplete="off"
                       aria-label="<?php esc_attr_e('Search products', 'eshop-theme'); ?>" />
                <button type="button" class="search-modal__close" aria-label="<?php esc_attr_e('Close search', 'eshop-theme'); ?>">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
        <div class="search-modal__results">
            <div class="search-modal__empty">
                <p class="search-modal__hint"><?php _e('Start typing to search products...', 'eshop-theme'); ?></p>
            </div>
        </div>
    </div>
</div>
