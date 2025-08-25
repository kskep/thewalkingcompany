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

<?php
// Add filter modal HTML to footer for WooCommerce pages
if (class_exists('WooCommerce') && (is_shop() || is_product_category() || is_product_tag())) : ?>
<!-- Filter Modal HTML (Added via Footer) -->
<div id="filter-backdrop" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 40; opacity: 0; visibility: hidden; transition: all 0.3s ease;" class="hidden"></div>

<div id="filter-drawer" style="position: fixed; top: 0; right: 0; bottom: 0; width: 100%; max-width: 400px; background: white; z-index: 50; transform: translateX(100%); transition: transform 0.3s ease; box-shadow: -4px 0 20px rgba(0,0,0,0.1);" role="dialog" aria-modal="true">
    <!-- Drawer Header -->
    <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #e5e7eb; background: white;">
        <h2 style="font-size: 18px; font-weight: 600; color: #111827; text-transform: uppercase; letter-spacing: 0.05em;">
            FILTERS
        </h2>
        <button id="close-filters" style="padding: 8px; color: #9ca3af; background: none; border: none; cursor: pointer;" aria-label="Close Filters">
            <i class="fas fa-times" style="font-size: 18px;"></i>
        </button>
    </div>

    <!-- Drawer Content -->
    <div style="padding: 24px;">
        <div style="margin-bottom: 24px;">
            <h4 style="font-size: 14px; font-weight: 600; color: #111827; margin-bottom: 12px;">
                Test Filters
            </h4>
            <p style="font-size: 14px; color: #6b7280; margin-bottom: 16px;">Filter functionality is working!</p>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox">
                    <span style="font-size: 14px; color: #374151;">Test Option 1</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox">
                    <span style="font-size: 14px; color: #374151;">Test Option 2</span>
                </label>
            </div>
        </div>
    </div>

    <!-- Drawer Footer -->
    <div style="display: flex; justify-content: space-between; padding: 16px 24px; border-top: 1px solid #e5e7eb; background: #f9fafb;">
        <button id="clear-filters" style="padding: 8px 16px; color: #6b7280; background: none; border: none; cursor: pointer;">
            Clear All
        </button>
        <button id="apply-filters" style="padding: 8px 24px; background: #ee81b3; color: white; border: none; cursor: pointer;">
            Apply Filters
        </button>
    </div>
</div>

<style>
#filter-backdrop.show { opacity: 1 !important; visibility: visible !important; }
#filter-drawer.open { transform: translateX(0) !important; }
body.overflow-hidden { overflow: hidden !important; }
.hidden { display: none !important; }
</style>
<?php endif; ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>