<?php get_header(); ?>

<main class="main-content">
    <div class="container mx-auto px-4 py-16">
        
        <div class="error-404 text-center max-w-2xl mx-auto">
            
            <!-- 404 Icon -->
            <div class="error-icon mb-8">
                <i class="fas fa-exclamation-triangle text-8xl text-primary opacity-50"></i>
            </div>
            
            <!-- Error Message -->
            <header class="page-header mb-8">
                <h1 class="page-title text-6xl font-bold text-dark mb-4">404</h1>
                <h2 class="text-2xl font-semibold text-gray-600 mb-4">Page Not Found</h2>
                <p class="text-lg text-gray-600 mb-8">
                    Sorry, but the page you are looking for doesn't exist or has been moved.
                </p>
            </header>
            
            <!-- Search Form -->
            <div class="error-search mb-8">
                <h3 class="text-lg font-semibold text-dark mb-4">Try searching for what you need:</h3>
                <form role="search" method="get" class="search-form max-w-md mx-auto" action="<?php echo home_url('/'); ?>">
                    <div class="flex">
                        <input type="search" 
                               class="search-field flex-1 px-4 py-3 border border-gray-300 rounded-l-lg focus:outline-none focus:border-primary" 
                               placeholder="Search..." 
                               value="<?php echo get_search_query(); ?>" 
                               name="s" />
                        <button type="submit" class="search-submit bg-primary text-white px-6 py-3 rounded-r-lg hover:bg-primary-dark transition-colors duration-200">
                            <i class="fas fa-search icon"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Quick Links -->
            <div class="error-links mb-8">
                <h3 class="text-lg font-semibold text-dark mb-4">Or try these popular pages:</h3>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="<?php echo home_url('/'); ?>" class="btn-primary">
                        <i class="fas fa-home icon-sm mr-2"></i>
                        Home Page
                    </a>
                    
                    <?php if (class_exists('WooCommerce')) : ?>
                        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-secondary">
                            <i class="fas fa-shopping-bag icon-sm mr-2"></i>
                            Shop
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" class="btn-secondary">
                        <i class="fas fa-blog icon-sm mr-2"></i>
                        Blog
                    </a>
                    
                    <?php
                    $contact_page = get_page_by_path('contact');
                    if ($contact_page) :
                    ?>
                        <a href="<?php echo get_permalink($contact_page); ?>" class="btn-secondary">
                            <i class="fas fa-envelope icon-sm mr-2"></i>
                            Contact
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Posts -->
            <div class="recent-posts">
                <h3 class="text-lg font-semibold text-dark mb-6">Recent Posts</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    $recent_posts = wp_get_recent_posts(array(
                        'numberposts' => 3,
                        'post_status' => 'publish'
                    ));
                    foreach ($recent_posts as $post) :
                    ?>
                        <div class="post-card bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                            <?php if (has_post_thumbnail($post['ID'])) : ?>
                                <div class="post-thumbnail">
                                    <a href="<?php echo get_permalink($post['ID']); ?>">
                                        <?php echo get_the_post_thumbnail($post['ID'], 'medium', array('class' => 'w-full h-32 object-cover')); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="post-content p-4">
                                <h4 class="font-semibold text-dark mb-2">
                                    <a href="<?php echo get_permalink($post['ID']); ?>" class="hover:text-primary transition-colors duration-200">
                                        <?php echo wp_trim_words($post['post_title'], 8); ?>
                                    </a>
                                </h4>
                                <p class="text-sm text-gray-600 mb-3">
                                    <?php echo wp_trim_words($post['post_content'], 15); ?>
                                </p>
                                <a href="<?php echo get_permalink($post['ID']); ?>" class="text-primary hover:text-primary-dark font-medium text-sm">
                                    Read More
                                    <i class="fas fa-arrow-right icon-sm ml-1"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; wp_reset_query(); ?>
                </div>
            </div>
            
        </div>
        
    </div>
</main>

<?php get_footer(); ?>