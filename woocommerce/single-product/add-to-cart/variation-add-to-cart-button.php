<?php
/**
 * Single variation cart button - Elegant Magazine Style
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$is_in_wishlist = function_exists('eshop_is_in_wishlist') ? eshop_is_in_wishlist($product->get_id()) : false;
?>

<div class="woocommerce-variation-add-to-cart variations_button">
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<div class="cart-actions-wrapper">
		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		do_action( 'woocommerce_before_add_to_cart_quantity' );

		// Quantity hidden and forced to 1
		echo '<input type="hidden" name="quantity" value="1" />';

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button type="submit" class="single_add_to_cart_button button alt">
			<span><?php echo esc_html( $product->single_add_to_cart_text() ); ?></span>
		</button>

		<!-- Wishlist Button -->
		<button type="button" class="wishlist-action-btn <?php echo $is_in_wishlist ? 'in-wishlist' : ''; ?>" 
				data-product-id="<?php echo esc_attr($product->get_id()); ?>"
				data-nonce="<?php echo wp_create_nonce('eshop_nonce'); ?>"
				aria-label="<?php echo $is_in_wishlist ? __('Remove from wishlist', 'eshop-theme') : __('Add to wishlist', 'eshop-theme'); ?>">
			<svg viewBox="0 0 24 24" fill="<?php echo $is_in_wishlist ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
				<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
			</svg>
		</button>

		<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
		<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
		<input type="hidden" name="variation_id" class="variation_id" value="0" />
	</div>

	<!-- Stock Info -->
	<div class="stock-info-row">
		<span class="stock-indicator"></span>
		<span class="stock-text"><?php _e('Please select options', 'woocommerce'); ?></span>
	</div>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</div>

<script>
jQuery(function($) {
	// Update stock status based on variation selection
	$(document).on('found_variation', function(event, variation) {
		var $stockRow = $('.stock-info-row');
		var $stockIndicator = $stockRow.find('.stock-indicator');
		var $stockText = $stockRow.find('.stock-text');
		
		if (variation.is_in_stock) {
			$stockRow.removeClass('out-of-stock');
			$stockIndicator.css('background', '#10b981');
			$stockText.text(variation.availability_html ? $(variation.availability_html).text() : '<?php echo esc_js(__('In stock', 'woocommerce')); ?>');
		} else {
			$stockRow.addClass('out-of-stock');
			$stockIndicator.css('background', '#ef4444');
			$stockText.text('<?php echo esc_js(__('Out of stock', 'woocommerce')); ?>');
		}
	});
	
	// Reset stock status when variation is reset
	$(document).on('reset_data', function() {
		var $stockRow = $('.stock-info-row');
		var $stockIndicator = $stockRow.find('.stock-indicator');
		var $stockText = $stockRow.find('.stock-text');
		
		$stockRow.removeClass('out-of-stock');
		$stockIndicator.css('background', '#10b981');
		$stockText.text('<?php echo esc_js(__('Please select options', 'woocommerce')); ?>');
	});
});
</script>
