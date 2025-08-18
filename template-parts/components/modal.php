<?php
/**
 * Reusable Modal Component
 *
 * Usage:
 * get_template_part('template-parts/components/modal', null, array(
 *   'id' => 'filters-modal',
 *   'title' => __('Filters', 'eshop-theme'),
 *   'content_cb' => function() { dynamic_sidebar('shop-filters'); }, // optional callback for content
 *   'content' => '', // fallback HTML string if no callback
 *   'size' => 'md', // sm|md|lg|xl - affects max-width
 *   'closeButton' => true,
 * ));
 */

if (!defined('ABSPATH')) { exit; }

$modal_id    = isset($args['id']) ? sanitize_html_class($args['id']) : 'modal-' . wp_unique_id();
$title       = isset($args['title']) ? $args['title'] : '';
$content_cb  = isset($args['content_cb']) && is_callable($args['content_cb']) ? $args['content_cb'] : null;
$content     = isset($args['content']) ? $args['content'] : '';
$size        = isset($args['size']) ? $args['size'] : 'md';
$closeButton = array_key_exists('closeButton', $args) ? (bool) $args['closeButton'] : true;

$size_class = 'max-w-lg';
switch ($size) {
    case 'sm': $size_class = 'max-w-sm'; break;
    case 'lg': $size_class = 'max-w-3xl'; break;
    case 'xl': $size_class = 'max-w-5xl'; break;
}
?>

<div id="<?php echo esc_attr($modal_id); ?>" class="eshop-modal fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr($modal_id); ?>-title">
    <div class="eshop-modal__overlay absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="eshop-modal__panel relative mx-auto w-full <?php echo esc_attr($size_class); ?> bg-white mt-10 sm:mt-20 border border-gray-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 id="<?php echo esc_attr($modal_id); ?>-title" class="text-lg font-semibold text-gray-900">
                <?php echo wp_kses_post($title); ?>
            </h3>
            <?php if ($closeButton) : ?>
                <button type="button" class="eshop-modal__close text-gray-500 hover:text-gray-700 p-1" aria-label="<?php esc_attr_e('Close', 'eshop-theme'); ?>">
                    <i class="fas fa-times"></i>
                </button>
            <?php endif; ?>
        </div>
        <div class="p-4">
            <?php
            if ($content_cb) {
                call_user_func($content_cb);
            } else {
                echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
        </div>
    </div>
</div>
