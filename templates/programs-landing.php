<?php
/**
 * Programs Landing Page / Archive Template
 * Renders the centralized Programs & Initiatives dashboard.
 */

if (!defined('ABSPATH')) {
    exit;
}

$desc = get_option('_nucleus_landing_desc', '');
$hf_set_id = get_option('_nucleus_landing_hf', '');
$highlighted_ids = get_option('_nucleus_landing_highlighted', array());
$page_title = get_option('_nucleus_landing_title', 'Programs & Initiatives');
if (empty($page_title)) $page_title = 'Programs & Initiatives';

// Fetch Highlighted
$highlighted_args = array(
    'post_type' => 'nucleus_program',
    'post__in' => !empty($highlighted_ids) ? $highlighted_ids : array(0), // prevent fetching everything if empty
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby' => 'post__in'
);
$highlighted_programs = !empty($highlighted_ids) ? get_posts($highlighted_args) : array();

// Fetch All Others (Past/Other Programs)
$all_others_args = array(
    'post_type' => 'nucleus_program',
    'post__not_in' => !empty($highlighted_ids) ? $highlighted_ids : array(),
    'numberposts' => -1,
    'post_status' => 'publish',
);
$other_programs = get_posts($all_others_args);

// Fetch All Past Events
$all_events_args = array(
    'post_type' => 'nucleus_event',
    'numberposts' => -1,
    'post_status' => 'publish',
);
$all_events = get_posts($all_events_args);

if ($hf_set_id) {
    ?><!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Programs & Initiatives - <?php bloginfo('name'); ?></title>
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

<main>

    <style>
/* Global Overflow Fix matching Page */
html { overflow-x: hidden; }
html, body { margin: 0 !important; padding: 0 !important; display: block !important; max-width: 100% !important; }
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

/* Landing Page Specific */
.p-container { max-width: 1200px; margin: 0 auto; padding: 60px 20px; font-family: 'Inter', -apple-system, sans-serif; }
.p-landing-desc { font-size: 1.15rem; line-height: 1.6; color: #475569; margin-bottom: 40px; max-width:800px; }
.section-title { font-size: 2.2rem; font-weight: 800; color: #0f172a; margin-bottom: 30px; letter-spacing:-0.03em; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;}

/* Carousel */
.p-carousel-wrap { position:relative; margin-bottom:80px; }
.p-carousel-inner { overflow:hidden; position:relative; border-radius:16px; background:#f8fafc; box-shadow:0 10px 25px -5px rgba(0,0,0,0.1); border:1px solid #e2e8f0; }
.p-slide { display:none; flex-direction:column; }
.p-slide.active { display:flex; animation: fadein 0.4s ease; }
@keyframes fadein { from { opacity:0; } to { opacity:1; } }

@media (min-width: 900px) {
    .p-slide { flex-direction:row; min-height: 480px; }
    .p-slide-content { flex:1.2; padding:60px 40px; display:flex; flex-direction:column; justify-content:center; }
    .p-slide-image { flex:1; background-size: cover; background-position: center; min-height: 480px; background-color:#e2e8f0; }
}
@media (max-width: 899px) {
    .p-slide-image { height: 250px; background-size: cover; background-position: center; background-color:#e2e8f0; }
    .p-slide-content { padding: 40px 20px; }
}

.p-slide-title { font-size:2.2rem; font-weight:800; color:#0f172a; margin-top:0; margin-bottom:16px; line-height:1.2; }
.p-slide-text { font-size:1.1rem; color:#475569; line-height:1.6; margin-bottom:32px; }
.view-btn { display:inline-flex; align-items:center; gap:8px; background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 1rem; transition: background 0.2s; align-self:flex-start;}
.view-btn:hover { background: #1d4ed8; color:#fff; }

.p-carousel-controls { position:absolute; bottom:20px; right:20px; display:flex; gap:10px; z-index:10; }
@media (max-width: 899px) {
    .p-carousel-controls { top: 20px; bottom: auto; right: 20px; }
}
.p-ctrl { background:#0f172a; color:#fff; border:none; width:44px; height:44px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
.p-ctrl:hover { background:#334155; }
.p-ctrl span { font-size: 24px; line-height:1; }

/* Minimal List */
.past-filters { display:flex; gap:16px; margin-bottom: 24px; flex-wrap:wrap; }
.past-input { flex:1; min-width:200px; padding:12px 16px; border:1px solid #cbd5e1; border-radius:6px; font-size:1rem; outline:none;}
.past-input:focus { border-color:#2563eb; }
.past-select { padding:12px 16px; border:1px solid #cbd5e1; border-radius:6px; font-size:1rem; background:#fff; outline:none;}

.past-table-wrap { width:100%; background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow-x:auto;}
.past-table { width:100%; border-collapse: collapse; text-align:left; min-width:600px;}
.past-table th { padding:16px; background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#64748b; font-size:0.9rem; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;}
.past-row { border-bottom:1px solid #e2e8f0; transition:background 0.2s; cursor:pointer;}
.past-row:hover { background:#f1f5f9; }
.past-row:last-child { border-bottom:none; }
.past-cell { padding:16px; color:#0f172a; font-size:1rem; }
.past-cell.date { font-weight:500; color:#64748b; white-space:nowrap; width:180px; }
.past-cell.title { font-weight:600; color:#2563eb; }
.past-cell.type { width:120px; }
.past-badge { display:inline-block; padding:4px 10px; font-size:0.7rem; font-weight:700; border-radius:99px; text-transform:uppercase; }
.past-badge.program { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; }
.past-badge.event { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }

@media (max-width: 600px) {
    .past-table-wrap { border: none; background: transparent; overflow: visible; }
    .past-table, .past-table tbody, .past-table tr, .past-table td { display: block; width: 100%; box-sizing: border-box; }
    .past-table thead { display: none; }
    .past-table tr { border-bottom: none; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 16px; background: #fff; padding: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .past-cell { padding: 0 0 12px 0; border: none !important; }
    .past-cell.date { font-size: 0.9rem; margin-bottom: 4px; padding: 0; width: auto; display: block; }
    .past-cell.title { font-size: 1.15rem; padding: 0; margin-bottom: 12px; display: block; }
    .past-cell.type { display: inline-block; padding: 0; width: auto; }
}
    </style>

    <section class="p-container">

        <h1 class="section-title" style="border:none; padding:0; margin-bottom:16px;"><?php echo esc_html($page_title); ?></h1>
        <?php if (!empty($desc)): ?>
            <div class="p-landing-desc">
                <?php echo wp_kses_post($desc); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($highlighted_programs)): ?>
            <div class="p-carousel-wrap">
                <div class="p-carousel-inner" id="n-prog-carousel">
                    <?php 
                    $slide_index = 0;
                    foreach ($highlighted_programs as $prog): 
                        // Try to fetch first image from media gallery
                        $program_media = get_post_meta($prog->ID, '_nucleus_program_media', true);
                        $bg_img = '';
                        if (!empty($program_media)) {
                            $media_arr = json_decode($program_media, true);
                            if (is_array($media_arr)) {
                                foreach ($media_arr as $m) {
                                    if ($m['type'] === 'image') {
                                        $bg_img = $m['url'];
                                        break;
                                    }
                                }
                            }
                        }
                    ?>
                        <div class="p-slide <?php echo $slide_index === 0 ? 'active' : ''; ?>">
                            <div class="p-slide-content">
                                <h3 class="p-slide-title"><?php echo esc_html($prog->post_title); ?></h3>
                                <div class="p-slide-text">
                                    <?php 
                                    $excerpt = get_post_meta($prog->ID, '_nucleus_program_desc', true);
                                    if(empty($excerpt)) $excerpt = 'Discover the impact and features of this highlighted program.';
                                    echo wp_trim_words(wp_strip_all_tags($excerpt), 30, '...');
                                    ?>
                                </div>
                                <?php
                                $h_date_type = get_post_meta($prog->ID, '_nucleus_program_date_type', true);
                                $h_start = get_post_meta($prog->ID, '_nucleus_program_start_date', true);
                                $h_end = get_post_meta($prog->ID, '_nucleus_program_end_date', true);
                                $h_hide = get_post_meta($prog->ID, '_nucleus_program_hide_date', true);

                                $h_date_str = '';
                                if ($h_hide !== '1') {
                                    if ($h_date_type === 'soon') $h_date_str = 'Starting Soon';
                                    elseif ($h_date_type === 'ongoing') $h_date_str = !empty($h_start) ? 'Ongoing since ' . gmdate('Y', strtotime($h_start)) : 'Ongoing';
                                    elseif (!empty($h_start)) {
                                        $h_date_str = gmdate('F j, Y', strtotime($h_start));
                                        if(!empty($h_end)) $h_date_str .= ' - ' . gmdate('F j, Y', strtotime($h_end));
                                    }
                                }
                                ?>
                                <?php if (!empty($h_date_str)): ?>
                                    <div style="font-size:0.8rem; font-weight:700; color:#cbd5e1; text-transform:uppercase; letter-spacing:1px; margin-bottom:16px; display: flex; align-items: center; gap: 8px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        <?php echo esc_html($h_date_str); ?>
                                    </div>
                                <?php endif; ?>
                                <a href="<?php echo esc_url(get_permalink($prog->ID)); ?>" class="view-btn">View Program <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
                            </div>
                            <div class="p-slide-image" style="background-image: url('<?php echo esc_url($bg_img); ?>');">
                            </div>
                        </div>
                    <?php 
                        $slide_index++;
                    endforeach; 
                    ?>
                </div>

                <?php if(count($highlighted_programs) > 1): ?>
                <div class="p-carousel-controls">
                    <button class="p-ctrl" onclick="moveSlide(-1)">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    </button>
                    <button class="p-ctrl" onclick="moveSlide(1)">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <script>
                let currentSlide = 0;
                const slides = document.querySelectorAll('.p-slide');
                let autoSlideInterval;
                
                function moveSlide(direction) {
                    slides[currentSlide].classList.remove('active');
                    currentSlide = (currentSlide + direction + slides.length) % slides.length;
                    slides[currentSlide].classList.add('active');
                    resetInterval();
                }

                function resetInterval() {
                    clearInterval(autoSlideInterval);
                    autoSlideInterval = setInterval(() => { moveSlide(1) }, 6000);
                }
                
                if (slides.length > 1) {
                    resetInterval();
                }
            </script>
        <?php endif; ?>

        <?php if (!empty($other_programs) || !empty($all_events)): ?>
            <h2 class="section-title">Activities Explorer</h2>
            
            <div class="past-filters">
                <input type="text" id="pastSearch" class="past-input" placeholder="Search for programs or events...">
                <select id="pastFilter" class="past-select">
                    <option value="all">All Activities</option>
                    <option value="program">Programs Only</option>
                    <option value="event">Events Only</option>
                </select>
                <select id="statusFilter" class="past-select">
                    <option value="all">All Statuses</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="soon">Starting Soon</option>
                    <option value="past">Past</option>
                </select>
                <select id="yearFilter" class="past-select">
                    <option value="all">All Years</option>
                </select>
            </div>

            <div class="past-table-wrap">
                <table class="past-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity Title</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody id="pastListTbody">
                        <?php 
                        $all_activities = array();
                        
                        foreach ($other_programs as $oprog) {
                            $date_type = get_post_meta($oprog->ID, '_nucleus_program_date_type', true);
                            $start_date = get_post_meta($oprog->ID, '_nucleus_program_start_date', true);
                            $end_date = get_post_meta($oprog->ID, '_nucleus_program_end_date', true);
                            $hide_date = get_post_meta($oprog->ID, '_nucleus_program_hide_date', true);
                            
                            $date_string = 'Past Initiative';
                            $sort_date = '';
                            $status = 'past';
                            if ($hide_date !== '1') {
                                if ($date_type === 'soon') { $date_string = 'Starting Soon'; $sort_date = '9999-12-31'; $status = 'soon'; }
                                elseif ($date_type === 'ongoing') { $date_string = !empty($start_date) ? 'Ongoing since ' . gmdate('Y', strtotime($start_date)) : 'Ongoing'; $sort_date = '9998-12-31'; $status = 'ongoing'; }
                                elseif (!empty($start_date)) {
                                    $date_string = gmdate('F Y', strtotime($start_date));
                                    $sort_date = $start_date;
                                    if(!empty($end_date)) {
                                        $date_string .= ' - ' . gmdate('F Y', strtotime($end_date));
                                        if (strtotime($end_date) >= time()) $status = 'ongoing';
                                    } else {
                                        if (strtotime($start_date) >= time()) $status = 'soon';
                                    }
                                }
                            }
                            $all_activities[] = array('post' => $oprog, 'type' => 'program', 'date' => $date_string, 'sort' => $sort_date, 'status' => $status);
                        }

                        foreach ($all_events as $event) {
                            $date_type = get_post_meta($event->ID, '_nucleus_event_date_type', true);
                            $start_date = get_post_meta($event->ID, '_nucleus_event_start_date', true);
                            $end_date = get_post_meta($event->ID, '_nucleus_event_end_date', true);
                            $hide_date = get_post_meta($event->ID, '_nucleus_event_hide_date', true);

                            $date_string = 'Past Event';
                            $sort_date = '';
                            $status = 'past';
                            if ($hide_date !== '1') {
                                if ($date_type === 'soon') { $date_string = 'Starting Soon'; $sort_date = '9999-12-31'; $status = 'soon'; }
                                elseif ($date_type === 'ongoing') { $date_string = !empty($start_date) ? 'Ongoing since ' . gmdate('Y', strtotime($start_date)) : 'Ongoing'; $sort_date = '9998-12-31'; $status = 'ongoing'; }
                                elseif (!empty($start_date)) {
                                    $date_string = gmdate('F Y', strtotime($start_date));
                                    $sort_date = $start_date;
                                    if(!empty($end_date)) {
                                        $date_string .= ' - ' . gmdate('F Y', strtotime($end_date));
                                        if (strtotime($end_date) >= time()) $status = 'ongoing';
                                    } else {
                                        if (strtotime($start_date) >= time()) $status = 'soon';
                                    }
                                }
                            }
                            $all_activities[] = array('post' => $event, 'type' => 'event', 'date' => $date_string, 'sort' => $sort_date, 'status' => $status);
                        }

                        // Sort newest to oldest
                        usort($all_activities, function($a, $b) {
                            return strtotime($b['sort'] ?: '0') - strtotime($a['sort'] ?: '0');
                        });

                        foreach($all_activities as $act):
                        ?>
                            <tr class="past-row" data-year="<?php echo esc_attr($act['sort'] ? substr($act['sort'], 0, 4) : ''); ?>" data-type="<?php echo esc_attr($act['type']); ?>" data-status="<?php echo esc_attr($act['status']); ?>" data-title="<?php echo esc_attr(strtolower($act['post']->post_title)); ?>" onclick="window.location='<?php echo esc_url(get_permalink($act['post']->ID)); ?>'">
                                <td class="past-cell date"><?php echo esc_html($act['date']); ?></td>
                                <td class="past-cell title"><?php echo esc_html($act['post']->post_title); ?></td>
                                <td class="past-cell type">
                                    <span class="past-badge <?php echo esc_attr($act['type']); ?>"><?php echo esc_html($act['type']); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="noResults" style="display:none; padding:32px; text-align:center; color:#64748b;">No activities match your search.</div>
            </div>
            
            <div id="loadMoreContainer" style="display:none; text-align:center; margin-top: 24px;">
                <button id="loadMoreBtn" style="padding:12px 24px; border-radius:6px; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; font-weight:600; transition:all 0.2s;">Load More Activities <span class="dashicons dashicons-arrow-down-alt2"></span></button>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const searchBox = document.getElementById('pastSearch');
                    const filterMenu = document.getElementById('pastFilter');
                    const statusMenu = document.getElementById('statusFilter');
                    const yearMenu = document.getElementById('yearFilter');
                    const rows = document.querySelectorAll('.past-row');
                    const noRes = document.getElementById('noResults');
                    const loadMoreContainer = document.getElementById('loadMoreContainer');
                    const loadMoreBtn = document.getElementById('loadMoreBtn');
                    
                    let limit = 10;
                    
                    let uniqueYears = new Set();
                    rows.forEach(row => {
                        let yr = row.getAttribute('data-year');
                        if (yr && parseInt(yr) > 1900 && parseInt(yr) < 3000) {
                            uniqueYears.add(yr);
                        }
                    });
                    let sortedYears = Array.from(uniqueYears).sort().reverse();
                    if (sortedYears.length > 0) {
                        sortedYears.forEach(y => {
                            let opt = document.createElement('option');
                            opt.value = y;
                            opt.textContent = y;
                            yearMenu.appendChild(opt);
                        });
                    } else {
                        yearMenu.style.display = 'none';
                    }

                    function filterTable() {
                        const searchTerm = searchBox.value.toLowerCase();
                        const filterType = filterMenu.value;
                        const statusType = statusMenu.value;
                        const yearType = yearMenu.value;
                        let visibleCount = 0;
                        let matchedRows = [];

                        rows.forEach(row => {
                            const title = row.getAttribute('data-title');
                            const type = row.getAttribute('data-type');
                            const status = row.getAttribute('data-status');
                            const year = row.getAttribute('data-year');
                            
                            const matchesSearch = title.includes(searchTerm);
                            const matchesFilter = (filterType === 'all' || filterType === type);
                            const matchesStatus = (statusType === 'all' || statusType === status);
                            const matchesYear = (yearType === 'all' || yearType === year);

                            if(matchesSearch && matchesFilter && matchesStatus && matchesYear) {
                                matchedRows.push(row);
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        matchedRows.forEach((row, index) => {
                            if (index < limit) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        noRes.style.display = (matchedRows.length === 0) ? 'block' : 'none';
                        loadMoreContainer.style.display = (matchedRows.length > limit) ? 'block' : 'none';
                    }

                    searchBox.addEventListener('input', () => { limit = 10; filterTable(); });
                    filterMenu.addEventListener('change', () => { limit = 10; filterTable(); });
                    statusMenu.addEventListener('change', () => { limit = 10; filterTable(); });
                    yearMenu.addEventListener('change', () => { limit = 10; filterTable(); });
                    
                    loadMoreBtn.addEventListener('click', () => {
                        limit += 10;
                        filterTable();
                    });
                    
                    filterTable();
                });
            </script>
        <?php endif; ?>
    </section>

</main>

<?php
if ($hf_set_id) {
    if (function_exists('nucleus_render_hf_set')) { echo nucleus_render_hf_set($hf_set_id, 'footer'); }
    wp_footer();
    echo '</body></html>';
} else {
    get_footer();
}
?>
