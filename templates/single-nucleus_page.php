<?php
/**
 * Template Name: Nucleus Page (Default)
 * Description: Default generic template for displaying Nucleus Pages.
 * Renders dynamic sections and applies custom CSS.
 */

$post_id = get_the_ID();
$hf_set_id = get_post_meta($post_id, '_nucleus_selected_hf_set', true);

if ($hf_set_id) {
    ?><!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
    <?php
    if (function_exists('nucleus_render_hf_set')) {
        echo nucleus_render_hf_set($hf_set_id, 'header');
    }
} else {
    get_header();
}

$page_meta = get_post_meta($post_id, '_nucleus_page_components', true);
$page_data = is_string($page_meta) ? json_decode(base64_decode($page_meta), true) : $page_meta;

$css_meta = get_post_meta($post_id, '_nucleus_page_css', true);
$page_css = is_string($css_meta) ? json_decode(base64_decode($css_meta), true) : $css_meta;

// -- 1. Render Custom CSS --
// Set strong default styles to ensure visibility against any theme overrides
echo "<style type='text/css'>
    /* -- Global Overflow Fix -- */
    html {
        overflow-x: hidden;
    }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
        max-width: 100% !important;
    }

    /* Override Oxygen / Theme wrappers that constrain page width */
    #ct-inner-content,
    .ct-inner-content,
    .ct-section-inner-wrap,
    .entry-content,
    .post-inner,
    .site-content,
    .content-area,
    #content,
    #primary,
    #main,
    .oxygen-body-content {
        max-width: 100% !important;
        width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    /* Page content container — force full width */
    .nucleus-page-container,
    #nucleus-page-container {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent;
        color: #1d2327;
        position: relative;
        z-index: 1;
        box-sizing: border-box;
        min-height: 60vh;
        display: block;
    }

    .nucleus-sections-root {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Each section: full width of parent (parent is already forced to 100%) */
    .nucleus-section {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }

    /* Keep header/footer sections normal (don't double-break-out) */
    .nucleus-hf-root,
    .nucleus-hf-section {
        max-width: 100%;
        box-sizing: border-box;
    }

    /* Keep Header safely at the top */
    .nucleus-header-root {
        position: relative !important;
        z-index: 9999;
        width: 100%;
        display: block;
    }

    /* Force sensible max sizes for custom logos in Header */
    .nucleus-header-root img {
        max-width: 130px !important;
        max-height: 45px !important;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    /* Keep Header Flexbox nicely spaced out */
    .nucleus-header-root .nucleus-container {
        justify-content: space-between !important;
        gap: 20px !important;
        flex-wrap: wrap !important;
    }
    
    /* Push navigation to the right and prevent overlap */
    .nucleus-header-root .nucleus-hf-comp:first-child {
        flex-shrink: 0;
        margin-right: auto;
    }

    /* Header text/nav items responsive */
    .nucleus-header-root .nucleus-hf-comp {
        font-size: clamp(0.7rem, 1.2vw, 0.85rem);
        white-space: nowrap;
    }

    /* Keep footer logos contained safely */
    .nucleus-footer-root img {
        max-width: 200px !important;
        max-height: 80px !important;
        width: auto;
        height: auto;
        object-fit: contain;
    }



    /* Prevent footer from collapsing over page content */
    .nucleus-footer-root {
        position: relative !important; 
        z-index: 999;
        width: 100%;
        clear: both;
        display: block;
    }

    /* ===== MOBILE RESPONSIVE ===== */
    @media (max-width: 768px) {
        .nucleus-header-root .nucleus-container {
            max-width: 100% !important;
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 12px 16px !important;
            gap: 6px !important;
            justify-content: center !important;
            flex-wrap: wrap !important;
        }

        /* Logo row — full width, centered */
        .nucleus-header-root .nucleus-hf-comp:first-child {
            width: 100%;
            text-align: center;
            margin-right: 0;
            margin-bottom: 6px;
            flex-shrink: 0;
        }

        /* Nav items — smaller, allow wrapping */
        .nucleus-header-root .nucleus-hf-comp {
            font-size: 0.68rem !important;
            padding: 4px 8px;
            white-space: nowrap;
        }

        .nucleus-header-root img {
            max-width: 90px !important;
            max-height: 32px !important;
        }

        .nucleus-footer-root .nucleus-container {
            flex-direction: column !important;
            text-align: center;
            gap: 12px !important;
            padding: 20px 16px !important;
        }

        .nucleus-footer-root .nucleus-hf-comp {
            font-size: 0.8rem;
        }
    }

    /* Extra small phones (iPhone SE, etc.) */
    @media (max-width: 400px) {
        .nucleus-header-root .nucleus-hf-comp {
            font-size: 0.6rem !important;
            padding: 3px 5px;
            letter-spacing: -0.01em;
        }
    }
</style>";

// -- 2. Render Page Content properly using our robust shortcode system --
if (function_exists('nucleus_page_content_shortcode')) {
    echo nucleus_page_content_shortcode(array('id' => $post_id));
}

// -- 3. Global Scroll-Reveal Animation System --
echo '
<style>
/* Scroll Reveal: Hidden state */
.nucleus-section {
    opacity: 0;
    transform: translateY(40px);
    transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), 
                transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Scroll Reveal: Visible state */
.nucleus-section.n-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Stagger children inside each section */
.nucleus-section.n-visible .nucleus-component {
    opacity: 0;
    animation: nRevealChild 0.6s ease forwards;
}
.nucleus-section.n-visible .nucleus-component:nth-child(1) { animation-delay: 0.1s; }
.nucleus-section.n-visible .nucleus-component:nth-child(2) { animation-delay: 0.2s; }
.nucleus-section.n-visible .nucleus-component:nth-child(3) { animation-delay: 0.3s; }
.nucleus-section.n-visible .nucleus-component:nth-child(4) { animation-delay: 0.4s; }
.nucleus-section.n-visible .nucleus-component:nth-child(5) { animation-delay: 0.5s; }
.nucleus-section.n-visible .nucleus-component:nth-child(6) { animation-delay: 0.55s; }
.nucleus-section.n-visible .nucleus-component:nth-child(7) { animation-delay: 0.6s; }
.nucleus-section.n-visible .nucleus-component:nth-child(8) { animation-delay: 0.65s; }

/* Stagger grouped items (cards, etc.) */
.nucleus-section.n-visible .nucleus-group {
    opacity: 0;
    animation: nRevealChild 0.6s ease forwards;
}
.nucleus-section.n-visible .nucleus-group:nth-child(1) { animation-delay: 0.15s; }
.nucleus-section.n-visible .nucleus-group:nth-child(2) { animation-delay: 0.25s; }
.nucleus-section.n-visible .nucleus-group:nth-child(3) { animation-delay: 0.35s; }
.nucleus-section.n-visible .nucleus-group:nth-child(4) { animation-delay: 0.45s; }
.nucleus-section.n-visible .nucleus-group:nth-child(5) { animation-delay: 0.55s; }

@keyframes nRevealChild {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* First section (hero) should be visible immediately */
.nucleus-section:first-child {
    opacity: 1;
    transform: translateY(0);
}
.nucleus-section:first-child .nucleus-component,
.nucleus-section:first-child .nucleus-group {
    opacity: 1;
    animation: none;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var sections = document.querySelectorAll(".nucleus-section");
    
    // Mark first section visible immediately
    if (sections.length > 0) {
        sections[0].classList.add("n-visible");
    }

    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add("n-visible");
                observer.unobserve(entry.target); // Only animate once
            }
        });
    }, {
        threshold: 0.15,  // Trigger when 15% of section is visible
        rootMargin: "0px 0px -50px 0px"
    });

    sections.forEach(function(section, index) {
        if (index > 0) { // Skip first section (hero)
            observer.observe(section);
        }
    });
});
</script>
';

if (isset($hf_set_id) && $hf_set_id) {
    if (function_exists('nucleus_render_hf_set')) {
        echo nucleus_render_hf_set($hf_set_id, 'footer');
    }
    wp_footer();
    echo '</body></html>';
} else {
    get_footer();
}