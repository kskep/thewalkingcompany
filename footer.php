    </div><!-- #content -->

    <!-- Footer -->
    <footer id="colophon" class="site-footer">
        <div class="container mx-auto px-4">
            
            <!-- Footer Widgets -->
            <?php if (is_active_sidebar('footer-widgets')) : ?>
                <div class="footer-widgets py-8 border-b border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <?php dynamic_sidebar('footer-widgets'); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom py-6">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    
                    <!-- Copyright -->
                    <div class="footer-info text-center md:text-left">
                        <p class="text-gray-300 text-sm">
                            &copy; <?php echo date('Y'); ?> 
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="text-white hover:text-primary transition-colors duration-200">
                                <?php bloginfo('name'); ?>
                            </a>
                            . All rights reserved.
                        </p>
                    </div>
                    
                    <!-- Footer Menu -->
                    <nav class="footer-navigation">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer',
                            'menu_id' => 'footer-menu',
                            'menu_class' => 'flex space-x-6 text-sm',
                            'container' => false,
                            'fallback_cb' => false,
                        ));
                        ?>
                    </nav>
                    
                    <!-- Social Links -->
                    <div class="social-links flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-primary transition-colors duration-200" aria-label="Facebook">
                            <i class="fab fa-facebook-f icon"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-primary transition-colors duration-200" aria-label="Twitter">
                            <i class="fab fa-twitter icon"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-primary transition-colors duration-200" aria-label="Instagram">
                            <i class="fab fa-instagram icon"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-primary transition-colors duration-200" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in icon"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-6 right-6 bg-primary text-white p-3 rounded-full shadow-lg hover:bg-primary-dark transition-all duration-300 opacity-0 invisible">
        <i class="fas fa-chevron-up icon"></i>
    </button>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>