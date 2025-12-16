<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * Contact page with SMTP-backed form and Google Maps embed.
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="site-content" class="static-page-main contact-page-main" role="main">
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php
            $page_id = get_the_ID();
            $page_excerpt = has_excerpt($page_id) ? wp_strip_all_tags(get_the_excerpt()) : '';
            ?>

            <article id="page-<?php the_ID(); ?>" <?php post_class('static-page-article contact-page-article'); ?>>
                <div class="page-shell static-page-shell contact-page-shell">
                    <header class="static-page-hero contact-page-hero">
                        <div class="static-page-hero__content">
                            <p class="static-page-eyebrow"><?php echo esc_html(get_bloginfo('name')); ?></p>
                            <h1 class="static-page-title"><?php the_title(); ?></h1>
                            <?php if (!empty($page_excerpt)) : ?>
                                <p class="static-page-dek"><?php echo esc_html($page_excerpt); ?></p>
                            <?php endif; ?>
                        </div>
                    </header>

                    <div class="contact-grid">
                        <section class="contact-form-panel">
                            <?php
                            $status = isset($_GET['contact']) ? sanitize_text_field(wp_unslash($_GET['contact'])) : '';
                            if ($status === 'success') :
                                ?>
                                <div class="contact-alert contact-alert--success" role="status">
                                    <?php esc_html_e('Thanks — your message has been sent.', 'eshop-theme'); ?>
                                </div>
                            <?php elseif ($status === 'invalid') : ?>
                                <div class="contact-alert contact-alert--error" role="alert">
                                    <?php esc_html_e('Please fill in all required fields with a valid email.', 'eshop-theme'); ?>
                                </div>
                            <?php elseif ($status === 'failed') : ?>
                                <div class="contact-alert contact-alert--error" role="alert">
                                    <?php esc_html_e('Message could not be sent right now. Please try again later.', 'eshop-theme'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="static-page-content contact-page-content">
                                <?php the_content(); ?>
                            </div>

                            <form class="contact-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="eshop_contact_form">
                                <?php wp_nonce_field('eshop_contact_submit', 'eshop_contact_nonce'); ?>

                                <div class="contact-form-row">
                                    <label class="contact-label" for="contact_name"><?php esc_html_e('Name', 'eshop-theme'); ?> *</label>
                                    <input class="contact-input" id="contact_name" name="contact_name" type="text" autocomplete="name" required>
                                </div>

                                <div class="contact-form-row">
                                    <label class="contact-label" for="contact_email"><?php esc_html_e('Email', 'eshop-theme'); ?> *</label>
                                    <input class="contact-input" id="contact_email" name="contact_email" type="email" autocomplete="email" required>
                                </div>

                                <div class="contact-form-row">
                                    <label class="contact-label" for="contact_phone"><?php esc_html_e('Phone', 'eshop-theme'); ?></label>
                                    <input class="contact-input" id="contact_phone" name="contact_phone" type="text" autocomplete="tel">
                                </div>

                                <div class="contact-form-row">
                                    <label class="contact-label" for="contact_subject"><?php esc_html_e('Subject', 'eshop-theme'); ?></label>
                                    <input class="contact-input" id="contact_subject" name="contact_subject" type="text">
                                </div>

                                <div class="contact-form-row">
                                    <label class="contact-label" for="contact_message"><?php esc_html_e('Message', 'eshop-theme'); ?> *</label>
                                    <textarea class="contact-textarea" id="contact_message" name="contact_message" rows="7" required></textarea>
                                </div>

                                <div class="contact-hp" aria-hidden="true">
                                    <label for="contact_company">Company</label>
                                    <input id="contact_company" name="contact_company" type="text" tabindex="-1" autocomplete="off">
                                </div>

                                <button class="btn-primary" type="submit">
                                    <?php esc_html_e('Send message', 'eshop-theme'); ?>
                                </button>
                            </form>
                        </section>

                        <aside class="contact-map-panel">
                            <h2 class="contact-panel-title"><?php esc_html_e('Find us', 'eshop-theme'); ?></h2>
                            <div class="contact-map" aria-label="<?php esc_attr_e('Google Map', 'eshop-theme'); ?>">
                                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d2726.7084382951202!2d28.221291!3d36.448682!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x149561ef6ff1a761%3A0x729238394d99425e!2sSofokli%20Venizelou%2097%2C%20Rodos%20851%2000!5e1!3m2!1sen!2sgr!4v1765878745200!5m2!1sen!2sgr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </aside>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    <?php else : ?>
        <section class="page-shell static-page-shell contact-page-shell">
            <p class="static-page-dek"><?php esc_html_e('Content is not available right now.', 'eshop-theme'); ?></p>
        </section>
    <?php endif; ?>
</main>

<?php get_footer(); ?>

