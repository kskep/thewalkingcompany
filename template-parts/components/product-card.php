<?php
/**
 * Product Card Component (Grid)
 * Implements minimalist image-focused card with size overlay and badges
 *
 * Expects global $product
 */

defined('ABSPATH') || exit;

global $product;
if (!$product || !$product->is_visible()) {
    return;
}

$product_id   = $product->get_id();
$main_image_id = $product->get_image_id();
$gallery_ids   = $product->get_gallery_image_ids();
$image_count   = ($main_image_id ? 1 : 0) + count($gallery_ids);

// Helper: get image HTML (cover) - use large high-quality size
$image_html = '';
if ($main_image_id) {
    $image_html = wp_get_attachment_image($main_image_id, 'product-large-hq', false, array(
        'class' => 'w-full h-full object-cover',
        'alt'   => $product->get_name(),
        'loading' => 'lazy'
    ));
} else {
    $image_html = '<div class="w-full h-full flex items-center justify-center" style="background-color: var(--bg-warm);"><i class="fas fa-image text-gray-300 text-4xl"></i></div>';
}

// Badges
$is_sale = $product->is_on_sale();
$created_date = get_the_date('U', $product_id);
$thirty_days_ago = strtotime('-30 days');
$is_new = $created_date > $thirty_days_ago;

// Low stock heuristic (global product stock)
$stock_quantity = $product->get_stock_quantity();
$is_low_stock = $product->is_in_stock() && $stock_quantity !== null && $stock_quantity > 0 && $stock_quantity <= 5;

?>

<article <?php wc_product_class('twc-card', $product); ?>>
  <div class="twc-card__image">
    <?php if ($image_count > 1) : ?>
      <div class="product-slider swiper">
        <div class="swiper-wrapper">
          <div class="swiper-slide">
            <a href="<?php the_permalink(); ?>">
              <?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </a>
          </div>
          <?php foreach ($gallery_ids as $gid) : ?>
            <div class="swiper-slide">
              <a href="<?php the_permalink(); ?>">
                <?php echo wp_get_attachment_image($gid, 'product-large-hq', false, array('class' => 'w-full h-full object-cover', 'loading' => 'lazy', 'alt' => esc_attr($product->get_name()))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
        <!-- Navigation -->
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
      </div>
    <?php else : ?>
      <a href="<?php the_permalink(); ?>">
        <?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      </a>
    <?php endif; ?>

    <div class="twc-card__badges">
      <?php if ($is_sale) : ?><span class="twc-badge twc-badge--sale">SALE</span><?php endif; ?>
      <?php if ($is_new) : ?><span class="twc-badge twc-badge--new">NEW</span><?php endif; ?>
    </div>
    
    <?php if ($image_count > 1) : ?>
      <div class="twc-card__dots" aria-hidden="true">
        <?php for ($i = 0; $i < $image_count; $i++) : ?>
          <span class="dot<?php echo $i === 0 ? ' is-active' : ''; ?>"></span>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

    <?php
    // Size overlay: use helper if available
    $sizes = function_exists('eshop_get_product_size_variants') ? eshop_get_product_size_variants($product, 8) : array();
    if (!empty($sizes)) : ?>
      <div class="twc-card__sizes" aria-hidden="true">
        <?php foreach ($sizes as $size) :
          $label = isset($size['name']) ? $size['name'] : '';
          $in_stock = !empty($size['in_stock']);
          $tooltip = '';
          $show_status = false;
          if ($in_stock && !empty($size['variation_id'])) {
              $variation = wc_get_product($size['variation_id']);
              if ($variation && $variation->managing_stock()) {
                  $vqty = $variation->get_stock_quantity();
                  if ($vqty !== null && $vqty > 0 && $vqty <= 5) {
                      $show_status = true;
                      $tooltip = 'ΧΑΜΗΛΟ ΑΠΟΘΕΜΑ';
                  }
              }
          }
          $btn_attrs = $tooltip ? ' data-tooltip="' . esc_attr($tooltip) . '"' : '';
          ?>
          <button class="size"<?php echo $btn_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
            <?php echo esc_html($label); ?>
            <?php if ($show_status) : ?><span class="status" title="<?php echo esc_attr($tooltip);?>"><span class="material-icons">schedule</span></span><?php endif; ?>
          </button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="twc-card__info">
    <div>
      <h3 class="twc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
      <div class="twc-card__sku">
        <?php
        $sku = $product->get_sku();
        echo $sku ? esc_html__('SKU: ', 'thewalkingtheme') . esc_html($sku) : '&nbsp;';
        ?>
      </div>
      <?php
      // Display category hierarchy (parent > child)
      $categories = get_the_terms($product_id, 'product_cat');
      if ($categories && !is_wp_error($categories)) :
          $cat_hierarchy = array();
          foreach ($categories as $category) {
              // Skip uncategorized
              if ($category->slug === 'uncategorized') continue;
              // Get parent if exists
              if ($category->parent) {
                  $parent = get_term($category->parent, 'product_cat');
                  if ($parent && !is_wp_error($parent)) {
                      $cat_hierarchy[] = esc_html($parent->name) . ' > ' . esc_html($category->name);
                  } else {
                      $cat_hierarchy[] = esc_html($category->name);
                  }
              } else {
                  $cat_hierarchy[] = esc_html($category->name);
              }
          }
          if (!empty($cat_hierarchy)) :
              ?>
              <div class="twc-card__category"><?php echo implode(', ', array_unique($cat_hierarchy)); ?></div>
          <?php endif;
      endif;
      ?>
      <div class="twc-card__price">
        <?php 
        // Only show price in red if on sale, otherwise use default color
        if ($is_sale) {
            echo wp_kses_post($product->get_price_html());
        } else {
            echo '<span class="price">' . wp_kses_post($product->get_price_html()) . '</span>';
        }
        ?>
      </div>
    </div>

    <button class="twc-card__wishlist add-to-wishlist" data-product-id="<?php echo esc_attr($product_id); ?>" title="<?php esc_attr_e('Add to Wishlist', 'thewalkingtheme'); ?>" aria-label="<?php esc_attr_e('Add to wishlist', 'thewalkingtheme'); ?>">
      <span class="material-icons">favorite_border</span>
    </button>
  </div>
</article>
