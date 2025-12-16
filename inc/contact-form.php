<?php
/**
 * Contact form handling and (optional) SMTP configuration.
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Optionally configure WordPress emails to use SMTP via constants.
 *
 * Define these in wp-config.php (or your environment):
 * - ESHOP_SMTP_HOST (required)
 * - ESHOP_SMTP_PORT (optional, default 587)
 * - ESHOP_SMTP_USER / ESHOP_SMTP_PASS (optional)
 * - ESHOP_SMTP_SECURE (optional: 'tls' or 'ssl')
 * - ESHOP_SMTP_FROM (optional)
 * - ESHOP_SMTP_FROM_NAME (optional)
 */
function eshop_configure_phpmailer_smtp($phpmailer) {
    if (!is_object($phpmailer)) {
        return;
    }

    if ($phpmailer->Mailer === 'smtp') {
        return;
    }

    $host = defined('ESHOP_SMTP_HOST') ? (string) ESHOP_SMTP_HOST : '';
    if ($host === '') {
        return;
    }

    $port = defined('ESHOP_SMTP_PORT') ? absint(ESHOP_SMTP_PORT) : 587;
    $user = defined('ESHOP_SMTP_USER') ? (string) ESHOP_SMTP_USER : '';
    $pass = defined('ESHOP_SMTP_PASS') ? (string) ESHOP_SMTP_PASS : '';
    $secure = defined('ESHOP_SMTP_SECURE') ? strtolower((string) ESHOP_SMTP_SECURE) : '';

    $from_email = defined('ESHOP_SMTP_FROM') ? (string) ESHOP_SMTP_FROM : '';
    $from_name = defined('ESHOP_SMTP_FROM_NAME') ? (string) ESHOP_SMTP_FROM_NAME : '';

    $phpmailer->isSMTP();
    $phpmailer->Host = $host;
    $phpmailer->Port = $port ?: 587;
    $phpmailer->SMTPAutoTLS = true;

    if (in_array($secure, array('tls', 'ssl'), true)) {
        $phpmailer->SMTPSecure = $secure;
    }

    if ($user !== '') {
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $user;
        $phpmailer->Password = $pass;
    } else {
        $phpmailer->SMTPAuth = false;
    }

    if ($from_email !== '' && is_email($from_email)) {
        $phpmailer->From = $from_email;
    }

    if ($from_name !== '') {
        $phpmailer->FromName = $from_name;
    }
}
add_action('phpmailer_init', 'eshop_configure_phpmailer_smtp');

/**
 * Handle contact form submission.
 */
function eshop_handle_contact_form_submission() {
    if (!isset($_POST['eshop_contact_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eshop_contact_nonce'])), 'eshop_contact_submit')) {
        wp_die('Security check failed');
    }

    $referer = wp_get_referer();
    if (!$referer) {
        $referer = home_url('/');
    }

    $honeypot = isset($_POST['contact_company']) ? sanitize_text_field(wp_unslash($_POST['contact_company'])) : '';
    if ($honeypot !== '') {
        wp_safe_redirect(add_query_arg('contact', 'success', $referer));
        exit;
    }

    $name = isset($_POST['contact_name']) ? sanitize_text_field(wp_unslash($_POST['contact_name'])) : '';
    $email = isset($_POST['contact_email']) ? sanitize_email(wp_unslash($_POST['contact_email'])) : '';
    $phone = isset($_POST['contact_phone']) ? sanitize_text_field(wp_unslash($_POST['contact_phone'])) : '';
    $subject_input = isset($_POST['contact_subject']) ? sanitize_text_field(wp_unslash($_POST['contact_subject'])) : '';
    $message_input = isset($_POST['contact_message']) ? sanitize_textarea_field(wp_unslash($_POST['contact_message'])) : '';

    if ($name === '' || $email === '' || !is_email($email) || $message_input === '') {
        wp_safe_redirect(add_query_arg('contact', 'invalid', $referer));
        exit;
    }

    $to = apply_filters('eshop_contact_form_recipient', get_option('admin_email'));
    if (!is_email($to)) {
        $to = get_option('admin_email');
    }

    $subject_label = $subject_input !== '' ? $subject_input : __('Website contact form', 'eshop-theme');
    $subject = sprintf(__('New message: %s', 'eshop-theme'), $subject_label);

    $lines = array(
        sprintf(__('Name: %s', 'eshop-theme'), $name),
        sprintf(__('Email: %s', 'eshop-theme'), $email),
    );

    if ($phone !== '') {
        $lines[] = sprintf(__('Phone: %s', 'eshop-theme'), $phone);
    }

    $lines[] = '';
    $lines[] = __('Message:', 'eshop-theme');
    $lines[] = $message_input;

    $headers = array('Content-Type: text/plain; charset=UTF-8');
    $headers[] = sprintf('Reply-To: %s <%s>', $name, $email);

    $sent = wp_mail($to, $subject, implode("\n", $lines), $headers);

    wp_safe_redirect(add_query_arg('contact', $sent ? 'success' : 'failed', $referer));
    exit;
}
add_action('admin_post_eshop_contact_form', 'eshop_handle_contact_form_submission');
add_action('admin_post_nopriv_eshop_contact_form', 'eshop_handle_contact_form_submission');

