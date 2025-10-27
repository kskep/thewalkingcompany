<?php
/**
 * Product Information Accordions Component
 * 
 * Displays product information in accordion format
 *
 * @package thewalkingtheme
 */

defined('ABSPATH') || exit;

global $product;

if (!$product) {
    return;
}
?>

<div class="product-accordions-container">
    
    <!-- ΧΑΡΑΚΤΗΡΙΣΤΙΚΑ (Characteristics) -->
    <div class="product-accordion">
        <button class="accordion-header" 
                aria-expanded="false" 
                aria-controls="panel-characteristics" 
                id="accordion-characteristics">
            <span class="accordion-title"><?php _e('ΧΑΡΑΚΤΗΡΙΣΤΙΚΑ', 'thewalkingtheme'); ?></span>
            <svg class="accordion-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14m0 0l6-6m-6 6l-6-6"/>
            </svg>
        </button>
        <div class="accordion-panel" 
             id="panel-characteristics" 
             role="region" 
             aria-labelledby="accordion-characteristics" 
             aria-hidden="true">
            <div class="accordion-content">
                <?php
                // Check if product has attributes to display
                $attributes = $product->get_attributes();
                $has_attributes = !empty($attributes);
                
                if ($has_attributes) :
                ?>
                    <div class="product-attributes">
                        <?php foreach ($attributes as $attribute) :
                            if ($attribute->get_variation()) {
                                continue; // Skip variation attributes
                            }
                            $attribute_name = $attribute->get_name();
                            $attribute_label = wc_attribute_label($attribute_name, $product);
                            $attribute_values = wp_get_post_terms($product->get_id(), $attribute_name, array('fields' => 'names'));
                        ?>
                            <div class="attribute-row">
                                <dt class="attribute-label"><?php echo esc_html($attribute_label); ?></dt>
                                <dd class="attribute-value"><?php echo esc_html(implode(', ', $attribute_values)); ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="no-attributes">
                        <p><?php _e('Δεν υπάρχουν διαθέσιμα χαρακτηριστικά για αυτό το προϊόν.', 'thewalkingtheme'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ΑΠΟΣΤΟΛΕΣ & ΕΠΙΣΤΡΟΦΕΣ (Shipping & Returns) -->
    <div class="product-accordion">
        <button class="accordion-header" 
                aria-expanded="false" 
                aria-controls="panel-shipping" 
                id="accordion-shipping">
            <span class="accordion-title"><?php _e('ΑΠΟΣΤΟΛΕΣ & ΕΠΙΣΤΡΟΦΕΣ', 'thewalkingtheme'); ?></span>
            <svg class="accordion-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14m0 0l6-6m-6 6l-6-6"/>
            </svg>
        </button>
        <div class="accordion-panel" 
             id="panel-shipping" 
             role="region" 
             aria-labelledby="accordion-shipping" 
             aria-hidden="true">
            <div class="accordion-content">
                <div class="shipping-info">
                    <h4><?php _e('Τρόποι Αποστολής', 'thewalkingtheme'); ?></h4>
                    <ul class="shipping-methods">
                        <li>
                            <strong><?php _e('Standard Delivery', 'thewalkingtheme'); ?></strong>
                            <span>3-5 εργάσιμες ημέρες - €4.99</span>
                        </li>
                        <li>
                            <strong><?php _e('Express Delivery', 'thewalkingtheme'); ?></strong>
                            <span>1-2 εργάσιμες ημέρες - €8.99</span>
                        </li>
                        <li>
                            <strong><?php _e('Αυθημερόν Παράδοση', 'thewalkingtheme'); ?></strong>
                            <span>Θεσσαλονίκη/Αθήνα - €12.99</span>
                        </li>
                    </ul>
                    
                    <h4><?php _e('Πολιτική Επιστροφών', 'thewalkingtheme'); ?></h4>
                    <div class="returns-policy">
                        <p><?php _e('Δικαιούστε επιστροφή ή ανταλλαγή εντός 14 ημερών από την παραλαβή του προϊόντος.', 'thewalkingtheme'); ?></p>
                        <ul>
                            <li><?php _e('Το προϊόν πρέπει να είναι σε άριστη κατάσταση', 'thewalkingtheme'); ?></li>
                            <li><?php _e('Με ετικέτες και συσκευασία', 'thewalkingtheme'); ?></li>
                            <li><?php _e('Δεν δέχονται επιστροφές σε εσώρρουχα για λόγους υγιεινής', 'thewalkingtheme'); ?></li>
                        </ul>
                        <p><strong><?php _e('Χρέωση Επιστροφής:', 'thewalkingtheme'); ?></strong> <?php _e('Ο πελάτης επιβαρύνεται με τα έξοδα αποστολής', 'thewalkingtheme'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ΧΡΕΙΑΖΕΣΑΙ ΒΟΗΘΕΙΑ (Need Help) -->
    <div class="product-accordion">
        <button class="accordion-header" 
                aria-expanded="false" 
                aria-controls="panel-help" 
                id="accordion-help">
            <span class="accordion-title"><?php _e('ΧΡΕΙΑΖΕΣΑΙ ΒΟΗΘΕΙΑ;', 'thewalkingtheme'); ?></span>
            <svg class="accordion-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14m0 0l6-6m-6 6l-6-6"/>
            </svg>
        </button>
        <div class="accordion-panel" 
             id="panel-help" 
             role="region" 
             aria-labelledby="accordion-help" 
             aria-hidden="true">
            <div class="accordion-content">
                <div class="help-section">
                    <h4><?php _e('Επικοινωνία', 'thewalkingtheme'); ?></h4>
                    <div class="contact-info">
                        <p>
                            <strong><?php _e('Τηλέφωνο:', 'thewalkingtheme'); ?></strong>
                            <a href="tel:+302311234567">+30 231 123 4567</a>
                        </p>
                        <p>
                            <strong><?php _e('Email:', 'thewalkingtheme'); ?></strong>
                            <a href="mailto:info@thewalkingcompany.gr">info@thewalkingcompany.gr</a>
                        </p>
                        <p>
                            <strong><?php _e('Ώρες Λειτουργίας:', 'thewalkingtheme'); ?></strong>
                            <?php _e('Δευτέρα - Παρασκευή: 9:00 - 18:00', 'thewalkingtheme'); ?>
                        </p>
                    </div>
                    
                    <h4><?php _e('Μεγεθολόγιο', 'thewalkingtheme'); ?></h4>
                    <p><?php _e('Δείτε το μεγεθολόγιό μας για να επιλέξετε το σωστό νούμερο.', 'thewalkingtheme'); ?></p>
                    <a href="#" class="btn-text size-guide-link">
                        <?php _e('Άνοιγμα Μεγεθολογίου', 'thewalkingtheme'); ?>
                    </a>
                    
                    <h4><?php _e('Συχνές Ερωτήσεις', 'thewalkingtheme'); ?></h4>
                    <div class="faq-section">
                        <details>
                            <summary><?php _e('Πώς μπορώ να παραγγείλω μεγαλύτερο νούμερο;', 'thewalkingtheme'); ?></summary>
                            <p><?php _e('Επικοινωνήστε μαζί μας και θα προσπαθήσουμε να σας βοηθήσουμε.', 'thewalkingtheme'); ?></p>
                        </details>
                        <details>
                            <summary><?php _e('Τι γίνεται αν το νούμερο δεν ταιριάζει;', 'thewalkingtheme'); ?></summary>
                            <p><?php _e('Μπορείτε να το επιστρέψετε εντός 14 ημερών και να παραγγείλετε άλλο νούμερο.', 'thewalkingtheme'); ?></p>
                        </details>
                        <details>
                            <summary><?php _e('Πώς μπορώ να επιστρέψω ένα προϊόν;', 'thewalkingtheme'); ?></summary>
                            <p><?php _e('Επικοινωνήστε μαζί μας και θα σας στείλουμε τις οδηγίες επιστροφής.', 'thewalkingtheme'); ?></p>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<script>
// Initialize product accordions when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const accordionsContainer = document.querySelector('.product-accordions-container');
    if (accordionsContainer && typeof ProductAccordions !== 'undefined') {
        new ProductAccordions(accordionsContainer);
    }
});
</script>