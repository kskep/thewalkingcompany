/**
 * E-Shop Theme JavaScript
 */

(function($) {
    'use strict';

    // DOM Ready
    $(document).ready(function() {
        
        // Mobile Menu Toggle
        $('.mobile-menu-toggle').on('click', function() {
            $('#mobile-navigation').toggleClass('hidden');
            $(this).find('i').toggleClass('fa-bars fa-times');
        });

        // Search Toggle
        $('.search-toggle').on('click', function() {
            $('#search-form').toggleClass('hidden');
            if (!$('#search-form').hasClass('hidden')) {
                $('#search-form .search-field').focus();
            }
        });

        // Back to Top Button
        var $backToTop = $('#back-to-top');
        
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $backToTop.removeClass('opacity-0 invisible').addClass('opacity-100 visible');
            } else {
                $backToTop.removeClass('opacity-100 visible').addClass('opacity-0 invisible');
            }
        });

        $backToTop.on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 600);
        });

        // Smooth Scrolling for Anchor Links
        $('a[href*="#"]:not([href="#"])').on('click', function() {
            if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 600);
                    return false;
                }
            }
        });

        // Product Card Hover Effects (for WooCommerce)
        $('.product-card, .post-card').hover(
            function() {
                $(this).addClass('transform scale-105');
            },
            function() {
                $(this).removeClass('transform scale-105');
            }
        );

        // Newsletter Form Submission
        $('.newsletter-form').on('submit', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var $input = $form.find('input[type="email"]');
            var email = $input.val();

            if (!email) {
                alert('Please enter your email address.');
                return;
            }

            // Disable button and show loading state
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin icon-sm mr-2"></i>Subscribing...');

            // Simulate API call (replace with actual newsletter service)
            setTimeout(function() {
                $button.html('<i class="fas fa-check icon-sm mr-2"></i>Subscribed!');
                $input.val('');
                
                setTimeout(function() {
                    $button.prop('disabled', false).html('<i class="fas fa-paper-plane icon-sm mr-2"></i>Subscribe');
                }, 2000);
            }, 1500);
        });

        // Add to Cart Animation (WooCommerce)
        $(document).on('added_to_cart', function(event, fragments, cart_hash, $button) {
            // Update cart count
            var cartCount = $(fragments['div.widget_shopping_cart_content']).find('.cart-contents-count').text();
            $('.cart-count').text(cartCount).removeClass('hidden');
            
            // Add success animation
            $button.addClass('added-to-cart');
            setTimeout(function() {
                $button.removeClass('added-to-cart');
            }, 2000);
        });

        // Quantity Input Controls
        $(document).on('click', '.quantity-plus', function() {
            var $input = $(this).siblings('.qty');
            var currentVal = parseInt($input.val()) || 0;
            $input.val(currentVal + 1).trigger('change');
        });

        $(document).on('click', '.quantity-minus', function() {
            var $input = $(this).siblings('.qty');
            var currentVal = parseInt($input.val()) || 0;
            if (currentVal > 1) {
                $input.val(currentVal - 1).trigger('change');
            }
        });

        // Image Lazy Loading (if not using native lazy loading)
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Accessibility: Skip to Content
        $('.skip-link').on('click', function(e) {
            e.preventDefault();
            $('#content').focus();
        });

        // Close mobile menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.mobile-menu-toggle, #mobile-navigation').length) {
                $('#mobile-navigation').addClass('hidden');
                $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
            }
        });

        // Close search form when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-toggle, #search-form').length) {
                $('#search-form').addClass('hidden');
            }
        });
    });

    // Window Load
    $(window).on('load', function() {
        // Remove loading class from body if it exists
        $('body').removeClass('loading');
        
        // Initialize any plugins that need to wait for full page load
    initializePlugins();
    initializeHeroSliders();
    });

    // Initialize Plugins
    function initializePlugins() {
        // Initialize any third-party plugins here
        console.log('E-Shop Theme loaded successfully!');
    }

    // Initialize Swiper sliders and GSAP animations for hero
    function initializeHeroSliders() {
        if (typeof Swiper === 'undefined') return;

        // Desktop slider
        var desktopEl = document.querySelector('.hero-slider-desktop');
        if (desktopEl) {
            var desktopSwiper = new Swiper(desktopEl, {
                loop: true,
                speed: 700,
                effect: 'fade',
                fadeEffect: { crossFade: true },
                autoplay: {
                    delay: 4500,
                    disableOnInteraction: false
                },
                pagination: {
                    el: desktopEl.querySelector('.swiper-pagination'),
                    clickable: true
                },
                navigation: {
                    nextEl: desktopEl.querySelector('.swiper-button-next'),
                    prevEl: desktopEl.querySelector('.swiper-button-prev')
                }
            });

            if (window.gsap) {
                desktopSwiper.on('slideChangeTransitionStart', function() {
                    var active = desktopEl.querySelector('.swiper-slide-active .slide-caption');
                    if (!active) return;
                    gsap.fromTo(active, { opacity: 0, y: 16 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out', delay: 0.1 });
                });
            }
        }

        // Mobile slider
        var mobileEl = document.querySelector('.hero-slider-mobile');
        if (mobileEl) {
            var mobileSwiper = new Swiper(mobileEl, {
                loop: true,
                speed: 600,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false
                },
                pagination: {
                    el: mobileEl.querySelector('.swiper-pagination'),
                    clickable: true
                }
            });

            if (window.gsap) {
                mobileSwiper.on('slideChangeTransitionStart', function() {
                    var active = mobileEl.querySelector('.swiper-slide-active .slide-caption');
                    if (!active) return;
                    gsap.fromTo(active, { opacity: 0, y: 14 }, { opacity: 1, y: 0, duration: 0.5, ease: 'power2.out', delay: 0.1 });
                });
            }
        }
    }

    // Utility Functions
    window.EShopTheme = {
        // Add utility functions here
        showNotification: function(message, type = 'success') {
            var notification = $('<div class="notification notification-' + type + ' fixed top-4 right-4 bg-white border-l-4 border-' + (type === 'success' ? 'green' : 'red') + '-500 p-4 shadow-lg rounded-md z-50">' +
                '<div class="flex items-center">' +
                '<i class="fas fa-' + (type === 'success' ? 'check-circle text-green-500' : 'exclamation-circle text-red-500') + ' mr-3"></i>' +
                '<span>' + message + '</span>' +
                '<button class="ml-4 text-gray-400 hover:text-gray-600" onclick="$(this).parent().parent().remove()">' +
                '<i class="fas fa-times"></i>' +
                '</button>' +
                '</div>' +
                '</div>');
            
            $('body').append(notification);
            
            setTimeout(function() {
                notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

})(jQuery);