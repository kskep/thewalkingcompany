<?php
/**
 * Size Transformation Functions
 * 
 * Handles transformation of full clothing size names to abbreviations
 * for display on the front-end.
 * 
 * @package TheWalkingCompany
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Transform full size names to abbreviations
 * 
 * @param string $size The original size name
 * @return string The transformed size abbreviation
 */
function twc_transform_size_label( $size ) {
    // Define the size mapping array
    $size_mapping = array(
        'XSmall/Small'   => 'XS/S',
        'One Size'       => 'OS',
        'XSmall'         => 'XS',
        'Small'          => 'S',
        'Medium'         => 'M',
        'Large'          => 'L',
        'XLarge'         => 'XL',
        'XXLarge'        => 'XXL',
        'XXXLarge'       => 'XXXL',
        'Small/Medium'   => 'S/M',
        'Medium/Large'   => 'M/L',
        'Large/XLarge'   => 'L/XL',
    );

    // Return the mapped value if it exists, otherwise return original
    return isset( $size_mapping[ $size ] ) ? $size_mapping[ $size ] : $size;
}

/**
 * Filter WooCommerce product attribute labels
 * 
 * @param string $value The attribute value
 * @param WC_Product $product The product object
 * @param array $attribute The attribute data
 * @return string The transformed attribute value
 */
function twc_filter_product_attribute_label( $value, $product, $attribute ) {
    // Only transform size-related attributes
    $size_attributes = array( 'select-size', 'size-selection' );
    
    if ( in_array( $attribute->get_name(), $size_attributes, true ) ) {
        return twc_transform_size_label( $value );
    }
    
    return $value;
}
add_filter( 'woocommerce_product_attribute_label', 'twc_filter_product_attribute_label', 10, 3 );

/**
 * Filter variation option names
 * 
 * @param string $value The option value
 * @param WC_Product $product The product object
 * @param string $name The option name
 * @return string The transformed option value
 */
function twc_filter_variation_option_name( $value, $product, $name ) {
    // Check if this is a size-related attribute
    $size_attributes = array( 'select-size', 'size-selection' );
    
    if ( in_array( $name, $size_attributes, true ) ) {
        return twc_transform_size_label( $value );
    }
    
    return $value;
}
add_filter( 'woocommerce_variation_option_name', 'twc_filter_variation_option_name', 10, 3 );

/**
 * Filter product attribute terms in dropdowns
 * 
 * @param string $term The term name
 * @param WP_Term $term_object The term object
 * @param string $taxonomy The taxonomy
 * @return string The transformed term name
 */
function twc_filter_attribute_term( $term, $term_object, $taxonomy ) {
    // Check if this is a size-related attribute taxonomy
    $size_taxonomies = array( 'pa_select-size', 'pa_size-selection' );
    
    if ( in_array( $taxonomy, $size_taxonomies, true ) ) {
        return twc_transform_size_label( $term );
    }
    
    return $term;
}
add_filter( 'get_term', 'twc_filter_attribute_term', 10, 3 );

/**
 * Add data attribute to size elements for JavaScript transformation
 * 
 * @param array $attributes The element attributes
 * @param WC_Product $product The product object
 * @param array $attribute The attribute data
 * @return array The modified attributes
 */
function twc_add_size_data_attributes( $attributes, $product, $attribute ) {
    $size_attributes = array( 'select-size', 'size-selection' );
    
    if ( in_array( $attribute->get_name(), $size_attributes, true ) ) {
        $attributes['data-size-transform'] = 'true';
    }
    
    return $attributes;
}
add_filter( 'woocommerce_product_attribute_class', 'twc_add_size_data_attributes', 10, 3 );

/**
 * Get all available size transformations for JavaScript
 * 
 * @return array The size mapping array
 */
function twc_get_size_transformations() {
    return array(
        'XSmall/Small'   => 'XS/S',
        'One Size'       => 'OS',
        'XSmall'         => 'XS',
        'Small'          => 'S',
        'Medium'         => 'M',
        'Large'          => 'L',
        'XLarge'         => 'XL',
        'XXLarge'        => 'XXL',
        'XXXLarge'       => 'XXXL',
        'Small/Medium'   => 'S/M',
        'Medium/Large'   => 'M/L',
        'Large/XLarge'   => 'L/XL',
    );
}

/**
 * Localize size transformation data for JavaScript
 */
function twc_localize_size_transformation() {
    wp_localize_script( 'size-transformation', 'twcSizeTransform', array(
        'mapping' => twc_get_size_transformations(),
        'attributes' => array( 'select-size', 'size-selection' ),
    ));
}
add_action( 'wp_enqueue_scripts', 'twc_localize_size_transformation' );