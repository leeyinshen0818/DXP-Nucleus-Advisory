<?php
/**
 * Template Name: Nucleus Event (Standalone)
 * Description: Default template for displaying Nucleus Events with Custom HF
 */

$post_id = get_the_ID();
$hf_set_id = get_post_meta($post_id, '_nucleus_selected_hf_set', true);

// Fetch Event Meta Data
$activity_desc = get_post_meta($post_id, '_nucleus_event_desc', true);
$media_json = get_post_meta($post_id, '_nucleus_event_media', true);
$media_files = json_decode($media_json, true);

$date_type = get_post_meta($post_id, '_nucleus_event_date_type', true);
$start_date = get_post_meta($post_id, '_nucleus_event_start_date', true);
$end_date = get_post_meta($post_id, '_nucleus_event_end_date', true);
$hide_date = get_post_meta($post_id, '_nucleus_event_hide_date', true);

$time_loc = get_post_meta($post_id, '_nucleus_event_time_loc', true);
$speakers = get_post_meta($post_id, '_nucleus_event_speakers', true);
$agenda = get_post_meta($post_id, '_nucleus_event_agenda', true);

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

// ----------------------------------------------------
// Page Body Container
// ----------------------------------------------------
?>
<style>
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

.n-act-wrapper,
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

/* ===== MOBILE RESPONSIVE HEADER/FOOTER ===== */
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

/* Reset */
.n-act-wrapper {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background-color: #ffffff;
    color: #0f172a;
    min-height: 80vh;
    padding-bottom: 120px;
    -webkit-font-smoothing: antialiased;
}

/* ========= HERO SECTION ========= */
.n-act-hero {
    position: relative;
    width: 100%;
    padding: 80px 32px 60px;
    text-align: left;
    background: #ffffff;
    color: #0f172a;
    box-sizing: border-box;
}

/* Center the hero content within 1200px */
.n-act-hero-inner {
    max-width: 1200px;
    margin: 0 auto;
}

.n-act-hero .nucleus-container-inner {
    max-width: 1200px;
    margin: 0 auto;
}

.n-act-parent-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    margin-bottom: 24px;
    transition: color 0.2s;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.n-act-parent-link:hover {
    color: #2563eb;
}

.n-act-title {
    font-size: 3.2rem;
    font-weight: 800;
    margin: 0 0 24px;
    max-width: 900px;
    color: #0f172a;
    letter-spacing: -0.04em;
    line-height: 1.1;
}

/* Minimalist corporate accent highlight */
.n-act-title span {
    color: #2563eb; 
}

.n-act-desc {
    max-width: 800px;
    font-size: 1.15rem;
    line-height: 1.7;
    color: #64748b;
    border-left: 3px solid #e2e8f0;
    padding-left: 24px;
}

/* ========= GALLERY MULTI-GRID ========= */
.n-act-gallery-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 32px;
}

.n-act-gallery-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
}

.n-act-gallery-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.n-act-gallery {
    display: grid;
    /* Strict identical grid sizes for uniform premium look */
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 32px;
    width: 100%;
}

.n-act-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    background: #f9fafb;
    border: 1px solid rgba(0,0,0,0.06); /* Corporate style soft border */
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
    cursor: pointer;
    /* Force 16:10 uniform cinematic aspect ratio so all photos match perfectly */
    aspect-ratio: 16 / 10; 
}

.n-act-item:hover {
    box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01);
}

.n-act-item img,
.n-act-item video {
    width: 100%;
    height: 100%;
    object-fit: cover !important; /* Perfect crop */
    display: block;
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.n-act-item:hover img,
.n-act-item:hover video {
    transform: scale(1.04);
}

/* Optional Overlay on Hover */
.n-act-item-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.1) 0%, transparent 40%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}
.n-act-item:hover .n-act-item-overlay {
    opacity: 1;
}

/* Video badge */
.n-act-video-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(4px);
    color: #111827;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

/* Empty State */
.n-act-empty {
    text-align: center;
    padding: 80px 40px;
    background: #f9fafb;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    color: #6b7280;
    grid-column: 1 / -1;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .n-act-hero { margin: 60px auto 40px; padding: 0 20px; }
    .n-act-title { font-size: 2.2rem; }
    .n-act-desc { font-size: 1rem; border-left:none; padding-left:0; margin-top:16px;}
    .n-act-gallery { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
    .n-act-gallery-container { padding: 0 20px; }
}
</style>

<div class="n-act-wrapper">
    <div class="n-act-hero">
        <div class="n-act-hero-inner">
            <?php
            $parent_program_id = get_post_meta($post_id, '_nucleus_parent_program', true);
            if ($parent_program_id) {
                $program_title = get_the_title($parent_program_id);
                $program_url = get_permalink($parent_program_id);
                ?>
                <a href="<?php echo esc_url($program_url); ?>" class="n-act-parent-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Part of <?php echo esc_html($program_title); ?>
                </a>
                <?php
            }
            ?>
            <!-- Smart split text feature if you type something like "Awesome Activity" -->
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
            <?php if (!empty($activity_desc)): ?>
                <div class="n-act-desc" style="margin-bottom:32px;">
                    <?php echo wp_kses_post($activity_desc); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($time_loc) || !empty($speakers) || !empty($agenda)): ?>
                <div style="background:#fff; border: 1px solid #e2e8f0; border-radius:12px; padding:40px; margin-bottom:48px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);">
                    <h2 style="margin-top:0; color:#0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom:16px; margin-bottom:24px;">Event Details</h2>
                    <div style="display:flex; flex-wrap:wrap; gap:40px;">
                        <div style="flex:1; min-width:280px;">
                            <?php if (!empty($time_loc)): ?>
                            <div style="margin-bottom:32px;">
                                <h4 style="color:#64748b; margin:0 0 8px; text-transform:uppercase; letter-spacing:0.05em; font-size:0.85rem;">Time & Location</h4>
                                <p style="font-size:1.15rem; font-weight:500; margin:0; color:#0f172a;"><?php echo esc_html($time_loc); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($speakers)): ?>
                            <div>
                                <h4 style="color:#64748b; margin:0 0 12px; text-transform:uppercase; letter-spacing:0.05em; font-size:0.85rem;">Speakers & Facilitators</h4>
                                <ul style="list-style:none; padding:0; margin:0;">
                                    <?php 
                                    $spk_list = explode("\n", str_replace("\r", "", $speakers));
                                    foreach ($spk_list as $spk) {
                                        if (trim($spk) !== '') {
                                            echo '<li style="margin-bottom:8px; display:flex; align-items:center; gap:8px; color:#0f172a;"><span class="dashicons dashicons-businessman" style="color:#94a3b8;"></span> ' . esc_html($spk) . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($agenda)): ?>
                        <div style="flex:2; min-width:300px;">
                            <h4 style="color:#64748b; margin:0 0 16px; text-transform:uppercase; letter-spacing:0.05em; font-size:0.85rem;">Agenda</h4>
                            <div style="background:#f8fafc; padding:24px; border-radius:8px; font-size:1.05rem; line-height:1.7; color:#334155;">
                                <?php echo wp_kses_post($agenda); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $n_images = [];
    $n_videos = [];
    if (!empty($media_files)) {
        foreach ($media_files as $media) {
            if ($media['type'] === 'video') {
                $n_videos[] = $media;
            } else {
                $n_images[] = $media;
            }
        }
    }
    ?>

    <?php if (!empty($n_videos)): ?>
    <!-- Featured Videos Section -->
    <div class="n-act-video-container" style="max-width: 1200px; margin: 0 auto 60px; padding: 0 32px;">
        <h3 class="n-act-gallery-title" style="margin-bottom: 24px;">Featured Footage</h3>
        <div style="display: flex; flex-direction: column; gap: 40px;">
            <?php foreach ($n_videos as $video): ?>
                <div style="position: relative; border-radius: 16px; overflow: hidden; background: #000; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.1);">
                    <video src="<?php echo esc_url($video['url']); ?>" controls preload="metadata" style="width: 100%; display: block; max-height: 80vh;"></video>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Image Gallery Section -->
    <div class="n-act-gallery-container">
        <?php if (!empty($n_images)): ?>
            <div class="n-act-gallery-header">
                <h3 class="n-act-gallery-title">Event Highlights</h3>
            </div>
            <div class="n-act-gallery">
                <?php foreach ($n_images as $img): ?>
                    <div class="n-act-item">
                        <img src="<?php echo esc_url($img['url']); ?>" loading="lazy" alt="<?php echo esc_attr(get_the_title()); ?> Event Photo">
                        <div class="n-act-item-overlay"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (empty($n_videos)): ?>
            <div class="n-act-empty">📸 Check back soon! Event highlights are currently being processed.</div>
        <?php endif; ?>
    </div>

    <?php
    // Enterprise Next/Previous Navigation UI
    $prev_post = get_previous_post();
    $next_post = get_next_post();
    
    if ($prev_post || $next_post):
    ?>
    <style>
        .n-act-nav-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 32px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 768px) {
            .n-act-nav-container { grid-template-columns: 1fr; }
        }
        .n-act-nav-card {
            padding: 40px 32px;
            background: #f8fafc;
            border-radius: 16px;
            text-decoration: none !important;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        .n-act-nav-card.prev { text-align: left; }
        .n-act-nav-card.next { text-align: right; }
        
        .n-act-nav-card:hover {
            background: #ffffff;
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.01);
            border-color: #cbd5e1;
        }
        .n-act-nav-label {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .n-act-nav-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.3;
        }
        .n-act-nav-card.next .n-act-nav-title span { display: inline-block; transition: transform 0.3s ease; color: #2563eb; margin-left:8px; }
        .n-act-nav-card.next:hover .n-act-nav-title span { transform: translateX(6px); }
        
        .n-act-nav-card.prev .n-act-nav-title span { display: inline-block; transition: transform 0.3s ease; color: #2563eb; margin-right:8px; }
        .n-act-nav-card.prev:hover .n-act-nav-title span { transform: translateX(-6px); }
    </style>
    
    <div class="n-act-nav-container">
        <?php if ($prev_post): ?>
            <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" class="n-act-nav-card prev">
                <span class="n-act-nav-label">Previous Event</span>
                <h3 class="n-act-nav-title"><span>&larr;</span> <?php echo esc_html(get_the_title($prev_post->ID)); ?></h3>
            </a>
        <?php else: ?>
            <div></div> <!-- Empty spacer -->
        <?php endif; ?>

        <?php if ($next_post): ?>
            <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" class="n-act-nav-card next">
                <span class="n-act-nav-label">Next Event</span>
                <h3 class="n-act-nav-title"><?php echo esc_html(get_the_title($next_post->ID)); ?> <span>&rarr;</span></h3>
            </a>
        <?php else: ?>
            <div></div> <!-- Empty spacer -->
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ================= LIGHTBOX SYSTEM ================= -->
<style>
.n-act-lightbox {
    position: fixed;
    z-index: 999999;
    inset: 0;
    background: rgba(10, 15, 25, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
    cursor: zoom-out;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}
.n-act-lightbox.active {
    opacity: 1;
    visibility: visible;
}
.n-act-lightbox img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 6px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    transform: scale(0.95);
    transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    object-fit: contain;
}
.n-act-lightbox.active img {
    transform: scale(1);
}
.n-act-lightbox-close {
    position: absolute;
    top: 30px;
    right: 40px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 50px;
    font-weight: 300;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
}
.n-act-lightbox-close:hover {
    color: #ffffff;
}
</style>

<div id="n-act-lightbox" class="n-act-lightbox" onclick="closeLightbox()">
    <span class="n-act-lightbox-close">&times;</span>
    <img id="n-act-lightbox-img" src="" alt="Popup Image">
</div>

<script>
function openLightbox(src) {
    var lightbox = document.getElementById('n-act-lightbox');
    var lightboxImg = document.getElementById('n-act-lightbox-img');
    lightboxImg.src = src;
    lightbox.classList.add('active');
}
function closeLightbox() {
    var lightbox = document.getElementById('n-act-lightbox');
    lightbox.classList.remove('active');
    setTimeout(function() {
        document.getElementById('n-act-lightbox-img').src = '';
    }, 300);
}
document.addEventListener("DOMContentLoaded", function() {
    var galleryItems = document.querySelectorAll('.n-act-item img');
    galleryItems.forEach(function(img) {
        img.parentElement.style.cursor = 'zoom-in';
        img.parentElement.addEventListener('click', function(e) {
            // Prevent if it's a video
            if (!this.querySelector('video')) {
                e.preventDefault();
                openLightbox(img.src);
            }
        });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === "Escape") {
            closeLightbox();
        }
    });
});
</script>

<?php
// ----------------------------------------------------
// Determine Footer
// ----------------------------------------------------
if ($hf_set_id) {
    if (function_exists('nucleus_render_hf_set')) {
        echo nucleus_render_hf_set($hf_set_id, 'footer');
    }
    
    // Render required WP Footer scripts for custom template
    wp_footer();
    echo '</body></html>';
} else {
    get_footer();
}
?>
