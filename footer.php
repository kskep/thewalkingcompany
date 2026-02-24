<?php
/**
 * The template for displaying the footer
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
    </div><!-- #content -->

    <footer id="colophon" class="site-footer bg-gray-100 pt-16 pb-8 border-t border-gray-200">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                
                <!-- Column 1: Brand Info -->
                <div class="footer-brand">
                    <div class="footer-logo mb-6">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="block">
                            <img src="https://walk.thewebplace.gr/wp-content/uploads/2023/01/twc-logo-pink.png"
                                 alt="<?php bloginfo('name'); ?>"
                                 class="h-12 w-auto">
                        </a>
                    </div>
                    <p class="text-gray-600 mb-6 max-w-sm">
                        Your daily dose of fashion inspiration, bringing you curated collections and the latest trends.
                    </p>
                    <div class="social-links flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-gray-900 transition-colors"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-gray-900 transition-colors"><i class="fab fa-facebook-f text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-gray-900 transition-colors"><i class="fab fa-twitter text-xl"></i></a>
                    </div>
                </div>

                <!-- Column 2: Footer Main Menu -->
                <div class="footer-nav">
                    <h3 class="text-lg font-bold mb-6 uppercase tracking-wide text-gray-900"><?php _e('ΠΛΗΡΟΦΟΡΙΕΣ', 'eshop-theme'); ?></h3>
                    <?php
                    $footer_main_markup = '';
                    if (has_nav_menu('footer-main')) {
                        $footer_main_markup = wp_nav_menu(array(
                            'theme_location' => 'footer-main',
                            'container' => false,
                            'menu_class' => 'space-y-3 text-sm text-gray-600',
                            'fallback_cb' => false,
                            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            'echo' => false,
                        ));
                    }

                    if (!empty($footer_main_markup) && strpos($footer_main_markup, '<li') !== false) {
                        echo $footer_main_markup;
                    } else {
                        echo '<ul class="space-y-3 text-sm text-gray-600">';
                        echo '<li><a href="' . esc_url(home_url('/shop/')) . '">' . esc_html__('Shop', 'eshop-theme') . '</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/contact/')) . '">' . esc_html__('Contact', 'eshop-theme') . '</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/my-account/')) . '">' . esc_html__('My Account', 'eshop-theme') . '</a></li>';
                        echo '</ul>';
                    }
                    ?>
                </div>

                <!-- Column 3: Account & Help -->
                <div class="footer-nav">
                    <h3 class="text-lg font-bold mb-6 uppercase tracking-wide text-gray-900"><?php _e('ΛΟΓΑΡΙΑΣΜΟΣ', 'eshop-theme'); ?></h3>
                    <?php
                    $footer_account_markup = '';
                    if (has_nav_menu('footer-account')) {
                        $footer_account_markup = wp_nav_menu(array(
                            'theme_location' => 'footer-account',
                            'container' => false,
                            'menu_class' => 'space-y-3 text-sm text-gray-600',
                            'fallback_cb' => false,
                            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                            'echo' => false,
                        ));
                    }

                    if (!empty($footer_account_markup) && strpos($footer_account_markup, '<li') !== false) {
                        echo $footer_account_markup;
                    } else {
                        echo '<ul class="space-y-3 text-sm text-gray-600">';
                        echo '<li><a href="' . esc_url(home_url('/my-account/')) . '">' . esc_html__('My Account', 'eshop-theme') . '</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/cart/')) . '">' . esc_html__('Cart', 'eshop-theme') . '</a></li>';
                        echo '<li><a href="' . esc_url(home_url('/checkout/')) . '">' . esc_html__('Checkout', 'eshop-theme') . '</a></li>';
                        echo '</ul>';
                    }
                    ?>
                </div>

            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-gray-200 mt-16 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm mb-4 md:mb-0">
                    &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All rights reserved.', 'eshop-theme'); ?>
                </p>
                <div class="payment-methods flex space-x-4 grayscale opacity-60 text-gray-600">
                    <i class="fab fa-cc-visa text-2xl"></i>
                    <i class="fab fa-cc-mastercard text-2xl"></i>
                    <i class="fab fa-cc-amex text-2xl"></i>
                    <i class="fab fa-cc-paypal text-2xl"></i>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-6 right-6 bg-primary text-white p-3 rounded-full shadow-lg hover:bg-primary-dark transition-all duration-300 opacity-0 invisible z-50">
        <i class="fas fa-chevron-up icon"></i>
    </button>

    <!-- Flying Cart Component -->
    <?php if (class_exists('WooCommerce')) : ?>
        <?php get_template_part('template-parts/components/flying-cart'); ?>
    <?php endif; ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
