<?php get_header(); ?>

<main class="main-content">
    <?php if (is_front_page()) : ?>
        <?php get_template_part('template-parts/front/hero'); ?>
    <?php endif; ?>
    <div class="container mx-auto">
        
        <?php while (have_posts()) : the_post(); ?>
            
            <article id="page-<?php the_ID(); ?>" <?php post_class('single-page max-w-4xl mx-auto'); ?>>
                
                <!-- Page Header -->
                <header class="page-header mb-8 text-center">
                    <h1 class="page-title text-4xl font-bold text-dark mb-4"><?php the_title(); ?></h1>
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="page-thumbnail mb-8">
                            <?php the_post_thumbnail('large', array('class' => 'w-full h-auto rounded-lg shadow-lg')); ?>
                        </div>
                    <?php endif; ?>
                </header>
                
                <!-- Page Content -->
                <div class="page-content prose prose-lg max-w-none">
                    <?php the_content(); ?>
                    
                    <?php
                    wp_link_pages(array(
                        'before' => '<div class="page-links flex items-center space-x-2 mt-8"><span class="font-medium">Pages:</span>',
                        'after' => '</div>',
                        'link_before' => '<span class="inline-block px-3 py-1 bg-gray-100 hover:bg-primary hover:text-white rounded transition-colors duration-200">',
                        'link_after' => '</span>',
                    ));
                    ?>
                </div>
                
            </article>
            
            <!-- Comments (if enabled for pages) -->
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="comments-section max-w-4xl mx-auto mt-12">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
            
        <?php endwhile; ?>
        
    </div>
</main>

<?php get_footer(); ?>