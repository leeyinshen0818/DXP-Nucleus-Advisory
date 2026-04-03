<?php
/**
 * Program Manager
 *
 * Registers Custom Post Types for Programs and Events, and handles the custom 
 * dashboard UI for editing media galleries and descriptions.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Program and Event Post Types
 */
function nucleus_register_program_event_post_types()
{
    // Program CPT
    $program_labels = array(
        'name'                  => _x('Programs', 'Post type general name', 'nucleus-dxp'),
        'singular_name'         => _x('Program', 'Post type singular name', 'nucleus-dxp'),
        'menu_name'             => _x('Program Manager', 'Admin Menu text', 'nucleus-dxp'),
        'name_admin_bar'        => _x('Program', 'Add New on Toolbar', 'nucleus-dxp'),
        'add_new'               => __('Add New', 'nucleus-dxp'),
        'add_new_item'          => __('Add New Program', 'nucleus-dxp'),
        'new_item'              => __('New Program', 'nucleus-dxp'),
        'edit_item'             => __('Edit Program', 'nucleus-dxp'),
        'view_item'             => __('View Program', 'nucleus-dxp'),
        'all_items'             => __('All Programs', 'nucleus-dxp'),
        'search_items'          => __('Search Programs', 'nucleus-dxp'),
        'not_found'             => __('No programs found.', 'nucleus-dxp'),
    );

    $program_args = array(
        'labels'             => $program_labels,
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
        'supports'           => array('title'), // Content managed via custom boxes
    );

    register_post_type('nucleus_program', $program_args);

    // Event CPT
    $event_labels = array(
        'name'                  => _x('Events', 'Post type general name', 'nucleus-dxp'),
        'singular_name'         => _x('Event', 'Post type singular name', 'nucleus-dxp'),
        'menu_name'             => _x('Events', 'Admin Menu text', 'nucleus-dxp'),
        'name_admin_bar'        => _x('Event', 'Add New on Toolbar', 'nucleus-dxp'),
        'add_new'               => __('Add New Event', 'nucleus-dxp'),
        'add_new_item'          => __('Add New Event', 'nucleus-dxp'),
        'new_item'              => __('New Event', 'nucleus-dxp'),
        'edit_item'             => __('Edit Event', 'nucleus-dxp'),
        'view_item'             => __('View Event', 'nucleus-dxp'),
        'all_items'             => __('Events', 'nucleus-dxp'),
        'search_items'          => __('Search Events', 'nucleus-dxp'),
        'not_found'             => __('No events found.', 'nucleus-dxp'),
    );

    $event_args = array(
        'labels'             => $event_labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=nucleus_program', // Child menu of Program Manager
        'query_var'          => true,
        'rewrite'            => array('slug' => 'events'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'supports'           => array('title'),
    );

    register_post_type('nucleus_event', $event_args);
}
add_action('init', 'nucleus_register_program_event_post_types');

/**
 * Add Meta Boxes
 */
function nucleus_program_event_meta_boxes()
{
    // Program boxes
    add_meta_box('nucleus_program_events_list', 'Events inside this Program', 'nucleus_program_events_list_html', 'nucleus_program', 'normal', 'high');
    add_meta_box('nucleus_program_details', 'Program Display Details', 'nucleus_program_event_meta_box_html', 'nucleus_program', 'normal', 'high');
    
    // Event boxes
    add_meta_box('nucleus_event_parent', 'Parent Program', 'nucleus_event_parent_html', 'nucleus_event', 'side', 'high');
    add_meta_box('nucleus_event_details', 'Event Display Details', 'nucleus_program_event_meta_box_html', 'nucleus_event', 'normal', 'high');
    
    // Landing Page Settings Main Box
    // Not using meta box for options page, we'll build a custom page

}
add_action('add_meta_boxes', 'nucleus_program_event_meta_boxes');

/**
 * Program Landing Page Settings
 */
function nucleus_program_landing_settings_page() {
    add_submenu_page(
        'edit.php?post_type=nucleus_program',
        'Landing Page',
        'Landing Page',
        'manage_options',
        'nucleus_program_landing',
        'nucleus_program_landing_html'
    );
}
add_action('admin_menu', 'nucleus_program_landing_settings_page');

function nucleus_register_program_settings() {
    register_setting('nucleus_program_options', '_nucleus_landing_title');
    register_setting('nucleus_program_options', '_nucleus_landing_desc');
    register_setting('nucleus_program_options', '_nucleus_landing_hf');
    register_setting('nucleus_program_options', '_nucleus_landing_highlighted');
}
add_action('admin_init', 'nucleus_register_program_settings');

function nucleus_program_landing_html() {
    if (!current_user_can('manage_options')) return;
    
    $title = get_option('_nucleus_landing_title', 'Programs & Initiatives');
    $desc = get_option('_nucleus_landing_desc', '');
    $hf_set_id = get_option('_nucleus_landing_hf', '');
    $highlighted = get_option('_nucleus_landing_highlighted', array());

    $hf_sets = get_posts(array(
        'post_type'      => 'nucleus_hf_set',
        'numberposts'    => -1,
        'post_status'    => 'any'
    ));

    $all_programs = get_posts(array(
        'post_type'   => 'nucleus_program',
        'numberposts' => -1,
        'post_status' => 'publish'
    ));

    ?>
    <div class="wrap">
        <h1>Program Landing Page Settings</h1>
        <p>Customize the content and layout used for the <code>/programs/</code> overview page.</p>
        <form method="post" action="options.php">
            <?php settings_fields('nucleus_program_options'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Header & Footer Layout</th>
                    <td>
                        <select name="_nucleus_landing_hf" style="max-width:300px;">
                            <option value="">-- Theme Default --</option>
                            <?php foreach ($hf_sets as $set): ?>
                                <option value="<?php echo esc_attr($set->ID); ?>" <?php selected($hf_set_id, $set->ID); ?>>
                                    <?php echo esc_html($set->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Page Title</th>
                    <td>
                        <input type="text" name="_nucleus_landing_title" class="regular-text" style="width:100%; max-width:400px;" value="<?php echo esc_attr($title); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Hero Description</th>
                    <td>
                        <?php 
                        wp_editor($desc, '_nucleus_landing_desc', array(
                            'textarea_name' => '_nucleus_landing_desc',
                            'media_buttons' => false,
                            'textarea_rows' => 6,
                            'teeny' => true,
                        ));
                        ?>
                        <p class="description">This description shows up next to "Programs & Initiatives" banner.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Highlighted Programs (Featured)</th>
                    <td>
                        <p class="description">Select 3-4 programs to highlight at the top of the page. Others will show below.</p>
                        <div style="max-height: 250px; overflow-y: auto; border: 1px solid #ccd0d4; padding: 10px; background: #fff; max-width: 600px;">
                            <?php foreach ($all_programs as $prog): ?>
                                <label style="display:block; margin-bottom:8px;">
                                    <input type="checkbox" name="_nucleus_landing_highlighted[]" value="<?php echo esc_attr($prog->ID); ?>" <?php echo is_array($highlighted) && in_array($prog->ID, $highlighted) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($prog->post_title); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Program -> Events List Box
 */
function nucleus_program_events_list_html($post)
{
    $events = get_posts(array(
        'post_type' => 'nucleus_event',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => '_nucleus_parent_program',
                'value' => $post->ID,
                'compare' => '='
            )
        )
    ));
    
    echo '<div style="padding: 10px 0;">';
    if (empty($events)) {
        echo '<p>No events linked to this program yet.</p>';
    } else {
        echo '<ul style="margin: 0; padding: 0; list-style: none;">';
        foreach ($events as $event) {
            echo '<li style="margin-bottom: 8px; padding: 10px; background: #fff; border: 1px solid #ccd0d4; border-radius: 4px;">';
            echo '<strong><a href="' . esc_url(get_edit_post_link($event->ID)) . '">' . esc_html($event->post_title) . '</a></strong>';
            echo '</li>';
        }
        echo '</ul>';
    }
    
    echo '<p style="margin-top: 15px;">';
    if ($post->post_status !== 'auto-draft') {
        echo '<a href="' . esc_url(admin_url('post-new.php?post_type=nucleus_event&n_program_id=' . $post->ID)) . '" class="button button-primary">Add New Event</a>';
    } else {
        echo '<em>Please save this Program first before adding events.</em>';
    }
    echo '</p>';
    echo '</div>';
}

/**
 * Event -> Parent Program Dropdown
 */
function nucleus_event_parent_html($post)
{
    $parent_id = get_post_meta($post->ID, '_nucleus_parent_program', true);
    
    // Check if we came from "Add New Event" inside a Program
    if (empty($parent_id) && isset($_GET['n_program_id'])) {
        $parent_id = absint($_GET['n_program_id']);
    }

    $programs = get_posts(array(
        'post_type' => 'nucleus_program',
        'numberposts' => -1,
        'post_status' => 'publish'
    ));

    ?>
    <p>Select which Program this event belongs to:</p>
    <select name="_nucleus_parent_program" style="width: 100%;">
        <option value="">-- No Parent Program --</option>
        <?php foreach ($programs as $prog): ?>
            <option value="<?php echo esc_attr($prog->ID); ?>" <?php selected($parent_id, $prog->ID); ?>>
                <?php echo esc_html($prog->post_title); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

/**
 * Shared Meta Box HTML (Details, HF, Media)
 */
function nucleus_program_event_meta_box_html($post)
{
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');

    $post_type = $post->post_type;
    $prefix = '_nucleus_' . ($post_type === 'nucleus_program' ? 'program' : 'event');
    
    $hf_set_id = get_post_meta($post->ID, '_nucleus_selected_hf_set', true);
    $desc = get_post_meta($post->ID, $prefix . '_desc', true);
    $media_json = get_post_meta($post->ID, $prefix . '_media', true);

    $date_type = get_post_meta($post->ID, $prefix . '_date_type', true);
    if (empty($date_type)) $date_type = 'specific';
    $start_date = get_post_meta($post->ID, $prefix . '_start_date', true);
    $end_date = get_post_meta($post->ID, $prefix . '_end_date', true);
    $hide_date = get_post_meta($post->ID, $prefix . '_hide_date', true);

    // New Professional Fields
    $outcomes = get_post_meta($post->ID, $prefix . '_outcomes', true);
    $audience = get_post_meta($post->ID, $prefix . '_audience', true);
    $time_loc = get_post_meta($post->ID, $prefix . '_time_loc', true);
    $speakers = get_post_meta($post->ID, $prefix . '_speakers', true);
    $agenda = get_post_meta($post->ID, $prefix . '_agenda', true);

    if (empty($media_json)) {
        $media_json = '[]';
    }

    $hf_sets = get_posts(array(
        'post_type'      => 'nucleus_hf_set',
        'numberposts'    => -1,
        'post_status'    => 'any'
    ));

    wp_nonce_field('nucleus_program_event_meta_box_nonce', 'nucleus_pe_nonce');

    ?>
<style>
    .nucleus-ui-panel {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        padding: 24px; background: #fafafa; border-radius: 8px; border: 1px solid #e2e4e7;
    }
    .nucleus-form-group { margin-bottom: 24px; }
    .nucleus-form-label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; color: #1d2327; }
    .nucleus-desc { color: #646970; font-size: 13px; margin-bottom: 8px; }
    .nucleus-input { width: 100%; max-width: 600px; padding: 8px 12px; border: 1px solid #8c8f94; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .nucleus-gallery-container { display: flex; flex-wrap: wrap; gap: 16px; margin-top: 16px; min-height: 100px; padding: 16px; border: 2px dashed #c3c4c7; border-radius: 8px; background: #fff; align-items: center; justify-content: flex-start; }
    .nucleus-media-item { position: relative; width: 140px; height: 140px; border-radius: 8px; border: 1px solid #dcdde1; overflow: hidden; background: #f0f0f1; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .nucleus-media-item img, .nucleus-media-item video { width: 100%; height: 100%; object-fit: cover; }
    .nucleus-media-remove { position: absolute; top: 6px; right: 6px; background: #d63638; color: white; border: none; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 14px; opacity: 0; transition: opacity 0.2s; }
    .nucleus-media-item:hover .nucleus-media-remove { opacity: 1; }
</style>

<div class="nucleus-ui-panel">
    <div class="nucleus-form-group">
        <label class="nucleus-form-label">Active Header & Footer Layout</label>
        <select name="_nucleus_selected_hf_set" class="nucleus-input" style="max-width:300px;">
            <option value="">-- No Custom Header/Footer --</option>
            <?php foreach ($hf_sets as $set): ?>
                <option value="<?php echo esc_attr($set->ID); ?>" <?php selected($hf_set_id, $set->ID); ?>>
                    <?php echo esc_html($set->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="nucleus-form-group">
        <label class="nucleus-form-label">Description</label>
        <?php 
        wp_editor($desc, $prefix . '_desc', array(
            'textarea_name' => $prefix . '_desc',
            'media_buttons' => false,
            'textarea_rows' => 8,
            'teeny' => true,
        ));
        ?>
    </div>

    <div class="nucleus-form-group">
        <label class="nucleus-form-label">Date Settings</label>
        <p class="nucleus-desc">Define the timeline. Check "Hide Date" to remove it from the public view.</p>
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <input type="checkbox" name="<?php echo esc_attr($prefix . '_hide_date'); ?>" value="1" <?php checked($hide_date, '1'); ?>> Hide Date
        </div>
        <select name="<?php echo esc_attr($prefix . '_date_type'); ?>" class="nucleus-input" style="max-width:300px; margin-bottom: 12px;" onchange="
            var v = this.value; 
            document.getElementById('n-date-fields-<?php echo esc_attr($post->ID); ?>').style.display = (v === 'specific' || v === 'ongoing') ? 'block' : 'none';
            document.getElementById('n-end-date-<?php echo esc_attr($post->ID); ?>').style.display = (v === 'specific') ? 'block' : 'none';
        ">
            <option value="specific" <?php selected($date_type, 'specific'); ?>>Specific Dates</option>
            <option value="soon" <?php selected($date_type, 'soon'); ?>>Starting Soon</option>
            <option value="ongoing" <?php selected($date_type, 'ongoing'); ?>>Ongoing</option>
        </select>
        <div id="n-date-fields-<?php echo esc_attr($post->ID); ?>" style="display: <?php echo ($date_type === 'specific' || $date_type === 'ongoing') ? 'block' : 'none'; ?>;">
            <div style="display: flex; gap: 16px;">
                <div>
                    <label style="font-size: 13px; display:block; margin-bottom: 4px;">Start Date</label>
                    <input type="date" name="<?php echo esc_attr($prefix . '_start_date'); ?>" class="nucleus-input" value="<?php echo esc_attr($start_date); ?>" style="width: 150px;">
                </div>
                <div id="n-end-date-<?php echo esc_attr($post->ID); ?>" style="display: <?php echo ($date_type === 'specific') ? 'block' : 'none'; ?>;">
                    <label style="font-size: 13px; display:block; margin-bottom: 4px;">End Date</label>
                    <input type="date" name="<?php echo esc_attr($prefix . '_end_date'); ?>" class="nucleus-input" value="<?php echo esc_attr($end_date); ?>" style="width: 150px;">
                </div>
            </div>
        </div>
    </div>

    <?php if ($post_type === 'nucleus_program'): ?>
    <div class="nucleus-form-group" style="padding-top:16px; border-top: 1px solid #e2e4e7;">
        <h3 style="margin-top:0;">Program Official Details (Optional)</h3>
        <p class="nucleus-desc">Leave fields blank to hide them on the public page.</p>
        <label class="nucleus-form-label">Target Audience</label>
        <input type="text" name="<?php echo esc_attr($prefix . '_audience'); ?>" class="nucleus-input" value="<?php echo esc_attr($audience); ?>" placeholder="e.g. Executives, General Public" style="margin-bottom: 16px;">
        
        <label class="nucleus-form-label">Key Outcomes / Objectives</label>
        <?php 
        wp_editor($outcomes, $prefix . '_outcomes', array(
            'textarea_name' => $prefix . '_outcomes',
            'media_buttons' => false,
            'textarea_rows' => 4,
            'teeny' => true,
        ));
        ?>
    </div>
    <?php endif; ?>

    <?php if ($post_type === 'nucleus_event'): ?>
    <div class="nucleus-form-group" style="padding-top:16px; border-top: 1px solid #e2e4e7;">
        <h3 style="margin-top:0;">Event Official Details (Optional)</h3>
        <p class="nucleus-desc">Leave fields blank to hide them on the public page.</p>
        
        <label class="nucleus-form-label">Specific Time & Location</label>
        <input type="text" name="<?php echo esc_attr($prefix . '_time_loc'); ?>" class="nucleus-input" value="<?php echo esc_attr($time_loc); ?>" placeholder="e.g. 9:00 AM PST • Zoom Webinar" style="margin-bottom: 16px;">
        
        <label class="nucleus-form-label">Speakers / Facilitators (One per line)</label>
        <textarea name="<?php echo esc_attr($prefix . '_speakers'); ?>" class="nucleus-input" rows="3" style="margin-bottom: 16px;"><?php echo esc_textarea($speakers); ?></textarea>
        
        <label class="nucleus-form-label">Event Agenda</label>
        <?php 
        wp_editor($agenda, $prefix . '_agenda', array(
            'textarea_name' => $prefix . '_agenda',
            'media_buttons' => false,
            'textarea_rows' => 6,
            'teeny' => true,
        ));
        ?>
    </div>
    <?php endif; ?>

    <div class="nucleus-form-group" style="padding-top:16px; border-top: 1px solid #e2e4e7;">
        <label class="nucleus-form-label">Media Gallery (Images & Videos)</label>
        <p class="nucleus-desc">Upload photos and videos. They organize automatically into sections.</p>
        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
            <button type="button" class="button button-primary" id="n-add-img-btn"><span class="dashicons dashicons-format-image" style="margin-top:4px;"></span> Add Images</button>
            <button type="button" class="button" id="n-add-vid-btn"><span class="dashicons dashicons-video-alt3" style="margin-top:4px;"></span> Add Videos</button>
        </div>
        <input type="hidden" name="<?php echo esc_attr($prefix . '_media'); ?>" id="n_media_data" value="<?php echo esc_attr($media_json); ?>">
        
        <h4 style="margin: 0 0 8px; font-size: 14px;">Images</h4>
        <div class="nucleus-gallery-container" id="n-img-gal"></div>

        <h4 style="margin: 0 0 8px; font-size: 14px;">Videos</h4>
        <div class="nucleus-gallery-container" id="n-vid-gal"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var mIn = $('#n_media_data');
    var iCon = $('#n-img-gal');
    var vCon = $('#n-vid-gal');
    var mData = [];
    try { var rv = mIn.val(); if(rv) mData = JSON.parse(rv); } catch(e) { mData = []; }

    function renderG() {
        iCon.empty(); vCon.empty();
        var hI = false, hV = false;
        mData.forEach(function(it, ix) {
            var el = ''; var isV = (it.type === 'video');
            if(isV) { el = '<video src="' + it.url + '" style="pointer-events:none;"></video>'; hV = true; } 
            else { el = '<img src="' + it.url + '">'; hI = true; }
            var wr = $('<div class="nucleus-media-item" data-id="'+it.id+'" data-url="'+it.url+'" data-type="'+it.type+'" style="cursor:move;"></div>')
                .append(el)
                .append('<button type="button" class="nucleus-media-remove" data-ix="'+ix+'">&times;</button>');
            if(isV) vCon.append(wr); else iCon.append(wr);
        });
        if(!hI) iCon.append('<div class="empty-state" style="color:#8c8f94; font-size:14px;">No images added.</div>');
        if(!hV) vCon.append('<div class="empty-state" style="color:#8c8f94; font-size:14px;">No videos added.</div>');
        mIn.val(JSON.stringify(mData));
        
        // Initialize sortable
        if($.fn.sortable) {
            iCon.sortable({
                items: '.nucleus-media-item',
                update: function(event, ui) { syncData(); }
            });
            vCon.sortable({
                items: '.nucleus-media-item',
                update: function(event, ui) { syncData(); }
            });
        }
    }
    
    function syncData() {
        var newData = [];
        $('.nucleus-media-item', iCon).each(function() {
            newData.push({
                id: $(this).data('id'),
                url: $(this).data('url'),
                type: $(this).data('type')
            });
        });
        $('.nucleus-media-item', vCon).each(function() {
            newData.push({
                id: $(this).data('id'),
                url: $(this).data('url'),
                type: $(this).data('type')
            });
        });
        mData = newData;
        mIn.val(JSON.stringify(mData));
        // Re-render to fix indices on delete buttons
        renderG();
    }

    renderG();

    $('.nucleus-gallery-container').on('click', '.nucleus-media-remove', function() {
        mData.splice($(this).data('ix'), 1); renderG();
    });

    function openUploader(mt, ttxt) {
        var fr = wp.media({ title: ttxt, button: { text: 'Add' }, multiple: true, library: { type: mt } });
        fr.on('select', function() {
            var sel = fr.state().get('selection');
            sel.map(function(att) {
                att = att.toJSON();
                mData.push({ id: att.id, url: att.url, type: att.type });
            });
            renderG();
        });
        fr.open();
    }
    $('#n-add-img-btn').on('click', function(e) { e.preventDefault(); openUploader('image', 'Select Images'); });
    $('#n-add-vid-btn').on('click', function(e) { e.preventDefault(); openUploader('video', 'Select Videos'); });
});
</script>
    <?php
}

/**
 * Save Meta Data
 */
function nucleus_save_program_event_meta($post_id)
{
    if (!isset($_POST['nucleus_pe_nonce']) || !wp_verify_nonce($_POST['nucleus_pe_nonce'], 'nucleus_program_event_meta_box_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $post_type = get_post_type($post_id);
    if (!in_array($post_type, array('nucleus_program', 'nucleus_event'))) return;

    $prefix = '_nucleus_' . ($post_type === 'nucleus_program' ? 'program' : 'event');

    if (isset($_POST['_nucleus_selected_hf_set'])) {
        update_post_meta($post_id, '_nucleus_selected_hf_set', sanitize_text_field($_POST['_nucleus_selected_hf_set']));
        update_post_meta($post_id, 'ct_other_template', '0'); // Oxygen fix
    }

    if (isset($_POST[$prefix . '_desc'])) {
        update_post_meta($post_id, $prefix . '_desc', wp_kses_post(wp_unslash($_POST[$prefix . '_desc'])));
    }

    if (isset($_POST[$prefix . '_date_type'])) {
        update_post_meta($post_id, $prefix . '_date_type', sanitize_text_field($_POST[$prefix . '_date_type']));
    }
    if (isset($_POST[$prefix . '_start_date'])) {
        update_post_meta($post_id, $prefix . '_start_date', sanitize_text_field($_POST[$prefix . '_start_date']));
    }
    if (isset($_POST[$prefix . '_end_date'])) {
        update_post_meta($post_id, $prefix . '_end_date', sanitize_text_field($_POST[$prefix . '_end_date']));
    }
    $hide_date_val = isset($_POST[$prefix . '_hide_date']) ? '1' : '0';
    update_post_meta($post_id, $prefix . '_hide_date', $hide_date_val);

    // Save Professional Fields
    if ($post_type === 'nucleus_program') {
        if (isset($_POST[$prefix . '_audience'])) update_post_meta($post_id, $prefix . '_audience', sanitize_text_field($_POST[$prefix . '_audience']));
        if (isset($_POST[$prefix . '_outcomes'])) update_post_meta($post_id, $prefix . '_outcomes', wp_kses_post(wp_unslash($_POST[$prefix . '_outcomes'])));
    }
    
    if ($post_type === 'nucleus_event') {
        if (isset($_POST[$prefix . '_time_loc'])) update_post_meta($post_id, $prefix . '_time_loc', sanitize_text_field($_POST[$prefix . '_time_loc']));
        if (isset($_POST[$prefix . '_speakers'])) update_post_meta($post_id, $prefix . '_speakers', sanitize_textarea_field($_POST[$prefix . '_speakers']));
        if (isset($_POST[$prefix . '_agenda'])) update_post_meta($post_id, $prefix . '_agenda', wp_kses_post(wp_unslash($_POST[$prefix . '_agenda'])));
    }

    if (isset($_POST[$prefix . '_media'])) {
        $m_raw = wp_unslash($_POST[$prefix . '_media']);
        $m_p = json_decode($m_raw, true);
        if (is_array($m_p)) {
            $cl = array();
            foreach ($m_p as $m) {
                if (isset($m['url']) && isset($m['type'])) {
                    $cl[] = array('id' => isset($m['id']) ? absint($m['id']) : 0, 'url' => esc_url_raw($m['url']), 'type' => sanitize_text_field($m['type']));
                }
            }
            update_post_meta($post_id, $prefix . '_media', wp_json_encode($cl));
        } else {
            update_post_meta($post_id, $prefix . '_media', '[]');
        }
    }

    if ($post_type === 'nucleus_event' && isset($_POST['_nucleus_parent_program'])) {
        update_post_meta($post_id, '_nucleus_parent_program', absint($_POST['_nucleus_parent_program']));
    }
}
add_action('save_post_nucleus_program', 'nucleus_save_program_event_meta');
add_action('save_post_nucleus_event', 'nucleus_save_program_event_meta');

/**
 * Template Routing
 */
add_filter('template_include', function ($template) {
    if (is_post_type_archive('nucleus_program')) {
        $custom_template = plugin_dir_path(dirname(__FILE__)) . 'templates/programs-landing.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    if (is_singular('nucleus_program') || is_singular('nucleus_event')) {
        $hf_set_id = get_post_meta(get_the_ID(), '_nucleus_selected_hf_set', true);
        if (!empty($hf_set_id)) {
            $pt = get_post_type();
            $custom_template = plugin_dir_path(dirname(__FILE__)) . 'templates/single-' . $pt . '.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
    }
    return $template;
}, 99999);

/**
 * Custom Columns for Events List
 */
add_filter('manage_nucleus_event_posts_columns', 'nucleus_event_set_columns');
function nucleus_event_set_columns($columns) {
    $new = array();
    foreach($columns as $key => $title) {
        $new[$key] = $title;
        if($key === 'title') {
            $new['parent_program'] = __('Parent Program', 'nucleus-dxp');
        }
    }
    return $new;
}

add_action('manage_nucleus_event_posts_custom_column', 'nucleus_event_custom_column', 10, 2);
function nucleus_event_custom_column($column, $post_id) {
    if ($column === 'parent_program') {
        $parent_id = get_post_meta($post_id, '_nucleus_parent_program', true);
        if(!empty($parent_id) && get_post_type($parent_id) === 'nucleus_program') {
            $parent = get_post($parent_id);
            if($parent) {
                $edit_url = get_edit_post_link($parent_id);
                echo '<strong><a href="'.esc_url($edit_url).'" style="color:#2563eb;">'.esc_html($parent->post_title).'</a></strong>';
            } else {
                echo '&mdash;';
            }
        } else {
        }
    }
}

/**
 * Enqueue Dashicons on Frontend for Program Manager Templates
 */
add_action('wp_enqueue_scripts', function() {
    if (is_post_type_archive('nucleus_program') || is_singular('nucleus_program') || is_singular('nucleus_event')) {
        wp_enqueue_style('dashicons');
    }
});
