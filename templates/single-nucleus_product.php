<?php
/**
 * Template Name: Nucleus Product (Standalone)
 * Description: Default generic template for displaying Nucleus Products with custom HF sets.
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
        gap: 40px !important;
    }
    
    /* Push navigation to the right and prevent overlap */
    .nucleus-header-root .nucleus-hf-comp:first-child {
        flex-shrink: 0;
        margin-right: auto;
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

echo '<div class="nucleus-page-container">';

// -- 2. Render Product Content properly using our robust shortcode system --
if (function_exists('nucleus_single_product_shortcode')) {
    echo nucleus_single_product_shortcode(array('id' => $post_id));
}

echo '</div>'; // End container

if (isset($hf_set_id) && $hf_set_id) {
    if (function_exists('nucleus_render_hf_set')) {
        echo nucleus_render_hf_set($hf_set_id, 'footer');
    }
    wp_footer();
    echo '</body></html>';
} else {
    get_footer();
}
