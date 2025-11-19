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
                        <?php 
                        if (has_custom_logo()) {
                            the_custom_logo();
                        } else {
                            ?>
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="text-2xl font-bold tracking-tighter uppercase text-gray-900 no-underline">
                                <?php bloginfo('name'); ?>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                    <p class="text-gray-600 mb-6 max-w-sm">
                        <?php echo get_bloginfo('description'); ?>
                    </p>
                    <div class="social-links flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-gray-900 transition-colors"><i class="fab fa-instagram text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-gray-900 transition-colors"><i class="fab fa-facebook-f text-xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-gray-900 transition-colors"><i class="fab fa-twitter text-xl"></i></a>
                    </div>
                </div>

                <!-- Column 2: Footer Main Menu -->
                <div class="footer-nav">
                    <h3 class="text-lg font-bold mb-6 uppercase tracking-wide text-gray-900"><?php _e('Explore', 'eshop-theme'); ?></h3>
                    <?php
                    if (has_nav_menu('footer-main')) {
                        wp_nav_menu(array(
                            'theme_location' => 'footer-main',
                            'container' => false,
                            'menu_class' => 'space-y-3 text-sm text-gray-600',
                            'fallback_cb' => false,
                            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        ));
                    } else {
                        // Fallback if no menu assigned
                        echo '<p class="text-sm text-gray-500">' . __('Please assign a menu to "Footer Main Menu" location.', 'eshop-theme') . '</p>';
                    }
                    ?>
                </div>

                <!-- Column 3: Account & Help -->
                <div class="footer-nav">
                    <h3 class="text-lg font-bold mb-6 uppercase tracking-wide text-gray-900"><?php _e('Account & Help', 'eshop-theme'); ?></h3>
                    <?php
                    if (has_nav_menu('footer-account')) {
                        wp_nav_menu(array(
                            'theme_location' => 'footer-account',
                            'container' => false,
                            'menu_class' => 'space-y-3 text-sm text-gray-600',
                            'fallback_cb' => false,
                            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                        ));
                    } else {
                         // Fallback if no menu assigned
                         echo '<p class="text-sm text-gray-500">' . __('Please assign a menu to "Footer Account Menu" location.', 'eshop-theme') . '</p>';
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