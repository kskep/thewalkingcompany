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

        // Mobile Dropdown Toggle
        $('.mobile-menu .menu-item-has-children > a').on('click', function(e) {
            e.preventDefault();
            var $parent = $(this).parent();
            $parent.toggleClass('open');
            $parent.siblings('.menu-item-has-children').removeClass('open');
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

        // Header Dropdowns
        $('.wishlist-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.wishlist-dropdown').toggleClass('hidden');
            $('.account-dropdown, .minicart-dropdown').addClass('hidden');
        });

        $('.account-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.account-dropdown').toggleClass('hidden');
            $('.wishlist-dropdown, .minicart-dropdown').addClass('hidden');
        });

        $('.minicart-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $('.minicart-dropdown').toggleClass('hidden');
            $('.wishlist-dropdown, .account-dropdown').addClass('hidden');
        });

        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.wishlist-wrapper, .account-wrapper, .minicart-wrapper').length) {
                $('.wishlist-dropdown, .account-dropdown, .minicart-dropdown').addClass('hidden');
            }
        });

        // Wishlist functionality
        $(document).on('click', '.add-to-wishlist', function(e) {
            e.preventDefault();
            var $button = $(this);
            var productId = $button.data('product-id');

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: productId,
                    nonce: eshop_ajax.nonce
                },
                beforeSend: function() {
                    $button.addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.action === 'added') {
                            $button.addClass('in-wishlist').find('i').removeClass('far').addClass('fas');
                            EShopTheme.showNotification('Product added to wishlist!', 'success');
                        } else {
                            $button.removeClass('in-wishlist').find('i').removeClass('fas').addClass('far');
                            EShopTheme.showNotification('Product removed from wishlist!', 'success');
                        }

                        // Update wishlist count
                        var $count = $('.wishlist-count');
                        if (response.data.count > 0) {
                            $count.text(response.data.count).removeClass('hidden');
                        } else {
                            $count.addClass('hidden');
                        }
                    }
                },
                complete: function() {
                    $button.removeClass('loading');
                }
            });
        });

        // Remove from wishlist in dropdown
        $(document).on('click', '.remove-from-wishlist', function(e) {
            e.preventDefault();
            var $button = $(this);
            var productId = $button.data('product-id');

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: productId,
                    nonce: eshop_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $button.closest('.wishlist-item').fadeOut(300, function() {
                            $(this).remove();

                            // Update wishlist count
                            var $count = $('.wishlist-count');
                            if (response.data.count > 0) {
                                $count.text(response.data.count);
                            } else {
                                $count.addClass('hidden');
                                $('.wishlist-items').html('<p class="text-gray-500 text-center py-4">Your wishlist is empty</p>');
                            }
                        });

                        EShopTheme.showNotification('Product removed from wishlist!', 'success');
                    }
                }
            });
        });

        // Remove from cart in minicart dropdown
        $(document).on('click', '.remove-from-cart', function(e) {
            e.preventDefault();
            var $button = $(this);
            var removeUrl = $button.attr('href');

            $.get(removeUrl, function() {
                // Trigger cart update
                $(document.body).trigger('wc_fragment_refresh');
                EShopTheme.showNotification('Product removed from cart!', 'success');
            });
        });

        // Product Archive Filters
        if ($('.shop-layout').length) {
            // Load filter component
            if (typeof EShopFilters !== 'undefined') {
                EShopFilters.init();
            } else {
                // Fallback to old method if component not loaded
                initProductFilters();
            }
        }

        // Quick add to cart
        $(document).on('click', '.add-to-cart-simple', function(e) {
            e.preventDefault();
            var $button = $(this);
            var productId = $button.data('product-id');

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'quick_add_to_cart',
                    product_id: productId,
                    quantity: 1,
                    nonce: eshop_ajax.nonce
                },
                beforeSend: function() {
                    $button.addClass('loading').text('Adding...');
                },
                success: function(response) {
                    if (response.success) {
                        EShopTheme.showNotification(response.data.message, 'success');
                        // Update cart fragments
                        $(document.body).trigger('wc_fragment_refresh');
                    } else {
                        EShopTheme.showNotification(response.data.message, 'error');
                    }
                },
                complete: function() {
                    $button.removeClass('loading').text('Add to Cart');
                }
            });
        });
    });

    // Window Load
    $(window).on('load', function() {
        // Remove loading class from body if it exists
        $('body').removeClass('loading');

        // Initialize any plugins that need to wait for full page load
    initializePlugins();
    initializeHeroSliders();
    initializeProductCardSliders();
    });

    // Initialize Plugins
    function initializePlugins() {
        // Initialize any third-party plugins here
        console.log('E-Shop Theme loaded successfully!');
    }

    // Initialize Swiper sliders and GSAP animations for hero
    function initializeHeroSliders() {
        if (typeof Swiper === 'undefined') return;

        document.querySelectorAll('.js-hero-slider').forEach(function(wrapper) {
            // Desktop instance per wrapper
            var desktopEl = wrapper.querySelector('.hero-slider-desktop');
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

            // Mobile instance per wrapper
            var mobileEl = wrapper.querySelector('.hero-slider-mobile');
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
        });
    }

    // Initialize product sliders used on archive cards
    function initializeProductCardSliders() {
        if (typeof Swiper === 'undefined') return;

        document.querySelectorAll('.product-slider').forEach(function(el) {
            // Avoid double init
            if (el.__eshop_inited) return;
            el.__eshop_inited = true;
            try {
                var swiperConfig = {
                    pagination: {
                        el: el.querySelector('.swiper-pagination'),
                        clickable: true
                    },
                    navigation: {
                        nextEl: el.querySelector('.swiper-button-next'),
                        prevEl: el.querySelector('.swiper-button-prev'),
                    },
                    loop: true,
                    // Disable autoplay on archive product sliders
                    autoplay: false,
                    effect: 'fade',
                    fadeEffect: {
                        crossFade: true
                    },
                    speed: 300,
                    on: {
                        init: function() {
                            // Hide navigation if only one slide
                            var slideCount = this.slides.length;
                            if (slideCount <= 1) {
                                var nav = el.querySelectorAll('.swiper-button-prev, .swiper-button-next, .swiper-pagination');
                                nav.forEach(function(navEl) {
                                    navEl.style.display = 'none';
                                });
                            }
                        }
                    }
                };

                new Swiper(el, swiperConfig);
            } catch (e) {
                console.warn('Swiper init failed for product slider', e);
            }
        });
    }

    // Product Filter Functions (fallback if component not loaded)
    function initProductFilters() {
        console.log('Using fallback filter initialization');
        // Basic filter functionality as fallback
    }

    // Filter functions moved to components/filters.js



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