<?php
/**
 * Service Manager (Custom Post Type)
 * ====================================
 * Registers the 'nucleus_service' post type to create a
 * Service Manager dashboard in the WordPress admin area.
 */

if (!defined('ABSPATH')) {
    exit;
}

// =============================================
// 1. Register Custom Post Type: nucleus_service
// =============================================
function nucleus_dxp_register_service_cpt()
{
    $labels = array(
        'name'                  => _x('Service Manager', 'Post Type General Name', 'text_domain'),
        'singular_name'         => _x('Service', 'Post Type Singular Name', 'text_domain'),
        'menu_name'             => __('Service Manager', 'text_domain'),
        'name_admin_bar'        => __('Service', 'text_domain'),
        'archives'              => __('Service Archives', 'text_domain'),
        'attributes'            => __('Service Attributes', 'text_domain'),
        'parent_item_colon'     => __('Parent Service:', 'text_domain'),
        'all_items'             => __('All Services', 'text_domain'),
        'add_new_item'          => __('Add New Service', 'text_domain'),
        'add_new'               => __('Add New', 'text_domain'),
        'new_item'              => __('New Service', 'text_domain'),
        'edit_item'             => __('Edit Service', 'text_domain'),
        'update_item'           => __('Update Service', 'text_domain'),
        'view_item'             => __('View Service', 'text_domain'),
        'view_items'            => __('View Services', 'text_domain'),
        'search_items'          => __('Search Service', 'text_domain'),
        'not_found'             => __('Not found', 'text_domain'),
        'not_found_in_trash'    => __('Not found in Trash', 'text_domain'),
        'featured_image'        => __('Service Image', 'text_domain'),
        'set_featured_image'    => __('Set service image', 'text_domain'),
        'remove_featured_image' => __('Remove service image', 'text_domain'),
        'use_featured_image'    => __('Use as service image', 'text_domain'),
        'insert_into_item'      => __('Insert into service', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this service', 'text_domain'),
        'items_list'            => __('Services list', 'text_domain'),
        'items_list_navigation' => __('Services list navigation', 'text_domain'),
        'filter_items_list'     => __('Filter services list', 'text_domain'),
    );

    $args = array(
        'label'               => __('Service', 'text_domain'),
        'description'         => __('Manage services for the website', 'text_domain'),
        'labels'              => $labels,
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 31,
        'menu_icon'           => 'dashicons-rest-api',
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest'        => true,
    );

    register_post_type('nucleus_service', $args);
}
add_action('init', 'nucleus_dxp_register_service_cpt', 0);

// =============================================
// 2. Register Meta Boxes
// =============================================
function nucleus_service_meta_boxes()
{
    add_meta_box(
        'nucleus_service_details',
        'Service Details',
        'nucleus_service_meta_box_html',
        'nucleus_service',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'nucleus_service_meta_boxes');

// =============================================
// 3. Meta Box HTML (UI + Filters + Repeater)
// =============================================
function nucleus_service_meta_box_html($post)
{
    // Retrieve saved meta values
    $service_subtitle  = get_post_meta($post->ID, '_nucleus_service_subtitle', true);
    $service_summary   = get_post_meta($post->ID, '_nucleus_service_summary', true);
    $service_category  = get_post_meta($post->ID, '_nucleus_service_category', true);
    $service_status    = get_post_meta($post->ID, '_nucleus_service_status', true);

    // Service Features repeater
    $service_features  = get_post_meta($post->ID, '_nucleus_service_features', true);
    if (empty($service_features) || !is_array($service_features)) {
        $service_features = array();
    }

    // Nonce for security
    wp_nonce_field('nucleus_service_meta_box_nonce', 'nucleus_service_nonce');

    // Dropdown option definitions
    $category_options = array(
        ''               => '— Select Category —',
        'consulting'     => 'Consulting',
        'implementation' => 'Implementation',
        'support'        => 'Support',
    );

    $status_options = array(
        ''           => '— Select Status —',
        'active'     => 'Active',
        'coming_soon'=> 'Coming Soon',
        'hidden'     => 'Hidden',
    );
    ?>

    <style>
        /* ---- Shared Tabs & Repeater CSS (mirrors Product Manager) ---- */
        .n-tabs { display: flex; gap: 5px; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-top: 10px; }
        .n-tab { padding: 10px 18px; cursor: pointer; border: 1px solid transparent; border-bottom: none; background: #f1f1f1; border-radius: 4px 4px 0 0; font-weight: 600; font-size: 13px; color: #555; transition: background 0.15s; }
        .n-tab:hover { background: #e5e5e5; }
        .n-tab.active { background: #fff; border-color: #ddd; border-bottom-color: #fff; margin-bottom: -2px; color: #2271b1; }
        .n-tab-content { display: none; padding-top: 5px; }
        .n-tab-content.active { display: block; }

        .n-repeater-container { margin-top: 10px; }

        .n-repeater-row {
            background: #f9f9f9;
            border: 1px solid #ccd0d4;
            padding: 10px 10px 10px 78px;
            margin-bottom: 8px;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            position: relative;
            transition: opacity 0.15s, box-shadow 0.15s;
        }

        .n-repeater-row.is-dragging { opacity: 0.4; }
        .n-repeater-row.drag-over { border-top: 2px dashed #2271b1; }

        .n-repeater-row input[type="text"],
        .n-repeater-row textarea { width: 100%; margin-bottom: 5px; }

        .n-repeater-row .n-row-fields { flex-grow: 1; }

        .n-drag-handle {
            position: absolute;
            left: 10px;
            top: 12px;
            width: 22px;
            height: 28px;
            cursor: grab;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 16px;
            letter-spacing: 2px;
            user-select: none;
            border-radius: 3px;
            transition: color 0.15s, background 0.15s;
        }
        .n-drag-handle:hover { color: #555; background: #eee; }
        .n-drag-handle:active { cursor: grabbing; }

        .n-row-number {
            position: absolute;
            left: 38px;
            top: 12px;
            width: 28px;
            height: 28px;
            background: #2271b1;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
        }

        .n-repeater-remove { color: #d63638; cursor: pointer; font-weight: bold; background: none; border: none; padding: 5px; }
        .n-repeater-remove:hover { color: #a00; }

        .n-add-row-btn {
            display: inline-block;
            margin-bottom: 20px;
            background: #2271b1;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }
        .n-add-row-btn:hover { background: #135e96; color: #fff; }

        .n-section-title {
            font-size: 16px;
            font-weight: 600;
            padding: 10px 0 5px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }

        /* ---- Service-manager-specific ---- */
        .ns-filter-bar {
            background: #eef5ff;
            border: 1px solid #9ba2aa;
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .ns-filter-bar label { font-weight: 600; font-size: 13px; margin-right: 6px; }
        .ns-filter-bar select { min-width: 180px; }

        .ns-add-service-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #2271b1;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-left: auto;
            transition: background 0.15s;
        }
        .ns-add-service-btn:hover { background: #135e96; color: #fff; }

        .ns-status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ns-status-active     { background: #d1fae5; color: #065f46; }
        .ns-status-coming_soon{ background: #fef3c7; color: #92400e; }
        .ns-status-hidden     { background: #f3f4f6; color: #6b7280; }

        .ns-feature-icon-input { flex: 0 0 90px; max-width: 90px; }
    </style>

    <?php
    // Determine badge class for current status
    $badge_map = array(
        'active'      => 'ns-status-active',
        'coming_soon' => 'ns-status-coming_soon',
        'hidden'      => 'ns-status-hidden',
    );
    $current_badge = isset($badge_map[$service_status]) ? $badge_map[$service_status] : '';
    ?>

    <!-- ====================== TOP FILTER / ACTION BAR ====================== -->
    <div class="ns-filter-bar">
        <div>
            <label for="ns_service_category"><span class="dashicons dashicons-category" style="vertical-align:middle;"></span> Service Category:</label>
            <select id="ns_service_category" name="ns_service_category">
                <?php foreach ($category_options as $val => $label): ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($service_category, $val); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="ns_service_status"><span class="dashicons dashicons-visibility" style="vertical-align:middle;"></span> Service Status:</label>
            <select id="ns_service_status" name="ns_service_status">
                <?php foreach ($status_options as $val => $label): ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($service_status, $val); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($service_status) && isset($badge_map[$service_status])): ?>
                <span class="ns-status-badge <?php echo esc_attr($current_badge); ?>" style="margin-left:8px;">
                    <?php echo esc_html($status_options[$service_status]); ?>
                </span>
            <?php endif; ?>
        </div>

        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=nucleus_service')); ?>"
           class="ns-add-service-btn"
           id="ns-add-new-service-btn">
            <span class="dashicons dashicons-plus-alt2" style="font-size:16px; width:16px; height:16px; margin-top:1px;"></span>
            Add New Service
        </a>
    </div>

    <!-- ====================== HERO DETAILS ====================== -->
    <div class="n-section-title">Hero Details</div>
    <p>
        <label for="ns_service_subtitle"><strong>Subtitle:</strong></label><br>
        <input type="text"
               id="ns_service_subtitle"
               name="ns_service_subtitle"
               value="<?php echo esc_attr($service_subtitle); ?>"
               style="width:100%;"
               placeholder="e.g. Empowering Your Business Growth">
        <small>Displays below the main title on the service page.</small>
    </p>
    <p>
        <label for="ns_service_summary"><strong>Service Summary</strong> <em>(appears in the hero section)</em>:</label><br>
        <textarea id="ns_service_summary"
                  name="ns_service_summary"
                  rows="3"
                  style="width:100%;"
                  placeholder="A short overview of this service..."><?php echo esc_textarea($service_summary); ?></textarea>
    </p>

    <!-- ====================== SERVICE FEATURES REPEATER ====================== -->
    <div class="n-section-title" style="margin-top:30px;">Service Features</div>
    <p><small>Add, remove, and reorder the key features of this service. Drag the <strong>⠿</strong> handle to reorder.</small></p>

    <div class="n-repeater-container" id="ns-features-container">
        <?php $i = 1;
        foreach ($service_features as $feature): ?>
            <div class="n-repeater-row" draggable="true">
                <span class="n-drag-handle" title="Drag to reorder">⠿</span>
                <span class="n-row-number"><?php echo $i; ?></span>
                <div class="n-row-fields">
                    <input type="text"
                           name="ns_feature_title[]"
                           value="<?php echo esc_attr(isset($feature['title']) ? $feature['title'] : ''); ?>"
                           placeholder="Feature Title (e.g. 24/7 Support)">
                    <input type="text"
                           name="ns_feature_icon[]"
                           value="<?php echo esc_attr(isset($feature['icon']) ? $feature['icon'] : ''); ?>"
                           placeholder="Dashicon class (e.g. dashicons-yes-alt) — optional"
                           class="ns-feature-icon-input"
                           style="max-width:260px;">
                    <textarea name="ns_feature_desc[]"
                              rows="2"
                              placeholder="Feature description (short paragraph)"><?php echo esc_textarea(isset($feature['desc']) ? $feature['desc'] : ''); ?></textarea>
                </div>
                <button type="button" class="n-repeater-remove" onclick="nsRemoveRow(this)">✖</button>
            </div>
        <?php $i++; endforeach; ?>
    </div>
    <button type="button" class="n-add-row-btn" id="ns-add-feature-btn" onclick="nsAddFeatureRow()">+ Add Feature</button>

    <script>
        // ---- Row numbering ----
        function nsRenumberRows(containerId) {
            var rows = document.getElementById(containerId).querySelectorAll('.n-repeater-row');
            rows.forEach(function (row, index) {
                var badge = row.querySelector('.n-row-number');
                if (badge) badge.textContent = index + 1;
            });
        }

        function nsRemoveRow(btn) {
            var container = btn.closest('.n-repeater-container');
            btn.parentElement.remove();
            nsRenumberRows(container.id);
        }

        // ---- Add Feature Row ----
        function nsAddFeatureRow() {
            var container = document.getElementById('ns-features-container');
            var num = container.querySelectorAll('.n-repeater-row').length + 1;
            var html = '<div class="n-repeater-row" draggable="true">'
                + '<span class="n-drag-handle" title="Drag to reorder">⠿</span>'
                + '<span class="n-row-number">' + num + '</span>'
                + '<div class="n-row-fields">'
                + '<input type="text" name="ns_feature_title[]" placeholder="Feature Title (e.g. 24/7 Support)">'
                + '<input type="text" name="ns_feature_icon[]" placeholder="Dashicon class (e.g. dashicons-yes-alt) — optional" style="max-width:260px;">'
                + '<textarea name="ns_feature_desc[]" rows="2" placeholder="Feature description (short paragraph)"></textarea>'
                + '</div>'
                + '<button type="button" class="n-repeater-remove" onclick="nsRemoveRow(this)">✖</button>'
                + '</div>';
            container.insertAdjacentHTML('beforeend', html);
            nsInitDragDrop(container);
        }

        // ---- Status badge live preview ----
        (function () {
            var statusSelect = document.getElementById('ns_service_status');
            if (!statusSelect) return;

            var badgeMap = {
                'active':       { cls: 'ns-status-active',      label: 'Active' },
                'coming_soon':  { cls: 'ns-status-coming_soon', label: 'Coming Soon' },
                'hidden':       { cls: 'ns-status-hidden',      label: 'Hidden' },
            };

            // Find or create a badge element next to the select
            var existingBadge = statusSelect.parentNode.querySelector('.ns-status-badge');

            function updateBadge() {
                var val = statusSelect.value;
                if (val && badgeMap[val]) {
                    if (!existingBadge) {
                        existingBadge = document.createElement('span');
                        existingBadge.className = 'ns-status-badge';
                        existingBadge.style.marginLeft = '8px';
                        statusSelect.after(existingBadge);
                    }
                    existingBadge.className = 'ns-status-badge ' + badgeMap[val].cls;
                    existingBadge.textContent = badgeMap[val].label;
                    existingBadge.style.display = 'inline-block';
                } else if (existingBadge) {
                    existingBadge.style.display = 'none';
                }
            }

            statusSelect.addEventListener('change', updateBadge);
        })();

        // ---- Drag and Drop (mirrors Product Manager logic) ----
        var nsDragSrcEl = null;

        function nsInitDragDrop(container) {
            var rows = container.querySelectorAll('.n-repeater-row');
            rows.forEach(function (row) {
                row.removeEventListener('dragstart', nsDragStart);
                row.removeEventListener('dragover',  nsDragOver);
                row.removeEventListener('dragleave', nsDragLeave);
                row.removeEventListener('drop',      nsDrop);
                row.removeEventListener('dragend',   nsDragEnd);
                row.addEventListener('dragstart', nsDragStart);
                row.addEventListener('dragover',  nsDragOver);
                row.addEventListener('dragleave', nsDragLeave);
                row.addEventListener('drop',      nsDrop);
                row.addEventListener('dragend',   nsDragEnd);
            });
        }

        function nsDragStart(e) {
            nsDragSrcEl = this;
            this.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
        }

        function nsDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (this !== nsDragSrcEl) {
                this.classList.add('drag-over');
            }
            return false;
        }

        function nsDragLeave() {
            this.classList.remove('drag-over');
        }

        function nsDrop(e) {
            e.stopPropagation();
            if (nsDragSrcEl !== this) {
                var container = this.closest('.n-repeater-container');
                var allRows   = Array.from(container.querySelectorAll('.n-repeater-row'));
                var fromIdx   = allRows.indexOf(nsDragSrcEl);
                var toIdx     = allRows.indexOf(this);
                if (fromIdx < toIdx) {
                    this.parentNode.insertBefore(nsDragSrcEl, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(nsDragSrcEl, this);
                }
                nsRenumberRows(container.id);
            }
            this.classList.remove('drag-over');
            return false;
        }

        function nsDragEnd() {
            this.classList.remove('is-dragging');
            var container = this.closest('.n-repeater-container');
            container.querySelectorAll('.n-repeater-row').forEach(function (r) {
                r.classList.remove('drag-over');
            });
        }

        // Init drag on page load
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.n-repeater-container').forEach(function (c) {
                nsInitDragDrop(c);
            });
        });
    </script>
    <?php
}

// =============================================
// 4. Save Meta Data (save_post_nucleus_service)
// =============================================
function nucleus_save_service_meta($post_id)
{
    // Nonce verification
    if (!isset($_POST['nucleus_service_nonce']) || !wp_verify_nonce($_POST['nucleus_service_nonce'], 'nucleus_service_meta_box_nonce')) {
        return;
    }

    // Autosave guard
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Capability check
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // ---- Hero Details ----
    if (isset($_POST['ns_service_subtitle'])) {
        update_post_meta($post_id, '_nucleus_service_subtitle', sanitize_text_field($_POST['ns_service_subtitle']));
    }

    if (isset($_POST['ns_service_summary'])) {
        update_post_meta($post_id, '_nucleus_service_summary', sanitize_textarea_field($_POST['ns_service_summary']));
    }

    // ---- Dropdown: Service Category ----
    $allowed_categories = array('consulting', 'implementation', 'support');
    if (isset($_POST['ns_service_category'])) {
        $cat = sanitize_text_field($_POST['ns_service_category']);
        if (in_array($cat, $allowed_categories, true) || $cat === '') {
            update_post_meta($post_id, '_nucleus_service_category', $cat);
        }
    }

    // ---- Dropdown: Service Status ----
    $allowed_statuses = array('active', 'coming_soon', 'hidden');
    if (isset($_POST['ns_service_status'])) {
        $status = sanitize_text_field($_POST['ns_service_status']);
        if (in_array($status, $allowed_statuses, true) || $status === '') {
            update_post_meta($post_id, '_nucleus_service_status', $status);
        }
    }

    // ---- Service Features Repeater ----
    $feature_titles = isset($_POST['ns_feature_title']) ? $_POST['ns_feature_title'] : array();
    $feature_icons  = isset($_POST['ns_feature_icon'])  ? $_POST['ns_feature_icon']  : array();
    $feature_descs  = isset($_POST['ns_feature_desc'])  ? $_POST['ns_feature_desc']  : array();

    $features = array();
    $count = count($feature_titles);

    for ($i = 0; $i < $count; $i++) {
        $title = sanitize_text_field(isset($feature_titles[$i]) ? $feature_titles[$i] : '');
        $icon  = sanitize_html_class(isset($feature_icons[$i])  ? $feature_icons[$i]  : '');
        $desc  = sanitize_textarea_field(isset($feature_descs[$i])  ? $feature_descs[$i]  : '');

        // Only save rows that have at least a title
        if (!empty($title)) {
            $features[] = array(
                'title' => $title,
                'icon'  => $icon,
                'desc'  => $desc,
            );
        }
    }

    update_post_meta($post_id, '_nucleus_service_features', $features);
}
add_action('save_post_nucleus_service', 'nucleus_save_service_meta');
