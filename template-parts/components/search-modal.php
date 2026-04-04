<?php
/**
 * Search Modal Component
 * Desktop fullscreen search overlay with live AJAX results.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="search-modal" class="search-modal hidden" role="dialog" aria-label="<?php esc_attr_e('Αναζήτηση', 'eshop-theme'); ?>">
    <div class="search-modal__overlay"></div>
    <div class="search-modal__container">
        <div class="search-modal__header">
            <form class="search-modal__form" role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
                <i class="fas fa-search search-modal__icon"></i>
                <input type="search"
                       name="s"
                       class="search-modal__input"
                       placeholder="<?php esc_attr_e('Αναζήτηση προϊόντων, μαρκών, κατηγοριών...', 'eshop-theme'); ?>"
                       autocomplete="off"
                       aria-label="<?php esc_attr_e('Αναζήτηση προϊόντων', 'eshop-theme'); ?>" />
                <button type="button" class="search-modal__close" aria-label="<?php esc_attr_e('Κλείσιμο αναζήτησης', 'eshop-theme'); ?>">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
        <div class="search-modal__results">
            <div class="search-modal__empty">
                <p class="search-modal__hint"><?php _e('Εισαγάγετε κείμενο για αναζήτηση προϊόντων...', 'eshop-theme'); ?></p>
            </div>
        </div>
    </div>
</div>
