    </div><!-- #content -->

    <!-- Footer -->
    <footer id="colophon" class="site-footer bg-white mt-24 py-12 border-t border-gray-200">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

                <!-- Brand & Newsletter -->
                <div>
                    <h3 class="font-serif text-2xl font-semibold text-gray-900 mb-4">
                        <?php bloginfo('name'); ?>
                    </h3>
                    <p class="text-gray-500 text-sm mb-6">
                        Your daily dose of fashion inspiration, bringing you curated collections and the latest trends.
                    </p>

                    <!-- Newsletter Signup -->
                    <div>
                        <h4 class="uppercase tracking-wider text-sm font-semibold text-gray-900 mb-4">Newsletter</h4>
                        <p class="text-gray-500 text-sm mb-4">Subscribe for exclusive offers and style updates.</p>
                        <form class="flex" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                            <input type="hidden" name="action" value="newsletter_signup">
                            <?php wp_nonce_field('newsletter_signup', 'newsletter_nonce'); ?>
                            <input class="w-full text-sm border border-gray-300 p-2 focus:outline-none focus:border-primary"
                                   placeholder="Your email"
                                   type="email"
                                   name="newsletter_email"
                                   required />
                            <button class="bg-primary text-white px-4 hover:opacity-90 transition-opacity" type="submit">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- My Account -->
                <div>
                    <h3 class="uppercase tracking-wider text-sm font-semibold text-gray-900 mb-4">My Account</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/account/">Account</a></li>
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/log-in/">Log In</a></li>
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/register/">Register</a></li>
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/password-reset/">Password Reset</a></li>
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/profile/">Profile</a></li>
                    </ul>
                </div>

                <!-- Orders & Returns -->
                <div>
                    <h3 class="uppercase tracking-wider text-sm font-semibold text-gray-900 mb-4">Orders & Returns</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/%ce%b1%ce%af%cf%84%ce%b7%ce%bc%ce%b1-%ce%b1%ce%bb%ce%bb%ce%b1%ce%b3%ce%ae%cf%82-%ce%b5%cf%80%ce%b9%cf%83%cf%84%cf%81%ce%bf%cf%86%ce%ae%cf%82/">Αίτημα αλλαγής/επιστροφής</a></li>
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/refund_returns/">Refund and Returns Policy</a></li>
                    </ul>
                </div>

                <!-- Help & Information -->
                <div>
                    <h3 class="uppercase tracking-wider text-sm font-semibold text-gray-900 mb-4">Help & Information</h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/contact-us/">Επικοινωνια</a></li>
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/%cf%83%cf%85%cf%87%ce%bd%ce%ad%cf%82-%ce%b5%cf%81%cf%89%cf%84%ce%ae%cf%83%ce%b5%ce%b9%cf%82/">Συχνές Ερωτήσεις</a></li>
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/about-us/">Σχετικά με εμάς</a></li>
                        <li><a class="hover:text-gray-900 transition-colors" href="https://www.thewalkingcompany.gr/%ce%b4%ce%ae%ce%bb%cf%89%cf%83%ce%b7-%cf%80%cf%81%ce%bf%cf%83%ce%b2%ce%b1%cf%83%ce%b7%ce%bc%cf%8c%cf%84%ce%b7%cf%84%ce%b1%cf%82/">Δήλωση Προσβασημότητας</a></li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="border-t border-gray-200 mt-8 pt-6 text-center text-sm text-gray-500">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All Rights Reserved.</p>

                    <!-- Social Links -->
                    <div class="social-links flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors duration-200" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors duration-200" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary transition-colors duration-200" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
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