<?php get_header(); ?>

<main class="main-content">
    <div class="container mx-auto px-4 py-8">
        
        <?php if (is_home() && !is_front_page()) : ?>
            <header class="page-header mb-8">
                <h1 class="text-3xl font-bold text-dark mb-4">
                    <i class="fas fa-blog icon-lg text-primary mr-2"></i>
                    <?php single_post_title(); ?>
                </h1>
            </header>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Main Content Area -->
            <div class="lg:col-span-2">
                <?php if (have_posts()) : ?>
                    <div class="posts-grid grid gap-6">
                        <?php while (have_posts()) : the_post(); ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class('post-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300'); ?>>
                                
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="post-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium_large', array('class' => 'w-full h-48 object-cover')); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="post-content p-6">
                                    <header class="post-header mb-4">
                                        <h2 class="post-title text-xl font-semibold mb-2">
                                            <a href="<?php the_permalink(); ?>" class="text-dark hover:text-primary transition-colors duration-200">
                                                <?php the_title(); ?>
                                            </a>
                                        </h2>
                                        
                                        <div class="post-meta text-sm text-gray-600 flex items-center space-x-4">
                                            <span class="flex items-center">
                                                <i class="far fa-calendar icon-sm mr-1"></i>
                                                <?php echo get_the_date(); ?>
                                            </span>
                                            <span class="flex items-center">
                                                <i class="far fa-user icon-sm mr-1"></i>
                                                <?php the_author(); ?>
                                            </span>
                                            <?php if (has_category()) : ?>
                                                <span class="flex items-center">
                                                    <i class="far fa-folder icon-sm mr-1"></i>
                                                    <?php the_category(', '); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </header>
                                    
                                    <div class="post-excerpt text-gray-700 mb-4">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    
                                    <footer class="post-footer">
                                        <a href="<?php the_permalink(); ?>" class="btn-primary inline-flex items-center">
                                            <span>Read More</span>
                                            <i class="fas fa-arrow-right icon-sm ml-2"></i>
                                        </a>
                                    </footer>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-wrapper mt-8">
                        <?php
                        the_posts_pagination(array(
                            'mid_size' => 2,
                            'prev_text' => '<i class="fas fa-chevron-left icon-sm mr-1"></i> Previous',
                            'next_text' => 'Next <i class="fas fa-chevron-right icon-sm ml-1"></i>',
                            'class' => 'flex justify-center space-x-2'
                        ));
                        ?>
                    </div>
                    
                <?php else : ?>
                    <div class="no-posts text-center py-12">
                        <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                        <h2 class="text-2xl font-semibold text-dark mb-4">No posts found</h2>
                        <p class="text-gray-600 mb-6">Sorry, but nothing matched your search criteria. Please try again with different keywords.</p>
                        <a href="<?php echo home_url('/'); ?>" class="btn-primary">
                            <i class="fas fa-home icon-sm mr-2"></i>
                            Back to Home
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar lg:col-span-1">
                <?php get_sidebar(); ?>
            </aside>
            
        </div>
    </div>
</main>

<?php get_footer(); ?>