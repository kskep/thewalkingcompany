<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * @package E-Shop Theme
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		
		<!-- Custom Variations Display -->
		<div class="variations custom-variations">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				$attribute_label = wc_attribute_label( $attribute_name );
				$attribute_slug = str_replace( 'pa_', '', $attribute_name );
				$is_size_attribute = ( strpos( strtolower( $attribute_name ), 'size' ) !== false );
				?>
				
				<div class="variation-wrapper mb-6" data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
					<label class="variation-label block text-sm font-semibold text-gray-900 mb-3">
						<?php echo esc_html( $attribute_label ); ?>:
						<span class="selected-value text-gray-600 font-normal"></span>
					</label>
					
					<?php if ( $is_size_attribute ) : ?>
						<!-- Size Options as Circular Buttons -->
						<div class="size-options-single flex flex-wrap gap-3">
							<?php
							// Get size data with stock status
							$size_data = array();
							foreach ( $available_variations as $variation ) {
								$variation_obj = wc_get_product( $variation['variation_id'] );
								$attr_key = 'attribute_' . strtolower( $attribute_name );
								$attr_value = $variation['attributes'][ $attr_key ];
								
								if ( $attr_value && ! isset( $size_data[ $attr_value ] ) ) {
									$size_data[ $attr_value ] = array(
										'name' => $attr_value,
										'in_stock' => $variation_obj && $variation_obj->is_in_stock(),
										'variation_id' => $variation['variation_id']
									);
								}
							}
							
							// Sort sizes
							uksort( $size_data, function( $a, $b ) {
								if ( is_numeric( $a ) && is_numeric( $b ) ) {
									return (float) $a - (float) $b;
								}
								return strcmp( $a, $b );
							} );
							
							foreach ( $size_data as $size_value => $size_info ) :
								$classes = 'size-option-single w-12 h-12 rounded-full border-2 border-gray-300 flex items-center justify-center text-sm font-semibold transition-all duration-200 cursor-pointer';
								$classes .= ! $size_info['in_stock'] ? ' opacity-50 cursor-not-allowed bg-gray-100 text-gray-400' : ' bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400';
								$aria_disabled = $size_info['in_stock'] ? 'false' : 'true';
								$tabindex = $size_info['in_stock'] ? '0' : '-1';
								$title = $size_info['in_stock'] ? $size_value : sprintf( '%s - %s', $size_value, __( 'Out of Stock', 'woocommerce' ) );
							?>
								<span class="<?php echo esc_attr( $classes ); ?>"
									  role="button"
									  aria-disabled="<?php echo esc_attr( $aria_disabled ); ?>"
									  tabindex="<?php echo esc_attr( $tabindex ); ?>"
									  data-value="<?php echo esc_attr( $size_value ); ?>"
									  data-attribute="<?php echo esc_attr( $attribute_slug ); ?>"
									  data-in-stock="<?php echo $size_info['in_stock'] ? 'true' : 'false'; ?>"
									  title="<?php echo esc_attr( $title ); ?>">
									<?php echo esc_html( $size_value ); ?>
								</span>
							<?php endforeach; ?>
						</div>
						
						<!-- Hidden select for WooCommerce compatibility -->
						<select name="attribute_<?php echo esc_attr( $attribute_name ); ?>" 
								id="<?php echo esc_attr( $attribute_name ); ?>" 
								class="hidden" 
								data-attribute_name="attribute_<?php echo esc_attr( $attribute_name ); ?>" 
								data-show_option_none="<?php echo ( $required ) ? 'no' : 'yes'; ?>">
							<option value=""><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', __( 'Choose an option', 'woocommerce' ) ) ); ?></option>
							<?php
							if ( ! empty( $options ) ) {
								if ( $product && taxonomy_exists( $attribute_name ) ) {
									$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );
									foreach ( $terms as $term ) {
										if ( in_array( $term->slug, $options, true ) ) {
											echo '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ) ) . '</option>';
										}
									}
								} else {
									foreach ( $options as $option ) {
										echo '<option value="' . esc_attr( $option ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product ) ) . '</option>';
									}
								}
							}
							?>
						</select>
						
					<?php else : ?>
						<!-- Other Attributes as Buttons -->
						<div class="attribute-options-single flex flex-wrap gap-2">
							<?php foreach ( $options as $option ) : ?>
								<?php if ( empty( $option ) ) continue; ?>
								<span class="attribute-option-single px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400" 
									  data-value="<?php echo esc_attr( $option ); ?>" 
									  data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
									<?php echo esc_html( $option ); ?>
								</span>
							<?php endforeach; ?>
						</div>
						
						<!-- Hidden select for WooCommerce compatibility -->
						<select name="attribute_<?php echo esc_attr( $attribute_name ); ?>" 
								id="<?php echo esc_attr( $attribute_name ); ?>" 
								class="hidden" 
								data-attribute_name="attribute_<?php echo esc_attr( $attribute_name ); ?>" 
								data-show_option_none="<?php echo ( $required ) ? 'no' : 'yes'; ?>">
							<option value=""><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', __( 'Choose an option', 'woocommerce' ) ) ); ?></option>
							<?php
							if ( ! empty( $options ) ) {
								if ( $product && taxonomy_exists( $attribute_name ) ) {
									$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );
									foreach ( $terms as $term ) {
										if ( in_array( $term->slug, $options, true ) ) {
											echo '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ) ) . '</option>';
										}
									}
								} else {
									foreach ( $options as $option ) {
										echo '<option value="' . esc_attr( $option ) . '">' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product ) ) . '</option>';
									}
								}
							}
							?>
						</select>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="single_variation_wrap">
			<?php
			/**
			 * Hook: woocommerce_before_single_variation.
			 */
			do_action( 'woocommerce_before_single_variation' );

			/**
			 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
			 *
			 * @since 2.4.0
			 * @hooked woocommerce_single_variation - 10 Empty div that variation data gets printed in
			 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button
			 */
			do_action( 'woocommerce_single_variation' );

			/**
			 * Hook: woocommerce_after_single_variation.
			 */
			do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
?>
