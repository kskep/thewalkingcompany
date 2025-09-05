<?php
/**
 * Authentication Modal Template Component
 * Renders login and register modals for logged-out users
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Only render modals for logged-out users
if (is_user_logged_in()) {
    return;
}
?>

<!-- Modal Backdrop -->
<div class="modal-backdrop hidden" id="modal-backdrop"></div>

<!-- Login Modal -->
<div class="auth-modal hidden" id="login-modal" role="dialog" aria-labelledby="login-title" aria-describedby="login-desc">
    <div class="auth-modal-header">
        <h2 id="login-title" class="auth-modal-title">Welcome Back</h2>
        <button class="modal-close" aria-label="Close login modal">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="auth-modal-body">
        <p id="login-desc" class="auth-modal-description">
            <?php _e('Sign in to your account to access exclusive features and personalized content.', 'eshop-theme'); ?>
        </p>
        
        <form class="auth-form login-form" method="post">
            <div class="auth-form-group">
                <label for="login-email"><?php _e('Email Address', 'eshop-theme'); ?></label>
                <input type="email" 
                       id="login-email" 
                       name="email" 
                       required 
                       data-validate="email"
                       placeholder="<?php _e('Enter your email address', 'eshop-theme'); ?>"
                       aria-describedby="email-error">
            </div>

            <div class="auth-form-group">
                <label for="login-password"><?php _e('Password', 'eshop-theme'); ?></label>
                <div class="password-field">
                    <input type="password" 
                           id="login-password" 
                           name="password" 
                           required 
                           data-validate="password"
                           placeholder="<?php _e('Enter your password', 'eshop-theme'); ?>"
                           aria-describedby="password-error">
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="auth-form-checkbox">
                <input type="checkbox" id="login-remember" name="remember" value="1">
                <label for="login-remember"><?php _e('Keep me signed in for 30 days', 'eshop-theme'); ?></label>
            </div>

            <button type="submit" class="auth-submit-btn">
                <span><?php _e('Sign In', 'eshop-theme'); ?></span>
            </button>

            <div class="forgot-password-link">
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" target="_blank">
                    <?php _e('Forgot your password?', 'eshop-theme'); ?>
                </a>
            </div>
        </form>

        <div class="auth-form-toggle">
            <?php _e("New to our community?", 'eshop-theme'); ?> 
            <a href="#" class="toggle-register"><?php _e('Create an account', 'eshop-theme'); ?></a>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="auth-modal hidden" id="register-modal" role="dialog" aria-labelledby="register-title" aria-describedby="register-desc">
    <div class="auth-modal-header">
        <h2 id="register-title" class="auth-modal-title">Join Our Community</h2>
        <button class="modal-close" aria-label="Close register modal">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="auth-modal-body">
        <p id="register-desc" class="auth-modal-description">
            <?php _e('Create your account to unlock exclusive access to our premium collection and member benefits.', 'eshop-theme'); ?>
        </p>
        
        <form class="auth-form register-form" method="post">
            <div class="auth-form-group half">
                <label for="register-first-name"><?php _e('First Name', 'eshop-theme'); ?></label>
                <input type="text" 
                       id="register-first-name" 
                       name="firstName" 
                       required 
                       data-validate="firstName"
                       placeholder="<?php _e('Your first name', 'eshop-theme'); ?>"
                       aria-describedby="firstName-error">
            </div>

            <div class="auth-form-group half">
                <label for="register-last-name"><?php _e('Last Name', 'eshop-theme'); ?></label>
                <input type="text" 
                       id="register-last-name" 
                       name="lastName" 
                       required 
                       data-validate="lastName"
                       placeholder="<?php _e('Your last name', 'eshop-theme'); ?>"
                       aria-describedby="lastName-error">
            </div>

            <div class="auth-form-group">
                <label for="register-email"><?php _e('Email Address', 'eshop-theme'); ?></label>
                <input type="email" 
                       id="register-email" 
                       name="email" 
                       required 
                       data-validate="email"
                       placeholder="<?php _e('Enter your email address', 'eshop-theme'); ?>"
                       aria-describedby="email-error">
            </div>

            <div class="auth-form-group">
                <label for="register-password"><?php _e('Create Password', 'eshop-theme'); ?></label>
                <div class="password-field">
                    <input type="password" 
                           id="register-password" 
                           name="password" 
                           required 
                           data-validate="password"
                           placeholder="<?php _e('Create a strong password', 'eshop-theme'); ?>"
                           aria-describedby="password-error">
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength"></div>
            </div>

            <div class="auth-form-group">
                <label for="register-confirm-password"><?php _e('Confirm Password', 'eshop-theme'); ?></label>
                <div class="password-field">
                    <input type="password" 
                           id="register-confirm-password" 
                           name="confirmPassword" 
                           required 
                           data-validate="confirmPassword"
                           placeholder="<?php _e('Confirm your password', 'eshop-theme'); ?>"
                           aria-describedby="confirmPassword-error">
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="auth-form-checkbox">
                <input type="checkbox" id="register-terms" name="terms" value="1" required>
                <label for="register-terms">
                    <?php _e('I agree to the', 'eshop-theme'); ?> 
                    <a href="<?php echo esc_url(get_privacy_policy_url()); ?>" target="_blank">
                        <?php _e('Terms of Service', 'eshop-theme'); ?>
                    </a>
                    <?php _e('and', 'eshop-theme'); ?>
                    <a href="<?php echo esc_url(get_privacy_policy_url()); ?>" target="_blank">
                        <?php _e('Privacy Policy', 'eshop-theme'); ?>
                    </a>
                </label>
            </div>

            <div class="auth-form-checkbox">
                <input type="checkbox" id="register-newsletter" name="newsletter" value="1">
                <label for="register-newsletter">
                    <?php _e('Subscribe to our newsletter for exclusive offers, style tips, and early access to new collections', 'eshop-theme'); ?>
                </label>
            </div>

            <button type="submit" class="auth-submit-btn">
                <span><?php _e('Create My Account', 'eshop-theme'); ?></span>
            </button>
        </form>

        <div class="auth-form-toggle">
            <?php _e('Already part of our community?', 'eshop-theme'); ?> 
            <a href="#" class="toggle-login"><?php _e('Sign in here', 'eshop-theme'); ?></a>
        </div>
    </div>
</div>