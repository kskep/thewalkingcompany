<?php get_header(); ?>

<main class="main-content">
    <div class="container mx-auto px-4 py-8">
        
        <?php while (have_posts()) : the_post(); ?>
            
            
                
                <!-- Post Header -->
                <header class="post-header mb-8 text-center">
                    <h1 class="post-title text-4xl font-bold text-dark mb-4"><?php the_title(); ?></h1>
                    
                    <div class="post-meta flex flex-wrap justify-center items-center space-x-6 text-gray-600 mb-6">
                        <span class="flex items-center">
                            <i class="far fa-calendar icon-sm mr-2"></i>
                            <?php echo get_the_date(); ?>
                        </span>
                        <span class="flex items-center">
                            <i class="far fa-user icon-sm mr-2"></i>
                            <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" class="hover:text-primary transition-colors duration-200">
                                <?php the_author(); ?>
                            </a>
                        </span>
                        <?php if (has_category()) : ?>
                            <span class="flex items-center">
                                <i class="far fa-folder icon-sm mr-2"></i>
                                <?php the_category(', '); ?>
                            </span>
                        <?php endif; ?>
                        <?php if (has_tag()) : ?>
                            <span class="flex items-center">
                                <i class="fas fa-tags icon-sm mr-2"></i>
                                <?php the_tags('', ', '); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="post-thumbnail mb-8">
                            <?php the_post_thumbnail('large', array('class' => 'w-full h-auto rounded-lg shadow-lg')); ?>
                        </div>
                    <?php endif; ?>
                </header>
                
                <!-- Post Content -->
                <div class="post-content prose prose-lg max-w-none mb-8">
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
                
                <!-- Post Footer -->
                <footer class="post-footer border-t border-gray-200 pt-8">
                    
                    <!-- Author Bio -->
                    <?php if (get_the_author_meta('description')) : ?>
                        <div class="author-bio bg-gray-50 rounded-lg p-6 mb-8">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <?php echo get_avatar(get_the_author_meta('ID'), 80, '', '', array('class' => 'rounded-full')); ?>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-dark mb-2">
                                        About <?php the_author(); ?>
                                    </h3>
                                    <p class="text-gray-700 mb-3"><?php echo get_the_author_meta('description'); ?></p>
                                    <a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" class="text-primary hover:text-primary-dark font-medium">
                                        View all posts by <?php the_author(); ?>
                                        <i class="fas fa-arrow-right icon-sm ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Social Share -->
                    <div class="social-share mb-8">
                        <h3 class="text-lg font-semibold text-dark mb-4">Share this post</h3>
                        <div class="flex space-x-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                               target="_blank" 
                               class="share-button bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition-colors duration-200">
                                <i class="fab fa-facebook-f icon-sm"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                               target="_blank" 
                               class="share-button bg-blue-400 text-white p-3 rounded-full hover:bg-blue-500 transition-colors duration-200">
                                <i class="fab fa-twitter icon-sm"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>" 
                               target="_blank" 
                               class="share-button bg-blue-800 text-white p-3 rounded-full hover:bg-blue-900 transition-colors duration-200">
                                <i class="fab fa-linkedin-in icon-sm"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode(get_the_title()); ?>&body=<?php echo urlencode(get_permalink()); ?>" 
                               class="share-button bg-gray-600 text-white p-3 rounded-full hover:bg-gray-700 transition-colors duration-200">
                                <i class="fas fa-envelope icon-sm"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Post Navigation -->
                    <div class="post-navigation grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();
                        ?>
                        
                        <?php if ($prev_post) : ?>
                            <div class="nav-previous">
                                <a href="<?php echo get_permalink($prev_post); ?>" class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                                    <div class="flex items-center text-sm text-gray-500 mb-2">
                                        <i class="fas fa-chevron-left icon-sm mr-2"></i>
                                        Previous Post
                                    </div>
                                    <h4 class="font-medium text-dark"><?php echo get_the_title($prev_post); ?></h4>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($next_post) : ?>
                            <div class="nav-next">
                                <a href="<?php echo get_permalink($next_post); ?>" class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 text-right">
                                    <div class="flex items-center justify-end text-sm text-gray-500 mb-2">
                                        Next Post
                                        <i class="fas fa-chevron-right icon-sm ml-2"></i>
                                    </div>
                                    <h4 class="font-medium text-dark"><?php echo get_the_title($next_post); ?></h4>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </footer>
            
            
            <!-- Comments -->
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="comments-section max-w-4xl mx-auto mt-12">
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
            
        <?php endwhile; ?>
        
    </div>
</main>

<?php get_footer(); ?>