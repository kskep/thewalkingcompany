<?php
/**
 * Authentication Functions
 * Handles AJAX login and registration for authentication modals
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Login Handler
 */
function eshop_login_user() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'eshop_auth_nonce')) {
        wp_send_json_error(array(
            'message' => __('Security check failed', 'eshop-theme')
        ));
    }

    // Validate required fields
    if (empty($_POST['email']) || empty($_POST['password'])) {
        wp_send_json_error(array(
            'message' => __('Email and password are required', 'eshop-theme')
        ));
    }

    // Rate limiting check
    $ip_address = $_SERVER['REMOTE_ADDR'];
    eshop_check_login_attempts($ip_address);

    // Sanitize input
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password']; // Don't sanitize passwords
    $remember = !empty($_POST['remember']);

    // Validate email
    if (!is_email($email)) {
        wp_send_json_error(array(
            'message' => __('Please enter a valid email address', 'eshop-theme')
        ));
    }

    // Prepare credentials
    $credentials = array(
        'user_login' => $email,
        'user_password' => $password,
        'remember' => $remember
    );

    // Attempt login
    $user = wp_signon($credentials, false);

    if (is_wp_error($user)) {
        // Track failed attempt
        eshop_track_failed_login($ip_address);
        
        $error_message = $user->get_error_message();
        
        // Customize error messages
        if (strpos($error_message, 'incorrect username') !== false || 
            strpos($error_message, 'incorrect password') !== false) {
            $error_message = __('Invalid email or password', 'eshop-theme');
        }

        wp_send_json_error(array(
            'message' => $error_message
        ));
    }

    // Clear failed attempts on successful login
    eshop_clear_failed_attempts($ip_address);

    // Determine redirect URL
    $redirect_url = wc_get_account_endpoint_url('dashboard');
    if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
        $redirect_url = esc_url_raw($_POST['redirect_to']);
    }

    wp_send_json_success(array(
        'message' => __('Login successful! Redirecting...', 'eshop-theme'),
        'redirect' => $redirect_url
    ));
}
add_action('wp_ajax_eshop_login_user', 'eshop_login_user');
add_action('wp_ajax_nopriv_eshop_login_user', 'eshop_login_user');

/**
 * AJAX Registration Handler
 */
function eshop_register_user() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'eshop_auth_nonce')) {
        wp_send_json_error(array(
            'message' => __('Security check failed', 'eshop-theme')
        ));
    }

    // Validate required fields
    $required_fields = array('firstName', 'lastName', 'email', 'password', 'confirmPassword');
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(array(
                'message' => __('All fields are required', 'eshop-theme')
            ));
        }
    }

    // Terms and conditions check
    if (empty($_POST['terms'])) {
        wp_send_json_error(array(
            'message' => __('You must agree to the Terms & Conditions', 'eshop-theme')
        ));
    }

    // Sanitize input
    $first_name = sanitize_text_field($_POST['firstName']);
    $last_name = sanitize_text_field($_POST['lastName']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];
    $newsletter = !empty($_POST['newsletter']);

    // Validate input
    $validation_errors = eshop_validate_registration_data(array(
        'firstName' => $first_name,
        'lastName' => $last_name,
        'email' => $email,
        'password' => $password,
        'confirmPassword' => $confirm_password
    ));

    if (!empty($validation_errors)) {
        wp_send_json_error(array(
            'message' => __('Please correct the errors below', 'eshop-theme'),
            'field_errors' => $validation_errors
        ));
    }

    // Create user
    $user_id = wp_create_user($email, $password, $email);

    if (is_wp_error($user_id)) {
        $error_message = $user_id->get_error_message();
        
        // Customize error messages
        if (strpos($error_message, 'username_exists') !== false) {
            $error_message = __('An account with this email already exists', 'eshop-theme');
        }
        
        wp_send_json_error(array(
            'message' => $error_message
        ));
    }

    // Update user meta
    wp_update_user(array(
        'ID' => $user_id,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $first_name . ' ' . $last_name
    ));

    // Set user role to customer if WooCommerce is active
    if (class_exists('WooCommerce')) {
        $user = new WP_User($user_id);
        $user->set_role('customer');
    }

    // Newsletter subscription
    if ($newsletter) {
        update_user_meta($user_id, 'newsletter_subscription', true);
        // Hook for newsletter service integration
        do_action('eshop_user_newsletter_subscribe', $user_id, $email);
    }

    // Auto-login after registration
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);

    // Send welcome email
    eshop_send_welcome_email($user_id);

    // Determine redirect URL
    $redirect_url = wc_get_account_endpoint_url('dashboard');

    wp_send_json_success(array(
        'message' => __('Account created successfully! Welcome aboard!', 'eshop-theme'),
        'redirect' => $redirect_url
    ));
}
add_action('wp_ajax_eshop_register_user', 'eshop_register_user');
add_action('wp_ajax_nopriv_eshop_register_user', 'eshop_register_user');

/**
 * Validate registration data
 */
function eshop_validate_registration_data($data) {
    $errors = array();

    // First name validation
    if (empty($data['firstName']) || strlen($data['firstName']) < 2) {
        $errors['firstName'] = __('First name must be at least 2 characters', 'eshop-theme');
    }

    // Last name validation
    if (empty($data['lastName']) || strlen($data['lastName']) < 2) {
        $errors['lastName'] = __('Last name must be at least 2 characters', 'eshop-theme');
    }

    // Email validation
    if (empty($data['email']) || !is_email($data['email'])) {
        $errors['email'] = __('Please enter a valid email address', 'eshop-theme');
    } elseif (email_exists($data['email'])) {
        $errors['email'] = __('An account with this email already exists', 'eshop-theme');
    }

    // Password validation
    if (empty($data['password'])) {
        $errors['password'] = __('Password is required', 'eshop-theme');
    } elseif (strlen($data['password']) < 8) {
        $errors['password'] = __('Password must be at least 8 characters', 'eshop-theme');
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $data['password'])) {
        $errors['password'] = __('Password must contain uppercase, lowercase, and number', 'eshop-theme');
    }

    // Confirm password validation
    if (empty($data['confirmPassword'])) {
        $errors['confirmPassword'] = __('Please confirm your password', 'eshop-theme');
    } elseif ($data['password'] !== $data['confirmPassword']) {
        $errors['confirmPassword'] = __('Passwords do not match', 'eshop-theme');
    }

    return $errors;
}

/**
 * Rate limiting functions
 */
function eshop_check_login_attempts($ip_address) {
    $attempts = get_transient("login_attempts_$ip_address");
    if ($attempts && $attempts >= 5) {
        wp_send_json_error(array(
            'message' => __('Too many login attempts. Please try again in 15 minutes.', 'eshop-theme')
        ));
    }
}

function eshop_track_failed_login($ip_address) {
    $attempts = get_transient("login_attempts_$ip_address");
    $attempts = $attempts ? $attempts + 1 : 1;
    set_transient("login_attempts_$ip_address", $attempts, 15 * MINUTE_IN_SECONDS);
}

function eshop_clear_failed_attempts($ip_address) {
    delete_transient("login_attempts_$ip_address");
}

/**
 * Send welcome email to new users
 */
function eshop_send_welcome_email($user_id) {
    $user = get_user_by('ID', $user_id);
    if (!$user) return;

    $subject = sprintf(__('Welcome to %s!', 'eshop-theme'), get_bloginfo('name'));
    
    $message = sprintf(
        __('Hi %s,

Welcome to %s! Your account has been created successfully.

You can now:
- Browse our products
- Add items to your wishlist
- Track your orders
- Manage your account

Visit your account dashboard: %s

Thank you for joining us!

Best regards,
The %s Team', 'eshop-theme'),
        $user->first_name,
        get_bloginfo('name'),
        wc_get_account_endpoint_url('dashboard'),
        get_bloginfo('name')
    );

    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    wp_mail($user->user_email, $subject, wpautop($message), $headers);
}

/**
 * Enqueue authentication scripts and styles
 */
function eshop_enqueue_auth_assets() {
    // Only load for logged-out users
    if (is_user_logged_in()) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'eshop-auth-modal',
        get_template_directory_uri() . '/css/components/auth-modal.css',
        array(),
        '1.0.0'
    );

    // Enqueue JavaScript
    wp_enqueue_script(
        'eshop-auth-modal',
        get_template_directory_uri() . '/js/components/auth-modal.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize script
    wp_localize_script('eshop-auth-modal', 'eshop_auth_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eshop_auth_nonce'),
        'strings' => array(
            'processing' => __('Processing...', 'eshop-theme'),
            'login_success' => __('Login successful!', 'eshop-theme'),
            'register_success' => __('Account created successfully!', 'eshop-theme'),
            'error_generic' => __('An error occurred. Please try again.', 'eshop-theme'),
            'error_network' => __('Connection error. Please try again.', 'eshop-theme'),
        )
    ));
}
add_action('wp_enqueue_scripts', 'eshop_enqueue_auth_assets');