<?php
/**
 * Front Page Template
 * 
 * @package E-Shop Theme
 */

get_header(); ?>

<main class="main-content">
    <?php 
    // Display hero section
    get_template_part('template-parts/front/hero'); 
    ?>
    
    <?php 
    // Display category grid section
    get_template_part('template-parts/front/category-grid'); 
    ?>
    
    <?php 
    // Display main content if there's any page content
    if (have_posts()) : 
        while (have_posts()) : the_post();
            if (get_the_content()) : ?>
                <div class="container mx-auto px-4 py-8">
                    <div class="page-content prose prose-lg max-w-none">
                        <?php the_content(); ?>
                    </div>
                </div>
            <?php endif;
        endwhile;
    endif; 
    ?>

    <?php 
    // Display service highlights above the footer
    get_template_part('template-parts/front/service-highlights'); 
    ?>
</main>

<?php get_footer(); ?>
