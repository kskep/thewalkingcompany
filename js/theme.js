/**
 * E-Shop Theme JavaScript
 */

(function ($) {
    'use strict';

    // DOM Ready
    $(document).ready(function () {
        console.log('Theme.js loaded and ready');

        // Mobile Menu Toggle
        $('.mobile-menu-toggle').on('click', function () {
            $('#mobile-navigation').toggleClass('hidden');
            $(this).find('i').toggleClass('fa-bars fa-times');
        });

        // Add expand/collapse buttons to menu items with children
        $('.mobile-menu .menu-item-has-children').each(function () {
            var $menuItem = $(this);
            var $link = $menuItem.find('> a');

            // Add expand toggle button
            $link.after('<button class="expand-toggle" aria-label="Toggle submenu"><i class="fas fa-chevron-down"></i></button>');
        });

        // Mobile Dropdown Toggle - Separate expand button from link
        $('.mobile-menu .menu-item-has-children .expand-toggle').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $parent = $(this).closest('.menu-item-has-children');
            var $submenu = $parent.find('.sub-menu');
            var isOpen = $parent.hasClass('open');

            // Close other submenus
            $parent.siblings('.menu-item-has-children').removeClass('open');
            $parent.siblings('.menu-item-has-children').find('.sub-menu').slideUp(200);

            if (!isOpen) {
                // Open this submenu
                $parent.addClass('open');
                $submenu.slideDown(200);
            } else {
                // Close this submenu
                $parent.removeClass('open');
                $submenu.slideUp(200);
            }
        });



        // Search Toggle
        $('.search-toggle').on('click', function () {
            $('#search-form').toggleClass('hidden');
            if (!$('#search-form').hasClass('hidden')) {
                $('#search-form .search-field').focus();
            }
        });

        // Back to Top Button
        var $backToTop = $('#back-to-top');

        $(window).scroll(function () {
            if ($(this).scrollTop() > 300) {
                $backToTop.removeClass('opacity-0 invisible').addClass('opacity-100 visible');
            } else {
                $backToTop.removeClass('opacity-100 visible').addClass('opacity-0 invisible');
            }
        });

        $backToTop.on('click', function (e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 600);
        });

        // Smooth Scrolling for Anchor Links
        $('a[href*="#"]:not([href="#"])').on('click', function () {
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

        // Product Card Hover Effects (for WooCommerce) - REMOVED scaling effect
        // $('.product-card, .post-card').hover(
        //     function() {
        //         $(this).addClass('transform scale-105');
        //     },
        //     function() {
        //         $(this).removeClass('transform scale-105');
        //     }
        // );

        // Newsletter Form Submission
        $('.newsletter-form').on('submit', function (e) {
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
            setTimeout(function () {
                $button.html('<i class="fas fa-check icon-sm mr-2"></i>Subscribed!');
                $input.val('');

                setTimeout(function () {
                    $button.prop('disabled', false).html('<i class="fas fa-paper-plane icon-sm mr-2"></i>Subscribe');
                }, 2000);
            }, 1500);
        });

        // Add to Cart Animation (WooCommerce)
        $(document).on('added_to_cart', function (event, fragments, cart_hash, $button) {
            // Update cart count
            var cartCount = $(fragments['div.widget_shopping_cart_content']).find('.cart-contents-count').text();
            $('.cart-count').text(cartCount).removeClass('hidden');

            // Add success animation
            $button.addClass('added-to-cart');
            setTimeout(function () {
                $button.removeClass('added-to-cart');
            }, 2000);
        });

        // Quantity Input Controls
        $(document).on('click', '.quantity-plus', function () {
            var $input = $(this).siblings('.qty');
            var currentVal = parseInt($input.val()) || 0;
            $input.val(currentVal + 1).trigger('change');
        });

        $(document).on('click', '.quantity-minus', function () {
            var $input = $(this).siblings('.qty');
            var currentVal = parseInt($input.val()) || 0;
            if (currentVal > 1) {
                $input.val(currentVal - 1).trigger('change');
            }
        });

        // Gift wrap controls (checkout)
        var giftWrapUpdateTimer;
        function scheduleGiftWrapUpdate() {
            clearTimeout(giftWrapUpdateTimer);
            giftWrapUpdateTimer = setTimeout(function () {
                $('body').trigger('update_checkout');
            }, 250);
        }

        $(document).on('click', '.gift-wrap-toggle', function () {
            var $section = $(this).closest('.gift-wrap-section');
            $section.toggleClass('is-collapsed');
        });

        $(document).on('click', '.gift-wrap-plus', function () {
            var $input = $(this).siblings('.gift-wrap-input');
            var currentVal = parseInt($input.val()) || 0;
            var maxVal = parseInt($input.attr('max')) || 99;
            if (currentVal < maxVal) {
                $input.val(currentVal + 1).trigger('change');
            }
        });

        $(document).on('click', '.gift-wrap-minus', function () {
            var $input = $(this).siblings('.gift-wrap-input');
            var currentVal = parseInt($input.val()) || 0;
            var minVal = parseInt($input.attr('min')) || 0;
            if (currentVal > minVal) {
                $input.val(currentVal - 1).trigger('change');
            }
        });

        $(document).on('change', '.gift-wrap-input', function () {
            var $input = $(this);
            var val = parseInt($input.val());
            var minVal = parseInt($input.attr('min')) || 0;
            var maxVal = parseInt($input.attr('max')) || 99;
            if (isNaN(val)) {
                val = 0;
            }
            val = Math.max(minVal, Math.min(maxVal, val));
            $input.val(val);
            scheduleGiftWrapUpdate();
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
        $('.skip-link').on('click', function (e) {
            e.preventDefault();
            $('#content').focus();
        });

        // Close mobile menu when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.mobile-menu-toggle, #mobile-navigation').length) {
                $('#mobile-navigation').addClass('hidden');
                $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
            }
        });

        // Close search form when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.search-toggle, #search-form').length) {
                $('#search-form').addClass('hidden');
            }
        });

        // Header Dropdowns
        $('.wishlist-toggle').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.wishlist-dropdown').toggleClass('hidden');
            $('.account-dropdown, .minicart-dropdown').addClass('hidden');
        });

        $('.account-toggle').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.account-dropdown').toggleClass('hidden');
            $('.wishlist-dropdown, .minicart-dropdown').addClass('hidden');
        });

        // Handle modal trigger clicks inside account dropdown
        $(document).on('click', '.account-dropdown .modal-trigger', function (e) {
            e.preventDefault();
            e.stopPropagation();
            // Close the dropdown first
            $('.account-dropdown').addClass('hidden');
            // Let the auth modal component handle the modal opening
        });

        $('.minicart-toggle').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('.minicart-dropdown').toggleClass('hidden');
            $('.wishlist-dropdown, .account-dropdown').addClass('hidden');
        });

        $(document).on('click', '.minicart-close', function (e) {
            e.preventDefault();
            $('.minicart-dropdown').addClass('hidden');
        });

        // Close dropdowns when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.wishlist-wrapper, .account-wrapper, .minicart-wrapper').length) {
                $('.wishlist-dropdown, .account-dropdown, .minicart-dropdown').addClass('hidden');
            }
        });

        function eshopNotify(message, type) {
            if (!message) {
                return;
            }
            if (window.EShopTheme && typeof EShopTheme.showNotification === 'function') {
                EShopTheme.showNotification(message, type || 'success');
            }
        }

        function eshopUpdateCartFragments(fragments) {
            if (!fragments) {
                return;
            }

            $.each(fragments, function (selector, html) {
                $(selector).replaceWith(html);
            });
        }

        function eshopSyncWishlistButtons(productId, data) {
            if (!productId) {
                return;
            }

            $('.add-to-wishlist[data-product-id="' + productId + '"]').each(function () {
                var $btn = $(this);
                if (data.is_in_wishlist) {
                    $btn.addClass('in-wishlist');
                } else {
                    $btn.removeClass('in-wishlist');
                }

                if (data.aria_label) {
                    $btn.attr('aria-label', data.aria_label).attr('title', data.aria_label);
                }

                if (typeof data.button_text !== 'undefined') {
                    var $text = $btn.find('.wishlist-text');
                    if ($text.length) {
                        $text.text(data.button_text);
                    }
                }

                var $faIcon = $btn.find('i.fa-heart');
                if ($faIcon.length) {
                    $faIcon.toggleClass('fas', !!data.is_in_wishlist);
                    $faIcon.toggleClass('far', !data.is_in_wishlist);
                }

                var $materialIcon = $btn.find('.material-icons');
                if ($materialIcon.length && data.icon) {
                    $materialIcon.text(data.icon);
                }

                var $svg = $btn.find('svg');
                if ($svg.length) {
                    $svg.attr('fill', data.is_in_wishlist ? 'currentColor' : 'none');
                }
            });
        }

        function eshopRefreshWishlistUI(data) {
            if (!data) {
                return;
            }

            if (typeof data.product_id !== 'undefined') {
                eshopSyncWishlistButtons(data.product_id, data);
            }

            if (typeof data.count !== 'undefined') {
                var $count = $('.wishlist-count');
                if (data.count > 0) {
                    $count.text(data.count_label || data.count).removeClass('hidden');
                } else {
                    $count.addClass('hidden');
                }
            }

            if (typeof data.dropdown_html !== 'undefined') {
                $('.wishlist-items').html(data.dropdown_html);
            }

            var $viewAll = $('.wishlist-view-all');
            if ($viewAll.length) {
                if (data.has_items) {
                    $viewAll.removeClass('hidden');
                } else {
                    $viewAll.addClass('hidden');
                }
            }
        }

        function eshopHandleWishlistResponse(response) {
            if (!response) {
                return;
            }

            // If server indicates auth required, prompt login/register or redirect
            if (!response.success && response.data && response.data.requires_auth) {
                var redirectUrl = response.data.redirect || (window.location.origin + '/my-account/');
                // Prefer opening modal if available
                if (window.authModal && typeof window.authModal.openRegister === 'function') {
                    // Close any open dropdowns to prevent overlay conflicts
                    $('.wishlist-dropdown, .account-dropdown').addClass('hidden');
                    window.authModal.openRegister();
                } else {
                    // Append redirect_to so user returns to current page
                    var sep = redirectUrl.indexOf('?') === -1 ? '?' : '&';
                    window.location.href = redirectUrl + sep + 'redirect_to=' + encodeURIComponent(window.location.href);
                }
                return;
            }

            if (response.success && response.data) {
                eshopRefreshWishlistUI(response.data);
                eshopNotify(response.data.message, response.data.notification_type || 'success');
            } else if (response.data && response.data.message) {
                eshopNotify(response.data.message, 'error');
            }
        }

        // Wishlist functionality
        $(document).on('click', '.add-to-wishlist', function (e) {
            e.preventDefault();
            // Prevent bubbling to product-card click handlers that navigate to PDP
            e.stopPropagation();
            var $button = $(this);
            if ($button.hasClass('loading')) {
                return;
            }

            // If the button indicates auth is required, open modal or redirect immediately
            if ($button.data('requires-auth')) {
                // Close any dropdowns
                $('.wishlist-dropdown, .account-dropdown').addClass('hidden');
                if (window.authModal && typeof window.authModal.openRegister === 'function') {
                    window.authModal.openRegister();
                } else {
                    var myAccountUrl = (typeof eshop_ajax !== 'undefined' && eshop_ajax.myaccount_url) ? eshop_ajax.myaccount_url : (window.location.origin + '/my-account/');
                    var sep = myAccountUrl.indexOf('?') === -1 ? '?' : '&';
                    window.location.href = myAccountUrl + sep + 'redirect_to=' + encodeURIComponent(window.location.href);
                }
                return;
            }

            var productId = $button.data('product-id');

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: productId,
                    nonce: eshop_ajax.nonce
                },
                beforeSend: function () {
                    $button.addClass('loading');
                    $button.attr('aria-busy', 'true');
                    $button.css('pointer-events', 'none');
                },
                success: function (response) {
                    eshopHandleWishlistResponse(response);
                },
                error: function () {
                    eshopNotify('Something went wrong. Please try again.', 'error');
                },
                complete: function () {
                    $button.removeClass('loading');
                    $button.attr('aria-busy', 'false');
                    $button.css('pointer-events', '');
                }
            });
        });

        // Remove from wishlist in dropdown
        $(document).on('click', '.remove-from-wishlist', function (e) {
            e.preventDefault();
            var $button = $(this);
            if ($button.hasClass('loading')) {
                return;
            }

            var productId = $button.data('product-id');

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'remove_from_wishlist',
                    product_id: productId,
                    nonce: eshop_ajax.nonce
                },
                beforeSend: function () {
                    $button.addClass('loading');
                    $button.attr('aria-busy', 'true');
                    $button.css('pointer-events', 'none');
                },
                success: function (response) {
                    eshopHandleWishlistResponse(response);
                },
                error: function () {
                    eshopNotify('Something went wrong. Please try again.', 'error');
                },
                complete: function () {
                    $button.removeClass('loading');
                    $button.attr('aria-busy', 'false');
                    $button.css('pointer-events', '');
                }
            });
        });

        // Remove from cart in minicart dropdown
        $(document).on('click', '.minicart-dropdown .remove-from-cart', function (e) {
            e.preventDefault();
            var $button = $(this);
            if ($button.hasClass('loading')) {
                return;
            }

            var cartItemKey = $button.data('cart-item-key');

            if (!cartItemKey) {
                // Fallback to default behavior if key missing
                window.location.href = $button.attr('href');
                return;
            }

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'remove_cart_item',
                    cart_item_key: cartItemKey,
                    nonce: eshop_ajax.nonce
                },
                beforeSend: function () {
                    $button.addClass('loading');
                    $button.attr('aria-disabled', 'true');
                },
                success: function (response) {
                    if (response.success && response.data) {
                        eshopUpdateCartFragments(response.data.fragments);
                        $(document.body).trigger('removed_from_cart', [response.data.fragments || {}, response.data.cart_hash || null, $button]);
                        $(document.body).trigger('wc_fragment_refresh');
                        eshopNotify(response.data.message || 'Product removed from cart!', 'success');
                    } else {
                        var errorMessage = response.data && response.data.message ? response.data.message : 'Unable to remove item. Please try again.';
                        eshopNotify(errorMessage, 'error');
                    }
                },
                error: function () {
                    eshopNotify('Unable to remove item. Please try again.', 'error');
                },
                complete: function () {
                    $button.removeClass('loading');
                    $button.removeAttr('aria-disabled');
                }
            });
        });

        // Minicart quantity plus button
        $(document).on('click', '.minicart-dropdown .qty-plus', function (e) {
            e.preventDefault();
            var $btn = $(this);
            if ($btn.hasClass('loading')) return;

            var cartItemKey = $btn.data('cart-item-key');
            var $qtyValue = $btn.siblings('.qty-value');
            var currentQty = parseInt($qtyValue.text()) || 1;

            updateMinicartQuantity(cartItemKey, currentQty + 1, $btn);
        });

        // Minicart quantity minus button
        $(document).on('click', '.minicart-dropdown .qty-minus', function (e) {
            e.preventDefault();
            var $btn = $(this);
            if ($btn.hasClass('loading')) return;

            var cartItemKey = $btn.data('cart-item-key');
            var $qtyValue = $btn.siblings('.qty-value');
            var currentQty = parseInt($qtyValue.text()) || 1;

            if (currentQty > 1) {
                updateMinicartQuantity(cartItemKey, currentQty - 1, $btn);
            } else {
                // If quantity would be 0, trigger removal
                var $removeBtn = $btn.closest('.minicart-item').find('.remove-from-cart');
                $removeBtn.trigger('click');
            }
        });

        // Helper function to update minicart quantity
        function updateMinicartQuantity(cartItemKey, quantity, $btn) {
            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'update_cart_quantity',
                    cart_item_key: cartItemKey,
                    quantity: quantity,
                    nonce: eshop_ajax.nonce
                },
                beforeSend: function () {
                    $btn.addClass('loading');
                    $btn.closest('.minicart-qty-controls').css('opacity', '0.6');
                },
                success: function (response) {
                    if (response.success && response.data) {
                        eshopUpdateCartFragments(response.data.fragments);
                        $(document.body).trigger('wc_fragment_refresh');

                        // Update the quantity display
                        $btn.siblings('.qty-value').text(quantity);

                        // Update cart count badge
                        var $cartCount = $('.cart-count');
                        if (response.data.cart_count > 0) {
                            $cartCount.text(response.data.cart_count).removeClass('hidden');
                        } else {
                            $cartCount.addClass('hidden');
                        }
                    } else {
                        eshopNotify('Error updating quantity', 'error');
                    }
                },
                error: function () {
                    eshopNotify('Error updating quantity', 'error');
                },
                complete: function () {
                    $btn.removeClass('loading');
                    $btn.closest('.minicart-qty-controls').css('opacity', '1');
                }
            });
        }

        // Product Archive Filters - Let the filter component handle initialization
        // This is now handled by the filters.js component itself
        console.log('Theme.js: Filter initialization delegated to filters.js component');

        // Quick add to cart
        $(document).on('click', '.add-to-cart-simple', function (e) {
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
                beforeSend: function () {
                    $button.addClass('loading').text('Adding...');
                },
                success: function (response) {
                    if (response.success) {
                        EShopTheme.showNotification(response.data.message, 'success');
                        // Update cart fragments
                        $(document.body).trigger('wc_fragment_refresh');
                    } else {
                        EShopTheme.showNotification(response.data.message, 'error');
                    }
                },
                complete: function () {
                    $button.removeClass('loading').text('Add to Cart');
                }
            });
        });
    });

    // Window Load
    $(window).on('load', function () {
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

        document.querySelectorAll('.js-hero-slider').forEach(function (wrapper) {
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
                    desktopSwiper.on('slideChangeTransitionStart', function () {
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
                    mobileSwiper.on('slideChangeTransitionStart', function () {
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

        document.querySelectorAll('.product-slider').forEach(function (el) {
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
                    loop: false,
                    slidesPerView: 1,
                    observer: true,
                    observeParents: true,
                    watchOverflow: true,
                    // Disable autoplay on archive product sliders
                    autoplay: false,
                    effect: 'fade',
                    fadeEffect: {
                        crossFade: true
                    },
                    speed: 300
                };

                var normaliseSlides = function (swiperInstance) {
                    if (!swiperInstance || !swiperInstance.slides) { return; }
                    swiperInstance.slides.forEach(function (slide) {
                        slide.style.width = '100%';
                    });
                };

                swiperConfig.on = {
                    beforeInit: function () {
                        normaliseSlides(this);
                    },
                    init: function () {
                        var slideCount = this.slides.length;
                        if (slideCount <= 1) {
                            var nav = el.querySelectorAll('.swiper-button-prev, .swiper-button-next, .swiper-pagination');
                            nav.forEach(function (navEl) {
                                navEl.style.display = 'none';
                            });
                        }
                        normaliseSlides(this);
                        this.updateSlides();
                    },
                    resize: function () {
                        normaliseSlides(this);
                        this.update();
                    },
                    observerUpdate: function () {
                        normaliseSlides(this);
                        this.update();
                    }
                };

                var swiper = new Swiper(el, swiperConfig);
                el.__swiper = swiper;

                // Final safety pass after layout settles
                setTimeout(function () {
                    normaliseSlides(swiper);
                    swiper.update();
                }, 150);

                // Hover-to-second-image behavior
                var originalIndex = 0;
                el.addEventListener('mouseenter', function () {
                    if (!swiper || swiper.slides.length <= 1) { return; }
                    try { swiper.slideTo(1, 200); } catch (e) { }
                });
                el.addEventListener('mouseleave', function () {
                    if (!swiper) { return; }
                    try { swiper.slideTo(0, 200); } catch (e) { }
                });
            } catch (e) {
                console.warn('Swiper init failed for product slider', e);
            }
        });
    }

    // Simple hover-to-second-image fallback for product cards without Swiper
    $(document).on('mouseenter', '.twc-card', function () {
        var $card = $(this);
        if ($card.data('hover-swap-initialized')) return;
        $card.data('hover-swap-initialized', true);

        var $img = $card.find('.twc-card__image img').first();
        if ($img.length === 0) return;

        var $gallery = $card.data('gallery');
        // If we have a swiper in the card, skip fallback
        if ($card.find('.product-slider.swiper').length) return;

        // Try to find a secondary image in Woo markup (common pattern)
        var $secondary = $card.find('img.secondary-image').first();
        if ($secondary.length) {
            var primarySrc = $img.attr('src');
            var secondarySrc = $secondary.attr('src');
            $card.on('mouseenter', function () { $img.attr('src', secondarySrc); });
            $card.on('mouseleave', function () { $img.attr('src', primarySrc); });
        }
    });

    // Product Filter Functions (fallback if component not loaded)
    function initProductFilters() {
        console.log('Using fallback filter initialization');
        console.log('Filter button exists:', $('#open-filters').length);
        console.log('Filter drawer exists:', $('#filter-drawer').length);
        console.log('Filter backdrop exists:', $('#filter-backdrop').length);

        // Remove any existing event handlers to prevent duplicates
        $('#open-filters').off('click.fallback');
        $('#close-filters, #filter-backdrop').off('click.fallback');

        // Basic filter drawer functionality
        $('#open-filters').on('click.fallback', function (e) {
            e.preventDefault();
            console.log('Filter button clicked (fallback)');
            $('#filter-backdrop').removeClass('hidden').addClass('show');
            $('#filter-drawer').addClass('open');
            $('body').addClass('overflow-hidden');
        });

        $('#close-filters, #filter-backdrop').on('click.fallback', function (e) {
            e.preventDefault();
            console.log('Closing filter drawer (fallback)');
            $('#filter-backdrop').removeClass('show').addClass('hidden');
            $('#filter-drawer').removeClass('open');
            $('body').removeClass('overflow-hidden');
        });

        // Keyboard accessibility - Escape to close drawer
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $('#filter-drawer').hasClass('open')) {
                $('#filter-backdrop').removeClass('show').addClass('hidden');
                $('#filter-drawer').removeClass('open');
                $('body').removeClass('overflow-hidden');
            }
        });
    }

    // Filter functions moved to components/filters.js



    // Utility Functions
    window.EShopTheme = {
        // Add utility functions here
        showNotification: function (message, type = 'success') {
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

            setTimeout(function () {
                notification.fadeOut(function () {
                    $(this).remove();
                });
            }, 5000);
        }
    };

})(jQuery);
