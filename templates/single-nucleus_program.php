<?php
/**
 * Template Name: Nucleus Program (Standalone)
 * Description: Default template for displaying Nucleus Programs with Custom HF
 */

$post_id = get_the_ID();
$hf_set_id = get_post_meta($post_id, '_nucleus_selected_hf_set', true);

$program_desc = get_post_meta($post_id, '_nucleus_program_desc', true);
$media_json = get_post_meta($post_id, '_nucleus_program_media', true);
$media_files = json_decode($media_json, true);

$date_type = get_post_meta($post_id, '_nucleus_program_date_type', true);
$start_date = get_post_meta($post_id, '_nucleus_program_start_date', true);
$end_date = get_post_meta($post_id, '_nucleus_program_end_date', true);
$hide_date = get_post_meta($post_id, '_nucleus_program_hide_date', true);

$outcomes = get_post_meta($post_id, '_nucleus_program_outcomes', true);
$audience = get_post_meta($post_id, '_nucleus_program_audience', true);

if (!is_array($media_files)) {
    $media_files = array();
}

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

?>
<style>
/* Global Resets matching Event Template */
html { overflow-x: hidden; }
html, body { margin: 0 !important; padding: 0 !important; display: block !important; max-width: 100% !important; }
.n-act-wrapper, .nucleus-hf-root, .nucleus-hf-section { max-width: 100%; box-sizing: border-box; }
.nucleus-header-root { position: relative !important; z-index: 9999; width: 100%; display: block; }
.nucleus-header-root img { max-width: 130px !important; max-height: 45px !important; width: auto; height: auto; object-fit: contain; }
.nucleus-header-root .nucleus-container { justify-content: space-between !important; gap: 20px !important; flex-wrap: wrap !important; }
.nucleus-header-root .nucleus-hf-comp:first-child { flex-shrink: 0; margin-right: auto; }
.nucleus-header-root .nucleus-hf-comp { font-size: clamp(0.7rem, 1.2vw, 0.85rem); white-space: nowrap; }
.nucleus-footer-root img { max-width: 200px !important; max-height: 80px !important; width: auto; height: auto; object-fit: contain; }
.nucleus-footer-root { position: relative !important; z-index: 999; width: 100%; clear: both; display: block; }

@media (max-width: 768px) {
    .nucleus-header-root .nucleus-container { max-width: 100% !important; width: 100% !important; box-sizing: border-box !important; padding: 12px 16px !important; gap: 6px !important; justify-content: center !important; flex-wrap: wrap !important; }
    .nucleus-header-root .nucleus-hf-comp:first-child { width: 100%; text-align: center; margin-right: 0; margin-bottom: 6px; flex-shrink: 0; }
    .nucleus-header-root .nucleus-hf-comp { font-size: 0.68rem !important; padding: 4px 8px; white-space: nowrap; }
    .nucleus-header-root img { max-width: 90px !important; max-height: 32px !important; }
    .nucleus-footer-root .nucleus-container { flex-direction: column !important; text-align: center; gap: 12px !important; padding: 20px 16px !important; }
    .nucleus-footer-root .nucleus-hf-comp { font-size: 0.8rem; }
}
@media (max-width: 400px) {
    .nucleus-header-root .nucleus-hf-comp { font-size: 0.6rem !important; padding: 3px 5px; letter-spacing: -0.01em; }
}

.n-act-wrapper { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #ffffff; color: #0f172a; min-height: 80vh; padding-bottom: 120px; -webkit-font-smoothing: antialiased; }
.n-act-hero { position: relative; width: 100%; padding: 80px 32px 60px; text-align: left; background: #ffffff; color: #0f172a; box-sizing: border-box; }
.n-act-hero-inner { max-width: 1200px; margin: 0 auto; }
.n-act-badge { display: inline-block; padding: 6px 16px; background: rgba(37,99,235,0.1); border: 1px solid rgba(37,99,235,0.2); color: #2563eb; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 24px; }
.n-act-title { font-size: 3.2rem; font-weight: 800; margin: 0 0 24px; max-width: 900px; color: #0f172a; letter-spacing: -0.04em; line-height: 1.1; }
.n-act-title span { color: #2563eb; }
.n-act-desc { max-width: 800px; font-size: 1.15rem; line-height: 1.7; color: #64748b; border-left: 3px solid #e2e8f0; padding-left: 24px; }

.n-act-gallery-container { max-width: 1200px; margin: 0 auto 60px; padding: 0 32px; }
.n-act-gallery-title { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 32px; }
.n-act-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 32px; width: 100%; }
.n-act-item { position: relative; border-radius: 12px; overflow: hidden; background: #f9fafb; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02); cursor: pointer; aspect-ratio: 16 / 10; }
.n-act-item:hover { box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01); }
.n-act-item img, .n-act-item video { width: 100%; height: 100%; object-fit: cover !important; display: block; transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
.n-act-item:hover img, .n-act-item:hover video { transform: scale(1.04); }
.n-act-empty { text-align: center; padding: 80px 40px; background: #f9fafb; border: 1px dashed #d1d5db; border-radius: 12px; color: #6b7280; font-size: 1.1rem; }

/* Event Child Cards */
.n-event-card { display: block; text-decoration: none; padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; transition: all 0.2s; }
.n-event-card:hover { border-color: #cbd5e1; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); transform: translateY(-2px); }
.n-event-card-title { font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0 0 8px; }
.n-event-card-desc { font-size: 0.95rem; color: #64748b; line-height: 1.5; margin: 0; }

@media (max-width: 768px) {
    .n-act-hero { margin: 60px auto 40px; padding: 0 20px; }
    .n-act-title { font-size: 2.2rem; }
    .n-act-desc { font-size: 1rem; border-left:none; padding-left:0; margin-top:16px;}
    .n-act-gallery { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .n-act-gallery-container { padding: 0 20px; }
}
</style>

<div class="n-act-wrapper">
    <div class="n-act-hero" id="overview">
        <div class="n-act-hero-inner">

            <h1 class="n-act-title">
                <?php 
                $title = get_the_title();
                $words = explode(' ', $title);
                if(count($words) > 1) {
                    $last_word = array_pop($words);
                    echo esc_html(implode(' ', $words)) . ' <span>' . esc_html($last_word) . '</span>';
                } else {
                    echo esc_html($title);
                }
                ?>
            </h1>
            <?php if ($hide_date !== '1'): ?>
                <?php
                $date_string = '';
                if ($date_type === 'soon') {
                    $date_string = 'Starting Soon';
                } elseif ($date_type === 'ongoing') {
                    $date_string = 'Ongoing';
                } else {
                    if (!empty($start_date)) {
                        $date_string = date('F j, Y', strtotime($start_date));
                        if (!empty($end_date)) {
                            $date_string .= ' &mdash; ' . date('F j, Y', strtotime($end_date));
                        }
                    }
                }
                ?>
                <?php if (!empty($date_string)): ?>
                    <div style="font-weight: 600; color: #64748b; margin-top: -12px; margin-bottom: 24px; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                        <span class="dashicons dashicons-calendar-alt"></span> <?php echo $date_string; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($program_desc)): ?>
                <div class="n-act-desc">
                    <?php echo wp_kses_post($program_desc); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($audience) || !empty($outcomes)): ?>
        <div class="n-act-gallery-container" id="outcomes" style="margin-top:0;">
            <div style="background:#f8fafc; border-radius:12px; padding:40px; margin-bottom:60px; border:1px solid #e2e8f0; display:flex; flex-wrap:wrap; gap:40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02)">
                <?php if (!empty($audience)): ?>
                <div style="flex:1; min-width:250px;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:1.25rem;">Target Audience</h3>
                    <p style="color:#475569; font-size:1.05rem; margin:0; font-weight:500;"><?php echo esc_html($audience); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($outcomes)): ?>
                <div style="flex:2; min-width:300px;">
                    <h3 style="margin-top:0; color:#0f172a; font-size:1.25rem;">Key Outcomes</h3>
                    <div style="color:#475569; line-height:1.6; font-size:1.05rem;">
                        <?php echo wp_kses_post($outcomes); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Linked Events Section -->
    <?php
    $events = get_posts(array(
        'post_type' => 'nucleus_event',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => '_nucleus_parent_program',
                'value' => $post_id,
                'compare' => '='
            )
        )
    ));
    if (!empty($events)):
    ?>
    <div class="n-act-gallery-container" id="events" style="border-bottom: 1px solid #e2e8f0; padding-bottom: 60px; margin-bottom: 60px;">
        <h3 class="n-act-gallery-title">Events in this Program</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
            <?php foreach ($events as $event): ?>
                <a href="<?php echo esc_url(get_permalink($event->ID)); ?>" class="n-event-card">
                    <h4 class="n-event-card-title"><?php echo esc_html($event->post_title); ?></h4>
                    <p class="n-event-card-desc">Click to view event details and gallery &rarr;</p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Media Highlights -->
    <?php
    $n_images = [];
    $n_videos = [];
    if (!empty($media_files)) {
        foreach ($media_files as $media) {
            if ($media['type'] === 'video') { $n_videos[] = $media; } 
            else { $n_images[] = $media; }
        }
    }
    ?>

    <?php if (!empty($n_videos)): ?>
    <div class="n-act-gallery-container" id="gallery">
        <h3 class="n-act-gallery-title">Featured Videos</h3>
        <div style="display: flex; flex-direction: column; gap: 40px;">
            <?php foreach ($n_videos as $video): ?>
                <div style="position: relative; border-radius: 16px; overflow: hidden; background: #000; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.1);">
                    <video src="<?php echo esc_url($video['url']); ?>" controls preload="metadata" style="width: 100%; display: block; max-height: 80vh;"></video>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($n_images)): ?>
    <div class="n-act-gallery-container" <?php if(empty($n_videos)) echo 'id="gallery"'; ?>>
        <h3 class="n-act-gallery-title">Program Gallery</h3>
        <div class="n-act-gallery">
            <?php foreach ($n_images as $img): ?>
                <div class="n-act-item">
                    <img src="<?php echo esc_url($img['url']); ?>" loading="lazy" alt="Program Photo">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
if ($hf_set_id) {
    if (function_exists('nucleus_render_hf_set')) { echo nucleus_render_hf_set($hf_set_id, 'footer'); }
    wp_footer();
    echo '</body></html>';
} else {
    get_footer();
}
?>
