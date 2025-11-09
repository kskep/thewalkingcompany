<?php
/**
 * Simple product add to cart - Elegant Magazine Style
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.2.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

$is_in_wishlist = function_exists('eshop_is_in_wishlist') ? eshop_is_in_wishlist($product->get_id()) : false;

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<div class="cart-actions-wrapper">
			<!-- Quantity Selector -->
			<?php
			woocommerce_quantity_input(
				array(
					'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
					'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
					'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
				)
			);
			?>

			<!-- Add to Cart Button -->
			<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt">
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
		</div>

		<!-- Stock Info -->
		<?php if ( $product->is_in_stock() ) : ?>
			<div class="stock-info-row">
				<span class="stock-indicator"></span>
				<span><?php _e('In Stock - Ready to Ship', 'eshop-theme'); ?></span>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php else : ?>
	<div class="stock-info-row out-of-stock">
		<span class="stock-indicator"></span>
		<span><?php _e('Out of Stock', 'eshop-theme'); ?></span>
	</div>
<?php endif; ?>