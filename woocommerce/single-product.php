<?php
/**
 * The Template for displaying all single products
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

get_header('shop'); ?>

<div class="single-product-wrapper">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>

        <?php wc_get_template_part('content', 'single-product'); ?>

    <?php endwhile; // end of the loop. ?>
</div>

<?php
get_footer('shop');
