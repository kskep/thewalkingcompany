/**
 * Authentication Modal JavaScript Component
 * Handles login/register modals, form validation, and AJAX submission
 */

class AuthModal {
    constructor() {
        this.init();
        this.bindEvents();
        this.setupFormValidation();
    }

    init() {
        this.currentModal = null;
        this.validationRules = {
            email: {
                required: true,
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: 'Please enter a valid email address'
            },
            password: {
                required: true,
                minLength: 8,
                message: 'Password must be at least 8 characters'
            },
            firstName: {
                required: true,
                minLength: 2,
                message: 'First name must be at least 2 characters'
            },
            lastName: {
                required: true,
                minLength: 2,
                message: 'Last name must be at least 2 characters'
            },
            confirmPassword: {
                required: true,
                match: 'password',
                message: 'Passwords do not match'
            }
        };
    }

    bindEvents() {
        // Modal trigger events
        $(document).on('click', '[data-action="open-login-modal"]', (e) => {
            e.preventDefault();
            this.openLogin();
        });

        $(document).on('click', '[data-action="open-register-modal"]', (e) => {
            e.preventDefault();
            this.openRegister();
        });

        // Modal close events
        $(document).on('click', '.modal-close', (e) => {
            e.preventDefault();
            this.closeModal();
        });

        $(document).on('click', '.modal-backdrop', (e) => {
            if (e.target === e.currentTarget) {
                this.closeModal();
            }
        });

        // Form toggle events
        $(document).on('click', '.toggle-register', (e) => {
            e.preventDefault();
            this.closeModal();
            setTimeout(() => this.openRegister(), 300);
        });

        $(document).on('click', '.toggle-login', (e) => {
            e.preventDefault();
            this.closeModal();
            setTimeout(() => this.openLogin(), 300);
        });

        // Form submission events
        $(document).on('submit', '.login-form', (e) => {
            e.preventDefault();
            this.handleLogin($(e.target));
        });

        $(document).on('submit', '.register-form', (e) => {
            e.preventDefault();
            this.handleRegister($(e.target));
        });

        // Password toggle events
        $(document).on('click', '.password-toggle', (e) => {
            e.preventDefault();
            this.togglePassword($(e.target));
        });

        // Real-time validation
        $(document).on('input', '.auth-form input', (e) => {
            this.validateField($(e.target));
        });

        // Keyboard events
        $(document).on('keydown', (e) => {
            if (e.key === 'Escape' && this.currentModal) {
                this.closeModal();
            }
        });
    }

    setupFormValidation() {
        // Initialize validation state
        this.isValidating = false;
    }

    openLogin() {
        this.currentModal = 'login';
        $('#login-modal').removeClass('hidden').addClass('active');
        $('.modal-backdrop').removeClass('hidden').addClass('active');
        $('body').addClass('modal-open');
        
        // Focus first input
        setTimeout(() => {
            $('#login-email').focus();
        }, 100);

        this.trapFocus('#login-modal');
    }

    openRegister() {
        this.currentModal = 'register';
        $('#register-modal').removeClass('hidden').addClass('active');
        $('.modal-backdrop').removeClass('hidden').addClass('active');
        $('body').addClass('modal-open');
        
        // Focus first input
        setTimeout(() => {
            $('#register-first-name').focus();
        }, 100);

        this.trapFocus('#register-modal');
    }

    closeModal() {
        if (this.currentModal) {
            $(`#${this.currentModal}-modal`).removeClass('active');
            $('.modal-backdrop').removeClass('active');
            
            setTimeout(() => {
                $(`#${this.currentModal}-modal`).addClass('hidden');
                $('.modal-backdrop').addClass('hidden');
                $('body').removeClass('modal-open');
                this.currentModal = null;
            }, 300);
        }
    }

    handleLogin($form) {
        if (!this.validateForm($form)) {
            return;
        }

        const formData = new FormData($form[0]);
        formData.append('action', 'eshop_login_user');
        formData.append('nonce', eshop_auth_ajax.nonce);

        this.submitForm('login', $form, formData);
    }

    handleRegister($form) {
        if (!this.validateForm($form)) {
            return;
        }

        const formData = new FormData($form[0]);
        formData.append('action', 'eshop_register_user');
        formData.append('nonce', eshop_auth_ajax.nonce);

        this.submitForm('register', $form, formData);
    }

    submitForm(type, $form, formData) {
        const $submitBtn = $form.find('.auth-submit-btn');
        const originalText = $submitBtn.text();

        // Clear previous errors
        this.clearFormErrors($form);

        // Show loading state
        $submitBtn.addClass('loading-state').prop('disabled', true).text('Processing...');

        $.ajax({
            url: eshop_auth_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                this.handleResponse(response, $form, type);
            },
            error: (xhr, status, error) => {
                this.handleNetworkError(xhr, $form);
            },
            complete: () => {
                $submitBtn.removeClass('loading-state').prop('disabled', false).text(originalText);
            }
        });
    }

    handleResponse(response, $form, type) {
        if (response.success) {
            this.showSuccess($form, response.data.message);
            
            setTimeout(() => {
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else {
                    window.location.reload();
                }
            }, 1500);
        } else {
            if (response.data.field_errors) {
                this.showFieldErrors($form, response.data.field_errors);
            } else {
                this.showError($form, response.data.message || 'An error occurred. Please try again.');
            }
        }
    }

    handleNetworkError(xhr, $form) {
        let message = 'Connection error. Please try again.';
        
        if (xhr.status === 429) {
            message = 'Too many requests. Please wait before trying again.';
        } else if (xhr.status === 500) {
            message = 'Server error. Please try again later.';
        }
        
        this.showError($form, message);
    }

    validateForm($form) {
        let isValid = true;
        const inputs = $form.find('input[required], input[data-validate]');

        inputs.each((index, input) => {
            const $input = $(input);
            if (!this.validateField($input)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField($input) {
        const fieldName = $input.attr('name');
        const value = $input.val().trim();
        const rules = this.validationRules[fieldName];

        // Clear previous error
        this.clearFieldError($input);

        if (!rules) return true;

        // Required validation
        if (rules.required && !value) {
            this.showFieldError($input, `${this.getFieldLabel($input)} is required`);
            return false;
        }

        if (!value) return true; // Skip other validations if field is empty but not required

        // Pattern validation
        if (rules.pattern && !rules.pattern.test(value)) {
            this.showFieldError($input, rules.message);
            return false;
        }

        // Minimum length validation
        if (rules.minLength && value.length < rules.minLength) {
            this.showFieldError($input, rules.message);
            return false;
        }

        // Password match validation
        if (rules.match) {
            const matchValue = $input.closest('form').find(`[name="${rules.match}"]`).val();
            if (value !== matchValue) {
                this.showFieldError($input, rules.message);
                return false;
            }
        }

        // Password strength validation
        if (fieldName === 'password') {
            this.updatePasswordStrength($input, value);
        }

        return true;
    }

    updatePasswordStrength($input, password) {
        const $strength = $input.siblings('.password-strength');
        if (!$strength.length) return;

        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;

        $strength.removeClass('weak medium strong');
        
        if (strength < 3) {
            $strength.addClass('weak').text('Weak password');
        } else if (strength < 5) {
            $strength.addClass('medium').text('Medium password');
        } else {
            $strength.addClass('strong').text('Strong password');
        }
    }

    getFieldLabel($input) {
        const label = $input.closest('.auth-form-group').find('label').text();
        return label || $input.attr('placeholder') || 'Field';
    }

    showFieldError($input, message) {
        $input.addClass('error');
        
        let $error = $input.siblings('.field-error');
        if (!$error.length) {
            $error = $('<span class="field-error"></span>');
            $input.after($error);
        }
        $error.text(message).show();
    }

    clearFieldError($input) {
        $input.removeClass('error');
        $input.siblings('.field-error').remove();
    }

    clearFormErrors($form) {
        $form.find('.auth-global-error, .auth-global-success').remove();
        $form.find('.field-error').remove();
        $form.find('input').removeClass('error');
    }

    showError($form, message) {
        this.clearFormErrors($form);
        const $error = $('<div class="auth-global-error"></div>').text(message);
        $form.prepend($error);
    }

    showSuccess($form, message) {
        this.clearFormErrors($form);
        const $success = $('<div class="auth-global-success"></div>').text(message);
        $form.prepend($success);
    }

    showFieldErrors($form, errors) {
        this.clearFormErrors($form);
        
        Object.keys(errors).forEach(fieldName => {
            const $input = $form.find(`[name="${fieldName}"]`);
            if ($input.length) {
                this.showFieldError($input, errors[fieldName]);
            }
        });
    }

    togglePassword($button) {
        const $input = $button.siblings('input');
        const type = $input.attr('type');
        
        if (type === 'password') {
            $input.attr('type', 'text');
            $button.html('<i class="far fa-eye-slash"></i>');
        } else {
            $input.attr('type', 'password');
            $button.html('<i class="far fa-eye"></i>');
        }
    }

    trapFocus(modalSelector) {
        const modal = $(modalSelector)[0];
        if (!modal) return;

        const focusableElements = modal.querySelectorAll(
            'button, input, textarea, select, a[href], [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        const handleTabKey = (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey && document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                } else if (!e.shiftKey && document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        };

        modal.addEventListener('keydown', handleTabKey);
        
        // Store the handler for cleanup
        modal._tabHandler = handleTabKey;
    }
}

// Initialize authentication modal when DOM is ready
$(document).ready(function() {
    if (typeof eshop_auth_ajax !== 'undefined') {
        window.authModal = new AuthModal();
    }
});