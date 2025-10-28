<?php
/**
 * Variable product add to cart
 * Custom implementation with circular size buttons per SINGLE_PRODUCT_PLAN.txt
 *
 * @package E-Shop Theme
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
		
		<!-- Custom Variations Display -->
		<div class="variations custom-variations">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				$attribute_label = wc_attribute_label( $attribute_name );
				$attribute_slug = sanitize_title( $attribute_name );
				$is_size_attribute = ( strpos( strtolower( $attribute_name ), 'size' ) !== false );
				?>
				
				<div class="variation-wrapper" data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
					<label class="variation-label">
						<?php echo esc_html( $attribute_label ); ?>:
						<span class="selected-value"></span>
					</label>
					
					<?php if ( $is_size_attribute ) : ?>
						<!-- Circular Size Buttons -->
						<div class="size-options-single">
							<?php
							if ( ! empty( $options ) ) {
								if ( $product && taxonomy_exists( $attribute_name ) ) {
									$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );
									
									foreach ( $terms as $term ) {
										if ( in_array( $term->slug, $options, true ) ) {
											// Get display name (abbreviated if needed)
											$display_name = function_exists('eshop_transform_size_label') ? 
															eshop_transform_size_label( $term->name ) : 
															$term->name;
											
											// Check if size is in stock
											$is_in_stock = true; // Default to in stock
											foreach ( $available_variations as $variation ) {
												if ( isset( $variation['attributes']['attribute_' . $attribute_name] ) &&
													 $variation['attributes']['attribute_' . $attribute_name] === $term->slug ) {
													$is_in_stock = $variation['is_in_stock'];
													break;
												}
											}
											
											$button_class = 'size-option-single';
											if ( ! $is_in_stock ) {
												$button_class .= ' out-of-stock';
											}
											?>
											<button type="button" 
													class="<?php echo esc_attr( $button_class ); ?>"
													data-value="<?php echo esc_attr( $term->slug ); ?>"
													data-attribute="<?php echo esc_attr( $attribute_slug ); ?>"
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
												class="size-option-single"
												data-value="<?php echo esc_attr( $option ); ?>"
												data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
											<?php echo esc_html( $display_name ); ?>
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
								if ( $product && taxonomy_exists( $attribute_name ) ) {
									$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );
									
									foreach ( $terms as $term ) {
										if ( in_array( $term->slug, $options, true ) ) {
											?>
											<button type="button" 
													class="attribute-option-single"
													data-value="<?php echo esc_attr( $term->slug ); ?>"
													data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
												<?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ) ); ?>
											</button>
											<?php
										}
									}
								} else {
									foreach ( $options as $option ) {
										?>
										<button type="button" 
												class="attribute-option-single"
												data-value="<?php echo esc_attr( $option ); ?>"
												data-attribute="<?php echo esc_attr( $attribute_slug ); ?>">
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
					<select name="attribute_<?php echo esc_attr( $attribute_name ); ?>"
							id="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
							class="hidden"
							data-attribute_name="attribute_<?php echo esc_attr( $attribute_name ); ?>"
							style="display: none;">
						<option value=""><?php echo esc_html__( 'Choose an option', 'woocommerce' ); ?></option>
						<?php
						if ( ! empty( $options ) ) {
							if ( $product && taxonomy_exists( $attribute_name ) ) {
								$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' ) );
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

<script>
// Handle size and attribute button clicks
jQuery(function($) {
	// Size button click handler
	$(document).on('click', '.size-option-single:not(.out-of-stock)', function(e) {
		e.preventDefault();
		var $this = $(this);
		var value = $this.data('value');
		
		// Update button states
		$this.siblings('.size-option-single').removeClass('selected');
		$this.addClass('selected');
		
		// Update hidden select
		var $select = $this.closest('.variation-wrapper').find('select');
		$select.val(value).trigger('change');
		
		// Update label
		$this.closest('.variation-wrapper').find('.selected-value').text($this.text());
	});
	
	// Other attribute button click handler
	$(document).on('click', '.attribute-option-single', function(e) {
		e.preventDefault();
		var $this = $(this);
		var value = $this.data('value');
		
		// Update button states
		$this.siblings('.attribute-option-single').removeClass('selected');
		$this.addClass('selected');
		
		// Update hidden select
		var $select = $this.closest('.variation-wrapper').find('select');
		$select.val(value).trigger('change');
		
		// Update label
		$this.closest('.variation-wrapper').find('.selected-value').text($this.text());
	});
});
</script>
