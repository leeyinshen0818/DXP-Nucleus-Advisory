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

$page_data = get_post_meta($post_id, '_nucleus_page_components', true);
$page_css = get_post_meta($post_id, '_nucleus_page_css', true);

// -- 1. Render Custom CSS --
// Set strong default styles to ensure visibility against any theme overrides
echo "<style type='text/css'>
    /* -- Page Structure & Safeties -- */
    body {
        margin: 0 !important;
        padding: 0 !important;
        /* Revert display flex to avoid breaking child row floats or flex assumptions */
        display: block !important; 
    }

    /* Keep Header safely at the top without overlapping content */
    .nucleus-header-root {
        position: relative !important; /* Changed from sticky just to ensure 100% natural document flow like Oxygen */
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
        gap: 40px !important;
    }
    
    /* Push navigation to the right and prevent overlap */
    .nucleus-header-root .nucleus-hf-comp:first-child {
        flex-shrink: 0;
        margin-right: auto;
    }

    /* ----- Native Dropdown Menu Styling Support ----- */
    .nucleus-header-menu {
        display: flex;
        align-items: center;
        gap: 30px;
        margin: 0;
        padding: 0;
        list-style: none;
        font-family: inherit;
        font-weight: 600;
        font-size: 14px;
        color: #fff;
    }
    .nucleus-header-menu a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s;
    }
    .nucleus-header-menu a:hover {
        color: #00e0ff; /* Light blue hover */
    }
    
    /* General Item Formatting */
    .nucleus-header-menu li {
        position: relative;
        padding: 10px 0;
    }

    /* Sub-menu initial hidden state */
    .nucleus-header-menu li ul.sub-menu {
        position: absolute;
        top: 100%;
        left: 0;
        background: #fff;
        min-width: 220px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-radius: 4px;
        list-style: none;
        margin: 0;
        padding: 10px 0;
        
        /* Hidden by default with smooth fade-in */
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.3s ease;
        z-index: 99999;
    }

    /* Display Sub-menu on parent hover */
    .nucleus-header-menu li:hover > ul.sub-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    /* Sub-menu link styling */
    .nucleus-header-menu li ul.sub-menu li {
        padding: 0;
    }
    .nucleus-header-menu li ul.sub-menu li a {
        padding: 10px 20px;
        display: block;
        color: #1d2327;
        font-weight: 500;
    }
    .nucleus-header-menu li ul.sub-menu li a:hover {
        background: #f0f0f1;
        color: #2271b1;
    }

    /* Keep footer logos contained safely */
    .nucleus-footer-root img {
        max-width: 200px !important;
        max-height: 80px !important;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    /* Force visibility defaults and push footer to the bottom */
    .nucleus-page-container {
        width: 100%;
        background: transparent;
        color: #1d2327;
        position: relative;
        z-index: 1;
        box-sizing: border-box;
        min-height: 60vh; /* Ensure there is space for content between header and footer */
        display: block;
    }

    /* Prevent footer from collapsing over page content */
    .nucleus-footer-root {
        position: relative !important; 
        z-index: 999;
        width: 100%;
        clear: both;
        display: block;
    }
</style>";

// -- 2. Render Page Content properly using our robust shortcode system --
if (function_exists('nucleus_page_content_shortcode')) {
    echo nucleus_page_content_shortcode(array('id' => $post_id));
}

if (isset($hf_set_id) && $hf_set_id) {
    if (function_exists('nucleus_render_hf_set')) {
        echo nucleus_render_hf_set($hf_set_id, 'footer');
    }
    wp_footer();
    echo '</body></html>';
} else {
    get_footer();
}