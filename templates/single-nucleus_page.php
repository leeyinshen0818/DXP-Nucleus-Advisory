<?php
/**
 * Template Name: Nucleus Page (Default)
 * Description: Default generic template for displaying Nucleus Pages. 
 * Renders dynamic sections and applies custom CSS.
 */

get_header(); 

$post_id = get_the_ID();
$page_data = get_post_meta($post_id, '_nucleus_page_components', true);
$page_css = get_post_meta($post_id, '_nucleus_page_css', true);

// -- 1. Render Custom CSS --
// Set strong default styles to ensure visibility against any theme overrides
echo "<style type='text/css'>
    /* Force visibility defaults */
    .nucleus-page-container {
        width: 100%;
        min-height: 100vh;
        background: #fff; /* Default white page bg */
        color: #1d2327;   /* Default dark text */
    }
    .nucleus-section {
        display: block;
        width: 100%;
        position: relative;
        padding: 60px 20px;
        box-sizing: border-box;
    }
    /* Ensure text is readable on light backgrounds by default */
    .nucleus-title, .nucleus-subtitle, .nucleus-text {
        color: inherit;
    }
    /* Debug helper (optional, can be removed) */
    .nucleus-section:empty::after {
        content: 'Empty Section';
        display: block;
        padding: 20px;
        background: #f0f0f1;
        color: #666;
        text-align: center;
    }
</style>";

if (!empty($page_css) && is_array($page_css)) {
    echo "\n<style type='text/css' id='nucleus-page-custom-css'>\n";
    foreach ($page_css as $section_id => $css_block) {
        // Output sanitized CSS (basic stripping here, but ideally use a robust library)
        if (!empty($css_block)) {
            echo "/* Section: " . esc_html($section_id) . " */\n";
            echo strip_tags($css_block) . "\n";
        }
    }
    echo "</style>\n";
}
?>

<div id="nucleus-page-container" class="nucleus-page-container">

    <!-- Standard WordPress Content (Optional Intro) -->
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php if(get_the_content()): ?>
            <div class="nucleus-standard-content" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>
    <?php endwhile; endif; ?>

    <!-- Dynamic Sections Renderer -->
    <?php if (!empty($page_data) && is_array($page_data)) : ?>
        <div class="nucleus-sections-root">
            <?php foreach ($page_data as $section) : 
                $original_sec_id = isset($section['section_id']) ? $section['section_id'] : 'section-'.rand(100,999);
                $sec_id = sanitize_title($original_sec_id);
                
                // Background Logic
                $bg_style = '';
                if (isset($section['bg_type'])) {
                    if ($section['bg_type'] === 'color' && !empty($section['bg_value'])) {
                        $bg_style = 'background-color: ' . esc_attr($section['bg_value']) . ';';
                    } elseif ($section['bg_type'] === 'image' && !empty($section['bg_value'])) {
                        $bg_style = 'background-image: url(' . esc_url($section['bg_value']) . '); background-size: cover; background-position: center;';
                    }
                }
            ?>
                <!-- Use ID for anchoring and CSS targeting -->
                <section id="nucleus-section-<?php echo esc_attr($sec_id); ?>" class="nucleus-section" style="<?php echo $bg_style; ?> padding: 60px 20px; position: relative;">
                    <div class="nucleus-container" style="max-width: 1200px; margin: 0 auto;">
                        
                        <?php if (!empty($section['components'])) : ?>
                            <?php foreach ($section['components'] as $comp) : 
                                $comp_id = isset($comp['id']) ? sanitize_title($comp['id']) : 'comp';
                                $full_id = $sec_id . '-' . $comp_id;
                                $val = isset($comp['value']) ? $comp['value'] : '';
                                $type = isset($comp['type']) ? $comp['type'] : 'text';
                            ?>
                                <?php 
                                // --- RENDER BASED ON TYPE --- ID goes directly on the element
                                if ($type === 'image') {
                                    if (!empty($val)) {
                                        echo '<img id="' . esc_attr($full_id) . '" class="nucleus-component" src="' . esc_url($val) . '" alt="' . esc_attr($full_id) . '" style="max-width: 100%; height: auto;" />';
                                    }
                                } elseif ($type === 'url') {
                                    if (!empty($val)) {
                                        echo '<a id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-link" href="' . esc_url($val) . '">' . esc_html($val) . '</a>';
                                    }
                                } elseif ($type === 'link_text') {
                                    $link_url = isset($comp['meta']) ? $comp['meta'] : '#';
                                    if (!empty($val)) {
                                        echo '<a id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-btn-primary" href="' . esc_url($link_url) . '">' . esc_html($val) . '</a>';
                                    }
                                } elseif ($type === 'textarea') {
                                    echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-text">' . wpautop(esc_html($val)) . '</div>';
                                } elseif ($type === 'number') {
                                    echo '<span id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-number">' . esc_html($val) . '</span>';
                                } else {
                                    if (strpos($comp_id, 'title') !== false && strpos($comp_id, 'subtitle') === false) {
                                        echo '<h2 id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-title">' . esc_html($val) . '</h2>';
                                    } elseif (strpos($comp_id, 'subtitle') !== false) {
                                        echo '<h4 id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-subtitle">' . esc_html($val) . '</h4>';
                                    } else {
                                        echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-text">' . esc_html($val) . '</div>';
                                    }
                                }
                                ?>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <?php if (current_user_can('edit_posts')) : ?>
            <div style="max-width: 800px; margin: 100px auto; padding: 40px; background: #fff; border: 1px dashed #ccc; text-align: center;">
                <h3>No Sections Found</h3>
                <p>Use the "Page Content & Style Builder" meta box to add sections and components.</p>
                <small>Debug: Post ID <?php echo get_the_ID(); ?> | Template Active</small>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
