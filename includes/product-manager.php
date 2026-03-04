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

// Assessments Provided
function nucleus_get_assessment_catalog() {
    return array(
        'personality_assessment' => array(
            'label' => 'Personality Assessment',
            'icon'  => 'personality.svg',
        ),
        'work_styles_assessment' => array(
            'label' => 'Work Styles Assessment',
            'icon'  => 'workstyle.svg',
        ),
        'work_interest_assessment' => array(
            'label' => 'Work Interest Assessment',
            'icon'  => 'interest.svg',
        ),
        'numerical_assessment' => array(
            'label' => 'Numerical Assessment',
            'icon'  => 'numerical.svg',
        ),
    );
}

function nucleus_product_meta_box_html($post)
{
    $subtitle = get_post_meta($post->ID, '_nucleus_product_subtitle', true);
    $price = get_post_meta($post->ID, '_nucleus_product_price', true);
    $hero_summary = get_post_meta($post->ID, '_nucleus_product_hero_summary', true);
    $assessment_types = get_post_meta($post->ID, '_nucleus_product_assessment_types', true);
    $catalog = nucleus_get_assessment_catalog();
    $shopify_button = get_post_meta($post->ID, '_nucleus_product_shopify_button', true);

    // New structured sections
    $sec1_items = get_post_meta($post->ID, '_nucleus_product_section_1_items', true) ?: array();
    $sec2_title = get_post_meta($post->ID, '_nucleus_product_section_2_title', true);
    $sec2_items = get_post_meta($post->ID, '_nucleus_product_section_2_items', true) ?: array();
    $sec3_title = get_post_meta($post->ID, '_nucleus_product_section_3_title', true);
    $sec3_items = get_post_meta($post->ID, '_nucleus_product_section_3_items', true) ?: array();

    wp_nonce_field('nucleus_product_meta_box_nonce', 'nucleus_product_nonce');

    if (!is_array($assessment_types)) {
        $assessment_types = array();
    }
    ?>

    <style>
        .n-repeater-container { margin-top: 10px; }
        .n-repeater-row { background: #f9f9f9; border: 1px solid #ccd0d4; padding: 10px 10px 10px 50px; margin-bottom: 8px; display: flex; gap: 10px; align-items: flex-start; position: relative;}
        .n-repeater-row input[type="text"], .n-repeater-row textarea { width: 100%; margin-bottom: 5px; }
        .n-repeater-row .n-row-fields { flex-grow: 1; }
        .n-row-number { position: absolute; left: 10px; top: 12px; width: 28px; height: 28px; background: #2271b1; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; }
        .n-repeater-remove { color: #d63638; cursor: pointer; font-weight: bold; background: none; border: none; padding: 5px; }
        .n-repeater-remove:hover { color: #a00; }
        .n-add-row-btn { display: inline-block; margin-bottom: 20px; background: #2271b1; color: #fff; border: none; padding: 6px 14px; border-radius: 3px; cursor: pointer; text-decoration: none; font-size: 13px;}
        .n-add-row-btn:hover { background: #135e96; color: #fff; }
        .n-section-title { font-size: 16px; font-weight: 600; padding: 10px 0 5px; border-bottom: 1px solid #eee; margin-bottom: 15px;}
    </style>

    <div class="n-section-title">Hero Details</div>
    <p>
        <label for="nucleus_product_subtitle"><strong>Subtitle:</strong></label><br>
        <input type="text" id="nucleus_product_subtitle" name="nucleus_product_subtitle"
            value="<?php echo esc_attr($subtitle); ?>" style="width:100%;" placeholder="e.g. Ignite Your Ambitions">
        <small>Displays below the main title on the product page.</small>
    </p>
    <p>
        <label for="nucleus_product_price"><strong>Price:</strong></label><br>
        <input type="text" id="nucleus_product_price" name="nucleus_product_price" value="<?php echo esc_attr($price); ?>"
            style="width:100%;" placeholder="e.g. RM80.00 MYR">
        <small>Displays as a large price tag. Leave blank if not needed.</small>
    </p>
    <p>
        <label for="nucleus_product_hero_summary"><strong>Hero Summary</strong> <em>(appears in the hero section next to the image)</em>:</label><br>
        <textarea id="nucleus_product_hero_summary" name="nucleus_product_hero_summary" rows="3" style="width:100%;"
            placeholder="A short overview of this product..."><?php echo esc_textarea($hero_summary); ?></textarea>
    </p>

    <!-- SECTION 1 -->
    <div class="n-section-title">Section 1: What You Will Receive</div>
    <p><small>Note: If you fill in these 3 sections, the main WordPress content editor is ignored (Safe to use!).</small></p>
    <div class="n-repeater-container" id="n-sec1-container">
        <?php $i = 1; foreach ($sec1_items as $item): ?>
            <div class="n-repeater-row">
                <span class="n-row-number"><?php echo $i; ?></span>
                <div class="n-row-fields">
                    <input type="text" name="n_sec1_item[]" value="<?php echo esc_attr($item); ?>" placeholder="e.g. Compiled Report of Your Assessments">
                </div>
                <button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button>
            </div>
        <?php $i++; endforeach; ?>
    </div>
    <button type="button" class="n-add-row-btn" onclick="addSec1Row()">+ Add Item</button>

    <!-- SECTION 2 -->
    <div class="n-section-title">Section 2 (Custom Layout Left)</div>
    <p>
        <label><strong>Section Title:</strong></label><br>
        <input type="text" name="n_sec2_title" value="<?php echo esc_attr($sec2_title); ?>" style="width:100%;" placeholder="e.g. The Framework & Audience">
    </p>
    <div class="n-repeater-container" id="n-sec2-container">
        <?php $i = 1; foreach ($sec2_items as $item): ?>
            <div class="n-repeater-row">
                <span class="n-row-number"><?php echo $i; ?></span>
                <div class="n-row-fields">
                    <input type="text" name="n_sec2_item_title[]" value="<?php echo esc_attr($item['title']); ?>" placeholder="Item Title (Bold)">
                    <textarea name="n_sec2_item_desc[]" rows="2" placeholder="Item Description"><?php echo esc_textarea($item['desc']); ?></textarea>
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
        <input type="text" name="n_sec3_title" value="<?php echo esc_attr($sec3_title); ?>" style="width:100%;" placeholder="e.g. The Transformation & Impact">
    </p>
    <div class="n-repeater-container" id="n-sec3-container">
        <?php $i = 1; foreach ($sec3_items as $item): ?>
            <div class="n-repeater-row">
                <span class="n-row-number"><?php echo $i; ?></span>
                <div class="n-row-fields">
                    <input type="text" name="n_sec3_item_title[]" value="<?php echo esc_attr($item['title']); ?>" placeholder="Item Title (Bold)">
                    <textarea name="n_sec3_item_desc[]" rows="2" placeholder="Item Description"><?php echo esc_textarea($item['desc']); ?></textarea>
                </div>
                <button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button>
            </div>
        <?php $i++; endforeach; ?>
    </div>
    <button type="button" class="n-add-row-btn" onclick="addSec3Row()">+ Add Item</button>


    <div class="n-section-title">Assessments & Integrations</div>
    <p>
        <label><strong>Assessments Included</strong></label><br>
        <?php foreach ($catalog as $key => $data) : ?>
            <label style="display:block; margin-bottom:6px;">
                <input type="checkbox"
                       name="nucleus_product_assessment_types[]"
                       value="<?php echo esc_attr($key); ?>"
                       <?php checked(in_array($key, $assessment_types)); ?>>

                <?php
                $icon_url = NUCLEUS_DXP_URL . 'assets/icons/' . $data['icon'];
                ?>
                <img src="<?php echo esc_url($icon_url); ?>" 
                     style="width:16px;height:16px;vertical-align:middle;margin-right:6px;">
                <?php echo esc_html($data['label']); ?>
            </label>
        <?php endforeach; ?>
    </p>
    <p>
        <label for="nucleus_product_shopify_button"><strong>🛒 Shopify Buy Button Code:</strong></label><br>
        <textarea id="nucleus_product_shopify_button" name="nucleus_product_shopify_button" rows="6"
            style="width:100%; font-family:monospace; font-size:12px;"
            placeholder="Paste your Shopify Buy Button embed code here..."><?php echo esc_textarea($shopify_button); ?></textarea>
    </p>

    <script>
        function renumberRows(containerId) {
            var rows = document.getElementById(containerId).querySelectorAll('.n-repeater-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.n-row-number');
                if (badge) badge.textContent = index + 1;
            });
        }
        function removeRow(btn) {
            var container = btn.closest('.n-repeater-container');
            btn.parentElement.remove();
            renumberRows(container.id);
        }
        function addSec1Row() {
            var container = document.getElementById('n-sec1-container');
            var num = container.querySelectorAll('.n-repeater-row').length + 1;
            var html = '<div class="n-repeater-row"><span class="n-row-number">' + num + '</span><div class="n-row-fields"><input type="text" name="n_sec1_item[]" placeholder="Item Text"></div><button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button></div>';
            container.insertAdjacentHTML('beforeend', html);
        }
        function addSec2Row() {
            var container = document.getElementById('n-sec2-container');
            var num = container.querySelectorAll('.n-repeater-row').length + 1;
            var html = '<div class="n-repeater-row"><span class="n-row-number">' + num + '</span><div class="n-row-fields"><input type="text" name="n_sec2_item_title[]" placeholder="Item Title (Bold)"><textarea name="n_sec2_item_desc[]" rows="2" placeholder="Item Description"></textarea></div><button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button></div>';
            container.insertAdjacentHTML('beforeend', html);
        }
        function addSec3Row() {
            var container = document.getElementById('n-sec3-container');
            var num = container.querySelectorAll('.n-repeater-row').length + 1;
            var html = '<div class="n-repeater-row"><span class="n-row-number">' + num + '</span><div class="n-row-fields"><input type="text" name="n_sec3_item_title[]" placeholder="Item Title (Bold)"><textarea name="n_sec3_item_desc[]" rows="2" placeholder="Item Description"></textarea></div><button type="button" class="n-repeater-remove" onclick="removeRow(this)">✖</button></div>';
            container.insertAdjacentHTML('beforeend', html);
        }
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
    if (isset($_POST['nucleus_product_subtitle'])) update_post_meta($post_id, '_nucleus_product_subtitle', sanitize_text_field($_POST['nucleus_product_subtitle']));
    if (isset($_POST['nucleus_product_price'])) update_post_meta($post_id, '_nucleus_product_price', sanitize_text_field($_POST['nucleus_product_price']));
    if (isset($_POST['nucleus_product_hero_summary'])) update_post_meta($post_id, '_nucleus_product_hero_summary', sanitize_textarea_field($_POST['nucleus_product_hero_summary']));
    
    // Assessments
    if (isset($_POST['nucleus_product_assessment_types']) && is_array($_POST['nucleus_product_assessment_types'])) {
        $catalog = nucleus_get_assessment_catalog();
        $valid_keys = array_keys($catalog);
        $submitted = array_map('sanitize_text_field', $_POST['nucleus_product_assessment_types']);
        $sanitized_assessments = array_intersect($submitted, $valid_keys);
        update_post_meta($post_id, '_nucleus_product_assessment_types', $sanitized_assessments);
    } else {
        delete_post_meta($post_id, '_nucleus_product_assessment_types');
    }

    // Shopify button
    if (isset($_POST['nucleus_product_shopify_button'])) {
        update_post_meta($post_id, '_nucleus_product_shopify_button', wp_unslash($_POST['nucleus_product_shopify_button']));
    }

    // Section 1 Repeater
    if (isset($_POST['n_sec1_item'])) {
        $sec1_items = array_filter(array_map('sanitize_text_field', $_POST['n_sec1_item']));
        update_post_meta($post_id, '_nucleus_product_section_1_items', $sec1_items);
    } else {
        delete_post_meta($post_id, '_nucleus_product_section_1_items');
    }

    // Section 2 Repeater
    if (isset($_POST['n_sec2_title'])) update_post_meta($post_id, '_nucleus_product_section_2_title', sanitize_text_field($_POST['n_sec2_title']));
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
    if (isset($_POST['n_sec3_title'])) update_post_meta($post_id, '_nucleus_product_section_3_title', sanitize_text_field($_POST['n_sec3_title']));
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
    $assessment_types = get_post_meta($product_id, '_nucleus_product_assessment_types', true);
    if (!is_array($assessment_types)) {
      $assessment_types = array();
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
