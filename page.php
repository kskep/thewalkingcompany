<?php
/**
 * Default Page Template
 * Editorial layout aligned with product archive & single product styling.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="site-content" class="static-page-main" role="main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php
            $page_id      = get_the_ID();
            $page_excerpt = has_excerpt($page_id) ? wp_strip_all_tags(get_the_excerpt()) : '';
            $ancestors    = get_post_ancestors($page_id);
            $parent_id    = wp_get_post_parent_id($page_id);
            $root_id      = !empty($ancestors) ? end($ancestors) : ($parent_id ? $parent_id : 0);
            $section_label = '';

            if ($root_id) {
                $section_label = get_the_title($root_id);
            }

            if (!$section_label) {
                $section_label = get_bloginfo('name');
            }

            $reading_time = function_exists('eshop_estimated_reading_time') ? eshop_estimated_reading_time($page_id) : 0;
            ?>

            <article id="page-<?php the_ID(); ?>" <?php post_class('static-page-article'); ?>>
                <div class="page-shell static-page-shell">
                    <div class="static-page-content-grid">
                        <div class="static-page-content">
                            <?php the_content(); ?>

                            <?php
                            wp_link_pages(array(
                                'before' => '<div class="static-page-pagination" role="navigation"><span class="label">' . esc_html__('Pages:', 'eshop-theme') . '</span>',
                                'after'  => '</div>',
                                'link_before' => '<span class="page-number">',
                                'link_after' => '</span>',
                            ));
                            ?>

                            <?php edit_post_link(esc_html__('Edit page', 'eshop-theme'), '<p class="static-page-edit">', '</p>'); ?>
                        </div>
                    </div>
                </div>
            </article>

            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="page-shell static-page-comments">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php else : ?>
        <section class="page-shell static-page-shell">
            <p class="static-page-dek"><?php esc_html_e('Content is not available right now.', 'eshop-theme'); ?></p>
        </section>
    <?php endif; ?>
</main>

<?php get_footer(); ?>