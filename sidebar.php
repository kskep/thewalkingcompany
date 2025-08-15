<?php
/**
 * The sidebar containing the main widget area
 *
 * @package E-Shop Theme
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside id="secondary" class="widget-area">
    
    <!-- Search Widget -->
    <section class="widget widget_search bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="widget-title text-lg font-semibold text-dark mb-4 flex items-center">
            <i class="fas fa-search icon-sm text-primary mr-2"></i>
            Search
        </h2>
        <form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
            <div class="flex">
                <input type="search" 
                       class="search-field flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:border-primary" 
                       placeholder="Search..." 
                       value="<?php echo get_search_query(); ?>" 
                       name="s" />
                <button type="submit" class="search-submit bg-primary text-white px-4 py-2 rounded-r-md hover:bg-primary-dark transition-colors duration-200">
                    <i class="fas fa-search icon-sm"></i>
                </button>
            </div>
        </form>
    </section>

    <!-- Recent Posts Widget -->
    <section class="widget widget_recent_entries bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="widget-title text-lg font-semibold text-dark mb-4 flex items-center">
            <i class="fas fa-clock icon-sm text-primary mr-2"></i>
            Recent Posts
        </h2>
        <ul class="recent-posts-list space-y-3">
            <?php
            $recent_posts = wp_get_recent_posts(array(
                'numberposts' => 5,
                'post_status' => 'publish'
            ));
            foreach ($recent_posts as $post) :
            ?>
                <li class="recent-post-item">
                    <a href="<?php echo get_permalink($post['ID']); ?>" class="flex items-start space-x-3 text-dark hover:text-primary transition-colors duration-200">
                        <?php if (has_post_thumbnail($post['ID'])) : ?>
                            <div class="flex-shrink-0">
                                <?php echo get_the_post_thumbnail($post['ID'], 'thumbnail', array('class' => 'w-12 h-12 object-cover rounded')); ?>
                            </div>
                        <?php endif; ?>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-medium line-clamp-2"><?php echo $post['post_title']; ?></h3>
                            <p class="text-xs text-gray-500 mt-1"><?php echo get_the_date('M j, Y', $post['ID']); ?></p>
                        </div>
                    </a>
                </li>
            <?php endforeach; wp_reset_query(); ?>
        </ul>
    </section>

    <!-- Categories Widget -->
    <section class="widget widget_categories bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="widget-title text-lg font-semibold text-dark mb-4 flex items-center">
            <i class="fas fa-folder icon-sm text-primary mr-2"></i>
            Categories
        </h2>
        <ul class="categories-list space-y-2">
            <?php
            $categories = get_categories(array('hide_empty' => true));
            foreach ($categories as $category) :
            ?>
                <li class="category-item">
                    <a href="<?php echo get_category_link($category->term_id); ?>" class="flex items-center justify-between text-dark hover:text-primary transition-colors duration-200 py-1">
                        <span class="flex items-center">
                            <i class="fas fa-tag icon-sm text-gray-400 mr-2"></i>
                            <?php echo $category->name; ?>
                        </span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                            <?php echo $category->count; ?>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- WooCommerce Product Categories (if WooCommerce is active) -->
    <?php if (class_exists('WooCommerce')) : ?>
        <section class="widget widget_product_categories bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="widget-title text-lg font-semibold text-dark mb-4 flex items-center">
                <i class="fas fa-shopping-bag icon-sm text-primary mr-2"></i>
                Product Categories
            </h2>
            <ul class="product-categories-list space-y-2">
                <?php
                $product_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'number' => 8
                ));
                foreach ($product_categories as $category) :
                ?>
                    <li class="product-category-item">
                        <a href="<?php echo get_term_link($category); ?>" class="flex items-center justify-between text-dark hover:text-primary transition-colors duration-200 py-1">
                            <span class="flex items-center">
                                <i class="fas fa-cube icon-sm text-gray-400 mr-2"></i>
                                <?php echo $category->name; ?>
                            </span>
                            <span class="text-xs bg-primary text-white px-2 py-1 rounded-full">
                                <?php echo $category->count; ?>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

    <!-- Newsletter Signup -->
    <section class="widget widget_newsletter bg-primary rounded-lg shadow-md p-6 mb-6 text-white">
        <h2 class="widget-title text-lg font-semibold mb-4 flex items-center">
            <i class="fas fa-envelope icon-sm mr-2"></i>
            Newsletter
        </h2>
        <p class="text-sm mb-4 opacity-90">Stay updated with our latest products and offers!</p>
        <form class="newsletter-form">
            <div class="space-y-3">
                <input type="email" 
                       class="w-full px-3 py-2 rounded-md text-dark focus:outline-none focus:ring-2 focus:ring-white" 
                       placeholder="Your email address" 
                       required />
                <button type="submit" class="w-full bg-white text-primary py-2 rounded-md font-medium hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-paper-plane icon-sm mr-2"></i>
                    Subscribe
                </button>
            </div>
        </form>
    </section>

    <!-- Dynamic Sidebar -->
    <?php dynamic_sidebar('sidebar-1'); ?>

</aside><!-- #secondary -->