<?php
/**
 * Programmes Landing / Archive
 * Simple carousel + compact explorer table.
 */
if (!defined('ABSPATH')) exit;

$desc       = get_option('_nucleus_landing_desc', '');
$hf_set_id  = get_option('_nucleus_landing_hf', '');
$page_title = get_option('_nucleus_landing_title', 'Programmes & Initiatives');
if (empty($page_title)) $page_title = 'Programmes & Initiatives';

$p   = '_nucleus_activity_';
$now = current_time('Y-m-d');

// Upcoming (carousel)
$upcoming = get_posts(array(
    'post_type'      => 'nucleus_program',
    'posts_per_page' => 6,
    'post_status'    => 'publish',
    'meta_key'       => $p . 'start_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => array(array('key' => $p . 'start_date', 'value' => $now, 'compare' => '>=', 'type' => 'DATE')),
));

// All (explorer)
$all = get_posts(array('post_type' => 'nucleus_program', 'numberposts' => -1, 'post_status' => 'publish'));

// Header
if ($hf_set_id) {
    ?><!DOCTYPE html><html <?php language_attributes(); ?>><head>
    <meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo esc_html($page_title); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?></head><body <?php body_class(); ?>>
    <?php if (function_exists('nucleus_render_hf_set')) echo nucleus_render_hf_set($hf_set_id, 'header');
} else { get_header(); }
?>

<main>
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

/* Page */
.pl-w{max-width:1100px;margin:0 auto;padding:48px 20px 72px;font-family:'Inter',-apple-system,sans-serif;color:var(--ncl-text-heading)}
.pl-h1{font-size:2rem;font-weight:700;margin:0 0 8px;color:var(--ncl-text-heading)}
.pl-desc{font-size:1rem;line-height:1.6;color:var(--ncl-text-muted);margin-bottom:40px;max-width:700px}
.pl-label{font-size:1.1rem;font-weight:600;color:var(--ncl-text-heading);margin:0 0 16px;padding-bottom:10px;border-bottom:1px solid var(--ncl-border)}

/* Carousel */
.pl-car{position:relative;margin-bottom:56px;border-radius:10px;overflow:hidden;background:var(--ncl-surface);border:1px solid var(--ncl-border)}
.pl-sl{display:none;min-height:320px}
.pl-sl.active{display:flex;animation:plF .35s ease}
@keyframes plF{from{opacity:0}to{opacity:1}}
.pl-sl-body{flex:1.2;padding:40px 36px;display:flex;flex-direction:column;justify-content:center}
.pl-sl-img{flex:1;background-size:cover;background-position:center;background-color:var(--ncl-border);min-height:320px}
.pl-sl-sup{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--ncl-text-muted);margin-bottom:10px}
.pl-sl-title{font-size:1.6rem;font-weight:700;color:var(--ncl-text-heading);margin:0 0 10px;line-height:1.25}
.pl-sl-date{font-size:.85rem;font-weight:600;color:var(--ncl-primary);margin-bottom:12px}
.pl-sl-text{font-size:.9rem;color:var(--ncl-text-muted);line-height:1.5;margin-bottom:20px}
.pl-sl-btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;background:var(--ncl-primary);color:var(--ncl-bg);border-radius:5px;text-decoration:none;font-weight:600;font-size:.85rem;transition:background .15s;align-self:flex-start}
.pl-sl-btn:hover{background:var(--ncl-primary-hover);color:var(--ncl-bg)}
.pl-sl-cta{display:inline-flex;align-items:center;gap:5px;padding:9px 18px;background:transparent;color:var(--ncl-primary);border:1px solid var(--ncl-primary);border-radius:5px;text-decoration:none;font-weight:600;font-size:.85rem;transition:all .15s}
.pl-sl-cta:hover{background:var(--ncl-primary-muted);color:var(--ncl-primary)}
.pl-car-nav{position:absolute;bottom:16px;right:16px;display:flex;gap:6px;z-index:10}
.pl-nav{background:var(--ncl-text-heading);color:var(--ncl-bg);border:none;width:34px;height:34px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;font-size:14px}
.pl-nav:hover{background:var(--ncl-text-heading)}
.pl-tune{text-align:center;padding:48px 24px;background:var(--ncl-surface);border:1px solid var(--ncl-border);border-radius:10px;margin-bottom:56px}
.pl-tune h3{font-size:1.2rem;font-weight:600;color:var(--ncl-text-heading);margin:0 0 6px}
.pl-tune p{color:var(--ncl-text-muted);font-size:.9rem;margin:0}

@media(max-width:860px){
    .pl-sl{flex-direction:column-reverse}
    .pl-sl-img{min-height:200px;flex:none}
    .pl-sl-body{padding:28px 20px}
    .pl-car-nav{top:12px;bottom:auto}
}

/* Explorer */
.pl-filters{display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap}
.pl-fi{padding:8px 12px;border:1px solid var(--ncl-border);border-radius:5px;font-size:.85rem;outline:none;background:var(--ncl-bg)}
.pl-fi:focus{border-color:var(--ncl-primary)}
.pl-fi[type=text]{flex:1;min-width:160px}

.pl-tw{width:100%;border:1px solid var(--ncl-border);border-radius:6px;overflow-x:auto;background:var(--ncl-bg)}
.pl-t{width:100%;border-collapse:collapse;text-align:left;min-width:620px}
.pl-t th{padding:10px 14px;background:var(--ncl-surface);border-bottom:1px solid var(--ncl-border);color:var(--ncl-text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;font-weight:600;white-space:nowrap}
.pl-tr{border-bottom:1px solid var(--ncl-surface-hover);transition:background .1s;cursor:pointer}
.pl-tr:hover{background:#fafafa}
.pl-tr:last-child{border-bottom:none}
.pl-td{padding:10px 14px;font-size:.88rem;vertical-align:middle;color:var(--ncl-text-heading)}
.pl-td.dt{color:var(--ncl-text-muted);white-space:nowrap;width:130px;font-size:.82rem}
.pl-td.st{width:90px;font-size:.78rem;font-weight:600}
.pl-td.tt{font-weight:500}
.pl-td.tg{width:120px;font-size:.78rem;color:var(--ncl-text-muted)}
.pl-td.tp{width:80px;font-size:.78rem;color:var(--ncl-text-muted)}
.pl-td.hr{width:50px;text-align:center}
.st-upcoming{color:var(--ncl-accent-green)}
.st-ongoing{color:var(--ncl-primary)}
.st-past{color:var(--ncl-text-muted)}
.st-no-date{color:var(--ncl-border)}
.pl-hrdc-y{color:var(--ncl-accent-green);text-decoration:none;font-weight:600}
.pl-hrdc-y:hover{text-decoration:underline}

.pl-more{display:none;text-align:center;margin-top:14px}
.pl-more button{padding:8px 20px;border-radius:5px;background:var(--ncl-surface-hover);border:1px solid var(--ncl-border);cursor:pointer;font-size:.85rem;font-weight:500}
.pl-more button:hover{background:var(--ncl-border)}

@media(max-width:600px){
    .pl-tw{border:none;background:transparent;overflow:visible}
    .pl-t,.pl-t tbody,.pl-t tr,.pl-t td{display:block;width:100%;box-sizing:border-box}
    .pl-t thead{display:none}
    .pl-tr{border:1px solid var(--ncl-border);border-radius:6px;margin-bottom:10px;background:var(--ncl-bg);padding:12px}
    .pl-td{padding:2px 0;border:none!important}
    .pl-td.dt{width:auto;display:block;margin-bottom:2px}
    .pl-td.tt{font-size:1rem;margin-bottom:6px;display:block}
    .pl-td.st,.pl-td.tg,.pl-td.tp,.pl-td.hr{display:inline-block;width:auto;padding-right:8px}
}
</style>

<section class="pl-w">

    <h1 class="pl-h1"><?php echo esc_html($page_title); ?></h1>
    <?php if (!empty($desc)): ?><div class="pl-desc"><?php echo wp_kses_post($desc); ?></div><?php endif; ?>

    <!-- ── CAROUSEL ── -->
    <?php if (!empty($upcoming)): ?>
    <div class="pl-car" id="pl-car">
        <?php
        $si = 0;
        foreach ($upcoming as $act):
            $a_type  = get_post_meta($act->ID, $p . 'type', true) ?: 'program';
            $a_desc  = get_post_meta($act->ID, $p . 'desc', true);
            $a_start = get_post_meta($act->ID, $p . 'start_date', true);
            $a_end   = get_post_meta($act->ID, $p . 'end_date', true);
            $tags    = get_the_terms($act->ID, 'nucleus_activity_tag');
            $tag_str = '';
            if (!empty($tags) && !is_wp_error($tags)) {
                $tag_str = implode(', ', wp_list_pluck($tags, 'name'));
            }

            // Compact date
            $ds = '';
            if (!empty($a_start)) {
                $ds = gmdate('M j, Y', strtotime($a_start));
                if (!empty($a_end) && $a_end !== $a_start) {
                    // Same month: "Apr 15 – 17, 2026"
                    if (gmdate('Y-m', strtotime($a_start)) === gmdate('Y-m', strtotime($a_end))) {
                        $ds = gmdate('M j', strtotime($a_start)) . ' – ' . gmdate('j, Y', strtotime($a_end));
                    } else {
                        $ds = gmdate('M j', strtotime($a_start)) . ' – ' . gmdate('M j, Y', strtotime($a_end));
                    }
                }
            }

            // First image
            $bg = '';
            $mr = get_post_meta($act->ID, $p . 'media', true);
            if ($mr) { $ma = json_decode($mr, true); if (is_array($ma)) foreach ($ma as $m) { if ($m['type'] === 'image') { $bg = $m['url']; break; } } }
            $a_contact = get_post_meta($act->ID, $p . 'contact_url', true);
        ?>
        <div class="pl-sl <?php echo $si === 0 ? 'active' : ''; ?>">
            <div class="pl-sl-body">
                <div class="pl-sl-sup"><?php echo esc_html(nucleus_activity_type_label($a_type)); ?><?php if ($tag_str) echo ' · ' . esc_html($tag_str); ?></div>
                <h3 class="pl-sl-title"><?php echo esc_html($act->post_title); ?></h3>
                <?php if ($ds): ?><div class="pl-sl-date"><?php echo esc_html($ds); ?></div><?php endif; ?>
                <div class="pl-sl-text"><?php echo esc_html(wp_trim_words(wp_strip_all_tags($a_desc ?: ''), 24, '…')); ?></div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="<?php echo esc_url(get_permalink($act->ID)); ?>" class="pl-sl-btn">View Details →</a>
                    <?php if (!empty($a_contact)): ?>
                        <a href="<?php echo esc_url($a_contact); ?>" class="pl-sl-cta">Contact Us</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="pl-sl-img" style="background-image:url('<?php echo esc_url($bg); ?>');"></div>
        </div>
        <?php $si++; endforeach; ?>
        <?php if (count($upcoming) > 1): ?>
        <div class="pl-car-nav">
            <button class="pl-nav" onclick="plS(-1)">‹</button>
            <button class="pl-nav" onclick="plS(1)">›</button>
        </div>
        <?php endif; ?>
    </div>
    <script>
    (function(){var c=0,s=document.querySelectorAll('.pl-sl'),t;
    window.plS=function(d){s[c].classList.remove('active');s[c].style.display='none';c=(c+d+s.length)%s.length;s[c].classList.add('active');s[c].style.display='flex';clearInterval(t);t=setInterval(function(){plS(1);},6000);};
    if(s.length>1)t=setInterval(function(){plS(1);},6000);})();
    </script>
    <?php else: ?>
    <div class="pl-tune">
        <h3>Stay Tuned</h3>
        <p>New activities are being planned. Check back soon.</p>
    </div>
    <?php endif; ?>

    <!-- ── EXPLORER ── -->
    <?php if (!empty($all)): ?>
    <h2 class="pl-label">Activities Explorer</h2>
    <div class="pl-filters">
        <input type="text" id="plSrch" class="pl-fi" placeholder="Search…">
        <select id="plType" class="pl-fi"><option value="all">All Types</option><option value="program">Programme</option><option value="event">Event</option></select>
        <select id="plStat" class="pl-fi"><option value="all">All Status</option><option value="upcoming">Upcoming</option><option value="ongoing">Ongoing</option><option value="past">Past</option></select>
        <select id="plTag" class="pl-fi"><option value="all">All Tags</option>
            <?php $atags = get_terms(array('taxonomy' => 'nucleus_activity_tag', 'hide_empty' => false));
            if (!empty($atags) && !is_wp_error($atags)) foreach ($atags as $t): ?>
                <option value="<?php echo esc_attr(strtolower($t->name)); ?>"><?php echo esc_html($t->name); ?></option>
            <?php endforeach; ?>
        </select>
        <select id="plYr" class="pl-fi"><option value="all">Year</option></select>
    </div>

    <div class="pl-tw">
        <table class="pl-t">
            <thead><tr><th>Date</th><th>Status</th><th>Title</th><th>Tags</th><th>Type</th><th style="text-align:center">HRDC</th></tr></thead>
            <tbody id="plBody">
            <?php
            // Build + sort
            $rows = array();
            foreach ($all as $act) {
                $a_s = get_post_meta($act->ID, $p . 'start_date', true);
                $a_e = get_post_meta($act->ID, $p . 'end_date', true);
                $a_t = get_post_meta($act->ID, $p . 'type', true) ?: 'program';
                $a_h = get_post_meta($act->ID, $p . 'hrdc', true);
                $a_hu = get_post_meta($act->ID, $p . 'hrdc_url', true);
                $tms = get_the_terms($act->ID, 'nucleus_activity_tag');
                $st  = nucleus_get_activity_status($act->ID);
                $tn  = array();
                if (!empty($tms) && !is_wp_error($tms)) foreach ($tms as $t) $tn[] = $t->name;
                // Compact date
                $dd = '—';
                if (!empty($a_s)) {
                    $dd = gmdate('M j, Y', strtotime($a_s));
                    if (!empty($a_e) && $a_e !== $a_s) {
                        if (gmdate('Y-m', strtotime($a_s)) === gmdate('Y-m', strtotime($a_e)))
                            $dd = gmdate('M j', strtotime($a_s)) . ' – ' . gmdate('j, Y', strtotime($a_e));
                        else
                            $dd = gmdate('M j', strtotime($a_s)) . ' – ' . gmdate('M j, Y', strtotime($a_e));
                    }
                }
                $rows[] = array('p' => $act, 's' => $a_s, 'dd' => $dd, 'st' => $st, 'tp' => $a_t, 'tg' => $tn, 'h' => $a_h, 'hu' => $a_hu);
            }
            usort($rows, function($a, $b) {
                $o = array('upcoming' => 0, 'ongoing' => 1, 'no-date' => 2, 'past' => 3);
                $oa = isset($o[$a['st']]) ? $o[$a['st']] : 2;
                $ob = isset($o[$b['st']]) ? $o[$b['st']] : 2;
                if ($oa !== $ob) return $oa - $ob;
                return strtotime($b['s'] ?: '0') - strtotime($a['s'] ?: '0');
            });
            foreach ($rows as $r):
                $yr = !empty($r['s']) ? substr($r['s'], 0, 4) : '';
                $tl = implode(',', array_map('strtolower', $r['tg']));
            ?>
            <tr class="pl-tr" data-title="<?php echo esc_attr(strtolower($r['p']->post_title)); ?>" data-type="<?php echo esc_attr($r['tp']); ?>" data-status="<?php echo esc_attr($r['st']); ?>" data-tags="<?php echo esc_attr($tl); ?>" data-year="<?php echo esc_attr($yr); ?>" onclick="window.location='<?php echo esc_url(get_permalink($r['p']->ID)); ?>'">
                <td class="pl-td dt"><?php echo esc_html($r['dd']); ?></td>
                <td class="pl-td st"><span class="st-<?php echo esc_attr($r['st']); ?>"><?php echo esc_html(ucfirst(str_replace('-', ' ', $r['st']))); ?></span></td>
                <td class="pl-td tt"><?php echo esc_html($r['p']->post_title); ?></td>
                <td class="pl-td tg"><?php echo esc_html(implode(', ', $r['tg'])); ?></td>
                <td class="pl-td tp"><?php echo esc_html(nucleus_activity_type_label($r['tp'])); ?></td>
                <td class="pl-td hr">
                    <?php if ($r['h'] === '1'): ?>
                        <?php if (!empty($r['hu'])): ?><a href="<?php echo esc_url($r['hu']); ?>" class="pl-hrdc-y" target="_blank" rel="noopener" onclick="event.stopPropagation();">✓</a>
                        <?php else: ?><span class="pl-hrdc-y">✓</span><?php endif; ?>
                    <?php else: ?><span style="color:var(--ncl-border);">—</span><?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div id="plNR" style="display:none;padding:24px;text-align:center;color:var(--ncl-text-muted);font-size:.9rem;">No activities found.</div>
    </div>

    <div class="pl-more" id="plMore"><button id="plMoreBtn">Load More</button></div>

    <script>
    document.addEventListener('DOMContentLoaded',function(){
        var sr=document.getElementById('plSrch'),tf=document.getElementById('plType'),sf=document.getElementById('plStat'),
            gf=document.getElementById('plTag'),yf=document.getElementById('plYr'),
            rows=document.querySelectorAll('.pl-tr'),nr=document.getElementById('plNR'),
            mc=document.getElementById('plMore'),mb=document.getElementById('plMoreBtn'),lim=10;
        var yrs=new Set();rows.forEach(function(r){var y=r.dataset.year;if(y&&+y>1900&&+y<3000)yrs.add(y);});
        Array.from(yrs).sort().reverse().forEach(function(y){var o=document.createElement('option');o.value=y;o.textContent=y;yf.appendChild(o);});
        if(!yrs.size)yf.style.display='none';
        function filt(){
            var s=sr.value.toLowerCase(),t=tf.value,st=sf.value,g=gf.value,y=yf.value,matched=[];
            rows.forEach(function(r){
                var ok=r.dataset.title.includes(s)&&(t==='all'||r.dataset.type===t)&&(st==='all'||r.dataset.status===st)&&(g==='all'||r.dataset.tags.split(',').indexOf(g)!==-1)&&(y==='all'||r.dataset.year===y);
                if(ok)matched.push(r);else r.style.display='none';
            });
            matched.forEach(function(r,i){r.style.display=i<lim?'':'none';});
            nr.style.display=matched.length?'none':'block';
            mc.style.display=matched.length>lim?'block':'none';
        }
        [sr].forEach(function(e){e.addEventListener('input',function(){lim=10;filt();});});
        [tf,sf,gf,yf].forEach(function(e){e.addEventListener('change',function(){lim=10;filt();});});
        mb.addEventListener('click',function(){lim+=10;filt();});
        filt();
    });
    </script>
    <?php endif; ?>

</section>
</main>

<?php
if ($hf_set_id) {
    if (function_exists('nucleus_render_hf_set')) echo nucleus_render_hf_set($hf_set_id, 'footer');
    wp_footer(); echo '</body></html>';
} else { get_footer(); }
?>
