/*
 * Custom JavaScript for MDB Theme
 * 
 * This file contains custom JavaScript functionality for the theme.
 * Follow WordPress best practices and use jQuery if needed.
 */

(function($) {
    'use strict';
    
    // Document ready function
    $(document).ready(function() {
        initMiniCartToggle();
        initProductLayout();
        initCartButtons();
        initMobileMenu();
        initAccountAjaxNav();
        initToasts();  // pick up any notices already on the page
        initAjaxAuth();
    });

    // Window load — allow fonts and images to settle before measuring header height.
    $(window).on('load', function() {
        initStickyHeader();
    });
    
    // Custom functions
    
    // Custom Sticky Header — reads logo, menu and cart data from the custom
    // PHP header (#mdb-custom-header) rendered server-side by custom-header.php.
    function initStickyHeader() {
        var $mainHeader = $('#mdb-custom-header');
        if (!$mainHeader.length) return;

        // ------------------------------------------------------------------
        // 1. Read data from the custom header elements
        // ------------------------------------------------------------------

        // Logo
        var $logoLink = $mainHeader.find('.mdb-header-logo').first();
        var logoSrc   = $logoLink.find('img').attr('src') || '';
        var logoHref  = $logoLink.attr('href') || '/';
        var logoAlt   = $logoLink.find('img').attr('alt') || '';

        // Favicon — resolved in PHP via get_site_icon_url() and passed as a
        // data attribute, so we don't have to scrape <link> tags.
        var faviconSrc = $mainHeader.data('favicon-src') || '';

        // Cart
        var $cartBtn  = $mainHeader.find('.mdb-topbar-cart').first();
        var cartUrl   = $cartBtn.data('cart-url') || '/cart/';
        var cartCount = parseInt($mainHeader.find('.mdb-topbar-cart-count').text().trim()) || 0;

        // Free Samples link (rendered separately from the nav menu in the main bar)
        var $fsSample       = $mainHeader.find('.mdb-header-free-samples').first();
        var freeSamplesHref = $fsSample.attr('href') || '/free-samples/';
        var freeSamplesText = $.trim($fsSample.text()) || '» Free Samples «';

        // Menu structure — parsed from the PHP walker-rendered nav list
        var menuItems = [];
        $mainHeader.find('.mdb-header-nav-list > li').each(function() {
            var $li         = $(this);
            var hasDropdown = $li.hasClass('mdb-mega-parent');
            var label, topHref = null;

            if (hasDropdown) {
                label = $.trim($li.find('> button.mdb-nav-toggle > span').text());
            } else {
                var $a  = $li.find('> a.mdb-nav-link').first();
                label   = $.trim($a.find('span').text());
                topHref = $a.attr('href') || null;
            }

            var cells = [];
            if (hasDropdown) {
                $li.find('.mdb-mega-grid > li').each(function() {
                    var $cell     = $(this);
                    var $cellLink = $cell.find('> a').first();
                    var cellHref  = $cellLink.attr('href') || '#';
                    var cellLabel = $.trim($cellLink.find('span').text());
                    var imgSrc    = $cellLink.find('img.mdb-nav-icon').attr('src') || '';
                    var imgAlt    = $cellLink.find('img.mdb-nav-icon').attr('alt') || cellLabel;
                    if (cellLabel) cells.push({ label: cellLabel, href: cellHref, imgSrc: imgSrc, imgAlt: imgAlt });
                });
            }

            if (label) menuItems.push({ label: label, href: topHref, hasDropdown: hasDropdown, cells: cells });
        });

        // Append Free Samples as the final nav item (matches main header layout)
        menuItems.push({ label: freeSamplesText, href: freeSamplesHref, hasDropdown: false, cells: [], isFree: true });

        // ------------------------------------------------------------------
        // 2. Build sticky header HTML
        // ------------------------------------------------------------------

        var cartSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" aria-hidden="true"><path d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"/></svg>';
        var chevronSvg = '<svg class="mdb-snav-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" aria-hidden="true"><path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"/></svg>';

        var navHtml = '';
        $.each(menuItems, function(i, item) {
            var cls = 'mdb-snav-item' +
                (item.hasDropdown ? ' has-dropdown' : '') +
                (item.isFree      ? ' is-free-samples' : '');
            navHtml += '<li class="' + cls + '">';

            if (!item.hasDropdown) {
                navHtml += '<a class="mdb-snav-link" href="' + (item.href || '#') + '">' +
                               item.label + '</a>';
            } else {
                navHtml += '<button class="mdb-snav-link mdb-snav-toggle" type="button" ' +
                               'aria-expanded="false" aria-haspopup="true">' +
                               item.label + chevronSvg +
                           '</button>';
                navHtml += '<div class="mdb-snav-dropdown" hidden><ul class="mdb-snav-grid">';
                $.each(item.cells, function(j, cell) {
                    navHtml += '<li><a href="' + cell.href + '">' +
                                   (cell.imgSrc ? '<img src="' + cell.imgSrc + '" alt="' + cell.imgAlt + '" width="30" height="30" loading="lazy">' : '') +
                                   '<span>' + cell.label + '</span>' +
                               '</a></li>';
                });
                navHtml += '</ul></div>';
            }

            navHtml += '</li>';
        });

        var countVisible = cartCount > 0 ? '' : ' style="display:none"';
        var $stickyHeader = $(
            '<div class="mdb-sticky-header" role="banner">' +
                '<div class="mdb-sticky-inner">' +
                    '<a class="mdb-sticky-logo" href="' + logoHref + '">' +
                        (logoSrc ? '<img src="' + logoSrc + '" alt="' + logoAlt + '">' : '') +
                    '</a>' +
                    (faviconSrc
                        ? '<a class="mdb-sticky-favicon" href="' + logoHref + '" aria-hidden="true" tabindex="-1">' +
                              '<img src="' + faviconSrc + '" alt="" width="48" height="48">' +
                          '</a>'
                        : '') +
                    '<nav class="mdb-sticky-nav" aria-label="Main navigation">' +
                        '<ul class="mdb-snav-list">' + navHtml + '</ul>' +
                    '</nav>' +
                    '<div class="mdb-sticky-actions">' +
                        '<button class="mdb-sticky-cart" type="button" aria-label="Cart (' + cartCount + ' items)">' +
                            cartSvg +
                            '<span class="mdb-sticky-cart-count"' + countVisible + '>' + cartCount + '</span>' +
                        '</button>' +
                        '<button class="mdb-sticky-hamburger" type="button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="mdb-mobile-drawer">' +
                            '<span class="mdb-sticky-hamburger__bar"></span>' +
                            '<span class="mdb-sticky-hamburger__bar"></span>' +
                            '<span class="mdb-sticky-hamburger__bar"></span>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>'
        );

        $('body').append($stickyHeader);

        // Sticky hamburger proxies to the main mobile toggle so initMobileMenu()
        // handles all drawer open/close/aria state in one place.
        $stickyHeader.on('click', '.mdb-sticky-hamburger', function() {
            $('.mdb-mobile-toggle').trigger('click');
        });

        // Keep sticky hamburger aria-expanded in sync with the drawer state.
        $(document).on('mdb:drawerOpen', function() {
            $stickyHeader.find('.mdb-sticky-hamburger').attr('aria-expanded', 'true').addClass('is-open');
        });
        $(document).on('mdb:drawerClose', function() {
            $stickyHeader.find('.mdb-sticky-hamburger').attr('aria-expanded', 'false').removeClass('is-open');
        });

        // ------------------------------------------------------------------
        // 3. Dropdown interaction
        // ------------------------------------------------------------------
        var $openItem = null;

        function closeAll() {
            if (!$openItem) return;
            $openItem.find('.mdb-snav-toggle').attr('aria-expanded', 'false');
            $openItem.find('.mdb-snav-dropdown').attr('hidden', '');
            $openItem.removeClass('is-open');
            $openItem = null;
        }

        $stickyHeader.on('click', '.mdb-snav-toggle', function(e) {
            e.stopPropagation();
            var $item   = $(this).closest('.mdb-snav-item');
            var isOpen  = $(this).attr('aria-expanded') === 'true';
            closeAll();
            if (!isOpen) {
                $(this).attr('aria-expanded', 'true');
                $item.find('.mdb-snav-dropdown').removeAttr('hidden');
                $item.addClass('is-open');
                $openItem = $item;
            }
        });

        $(document).on('click.stickyHeader', function(e) {
            if (!$(e.target).closest('.mdb-sticky-header').length) closeAll();
        });
        $(document).on('keydown.stickyHeader', function(e) {
            if (e.key === 'Escape') closeAll();
        });

        // ------------------------------------------------------------------
        // 4. Scroll visibility — show after scrolling past the main header
        // ------------------------------------------------------------------
        var isStickyActive = false;

        $(window).on('scroll.stickyHeader', function() {
            var headerBottom = $mainHeader.offset().top + $mainHeader.outerHeight();
            if ($(window).scrollTop() > headerBottom) {
                if (!isStickyActive) {
                    isStickyActive = true;
                    $stickyHeader.addClass('is-visible');
                    document.documentElement.style.setProperty(
                        '--sticky-header-height', $stickyHeader[0].offsetHeight + 'px'
                    );
                }
            } else {
                if (isStickyActive) {
                    isStickyActive = false;
                    $stickyHeader.removeClass('is-visible');
                    closeAll();
                }
            }
            syncPinnedButtons();
        });

        // ------------------------------------------------------------------
        // 5. Pin-to-sticky — elements with class .mdb-pin-to-sticky get cloned
        //    into .mdb-sticky-actions when they scroll above the viewport.
        //    Add .mdb-pin-to-sticky to any Elementor button or link on the page.
        // ------------------------------------------------------------------
        var $actionsSlot = $stickyHeader.find('.mdb-sticky-actions');
        var pinIdCounter  = 0;

        function syncPinnedButtons() {
            $('.mdb-pin-to-sticky').each(function() {
                var $pin = $(this);

                // Assign a stable ID to this pin source on first pass.
                if (!$pin.data('mdb-pin-id')) {
                    $pin.data('mdb-pin-id', 'mdbpin-' + (++pinIdCounter));
                }
                var pinId    = $pin.data('mdb-pin-id');
                var isAbove  = this.getBoundingClientRect().bottom < 0;
                var $cloneEl = $actionsSlot.find('[data-mdb-pin-id="' + pinId + '"]');

                if (isAbove && !$cloneEl.length) {
                    // When .mdb-pin-to-sticky is placed on an Elementor widget wrapper
                    // (a div), resolve the inner <a> so we get the correct href.
                    var $pinLink = $pin.is('a, button') ? $pin : $pin.find('a, button').first();

                    // Prefer Elementor's dedicated text span; fall back to element text.
                    var text = ($pin.find('.elementor-button-text').first().text().trim()
                        || $pin.find('.mdb-btn-text').first().text().trim()
                        || $pin.text().trim());

                    var href   = $pinLink.is('a') ? ($pinLink.attr('href') || null) : null;
                    var target = $pinLink.attr('target') || null;
                    var $clone;

                    if (href) {
                        $clone = $('<a class="mdb-sticky-pinned-btn"></a>')
                            .attr('href', href)
                            .attr('target', target)
                            .text(text);
                    } else {
                        $clone = $('<button class="mdb-sticky-pinned-btn" type="button"></button>')
                            .text(text);
                        // Proxy clicks to the resolved interactive element.
                        $clone.on('click', function() {
                            ($pinLink.length ? $pinLink[0] : $pin[0]).click();
                        });
                    }

                    // Do NOT copy Elementor/theme classes — our styling is self-contained.
                    $clone.attr('data-mdb-pin-id', pinId);

                    // Insert before the cart icon.
                    $actionsSlot.find('.mdb-sticky-cart').before($clone);
                    $stickyHeader.addClass('has-pinned-btn');

                } else if (!isAbove && $cloneEl.length) {
                    $cloneEl.remove();
                    if (!$actionsSlot.find('.mdb-sticky-pinned-btn').length) {
                        $stickyHeader.removeClass('has-pinned-btn');
                    }
                }
            });
        }

        // Run once on load in case the page was already scrolled (e.g. back-navigation).
        syncPinnedButtons();
    }

    // Product Page Custom Layout restructuring
    function initProductLayout() {
        // Only run on the single product page
        if (!$('body.single-product').length) return;
        
        var $form = $('form.cart');
        if (!$form.length) return;
        
        // Add layout class to form
        $form.addClass('mdb-product-layout');
        
        // 1. Wrap the options module
        $('#uni_cpo_options').wrap('<div class="mdb-product-main"></div>');
        
        // 2. Create the Sidebar
        var $sidebar = $('<div class="mdb-product-sidebar"></div>');
        $form.append($sidebar);
        
        // 3. Populate Sidebar Content
        $sidebar.append('<h3 class="mdb-summary-heading">Order Summary</h3>');
        
        // Move the product title
        $sidebar.append($('.product_title'));
        
        // Ensure NBO summary wrapper exists or move it
        var $nboWrapper = $('.uni-cpo-summary');
        if (!$nboWrapper.length) {
             // Fallback if not physically present yet
             $nboWrapper = $('<div class="uni-cpo-summary"></div>');
        }
        $sidebar.append($nboWrapper);
        
        // Pricing block
        var $priceWrap = $('<div class="mdb-summary-price"><span>Total Price:</span></div>');
        var $dynamicPrice = $('#uni_cpo_pricedisplay_roller');
        if ($dynamicPrice.length) {
            $priceWrap.append($dynamicPrice);
        } else {
            $priceWrap.append($('.price').not('.mdb-summary-price .price').first());
        }
        $sidebar.append($priceWrap);
        
        // Actions block
        var $actions = $('<div class="mdb-summary-actions"></div>');
        
        // If uni calculate button is used, move it
        var $calcBtn = $('.uni-cpo-calculate-btn');
        if ($calcBtn.length) {
            $actions.append($calcBtn);
        }
        
        $actions.append($('.quantity'));
        $actions.append($('.single_add_to_cart_button'));
        
        $sidebar.append($actions);
        
        // Secure payments footer
        $sidebar.append('<div class="mdb-secure-payments">Secure Payments via<br><img src="/wp-content/plugins/woocommerce/assets/images/icons/credit-cards/visa.svg" alt="Visa" style="display:inline-block;width:40px;margin:5px;" /><img src="/wp-content/plugins/woocommerce/assets/images/icons/credit-cards/mastercard.svg" alt="Mastercard" style="display:inline-block;width:40px;margin:5px;" /><img src="/wp-content/plugins/woocommerce/assets/images/icons/credit-cards/amex.svg" alt="Amex" style="display:inline-block;width:40px;margin:5px;" /></div>');
        
        // Optionally inject step labels (STEP 1, STEP 2) iteratively over uni_rows
        $('.uni-builderius-container > [id^="uni_row_"]').each(function(index) {
            $(this).attr('data-step-title', 'STEP ' + (index + 1));
        });
    }
    
    // Mini Cart Variations Toggle
    function initMiniCartToggle() {
        var chevronSvg = '<svg class="mdb-chevron" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>';

        function enhanceVariations() {
            // Find all variation lists that haven't been processed yet
            $('.elementor-menu-cart__product-name dl.variation:not(.mdb-toggled)').each(function() {
                var $dl = $(this);
                $dl.addClass('mdb-toggled');

                // Create a toggle button with SVG chevron
                var $btn = $('<button type="button" class="mdb-options-toggle-btn">View Configuration ' + chevronSvg + '</button>');

                // Insert button right after the product title link (below the title, above price/meta)
                var $nameDiv = $dl.closest('.elementor-menu-cart__product-name');
                var $titleLink = $nameDiv.find('> a').first();
                if ($titleLink.length) {
                    $titleLink.after($btn);
                } else {
                    $dl.before($btn);
                }

                // Wrap the options in a slid-able container that is initially hidden
                $dl.wrap('<div class="mdb-options-wrapper" style="display: none;"></div>');

                // Handle click action — find wrapper via shared parent since button and
                // wrapper may not be direct siblings after the title-link insertion.
                $btn.on('click', function(e) {
                    e.preventDefault();
                    var $wrapper = $(this).closest('.elementor-menu-cart__product-name').find('.mdb-options-wrapper');

                    // Slide toggle and update button text/state
                    $wrapper.slideToggle(200);
                    $(this).toggleClass('active');

                    var label = $(this).hasClass('active') ? 'Hide Configuration ' : 'View Configuration ';
                    $(this).html(label + chevronSvg);
                });
            });
        }

        // Run initially for any loaded cart
        enhanceVariations();

        // Elementor/WooCommerce loads mini cart dynamically sometimes (AJAX)
        // Ensure new lists get toggled:
        $(document).ajaxComplete(function() {
            enhanceVariations();
        });
        
        // Elementor menu cart specific observer just in case
        $(document).on('click', '.elementor-menu-cart__toggle_button', function() {
            setTimeout(enhanceVariations, 100);
        });
    }
    
    // Off-canvas mobile navigation drawer
    function initMobileMenu() {
        var $toggle  = $('.mdb-mobile-toggle');
        var $drawer  = $('#mdb-mobile-drawer');

        if ( !$toggle.length || !$drawer.length ) return;

        var $overlay = $drawer.find('.mdb-mobile-drawer__overlay');
        var $close   = $drawer.find('.mdb-mobile-drawer__close');

        function openDrawer() {
            // Measure scrollbar width before locking scroll to prevent layout shift.
            var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
            document.documentElement.style.setProperty('--mdb-scrollbar-width', scrollbarWidth + 'px');
            $drawer.attr('aria-hidden', 'false');
            $toggle.attr('aria-expanded', 'true').addClass('is-open');
            document.body.classList.add('mdb-drawer-open');
            $(document).trigger('mdb:drawerOpen');
            // Move focus into the panel for keyboard / screen-reader users.
            setTimeout(function() { $close[0] && $close[0].focus(); }, 310);
        }

        function closeDrawer() {
            $drawer.attr('aria-hidden', 'true');
            $toggle.attr('aria-expanded', 'false').removeClass('is-open');
            document.body.classList.remove('mdb-drawer-open');
            $(document).trigger('mdb:drawerClose');
            // preventScroll stops the browser scrolling to the (possibly off-screen)
            // regular header button when the sticky header is showing.
            if ($toggle[0]) { $toggle[0].focus({ preventScroll: true }); }
        }

        // Hamburger toggle
        $toggle.on('click', function() {
            $drawer.attr('aria-hidden') === 'false' ? closeDrawer() : openDrawer();
        });

        // Close via button / overlay
        $close.on('click', closeDrawer);
        $overlay.on('click', closeDrawer);

        // Close on ESC
        $(document).on('keydown.mobileMenu', function(e) {
            if ( e.key === 'Escape' && $drawer.attr('aria-hidden') === 'false' ) {
                closeDrawer();
            }
        });

        // Accordion: toggle sub-menus
        $drawer.on('click', '.mdb-mnav-toggle', function() {
            var $btn     = $(this);
            var $sub     = $btn.closest('.mdb-mnav-item').find('> .mdb-mnav-sub');
            var isOpen   = $btn.attr('aria-expanded') === 'true';

            if ( isOpen ) {
                $btn.attr('aria-expanded', 'false');
                $sub.attr('hidden', '');
            } else {
                $btn.attr('aria-expanded', 'true');
                $sub.removeAttr('hidden');
            }
        });

        // Close drawer when a nav link (not a toggle) is tapped
        $drawer.on('click', 'a.mdb-mnav-link, .mdb-mnav-free-samples', function() {
            closeDrawer();
        });
    }
    
    // Opens our custom mini-cart side panel.
    function openMdbCart() {
        var panel = document.getElementById('mdb-mini-cart');
        if (!panel) return;
        // Measure scrollbar width before hiding overflow so the page doesn't shift.
        var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        document.documentElement.style.setProperty('--mdb-scrollbar-width', scrollbarWidth + 'px');
        panel.setAttribute('aria-hidden', 'false');
        document.body.classList.add('mdb-cart-open');
    }

    // Closes our custom mini-cart side panel.
    function closeMdbCart() {
        var panel = document.getElementById('mdb-mini-cart');
        if (!panel) return;
        panel.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('mdb-cart-open');
    }

    // Triggers the mini-cart. Tries the Elementor widget toggle first (for
    // backwards compatibility if an Elementor cart widget is on the page),
    // then falls back to our standalone PHP panel.
    function triggerElementorCart() {
        var selectors = [
            '#elementor-menu-cart__toggle_button',
            '.elementor-menu-cart__toggle > a',
            '.elementor-menu-cart__toggle > button'
        ];
        for (var i = 0; i < selectors.length; i++) {
            var el = document.querySelector(selectors[i]);
            if (el) { el.click(); return; }
        }
        openMdbCart();
    }

    // Reads cart item count from WooCommerce-managed elements in the DOM.
    function getCartCount() {
        // WooCommerce updates the mini-cart HTML on fragment refresh;
        // read count from the refreshed content inside our panel.
        var n = parseInt($('.mdb-mini-cart .woocommerce-mini-cart__total .amount, .mdb-mini-cart [data-product_sku]').length) || 0;
        // Fall back to WC cart-count span if present anywhere on page.
        if (!n) n = parseInt($('.woocommerce-cart-link__badge, .cart-contents-count, [data-cart-count]').first().text()) || 0;
        return n;
    }

    // Click handlers and count-sync for all cart buttons (topbar + sticky),
    // and open/close behaviour for our custom mini-cart panel.
    function initCartButtons() {
        // Open cart on topbar/sticky button click.
        $(document).on('click', '.mdb-topbar-cart, .mdb-sticky-cart', function() {
            triggerElementorCart();
        });

        // AJAX remove item — intercept .remove_from_cart_button clicks inside our panel.
        // Elementor's remove link (elementor_remove_from_cart_button) is hidden via CSS;
        // WooCommerce's remove_from_cart_button href is a valid remove URL we fetch silently.
        $(document).on('click', '#mdb-mini-cart .remove_from_cart_button', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var url  = $btn.attr('href');
            if (!url) return;
            $btn.addClass('is-removing');
            $.get(url, function() {
                // Trigger WooCommerce fragment refresh which will update
                // div.widget_shopping_cart_content and fire wc_fragments_refreshed.
                $(document.body).trigger('wc_fragment_refresh');
            }).fail(function() {
                $btn.removeClass('is-removing');
            });
        });

        // Close cart via close button or overlay click.
        $(document).on('click', '.mdb-mini-cart__close, .mdb-mini-cart__overlay', function() {
            closeMdbCart();
        });

        // Close on ESC.
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') closeMdbCart();
        });

        // Count sync: WooCommerce AJAX fragment updates fire these events.
        // We re-count from the refreshed DOM to stay accurate.
        $(document.body).on('wc_fragments_refreshed added_to_cart removed_from_cart updated_cart_totals', function() {
            // Derive counts from the WooCommerce cart object echoed into
            // the refreshed mini-cart fragment (most reliable source).
            var n = parseInt(
                $('.mdb-mini-cart .woocommerce-mini-cart__total').siblings('.woocommerce-mini-cart__count, .quantity, .cart-items-number').first().text()
            ) || $('.mdb-mini-cart .woocommerce-mini-cart-item').length || 0;
            if (!n) {
                // WooCommerce stores count on the cart-menu-item link badge
                n = parseInt($('.cart-contents-count, .elementor-button-badge, [data-counter]').first().text()) || 0;
            }
            $('.mdb-topbar-cart-count').text(n).toggleClass('is-hidden', n === 0);
            $('.mdb-topbar-cart').attr('aria-label', 'Cart (' + n + ' items)');
            $('.mdb-sticky-cart-count').text(n).toggle(n > 0);
            $('.mdb-sticky-cart').attr('aria-label', 'Cart (' + n + ' items)');

            // Auto-open cart when item is added (mirrors Elementor's UX).
            if (typeof arguments[0] !== 'undefined' && arguments[0].type === 'added_to_cart') {
                openMdbCart();
            }
        });
    }

    // Example: Smooth scroll to anchor links
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            var target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 1000);
            }
        });
    }

    // -----------------------------------------------------------------------
    // My Account — AJAX tab navigation
    // Intercepts sidebar nav clicks and swaps only .mdb-account__content,
    // keeping the sidebar and header in place. Falls back to full page load
    // on errors or if the response doesn't contain the expected element.
    // Logout links are always handled as full-page navigations (they need
    // the redirect chain to clear cookies / session).
    // -----------------------------------------------------------------------
    function initAccountAjaxNav() {
        var $nav = $('.mdb-account-nav');
        if (!$nav.length) return;

        var $content = $('.mdb-account__content');
        if (!$content.length) return;

        // Save initial state so back-button works on first visit.
        history.replaceState({ mdbAccount: true, url: window.location.href }, document.title);

        // Click handler on sidebar links.
        $nav.on('click', '.mdb-account-nav__link', function (e) {
            var href = $(this).attr('href') || '';

            // Let the browser handle logout — it needs cookies to be cleared.
            if (!href || href.indexOf('customer-logout') !== -1) return;

            e.preventDefault();
            fetchAccountPage(href, $(this));
        });

        // Click handler on dashboard quick-link cards.
        // Delegated on body so it keeps working after each AJAX swap.
        $(document.body).on('click', '.mdb-account-quicklink', function (e) {
            var href = $(this).attr('href') || '';
            if (!href || href.indexOf('customer-logout') !== -1) return;

            e.preventDefault();

            // Find the matching sidebar link so active states stay in sync.
            var $matchingNav = $nav.find('.mdb-account-nav__link').filter(function () {
                return $(this).attr('href') === href;
            });

            fetchAccountPage(href, $matchingNav.length ? $matchingNav : null);
        });

        // Handle browser back / forward.
        $(window).on('popstate', function (e) {
            var state = e.originalEvent.state;
            if (state && state.mdbAccount) {
                fetchAccountPage(state.url, null, true);
            }
        });

        /**
         * Fetches an account page and swaps content.
         *
         * @param {string}  url          Destination URL.
         * @param {jQuery}  $link        Clicked nav link (null on popstate).
         * @param {boolean} isPopstate   True when triggered by back/forward.
         */
        function fetchAccountPage(url, $link, isPopstate) {
            $content.addClass('mdb-account-loading');

            $.ajax({
                url: url,
                type: 'GET',
                success: function (html) {
                    // Parse response and extract just the content area.
                    var $doc        = $('<div>').append($.parseHTML(html, document, true));
                    var newContent  = $doc.find('.mdb-account__content').html();
                    var newTitle    = $doc.filter('title').text() || $doc.find('title').text();

                    if (!newContent) {
                        // Couldn't find our wrapper — fall back to full reload.
                        window.location.href = url;
                        return;
                    }

                    $content.html(newContent).removeClass('mdb-account-loading');

                    // Keep page <title> in sync.
                    if (newTitle) document.title = newTitle;

                    // Update active states on the nav links.
                    if ($link) {
                        $nav.find('.mdb-account-nav__link')
                            .removeClass('mdb-account-nav__link--active')
                            .removeAttr('aria-current');
                        $link
                            .addClass('mdb-account-nav__link--active')
                            .attr('aria-current', 'page');

                        // Mirror WooCommerce's is-active class on the <li>.
                        $nav.find('.mdb-account-nav__item')
                            .removeClass('is-active');
                        $link.closest('.mdb-account-nav__item')
                            .addClass('is-active');
                    }

                    // Push the new URL into the browser history.
                    if (!isPopstate) {
                        history.pushState({ mdbAccount: true, url: url }, document.title, url);
                    }

                    // Let WooCommerce blocks / fragments know the DOM changed.
                    $(document.body).trigger('wc_fragment_refresh');

                    // Convert any notices injected by the new content into toasts.
                    initToasts($content[0]);

                    // Smooth scroll to top of the content panel.
                    $('html, body').animate(
                        { scrollTop: $content.offset().top - 120 },
                        200
                    );
                },
                error: function () {
                    // Network error — fall back to normal navigation.
                    window.location.href = url;
                }
            });
        }
    }

    // -----------------------------------------------------------------------
    // Toast notifications
    // Finds .mdb-toast elements anywhere in the DOM (or within a given root),
    // moves them into a fixed #mdb-toast-stack, then auto-dismisses them.
    //
    // Success / info toasts auto-dismiss after 5 s.
    // Error toasts stay until manually closed.
    // -----------------------------------------------------------------------
    function initToasts(root) {
        var AUTO_DISMISS_MS = 5000;
        var $root = root ? $(root) : $(document.body);

        // Ensure the stack container exists in the DOM.
        var $stack = $('#mdb-toast-stack');
        if (!$stack.length) {
            $stack = $('<div id="mdb-toast-stack" aria-live="polite" aria-atomic="false"></div>')
                .appendTo(document.body);
        }

        // Find all toast elements within root, move them to the stack.
        $root.find('.mdb-toast').each(function () {
            var $toast = $(this);

            // Avoid double-processing.
            if ($toast.data('mdb-toast-init')) return;
            $toast.data('mdb-toast-init', true);

            // Move to fixed stack (detach keeps data/events).
            $toast.detach().appendTo($stack).show();

            var isError = $toast.hasClass('mdb-toast--error');
            var duration = isError ? null : AUTO_DISMISS_MS;

            // Add animated progress bar for auto-dismiss toasts.
            var $progress = null;
            if (duration) {
                $progress = $('<span class="mdb-toast__progress"></span>')
                    .css('animation-duration', duration + 'ms')
                    .appendTo($toast);
            }

            // Close button.
            $toast.find('.mdb-toast__close').on('click', function () {
                dismissToast($toast);
            });

            // Auto-dismiss.
            var timer = null;
            if (duration) {
                timer = setTimeout(function () {
                    dismissToast($toast);
                }, duration);
            }

            // Pause auto-dismiss on hover.
            if (timer) {
                $toast.on('mouseenter', function () {
                    clearTimeout(timer);
                    if ($progress) $progress.css('animation-play-state', 'paused');
                }).on('mouseleave', function () {
                    if ($progress) $progress.css('animation-play-state', 'running');
                    timer = setTimeout(function () {
                        dismissToast($toast);
                    }, 1500); // shorter grace period after hover
                });
            }
        });

        function dismissToast($toast) {
            $toast.addClass('mdb-toast--leaving');
            $toast.one('animationend webkitAnimationEnd', function () {
                $toast.remove();
            });
        }
    }

    // -------------------------------------------------------------------------
    // AJAX Login & Registration
    // -------------------------------------------------------------------------
    function initAjaxAuth() {
        // Only run on the my-account page login/register forms.
        if (!$('.mdb-login-form').length) return;

        // ---- Tab switching -----------------------------------------------
        $(document).on('click', '.mdb-auth-tab', function() {
            var $tab = $(this);
            var target = $tab.data('tab'); // 'login' | 'register'

            // Update tab button states.
            $tab.closest('.mdb-auth-tabs').find('.mdb-auth-tab')
                .removeClass('mdb-auth-tab--active')
                .attr('aria-selected', 'false');
            $tab.addClass('mdb-auth-tab--active').attr('aria-selected', 'true');

            // Show the matching panel, hide the other.
            var $card = $tab.closest('.mdb-account-login').find('.mdb-account-login__card');
            $card.find('.mdb-auth-panel').removeClass('mdb-auth-panel--active').attr('hidden', true);
            $card.find('#mdb-panel-' + target).addClass('mdb-auth-panel--active').removeAttr('hidden');
        });

        /**
         * Show an inline error/success message inside a form card.
         * @param {jQuery} $form  - the form element
         * @param {string} msg    - message text
         * @param {string} type   - 'error' | 'success'
         */
        function showFormMessage($form, msg, type) {
            $form.find('.mdb-auth-message').remove();
            var $msg = $('<p class="mdb-auth-message mdb-auth-message--' + type + '"></p>').text(msg);
            $form.prepend($msg);
        }

        /**
         * Set a form's loading state.
         * @param {jQuery}  $form    - the form element
         * @param {boolean} loading  - true = disable + show spinner
         */
        function setLoading($form, loading) {
            var $btn = $form.find('[type="submit"]');
            if (loading) {
                $btn.prop('disabled', true).addClass('mdb-btn--loading');
            } else {
                $btn.prop('disabled', false).removeClass('mdb-btn--loading');
            }
        }

        // ---- Login form ------------------------------------------------
        $(document).on('submit', '.mdb-login-form.login', function(e) {
            e.preventDefault();
            var $form = $(this);

            $form.find('.mdb-auth-message').remove();
            setLoading($form, true);

            $.post(
                mdbAjax.url,
                $form.serialize() + '&action=mdb_ajax_login',
                function(response) {
                    setLoading($form, false);
                    if (response.success) {
                        showFormMessage($form, 'Signed in! Redirecting…', 'success');
                        window.location.href = response.data.redirect;
                    } else {
                        showFormMessage($form, response.data.message, 'error');
                    }
                }
            ).fail(function() {
                setLoading($form, false);
                showFormMessage($form, 'Something went wrong. Please try again.', 'error');
            });
        });

        // ---- Register form ---------------------------------------------
        $(document).on('submit', '.mdb-login-form.register', function(e) {
            e.preventDefault();
            var $form = $(this);

            $form.find('.mdb-auth-message').remove();
            setLoading($form, true);

            $.post(
                mdbAjax.url,
                $form.serialize() + '&action=mdb_ajax_register',
                function(response) {
                    setLoading($form, false);
                    if (response.success) {
                        showFormMessage($form, 'Account created! Redirecting…', 'success');
                        window.location.href = response.data.redirect;
                    } else {
                        showFormMessage($form, response.data.message, 'error');
                    }
                }
            ).fail(function() {
                setLoading($form, false);
                showFormMessage($form, 'Something went wrong. Please try again.', 'error');
            });
        });
    }

})(jQuery);