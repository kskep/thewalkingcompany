<?php
/**
 * Search Results Template
 * Displays WooCommerce product search results in the standard grid layout.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main id="search-results" class="search-results-page">
    <div class="page-shell">
        <?php
        $search_query = get_search_query();
        $results_query = $wp_query;

        if ($results_query->have_posts()) :
        ?>
            <div class="search-results-header" style="padding: 32px 0 24px;">
                <h1 style="font-family: 'Roboto Condensed', sans-serif; font-size: 24px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 8px;">
                    <?php printf(esc_html__('Apotelesmata anazitisis gia "%s"', 'eshop-theme'), esc_html($search_query)); ?>
                </h1>
                <p style="color: #6b7280; font-size: 14px; margin: 0;">
                    <?php printf(esc_html__('%d proionta vrethikan', 'eshop-theme'), $results_query->found_posts); ?>
                </p>
            </div>

            <ul class="products-grid">
                <?php
                while ($results_query->have_posts()) :
                    $results_query->the_post();
                    get_template_part('template-parts/components/product-card');
                endwhile;
                ?>
            </ul>

            <?php if ($results_query->max_num_pages > 1) : ?>
                <nav class="woocommerce-pagination" aria-label="<?php esc_attr_e('Selidoseis', 'eshop-theme'); ?>" style="padding: 32px 0;">
                    <?php
                    echo paginate_links(array(
                        'total'     => $results_query->max_num_pages,
                        'current'   => max(1, get_query_var('paged')),
                        'format'    => '?paged=%#%',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                    ));
                    ?>
                </nav>
            <?php endif; ?>

        <?php else : ?>
            <div class="search-no-results" style="text-align: center; padding: 80px 0;">
                <div style="margin-bottom: 24px;">
                    <i class="fas fa-search" style="font-size: 64px; color: #d1d5db;"></i>
                </div>
                <h2 style="font-family: 'Roboto Condensed', sans-serif; font-size: 22px; font-weight: 700; margin: 0 0 12px;">
                    <?php esc_html_e('Den vrethikan proionta', 'eshop-theme'); ?>
                </h2>
                <p style="color: #6b7280; font-size: 15px; margin: 0 0 24px;">
                    <?php printf(esc_html_e('Kanena apotelesma gia "%s". Dokimaste alli lexi kleidi.', 'eshop-theme'), esc_html($search_query)); ?>
                </p>
                <a href="<?php echo esc_url(home_url('/shop/')); ?>"
                   style="display: inline-block; background: #1f2937; color: #fff; padding: 14px 32px; text-decoration: none; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; font-family: 'Roboto Condensed', sans-serif;">
                    <?php esc_html_e('Anazitisi olon ton proionton', 'eshop-theme'); ?>
                </a>
            </div>
        <?php endif;
        wp_reset_postdata();
        ?>
    </div>
</main>
<?php get_footer(); ?>
