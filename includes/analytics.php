<?php
/**
 * Analytics: GTM + GA4 Script Injection
 * Loads on the 'testing-lab' page AND single product pages (nucleus_product CPT)
 * Debug mode: ON — events visible in GA4 DebugView in real time.
 *             Set NUCLEUS_GA4_DEBUG to false to disable before going to production.
 */

if (!defined('ABSPATH'))
    exit;

// GA4 Measurement ID (change this if you switch GA4 properties)
define('NUCLEUS_GA4_ID',    'G-V6CKR789PG');
define('NUCLEUS_GTM_ID',    'GTM-NKL3T3HW');
define('NUCLEUS_GA4_DEBUG', true);  // ← set to false before going to production

// Helper: detects if the current page uses the products landing shortcode
// This is more reliable than is_page('assessments') which depends on the slug.
function nucleus_is_products_landing() {
    global $post;
    return $post && has_shortcode($post->post_content, 'nucleus_products_landing');
}

// Helper: returns true on pages where analytics should load
function nucleus_should_load_analytics() {
    return is_page('testing-lab') || is_singular('nucleus_product') || nucleus_is_products_landing();
}

// Inject GTM + GA4 into <head>
function nucleus_core_add_gtm_head()
{
    if (nucleus_should_load_analytics()) {
        ?>
        <!-- Google Tag Manager -->
        <script>(function (w, d, s, l, i) {
                w[l] = w[l] || []; w[l].push({
                    'gtm.start':
                        new Date().getTime(), event: 'gtm.js'
                }); var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                        'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
            })(window, document, 'script', 'dataLayer', '<?php echo NUCLEUS_GTM_ID; ?>');</script>
        <!-- End Google Tag Manager -->

        <!-- GA4 Direct (for custom events) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo NUCLEUS_GA4_ID; ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '<?php echo NUCLEUS_GA4_ID; ?>', {
                send_page_view: false,
                debug_mode:     <?php echo NUCLEUS_GA4_DEBUG ? 'true' : 'false'; ?>
            });
        </script>
        <?php
    }
}
add_action('wp_head', 'nucleus_core_add_gtm_head');

// Inject GTM noscript into <body>
function nucleus_core_add_gtm_body()
{
    if (nucleus_should_load_analytics()) {
        ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo NUCLEUS_GTM_ID; ?>" height="0" width="0"
                style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <?php
    }
}
add_action('wp_body_open', 'nucleus_core_add_gtm_body');
