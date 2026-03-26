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

    .nucleus-page-container,
    .nucleus-hf-root,
    .nucleus-hf-section {
        max-width: 100%;
        box-sizing: border-box;
    }

    .nucleus-header-root {
        position: relative !important;
        z-index: 9999;
        width: 100%;
        display: block;
    }

    .nucleus-header-root img {
        max-width: 130px !important;
        max-height: 45px !important;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .nucleus-header-root .nucleus-container {
        justify-content: space-between !important;
        gap: 20px !important;
        flex-wrap: wrap !important;
    }
    
    .nucleus-header-root .nucleus-hf-comp:first-child {
        flex-shrink: 0;
        margin-right: auto;
    }

    .nucleus-header-root .nucleus-hf-comp {
        font-size: clamp(0.7rem, 1.2vw, 0.85rem);
        white-space: nowrap;
    }

    .nucleus-footer-root img {
        max-width: 200px !important;
        max-height: 80px !important;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .nucleus-page-container {
        width: 100%;
        background: transparent;
        color: #1d2327;
        position: relative;
        z-index: 1;
        box-sizing: border-box;
        min-height: 60vh;
        display: block;
    }

    .nucleus-footer-root {
        position: relative !important; 
        z-index: 999;
        width: 100%;
        clear: both;
        display: block;
    }

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
        .nucleus-header-root .nucleus-hf-comp:first-child {
            width: 100%;
            text-align: center;
            margin-right: 0;
            margin-bottom: 6px;
            flex-shrink: 0;
        }
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

    @media (max-width: 400px) {
        .nucleus-header-root .nucleus-hf-comp {
            font-size: 0.6rem !important;
            padding: 3px 5px;
            letter-spacing: -0.01em;
        }
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
