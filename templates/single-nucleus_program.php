<?php
/**
 * Single Activity Page — Simple, official, compact.
 */
if (!defined('ABSPATH')) exit;

$post_id   = get_the_ID();
$hf_set_id = get_post_meta($post_id, '_nucleus_selected_hf_set', true);
$p         = '_nucleus_activity_';

$a_type     = get_post_meta($post_id, $p . 'type', true) ?: 'program';
$desc       = get_post_meta($post_id, $p . 'desc', true);
$start_date = get_post_meta($post_id, $p . 'start_date', true);
$end_date   = get_post_meta($post_id, $p . 'end_date', true);
$start_time = get_post_meta($post_id, $p . 'start_time', true);
$end_time   = get_post_meta($post_id, $p . 'end_time', true);
$location   = get_post_meta($post_id, $p . 'location', true);
$show_date     = get_post_meta($post_id, $p . 'show_date', true);
$show_time     = get_post_meta($post_id, $p . 'show_time', true);
$show_location = get_post_meta($post_id, $p . 'show_location', true);
$hrdc        = get_post_meta($post_id, $p . 'hrdc', true);
$hrdc_url    = get_post_meta($post_id, $p . 'hrdc_url', true);
$contact_url = get_post_meta($post_id, $p . 'contact_url', true);
$media_json  = get_post_meta($post_id, $p . 'media', true);
$media_files = json_decode($media_json, true);
if (!is_array($media_files)) $media_files = array();
$tags = get_the_terms($post_id, 'nucleus_activity_tag');
$status = function_exists('nucleus_get_activity_status') ? nucleus_get_activity_status($post_id) : 'no-date';
$is_upcoming = ($status === 'upcoming');

$n_images = $n_videos = array();
foreach ($media_files as $m) { if ($m['type'] === 'video') $n_videos[] = $m; else $n_images[] = $m; }

// Header
if ($hf_set_id) {
    ?><!DOCTYPE html><html <?php language_attributes(); ?>><head>
    <meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo esc_html(get_the_title()); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?></head><body <?php body_class(); ?>>
    <?php if (function_exists('nucleus_render_hf_set')) echo nucleus_render_hf_set($hf_set_id, 'header');
} else { get_header(); }
?>

<style>
html{overflow-x:hidden}
html,body{margin:0!important;padding:0!important;display:block!important;max-width:100%!important}
.nucleus-header-root{position:relative!important;z-index:9999;width:100%;display:block}
.nucleus-header-root img{max-width:130px!important;max-height:45px!important;width:auto;height:auto;object-fit:contain}
.nucleus-header-root .nucleus-container{justify-content:space-between!important;gap:20px!important;flex-wrap:wrap!important}
.nucleus-header-root .nucleus-hf-comp:first-child{flex-shrink:0;margin-right:auto}
.nucleus-header-root .nucleus-hf-comp{font-size:clamp(.7rem,1.2vw,.85rem);white-space:nowrap}
.nucleus-footer-root img{max-width:200px!important;max-height:80px!important;width:auto;height:auto;object-fit:contain}
.nucleus-footer-root{position:relative!important;z-index:999;width:100%;clear:both;display:block}
@media(max-width:768px){
    .nucleus-header-root .nucleus-container{max-width:100%!important;width:100%!important;box-sizing:border-box!important;padding:12px 16px!important;gap:6px!important;justify-content:center!important;flex-wrap:wrap!important}
    .nucleus-header-root .nucleus-hf-comp:first-child{width:100%;text-align:center;margin-right:0;margin-bottom:6px}
    .nucleus-header-root .nucleus-hf-comp{font-size:.68rem!important;padding:4px 8px}
    .nucleus-header-root img{max-width:90px!important;max-height:32px!important}
    .nucleus-footer-root .nucleus-container{flex-direction:column!important;text-align:center;gap:12px!important;padding:20px 16px!important}
}

.sp-w{font-family:'Inter',-apple-system,sans-serif;background:var(--ncl-bg);color:var(--ncl-text-heading);min-height:80vh;padding-bottom:60px;-webkit-font-smoothing:antialiased}
.sp-c{max-width:780px;margin:0 auto;padding:0 20px}

/* Hero */
.sp-hero{padding:56px 0 32px}
.sp-back{display:inline-flex;align-items:center;gap:5px;color:var(--ncl-text-muted);text-decoration:none;font-size:.8rem;font-weight:500;margin-bottom:20px;transition:color .15s}
.sp-back:hover{color:var(--ncl-primary)}
.sp-meta-line{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px;font-size:.75rem;color:var(--ncl-text-muted)}
.sp-meta-line span{font-weight:500}
.sp-dot{color:var(--ncl-border)}
.sp-title{font-size:2.2rem;font-weight:700;color:var(--ncl-text-heading);margin:0 0 16px;line-height:1.2;letter-spacing:-.02em}
.sp-info{display:flex;flex-wrap:wrap;gap:16px;margin-bottom:24px;font-size:.88rem;color:var(--ncl-text-muted)}
.sp-info-item{display:flex;align-items:center;gap:5px}
.sp-info-item svg{color:var(--ncl-text-muted);flex-shrink:0}
.sp-desc{font-size:1rem;line-height:1.7;color:var(--ncl-text-body)}
.sp-desc p:first-child{margin-top:0}

/* Gallery */
.sp-gal-sec{margin:36px 0}
.sp-gal-h{font-size:1rem;font-weight:600;color:var(--ncl-text-heading);margin:0 0 14px}
.sp-gal{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
.sp-gi{border-radius:8px;overflow:hidden;background:var(--ncl-surface-hover);aspect-ratio:16/10;cursor:pointer}
.sp-gi img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .3s}
.sp-gi:hover img{transform:scale(1.03)}
.sp-vid{border-radius:8px;overflow:hidden;background:var(--ncl-text-heading);margin-bottom:14px}
.sp-vid video{width:100%;display:block;max-height:55vh}

/* Small CTAs */
.sp-actions{display:flex;gap:12px;flex-wrap:wrap;margin:32px 0;align-items:center}
.sp-hrdc-cta{display:inline-flex;align-items:center;gap:8px;padding:7px 16px;background:rgba(var(--ncl-accent-green-rgb), 0.1);color:var(--ncl-text-body);border:1px solid rgba(var(--ncl-accent-green-rgb), 0.3);border-radius:5px;text-decoration:none;font-size:1rem;font-weight:800;transition:background .15s}
.sp-contact-cta{display:inline-flex;align-items:center;gap:5px;padding:7px 16px;background:var(--ncl-primary);color:var(--ncl-bg);border-radius:5px;text-decoration:none;font-size:.82rem;font-weight:600;transition:background .15s}
.sp-contact-cta:hover{background:var(--ncl-primary-hover);color:var(--ncl-bg)}

/* Lightbox */
.sp-lb{position:fixed;z-index:999999;inset:0;background:rgba(var(--ncl-text-heading-rgb), .92);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(6px);cursor:zoom-out;opacity:0;visibility:hidden;transition:opacity .25s,visibility .25s}
.sp-lb.active{opacity:1;visibility:visible}
.sp-lb img{max-width:90vw;max-height:90vh;border-radius:4px;box-shadow:0 20px 40px rgba(var(--ncl-text-heading-rgb), .4);transform:scale(.96);transition:transform .3s;object-fit:contain}
.sp-lb.active img{transform:scale(1)}
.sp-lb-x{position:absolute;top:24px;right:32px;color:rgba(var(--ncl-bg-rgb), .6);font-size:40px;cursor:pointer;line-height:1;transition:color .15s}
.sp-lb-x:hover{color:var(--ncl-bg)}

@media(max-width:768px){
    .sp-hero{padding:40px 0 20px}
    .sp-title{font-size:1.7rem}
    .sp-gal{grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px}
}
</style>

<div class="sp-w">
<div class="sp-c">

    <div class="sp-hero">

        <a href="<?php echo esc_url(get_post_type_archive_link('nucleus_program')); ?>" class="sp-back">← All Activities</a>

        <div class="sp-meta-line">
            <span><?php echo esc_html(nucleus_activity_type_label($a_type)); ?></span>
            <?php if (!empty($tags) && !is_wp_error($tags)): ?>
                <?php foreach ($tags as $tag): ?>
                    <span class="sp-dot">·</span><span><?php echo esc_html($tag->name); ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if ($is_upcoming): ?>
                <span class="sp-dot">·</span><span style="color:var(--ncl-accent-green);">Upcoming</span>
            <?php elseif ($status === 'ongoing'): ?>
                <span class="sp-dot">·</span><span style="color:var(--ncl-primary);">Ongoing</span>
            <?php endif; ?>
        </div>

        <h1 class="sp-title"><?php echo esc_html(get_the_title()); ?></h1>

        <?php
        $info = array();
        if ($show_date === '1' && !empty($start_date)) {
            $ds = gmdate('F j, Y', strtotime($start_date));
            if (!empty($end_date) && $end_date !== $start_date) $ds .= ' — ' . gmdate('F j, Y', strtotime($end_date));
            $info[] = array('svg' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>', 'txt' => $ds);
        }
        if ($show_time === '1' && !empty($start_time)) {
            $ts = gmdate('g:i A', strtotime($start_time));
            if (!empty($end_time)) $ts .= ' – ' . gmdate('g:i A', strtotime($end_time));
            $info[] = array('svg' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'txt' => $ts);
        }
        if ($show_location === '1' && !empty($location)) {
            $info[] = array('svg' => '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>', 'txt' => $location);
        }
        if (!empty($info)):
        ?>
        <div class="sp-info">
            <?php foreach ($info as $i): ?>
                <div class="sp-info-item"><?php echo $i['svg']; ?> <?php echo esc_html($i['txt']); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($desc)): ?>
            <div class="sp-desc"><?php echo wp_kses_post($desc); ?></div>
        <?php endif; ?>

    </div>

    <!-- CTAs -->
    <?php if (($hrdc === '1') || ($is_upcoming && !empty($contact_url))): ?>
    <div class="sp-actions">
        <?php if ($hrdc === '1' && !empty($hrdc_url)): ?>
            <span class="sp-hrdc-cta">
              <img
                src="https://nucleusadvisory.co/wp-content/uploads/2026/04/RESIZE-HRD-Corp-Claimable-Logo.png"
                height="64px"
                width="64px"
              />
              HRDC Claimable
            </span>
        <?php elseif ($hrdc === '1'): ?>
            <span class="sp-hrdc-cta" style="cursor:default;">HRDC Claimable</span>
        <?php endif; ?>
        <?php if ($is_upcoming && !empty($contact_url)): ?>
            <a href="<?php echo esc_url($contact_url); ?>" class="sp-contact-cta">Contact Us →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Videos -->
    <?php if (!empty($n_videos)): ?>
    <div class="sp-gal-sec">
        <h3 class="sp-gal-h">Videos</h3>
        <?php foreach ($n_videos as $v): ?>
            <div class="sp-vid"><video src="<?php echo esc_url($v['url']); ?>" controls preload="metadata"></video></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Gallery -->
    <?php if (!empty($n_images)): ?>
    <div class="sp-gal-sec">
        <h3 class="sp-gal-h">Gallery</h3>
        <div class="sp-gal">
            <?php foreach ($n_images as $img): ?>
                <div class="sp-gi" onclick="spLB('<?php echo esc_url($img['url']); ?>')">
                    <img src="<?php echo esc_url($img['url']); ?>" loading="lazy" alt="<?php echo esc_attr(get_the_title()); ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>

<!-- Lightbox -->
<div id="sp-lb" class="sp-lb" onclick="spLBc()">
    <span class="sp-lb-x">&times;</span>
    <img id="sp-lb-img" src="" alt="">
</div>
<script>
function spLB(s){document.getElementById('sp-lb-img').src=s;document.getElementById('sp-lb').classList.add('active');}
function spLBc(){document.getElementById('sp-lb').classList.remove('active');setTimeout(function(){document.getElementById('sp-lb-img').src='';},250);}
document.addEventListener('keydown',function(e){if(e.key==='Escape')spLBc();});
</script>

<?php
if ($hf_set_id) {
    if (function_exists('nucleus_render_hf_set')) echo nucleus_render_hf_set($hf_set_id, 'footer');
    wp_footer(); echo '</body></html>';
} else { get_footer(); }
?>
