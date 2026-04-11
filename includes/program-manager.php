<?php
/**
 * Program Manager
 *
 * Unified Activity CPT (Programs & Events) with inline tag management,
 * comprehensive date/time/location, and compact admin UI.
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ================================================================
   1. REGISTER CPT + TAXONOMY
   ================================================================ */

function nucleus_register_activity_cpt()
{
    register_post_type('nucleus_program', array(
        'labels' => array(
            'name'           => 'Activities',
            'singular_name'  => 'Activity',
            'menu_name'      => 'Program Manager',
            'name_admin_bar' => 'Activity',
            'add_new'        => 'Add New',
            'add_new_item'   => 'Add New Activity',
            'new_item'       => 'New Activity',
            'edit_item'      => 'Edit Activity',
            'view_item'      => 'View Activity',
            'all_items'      => 'All Activities',
            'search_items'   => 'Search Activities',
            'not_found'      => 'No activities found.',
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'programs'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 8,
        'menu_icon'          => 'dashicons-networking',
        'supports'           => array('title'),
    ));

    // Flat taxonomy (no parent categories)
    register_taxonomy('nucleus_activity_tag', array('nucleus_program'), array(
        'hierarchical'       => false,
        'labels'             => array(
            'name'          => 'Activity Tags',
            'singular_name' => 'Tag',
            'add_new_item'  => 'Add New Tag',
        ),
        'show_ui'            => false,   // Hidden from admin menu
        'show_in_menu'       => false,
        'show_admin_column'  => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'activity-tag'),
    ));

    // Seed defaults once
    if (get_option('_nucleus_tags_seeded') !== '1') {
        foreach (array('Workshop', 'Gathering', 'Seminar', 'Conference', 'Webinar', 'Training') as $tag) {
            if (!term_exists($tag, 'nucleus_activity_tag')) wp_insert_term($tag, 'nucleus_activity_tag');
        }
        update_option('_nucleus_tags_seeded', '1');
    }
}
add_action('init', 'nucleus_register_activity_cpt');


/* ================================================================
   2. META BOX
   ================================================================ */

function nucleus_activity_meta_boxes()
{
    add_meta_box('nucleus_activity_details', 'Activity Details', 'nucleus_activity_meta_box_html', 'nucleus_program', 'normal', 'high');
}
add_action('add_meta_boxes', 'nucleus_activity_meta_boxes');


/* ================================================================
   3. LANDING PAGE SETTINGS
   ================================================================ */

function nucleus_activity_landing_menu()
{
    add_submenu_page('edit.php?post_type=nucleus_program', 'Landing Page', 'Landing Page', 'manage_options', 'nucleus_program_landing', 'nucleus_activity_landing_html');
}
add_action('admin_menu', 'nucleus_activity_landing_menu');

function nucleus_register_activity_settings()
{
    register_setting('nucleus_program_options', '_nucleus_landing_title');
    register_setting('nucleus_program_options', '_nucleus_landing_desc');
    register_setting('nucleus_program_options', '_nucleus_landing_hf');
}
add_action('admin_init', 'nucleus_register_activity_settings');

function nucleus_activity_landing_html()
{
    if (!current_user_can('manage_options')) return;
    $title     = get_option('_nucleus_landing_title', 'Programs & Initiatives');
    $desc      = get_option('_nucleus_landing_desc', '');
    $hf_set_id = get_option('_nucleus_landing_hf', '');
    $hf_sets   = get_posts(array('post_type' => 'nucleus_hf_set', 'numberposts' => -1, 'post_status' => 'any'));
    ?>
    <div class="wrap">
        <h1>Landing Page Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('nucleus_program_options'); ?>
            <table class="form-table">
                <tr><th>Header &amp; Footer</th><td>
                    <select name="_nucleus_landing_hf" style="max-width:300px;">
                        <option value="">-- Theme Default --</option>
                        <?php foreach ($hf_sets as $s): ?><option value="<?php echo esc_attr($s->ID); ?>" <?php selected($hf_set_id, $s->ID); ?>><?php echo esc_html($s->post_title); ?></option><?php endforeach; ?>
                    </select>
                </td></tr>
                <tr><th>Page Title</th><td><input type="text" name="_nucleus_landing_title" class="regular-text" value="<?php echo esc_attr($title); ?>"></td></tr>
                <tr><th>Hero Description</th><td><?php wp_editor($desc, '_nucleus_landing_desc', array('textarea_name' => '_nucleus_landing_desc', 'media_buttons' => false, 'textarea_rows' => 5, 'teeny' => true)); ?></td></tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


/* ================================================================
   4. META BOX HTML — Single compact section
   ================================================================ */

function nucleus_activity_meta_box_html($post)
{
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    $p = '_nucleus_activity_';

    // Load meta with backward-compat fallback
    $type       = get_post_meta($post->ID, $p . 'type', true) ?: 'program';
    $desc       = get_post_meta($post->ID, $p . 'desc', true);
    if (empty($desc)) $desc = get_post_meta($post->ID, '_nucleus_program_desc', true);
    if (empty($desc)) $desc = get_post_meta($post->ID, '_nucleus_event_desc', true);

    $start_date = get_post_meta($post->ID, $p . 'start_date', true) ?: get_post_meta($post->ID, '_nucleus_program_start_date', true);
    $end_date   = get_post_meta($post->ID, $p . 'end_date', true) ?: get_post_meta($post->ID, '_nucleus_program_end_date', true);
    $start_time = get_post_meta($post->ID, $p . 'start_time', true);
    $end_time   = get_post_meta($post->ID, $p . 'end_time', true);
    $location   = get_post_meta($post->ID, $p . 'location', true);

    $show_date     = get_post_meta($post->ID, $p . 'show_date', true);
    $show_time     = get_post_meta($post->ID, $p . 'show_time', true);
    $show_location = get_post_meta($post->ID, $p . 'show_location', true);
    if ($show_date === '')     $show_date     = '1';
    if ($show_time === '')     $show_time     = '1';
    if ($show_location === '') $show_location = '1';

    $hrdc        = get_post_meta($post->ID, $p . 'hrdc', true);
    $hrdc_url    = get_post_meta($post->ID, $p . 'hrdc_url', true);
    $contact_url = get_post_meta($post->ID, $p . 'contact_url', true);

    $media_json = get_post_meta($post->ID, $p . 'media', true);
    if (empty($media_json)) $media_json = get_post_meta($post->ID, '_nucleus_program_media', true);
    if (empty($media_json)) $media_json = '[]';

    $hf_set_id = get_post_meta($post->ID, '_nucleus_selected_hf_set', true);
    $hf_sets   = get_posts(array('post_type' => 'nucleus_hf_set', 'numberposts' => -1, 'post_status' => 'any'));

    // Tags
    $all_tags      = get_terms(array('taxonomy' => 'nucleus_activity_tag', 'hide_empty' => false));
    $assigned_tags = wp_get_post_terms($post->ID, 'nucleus_activity_tag', array('fields' => 'ids'));

    wp_nonce_field('nucleus_activity_meta_box_nonce', 'nucleus_activity_nonce');
    ?>
<style>
.nm{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif}
.nm-row{display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap;align-items:flex-start}
.nm-lbl{display:block;font-weight:600;font-size:12px;margin-bottom:4px;color:#1d2327;text-transform:uppercase;letter-spacing:.03em}
.nm-input{padding:7px 10px;border:1px solid #8c8f94;border-radius:4px;box-sizing:border-box;font-size:13px}
.nm-sep{border:none;border-top:1px solid #e2e4e7;margin:20px 0}
.nm-note{color:#646970;font-size:11px;margin-top:3px}
.nm-chk{display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#50575e;margin-right:12px;white-space:nowrap}
/* Tags */
.nm-tags-wrap{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
.nm-tag{display:inline-flex;align-items:center;gap:3px;padding:3px 10px;background:#f0f0f1;border:1px solid #c3c4c7;border-radius:3px;font-size:12px;cursor:pointer;transition:all .15s;position:relative}
.nm-tag.selected{background:#2563eb;color:#fff;border-color:#2563eb}
.nm-tag-del{display:inline-flex;align-items:center;justify-content:center;width:14px;height:14px;border-radius:50%;background:rgba(0,0,0,.15);color:#666;font-size:10px;line-height:1;cursor:pointer;margin-left:2px;border:none;padding:0;transition:background .15s}
.nm-tag.selected .nm-tag-del{background:rgba(255,255,255,.3);color:#fff}
.nm-tag-del:hover{background:#d63638;color:#fff}
.nm-tag-add{display:inline-flex;gap:4px;align-items:center}
.nm-tag-add input{width:110px;padding:4px 8px;font-size:12px;border:1px solid #c3c4c7;border-radius:3px}
.nm-tag-add button{padding:4px 10px;font-size:11px;cursor:pointer;background:#f0f0f1;border:1px solid #c3c4c7;border-radius:3px}
.nm-tag-add button:hover{background:#e2e4e7}
/* Gallery */
.nm-gal{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px;min-height:60px;padding:10px;border:1px dashed #c3c4c7;border-radius:6px;background:#fafafa;align-items:center}
.nm-gi{position:relative;width:90px;height:90px;border-radius:6px;border:1px solid #dcdde1;overflow:hidden;background:#f0f0f1;cursor:move}
.nm-gi img,.nm-gi video{width:100%;height:100%;object-fit:cover}
.nm-gi-x{position:absolute;top:2px;right:2px;background:#d63638;color:#fff;border:none;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:11px;opacity:0;transition:opacity .15s}
.nm-gi:hover .nm-gi-x{opacity:1}
</style>

<div class="nm">

    <!-- Type + Tags + HF -->
    <div class="nm-row">
        <div>
            <label class="nm-lbl">Type</label>
            <select name="<?php echo esc_attr($p); ?>type" class="nm-input" style="width:140px;">
                <option value="program" <?php selected($type, 'program'); ?>>Program</option>
                <option value="event" <?php selected($type, 'event'); ?>>Event</option>
            </select>
        </div>
        <div>
            <label class="nm-lbl">Header &amp; Footer</label>
            <select name="_nucleus_selected_hf_set" class="nm-input" style="width:200px;">
                <option value="">-- Default --</option>
                <?php foreach ($hf_sets as $s): ?><option value="<?php echo esc_attr($s->ID); ?>" <?php selected($hf_set_id, $s->ID); ?>><?php echo esc_html($s->post_title); ?></option><?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="nm-row">
        <div style="flex:1;">
            <label class="nm-lbl">Tags</label>
            <div class="nm-tags-wrap">
                <?php if (!empty($all_tags) && !is_wp_error($all_tags)):
                    foreach ($all_tags as $t):
                        $sel = in_array($t->term_id, $assigned_tags); ?>
                        <label class="nm-tag <?php echo $sel ? 'selected' : ''; ?>" data-term-id="<?php echo esc_attr($t->term_id); ?>">
                            <input type="checkbox" name="nucleus_tags[]" value="<?php echo esc_attr($t->term_id); ?>" <?php checked($sel); ?> style="display:none;" onchange="this.parentElement.classList.toggle('selected',this.checked);">
                            <?php echo esc_html($t->name); ?>
                            <button type="button" class="nm-tag-del" onclick="nmDelTag(event,<?php echo esc_attr($t->term_id); ?>)" title="Delete tag">×</button>
                        </label>
                    <?php endforeach;
                endif; ?>
                <span class="nm-tag-add">
                    <input type="text" id="nm-new-tag" placeholder="New tag…">
                    <button type="button" onclick="nmAddTag()">+</button>
                </span>
            </div>
        </div>
    </div>

    <hr class="nm-sep">

    <!-- Date, Time, Location -->
    <div class="nm-row">
        <div>
            <label class="nm-lbl">Start Date</label>
            <input type="date" name="<?php echo esc_attr($p); ?>start_date" class="nm-input" value="<?php echo esc_attr($start_date); ?>" style="width:155px;">
        </div>
        <div>
            <label class="nm-lbl">End Date</label>
            <input type="date" name="<?php echo esc_attr($p); ?>end_date" class="nm-input" value="<?php echo esc_attr($end_date); ?>" style="width:155px;">
        </div>
        <div>
            <label class="nm-lbl">Start Time</label>
            <input type="time" name="<?php echo esc_attr($p); ?>start_time" class="nm-input" value="<?php echo esc_attr($start_time); ?>" style="width:130px;">
        </div>
        <div>
            <label class="nm-lbl">End Time</label>
            <input type="time" name="<?php echo esc_attr($p); ?>end_time" class="nm-input" value="<?php echo esc_attr($end_time); ?>" style="width:130px;">
        </div>
    </div>
    <div class="nm-row">
        <div style="flex:1;max-width:500px;">
            <label class="nm-lbl">Location</label>
            <input type="text" name="<?php echo esc_attr($p); ?>location" class="nm-input" value="<?php echo esc_attr($location); ?>" style="width:100%;" placeholder="e.g. Kuala Lumpur Convention Centre">
        </div>
    </div>
    <div class="nm-row" style="margin-bottom:4px;">
        <label class="nm-chk"><input type="checkbox" name="<?php echo esc_attr($p); ?>show_date" value="1" <?php checked($show_date, '1'); ?>> Show date</label>
        <label class="nm-chk"><input type="checkbox" name="<?php echo esc_attr($p); ?>show_time" value="1" <?php checked($show_time, '1'); ?>> Show time</label>
        <label class="nm-chk"><input type="checkbox" name="<?php echo esc_attr($p); ?>show_location" value="1" <?php checked($show_location, '1'); ?>> Show location</label>
        <span class="nm-note" style="margin-top:0;">— on public page</span>
    </div>

    <hr class="nm-sep">

    <!-- Description -->
    <div style="margin-bottom:16px;">
        <label class="nm-lbl">Description</label>
        <?php wp_editor($desc, $p . 'desc', array('textarea_name' => $p . 'desc', 'media_buttons' => false, 'textarea_rows' => 8, 'teeny' => true)); ?>
    </div>

    <hr class="nm-sep">

    <!-- HRDC & Contact -->
    <div class="nm-row">
        <div style="max-width:380px;">
            <label class="nm-chk" style="margin-bottom:6px;">
                <input type="checkbox" name="<?php echo esc_attr($p); ?>hrdc" value="1" <?php checked($hrdc, '1'); ?> id="nm-hrdc-chk" onchange="document.getElementById('nm-hrdc-url').style.display=this.checked?'block':'none';">
                HRDC Claimable
            </label>
            <div id="nm-hrdc-url" style="display:<?php echo ($hrdc === '1') ? 'block' : 'none'; ?>;margin-top:4px;">
                <input type="url" name="<?php echo esc_attr($p); ?>hrdc_url" class="nm-input" value="<?php echo esc_attr($hrdc_url); ?>" placeholder="https://..." style="width:100%;">
                <span class="nm-note">Link to HRDC document or page.</span>
            </div>
        </div>
        <div style="max-width:380px;">
            <label class="nm-lbl">Contact CTA URL</label>
            <input type="url" name="<?php echo esc_attr($p); ?>contact_url" class="nm-input" value="<?php echo esc_attr($contact_url); ?>" placeholder="https://your-site.com/contact" style="width:100%;">
            <span class="nm-note">Shown on upcoming activities as a CTA.</span>
        </div>
    </div>

    <hr class="nm-sep">

    <!-- Media -->
    <div>
        <label class="nm-lbl">Media Gallery</label>
        <div style="display:flex;gap:8px;margin:6px 0;">
            <button type="button" class="button button-small" id="nm-add-img">+ Images</button>
            <button type="button" class="button button-small" id="nm-add-vid">+ Videos</button>
        </div>
        <input type="hidden" name="<?php echo esc_attr($p); ?>media" id="nm_media" value="<?php echo esc_attr($media_json); ?>">
        <div class="nm-gal" id="nm-img-gal"></div>
        <div class="nm-gal" id="nm-vid-gal" style="margin-top:8px;"></div>
    </div>

</div>

<script>
// Tag add
function nmAddTag(){
    var inp=document.getElementById('nm-new-tag'),v=inp.value.trim();
    if(!v)return;
    // Create hidden input for new tag name
    var h=document.createElement('input');h.type='hidden';h.name='nucleus_new_tags[]';h.value=v;
    var lbl=document.createElement('label');lbl.className='nm-tag selected';
    var cb=document.createElement('input');cb.type='checkbox';cb.name='nucleus_new_tags[]';cb.value=v;cb.checked=true;cb.style.display='none';
    cb.onchange=function(){lbl.classList.toggle('selected',cb.checked);};
    lbl.appendChild(cb);lbl.appendChild(document.createTextNode(v));
    var wrap=document.querySelector('.nm-tags-wrap');
    wrap.insertBefore(lbl,wrap.querySelector('.nm-tag-add'));
    inp.value='';
}
document.getElementById('nm-new-tag').addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();nmAddTag();}});

// Tag delete
function nmDelTag(e,tid){
    e.preventDefault();e.stopPropagation();
    if(!confirm('Delete this tag permanently?'))return;
    jQuery.post(ajaxurl,{action:'nucleus_delete_tag',term_id:tid},function(r){
        if(r.success){var el=document.querySelector('.nm-tag[data-term-id="'+tid+'"]');if(el)el.remove();}
        else{alert('Could not delete tag.');}
    });
}

// Gallery
jQuery(document).ready(function($){
    var mIn=$('#nm_media'),iC=$('#nm-img-gal'),vC=$('#nm-vid-gal'),mD=[];
    try{var r=mIn.val();if(r)mD=JSON.parse(r);}catch(e){mD=[];}
    function render(){
        iC.empty();vC.empty();var hI=false,hV=false;
        mD.forEach(function(it,ix){
            var el,isV=(it.type==='video');
            if(isV){el='<video src="'+it.url+'" style="pointer-events:none;"></video>';hV=true;}
            else{el='<img src="'+it.url+'">';hI=true;}
            var w=$('<div class="nm-gi" data-id="'+it.id+'" data-url="'+it.url+'" data-type="'+it.type+'"></div>')
                .append(el).append('<button type="button" class="nm-gi-x" data-ix="'+ix+'">&times;</button>');
            if(isV)vC.append(w);else iC.append(w);
        });
        if(!hI)iC.append('<span style="color:#8c8f94;font-size:12px;">No images.</span>');
        if(!hV)vC.append('<span style="color:#8c8f94;font-size:12px;">No videos.</span>');
        mIn.val(JSON.stringify(mD));
        if($.fn.sortable){iC.sortable({items:'.nm-gi',update:sync});vC.sortable({items:'.nm-gi',update:sync});}
    }
    function sync(){
        var n=[];
        $('.nm-gi',iC).each(function(){n.push({id:$(this).data('id'),url:$(this).data('url'),type:$(this).data('type')});});
        $('.nm-gi',vC).each(function(){n.push({id:$(this).data('id'),url:$(this).data('url'),type:$(this).data('type')});});
        mD=n;mIn.val(JSON.stringify(mD));render();
    }
    render();
    $('.nm-gal').on('click','.nm-gi-x',function(){mD.splice($(this).data('ix'),1);render();});
    function openUp(mt,t){
        var fr=wp.media({title:t,button:{text:'Add'},multiple:true,library:{type:mt}});
        fr.on('select',function(){fr.state().get('selection').map(function(a){a=a.toJSON();mD.push({id:a.id,url:a.url,type:a.type});});render();});
        fr.open();
    }
    $('#nm-add-img').on('click',function(e){e.preventDefault();openUp('image','Select Images');});
    $('#nm-add-vid').on('click',function(e){e.preventDefault();openUp('video','Select Videos');});
});
</script>
    <?php
}


/* ================================================================
   5. SAVE META
   ================================================================ */

function nucleus_save_activity_meta($post_id)
{
    if (!isset($_POST['nucleus_activity_nonce']) || !wp_verify_nonce($_POST['nucleus_activity_nonce'], 'nucleus_activity_meta_box_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $p = '_nucleus_activity_';

    // Type
    if (isset($_POST[$p . 'type'])) update_post_meta($post_id, $p . 'type', sanitize_text_field($_POST[$p . 'type']));

    // HF Set
    if (isset($_POST['_nucleus_selected_hf_set'])) {
        update_post_meta($post_id, '_nucleus_selected_hf_set', sanitize_text_field($_POST['_nucleus_selected_hf_set']));
        update_post_meta($post_id, 'ct_other_template', '0');
    }

    // Description
    if (isset($_POST[$p . 'desc'])) update_post_meta($post_id, $p . 'desc', wp_kses_post(wp_unslash($_POST[$p . 'desc'])));

    // Text fields
    foreach (array('start_date', 'end_date', 'start_time', 'end_time', 'location', 'contact_url', 'hrdc_url') as $f) {
        if (isset($_POST[$p . $f])) update_post_meta($post_id, $p . $f, sanitize_text_field($_POST[$p . $f]));
    }

    // Toggles
    foreach (array('show_date', 'show_time', 'show_location') as $t) {
        update_post_meta($post_id, $p . $t, isset($_POST[$p . $t]) ? '1' : '0');
    }

    // HRDC
    update_post_meta($post_id, $p . 'hrdc', isset($_POST[$p . 'hrdc']) ? '1' : '0');

    // Tags — existing term IDs
    $tag_ids = array();
    if (isset($_POST['nucleus_tags']) && is_array($_POST['nucleus_tags'])) {
        $tag_ids = array_map('absint', $_POST['nucleus_tags']);
    }
    // New tags
    if (isset($_POST['nucleus_new_tags']) && is_array($_POST['nucleus_new_tags'])) {
        foreach ($_POST['nucleus_new_tags'] as $new_tag) {
            $new_tag = sanitize_text_field($new_tag);
            if (empty($new_tag)) continue;
            $existing = term_exists($new_tag, 'nucleus_activity_tag');
            if ($existing) {
                $tag_ids[] = (int) $existing['term_id'];
            } else {
                $inserted = wp_insert_term($new_tag, 'nucleus_activity_tag');
                if (!is_wp_error($inserted)) $tag_ids[] = (int) $inserted['term_id'];
            }
        }
    }
    wp_set_post_terms($post_id, array_unique($tag_ids), 'nucleus_activity_tag');

    // Media
    if (isset($_POST[$p . 'media'])) {
        $raw = wp_unslash($_POST[$p . 'media']);
        $arr = json_decode($raw, true);
        if (is_array($arr)) {
            $clean = array();
            foreach ($arr as $m) {
                if (isset($m['url'], $m['type'])) {
                    $clean[] = array('id' => isset($m['id']) ? absint($m['id']) : 0, 'url' => esc_url_raw($m['url']), 'type' => sanitize_text_field($m['type']));
                }
            }
            update_post_meta($post_id, $p . 'media', wp_json_encode($clean));
        } else {
            update_post_meta($post_id, $p . 'media', '[]');
        }
    }
}
add_action('save_post_nucleus_program', 'nucleus_save_activity_meta');


/* ================================================================
   6. TEMPLATE ROUTING
   ================================================================ */

add_filter('template_include', function ($template) {
    if (is_post_type_archive('nucleus_program')) {
        $c = plugin_dir_path(dirname(__FILE__)) . 'templates/programs-landing.php';
        if (file_exists($c)) return $c;
    }
    if (is_singular('nucleus_program')) {
        $hf = get_post_meta(get_the_ID(), '_nucleus_selected_hf_set', true);
        if (!empty($hf)) {
            $c = plugin_dir_path(dirname(__FILE__)) . 'templates/single-nucleus_program.php';
            if (file_exists($c)) return $c;
        }
    }
    return $template;
}, 99999);


/* ================================================================
   7. ADMIN COLUMNS
   ================================================================ */

add_filter('manage_nucleus_program_posts_columns', function ($cols) {
    $new = array();
    foreach ($cols as $k => $v) {
        $new[$k] = $v;
        if ($k === 'title') {
            $new['activity_type']   = 'Type';
            $new['activity_date']   = 'Date';
            $new['activity_status'] = 'Status';
            $new['activity_hrdc']   = 'HRDC';
        }
    }
    unset($new['date']);
    return $new;
});

add_action('manage_nucleus_program_posts_custom_column', function ($col, $pid) {
    $p = '_nucleus_activity_';
    if ($col === 'activity_type') {
        $t = get_post_meta($pid, $p . 'type', true) ?: 'program';
        echo esc_html(ucfirst($t));
    }
    if ($col === 'activity_date') {
        $s = get_post_meta($pid, $p . 'start_date', true);
        if ($s) {
            echo esc_html(gmdate('M j, Y', strtotime($s)));
            $e = get_post_meta($pid, $p . 'end_date', true);
            if ($e) echo ' — ' . esc_html(gmdate('M j, Y', strtotime($e)));
        } else { echo '—'; }
    }
    if ($col === 'activity_status') {
        $st = nucleus_get_activity_status($pid);
        echo esc_html(ucfirst(str_replace('-', ' ', $st)));
    }
    if ($col === 'activity_hrdc') {
        echo (get_post_meta($pid, $p . 'hrdc', true) === '1') ? '✓' : '—';
    }
}, 10, 2);


/* ================================================================
   8. HELPER
   ================================================================ */

function nucleus_get_activity_status($pid)
{
    $p   = '_nucleus_activity_';
    $s   = get_post_meta($pid, $p . 'start_date', true);
    $e   = get_post_meta($pid, $p . 'end_date', true);
    $now = current_time('Y-m-d');
    if (empty($s)) return 'no-date';
    if ($s > $now)  return 'upcoming';
    if ($s <= $now && (!empty($e) && $e >= $now)) return 'ongoing';
    if ($s === $now && empty($e)) return 'ongoing';
    return 'past';
}


/* ================================================================
   9. FRONT-END DASHICONS
   ================================================================ */

add_action('wp_enqueue_scripts', function () {
    if (is_post_type_archive('nucleus_program') || is_singular('nucleus_program')) {
        wp_enqueue_style('dashicons');
    }
});


/* ================================================================
   10. AJAX — DELETE TAG
   ================================================================ */

add_action('wp_ajax_nucleus_delete_tag', function () {
    if (!current_user_can('manage_options')) wp_send_json_error('Unauthorized');
    $term_id = isset($_POST['term_id']) ? absint($_POST['term_id']) : 0;
    if (!$term_id) wp_send_json_error('Missing term ID');
    $result = wp_delete_term($term_id, 'nucleus_activity_tag');
    if (is_wp_error($result)) wp_send_json_error($result->get_error_message());
    wp_send_json_success();
});
