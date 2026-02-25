/**
 * Nucleus DXP — Event Tracking
 * =============================
 * Tracks user interactions and sends events to GA4.
 * Edit this file to add/modify tracked events.
 *
 * Events fired:
 *   - view_feature      (click on a feature card)
 *   - view_service      (click on a service tag)
 *   - download_brochure (click on download button)
 *   - generate_lead     (form submit — fired via CF7 wpcf7mailsent DOM event)
 *   - view_item         (product page loaded — for view-to-cart ratio)
 *   - add_to_cart       (Shopify Buy Button clicked — for view-to-cart ratio)
 *   - view_assessment   (click on View Assessment button on products landing page)
 */

document.addEventListener('DOMContentLoaded', function () {

    // Track Feature Card Clicks (What We Do + Value Prop)
    // Supports both old (.feature-card) and new (.what-item, .value-card) classes
    const featureSelectors = '.feature-card, .what-item, .value-card';
    document.querySelectorAll(featureSelectors).forEach(function (card) {
        card.addEventListener('click', function () {
            // Try to find title in h3 or h4
            let titleEl = this.querySelector('h3') || this.querySelector('h4');
            var featureName = titleEl ? titleEl.innerText : 'Unknown Feature';

            console.log('✅ Feature Clicked:', featureName);
            if (typeof gtag === 'function') {
                gtag('event', 'view_feature', { 'feature_name': featureName });
            }
        });
    });

    // Track Service Tag Clicks
    // Supports both old (.service-tag) and new (.stag) classes
    const serviceSelectors = '.service-tag, .stag';
    document.querySelectorAll(serviceSelectors).forEach(function (tag) {
        tag.addEventListener('click', function () {
            var serviceName = this.innerText;
            console.log('✅ Service Viewed:', serviceName);
            if (typeof gtag === 'function') {
                gtag('event', 'view_service', { 'service_name': serviceName });
            }
        });
    });

    // Track Download Brochure Clicks
    // We added the specific class .download-brochure-btn back to the link
    document.querySelectorAll('.download-brochure-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            // Prevent default if it's a # link (for demo purposes)
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
            }

            console.log('✅ Brochure Download Clicked');
            if (typeof gtag === 'function') {
                gtag('event', 'download_brochure');
            }
        });
    });

    // ─── Product View-to-Cart Ratio Tracking ─────────────────────────────────
    // Fires 'view_item' on page load for any single-product page, and
    // 'add_to_cart' when the Shopify Buy Button is clicked.
    // View-to-Cart Ratio in GA4 = add_to_cart events ÷ view_item events

    const productWrapper = document.querySelector('.nucleus-single-product-wrapper');

    if (productWrapper) {
        // Grab product meta from the rendered template
        const productTitleEl = productWrapper.querySelector('.n-product-title');
        const productPriceEl = productWrapper.querySelector('.n-product-price');
        const productName = productTitleEl ? productTitleEl.innerText.trim() : 'Unknown Product';
        const productPrice = productPriceEl ? productPriceEl.innerText.trim() : null;

        // 1. Fire 'view_item' — counts every product page view
        console.log('✅ Product Viewed:', productName);
        if (typeof gtag === 'function') {
            gtag('event', 'view_item', {
                'item_name': productName,
                'item_price': productPrice
            });
        }

        // 2. Fire 'add_to_cart' — Shopify Buy Button lives in an iframe, so we
        //    listen for postMessage events it broadcasts on button click, with a
        //    fallback click listener on the wrapper div for non-iframe renders.
        const buyButtonWrapper = productWrapper.querySelector('.n-product-buy-button');

        // Fallback: direct click on the buy-button container (catches non-iframe cases)
        if (buyButtonWrapper) {
            buyButtonWrapper.addEventListener('click', function () {
                console.log('✅ Add to Cart Clicked (fallback):', productName);
                if (typeof gtag === 'function') {
                    gtag('event', 'add_to_cart', {
                        'item_name': productName,
                        'item_price': productPrice
                    });
                }
            });
        }

        // Primary: listen for Shopify's postMessage broadcast from the iframe
        // Shopify Buy Button emits 'Shopify.cartUpdate' or a click message cross-origin
        window.addEventListener('message', function (event) {
            // Only handle messages from Shopify's Buy Button domain
            if (!event.origin.includes('shopify.com') && !event.origin.includes('myshopify.com')) {
                return;
            }

            var data = event.data || {};
            // Shopify Buy Button fires a 'Shopify.cartUpdate' type message on add-to-cart
            if (data.type === 'Shopify.cartUpdate' || (typeof data === 'string' && data.includes('cart'))) {
                console.log('✅ Add to Cart Clicked (Shopify iframe):', productName);
                if (typeof gtag === 'function') {
                    gtag('event', 'add_to_cart', {
                        'item_name': productName,
                        'item_price': productPrice
                    });
                }
            }
        });
    }

    // ─── Products Landing — "View Assessment" Button Tracking ────────────────
    // Fires 'view_assessment' for each product's button click.
    // Use 'item_name' dimension in GA4 to rank products by attraction.

    document.querySelectorAll('.nl-slide-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var productName = this.getAttribute('data-product') || 'Unknown Assessment';
            var position = this.getAttribute('data-position') || 'Unknown';

            console.log('✅ View Assessment Clicked:', productName, '(Position ' + position + ')');
            if (typeof gtag === 'function') {
                gtag('event', 'view_assessment', {
                    'item_name': productName,
                    'carousel_position': parseInt(position)  // 1, 2, or 3
                });
            }
        });
    });

    // ─── Consultation Lead Form — Generate Lead Event ────────────────────────
    // CF7 fires the custom DOM event 'wpcf7mailsent' on the form element
    // immediately after a successful submission (mail sent + DB saved).
    // We listen for it here and push a 'generate_lead' event to GA4.

    document.addEventListener('wpcf7mailsent', function (event) {
        var formDetail = event.detail || {};
        var formId = formDetail.contactFormId || formDetail.id || 'unknown';
        var formTitle = (formDetail.inputs || []).reduce(function (acc, input) {
            // Try to pull the page title as a fallback label
            return acc;
        }, document.title || 'Consultation Form');

        console.log('✅ Lead Form Submitted — firing generate_lead (Form ID: ' + formId + ')');

        if (typeof gtag === 'function') {
            gtag('event', 'generate_lead', {
                'form_id': String(formId),
                'form_name': formTitle,
                'page_location': window.location.href
            });
        }
    });

    console.log('✅ Nucleus DXP Tracking Active (v2.4)');
});