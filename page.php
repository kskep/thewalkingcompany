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
            $raw_title    = get_post_field('post_title', $page_id);
            $page_title   = $raw_title ? $raw_title : get_the_title();
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

            $child_nav = '';
            $child_nav_heading = '';

            $child_list = wp_list_pages(array(
                'child_of' => $page_id,
                'title_li' => '',
                'echo' => 0,
                'depth' => 1,
                'sort_column' => 'menu_order,post_title',
            ));

            if (!empty($child_list)) {
                $child_nav = $child_list;
                $child_nav_heading = esc_html__('Subpages', 'eshop-theme');
            } elseif ($parent_id) {
                $sibling_list = wp_list_pages(array(
                    'child_of' => $parent_id,
                    'title_li' => '',
                    'echo' => 0,
                    'depth' => 1,
                    'sort_column' => 'menu_order,post_title',
                ));

                if (!empty($sibling_list)) {
                    $child_nav = $sibling_list;
                    $child_nav_heading = esc_html__('In this section', 'eshop-theme');
                }
            }
            ?>

            <article id="page-<?php the_ID(); ?>" <?php post_class('static-page-article'); ?>>
                <div class="page-shell static-page-shell">
                    <header class="static-page-hero">
                        <div class="static-page-hero__content">
                            <?php get_template_part('template-parts/components/breadcrumbs'); ?>

                            <p class="static-page-eyebrow"><?php echo esc_html($section_label); ?></p>
                            <h1 class="static-page-title"><?php echo esc_html($page_title); ?></h1>

                            <?php if (!empty($page_excerpt)) : ?>
                                <p class="static-page-dek"><?php echo esc_html($page_excerpt); ?></p>
                            <?php endif; ?>

                            <ul class="static-page-meta">
                                <li>
                                    <span><?php esc_html_e('Updated', 'eshop-theme'); ?></span>
                                    <strong><?php echo esc_html(get_the_modified_date()); ?></strong>
                                </li>
                                <li>
                                    <span><?php esc_html_e('Published', 'eshop-theme'); ?></span>
                                    <strong><?php echo esc_html(get_the_date()); ?></strong>
                                </li>
                                <?php if ($reading_time) : ?>
                                    <li>
                                        <span><?php esc_html_e('Reading time', 'eshop-theme'); ?></span>
                                        <strong>
                                            <?php
                                            printf(
                                                esc_html(_n('%s minute', '%s minutes', $reading_time, 'eshop-theme')),
                                                esc_html(number_format_i18n($reading_time))
                                            );
                                            ?>
                                        </strong>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <?php if (has_post_thumbnail()) : ?>
                            <figure class="static-page-hero__media">
                                <?php the_post_thumbnail('full', array('class' => 'static-page-featured-image')); ?>
                            </figure>
                        <?php endif; ?>
                    </header>

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

                        <aside class="static-page-aside" aria-label="Secondary">
                            <?php if (!empty($child_nav) && !empty($child_nav_heading)) : ?>
                                <div class="static-page-outline">
                                    <p class="static-page-outline__label"><?php echo esc_html($child_nav_heading); ?></p>
                                    <ul class="static-page-outline__list">
                                        <?php echo wp_kses_post($child_nav); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <div class="static-page-note">
                                <p class="note-eyebrow"><?php esc_html_e('Need support?', 'eshop-theme'); ?></p>
                                <p class="note-body"><?php esc_html_e('Our stylists reply within 24 hours. Use the contact form or visit a boutique for personal guidance.', 'eshop-theme'); ?></p>
                                <a class="note-link" href="<?php echo esc_url(home_url('/contact/')); ?>">
                                    <?php esc_html_e('Contact us', 'eshop-theme'); ?>
                                </a>
                            </div>
                        </aside>
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