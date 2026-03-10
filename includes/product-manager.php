<?php
/**
 * Product Manager (Custom Post Type)
 * ==================================
 * Registers the 'nucleus_product' post type to create a 
 * Product Manager dashboard in the WordPress admin area.
 */

if (!defined('ABSPATH')) {
    exit;
}

function nucleus_dxp_register_product_cpt()
{
    $labels = array(
        'name' => _x('Product Manager', 'Post Type General Name', 'text_domain'),
        'singular_name' => _x('Product', 'Post Type Singular Name', 'text_domain'),
        'menu_name' => __('Product Manager', 'text_domain'),
        'name_admin_bar' => __('Product', 'text_domain'),
        'archives' => __('Product Archives', 'text_domain'),
        'attributes' => __('Product Attributes', 'text_domain'),
        'parent_item_colon' => __('Parent Product:', 'text_domain'),
        'all_items' => __('All Products', 'text_domain'),
        'add_new_item' => __('Add New Product', 'text_domain'),
        'add_new' => __('Add New', 'text_domain'),
        'new_item' => __('New Product', 'text_domain'),
        'edit_item' => __('Edit Product', 'text_domain'),
        'update_item' => __('Update Product', 'text_domain'),
        'view_item' => __('View Product', 'text_domain'),
        'view_items' => __('View Products', 'text_domain'),
        'search_items' => __('Search Product', 'text_domain'),
        'not_found' => __('Not found', 'text_domain'),
        'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
        'featured_image' => __('Product Image', 'text_domain'),
        'set_featured_image' => __('Set product image', 'text_domain'),
        'remove_featured_image' => __('Remove product image', 'text_domain'),
        'use_featured_image' => __('Use as product image', 'text_domain'),
        'insert_into_item' => __('Insert into product', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this product', 'text_domain'),
        'items_list' => __('Products list', 'text_domain'),
        'items_list_navigation' => __('Products list navigation', 'text_domain'),
        'filter_items_list' => __('Filter products list', 'text_domain'),
    );

    $args = array(
        'label' => __('Product', 'text_domain'),
        'description' => __('Manage products for the website', 'text_domain'),
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 30,
        'menu_icon' => 'dashicons-products', // Cube icon
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => false,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true, // Enables the Gutenberg editor (Block editor)
    );

    register_post_type('nucleus_product', $args);
}
add_action('init', 'nucleus_dxp_register_product_cpt', 0);

/**
 * =====================================
 * Meta Boxes for Subtitle & Price
 * =====================================
 */
function nucleus_product_meta_boxes()
{
    add_meta_box('nucleus_product_details', 'Product Details', 'nucleus_product_meta_box_html', 'nucleus_product', 'normal', 'high');
}
add_action('add_meta_boxes', 'nucleus_product_meta_boxes');

// Remove AJAX save as it's not needed (main post save handles it)

// Assessments Provided — reads from database (managed via Assessments settings page)
function nucleus_get_assessment_catalog()
{
    $saved = get_option('nucleus_assessment_catalog', null);

    // Build catalog from saved option
    if (!empty($saved) && is_array($saved)) {
        $catalog = array();
        foreach ($saved as $a) {
            if (!empty($a['key']) && !empty($a['label'])) {
                $catalog[$a['key']] = array(
                    'label' => $a['label'],
                    'icon_url' => isset($a['icon_url']) ? $a['icon_url'] : '',
                );
            }
        }
        if (!empty($catalog))
            return $catalog;
    }

    // Fallback defaults
    return array(
        'personality_assessment' => array(
            'label' => 'Personality Assessment',
            'icon_url' => NUCLEUS_DXP_URL . 'assets/icons/personality.svg',
        ),
        'work_styles_assessment' => array(
            'label' => 'Work Styles Assessment',
            'icon_url' => NUCLEUS_DXP_URL . 'assets/icons/workstyle.svg',
        ),
        'work_interest_assessment' => array(
            'label' => 'Work Interest Assessment',
            'icon_url' => NUCLEUS_DXP_URL . 'assets/icons/interest.svg',
        ),
        'numerical_assessment' => array(
            'label' => 'Numerical Assessment',
            'icon_url' => NUCLEUS_DXP_URL . 'assets/icons/numerical.svg',
        ),
    );
}

function nucleus_product_meta_box_html($post)
{
    wp_enqueue_media();
    $subtitle = get_post_meta($post->ID, '_nucleus_product_subtitle', true);
    $hero_summary = get_post_meta($post->ID, '_nucleus_product_hero_summary', true);
    $catalog = nucleus_get_assessment_catalog();

    // Packages array implementation
    $packages = get_post_meta($post->ID, '_nucleus_packages', true);
    if (empty($packages) || !is_array($packages)) {
        // Migrate old data
        $price = get_post_meta($post->ID, '_nucleus_product_price', true);
        $shopify_button = get_post_meta($post->ID, '_nucleus_product_shopify_button', true);
        $sec1_items = get_post_meta($post->ID, '_nucleus_product_section_1_items', true) ?: array();
        $packages = array(
            'basic' => array('price' => $price, 'shopify' => $shopify_button, 'sec1_items' => $sec1_items),
            'plus' => array('price' => '', 'shopify' => '', 'sec1_items' => array()),
            'max' => array('price' => '', 'shopify' => '', 'sec1_items' => array())
        );
    }
    // Ensure all keys
    foreach (array('basic', 'plus', 'max') as $k) {
        if (!isset($packages[$k])) $packages[$k] = array('price'=>'', 'shopify'=>'', 'sec1_items'=>array());
    }

    // New structured sections
    $sec2_title = get_post_meta($post->ID, '_nucleus_product_section_2_title', true);
    $sec2_items = get_post_meta($post->ID, '_nucleus_product_section_2_items', true) ?: array();
    $sec3_title = get_post_meta($post->ID, '_nucleus_product_section_3_title', true);
    $sec3_items = get_post_meta($post->ID, '_nucleus_product_section_3_items', true) ?: array();

    wp_nonce_field('nucleus_product_meta_box_nonce', 'nucleus_product_nonce');
    ?>

    <style>
        /* Tabs CSS */
        .n-tabs { display: flex; gap: 5px; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-top: 10px; }
        .n-tab { padding: 10px 18px; cursor: pointer; border: 1px solid transparent; border-bottom: none; background: #f1f1f1; border-radius: 4px 4px 0 0; font-weight: 600; font-size: 13px; color: #555; transition: background 0.15s; }
        .n-tab:hover { background: #e5e5e5; }
        .n-tab.active { background: #fff; border-color: #ddd; border-bottom-color: #fff; margin-bottom: -2px; color: #2271b1;}
        .n-tab-content { display: none; padding-top: 5px;}
        .n-tab-content.active { display: block; }
        
        .n-repeater-container {
            margin-top: 10px;
        }

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

        .n-repeater-row.is-dragging {
            opacity: 0.4;
        }

        .n-repeater-row.drag-over {
            border-top: 2px dashed #2271b1;
        }

        .n-repeater-row input[type="text"],
        .n-repeater-row textarea {
            width: 100%;
            margin-bottom: 5px;
        }

        .n-repeater-row .n-row-fields {
            flex-grow: 1;
        }

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

        .n-drag-handle:hover {
            color: #555;
            background: #eee;
        }

        .n-drag-handle:active {
            cursor: grabbing;
        }

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

        .n-repeater-remove {
            color: #d63638;
            cursor: pointer;
            font-weight: bold;
            background: none;
            border: none;
            padding: 5px;
        }

        .n-repeater-remove:hover {
            color: #a00;
        }

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

        .n-add-row-btn:hover {
            background: #135e96;
            color: #fff;
        }

        .n-section-title {
            font-size: 16px;
            font-weight: 600;
            padding: 10px 0 5px;
            border-bottom: 1px solid #eee;
            margin-bottom: 15px;
        }

        .n-sec1-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
        }

        .n-sec1-text-input {
            flex: 1;
        }

        .n-sec1-or {
            color: #888;
            font-size: 12px;
            font-style: italic;
            white-space: nowrap;
        }

        .n-sec1-assessment-select {
            flex: 0 0 180px;
            max-width: 180px;
        }

        /* Assessment Catalog Manager */
        .n-catalog-toggle { background: none; border: 1px solid #c3c4c7; padding: 6px 14px; cursor: pointer; font-size: 12px; color: #2271b1; border-radius: 3px; margin-top: 8px; margin-left: 8px; }
        .n-catalog-toggle:hover { background: #f0f0f1; color: #135e96; }
        .n-catalog-panel { display: none; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 12px; margin-top: 10px; }
        .n-catalog-panel.open { display: block; }
        .n-catalog-row { display: flex; align-items: center; gap: 10px; padding: 6px 0; border-bottom: 1px solid #eee; }
        .n-catalog-row:last-child { border-bottom: none; }
        .n-catalog-icon-preview { width: 28px; height: 28px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; padding: 2px; background: #fafafa; }
        .n-catalog-icon-placeholder { width: 28px; height: 28px; border: 1px dashed #ccc; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #bbb; font-size: 12px; background: #fafafa; }
        .n-catalog-label { flex: 1; font-size: 13px; }
        .n-catalog-label input { width: 100%; padding: 3px 6px; font-size: 13px; }
        .n-catalog-btn { background: #f0f0f1; border: 1px solid #c3c4c7; padding: 2px 8px; font-size: 11px; cursor: pointer; border-radius: 3px; color: #2271b1; }
        .n-catalog-btn:hover { background: #e5e5e5; color: #135e96; }
        .n-catalog-remove-icon { background: none; border: none; color: #d63638; font-size: 11px; cursor: pointer; padding: 2px 4px; }
        .n-catalog-remove-icon:hover { color: #a00; }
        .n-catalog-delete { background: none; border: none; color: #d63638; font-size: 16px; cursor: pointer; padding: 2px 6px; }
        .n-catalog-delete:hover { color: #a00; }
        .n-catalog-add-btn { margin-top: 8px; background: #f0f0f1; border: 1px solid #c3c4c7; padding: 4px 12px; font-size: 12px; cursor: pointer; border-radius: 3px; color: #2271b1; }
        .n-catalog-add-btn:hover { background: #e5e5e5; }
        .n-catalog-hint { color: #888; font-size: 11px; margin-top: 6px; }
    </style>

    <div class="n-section-title">Hero Details</div>
    <p>
        <label for="nucleus_product_subtitle"><strong>Subtitle:</strong></label><br>
        <input type="text" id="nucleus_product_subtitle" name="nucleus_product_subtitle"
            value="<?php echo esc_attr($subtitle); ?>" style="width:100%;" placeholder="e.g. Ignite Your Ambitions">
        <small>Displays below the main title on the product page.</small>
    </p>
    <p>
        <label for="nucleus_product_hero_summary"><strong>Hero Summary</strong> <em>(appears in the hero section next to the
                image)</em>:</label><br>
        <textarea id="nucleus_product_hero_summary" name="nucleus_product_hero_summary" rows="3" style="width:100%;"
            placeholder="A short overview of this product..."><?php echo esc_textarea($hero_summary); ?></textarea>
    </p>

    <!-- PACKAGES TABS -->
    <div class="n-section-title" style="margin-top: 30px; border-bottom: none; margin-bottom: 0;">Product Packages</div>
    <div class="n-tabs">
        <div class="n-tab active" onclick="nSwitchTab('basic', this)">Basic Package</div>
        <div class="n-tab" onclick="nSwitchTab('plus', this)">Plus Package</div>
        <div class="n-tab" onclick="nSwitchTab('max', this)">Max Package</div>
    </div>

    <?php 
    $package_keys = array('basic', 'plus', 'max');
    foreach ($package_keys as $pkg): 
        $pkg_data = $packages[$pkg];
        $is_active = $pkg === 'basic' ? ' active' : '';
    ?>
    <div class="n-tab-content<?php echo $is_active; ?>" id="n-tab-content-<?php echo $pkg; ?>">
        <p>
            <label><strong>Price (<?php echo ucfirst($pkg); ?> Package):</strong></label><br>
            <input type="text" name="n_pkg_<?php echo $pkg; ?>_price" value="<?php echo esc_attr($pkg_data['price']); ?>"
                style="width:100%;" placeholder="e.g. RM80.00 MYR">
        </p>
        <p>
            <label><strong>Shopify Buy Button Code:</strong></label><br>
            <textarea name="n_pkg_<?php echo $pkg; ?>_shopify" rows="3" style="width:100%;"
                placeholder="Paste the customized Shopify HTML embed code for this specific variation here."><?php echo esc_textarea($pkg_data['shopify']); ?></textarea>
        </p>

        <p>
            <label><strong>What You Will Receive (Assessments):</strong></label><br>
            <small>If you add an assessment to the Basic Package, it will automatically clone over to the Plus and Max packages as well to save you time.</small>
        </p>
        <div class="n-repeater-container" id="n-sec1-container-<?php echo $pkg; ?>">
            <?php $i = 1;
            foreach ($pkg_data['sec1_items'] as $item):
                $item_key = isset($item['key']) ? $item['key'] : '';
                $item_desc = isset($item['desc']) ? $item['desc'] : '';
                $item_text = isset($item['text']) ? $item['text'] : (is_string($item) ? $item : '');
                ?>
                <div class="n-repeater-row" draggable="true">
                    <span class="n-drag-handle" title="Drag to reorder">⠿</span>
                    <span class="n-row-number"><?php echo $i; ?></span>
                    <div class="n-row-fields">
                        <div class="n-sec1-inline">
                            <input type="hidden" name="n_pkg_<?php echo $pkg; ?>_sec1_text[]" value="<?php echo esc_attr($item_text); ?>" class="n-sec1-text-input">
                            
                            <select name="n_pkg_<?php echo $pkg; ?>_sec1_key[]" class="n-sec1-assessment-select" onchange="onAssessmentSelect(this)" style="min-width: 200px;">
                                <option value="">📋 Pick Assessment</option>
                                <?php if (empty($item_key) && !empty($item_text)): ?>
                                    <!-- Legacy custom text fallback -->
                                    <option value="" selected><?php echo esc_html($item_text); ?> (Custom)</option>
                                <?php endif; ?>
                                <?php foreach ($catalog as $key => $data): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($item_key, $key); ?>
                                        data-label="<?php echo esc_attr($data['label']); ?>">
                                        <?php echo esc_html($data['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <textarea name="n_pkg_<?php echo $pkg; ?>_sec1_desc[]" rows="2"
                            placeholder="Description (shown in popup)"><?php echo esc_textarea($item_desc); ?></textarea>
                    </div>
                    <button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button>
                </div>
                <?php $i++; endforeach; ?>
        </div>
        <button type="button" class="n-add-row-btn" onclick="addSec1Row('<?php echo $pkg; ?>')">+ Add Item</button>
    </div>
    <?php endforeach; ?>
    <button type="button" class="n-catalog-toggle" onclick="toggleCatalogPanel()">⚙ Manage Assessments</button>

    <div class="n-catalog-panel" id="n-catalog-panel">
        <strong>Assessment Catalog</strong>
        <p class="n-catalog-hint">Manage the assessments available in the dropdown above. Icons uploaded here apply to
            <b>all products</b>.</p>
        <div id="n-catalog-rows">
            <?php foreach ($catalog as $key => $data):
                $cat_icon = !empty($data['icon_url']) ? $data['icon_url'] : '';
                ?>
                <div class="n-catalog-row">
                    <?php if (!empty($cat_icon)): ?>
                        <img src="<?php echo esc_url($cat_icon); ?>" class="n-catalog-icon-preview">
                    <?php else: ?>
                        <div class="n-catalog-icon-placeholder">+</div>
                    <?php endif; ?>
                    <button type="button" class="n-catalog-btn"
                        onclick="nCatalogUploadIcon(this)"><?php echo !empty($cat_icon) ? 'Replace' : 'Upload'; ?></button>
                    <?php if (!empty($cat_icon)): ?>
                        <button type="button" class="n-catalog-remove-icon" onclick="nCatalogRemoveIcon(this)">✕</button>
                    <?php endif; ?>
                    <div class="n-catalog-label">
                        <input type="text" name="n_catalog_label[]" value="<?php echo esc_attr($data['label']); ?>"
                            placeholder="Assessment name">
                    </div>
                    <input type="hidden" name="n_catalog_key[]" value="<?php echo esc_attr($key); ?>">
                    <input type="hidden" name="n_catalog_icon_url[]" value="<?php echo esc_url($cat_icon); ?>">
                    <button type="button" class="n-catalog-delete" onclick="nCatalogDeleteRow(this)" title="Delete">✖</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="n-catalog-add-btn" onclick="nCatalogAddRow()">+ Add Assessment</button>
    </div>

    <!-- SECTION 2 -->
    <div class="n-section-title">Section 2 (Custom Layout Left)</div>
    <p>
        <label><strong>Section Title:</strong></label><br>
        <input type="text" name="n_sec2_title" value="<?php echo esc_attr($sec2_title); ?>" style="width:100%;"
            placeholder="e.g. The Framework & Audience">
    </p>
    <div class="n-repeater-container" id="n-sec2-container">
        <?php $i = 1;
        foreach ($sec2_items as $item): ?>
            <div class="n-repeater-row" draggable="true">
                <span class="n-drag-handle" title="Drag to reorder">⠿</span>
                <span class="n-row-number"><?php echo $i; ?></span>
                <div class="n-row-fields">
                    <input type="text" name="n_sec2_item_title[]" value="<?php echo esc_attr($item['title']); ?>"
                        placeholder="Item Title (Bold)">
                    <textarea name="n_sec2_item_desc[]" rows="2"
                        placeholder="Item Description"><?php echo esc_textarea($item['desc']); ?></textarea>
                </div>
                <button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button>
            </div>
            <?php $i++; endforeach; ?>
    </div>
    <button type="button" class="n-add-row-btn" onclick="addSec2Row()">+ Add Item</button>

    <!-- SECTION 3 -->
    <div class="n-section-title">Section 3 (Custom Layout Right)</div>
    <p>
        <label><strong>Section Title:</strong></label><br>
        <input type="text" name="n_sec3_title" value="<?php echo esc_attr($sec3_title); ?>" style="width:100%;"
            placeholder="e.g. The Transformation & Impact">
    </p>
    <div class="n-repeater-container" id="n-sec3-container">
        <?php $i = 1;
        foreach ($sec3_items as $item): ?>
            <div class="n-repeater-row" draggable="true">
                <span class="n-drag-handle" title="Drag to reorder">⠿</span>
                <span class="n-row-number"><?php echo $i; ?></span>
                <div class="n-row-fields">
                    <input type="text" name="n_sec3_item_title[]" value="<?php echo esc_attr($item['title']); ?>"
                        placeholder="Item Title (Bold)">
                    <textarea name="n_sec3_item_desc[]" rows="2"
                        placeholder="Item Description"><?php echo esc_textarea($item['desc']); ?></textarea>
                </div>
                <button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button>
            </div>
            <?php $i++; endforeach; ?>
    </div>
    <button type="button" class="n-add-row-btn" onclick="addSec3Row()">+ Add Item</button>


    <script>
        var nCatalogOptions = '';
        <?php foreach ($catalog as $key => $data): ?>
            nCatalogOptions += '<option value="<?php echo esc_attr($key); ?>" data-label="<?php echo esc_attr($data['label']); ?>"><?php echo esc_html($data['label']); ?></option>';
        <?php endforeach; ?>

        function onAssessmentSelect(select) {
            var row = select.closest('.n-repeater-row');
            var textInput = row.querySelector('.n-sec1-text-input');
            var opt = select.options[select.selectedIndex];
            if (select.value && opt) {
                textInput.value = opt.getAttribute('data-label') || opt.textContent.trim();
            }
        }

        // --- Catalog Manager ---
        function toggleCatalogPanel() {
            document.getElementById('n-catalog-panel').classList.toggle('open');
        }

        function nCatalogUploadIcon(btn) {
            var row = btn.closest('.n-catalog-row');
            var frame = wp.media({ title: 'Select Assessment Icon', button: { text: 'Use this icon' }, multiple: false });
            frame.on('select', function () {
                var url = frame.state().get('selection').first().toJSON().url;
                row.querySelector('input[name="n_catalog_icon_url[]"]').value = url;
                var prev = row.querySelector('.n-catalog-icon-preview');
                var ph = row.querySelector('.n-catalog-icon-placeholder');
                if (prev) { prev.src = url; }
                else if (ph) { var img = document.createElement('img'); img.src = url; img.className = 'n-catalog-icon-preview'; ph.parentNode.replaceChild(img, ph); }
                btn.textContent = 'Replace';
                if (!row.querySelector('.n-catalog-remove-icon')) {
                    var rm = document.createElement('button'); rm.type = 'button'; rm.className = 'n-catalog-remove-icon'; rm.textContent = '✕'; rm.onclick = function () { nCatalogRemoveIcon(rm); }; btn.after(rm);
                }
            });
            frame.open();
        }

        function nCatalogRemoveIcon(btn) {
            var row = btn.closest('.n-catalog-row');
            row.querySelector('input[name="n_catalog_icon_url[]"]').value = '';
            var prev = row.querySelector('.n-catalog-icon-preview');
            if (prev) { var ph = document.createElement('div'); ph.className = 'n-catalog-icon-placeholder'; ph.textContent = '+'; prev.parentNode.replaceChild(ph, prev); }
            var uploadBtn = row.querySelector('.n-catalog-btn');
            if (uploadBtn) uploadBtn.textContent = 'Upload';
            btn.remove();
        }

        function nCatalogDeleteRow(btn) {
            if (!confirm('Remove this assessment from the catalog?')) return;
            btn.closest('.n-catalog-row').remove();
            updateCatalogDropdowns();
        }

        function nCatalogAddRow() {
            var container = document.getElementById('n-catalog-rows');
            var div = document.createElement('div'); div.className = 'n-catalog-row';
            div.innerHTML = '<div class="n-catalog-icon-placeholder">+</div>'
                + '<button type="button" class="n-catalog-btn" onclick="nCatalogUploadIcon(this)">Upload</button>'
                + '<div class="n-catalog-label"><input type="text" name="n_catalog_label[]" placeholder="e.g. Leadership Assessment"></div>'
                + '<input type="hidden" name="n_catalog_key[]" value="">'
                + '<input type="hidden" name="n_catalog_icon_url[]" value="">'
                + '<button type="button" class="n-catalog-delete" onclick="nCatalogDeleteRow(this)" title="Delete">✖</button>';
            container.appendChild(div);
            updateCatalogDropdowns();
        }

        // Dynamically rebuild the assessment dropdowns based on inputs in the catalog manager
        function updateCatalogDropdowns() {
            var panel = document.getElementById('n-catalog-panel');
            var labels = panel.querySelectorAll('input[name="n_catalog_label[]"]');
            var keys = panel.querySelectorAll('input[name="n_catalog_key[]"]');
            
            nCatalogOptions = '';
            for (var i = 0; i < labels.length; i++) {
                var label = labels[i].value.trim();
                if (!label) continue;
                
                var key = keys[i].value;
                if (!key) {
                    // Generate a fake string key to link selection if not already saved
                    key = label.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
                    keys[i].value = key;
                }
                
                nCatalogOptions += '<option value="' + key + '" data-label="' + label + '">' + label + '</option>';
            }
            
            var selects = document.querySelectorAll('.n-sec1-assessment-select');
            selects.forEach(function(sel){
                var val = sel.value;
                var isCustom = (val === '' && sel.options[sel.selectedIndex] && sel.options[sel.selectedIndex].text.indexOf('(Custom)') !== -1);
                var customHtml = isCustom ? '<option value="" selected>' + sel.options[sel.selectedIndex].text + '</option>' : '';
                sel.innerHTML = '<option value="">📋 Pick Assessment</option>' + customHtml + nCatalogOptions;
                if (!isCustom) {
                    sel.value = val;
                }
            });
        }

        // Rebuild dropdowns reactively when the user types
        document.addEventListener('DOMContentLoaded', function() {
            var catalogPanel = document.getElementById('n-catalog-panel');
            if(catalogPanel) {
                catalogPanel.addEventListener('input', function(e){
                    if(e.target.name === 'n_catalog_label[]') {
                        updateCatalogDropdowns();
                    }
                });
            }
        });

        function renumberRows(containerId) {
            var rows = document.getElementById(containerId).querySelectorAll('.n-repeater-row');
            rows.forEach(function (row, index) {
                var badge = row.querySelector('.n-row-number');
                if (badge) badge.textContent = index + 1;
            });
        }
        function removeRow(btn) {
            var container = btn.closest('.n-repeater-container');
            btn.parentElement.remove();
            renumberRows(container.id);
        }
        function nSwitchTab(pkg, tabEl) {
            document.querySelectorAll('.n-tab-content').forEach(function(c) { c.classList.remove('active'); });
            document.querySelectorAll('.n-tab').forEach(function(t) { t.classList.remove('active'); });
            document.getElementById('n-tab-content-' + pkg).classList.add('active');
            tabEl.classList.add('active');
        }

        function addSec1Row(sourcePkg) {
            // First append to the current active tab
            appendRowToPkg(sourcePkg);
            // If they are adding to basic, let's instantly duplicate it for the higher packages!
            if (sourcePkg === 'basic') {
                appendRowToPkg('plus');
                appendRowToPkg('max');
            }
        }

        function appendRowToPkg(pkg) {
            var container = document.getElementById('n-sec1-container-' + pkg);
            if (!container) return;
            var num = container.querySelectorAll('.n-repeater-row').length + 1;
            var html = '<div class="n-repeater-row" draggable="true"><span class="n-drag-handle" title="Drag to reorder">⠿</span><span class="n-row-number">' + num + '</span><div class="n-row-fields">'
                + '<div class="n-sec1-inline"><input type="hidden" name="n_pkg_' + pkg + '_sec1_text[]" class="n-sec1-text-input"><select name="n_pkg_' + pkg + '_sec1_key[]" class="n-sec1-assessment-select" onchange="onAssessmentSelect(this)" style="min-width: 200px;"><option value="">📋 Pick Assessment</option>' + nCatalogOptions + '</select></div>'
                + '<textarea name="n_pkg_' + pkg + '_sec1_desc[]" rows="2" placeholder="Description (shown in popup)"></textarea>'
                + '</div><button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button></div>';
            container.insertAdjacentHTML('beforeend', html);
            initDragDrop(container);
        }
        function addSec2Row() {
            var container = document.getElementById('n-sec2-container');
            var num = container.querySelectorAll('.n-repeater-row').length + 1;
            var html = '<div class="n-repeater-row" draggable="true"><span class="n-drag-handle" title="Drag to reorder">⠿</span><span class="n-row-number">' + num + '</span><div class="n-row-fields"><input type="text" name="n_sec2_item_title[]" placeholder="Item Title (Bold)"><textarea name="n_sec2_item_desc[]" rows="2" placeholder="Item Description"></textarea></div><button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button></div>';
            container.insertAdjacentHTML('beforeend', html);
            initDragDrop(container);
        }
        function addSec3Row() {
            var container = document.getElementById('n-sec3-container');
            var num = container.querySelectorAll('.n-repeater-row').length + 1;
            var html = '<div class="n-repeater-row" draggable="true"><span class="n-drag-handle" title="Drag to reorder">⠿</span><span class="n-row-number">' + num + '</span><div class="n-row-fields"><input type="text" name="n_sec3_item_title[]" placeholder="Item Title (Bold)"><textarea name="n_sec3_item_desc[]" rows="2" placeholder="Item Description"></textarea></div><button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button></div>';
            container.insertAdjacentHTML('beforeend', html);
            initDragDrop(container);
        }

        // --- Drag and Drop ---
        var dragSrcEl = null;
        function initDragDrop(container) {
            var rows = container.querySelectorAll('.n-repeater-row');
            rows.forEach(function (row) {
                row.removeEventListener('dragstart', handleDragStart);
                row.removeEventListener('dragover', handleDragOver);
                row.removeEventListener('dragleave', handleDragLeave);
                row.removeEventListener('drop', handleDrop);
                row.removeEventListener('dragend', handleDragEnd);
                row.addEventListener('dragstart', handleDragStart);
                row.addEventListener('dragover', handleDragOver);
                row.addEventListener('dragleave', handleDragLeave);
                row.addEventListener('drop', handleDrop);
                row.addEventListener('dragend', handleDragEnd);
            });
        }
        function handleDragStart(e) {
            dragSrcEl = this;
            this.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.outerHTML);
        }
        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            if (this !== dragSrcEl) {
                this.classList.add('drag-over');
            }
            return false;
        }
        function handleDragLeave() {
            this.classList.remove('drag-over');
        }
        function handleDrop(e) {
            e.stopPropagation();
            if (dragSrcEl !== this) {
                var container = this.closest('.n-repeater-container');
                var allRows = Array.from(container.querySelectorAll('.n-repeater-row'));
                var fromIdx = allRows.indexOf(dragSrcEl);
                var toIdx = allRows.indexOf(this);
                if (fromIdx < toIdx) {
                    this.parentNode.insertBefore(dragSrcEl, this.nextSibling);
                } else {
                    this.parentNode.insertBefore(dragSrcEl, this);
                }
                renumberRows(container.id);
            }
            this.classList.remove('drag-over');
            return false;
        }
        function handleDragEnd() {
            this.classList.remove('is-dragging');
            var container = this.closest('.n-repeater-container');
            container.querySelectorAll('.n-repeater-row').forEach(function (r) {
                r.classList.remove('drag-over');
            });
        }

        // Init drag on page load
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.n-repeater-container').forEach(function (c) {
                initDragDrop(c);
            });
        });
    </script>
    <?php
}

function nucleus_save_product_meta($post_id)
{
    if (!isset($_POST['nucleus_product_nonce']) || !wp_verify_nonce($_POST['nucleus_product_nonce'], 'nucleus_product_meta_box_nonce'))
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!current_user_can('edit_post', $post_id))
        return;

    // Basic meta
    if (isset($_POST['nucleus_product_subtitle']))
        update_post_meta($post_id, '_nucleus_product_subtitle', sanitize_text_field($_POST['nucleus_product_subtitle']));
    if (isset($_POST['nucleus_product_hero_summary']))
        update_post_meta($post_id, '_nucleus_product_hero_summary', sanitize_textarea_field($_POST['nucleus_product_hero_summary']));

    // Save Assessment Catalog (global, shared across all products)
    if (isset($_POST['n_catalog_label'])) {
        $cat_labels = $_POST['n_catalog_label'];
        $cat_keys = isset($_POST['n_catalog_key']) ? $_POST['n_catalog_key'] : array();
        $cat_icons = isset($_POST['n_catalog_icon_url']) ? $_POST['n_catalog_icon_url'] : array();
        $new_catalog = array();
        for ($c = 0; $c < count($cat_labels); $c++) {
            $cl = sanitize_text_field($cat_labels[$c]);
            if (empty($cl))
                continue;
            $ck = !empty($cat_keys[$c]) ? sanitize_text_field($cat_keys[$c]) : sanitize_title($cl);
            $ck = str_replace('-', '_', $ck);
            $ci = isset($cat_icons[$c]) ? esc_url_raw($cat_icons[$c]) : '';
            // Explicitly set string key here
            $new_catalog[$ck] = array('key' => $ck, 'label' => $cl, 'icon_url' => $ci);
        }
        if (!empty($new_catalog)) {
            update_option('nucleus_assessment_catalog', $new_catalog);
        }
    }

    // Save Packages
    $package_keys = array('basic', 'plus', 'max');
    $packages = array();
    $all_assessments = array(); // For backward compatibility 

    // Catalog handles validity 
    $catalog = nucleus_get_assessment_catalog();
    $valid_keys = array_keys($catalog);

    foreach ($package_keys as $pkg) {
        $price = isset($_POST['n_pkg_' . $pkg . '_price']) ? sanitize_text_field($_POST['n_pkg_' . $pkg . '_price']) : '';
        $shopify = isset($_POST['n_pkg_' . $pkg . '_shopify']) ? wp_unslash($_POST['n_pkg_' . $pkg . '_shopify']) : '';
        
        $texts = isset($_POST['n_pkg_' . $pkg . '_sec1_text']) ? $_POST['n_pkg_' . $pkg . '_sec1_text'] : array();
        $keys = isset($_POST['n_pkg_' . $pkg . '_sec1_key']) ? $_POST['n_pkg_' . $pkg . '_sec1_key'] : array();
        $descs = isset($_POST['n_pkg_' . $pkg . '_sec1_desc']) ? $_POST['n_pkg_' . $pkg . '_sec1_desc'] : array();
        
        $sec1_items = array();
        
        for ($i = 0; $i < count($texts); $i++) {
            $txt = sanitize_text_field($texts[$i]);
            $k = isset($keys[$i]) ? sanitize_text_field($keys[$i]) : '';
            $d = isset($descs[$i]) ? sanitize_textarea_field($descs[$i]) : '';
            // Determine type: if valid assessment key selected, it's assessment
            if (!empty($k) && in_array($k, $valid_keys)) {
                $sec1_items[] = array('type' => 'assessment', 'key' => $k, 'desc' => $d, 'text' => $txt);
                $all_assessments[$k] = true;
            } elseif (!empty($txt)) {
                $sec1_items[] = array('type' => 'custom', 'key' => '', 'desc' => $d, 'text' => $txt);
            }
        }
        
        $packages[$pkg] = array(
            'price' => $price,
            'shopify' => $shopify,
            'sec1_items' => $sec1_items
        );
    }
    update_post_meta($post_id, '_nucleus_packages', $packages);
    update_post_meta($post_id, '_nucleus_product_assessment_types', array_keys($all_assessments));
    
    // Also store basic price + shopify + sec1 items backward compatibility tags in case any old code fails 
    if(isset($packages['basic'])){
        update_post_meta($post_id, '_nucleus_product_price', sanitize_text_field($packages['basic']['price']));
        update_post_meta($post_id, '_nucleus_product_shopify_button', wp_unslash($packages['basic']['shopify']));
        update_post_meta($post_id, '_nucleus_product_section_1_items', $packages['basic']['sec1_items']);
    }

    // Section 2 Repeater
    if (isset($_POST['n_sec2_title']))
        update_post_meta($post_id, '_nucleus_product_section_2_title', sanitize_text_field($_POST['n_sec2_title']));
    if (isset($_POST['n_sec2_item_title']) && isset($_POST['n_sec2_item_desc'])) {
        $titles = $_POST['n_sec2_item_title'];
        $descs = $_POST['n_sec2_item_desc'];
        $items = array();
        for ($i = 0; $i < count($titles); $i++) {
            if (!empty(trim($titles[$i]))) {
                $items[] = array('title' => sanitize_text_field($titles[$i]), 'desc' => sanitize_textarea_field($descs[$i]));
            }
        }
        update_post_meta($post_id, '_nucleus_product_section_2_items', $items);
    } else {
        delete_post_meta($post_id, '_nucleus_product_section_2_items');
    }

    // Section 3 Repeater
    if (isset($_POST['n_sec3_title']))
        update_post_meta($post_id, '_nucleus_product_section_3_title', sanitize_text_field($_POST['n_sec3_title']));
    if (isset($_POST['n_sec3_item_title']) && isset($_POST['n_sec3_item_desc'])) {
        $titles = $_POST['n_sec3_item_title'];
        $descs = $_POST['n_sec3_item_desc'];
        $items = array();
        for ($i = 0; $i < count($titles); $i++) {
            if (!empty(trim($titles[$i]))) {
                $items[] = array('title' => sanitize_text_field($titles[$i]), 'desc' => sanitize_textarea_field($descs[$i]));
            }
        }
        update_post_meta($post_id, '_nucleus_product_section_3_items', $items);
    } else {
        delete_post_meta($post_id, '_nucleus_product_section_3_items');
    }
}
add_action('save_post_nucleus_product', 'nucleus_save_product_meta');

/**
 * =====================================
 * Shortcode: [nucleus_single_product]
 * =====================================
 * Usage in Oxygen: Add a "Shortcode" element → paste [nucleus_single_product]
 * 
 * It auto-detects the current product if on a product page,
 * or you can pass a specific product: [nucleus_single_product id="123"]
 */
function nucleus_single_product_shortcode($atts)
{
    $atts = shortcode_atts(array('id' => 0), $atts);
    $product_id = intval($atts['id']);

    // Auto-detect current product if no ID given
    if (!$product_id) {
        global $post;
        if ($post && $post->post_type === 'nucleus_product') {
            $product_id = $post->ID;
        }
    }

    if (!$product_id) {
        return '<p style="text-align:center;padding:40px;color:#888;">No product found. Please specify an ID: [nucleus_single_product id="123"]</p>';
    }

    // Enqueue CSS when shortcode is used
    wp_enqueue_style(
        'nucleus-single-product',
        NUCLEUS_DXP_URL . 'assets/css/single-product.css',
        array(),
        filemtime(NUCLEUS_DXP_PATH . 'assets/css/single-product.css')
    );

    // Get product data
    $product = get_post($product_id);
    if (!$product)
        return '<p>Product not found.</p>';

    $title = get_the_title($product_id);
    $subtitle = get_post_meta($product_id, '_nucleus_product_subtitle', true);
    $price = get_post_meta($product_id, '_nucleus_product_price', true);
    $hero_summary = get_post_meta($product_id, '_nucleus_product_hero_summary', true);
    
    wp_nonce_field('nucleus_product_meta_box_nonce', 'nucleus_product_nonce');
    wp_nonce_field('nucleus_catalog_nonce', 'nucleus_catalog_nonce_field');

    // Retrieve existing assigned assessments
    $assigned_assessments = get_post_meta($product_id, '_nucleus_product_assessment_types', true);
    if (!is_array($assigned_assessments)) {
        $assigned_assessments = array();
    }
    $shopify_button = get_post_meta($product_id, '_nucleus_product_shopify_button', true);
    $thumbnail_url = get_the_post_thumbnail_url($product_id, 'full');
    $content = apply_filters('the_content', $product->post_content);

    ob_start();
    include NUCLEUS_DXP_PATH . 'templates/single-product.php';
    return ob_get_clean();
}
add_shortcode('nucleus_single_product', 'nucleus_single_product_shortcode');
// Template rendering is handled by Oxygen Builder.
// Use [nucleus_single_product] shortcode inside Oxygen editor.

/**
 * =====================================
 * Auto-Setup Oxygen for New Products
 * =====================================
 * Automatically assigns the "Header Footer" template (ID: 36)
 * and sets our shortcode as the Oxygen content when a product
 * is created or published. No manual Oxygen setup needed.
 */
function nucleus_auto_setup_oxygen_template($post_id)
{
    // Only for our products
    if (get_post_type($post_id) !== 'nucleus_product')
        return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // Auto-assign Header Footer template (only if not already set)
    if (!get_post_meta($post_id, 'ct_other_template', true)) {
        update_post_meta($post_id, 'ct_other_template', '36');
    }

    // Auto-set Oxygen content with our shortcode (only if not edited in Oxygen yet)
    if (!get_post_meta($post_id, 'ct_builder_shortcodes', true)) {
        update_post_meta($post_id, 'ct_builder_shortcodes', '[nucleus_single_product]');
    }
}
add_action('save_post', 'nucleus_auto_setup_oxygen_template', 20);

/**
 * =====================================
 * Shortcode: [nucleus_products_landing]
 * =====================================
 * Renders a landing page with all products.
 * Usage: Create a page → add this shortcode.
 */
function nucleus_products_landing_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'title' => 'Self-Discovery Assessments',
        'subtitle' => 'Unlock your potential with our premium psychometric assessments',
    ), $atts);

    // Enqueue CSS
    wp_enqueue_style('nucleus-products-landing', NUCLEUS_DXP_URL . 'assets/css/products-landing.css', array(), '3.3');

    // Get all products
    $products = get_posts(array(
        'post_type' => 'nucleus_product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'ASC',
    ));

    ob_start();
    include NUCLEUS_DXP_PATH . 'templates/products-landing.php';
    return ob_get_clean();
}
add_shortcode('nucleus_products_landing', 'nucleus_products_landing_shortcode');
