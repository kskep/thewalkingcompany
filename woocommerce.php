<?php
/**
 * WooCommerce Template
 * 
 * This template is used for all WooCommerce pages
 */

get_header(); ?>

<main class="main-content woocommerce-page">
    <div class="container mx-auto px-4 py-8">
        
        <!-- Breadcrumbs -->
        <?php if (function_exists('woocommerce_breadcrumb')) : ?>
            <div class="woocommerce-breadcrumb mb-6">
                <?php woocommerce_breadcrumb(array(
                    'delimiter' => ' <i class="fas fa-chevron-right text-gray-400 mx-2"></i> ',
                    'wrap_before' => '<nav class="breadcrumb text-sm text-gray-600">',
                    'wrap_after' => '</nav>',
                    'before' => '<span>',
                    'after' => '</span>',
                    'home' => '<i class="fas fa-home mr-1"></i>Home',
                )); ?>
            </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Main Content -->
            <div class="lg:col-span-3">
                <?php woocommerce_content(); ?>
            </div>
            
            <!-- Sidebar -->
            <aside class="sidebar lg:col-span-1">
                
                <!-- Product Search -->
                <div class="widget bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="widget-title text-lg font-semibold text-dark mb-4 flex items-center">
                        <i class="fas fa-search icon-sm text-primary mr-2"></i>
                        Search Products
                    </h3>
                    <?php if (function_exists('get_product_search_form')) : ?>
                        <?php get_product_search_form(); ?>
                    <?php endif; ?>
                </div>
                
                <!-- Product Categories -->
                <?php if (function_exists('woocommerce_product_categories')) : ?>
                    <div class="widget bg-white rounded-lg shadow-md p-6 mb-6">
                        <h3 class="widget-title text-lg font-semibold text-dark mb-4 flex items-center">
                            <i class="fas fa-list icon-sm text-primary mr-2"></i>
                            Product Categories
                        </h3>
                        <ul class="product-categories space-y-2">
                            <?php
                            $product_categories = get_terms(array(
                                'taxonomy' => 'product_cat',
                                'hide_empty' => true,
                                'parent' => 0
                            ));
                            foreach ($product_categories as $category) :
                            ?>
                                <li>
                                    <a href="<?php echo get_term_link($category); ?>" class="flex items-center justify-between text-dark hover:text-primary transition-colors duration-200 py-2">
                                        <span class="flex items-center">
                                            <i class="fas fa-tag icon-sm text-gray-400 mr-2"></i>
                                            <?php echo $category->name; ?>
                                        </span>
                                        <span class="text-xs bg-primary text-white px-2 py-1 rounded-full">
                                            <?php echo $category->count; ?>
                                        </span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Price Filter -->
                <div class="widget bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="widget-title text-lg font-semibold text-dark mb-4 flex items-center">
                        <i class="fas fa-dollar-sign icon-sm text-primary mr-2"></i>
                        Filter by Price
                    </h3>
                    <?php if (is_active_sidebar('shop-sidebar')) : ?>
                        <?php dynamic_sidebar('shop-sidebar'); ?>
                    <?php else : ?>
                        <div class="price-filter">
                            <form method="get">
                                <div class="flex space-x-2 mb-3">
                                    <input type="number" name="min_price" placeholder="Min" class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" value="<?php echo isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : ''; ?>">
                                    <input type="number" name="max_price" placeholder="Max" class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-primary" value="<?php echo isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : ''; ?>">
                                </div>
                                <button type="submit" class="w-full btn-primary">
                                    <i class="fas fa-filter icon-sm mr-2"></i>
                                    Filter
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Products -->
                <div class="widget bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="widget-title text-lg font-semibold text-dark mb-4 flex items-center">
                        <i class="fas fa-star icon-sm text-primary mr-2"></i>
                        Recent Products
                    </h3>
                    <?php
                    $recent_products = wc_get_products(array(
                        'limit' => 3,
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'status' => 'publish'
                    ));
                    if ($recent_products) :
                    ?>
                        <div class="recent-products space-y-4">
                            <?php foreach ($recent_products as $product) : ?>
                                <div class="recent-product flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <a href="<?php echo get_permalink($product->get_id()); ?>">
                                            <?php echo $product->get_image(array(50, 50)); ?>
                                        </a>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-medium text-dark">
                                            <a href="<?php echo get_permalink($product->get_id()); ?>" class="hover:text-primary transition-colors duration-200">
                                                <?php echo wp_trim_words($product->get_name(), 4); ?>
                                            </a>
                                        </h4>
                                        <p class="text-sm text-primary font-semibold">
                                            <?php echo $product->get_price_html(); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sale Products -->
                <?php
                $sale_products = wc_get_products(array(
                    'limit' => 3,
                    'meta_key' => '_sale_price',
                    'meta_value' => '',
                    'meta_compare' => '!=',
                    'status' => 'publish'
                ));
                if ($sale_products) :
                ?>
                    <div class="widget bg-primary rounded-lg shadow-md p-6 mb-6 text-white">
                        <h3 class="widget-title text-lg font-semibold mb-4 flex items-center">
                            <i class="fas fa-fire icon-sm mr-2"></i>
                            Hot Deals
                        </h3>
                        <div class="sale-products space-y-4">
                            <?php foreach ($sale_products as $product) : ?>
                                <div class="sale-product">
                                    <h4 class="text-sm font-medium mb-1">
                                        <a href="<?php echo get_permalink($product->get_id()); ?>" class="text-white hover:text-gray-200 transition-colors duration-200">
                                            <?php echo wp_trim_words($product->get_name(), 4); ?>
                                        </a>
                                    </h4>
                                    <p class="text-sm">
                                        <?php echo $product->get_price_html(); ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            </aside>
            
        </div>
    </div>
</main>

<?php get_footer(); ?>