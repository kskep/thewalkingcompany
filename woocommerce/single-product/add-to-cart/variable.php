<?php
/**
 * Variable product add to cart
 * Enhanced implementation with circular size buttons and modern UI
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		<!-- Enhanced Variations Display -->
		<div class="variations custom-variations">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				// Normalize attribute keys for WooCommerce
				$attr_key   = (strpos($attribute_name, 'attribute_') === 0) ? $attribute_name : 'attribute_' . $attribute_name;
				$taxonomy   = str_replace('attribute_', '', $attribute_name);
				$attribute_label = wc_attribute_label( $taxonomy );
				$attribute_slug = sanitize_title( $taxonomy );
				
				// Determine attribute type for styling
				$is_size_attribute = ( strpos( strtolower( $attribute_name ), 'size' ) !== false );
				$is_color_attribute = ( strpos( strtolower( $attribute_name ), 'color' ) !== false || strpos( strtolower( $attribute_name ), 'colour' ) !== false );
				?>
				
				<div class="variation-wrapper" data-attribute="<?php echo esc_attr( $attr_key ); ?>">
					<label class="block-label variation-label">
						<?php echo esc_html( $attribute_label ); ?>
						<span class="selected-value"></span>
					</label>
					
					<?php if ( $is_size_attribute ) : ?>
						<!-- Circular Size Buttons -->
						<div class="size-grid">
							<?php
							if ( ! empty( $options ) ) {
								if ( $product && taxonomy_exists( $taxonomy ) ) {
									$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );
									
									foreach ( $terms as $term ) {
										if ( in_array( $term->slug, $options, true ) ) {
											// Get display name (abbreviated if needed)
											$display_name = function_exists('eshop_transform_size_label') ? 
															eshop_transform_size_label( $term->name ) : 
															$term->name;
											
											// Check stock status for this size
											$is_in_stock = false;
											$found_variation = false;
											
											// Check if this size option corresponds to any available variation
											$matching_variations = array();
											foreach ( $available_variations as $variation ) {
												if ( isset( $variation['attributes'][ $attr_key ] ) &&
													 $variation['attributes'][ $attr_key ] === $term->slug ) {
													$matching_variations[] = $variation;
													$found_variation = true;
												}
											}
											
											// If we found matching variations, check if any are in stock
											if ( $found_variation ) {
												foreach ( $matching_variations as $variation ) {
													if ( $variation['is_in_stock'] ) {
														$is_in_stock = true;
														break;
													}
												}
											}
											
											$button_class = 'size-tile';
											if ( ! $is_in_stock ) {
												$button_class .= ' disabled';
											}
											?>
											<button type="button" 
													class="<?php echo esc_attr( $button_class ); ?>"
													data-value="<?php echo esc_attr( $term->slug ); ?>"
													data-attribute="<?php echo esc_attr( $attr_key ); ?>"
													<?php echo ! $is_in_stock ? 'disabled' : ''; ?>>
												<?php echo esc_html( $display_name ); ?>
											</button>
											<?php
										}
									}
								} else {
									// Non-taxonomy attributes
									foreach ( $options as $option ) {
										$display_name = function_exists('eshop_transform_size_label') ? 
														eshop_transform_size_label( $option ) : 
														$option;
										?>
										<button type="button" 
												class="size-tile"
												data-value="<?php echo esc_attr( $option ); ?>"
												data-attribute="<?php echo esc_attr( $attr_key ); ?>">
											<?php echo esc_html( $display_name ); ?>
										</button>
										<?php
									}
								}
							}
							?>
						</div>
						
					<?php elseif ( $is_color_attribute ) : ?>
						<!-- Color Swatches -->
						<div class="color-palette">
							<?php
							if ( ! empty( $options ) ) {
								if ( $product && taxonomy_exists( $taxonomy ) ) {
									$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );
									
									foreach ( $terms as $term ) {
										if ( in_array( $term->slug, $options, true ) ) {
											// Check stock status for this color
											$is_in_stock = false;
											$found_variation = false;
											
											// Check if this color option corresponds to any available variation
											foreach ( $available_variations as $variation ) {
												if ( isset( $variation['attributes'][ $attr_key ] ) &&
													 $variation['attributes'][ $attr_key ] === $term->slug ) {
													$found_variation = true;
													if ( $variation['is_in_stock'] ) {
														$is_in_stock = true;
													}
												}
											}
											
											$swatch_class = 'swatch';
											if ( ! $is_in_stock ) {
												$swatch_class .= ' disabled';
											}
											
											// Try to get color hex from term meta
											$color_hex = get_term_meta( $term->term_id, 'color', true );
											?>
											<button type="button" 
													class="<?php echo esc_attr( $swatch_class ); ?>"
													data-value="<?php echo esc_attr( $term->slug ); ?>"
													data-attribute="<?php echo esc_attr( $attr_key ); ?>"
													<?php echo ! $is_in_stock ? 'disabled' : ''; ?>>
												<?php if ( $color_hex ) : ?>
													<span class="tone" style="background-color: <?php echo esc_attr( $color_hex ); ?>"></span>
												<?php endif; ?>
												<span class="swatch-name"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ) ); ?></span>
											</button>
											<?php
										}
									}
								} else {
									// Non-taxonomy color attributes
									foreach ( $options as $option ) {
										$swatch_class = 'swatch';
										?>
										<button type="button" 
												class="<?php echo esc_attr( $swatch_class ); ?>"
												data-value="<?php echo esc_attr( $option ); ?>"
												data-attribute="<?php echo esc_attr( $attr_key ); ?>">
											<span class="swatch-name"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product ) ); ?></span>
										</button>
										<?php
									}
								}
							}
							?>
						</div>
						
					<?php else : ?>
						<!-- Other Attributes as Buttons -->
						<div class="attribute-options-single">
							<?php
							if ( ! empty( $options ) ) {
								if ( $product && taxonomy_exists( $taxonomy ) ) {
									$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );
									
									foreach ( $terms as $term ) {
										if ( in_array( $term->slug, $options, true ) ) {
											// Check stock status for this attribute
											$is_in_stock = false;
											
											// Check if this option corresponds to any available variation
											foreach ( $available_variations as $variation ) {
												if ( isset( $variation['attributes'][ $attr_key ] ) &&
													 $variation['attributes'][ $attr_key ] === $term->slug &&
													 $variation['is_in_stock'] ) {
													$is_in_stock = true;
													break;
												}
											}
											
											$button_class = 'attribute-option-single';
											if ( ! $is_in_stock ) {
												$button_class .= ' disabled';
											}
											?>
											<button type="button" 
													class="<?php echo esc_attr( $button_class ); ?>"
													data-value="<?php echo esc_attr( $term->slug ); ?>"
													data-attribute="<?php echo esc_attr( $attr_key ); ?>"
													<?php echo ! $is_in_stock ? 'disabled' : ''; ?>>
												<?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ) ); ?>
											</button>
											<?php
										}
									}
								} else {
									foreach ( $options as $option ) {
										$button_class = 'attribute-option-single';
										?>
										<button type="button" 
												class="<?php echo esc_attr( $button_class ); ?>"
												data-value="<?php echo esc_attr( $option ); ?>"
												data-attribute="<?php echo esc_attr( $attr_key ); ?>">
											<?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product ) ); ?>
										</button>
										<?php
									}
								}
							}
							?>
						</div>
					<?php endif; ?>
					
					<!-- Hidden select for WooCommerce compatibility -->
					<select name="<?php echo esc_attr( $attr_key ); ?>"
							id="<?php echo esc_attr( sanitize_title( $attr_key ) ); ?>"
							class="hidden"
							data-attribute_name="<?php echo esc_attr( $attr_key ); ?>"
							style="display: none;">
						<option value=""><?php echo esc_html__( 'Choose an option', 'woocommerce' ); ?></option>
						<?php
						if ( ! empty( $options ) ) {
							if ( $product && taxonomy_exists( $taxonomy ) ) {
								$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );
								foreach ( $terms as $term ) {
									if ( in_array( $term->slug, $options, true ) ) {
										echo '<option value="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</option>';
									}
								}
							} else {
								foreach ( $options as $option ) {
									echo '<option value="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</option>';
								}
							}
						}
						?>
					</select>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="single_variation_wrap">
			<?php
				do_action( 'woocommerce_before_single_variation' );
				do_action( 'woocommerce_single_variation' );
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
?>

<!-- JavaScript functionality moved to js/components/single-product-swatches.js -->
<!-- This prevents conflicts and consolidates all variation handling -->
