<?php
/**
 * Activity Manager
 *
 * Registers the Custom Post Type for Activities and handles the custom 
 * dashboard UI for editing activity media galleries and descriptions.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Activity Post Type
 */
function nucleus_register_activity_post_type()
{
    $labels = array(
        'name'                  => _x('Activities', 'Post type general name', 'nucleus-dxp'),
        'singular_name'         => _x('Activity', 'Post type singular name', 'nucleus-dxp'),
        'menu_name'             => _x('Activity Manager', 'Admin Menu text', 'nucleus-dxp'),
        'name_admin_bar'        => _x('Activity', 'Add New on Toolbar', 'nucleus-dxp'),
        'add_new'               => __('Add New', 'nucleus-dxp'),
        'add_new_item'          => __('Add New Activity', 'nucleus-dxp'),
        'new_item'              => __('New Activity', 'nucleus-dxp'),
        'edit_item'             => __('Edit Activity', 'nucleus-dxp'),
        'view_item'             => __('View Activity', 'nucleus-dxp'),
        'all_items'             => __('All Activities', 'nucleus-dxp'),
        'search_items'          => __('Search Activities', 'nucleus-dxp'),
        'not_found'             => __('No activities found.', 'nucleus-dxp'),
        'not_found_in_trash'    => __('No activities found in Trash.', 'nucleus-dxp'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'activities'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 8,
        'menu_icon'          => 'dashicons-format-gallery', // Icon for gallery/activities
        'supports'           => array('title'), // Content managed via custom boxes
    );

    register_post_type('nucleus_activity', $args);
}
add_action('init', 'nucleus_register_activity_post_type');

/**
 * Add Meta Boxes to Activity
 */
function nucleus_activity_meta_boxes()
{
    add_meta_box('nucleus_activity_details', 'Activity Display Details', 'nucleus_activity_meta_box_html', 'nucleus_activity', 'normal', 'high');
}
add_action('add_meta_boxes', 'nucleus_activity_meta_boxes');

/**
 * Render Meta Box output
 */
function nucleus_activity_meta_box_html($post)
{
    // Need wp.media to upload files safely
    wp_enqueue_media();

    $hf_set_id = get_post_meta($post->ID, '_nucleus_selected_hf_set', true);
    $activity_desc = get_post_meta($post->ID, '_nucleus_activity_desc', true);
    $media_json = get_post_meta($post->ID, '_nucleus_activity_media', true);

    if (empty($media_json)) {
        $media_json = '[]';
    }

    $hf_sets = get_posts(array(
        'post_type'      => 'nucleus_hf_set',
        'numberposts'    => -1,
        'post_status'    => 'any'
    ));

    wp_nonce_field('nucleus_activity_meta_box_nonce', 'nucleus_activity_nonce');

    // UI Styles
    ?>
<style>
    .nucleus-ui-panel {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        padding: 24px;
        background: #fafafa;
        border-radius: 8px;
        border: 1px solid #e2e4e7;
    }
    .nucleus-form-group { margin-bottom: 24px; }
    .nucleus-form-label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 8px; color: #1d2327; }
    .nucleus-desc { color: #646970; font-size: 13px; margin-bottom: 8px; }
    .nucleus-input {
        width: 100%; max-width: 600px; padding: 8px 12px; border: 1px solid #8c8f94; border-radius: 4px;
        font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .nucleus-gallery-container {
        display: flex; flex-wrap: wrap; gap: 16px; margin-top: 16px; min-height: 100px; padding: 16px;
        border: 2px dashed #c3c4c7; border-radius: 8px; background: #fff; align-items: center; justify-content: center;
    }
    .nucleus-media-item {
        position: relative; width: 140px; height: 140px; border-radius: 8px; border: 1px solid #dcdde1;
        overflow: hidden; background: #f0f0f1; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .nucleus-media-item img, .nucleus-media-item video {
        width: 100%; height: 100%; object-fit: cover;
    }
    .nucleus-media-remove {
        position: absolute; top: 6px; right: 6px; background: #d63638; color: white; border: none;
        width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: 14px; font-weight: bold; line-height: 1; opacity: 0; transition: opacity 0.2s;
    }
    .nucleus-media-item:hover .nucleus-media-remove { opacity: 1; }
    .nucleus-gallery-empty { color: #8c8f94; font-size: 14px; }
</style>

<div class="nucleus-ui-panel">
    <!-- Header & Footer Selection -->
    <div class="nucleus-form-group">
        <label class="nucleus-form-label">Active Header & Footer Layout</label>
        <p class="nucleus-desc">Select the global layout overlay to build the top and bottom of this activity page.</p>
        <select name="_nucleus_selected_hf_set" class="nucleus-input" style="max-width:300px;">
            <option value="">-- No Custom Header/Footer --</option>
            <?php foreach ($hf_sets as $set): ?>
                <option value="<?php echo esc_attr($set->ID); ?>" <?php selected($hf_set_id, $set->ID); ?>>
                    <?php echo esc_html($set->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Description -->
    <div class="nucleus-form-group">
        <label class="nucleus-form-label">Activity Description</label>
        <p class="nucleus-desc">Write an engaging description for what happened at this activity.</p>
        <?php 
        wp_editor($activity_desc, '_nucleus_activity_desc', array(
            'textarea_name' => '_nucleus_activity_desc',
            'media_buttons' => false,
            'textarea_rows' => 8,
            'teeny' => true,
        ));
        ?>
    </div>

    <!-- Media Gallery Section -->
    <div class="nucleus-form-group">
        <label class="nucleus-form-label">Activity Media (Images & Videos)</label>
        <p class="nucleus-desc">Upload photos and videos for this activity. They will automatically organize into their respective sections.</p>
        
        <div style="display: flex; gap: 12px; margin-bottom: 16px;">
            <button type="button" class="button button-primary" id="nucleus-add-image-btn">
                <span class="dashicons dashicons-format-image" style="margin-top:4px;"></span> Add Images
            </button>
            <button type="button" class="button" id="nucleus-add-video-btn">
                <span class="dashicons dashicons-video-alt3" style="margin-top:4px;"></span> Add Videos
            </button>
        </div>
        
        <input type="hidden" name="_nucleus_activity_media" id="nucleus_activity_media_data" value="<?php echo esc_attr($media_json); ?>">
        
        <h4 style="margin: 0 0 8px; font-size: 14px; color: #1d2327;">Uploaded Images</h4>
        <div class="nucleus-gallery-container" id="nucleus-image-gallery" style="margin-bottom: 24px; justify-content: flex-start;"></div>

        <h4 style="margin: 0 0 8px; font-size: 14px; color: #1d2327;">Uploaded Videos</h4>
        <div class="nucleus-gallery-container" id="nucleus-video-gallery" style="justify-content: flex-start;"></div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        var mediaInput = $('#nucleus_activity_media_data');
        var imgContainer = $('#nucleus-image-gallery');
        var vidContainer = $('#nucleus-video-gallery');
        var mediaData = [];
        
        try {
            var rawVal = mediaInput.val();
            if(rawVal) mediaData = JSON.parse(rawVal);
        } catch(e) {
            mediaData = [];
        }

        function renderGallery() {
            imgContainer.empty();
            vidContainer.empty();
            
            var hasImages = false;
            var hasVideos = false;

            mediaData.forEach(function(item, index) {
                var mediaEl = '';
                var isVideo = (item.type === 'video');
                
                if(isVideo) {
                    mediaEl = '<video src="' + item.url + '" style="pointer-events:none;"></video>';
                    hasVideos = true;
                } else {
                    mediaEl = '<img src="' + item.url + '" alt="Activity Media">';
                    hasImages = true;
                }
                
                var wrapper = $('<div class="nucleus-media-item"></div>');
                wrapper.append(mediaEl);
                wrapper.append('<button type="button" class="nucleus-media-remove" data-index="'+index+'">&times;</button>');
                
                if (isVideo) {
                    vidContainer.append(wrapper);
                } else {
                    imgContainer.append(wrapper);
                }
            });
            
            if (!hasImages) imgContainer.append('<div class="nucleus-gallery-empty">No images added yet.</div>');
            if (!hasVideos) vidContainer.append('<div class="nucleus-gallery-empty">No videos added yet.</div>');
            
            mediaInput.val(JSON.stringify(mediaData));
        }

        renderGallery();

        // Remove Media (delegated to both containers)
        $('.nucleus-gallery-container').on('click', '.nucleus-media-remove', function() {
            var idx = $(this).data('index');
            mediaData.splice(idx, 1);
            renderGallery();
        });

        // Add Media Helper
        function openMediaUploader(mediaType, titleText) {
            var frame = wp.media({
                title: titleText,
                button: { text: 'Add to Activity' },
                multiple: true,
                library: { type: mediaType }
            });

            frame.on('select', function() {
                var selection = frame.state().get('selection');
                selection.map(function(attachment) {
                    attachment = attachment.toJSON();
                    mediaData.push({
                        id: attachment.id,
                        url: attachment.url,
                        type: attachment.type
                    });
                });
                renderGallery();
            });

            frame.open();
        }

        // Bind Buttons
        $('#nucleus-add-image-btn').on('click', function(e) {
            e.preventDefault();
            openMediaUploader('image', 'Select Activity Images');
        });

        $('#nucleus-add-video-btn').on('click', function(e) {
            e.preventDefault();
            openMediaUploader('video', 'Select Activity Videos');
        });
    });
</script>
    <?php
}

/**
 * Save Activity Meta Box Data
 */
function nucleus_save_activity_meta($post_id)
{
    if (!isset($_POST['nucleus_activity_nonce']) || !wp_verify_nonce($_POST['nucleus_activity_nonce'], 'nucleus_activity_meta_box_nonce'))
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (!current_user_can('edit_post', $post_id))
        return;

    // Header & Footer
    if (isset($_POST['_nucleus_selected_hf_set'])) {
        update_post_meta($post_id, '_nucleus_selected_hf_set', sanitize_text_field($_POST['_nucleus_selected_hf_set']));
    }

    // Details 
    if (isset($_POST['_nucleus_activity_desc'])) {
        update_post_meta($post_id, '_nucleus_activity_desc', wp_kses_post(wp_unslash($_POST['_nucleus_activity_desc'])));
    }

    // Media Gallery Array JSON Storage
    if (isset($_POST['_nucleus_activity_media'])) {
        // Since it's JSON array built by JS, sanitize to avoid XSS but preserve JSON structure
        $media_raw = wp_unslash($_POST['_nucleus_activity_media']);
        $media_parsed = json_decode($media_raw, true);
        if (is_array($media_parsed)) {
            $clean_media = array();
            foreach ($media_parsed as $m) {
                if (isset($m['url']) && isset($m['type'])) {
                    $clean_media[] = array(
                        'id' => isset($m['id']) ? absint($m['id']) : 0,
                        'url' => esc_url_raw($m['url']),
                        'type' => sanitize_text_field($m['type'])
                    );
                }
            }
            update_post_meta($post_id, '_nucleus_activity_media', wp_json_encode($clean_media));
        } else {
            update_post_meta($post_id, '_nucleus_activity_media', '[]');
        }
    }
    
    // Auto Setup Oxygen (Strip template if HF Override applied exactly like products)
    $hf_set_id = get_post_meta($post_id, '_nucleus_selected_hf_set', true);
    if (!empty($hf_set_id)) {
        update_post_meta($post_id, 'ct_other_template', '0');
    }
}
add_action('save_post_nucleus_activity', 'nucleus_save_activity_meta');

/**
 * Template Routing for Activity
 */
add_filter('template_include', function ($template) {
    if (is_singular('nucleus_activity')) {
        $hf_set_id = get_post_meta(get_the_ID(), '_nucleus_selected_hf_set', true);
        if (!empty($hf_set_id)) {
            $custom_template = plugin_dir_path(dirname(__FILE__)) . 'templates/single-nucleus_activity.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
    }
    return $template;
}, 99999);
