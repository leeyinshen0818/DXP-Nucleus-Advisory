<?php

/**
 * Page Manager (Custom Post Type)
 * ==================================
 * Registers the 'nucleus_page' post type to create a 
 * headless Page Manager dashboard. 
 * Allows dynamic creation of sections, components, and CSS.
 */

if (!defined('ABSPATH')) {
  exit;
}

function nucleus_dxp_register_page_cpt()
{
  $labels = array(
    'name'               => _x('Page Manager', 'Post Type General Name', 'text_domain'),
    'singular_name'      => _x('Page', 'Post Type Singular Name', 'text_domain'),
    'menu_name'          => __('Page Manager', 'text_domain'),
    'name_admin_bar'     => __('Page', 'text_domain'),
    'all_items'          => __('All Pages', 'text_domain'),
    'add_new_item'       => __('Add New Page', 'text_domain'),
    'add_new'            => __('Add New', 'text_domain'),
    'new_item'           => __('New Page', 'text_domain'),
    'edit_item'          => __('Edit Page', 'text_domain'),
    'update_item'        => __('Update Page', 'text_domain'),
    'view_item'          => __('View Page', 'text_domain'),
    'search_items'       => __('Search Page', 'text_domain'),
    'not_found'          => __('Not found', 'text_domain'),
    'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
  );

  $args = array(
    'label'               => __('Page', 'text_domain'),
    'description'         => __('Manage dynamic pages, sections, and their components.', 'text_domain'),
    'labels'              => $labels,
    'supports'            => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions', 'page-attributes'),
    'hierarchical'        => true,
    'public'              => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'menu_position'       => 31,
    'menu_icon'           => 'dashicons-admin-page',
    'show_in_admin_bar'   => true,
    'show_in_nav_menus'   => true,
    'can_export'          => true,
    'has_archive'         => true, // Enable archive
    'exclude_from_search' => false,
    'publicly_queryable'  => true,
    'capability_type'     => 'page',
    'show_in_rest'        => true,
    'rewrite'             => array('slug' => 'nucleus_page', 'with_front' => true), // Explicit rewrite rule
  );

  register_post_type('nucleus_page', $args);

  // Refresh permalinks on first load
  if (get_option('nucleus_page_cpt_flush_rewrite', false)) {
    flush_rewrite_rules();
    update_option('nucleus_page_cpt_flush_rewrite', false);
  }
}
add_action('init', 'nucleus_dxp_register_page_cpt');

function nucleus_dxp_register_hf_cpt()
{
  $labels = array(
    'name'               => _x('Header & Footer Sets', 'Post Type General Name', 'text_domain'),
    'singular_name'      => _x('H&F Set', 'Post Type Singular Name', 'text_domain'),
    'menu_name'          => __('Header & Footer', 'text_domain'),
    'all_items'          => __('H&F Manager', 'text_domain'),
    'add_new_item'       => __('Add New Set', 'text_domain'),
    'add_new'            => __('Add New', 'text_domain'),
    'new_item'           => __('New Set', 'text_domain'),
    'edit_item'          => __('Edit Set', 'text_domain'),
    'update_item'        => __('Update Set', 'text_domain'),
    'view_item'          => __('View Set', 'text_domain'),
    'search_items'       => __('Search Sets', 'text_domain'),
    'not_found'          => __('Not found', 'text_domain'),
    'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
  );

  $args = array(
    'label'               => __('Header & Footer Set', 'text_domain'),
    'description'         => __('Manage Header and Footer templates.', 'text_domain'),
    'labels'              => $labels,
    'supports'            => array('title'),
    'hierarchical'        => false,
    'public'              => false,  // Don't need public single pages for them
    'show_ui'             => true,
    'show_in_menu'        => 'edit.php?post_type=nucleus_page',
    'menu_position'       => null,
    'show_in_admin_bar'   => false,
    'show_in_nav_menus'   => false,
    'can_export'          => true,
    'has_archive'         => false,
    'exclude_from_search' => true,
    'publicly_queryable'  => false,
    'capability_type'     => 'page',
    'show_in_rest'        => false,
  );

  register_post_type('nucleus_hf_set', $args);
}
add_action('init', 'nucleus_dxp_register_hf_cpt');

// Add rewrite flush logic
function nucleus_dxp_rewrite_flush()
{
  // Add activation flush
  nucleus_dxp_register_page_cpt();
  flush_rewrite_rules();
}
// Hook for manual plugin activation
register_activation_hook(NUCLEUS_DXP_PATH . 'nucleus-dxp.php', 'nucleus_dxp_rewrite_flush');

// Add a one-time automatic flush on admin init to fix existing active installs
function nucleus_dxp_auto_flush_on_update()
{
  if (!get_option('nucleus_page_manager_rules_flushed')) {
    nucleus_dxp_register_page_cpt();
    flush_rewrite_rules();
    update_option('nucleus_page_manager_rules_flushed', true);
  }
}
add_action('admin_init', 'nucleus_dxp_auto_flush_on_update');

/**
 * Force template loading for Single Nucleus Page
 */
function nucleus_page_template_include($template)
{
  if (is_singular('nucleus_page')) {
    $plugin_template = NUCLEUS_DXP_PATH . 'templates/single-nucleus_page.php';
    if (file_exists($plugin_template)) {
      return $plugin_template;
    }
  }
  return $template;
}
add_filter('template_include', 'nucleus_page_template_include');

/**
 * =====================================
 * Meta Boxes for Sections & Components Builder
 * =====================================
 */
function nucleus_page_manager_meta_boxes()
{
  add_meta_box(
    'nucleus_page_dynamic_builder',
    'Page Content & Style Builder',
    'nucleus_page_dynamic_builder_html',
    'nucleus_page',
    'normal',
    'high'
  );

  add_meta_box(
    'nucleus_hf_builder',
    'Header & Footer Builder',
    'nucleus_hf_builder_html',
    'nucleus_hf_set',
    'normal',
    'high'
  );
}
add_action('add_meta_boxes', 'nucleus_page_manager_meta_boxes');

function nucleus_hf_builder_html($post)
{
  wp_nonce_field('nucleus_save_page_builder_data', 'nucleus_page_builder_meta_box_nonce');
  wp_enqueue_script('jquery-ui-sortable');
  wp_enqueue_script('wp-theme-plugin-editor');
  wp_enqueue_style('wp-codemirror');
  wp_enqueue_media();
  wp_enqueue_editor();

  // Data for Header and Footer (backward compatible with non-base64 array values)
  $header_meta = get_post_meta($post->ID, '_nucleus_header_components', true);
  $header_data = is_string($header_meta) ? json_decode(base64_decode($header_meta), true) : $header_meta;
  if (!is_array($header_data)) $header_data = array();

  $footer_meta = get_post_meta($post->ID, '_nucleus_footer_components', true);
  $footer_data = is_string($footer_meta) ? json_decode(base64_decode($footer_meta), true) : $footer_meta;
  if (!is_array($footer_data)) $footer_data = array();

  $css_meta = get_post_meta($post->ID, '_nucleus_hf_css', true);
  $hf_css_data = is_string($css_meta) ? json_decode(base64_decode($css_meta), true) : $css_meta;
  if (!is_array($hf_css_data)) $hf_css_data = array();

  $default_hf_set_id = get_option('nucleus_default_hf_set', '');
  $is_default = ($default_hf_set_id == $post->ID);
?>
  <!-- Set as Default -->
  <div style="background: #eef5ff; border: 1px solid #9ba2aa; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
    <label style="font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
      <input type="checkbox" name="_ncl_is_default_hf" value="1" <?php checked($is_default); ?> />
      Set as Default Header & Footer Set (Automatically applies to new pages)
    </label>
  </div>

  <!-- TABS NAVIGATION -->
  <div class="ncl-tabs-nav">
    <button type="button" class="ncl-tab-btn active" data-tab="header">Header Builder</button>
    <button type="button" class="ncl-tab-btn" data-tab="footer">Footer Builder</button>
    <button type="button" class="ncl-tab-btn" data-tab="css">CSS Manager</button>
  </div>

  <!-- HEADER BUILDER TAB -->
  <div id="ncl-tab-content-header" class="ncl-tab-pane active">
    <!-- Simplistic Builder JS uses this -->
    <div id="nucleus-header-builder-root" class="ncl-hf-builder-root" data-hf="header"></div>
    <input type="hidden" name="_nucleus_header_data_json" id="_nucleus_header_data_json" value="" />
  </div>

  <!-- FOOTER BUILDER TAB -->
  <div id="ncl-tab-content-footer" class="ncl-tab-pane">
    <div id="nucleus-footer-builder-root" class="ncl-hf-builder-root" data-hf="footer"></div>
    <input type="hidden" name="_nucleus_footer_data_json" id="_nucleus_footer_data_json" value="" />
  </div>

  <!-- CSS MANAGER TAB -->
  <div id="ncl-tab-content-css" class="ncl-tab-pane">
    <div class="ncl-css-manager-wrapper ncl-css-sidebar-layout">
      <div class="ncl-css-sidebar">
        <div class="ncl-css-sidebar-title">Sections</div>
        <ul id="ncl-css-sidebar-list"></ul>
      </div>
      <div class="ncl-css-editor-panel">
        <div id="ncl-css-editor-container"></div>
        <p class="description">Use IDs like <code>#header-logo</code> or <code>#footer-links</code>.</p>
      </div>
    </div>
    <input type="hidden" name="_nucleus_hf_css_json" id="_nucleus_hf_css_json" value="" />
  </div>

  <style>
    /* Reset and shared styles from page builder */
    .ncl-tabs-nav {
      border-bottom: 1px solid #dcdcde;
      margin-bottom: 20px;
      display: flex;
      gap: 5px;
    }

    .ncl-tab-btn {
      background: #f0f0f1;
      border: 1px solid #dcdcde;
      border-bottom: none;
      padding: 10px 20px;
      cursor: pointer;
      font-weight: 600;
      color: #50575e;
      margin-bottom: -1px;
      border-radius: 4px 4px 0 0;
    }

    .ncl-tab-btn.active {
      background: #fff;
      border-bottom: 1px solid #fff;
      color: #1d2327;
    }

    .ncl-tab-pane {
      display: none;
    }

    .ncl-tab-pane.active {
      display: block;
    }

    /* --- CSS Sidebar Layout --- */
    .ncl-css-sidebar-layout {
      display: flex;
      gap: 0;
      padding: 0 !important;
      overflow: hidden;
      background: #fff;
      border: 1px solid #c3c4c7;
      border-radius: 4px;
    }

    .ncl-css-sidebar {
      width: 220px;
      min-width: 220px;
      background: #f6f7f7;
      border-right: 1px solid #c3c4c7;
      padding: 0;
      flex-shrink: 0;
    }

    .ncl-css-sidebar-title {
      font-weight: 700;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #50575e;
      padding: 14px 16px 8px;
    }

    #ncl-css-sidebar-list {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    #ncl-css-sidebar-list li {
      padding: 10px 16px;
      cursor: pointer;
      font-size: 13px;
      color: #1d2327;
      border-left: 3px solid transparent;
      transition: background 0.15s, border-color 0.15s;
      border-bottom: 1px solid #eee;
    }

    #ncl-css-sidebar-list li:hover {
      background: #e9ecf0;
    }

    #ncl-css-sidebar-list li.active {
      background: #fff;
      border-left-color: #2271b1;
      font-weight: 600;
      color: #2271b1;
    }

    .ncl-css-editor-panel {
      flex: 1;
      padding: 20px;
      min-width: 0;
    }
  </style>

  <script type="text/javascript">
    jQuery(document).ready(function($) {
      let headerData = <?php echo json_encode($header_data); ?>;
      let footerData = <?php echo json_encode($footer_data); ?>;
      let cssData = <?php echo json_encode($hf_css_data); ?>;

      if (!Array.isArray(headerData)) headerData = [];
      if (!Array.isArray(footerData)) footerData = [];
      if (!cssData || Array.isArray(cssData)) cssData = {};

      let currentCssSection = '';
      const $cssRoot = $('#ncl-css-editor-container');
      const $sidebarList = $('#ncl-css-sidebar-list');

      function syncHiddenInputs() {
        $('#_nucleus_header_data_json').val(JSON.stringify(headerData));
        $('#_nucleus_footer_data_json').val(JSON.stringify(footerData));
        $('#_nucleus_hf_css_json').val(JSON.stringify(cssData));
      }

      function renderBuilder(type) {
        const dataArr = type === 'header' ? headerData : footerData;
        const $root = $('#nucleus-' + type + '-builder-root');
        $root.empty();

        const $sectionsWrapper = $('<div class="ncl-sections-wrapper"></div>');

        dataArr.forEach((section, sIndex) => {
          const $sectionBox = $(`
                <div class="ncl-section-block" data-sindex="${sIndex}" data-type="${type}" style="background: #f0f0f1; padding: 15px; margin-bottom: 15px; border: 1px solid #c3c4c7; border-radius: 4px;">
                    <div class="ncl-section-header" style="display:flex; justify-content:space-between; margin-bottom: 10px; border-bottom: 2px solid #ccc; padding-bottom: 10px;">
                        <h3>
                            <span class="dashicons dashicons-menu ncl-drag-handle" style="cursor:grab;"></span>
                            Section Name:
                            <input type="text" class="input-sec-id" value="${escapeHtml(section.section_id)}" style="font-family: monospace;" />
                            <code style="color: #d63638; background: #fff; padding: 2px 5px;">#${escapeHtml(type)}-${escapeHtml(section.section_id)}</code>
                        </h3>
                        <button type="button" class="btn-delete-section" style="background:#d63638; color:#fff; border:none; padding:4px 8px; border-radius:3px;">Delete Section</button>
                    </div>
                    
                    <div class="ncl-bg-settings" style="margin-bottom:10px;">
                        <label>Nav/Footer Background color:</label>
                        <input type="text" class="input-sec-bg" value="${escapeHtml(section.bg_value || '#ffffff')}" placeholder="#ffffff or rgba(0,0,0,1)" style="width: 150px; padding: 4px 8px; border: 1px solid #8c8f94; border-radius: 3px;" />
                    </div>

                    <div class="ncl-comp-list" style="margin-left: 20px; display:flex; flex-direction:column; gap:10px;"></div>
                    <div style="margin-top:10px; text-align:right;">
                        <button type="button" class="btn-add-comp" style="background:#2271b1; color:#fff; border:none; padding:6px 12px; border-radius:3px;">+ Add Component</button>
                    </div>
                </div>
            `);

          const $compList = $sectionBox.find('.ncl-comp-list');
          if (section.components) {
            section.components.forEach((comp, cIndex) => {
              const fullHtmlId = `${type}-${section.section_id}-${comp.id}`;
              $compList.append(`
                        <div class="ncl-comp-item" data-cindex="${cIndex}" style="background:#fff; border:1px solid #ccc; border-left:4px solid #2271b1; padding:10px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                <div><strong>ID:</strong> <code>${escapeHtml(fullHtmlId)}</code></div>
                                <button type="button" class="btn-delete-comp" style="background:transparent; border:1px solid #d63638; color:#d63638; padding:2px 6px; cursor:pointer;">Remove</button>
                            </div>
                            <div style="display:flex; gap:10px; margin-bottom:10px;">
                                <div>
                                    <label>Type:</label>
                                    <select class="input-comp-type">
                                        <option value="text" ${comp.type==='text'?'selected':''}>Text / String</option>
                                        <option value="image" ${comp.type==='image'?'selected':''}>Image / Logo</option>
                                        <option value="url" ${comp.type==='url'?'selected':''}>Link URL</option>
                                        <option value="html" ${comp.type==='html'?'selected':''}>Custom HTML</option>
                                        <option value="shortcode" ${comp.type==='shortcode'?'selected':''}>Shortcode (Nav Menu)</option>
                                    </select>
                                </div>
                                <div>
                                    <label>Name:</label>
                                    <input type="text" class="input-comp-key" value="${escapeHtml(comp.id)}" placeholder="e.g. logo, link-1" />
                                </div>
                            </div>
                            <div>
                                <label>Value:</label>
                                ${comp.type === 'html' ? 
                                    `<textarea class="input-comp-val" rows="3" style="width:100%; font-family:monospace;">${escapeHtml(typeof comp.value === 'string' ? comp.value : '')}</textarea>` :
                                    `<input type="text" class="input-comp-val" value="${escapeHtml(typeof comp.value === 'string' ? comp.value : '')}" style="width:100%;" />`
                                }
                                ${(comp.type === 'image') ? `<button type="button" class="btn-upload-img" style="margin-top:5px;">Select Image</button>` : ''}
                            </div>
                        </div>
                    `);
            });
          }

          $sectionsWrapper.append($sectionBox);
        });

        $root.append($sectionsWrapper);
        $root.append(`
            <div style="text-align:center; padding:20px; border:2px dashed #ccc; background:#fff; margin-top:20px;">
                <button type="button" class="btn-add-section" style="background:#2271b1; color:#fff; border:none; padding:10px 20px; border-radius:3px;">+ Add New ${type.charAt(0).toUpperCase() + type.slice(1)} Section</button>
            </div>
        `);

        $sectionsWrapper.sortable({
          handle: '.ncl-drag-handle',
          update: function() {
            reorderDataArray(type);
          }
        });

        syncHiddenInputs();
        buildCssSidebar();
      }

      function reorderDataArray(type) {
        const newData = [];
        const dataArr = type === 'header' ? headerData : footerData;
        $('#nucleus-' + type + '-builder-root .ncl-section-block').each(function() {
          const originalIndex = $(this).data('sindex');
          newData.push(dataArr[originalIndex]);
        });
        if (type === 'header') headerData = newData;
        else footerData = newData;
        renderBuilder(type);
      }

      function buildCssSidebar() {
        $sidebarList.empty();
        $sidebarList.append(
          `<li data-section="global" class="${currentCssSection === 'global' ? 'active' : ''}" style="padding:10px; cursor:pointer; font-weight:bold; border-bottom:1px solid #ccc;">Global H&F CSS</li>`
        );

        ['header', 'footer'].forEach(type => {
          const arr = type === 'header' ? headerData : footerData;
          arr.forEach(sec => {
            const secId = type + '-' + sec.section_id;
            $sidebarList.append(
              `<li data-section="${secId}" class="${currentCssSection === secId ? 'active' : ''}" style="padding:10px; cursor:pointer; border-bottom:1px solid #eee;">${secId}</li>`
            );
          });
        });
        renderCssEditor();
      }

      function renderCssEditor() {
        $cssRoot.empty();
        if (!currentCssSection) {
          $cssRoot.html(
            '<div style="padding:40px; text-align:center; color:#666;">Select a section to edit CSS</div>'
          );
          return;
        }
        const val = cssData[currentCssSection] || '';
        $cssRoot.append(`
            <textarea id="ncl-active-css-editor" style="width:100%; height:300px; font-family:monospace; padding:10px; background:#fafafa;">${escapeHtml(val)}</textarea>
        `);
      }

      function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe).replace(/[&<"']/g, function(m) {
          switch (m) {
            case '&':
              return '&amp;';
            case '<':
              return '&lt;';
            case '"':
              return '&quot;';
            case "'":
              return '&#039;';
          }
        });
      }

      // Tabs
      $('.ncl-tab-btn').on('click', function() {
        $('.ncl-tab-btn').removeClass('active');
        $(this).addClass('active');
        const tab = $(this).data('tab');
        $('.ncl-tab-pane').removeClass('active');
        $('#ncl-tab-content-' + tab).addClass('active');
      });

      // Content Interactions
      $(document).on('click', '.ncl-hf-builder-root .btn-add-section', function() {
        const type = $(this).closest('.ncl-hf-builder-root').data('hf');
        const arr = type === 'header' ? headerData : footerData;
        arr.push({
          section_id: 'sec-' + Math.floor(Math.random() * 100),
          bg_value: '#ffffff',
          components: []
        });
        renderBuilder(type);
      });

      $(document).on('click', '.ncl-hf-builder-root .btn-delete-section', function() {
        if (confirm('Delete section?')) {
          const block = $(this).closest('.ncl-section-block');
          const type = block.data('type');
          const sIndex = block.data('sindex');
          const arr = type === 'header' ? headerData : footerData;
          arr.splice(sIndex, 1);
          renderBuilder(type);
        }
      });

      $(document).on('input', '.ncl-hf-builder-root .input-sec-id', function() {
        const block = $(this).closest('.ncl-section-block');
        const type = block.data('type');
        const sIndex = block.data('sindex');
        const arr = type === 'header' ? headerData : footerData;
        arr[sIndex].section_id = $(this).val().trim().toLowerCase().replace(/[^a-z0-9_-]/g, '');
        syncHiddenInputs();
      });

      $(document).on('input', '.ncl-hf-builder-root .input-sec-bg', function() {
        const block = $(this).closest('.ncl-section-block');
        const type = block.data('type');
        const sIndex = block.data('sindex');
        const arr = type === 'header' ? headerData : footerData;
        arr[sIndex].bg_value = $(this).val();
        syncHiddenInputs();
      });

      $(document).on('click', '.ncl-hf-builder-root .btn-add-comp', function() {
        const block = $(this).closest('.ncl-section-block');
        const type = block.data('type');
        const sIndex = block.data('sindex');
        const arr = type === 'header' ? headerData : footerData;
        arr[sIndex].components.push({
          id: 'comp-' + Math.floor(Math.random() * 100),
          type: 'text',
          value: ''
        });
        renderBuilder(type);
      });

      $(document).on('click', '.ncl-hf-builder-root .btn-delete-comp', function() {
        const block = $(this).closest('.ncl-section-block');
        const item = $(this).closest('.ncl-comp-item');
        const type = block.data('type');
        const sIndex = block.data('sindex');
        const cIndex = item.data('cindex');
        const arr = type === 'header' ? headerData : footerData;
        arr[sIndex].components.splice(cIndex, 1);
        renderBuilder(type);
      });

      $(document).on('change', '.ncl-hf-builder-root .input-comp-type', function() {
        const block = $(this).closest('.ncl-section-block');
        const item = $(this).closest('.ncl-comp-item');
        const type = block.data('type');
        const sIndex = block.data('sindex');
        const cIndex = item.data('cindex');
        const arr = type === 'header' ? headerData : footerData;
        arr[sIndex].components[cIndex].type = $(this).val();
        renderBuilder(type);
      });

      $(document).on('input', '.ncl-hf-builder-root .input-comp-key, .ncl-hf-builder-root .input-comp-val',
        function() {
          const block = $(this).closest('.ncl-section-block');
          const item = $(this).closest('.ncl-comp-item');
          const type = block.data('type');
          const sIndex = block.data('sindex');
          const cIndex = item.data('cindex');
          const arr = type === 'header' ? headerData : footerData;

          arr[sIndex].components[cIndex].id = item.find('.input-comp-key').val().trim().toLowerCase()
            .replace(/[^a-z0-9_-]/g, '');
          arr[sIndex].components[cIndex].value = item.find('.input-comp-val').val();
          syncHiddenInputs();
        });

      // Image Upload
      let mediaUploader;
      $(document).on('click', '.ncl-hf-builder-root .btn-upload-img', function(e) {
        e.preventDefault();
        const block = $(this).closest('.ncl-section-block');
        const item = $(this).closest('.ncl-comp-item');
        const type = block.data('type');
        const sIndex = block.data('sindex');
        const cIndex = item.data('cindex');
        const arr = type === 'header' ? headerData : footerData;

        let compUploader = wp.media({
          title: 'Choose Image',
          button: {
            text: 'Choose'
          },
          multiple: false
        });
        compUploader.on('select', function() {
          const attachment = compUploader.state().get('selection').first().toJSON();
          arr[sIndex].components[cIndex].value = attachment.url;
          renderBuilder(type);
        });
        compUploader.open();
      });

      // CSS actions
      $(document).on('click', '#ncl-css-sidebar-list li', function() {
        currentCssSection = $(this).data('section');
        $('#ncl-css-sidebar-list li').removeClass('active');
        $(this).addClass('active');
        if (currentCssSection && !cssData[currentCssSection]) cssData[currentCssSection] =
          `/* Style for ${currentCssSection} */\n`;
        renderCssEditor();
        syncHiddenInputs();
      });

      $(document).on('input', '#ncl-active-css-editor', function() {
        if (currentCssSection) {
          cssData[currentCssSection] = $(this).val();
          syncHiddenInputs();
        }
      });

      renderBuilder('header');
      renderBuilder('footer');
    });
  </script>
<?php
}

function nucleus_page_dynamic_builder_html($post)
{
  wp_nonce_field('nucleus_save_page_builder_data', 'nucleus_page_builder_meta_box_nonce');
  wp_enqueue_script('jquery-ui-sortable');
  wp_enqueue_script('wp-theme-plugin-editor');
  wp_enqueue_style('wp-codemirror');
  wp_enqueue_media();
  wp_enqueue_editor();

  // Retrieve Component Data (backward compatible with non-base64 array values)
  $page_meta = get_post_meta($post->ID, '_nucleus_page_components', true);
  $page_data = is_string($page_meta) ? json_decode(base64_decode($page_meta), true) : $page_meta;
  if (!is_array($page_data)) {
    $page_data = array();
  }

  // Retrieve CSS Data (backward compatible with non-base64 array values)
  $css_meta = get_post_meta($post->ID, '_nucleus_page_css', true);
  $page_css_data = is_string($css_meta) ? json_decode(base64_decode($css_meta), true) : $css_meta;
  if (!is_array($page_css_data)) {
    $page_css_data = array();
  }

  // Retrieve HF Sets
  $hf_sets = get_posts(array(
    'post_type' => 'nucleus_hf_set',
    'numberposts' => -1,
    'post_status' => 'any'
  ));

  $selected_hf = get_post_meta($post->ID, '_nucleus_selected_hf_set', true);

  // Auto-select Default HF Set for brand new unused draft pages
  if (empty($selected_hf) && in_array($post->post_status, array('auto-draft', 'draft')) && empty($page_data)) {
    $selected_hf = get_option('nucleus_default_hf_set', '');
  }
?>

  <!-- HF SET SELECTION -->
  <div
    style="background: #eef5ff; border: 1px solid #9ba2aa; padding: 15px; margin-bottom: 20px; border-radius: 4px; display: flex; align-items: center; gap: 15px;">
    <strong><span class="dashicons dashicons-layout"></span> Header & Footer Template:</strong>
    <select name="_nucleus_selected_hf_set" style="min-width: 250px;">
      <option value="">Use Theme Default (Oxygen)</option>
      <?php foreach ($hf_sets as $set): ?>
        <option value="<?php echo esc_attr($set->ID); ?>" <?php selected($selected_hf, $set->ID); ?>>
          <?php echo esc_html($set->post_title); ?></option>
      <?php endforeach; ?>
    </select>
    <a href="<?php echo admin_url('edit.php?post_type=nucleus_hf_set'); ?>" target="_blank"
      style="text-decoration:none; font-size:13px;">Manage Sets ↗</a>
  </div>

  <!-- TABS NAVIGATION -->
  <div class="ncl-tabs-nav">
    <button type="button" class="ncl-tab-btn active" data-tab="content">Content Builder</button>
    <button type="button" class="ncl-tab-btn" data-tab="css">CSS Manager</button>
    <button type="button" class="ncl-tab-btn" data-tab="help"><span class="dashicons dashicons-book"></span> User
      Handbook</button>
  </div>

  <!-- CONTENT BUILDER TAB -->
  <div id="ncl-tab-content-content" class="ncl-tab-pane active">
    <div id="nucleus-page-builder-root"></div>
    <input type="hidden" name="_nucleus_page_data_json" id="_nucleus_page_data_json" value="" />
  </div>

  <!-- CSS MANAGER TAB -->
  <div id="ncl-tab-content-css" class="ncl-tab-pane">
    <div class="ncl-css-manager-wrapper ncl-css-sidebar-layout">
      <div class="ncl-css-sidebar">
        <div class="ncl-css-sidebar-title">Sections</div>
        <ul id="ncl-css-sidebar-list">
          <!-- Populated via JS -->
        </ul>
      </div>
      <div class="ncl-css-editor-panel">
        <div id="ncl-css-editor-container"></div>
        <p class="description">Enter standard CSS. Use selectors like <code>#section-component</code> or specific
          IDs.</p>
      </div>
    </div>
    <!-- Hidden elements kept for JS compatibility -->
    <select id="ncl-css-section-select" style="display:none;"></select>
    <input type="hidden" name="_nucleus_page_css_json" id="_nucleus_page_css_json" value="" />
  </div>

  <!-- HELP HANDBOOK TAB -->
  <div id="ncl-tab-content-help" class="ncl-tab-pane">
    <div class="ncl-help-container"
      style="background:#fff; border:1px solid #c3c4c7; padding:30px; border-radius:4px; max-width:850px;">
      <h2 style="margin-top:0; font-size:24px;">📖 Nucleus Page Manager: CSS & Layout Handbook</h2>
      <p style="font-size:15px; color:#50575e; line-height:1.6;">Welcome to the Nucleus Page Manager! This builder
        allows you to stack Sections and Components to build dynamic pages. Because the builder uses standard HTML,
        you can use the <strong>CSS Manager</strong> tab to style absolutely anything you want.</p>

      <div
        style="background-color: #fff8e5; border-left: 4px solid #f0b849; padding: 12px 15px; margin-bottom: 20px;">
        <strong>⚠️ Important Note:</strong> If you modify a section or component name, the CSS of the page might not
        catch up automatically. You should make a simple change in the CSS Manager (like adding a space or
        re-copying and pasting your CSS) to trigger a refresh.
      </div>

      <hr style="margin:25px 0; border:0; border-top:1px solid #dcdcde;">

      <h3 style="font-size:18px;">1. Targeting Sections & Backgrounds</h3>
      <p>Every section you create gets an automatic ID based on its <strong>Section Name</strong>. All section IDs
        start with <code>nucleus-section-</code>. When you add a <strong>Background Image or Color</strong> using
        the dropdown settings, the style is applied directly to this section container.</p>
      <ul style="list-style:disc; margin-left:20px; margin-bottom:15px;">
        <li><strong>Section ID Formula:</strong> <code>#nucleus-section-{section_name}</code></li>
      </ul>
      <p><em>Example: Accessing Section Layout & Styling Background Overlays (Assuming Section Name is "hero")</em>
      </p>
      <pre style="background:#f0f0f1; padding:15px; border-left:4px solid #2271b1; overflow-x:auto;"><code>/* Set padding and text alignment for the entire section */
#nucleus-section-hero {
    padding: 80px 20px;
    text-align: center;
    position: relative; /* Required for absolute positioning inside */
    z-index: 1; /* Required for overlays */
}

/* Example: Add a dark dimming overlay directly over the attached Background Image */
#nucleus-section-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.6); /* 60% black dim shadow over the background image */
    z-index: -1; /* Pushes the dim overlay behind the contents but above the image */
}

/* Advanced: Apply CSS filters (like blur or grayscale) to the section, but use backdrop-filter or careful targeting so contents don't blur */
#nucleus-section-hero {
    /* If you uploaded an image via builder but need to override how it behaves: */
    background-position: top center !important; 
    background-attachment: fixed !important; /* Parallax effect */
}</code></pre>

      <hr style="margin:25px 0; border:0; border-top:1px solid #dcdcde;">

      <h3 style="font-size:18px;">2. Targeting Individual Components</h3>
      <p>Every component inside a section is given a unique ID combining the Section Name and the Component Name.</p>
      <ul style="list-style:disc; margin-left:20px;">
        <li><strong>Formula:</strong> <code>#{section_name}-{component_name}</code></li>
      </ul>
      <p><em>Example: (If section is "hero" and text component is "main-title")</em></p>
      <pre style="background:#f0f0f1; padding:15px; border-left:4px solid #2271b1; overflow-x:auto;"><code>/* Target the specific title in the hero section */
#hero-main-title {
    color: #ffffff;
    font-size: 48px;
    text-align: center;
}</code></pre>

      <hr style="margin:25px 0; border:0; border-top:1px solid #dcdcde;">

      <h3 style="font-size:18px;">3. General Component Classes</h3>
      <p>If you want to style <em>all</em> components of a certain type across a section, use the built-in classes
        below:</p>
      <table style="width:100%; border-collapse:collapse; margin-bottom:15px; border:1px solid #dcdcde;">
        <tr style="background:#f6f7f7;">
          <th style="padding:10px; border:1px solid #dcdcde; text-align:left;">Class Name</th>
          <th style="padding:10px; border:1px solid #dcdcde; text-align:left;">Applies To</th>
        </tr>
        <tr>
          <td style="padding:10px; border:1px solid #dcdcde;"><code>.nucleus-title</code></td>
          <td style="padding:10px; border:1px solid #dcdcde;">Any component name containing "title"</td>
        </tr>
        <tr>
          <td style="padding:10px; border:1px solid #dcdcde;"><code>.nucleus-subtitle</code></td>
          <td style="padding:10px; border:1px solid #dcdcde;">Any component name containing "subtitle"</td>
        </tr>
        <tr>
          <td style="padding:10px; border:1px solid #dcdcde;"><code>.nucleus-text</code></td>
          <td style="padding:10px; border:1px solid #dcdcde;">Standard String (Text), text areas, and WYSIWYG
            boxes</td>
        </tr>
        <tr>
          <td style="padding:10px; border:1px solid #dcdcde;"><code>.nucleus-number</code></td>
          <td style="padding:10px; border:1px solid #dcdcde;">All "Number" fields</td>
        </tr>
        <tr>
          <td style="padding:10px; border:1px solid #dcdcde;"><code>.nucleus-heading</code></td>
          <td style="padding:10px; border:1px solid #dcdcde;">Explicit H1-H6 Heading tags</td>
        </tr>
        <tr>
          <td style="padding:10px; border:1px solid #dcdcde;"><code>.nucleus-btn-primary</code></td>
          <td style="padding:10px; border:1px solid #dcdcde;">Button (Text + Link) modules</td>
        </tr>
        <tr>
          <td style="padding:10px; border:1px solid #dcdcde;"><code>.nucleus-link</code></td>
          <td style="padding:10px; border:1px solid #dcdcde;">Raw Link URL fields</td>
        </tr>
      </table>
      <p><em>Example:</em></p>
      <pre style="background:#f0f0f1; padding:15px; border-left:4px solid #2271b1; overflow-x:auto;"><code>/* Change the background color of ALL buttons in the 'pricing' section */
#nucleus-section-pricing .nucleus-btn-primary {
    background-color: #ff5500;
}</code></pre>

      <hr style="margin:25px 0; border:0; border-top:1px solid #dcdcde;">

      <h3 style="font-size:18px;">4. Styling Advanced Components</h3>
      <p>Advanced blocks output specific wrapper classes that contain inner elements. Here are the keys you need to
        style them:</p>

      <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <div
          style="flex:1; min-width:300px; background:#f9f9f9; padding:15px; border-radius:4px; border:1px solid #e0e0e0;">
          <h4 style="margin-top:0;">Accordion / FAQ (<code>.nucleus-accordion</code>)</h4>
          <ul style="font-size:13px; font-family:monospace; margin-left:15px;">
            <li>.nucleus-accordion-item</li>
            <li>.nucleus-accordion-header</li>
            <li>.nucleus-accordion-content</li>
          </ul>
        </div>

        <div
          style="flex:1; min-width:300px; background:#f9f9f9; padding:15px; border-radius:4px; border:1px solid #e0e0e0;">
          <h4 style="margin-top:0;">Slider (<code>.nucleus-carousel-wrapper</code>)</h4>
          <ul style="font-size:13px; font-family:monospace; margin-left:15px;">
            <li>.nucleus-carousel-slide</li>
            <li>.nucleus-carousel-slide-title</li>
            <li>.nucleus-carousel-slide-desc</li>
            <li>.nucleus-carousel-dot.active</li>
          </ul>
        </div>

        <div
          style="flex:1; min-width:300px; background:#f9f9f9; padding:15px; border-radius:4px; border:1px solid #e0e0e0;">
          <h4 style="margin-top:0;">Testimonial (<code>.nucleus-testimonial</code>)</h4>
          <ul style="font-size:13px; font-family:monospace; margin-left:15px;">
            <li>.nucleus-testimonial-quote</li>
            <li>.nucleus-testimonial-author</li>
            <li>.nucleus-testimonial-avatar</li>
          </ul>
        </div>

        <div
          style="flex:1; min-width:300px; background:#f9f9f9; padding:15px; border-radius:4px; border:1px solid #e0e0e0;">
          <h4 style="margin-top:0;">Stats / KPI (<code>.nucleus-stats</code>)</h4>
          <ul style="font-size:13px; font-family:monospace; margin-left:15px;">
            <li>.nucleus-stats-number</li>
            <li>.nucleus-stats-label</li>
          </ul>
        </div>
      </div>

      <hr style="margin:25px 0; border:0; border-top:1px solid #dcdcde;">

      <h3 style="font-size:18px;">5. Grouping Components (Flexbox & Grids)</h3>
      <p>The builder has a hidden superpower: <strong>Prefix Grouping</strong>. If you name multiple components with
        the <em>exact same word and a dash</em> (e.g., <code>card-image</code>, <code>card-title</code>,
        <code>card-text</code>), they are automatically wrapped in a single Group <code>&lt;div&gt;</code> so you
        can style them together easily.
      </p>
      <ul style="list-style:disc; margin-left:20px;">
        <li><strong>Group Class:</strong> <code>.nucleus-group-{prefix}</code></li>
        <li><strong>Group ID:</strong> <code>#{section_name}-{prefix}</code></li>
      </ul>
      <p><em>Example: (You are in section 'services' and add components: box-1, box-2, box-3. They are automatically
          grouped under <code>#services-box</code>)</em></p>
      <pre style="background:#f0f0f1; padding:15px; border-left:4px solid #2271b1; overflow-x:auto;"><code>/* Turn the group into a 3-column CSS Grid */
#services-box {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}</code></pre>
    </div>
  </div>

  <style>
    .ncl-tabs-nav {
      border-bottom: 1px solid #dcdcde;
      margin-bottom: 20px;
      display: flex;
      gap: 5px;
    }

    .ncl-tab-btn {
      background: #f0f0f1;
      border: 1px solid #dcdcde;
      border-bottom: none;
      padding: 10px 20px;
      cursor: pointer;
      font-weight: 600;
      color: #50575e;
      margin-bottom: -1px;
      border-radius: 4px 4px 0 0;
    }

    .ncl-tab-btn.active {
      background: #fff;
      border-bottom: 1px solid #fff;
      color: #1d2327;
    }

    .ncl-tab-pane {
      display: none;
    }

    .ncl-tab-pane.active {
      display: block;
    }

    .ncl-css-manager-wrapper {
      background: #fff;
      border: 1px solid #c3c4c7;
      padding: 20px;
      border-radius: 4px;
    }

    .ncl-css-header {
      display: flex;
      gap: 15px;
      align-items: center;
      margin-bottom: 15px;
      background: #f0f0f1;
      padding: 10px;
      border-radius: 4px;
    }

    .ncl-css-box {
      border: 1px solid #ddd;
      margin-bottom: 15px;
      border-radius: 4px;
      overflow: hidden;
    }

    .ncl-css-box-header {
      background: #f6f7f7;
      padding: 8px 12px;
      border-bottom: 1px solid #ddd;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: bold;
      font-family: monospace;
      color: #2271b1;
    }

    .ncl-css-textarea {
      width: 100%;
      height: 150px;
      font-family: monospace;
      border: none;
      padding: 10px;
      resize: vertical;
      background: #fafafa;
    }

    .ncl-css-textarea:focus {
      background: #fff;
      outline: none;
    }

    #nucleus-page-builder-root {
      margin-top: 15px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    }

    .ncl-section-block {
      background: #f0f0f1;
      border: 1px solid #c3c4c7;
      padding: 15px;
      margin-bottom: 25px;
      border-radius: 4px;
      position: relative;
    }

    .ncl-section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 10px;
      border-bottom: 2px solid #dcdcde;
      cursor: move;
    }

    .ncl-section-header h3 {
      margin: 0;
      font-size: 16px;
      color: #1d2327;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .ncl-drag-handle {
      cursor: grab;
      font-size: 18px;
      color: #8c8f94;
    }

    .ncl-section-badge {
      background: #2271b1;
      color: white;
      padding: 3px 8px;
      border-radius: 3px;
      font-family: monospace;
      font-size: 13px;
      margin-left: 10px;
    }

    .ncl-bg-settings {
      background: #fff;
      padding: 10px 15px;
      border: 1px solid #dcdcde;
      border-radius: 4px;
      margin-bottom: 15px;
      display: flex;
      gap: 15px;
      align-items: center;
    }

    .ncl-bg-settings label {
      font-weight: 600;
      color: #50575e;
    }

    .ncl-bg-settings select,
    .ncl-bg-settings input {
      padding: 4px 8px;
      border: 1px solid #8c8f94;
      border-radius: 3px;
    }

    .ncl-comp-list {
      margin-left: 20px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .ncl-comp-item {
      background: #fff;
      border: 1px solid #dcdcde;
      padding: 15px;
      border-radius: 4px;
      border-left: 4px solid #2271b1;
      box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    }

    .ncl-comp-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .ncl-comp-id-display {
      font-family: monospace;
      font-size: 14px;
      font-weight: 600;
      color: #d63638;
      background: #f6f7f7;
      padding: 4px 8px;
      border-radius: 3px;
      border: 1px solid #dcdcde;
    }

    .ncl-form-row {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }

    .ncl-form-row label {
      width: 120px;
      font-weight: 600;
      color: #50575e;
      flex-shrink: 0;
    }

    .ncl-form-row input[type="text"],
    .ncl-form-row input[type="number"],
    .ncl-form-row input[type="url"],
    .ncl-form-row textarea,
    .ncl-form-row select {
      width: 100%;
      max-width: 600px;
      padding: 6px 8px;
      border: 1px solid #8c8f94;
      border-radius: 3px;
    }

    .ncl-btn {
      cursor: pointer;
      border: none;
      padding: 6px 14px;
      border-radius: 3px;
      font-size: 13px;
      font-weight: 500;
      text-decoration: none;
      display: inline-block;
    }

    .ncl-btn-primary {
      background: #2271b1;
      color: #fff;
      border: 1px solid #2271b1;
    }

    .ncl-btn-primary:hover {
      background: #135e96;
      border-color: #135e96;
    }

    .ncl-btn-danger {
      background: #fff;
      color: #d63638;
      border: 1px solid #d63638;
    }

    .ncl-btn-danger:hover {
      background: #d63638;
      color: #fff;
    }

    .ncl-btn-secondary {
      background: #f6f7f7;
      color: #2271b1;
      border: 1px solid #2271b1;
    }

    .ncl-btn-secondary:hover {
      background: #f0f0f1;
      border-color: #0a4b78;
      color: #0a4b78;
    }

    .ncl-add-comp-container {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px dashed #c3c4c7;
      text-align: right;
    }

    .ncl-add-section-container {
      padding: 30px;
      background: #fff;
      border: 2px dashed #c3c4c7;
      text-align: center;
      border-radius: 4px;
    }

    .ui-sortable-helper {
      opacity: 0.9;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .ui-sortable-placeholder {
      border: 2px dashed #2271b1;
      background: #f0f6fc;
      visibility: visible !important;
      margin-bottom: 25px;
      border-radius: 4px;
    }

    /* --- Collapsible Sections & Components --- */
    .ncl-toggle-btn {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 18px;
      color: #50575e;
      padding: 2px 6px;
      transition: transform 0.2s;
      display: inline-flex;
      align-items: center;
    }

    .ncl-toggle-btn:hover {
      color: #2271b1;
    }

    .ncl-toggle-btn .dashicons {
      transition: transform 0.2s;
    }

    .ncl-section-block.collapsed .ncl-toggle-btn .dashicons {
      transform: rotate(-90deg);
    }

    .ncl-section-block.collapsed .ncl-bg-settings,
    .ncl-section-block.collapsed .ncl-comp-list,
    .ncl-section-block.collapsed .ncl-add-comp-container {
      display: none;
    }

    .ncl-section-block.collapsed .ncl-section-header {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }

    .ncl-comp-item.collapsed .ncl-form-row {
      display: none;
    }

    .ncl-comp-item.collapsed .ncl-comp-header {
      margin-bottom: 0;
    }

    .ncl-comp-toggle {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 16px;
      color: #50575e;
      padding: 2px 4px;
      margin-right: 4px;
      display: inline-flex;
      align-items: center;
    }

    .ncl-comp-toggle:hover {
      color: #2271b1;
    }

    .ncl-comp-toggle .dashicons {
      transition: transform 0.2s;
    }

    .ncl-comp-item.collapsed .ncl-comp-toggle .dashicons {
      transform: rotate(-90deg);
    }

    /* --- CSS Sidebar Layout --- */
    .ncl-css-sidebar-layout {
      display: flex;
      gap: 0;
      padding: 0 !important;
      overflow: hidden;
    }

    .ncl-css-sidebar {
      width: 220px;
      min-width: 220px;
      background: #f6f7f7;
      border-right: 1px solid #c3c4c7;
      padding: 0;
      flex-shrink: 0;
    }

    .ncl-css-sidebar-title {
      font-weight: 700;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #50575e;
      padding: 14px 16px 8px;
    }

    #ncl-css-sidebar-list {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    #ncl-css-sidebar-list li {
      padding: 10px 16px;
      cursor: pointer;
      font-size: 13px;
      color: #1d2327;
      border-left: 3px solid transparent;
      transition: background 0.15s, border-color 0.15s;
    }

    #ncl-css-sidebar-list li:hover {
      background: #e9ecf0;
    }

    #ncl-css-sidebar-list li.active {
      background: #fff;
      border-left-color: #2271b1;
      font-weight: 600;
      color: #2271b1;
    }

    #ncl-css-sidebar-list li .dashicons {
      font-size: 16px;
      width: 16px;
      height: 16px;
      vertical-align: middle;
      margin-right: 6px;
      color: #8c8f94;
    }

    #ncl-css-sidebar-list li.active .dashicons {
      color: #2271b1;
    }

    .ncl-css-editor-panel {
      flex: 1;
      padding: 20px;
      min-width: 0;
    }

    /* --- Tabs Editor --- */
    .ncl-tabs-editor {
      background: #fafafa;
      border: 1px solid #dcdcde;
      border-radius: 4px;
      padding: 15px;
      margin-top: 10px;
    }

    .ncl-tabs-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-bottom: 12px;
    }

    .ncl-tab-item {
      background: #fff;
      border: 1px solid #dcdcde;
      border-left: 3px solid #2271b1;
      padding: 12px;
      border-radius: 3px;
    }

    .ncl-tab-item .input-tab-title {
      display: block;
      width: 100%;
      padding: 6px 8px;
      margin-bottom: 8px;
      border: 1px solid #8c8f94;
      border-radius: 3px;
      font-weight: 600;
      font-size: 13px;
    }

    .ncl-tab-item .input-tab-content {
      display: block;
      width: 100%;
      padding: 8px;
      margin-bottom: 8px;
      border: 1px solid #8c8f94;
      border-radius: 3px;
      font-family: monospace;
      font-size: 12px;
      resize: vertical;
    }

    .ncl-tab-item .btn-delete-tab {
      max-width: 100%;
    }
  </style>

  <script type="text/javascript">
    jQuery(document).ready(function($) {
      let mediaUploader;

      // DATA
      let pageData = <?php echo json_encode($page_data); ?>;
      let cssData = <?php echo json_encode($page_css_data); ?>; // { "hero": ".hero { bg... }", "intro": "..." }

      // Ensure CSS data is object
      if (!cssData || Array.isArray(cssData)) cssData = {};

      // --- MIGRATION Logic ---
      if (pageData && !Array.isArray(pageData) && Object.keys(pageData).length > 0) {
        let newData = [];
        Object.keys(pageData).forEach(secKey => {
          let comps = [];
          Object.keys(pageData[secKey]).forEach(compKey => {
            comps.push({
              id: compKey,
              type: pageData[secKey][compKey].type || 'text',
              value: pageData[secKey][compKey].value || ''
            });
          });
          newData.push({
            section_id: secKey,
            bg_type: 'none',
            bg_value: '',
            components: comps
          });
        });
        pageData = newData;
      } else if (!Array.isArray(pageData)) {
        pageData = [];
      }
      // --- END MIGRATION ---

      const $root = $('#nucleus-page-builder-root');
      const $cssRoot = $('#ncl-css-editor-container');
      const $sectionSelect = $('#ncl-css-section-select');

      // Tracks currently active CSS section in the editor
      let currentCssSection = '';

      const $hiddenInput = $('#_nucleus_page_data_json');
      const $hiddenCssInput = $('#_nucleus_page_css_json');

      // Track collapsed section IDs to preserve state across re-renders
      let collapsedSections = new Set();

      function renderBuilder() {
        // Save currently collapsed sections before re-rendering
        collapsedSections.clear();
        $('.ncl-section-block.collapsed').each(function() {
          const sIndex = $(this).data('sindex');
          const sectionId = pageData[sIndex]?.section_id;
          if (sectionId) {
            collapsedSections.add(sectionId);
          }
        });

        // Destroy existing wp_editor instances to prevent caching issues
        if (typeof wp !== 'undefined' && wp.editor) {
          $('.input-comp-wysiwyg').each(function() {
            wp.editor.remove($(this).attr('id'));
          });
        }

        $root.empty();

        // Build CSS sidebar list
        const $sidebarList = $('#ncl-css-sidebar-list');
        $sidebarList.empty();
        $sidebarList.append(
          `<li data-section="global" class="${currentCssSection === 'global' ? 'active' : ''}"><span class="dashicons dashicons-admin-customizer"></span>Global Custom CSS</li>`
        );

        const $sectionsWrapper = $('<div class="ncl-sections-wrapper"></div>');

        pageData.forEach((section, sIndex) => {
          // Populate CSS sidebar
          $sidebarList.append(
            `<li data-section="${escapeHtml(section.section_id)}" class="${currentCssSection === section.section_id ? 'active' : ''}"><span class="dashicons dashicons-layout"></span>${escapeHtml(section.section_id)}</li>`
          );

          const $sectionBox = $(`
                        <div class="ncl-section-block" data-sindex="${sIndex}">
                            <div class="ncl-section-header">
                                <h3>
                                    <button type="button" class="ncl-toggle-btn btn-toggle-section"><span class="dashicons dashicons-arrow-down-alt2"></span></button>
                                    <span class="dashicons dashicons-menu ncl-drag-handle"></span> 
                                    Section Name: 
                                    <input type="text" class="input-sec-id" data-sindex="${sIndex}" value="${escapeHtml(section.section_id)}" placeholder="e.g. hero" style="margin-left: 10px; padding: 4px 8px; border: 1px solid #8c8f94; border-radius: 3px; font-family: monospace; width: 180px; font-size: 14px; font-weight: normal;"/>
                                    <div style="font-size: 13px; font-weight: normal; color: #50575e; margin-left: 15px; display: inline-block;">
                                        Section CSS ID: <code class="ncl-sec-css-display" style="color: #d63638; background: #f0f0f1; padding: 2px 5px; border-radius: 3px;">#nucleus-section-${escapeHtml(section.section_id)}</code>
                                    </div>
                                </h3>
                                <button type="button" class="ncl-btn ncl-btn-danger btn-delete-section" data-sindex="${sIndex}">Delete Section</button>
                            </div>
                            
                            <div class="ncl-bg-settings">
                                <label>Background:</label>
                                <select class="input-sec-bg-type" data-sindex="${sIndex}">
                                    <option value="none" ${section.bg_type === 'none' ? 'selected' : ''}>None</option>
                                    <option value="color" ${section.bg_type === 'color' ? 'selected' : ''}>Solid Color</option>
                                    <option value="image" ${section.bg_type === 'image' ? 'selected' : ''}>Image</option>
                                </select>
                                
                                ${section.bg_type === 'color' ? 
                                    `<input type="color" class="input-sec-bg-val" data-sindex="${sIndex}" value="${escapeHtml(section.bg_value)}" /> (Hex Color)` 
                                : ''}
                                
                                ${section.bg_type === 'image' ? 
                                    `<input type="text" class="input-sec-bg-val" data-sindex="${sIndex}" value="${escapeHtml(section.bg_value)}" placeholder="Image URL..." style="width: 250px;" />
                                     <button type="button" class="ncl-btn ncl-btn-secondary btn-upload-image" data-sindex="${sIndex}">Select Image</button>`
                                : ''}
                            </div>

                            <div class="ncl-comp-list"></div>
                            <div class="ncl-add-comp-container">
                                <button type="button" class="ncl-btn ncl-btn-secondary btn-add-comp" data-sindex="${sIndex}">+ Add Component</button>
                            </div>
                        </div>
                    `);

          const $compList = $sectionBox.find('.ncl-comp-list');

          if (section.components && section.components.length > 0) {
            section.components.forEach((comp, cIndex) => {
              const fullHtmlId = `${section.section_id}-${comp.id}`;

              $compBox = $(`
                                <div class="ncl-comp-item">
                                    <div class="ncl-comp-header">
                                        <div><button type="button" class="ncl-comp-toggle btn-toggle-comp"><span class="dashicons dashicons-arrow-down-alt2"></span></button><span class="dashicons dashicons-editor-code" style="color: #2271b1; vertical-align: middle;"></span> Component ID: <span class="ncl-comp-id-display">${escapeHtml(fullHtmlId)}</span></div>
                                        <button type="button" class="ncl-btn ncl-btn-danger btn-delete-comp" data-sindex="${sIndex}" data-cindex="${cIndex}">Remove</button>
                                    </div>
                                    <div class="ncl-form-row">
                                        <label>Field Type</label>
                                        <select class="input-comp-type" data-sindex="${sIndex}" data-cindex="${cIndex}">
                                            <optgroup label="Basic">
                                                <option value="text" ${comp.type === 'text' ? 'selected' : ''}>String (Text)</option>
                                                <option value="heading" ${comp.type === 'heading' ? 'selected' : ''}>Heading</option>
                                                <option value="textarea" ${comp.type === 'textarea' ? 'selected' : ''}>Text Area</option>
                                                <option value="image" ${comp.type === 'image' ? 'selected' : ''}>Image URL</option>
                                                <option value="number" ${comp.type === 'number' ? 'selected' : ''}>Number</option>
                                            </optgroup>
                                            <optgroup label="Links & Buttons">
                                                <option value="link_text" ${comp.type === 'link_text' ? 'selected' : ''}>Button (Text + Link)</option>
                                                <option value="url" ${comp.type === 'url' ? 'selected' : ''}>Raw Link URL</option>
                                            </optgroup>
                                            <optgroup label="Interactive & Media">
                                                <option value="tabs" ${comp.type === 'tabs' ? 'selected' : ''}>Tabs / Sidebar</option>
                                                <option value="carousel" ${comp.type === 'carousel' ? 'selected' : ''}>Carousel / Slider</option>
                                                <option value="accordion" ${comp.type === 'accordion' ? 'selected' : ''}>Accordion / FAQ</option>
                                                <option value="video" ${comp.type === 'video' ? 'selected' : ''}>Video Embed</option>
                                            </optgroup>
                                            <optgroup label="Content Blocks">
                                                <option value="testimonial" ${comp.type === 'testimonial' ? 'selected' : ''}>Testimonial / Quote</option>
                                                <option value="stats" ${comp.type === 'stats' ? 'selected' : ''}>Stats / KPI Block</option>
                                                <option value="checklist" ${comp.type === 'checklist' ? 'selected' : ''}>Checklist / Feature List</option>
                                            </optgroup>
                                            <optgroup label="Advanced">
                                                <option value="wysiwyg" ${comp.type === 'wysiwyg' ? 'selected' : ''}>Rich Text Editor (WYSIWYG)</option>
                                                <option value="html" ${comp.type === 'html' ? 'selected' : ''}>Custom HTML / Embed</option>
                                                <option value="code" ${comp.type === 'code' ? 'selected' : ''}>Code Block</option>
                                                <option value="shortcode" ${comp.type === 'shortcode' ? 'selected' : ''}>WP Shortcode (e.g. Form)</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="ncl-form-row">
                                        <label>Component Name</label>
                                        <input type="text" class="input-comp-key" data-sindex="${sIndex}" data-cindex="${cIndex}" value="${escapeHtml(comp.id)}" placeholder="e.g. title, subtitle" />
                                    </div>
                                    ${comp.type === 'tabs' ? `
                                        <div class="ncl-tabs-editor">
                                            <div class="ncl-tabs-list">
                                                ${Array.isArray(comp.value) ? comp.value.map((item, tIndex) => `
                                                    <div class="ncl-tab-item" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}">
                                                        <input type="text" class="input-tab-title" placeholder="Tab Title" value="${escapeHtml(item.title || '')}" />
                                                        <textarea class="input-tab-content" placeholder="Tab Content" rows="3">${escapeHtml(item.content || '')}</textarea>
                                                        <button type="button" class="ncl-btn ncl-btn-danger btn-delete-tab" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}">Remove Tab</button>
                                                    </div>
                                                `).join('') : ''}
                                            </div>
                                            <button type="button" class="ncl-btn ncl-btn-secondary btn-add-tab" data-sindex="${sIndex}" data-cindex="${cIndex}">+ Add Tab Item</button>
                                        </div>
                                    ` : ''}
                                    ${comp.type === 'carousel' ? `
                                        <div class="ncl-carousel-editor" style="background:#fafafa; border:1px solid #dcdcde; border-radius:4px; padding:15px; margin-top:10px;">
                                            <div class="ncl-carousel-list" style="display:flex; flex-direction:column; gap:12px; margin-bottom:12px;">
                                                ${Array.isArray(comp.value) ? comp.value.map((item, tIndex) => `
                                                    <div class="ncl-carousel-item" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}" style="background:#fff; border:1px solid #dcdcde; border-left:3px solid #68d391; padding:12px; border-radius:3px;">
                                                        <div style="display:flex; gap:10px; margin-bottom:8px;">
                                                            <input type="text" class="input-carousel-image" style="flex:1; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px;" placeholder="Image URL" value="${escapeHtml(item.image || '')}" />
                                                            <button type="button" class="ncl-btn ncl-btn-secondary btn-upload-carousel-image" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}">Upload</button>
                                                        </div>
                                                        <input type="text" class="input-carousel-title" placeholder="Slide Title" value="${escapeHtml(item.title || '')}" style="display:block; width:100%; padding:6px 8px; margin-bottom:8px; border:1px solid #8c8f94; border-radius:3px;" />
                                                        <textarea class="input-carousel-content" placeholder="Slide Content (Text)" rows="2" style="display:block; width:100%; padding:8px; margin-bottom:8px; border:1px solid #8c8f94; border-radius:3px;">${escapeHtml(item.content || '')}</textarea>
                                                        <input type="text" class="input-carousel-link" placeholder="Button Link URL (Optional)" value="${escapeHtml(item.link || '')}" style="display:block; width:100%; padding:6px 8px; margin-bottom:8px; border:1px solid #8c8f94; border-radius:3px;" />
                                                        <button type="button" class="ncl-btn ncl-btn-danger btn-delete-carousel-slide" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}">Remove Slide</button>
                                                    </div>
                                                `).join('') : ''}
                                            </div>
                                            <button type="button" class="ncl-btn ncl-btn-secondary btn-add-carousel-slide" data-sindex="${sIndex}" data-cindex="${cIndex}">+ Add Slide</button>
                                        </div>
                                    ` : ''}
                                    ${comp.type === 'checklist' ? `
                                    <div class="ncl-checklist-editor" style="background:#fafafa; border:1px solid #dcdcde; border-radius:4px; padding:15px; margin-top:10px;">
                                    <div class="ncl-checklist-list" style="display:flex; flex-direction:column; gap:10px; margin-bottom:12px;">
                                    ${Array.isArray(comp.value) ? comp.value.map((item, tIndex) => `
                                    <div class="ncl-checklist-item" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}" style="background:#fff; border:1px solid #dcdcde; border-left:3px solid #38a169; padding:10px 12px; border-radius:3px; display:flex; gap:10px; align-items:center;">
                                    <input type="text" class="input-checklist-item" placeholder="List item text" value="${escapeHtml(item || '')}" style="flex:1; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px;" />
                                    <button type="button" class="ncl-btn ncl-btn-danger btn-delete-checklist" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}">Remove</button>
                                    </div>
                                    `).join('') : ''}
                                    </div>
                                    <button type="button" class="ncl-btn ncl-btn-secondary btn-add-checklist" data-sindex="${sIndex}" data-cindex="${cIndex}">+ Add Item</button>
                                    </div>
                                    ` : ''}
                                    ${comp.type === 'accordion' ? `
                                        <div class="ncl-accordion-editor" style="background:#fafafa; border:1px solid #dcdcde; border-radius:4px; padding:15px; margin-top:10px;">
                                            <div class="ncl-accordion-list" style="display:flex; flex-direction:column; gap:12px; margin-bottom:12px;">
                                                ${Array.isArray(comp.value) ? comp.value.map((item, tIndex) => `
                                                    <div class="ncl-accordion-item" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}" style="background:#fff; border:1px solid #dcdcde; border-left:3px solid #f56565; padding:12px; border-radius:3px;">
                                                        <input type="text" class="input-accordion-title" placeholder="Accordion Title" value="${escapeHtml(item.title || '')}" style="display:block; width:100%; padding:6px 8px; margin-bottom:8px; border:1px solid #8c8f94; border-radius:3px;" />
                                                        <textarea class="input-accordion-content" placeholder="Accordion Content" rows="3" style="display:block; width:100%; padding:8px; margin-bottom:8px; border:1px solid #8c8f94; border-radius:3px;">${escapeHtml(item.content || '')}</textarea>
                                                        <button type="button" class="ncl-btn ncl-btn-danger btn-delete-accordion" data-sindex="${sIndex}" data-cindex="${cIndex}" data-tindex="${tIndex}">Remove Item</button>
                                                    </div>
                                                `).join('') : ''}
                                            </div>
                                            <button type="button" class="ncl-btn ncl-btn-secondary btn-add-accordion" data-sindex="${sIndex}" data-cindex="${cIndex}">+ Add Accordion Item</button>
                                        </div>
                                    ` : ''}
                                    ${comp.type === 'testimonial' ? `
                                        <div class="ncl-testimonial-editor" data-sindex="${sIndex}" data-cindex="${cIndex}" style="background:#fafafa; border:1px solid #dcdcde; border-radius:4px; padding:15px; margin-top:10px;">
                                            <textarea class="input-testimonial-quote" placeholder="Testimonial Quote" rows="3" style="display:block; width:100%; padding:8px; margin-bottom:8px; border:1px solid #8c8f94; border-radius:3px;">${escapeHtml(comp.value?.quote || '')}</textarea>
                                            <div style="display:flex; gap:10px;">
                                                <input type="text" class="input-testimonial-author" placeholder="Author Name" value="${escapeHtml(comp.value?.author || '')}" style="flex:1; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px;" />
                                                <div style="flex:1; display:flex; gap:10px;">
                                                    <input type="text" class="input-testimonial-image" placeholder="Author Avatar URL" value="${escapeHtml(comp.value?.image || '')}" style="flex:1; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px;" />
                                                    <button type="button" class="ncl-btn ncl-btn-secondary btn-upload-testimonial-image" data-sindex="${sIndex}" data-cindex="${cIndex}">Upload</button>
                                                </div>
                                            </div>
                                        </div>
                                    ` : ''}
                                    <div class="ncl-form-row" style="align-items: flex-start;">
                                        <label style="padding-top:6px;">Content</label>
                                        ${(comp.type === 'tabs' || comp.type === 'carousel' || comp.type === 'accordion' || comp.type === 'testimonial' || comp.type === 'checklist') ? '' :
                                        comp.type === 'wysiwyg'
                                            ? `<div style="width: 100%; max-width: 800px; background: #fff;"><textarea id="wysiwyg_${sIndex}_${cIndex}" class="input-comp-wysiwyg" data-sindex="${sIndex}" data-cindex="${cIndex}" style="width:100%; height: 250px;">${escapeHtml(typeof comp.value === 'string' ? comp.value : '')}</textarea></div>` :
                                        (comp.type === 'html' || comp.type === 'code')
                                            ? `<textarea class="input-comp-val input-comp-html" data-sindex="${sIndex}" data-cindex="${cIndex}" rows="6" style="font-family: monospace; background: #2d2d2d; color: #ccc;" placeholder="${comp.type === 'code' ? '/* Raw Code Snippet */' : '<!-- raw HTML here -->'}">${escapeHtml(typeof comp.value === 'string' ? comp.value : '')}</textarea>` :
                                        comp.type === 'textarea'
                                            ? `<textarea class="input-comp-val" data-sindex="${sIndex}" data-cindex="${cIndex}" rows="4">${escapeHtml(typeof comp.value === 'string' ? comp.value : '')}</textarea>`
                                            : `<input type="${comp.type === 'number' ? 'number' : 'text'}" class="input-comp-val" data-sindex="${sIndex}" data-cindex="${cIndex}" value="${escapeHtml(typeof comp.value === 'string' ? comp.value : '')}" placeholder="${comp.type === 'video' ? 'YouTube / Vimeo URL' : comp.type === 'shortcode' ? '[your_shortcode]' : 'Value / Text'}" />`
                                        }
                                        ${comp.type === 'image' ? 
                                            `<button type="button" style="margin-left: 10px;" class="ncl-btn ncl-btn-secondary btn-upload-comp-image" data-sindex="${sIndex}" data-cindex="${cIndex}">Upload File</button>`
                                        : ''}
                                        
                                        ${comp.type === 'heading' ? 
                                            `<select class="input-comp-meta" data-sindex="${sIndex}" data-cindex="${cIndex}" style="margin-left:10px; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px;">
                                                <option value="h1" ${comp.meta === 'h1' ? 'selected' : ''}>H1</option>
                                                <option value="h2" ${comp.meta === 'h2' || !comp.meta ? 'selected' : ''}>H2</option>
                                                <option value="h3" ${comp.meta === 'h3' ? 'selected' : ''}>H3</option>
                                                <option value="h4" ${comp.meta === 'h4' ? 'selected' : ''}>H4</option>
                                                <option value="h5" ${comp.meta === 'h5' ? 'selected' : ''}>H5</option>
                                                <option value="h6" ${comp.meta === 'h6' ? 'selected' : ''}>H6</option>
                                            </select>`
                                        : ''}

                                        ${comp.type === 'stats' ? 
                                            `<input type="text" class="input-comp-meta" data-sindex="${sIndex}" data-cindex="${cIndex}" value="${escapeHtml(comp.meta || '')}" placeholder="Label (e.g. Happy Clients)" style="margin-left:10px;" />` 
                                        : ''}
                                        
                                        ${comp.type === 'link_text' ? 
                                            `<input type="text" class="input-comp-meta" data-sindex="${sIndex}" data-cindex="${cIndex}" value="${escapeHtml(comp.meta || '')}" placeholder="Link URL (http://...)" style="margin-left:10px;" />` 
                                        : ''}
                                    </div>
                                </div>
                            `);
              $compList.append($compBox);
            });
          } else {
            $compList.append(
              '<p style="color:#646970; font-style:italic;">No components added to this section yet.</p>'
            );
          }

          $sectionsWrapper.append($sectionBox);
        });

        $root.append($sectionsWrapper);
        $root.append(`
                    <div class="ncl-add-section-container">
                        <button type="button" class="ncl-btn ncl-btn-primary btn-add-section" style="font-size: 15px; padding: 10px 20px;">+ Add New Section</button>
                    </div>
                `);

        // Restore collapsed state
        collapsedSections.forEach(sectionId => {
          $sectionBlock = $root.find('.ncl-section-block').filter(function() {
            const sIndex = $(this).data('sindex');
            return pageData[sIndex]?.section_id === sectionId;
          });
          if ($sectionBlock.length) {
            $sectionBlock.addClass('collapsed');
          }
        });

        // Initialize Sortable for Drag and Drop
        $sectionsWrapper.sortable({
          handle: '.ncl-section-header',
          placeholder: 'ui-sortable-placeholder',
          forcePlaceholderSize: true,
          update: function(event, ui) {
            reorderDataArray();
          }
        });

        // Initialize WYSIWYG editors
        if (typeof wp !== 'undefined' && wp.editor) {
          $('.input-comp-wysiwyg').each(function() {
            const id = $(this).attr('id');
            const sIndex = $(this).data('sindex');
            const cIndex = $(this).data('cindex');

            wp.editor.initialize(id, {
              tinymce: {
                wpautop: true,
                setup: function(ed) {
                  ed.on('keyup change redo undo', function() {
                    ed.save(); // Save visual content back to textarea
                    $('#' + id).trigger(
                      'change'); // Trigger our saving logic
                  });
                }
              },
              quicktags: true
            });
          });
        }

        syncHiddenInput();
        renderCssEditor(); // Re-render CSS portion
      }

      // NEW: Render just the editor for the stored/current selection
      function renderCssEditor() {
        $cssRoot.empty();

        if (!currentCssSection) {
          $cssRoot.html(
            '<div style="padding: 40px; text-align: center; color: #666; background: #f9f9f9; border: 1px solid #eee; border-radius: 4px;">Select a section from the sidebar to edit its CSS.</div>'
          );
          return;
        }

        const cssContent = cssData[currentCssSection] || '';
        let placeholder = '';
        if (currentCssSection === 'global') {
          placeholder = "/* Global CSS — applies to the entire page */\nbody {\n\n}";
        } else {
          // Build a helpful placeholder showing component IDs
          const section = pageData.find(s => s.section_id === currentCssSection);
          let compHints = '';
          if (section && section.components && section.components.length > 0) {
            compHints = section.components.map(c =>
              `#${currentCssSection}-${c.id} {\n    /* style this component */\n}`).join('\n\n');
          } else {
            compHints = `/* Add components in Content Builder first */`;
          }
          placeholder = `/* CSS for section: ${currentCssSection} */\n${compHints}`;
        }

        const $editor = $(`
            <div class="ncl-css-editor-single">
                <textarea class="ncl-css-textarea" id="ncl-active-css-editor" placeholder="${placeholder}" style="height: 300px;">${escapeHtml(cssContent)}</textarea>
            </div>
        `);

        $cssRoot.append($editor);
      }

      function syncCssInput() {
        $hiddenCssInput.val(JSON.stringify(cssData));
      }

      function reorderDataArray() {
        const newData = [];
        $('.ncl-section-block').each(function() {
          const originalIndex = $(this).data('sindex');
          newData.push(pageData[originalIndex]);
        });
        pageData = newData;
        renderBuilder(); // Re-render to fix indices
      }

      function syncHiddenInput() {
        $hiddenInput.val(JSON.stringify(pageData));
      }

      function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe).replace(/[&<"']/g, function(m) {
          switch (m) {
            case '&':
              return '&amp;';
            case '<':
              return '&lt;';
            case '"':
              return '&quot;';
            case "'":
              return '&#039;';
          }
        });
      }

      // --- Interactions --- //

      // TABS
      $('.ncl-tab-btn').on('click', function() {
        $('.ncl-tab-btn').removeClass('active');
        $(this).addClass('active');
        const tab = $(this).data('tab');
        $('.ncl-tab-pane').removeClass('active');
        $('#ncl-tab-content-' + tab).addClass('active');
      });

      // CSS Interactions
      // 1. Handle Dropdown Change (kept for backward compat, sidebar is primary)
      $sectionSelect.on('change', function() {
        const val = $(this).val();
        currentCssSection = val;
        renderCssEditor();
      });

      // 2. CSS Sidebar Click
      $(document).on('click', '#ncl-css-sidebar-list li', function() {
        const val = $(this).data('section');
        currentCssSection = val;
        $('#ncl-css-sidebar-list li').removeClass('active');
        $(this).addClass('active');

        // Auto-initialize if empty
        if (val && !cssData[val]) {
          if (val === 'global') {
            cssData[val] = "/* Global Styles */\nbody {\n\n}";
          } else {
            const section = pageData.find(s => s.section_id === val);
            let starterCss = `/* CSS for section: ${val} */\n`;
            if (section && section.components && section.components.length > 0) {
              starterCss += section.components.map(c => `#${val}-${c.id} {\n\n}`).join('\n\n');
            }
            cssData[val] = starterCss;
          }
          syncCssInput();
        }

        renderCssEditor();
      });

      // 3. Live Type Saving
      $cssRoot.on('input', '#ncl-active-css-editor', function() {
        if (currentCssSection) {
          cssData[currentCssSection] = $(this).val();
          syncCssInput();
        }
      });

      // Content Interactions (Existing)
      $root.on('click', '.btn-add-section', function() {
        // Generate a readable default name like "new-section", "new-section-2", etc.
        let baseName = 'new-section';
        let newId = baseName;
        let counter = 2;
        while (pageData.some(s => s.section_id === newId)) {
          newId = baseName + '-' + counter;
          counter++;
        }
        pageData.push({
          section_id: newId,
          bg_type: 'none',
          bg_value: '',
          components: []
        });
        renderBuilder();
      });

      $root.on('click', '.btn-delete-section', function() {
        if (confirm('Are you sure you want to delete this entire section and all its contents?')) {
          const sIndex = $(this).data('sindex');
          pageData.splice(sIndex, 1);
          renderBuilder();
        }
      });

      $root.on('input', '.input-sec-id', function() {
        const sIndex = $(this).data('sindex');
        let newId = $(this).val().trim().toLowerCase().replace(/[^a-z0-9_]/g, '-');
        pageData[sIndex].section_id = newId;

        // Live update the frontend IDs displayed for all components in this section
        $(this).closest('.ncl-section-block').find('.ncl-sec-css-display').text('#nucleus-section-' + (
          newId || '[empty]'));
        $(this).closest('.ncl-section-block').find('.ncl-comp-item').each(function() {
          const cIndex = $(this).find('.input-comp-key').data('cindex');
          const compId = pageData[sIndex].components[cIndex].id;
          $(this).find('.ncl-comp-id-display').text(newId + '-' + (compId || '[empty]'));
        });
        syncHiddenInput();
      });

      // Toggle section collapse
      $root.on('click', '.btn-toggle-section', function(e) {
        e.stopPropagation();
        $(this).closest('.ncl-section-block').toggleClass('collapsed');
      });

      // Toggle component collapse
      $root.on('click', '.btn-toggle-comp', function(e) {
        e.stopPropagation();
        $(this).closest('.ncl-comp-item').toggleClass('collapsed');
      });

      $root.on('click', '.btn-add-comp', function() {
        const sIndex = $(this).data('sindex');
        pageData[sIndex].components.push({
          id: '',
          type: 'text',
          value: '',
          meta: ''
        });
        renderBuilder();
      });

      $root.on('click', '.btn-delete-comp', function() {
        if (confirm('Delete this component?')) {
          const sIndex = $(this).data('sindex');
          const cIndex = $(this).data('cindex');
          pageData[sIndex].components.splice(cIndex, 1);
          renderBuilder();
        }
      });

      // Updating Data dynamically
      $root.on('change', '.input-sec-bg-type', function() {
        const sIndex = $(this).data('sindex');
        pageData[sIndex].bg_type = $(this).val();

        // Reset value when switching types
        if (pageData[sIndex].bg_type === 'color') pageData[sIndex].bg_value = '#ffffff';
        else pageData[sIndex].bg_value = '';

        renderBuilder();
      });

      $root.on('input change', '.input-sec-bg-val', function() {
        const sIndex = $(this).data('sindex');
        pageData[sIndex].bg_value = $(this).val();
        syncHiddenInput();
      });

      // Handle Component Type Change
      $root.on('change', '.input-comp-type', function() {
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        const newType = $(this).val();
        pageData[sIndex].components[cIndex].type = newType;

        // Initialize tabs array if switching to tabs type
        if (newType === 'tabs' && !Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value = [{
            title: '',
            content: ''
          }];
        }

        // Initialize carousel array if switching to carousel type
        if (newType === 'carousel' && !Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value = [{
            image: '',
            title: '',
            content: '',
            link: ''
          }];
        }

        // Initialize accordion array
        if (newType === 'accordion' && !Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value = [{
            title: '',
            content: ''
          }];
        }

        // Initialize checklist array
        if (newType === 'checklist' && !Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value = [];
        }

        // Initialize testimonial object
        if (newType === 'testimonial' && typeof pageData[sIndex].components[cIndex].value !==
          'object') {
          pageData[sIndex].components[cIndex].value = {
            quote: '',
            author: '',
            image: ''
          };
        }

        renderBuilder();
      });

      // Tabs: Add Tab Item
      $root.on('click', '.btn-add-tab', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');

        if (!Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value = [];
        }
        pageData[sIndex].components[cIndex].value.push({
          title: '',
          content: ''
        });
        renderBuilder();
      });

      // Tabs: Delete Tab Item
      $root.on('click', '.btn-delete-tab', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        const tIndex = $(this).data('tindex');

        if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value.splice(tIndex, 1);
          renderBuilder();
        }
      });

      // Tabs: Update Tab Title
      $root.on('input', '.input-tab-title', function() {
        const sIndex = $(this).closest('.ncl-tab-item').data('sindex');
        const cIndex = $(this).closest('.ncl-tab-item').data('cindex');
        const tIndex = $(this).closest('.ncl-tab-item').data('tindex');

        if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value[tIndex].title = $(this).val();
          syncHiddenInput();
        }
      });

      // Tabs: Update Tab Content
      $root.on('input', '.input-tab-content', function() {
        const sIndex = $(this).closest('.ncl-tab-item').data('sindex');
        const cIndex = $(this).closest('.ncl-tab-item').data('cindex');
        const tIndex = $(this).closest('.ncl-tab-item').data('tindex');

        if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value[tIndex].content = $(this).val();
          syncHiddenInput();
        }
      });

      // Carousel: Add Slide
      $root.on('click', '.btn-add-carousel-slide', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');

        if (!Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value = [];
        }
        pageData[sIndex].components[cIndex].value.push({
          image: '',
          title: '',
          content: '',
          link: ''
        });
        renderBuilder();
      });

      // Carousel: Delete Slide
      $root.on('click', '.btn-delete-carousel-slide', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        const tIndex = $(this).data('tindex');

        if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value.splice(tIndex, 1);
          renderBuilder();
        }
      });

      // Carousel: Update Fields
      $root.on('input',
        '.input-carousel-image, .input-carousel-title, .input-carousel-content, .input-carousel-link',
        function() {
          const $item = $(this).closest('.ncl-carousel-item');
          const sIndex = $item.data('sindex');
          const cIndex = $item.data('cindex');
          const tIndex = $item.data('tindex');

          if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
            pageData[sIndex].components[cIndex].value[tIndex] = {
              image: $item.find('.input-carousel-image').val(),
              title: $item.find('.input-carousel-title').val(),
              content: $item.find('.input-carousel-content').val(),
              link: $item.find('.input-carousel-link').val()
            };
            syncHiddenInput();
          }
        });

      // Carousel: Upload Image
      $root.on('click', '.btn-upload-carousel-image', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        const tIndex = $(this).data('tindex');

        let compUploader = wp.media({
          title: 'Choose Slide Image',
          button: {
            text: 'Choose Image'
          },
          multiple: false
        });

        compUploader.on('select', function() {
          const attachment = compUploader.state().get('selection').first().toJSON();
          if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
            pageData[sIndex].components[cIndex].value[tIndex].image = attachment.url;
            renderBuilder();
          }
        });
        compUploader.open();
      });

      // --- Checklist: Add item ---
      $root.on('click', '.btn-add-checklist', function() {
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        if (!Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value = [];
        }
        pageData[sIndex].components[cIndex].value.push('');
        renderBuilder();
      });

      // --- Checklist: Remove item ---
      $root.on('click', '.btn-delete-checklist', function() {
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        const tIndex = $(this).data('tindex');
        pageData[sIndex].components[cIndex].value.splice(tIndex, 1);
        renderBuilder();
      });

      // --- Checklist: Update item text on change ---
      $root.on('input', '.input-checklist-item', function() {
        const $item = $(this).closest('.ncl-checklist-item');
        const sIndex = $item.data('sindex');
        const cIndex = $item.data('cindex');
        const tIndex = $item.data('tindex');
        if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value[tIndex] = $(this).val();
          syncHiddenInput();
        }
      });

      // Accordion: Add Item
      $root.on('click', '.btn-add-accordion', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        if (!Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value = [];
        }
        pageData[sIndex].components[cIndex].value.push({
          title: '',
          content: ''
        });
        renderBuilder();
      });

      // Accordion: Delete Item
      $root.on('click', '.btn-delete-accordion', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        const tIndex = $(this).data('tindex');
        if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value.splice(tIndex, 1);
          renderBuilder();
        }
      });

      // Accordion: Update Fields
      $root.on('input', '.input-accordion-title, .input-accordion-content', function() {
        const $item = $(this).closest('.ncl-accordion-item');
        const sIndex = $item.data('sindex');
        const cIndex = $item.data('cindex');
        const tIndex = $item.data('tindex');
        if (Array.isArray(pageData[sIndex].components[cIndex].value)) {
          pageData[sIndex].components[cIndex].value[tIndex] = {
            title: $item.find('.input-accordion-title').val(),
            content: $item.find('.input-accordion-content').val()
          };
          syncHiddenInput();
        }
      });

      // Testimonial: Update Fields
      $root.on('input', '.input-testimonial-quote, .input-testimonial-author, .input-testimonial-image',
        function() {
          const $editor = $(this).closest('.ncl-testimonial-editor');
          const sIndex = $editor.data('sindex');
          const cIndex = $editor.data('cindex');
          pageData[sIndex].components[cIndex].value = {
            quote: $editor.find('.input-testimonial-quote').val(),
            author: $editor.find('.input-testimonial-author').val(),
            image: $editor.find('.input-testimonial-image').val()
          };
          syncHiddenInput();
        });

      // Testimonial: Upload Image
      $root.on('click', '.btn-upload-testimonial-image', function(e) {
        e.preventDefault();
        const $editor = $(this).closest('.ncl-testimonial-editor');
        const sIndex = $editor.data('sindex');
        const cIndex = $editor.data('cindex');

        let compUploader = wp.media({
          title: 'Choose Author Image',
          button: {
            text: 'Choose Image'
          },
          multiple: false
        });
        compUploader.on('select', function() {
          const attachment = compUploader.state().get('selection').first().toJSON();
          if (typeof pageData[sIndex].components[cIndex].value !== 'object') {
            pageData[sIndex].components[cIndex].value = {
              quote: '',
              author: '',
              image: ''
            };
          }
          pageData[sIndex].components[cIndex].value.image = attachment.url;
          renderBuilder();
        });
        compUploader.open();
      });

      // Sanitize Section ID on change
      $root.on('change', '.input-sec-id', function() {
        const sIndex = $(this).data('sindex');
        let val = $(this).val().trim();
        // Convert to slug: lowercase, replace spaces/specials with dashes
        val = val.toLowerCase().replace(/[^a-z0-9-]/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
        if (!val) val = 'section-' + Math.floor(Math.random() * 1000);

        pageData[sIndex].section_id = val;
        renderBuilder(); // Refresh UI to show sanitized value
      });

      $root.on('input change', '.input-comp-val, .input-comp-wysiwyg', function() {
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        pageData[sIndex].components[cIndex].value = $(this).val();
        syncHiddenInput();
      });

      // New META field listener (for links)
      $root.on('input change', '.input-comp-meta', function() {
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        pageData[sIndex].components[cIndex].meta = $(this).val();
        syncHiddenInput();
      });

      // Handle Component Key Renaming Live (Updates the UI frontend ID string instantly)
      $root.on('input', '.input-comp-key', function() {
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');
        let newId = $(this).val().trim().toLowerCase().replace(/[^a-z0-9_]/g, '-');

        pageData[sIndex].components[cIndex].id = newId;

        // Live visual update on the header
        $(this).closest('.ncl-comp-item')
          .find('.ncl-comp-id-display')
          .text(pageData[sIndex].section_id + '-' + (newId || '[empty]'));

        syncHiddenInput();
      });

      // WP Media Uploader for Section Backgrounds
      $root.on('click', '.btn-upload-image', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');

        if (mediaUploader) {
          mediaUploader.open();
          return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
          title: 'Choose Background Image',
          button: {
            text: 'Choose Image'
          },
          multiple: false
        });

        mediaUploader.on('select', function() {
          const attachment = mediaUploader.state().get('selection').first().toJSON();
          pageData[sIndex].bg_value = attachment.url;
          renderBuilder();
        });
        mediaUploader.open();
      });

      // WP Media Uploader for Component Images
      $root.on('click', '.btn-upload-comp-image', function(e) {
        e.preventDefault();
        const sIndex = $(this).data('sindex');
        const cIndex = $(this).data('cindex');

        let compUploader = wp.media({
          title: 'Choose Image Component',
          button: {
            text: 'Choose Image'
          },
          multiple: false
        });

        compUploader.on('select', function() {
          const attachment = compUploader.state().get('selection').first().toJSON();
          pageData[sIndex].components[cIndex].value = attachment.url;
          renderBuilder();
        });
        compUploader.open();
      });

      // Initialize UI
      renderBuilder();
    });
  </script>
<?php
}

/**
 * =====================================
 * Save Meta Box Data
 * =====================================
 */
function nucleus_save_page_builder_data($post_id)
{
  // Check if nonce is set and valid.
  if (!isset($_POST['nucleus_page_builder_meta_box_nonce']) || !wp_verify_nonce($_POST['nucleus_page_builder_meta_box_nonce'], 'nucleus_save_page_builder_data')) {
    return;
  }

  // Ignore autosaves
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  // Check permissions
  if (isset($_POST['post_type']) && 'nucleus_page' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id)) return;
  } elseif (isset($_POST['post_type']) && 'nucleus_hf_set' == $_POST['post_type']) {
    if (!current_user_can('edit_page', $post_id)) return;
  } else {
    if (!current_user_can('edit_post', $post_id)) return;
  }

  // Save Default HF Set configuration
  if (isset($_POST['post_type']) && 'nucleus_hf_set' == $_POST['post_type']) {
    if (isset($_POST['_ncl_is_default_hf']) && $_POST['_ncl_is_default_hf'] == '1') {
      update_option('nucleus_default_hf_set', $post_id);
    } else {
      // Only remove if this specific one was the default
      if (get_option('nucleus_default_hf_set') == $post_id) {
        delete_option('nucleus_default_hf_set');
      }
    }
  }

  // Save Page Data - using base64 to prevent WordPress/MySQL Emoji truncation bugs
  if (isset($_POST['_nucleus_page_data_json'])) {
    $json_string = wp_unslash($_POST['_nucleus_page_data_json']);
    $decoded_data = json_decode($json_string, true);
    if (is_array($decoded_data)) {
      update_post_meta($post_id, '_nucleus_page_components', base64_encode($json_string));
    } else {
      delete_post_meta($post_id, '_nucleus_page_components');
    }
  }

  // Save Page CSS - using base64 to prevent WordPress/MySQL Emoji truncation bugs
  if (isset($_POST['_nucleus_page_css_json'])) {
    $json_string = wp_unslash($_POST['_nucleus_page_css_json']);
    $decoded_data = json_decode($json_string, true);
    if (is_array($decoded_data)) {
      update_post_meta($post_id, '_nucleus_page_css', base64_encode($json_string));
    } else {
      delete_post_meta($post_id, '_nucleus_page_css');
    }
  }

  // Save HF selection on Page
  if (isset($_POST['_nucleus_selected_hf_set'])) {
    update_post_meta($post_id, '_nucleus_selected_hf_set', sanitize_text_field($_POST['_nucleus_selected_hf_set']));
  }

  // Save HF Data - using base64 for data persistence to prevent WordPress/MySQL Emoji truncation bugs
  if (isset($_POST['_nucleus_header_data_json'])) {
    $json_string = wp_unslash($_POST['_nucleus_header_data_json']);
    $decoded_data = json_decode($json_string, true);
    if (is_array($decoded_data)) update_post_meta($post_id, '_nucleus_header_components', base64_encode($json_string));
  }
  if (isset($_POST['_nucleus_footer_data_json'])) {
    $json_string = wp_unslash($_POST['_nucleus_footer_data_json']);
    $decoded_data = json_decode($json_string, true);
    if (is_array($decoded_data)) update_post_meta($post_id, '_nucleus_footer_components', base64_encode($json_string));
  }
  if (isset($_POST['_nucleus_hf_css_json'])) {
    $json_string = wp_unslash($_POST['_nucleus_hf_css_json']);
    $decoded_data = json_decode($json_string, true);
    if (is_array($decoded_data)) update_post_meta($post_id, '_nucleus_hf_css', base64_encode($json_string));
  }
}
add_action('save_post', 'nucleus_save_page_builder_data');

/**
 * =====================================
 * Shortcode: [nucleus_page_content]
 * =====================================
 * Renders the dynamic sections and CSS for the current nucleus_page.
 * Use this shortcode inside Oxygen Builder to display the page content.
 */
function nucleus_page_content_shortcode($atts)
{
  // Allow specifying a post_id, default to current post
  $atts = shortcode_atts(array('id' => 0), $atts);
  $post_id = intval($atts['id']) > 0 ? intval($atts['id']) : get_the_ID();

  if (!$post_id) return '';

  // Backward compatible: support both base64-encoded strings and raw arrays
  $page_meta = get_post_meta($post_id, '_nucleus_page_components', true);
  $page_data = is_string($page_meta) ? json_decode(base64_decode($page_meta), true) : $page_meta;

  $css_meta = get_post_meta($post_id, '_nucleus_page_css', true);
  $page_css = is_string($css_meta) ? json_decode(base64_decode($css_meta), true) : $css_meta;

  ob_start();

  // -- Default Styles --
  echo "<style type='text/css'>
        .nucleus-page-container {
            width: 100%;
            color: #1d2327;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .nucleus-section {
            display: block;
            width: 100%;
            position: relative;
            padding: 60px 20px;
            box-sizing: border-box;
        }
        .nucleus-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .nucleus-title {
            font-size: 2.5em;
            font-weight: 700;
            margin: 0 0 15px 0;
            color: inherit;
        }
        .nucleus-subtitle {
            font-size: 1.4em;
            font-weight: 400;
            margin: 0 0 20px 0;
            opacity: 0.85;
            color: inherit;
        }
        .nucleus-text {
            font-size: 1em;
            line-height: 1.7;
            margin-bottom: 15px;
            color: inherit;
        }
        .nucleus-number {
            font-size: 2em;
            font-weight: 700;
            color: inherit;
        }
        .nucleus-component {
            margin-bottom: 20px;
        }
        .nucleus-btn-primary {
            display: inline-block;
            padding: 12px 24px;
            background: #2271b1;
            color: #fff !important;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .nucleus-btn-primary:hover {
            background: #135e96;
        }
        .nucleus-link {
            color: #2271b1;
            text-decoration: underline;
        }
        .nucleus-tabs {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        .nucleus-tabs-sidebar {
            flex: 0 0 200px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .nucleus-tabs-button {
            padding: 12px 16px;
            background: #f0f0f1;
            border: 1px solid #c3c4c7;
            border-left: 3px solid transparent;
            cursor: pointer;
            text-align: left;
            font-weight: 500;
            border-radius: 3px 0 0 3px;
            color: #1d2327;
            transition: background 0.2s, border-color 0.2s;
        }
        .nucleus-tabs-button:hover {
            background: #e9ecf0;
        }
        .nucleus-tabs-button.active {
            background: #fff;
            border-left-color: #2271b1;
            color: #2271b1;
        }
        .nucleus-tabs-content {
            flex: 1;
            min-width: 0;
        }
        .nucleus-tab-pane {
            display: none;
            padding: 20px;
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 3px;
        }
        .nucleus-tab-pane.active {
            display: block;
        }
        .nucleus-carousel-wrapper { position: relative; overflow: hidden; width: 100%; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .nucleus-carousel-inner { display: flex; transition: transform 0.5s ease; width: 100%; }
        .nucleus-carousel-slide { flex: 0 0 100%; width: 100%; box-sizing: border-box; }
        .nucleus-carousel-slide-content { display: flex; flex-wrap: wrap; background: #fff; align-items: stretch; min-height: 100%; }
        .nucleus-carousel-slide-image { flex: 1; min-width: 300px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .nucleus-carousel-slide-image img { width: 100%; height: 100%; object-fit: cover; }
        .nucleus-carousel-slide-text { flex: 1; padding: 40px; min-width: 300px; display: flex; flex-direction: column; justify-content: center; }
        .nucleus-carousel-slide-title { font-size: 2em; margin-top: 0; margin-bottom: 20px; font-weight: 700; color: #1d2327; }
        .nucleus-carousel-slide-desc { font-size: 1.1em; color: #555; margin-bottom: 25px; line-height: 1.6; }
        .nucleus-carousel-controls { position: absolute; top: 50%; width: 100%; display: flex; justify-content: space-between; transform: translateY(-50%); pointer-events: none; }
        .nucleus-carousel-btn { background: rgba(0,0,0,0.5); color: #fff; border: none; padding: 15px 20px; cursor: pointer; pointer-events: auto; font-size: 20px; transition: background 0.3s; }
        .nucleus-carousel-btn:hover { background: rgba(0,0,0,0.8); }
        .nucleus-carousel-dots { position: absolute; bottom: 15px; width: 100%; display: flex; justify-content: center; gap: 8px; z-index: 10; margin: 0; padding: 0; }
        .nucleus-carousel-dot { width: 12px; height: 12px; background: rgba(0,0,0,0.3); border-radius: 50%; cursor: pointer; border: none; padding: 0; transition: background 0.3s; }
        .nucleus-carousel-dot.active { background: #2271b1; }
        
        .nucleus-accordion { border: 1px solid #c3c4c7; border-radius: 4px; overflow: hidden; margin-bottom: 20px; }
        .nucleus-accordion-item { border-bottom: 1px solid #c3c4c7; background: #fff; }
        .nucleus-accordion-item:last-child { border-bottom: none; }
        .nucleus-accordion-header { width: 100%; text-align: left; padding: 15px 20px; font-weight: 600; font-size: 1.1em; background: #fafafa; border: none; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s; color: #1d2327; margin: 0; }
        .nucleus-accordion-header:hover { background: #f0f0f1; }
        .nucleus-accordion-header.active { background: #fff; border-bottom: 1px solid #eee; }
        .nucleus-accordion-icon { transition: transform 0.3s; font-family: monospace; font-size: 1.2em; font-weight: bold; }
        .nucleus-accordion-header.active .nucleus-accordion-icon { transform: rotate(45deg); }
        .nucleus-accordion-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .nucleus-accordion-content-inner { padding: 20px; }
        
        .nucleus-testimonial { padding: 30px; background: #f9f9f9; border-radius: 8px; font-style: italic; border-left: 4px solid #2271b1; margin-bottom: 20px; }
        .nucleus-testimonial-quote { font-size: 1.25em; line-height: 1.6; margin-bottom: 20px; color: #3c434a; }
        .nucleus-testimonial-author-area { display: flex; align-items: center; gap: 15px; }
        .nucleus-testimonial-avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
        .nucleus-testimonial-author { font-weight: 600; font-style: normal; color: #1d2327; }
        
        .nucleus-stats { text-align: center; padding: 20px; margin-bottom: 20px; }
        .nucleus-stats-number { font-size: 3em; font-weight: 800; color: #2271b1; line-height: 1; margin-bottom: 10px; }
        .nucleus-stats-label { font-size: 1.1em; font-weight: 500; color: #50575e; text-transform: uppercase; letter-spacing: 1px; }

        .nucleus-code-block { background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 6px; overflow-x: auto; font-family: monospace; font-size: 0.9em; margin-bottom: 20px; }
        .nucleus-video-wrapper { position: relative; width: 100%; border-radius: 8px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        @media (max-width: 768px) {\n            .nucleus-section { padding: 40px 15px; }\n            .nucleus-title { font-size: 1.8em; }\n            .nucleus-tabs { flex-direction: column; }\n            .nucleus-tabs-sidebar { flex: 1; }\n            .nucleus-tabs-button { border-radius: 3px; }\n        }
    </style>";

  // -- Custom CSS from Builder --
  if (!empty($page_css) && is_array($page_css)) {
    echo "\n<style type='text/css' id='nucleus-page-custom-css'>\n";
    foreach ($page_css as $section_id => $css_block) {
      if (!empty($css_block)) {
        echo "/* Section: " . esc_html($section_id) . " */\n";
        echo strip_tags($css_block) . "\n";
      }
    }
    echo "</style>\n";
  }

  echo '<div id="nucleus-page-container" class="nucleus-page-container">';

  // Debug: show section count and timestamp in HTML comment (View Source only)
  $section_count = is_array($page_data) ? count($page_data) : 0;
  echo '<!-- Nucleus Debug: ' . $section_count . ' sections | ' . date('H:i:s') . ' -->';

  if (!empty($page_data) && is_array($page_data)) {
    echo '<div class="nucleus-sections-root">';

    foreach ($page_data as $section) {
      $original_sec_id = isset($section['section_id']) ? $section['section_id'] : 'section-' . rand(100, 999);
      $sec_id = sanitize_title($original_sec_id);

      // Background Logic
      $bg_style = '';
      if (isset($section['bg_type'])) {
        if ($section['bg_type'] === 'color' && !empty($section['bg_value'])) {
          $bg_style = 'background-color: ' . esc_attr($section['bg_value']) . ';';
        } elseif ($section['bg_type'] === 'image' && !empty($section['bg_value'])) {
          $bg_style = 'background-image: url(' . esc_url($section['bg_value']) . '); background-size: cover; background-position: center;';
        }
      }

      echo '<section id="nucleus-section-' . esc_attr($sec_id) . '" class="nucleus-section" style="' . $bg_style . '">';
      echo '<div class="nucleus-container">';

      if (!empty($section['components'])) {
        // --- GROUP components by prefix ---
        // Components named "card-heading", "card-list" share prefix "card"
        // Components named "cta-primary", "cta-secondary" share prefix "cta"  
        // Components with no dash (e.g. "badge", "title") are standalone
        $groups = array();
        $group_order = array();

        foreach ($section['components'] as $comp) {
          $comp_id = isset($comp['id']) ? sanitize_title($comp['id']) : 'comp';
          $parts = explode('-', $comp_id, 2);
          $prefix = (count($parts) > 1) ? $parts[0] : '__standalone_' . $comp_id;

          if (!isset($groups[$prefix])) {
            $groups[$prefix] = array();
            $group_order[] = $prefix;
          }
          $groups[$prefix][] = $comp;
        }

        foreach ($group_order as $prefix) {
          $comps_in_group = $groups[$prefix];
          $is_group = (count($comps_in_group) > 1 && strpos($prefix, '__standalone_') !== 0);

          // Open wrapper div for grouped components
          if ($is_group) {
            echo '<div id="' . esc_attr($sec_id . '-' . $prefix) . '" class="nucleus-group nucleus-group-' . esc_attr($prefix) . '">';
          }

          foreach ($comps_in_group as $comp) {
            $comp_id = isset($comp['id']) ? sanitize_title($comp['id']) : 'comp';
            $full_id = $sec_id . '-' . $comp_id;
            $val  = isset($comp['value']) ? $comp['value'] : '';
            $type = isset($comp['type']) ? $comp['type'] : 'text';

            if ($type === 'image') {
              if (!empty($val)) {
                echo '<img id="' . esc_attr($full_id) . '" class="nucleus-component" src="' . esc_url($val) . '" alt="' . esc_attr($full_id) . '" style="max-width: 100%; height: auto;" />';
              }
            } elseif ($type === 'url') {
              if (!empty($val)) {
                echo '<a id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-link" href="' . esc_url($val) . '">' . wp_kses_post($val) . '</a>';
              }
            } elseif ($type === 'link_text') {
              $link_url = isset($comp['meta']) ? $comp['meta'] : '#';
              if (!empty($val)) {
                echo '<a id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-btn-primary" href="' . esc_url($link_url) . '">' . wp_kses_post($val) . '</a>';
              }
            } elseif ($type === 'textarea') {
              echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-text">' . wpautop(esc_html($val)) . '</div>';
            } elseif ($type === 'number') {
              echo '<span id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-number">' . esc_html($val) . '</span>';
            } elseif ($type === 'tabs') {
              // Render tabs/sidebar component
              if (is_array($val) && !empty($val)) {
                echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-tabs" data-comp-id="' . esc_attr($full_id) . '">';
                echo '<div class="nucleus-tabs-sidebar">';

                foreach ($val as $tab_index => $tab) {
                  $tab_title = isset($tab['title']) ? $tab['title'] : 'Tab ' . ($tab_index + 1);
                  $is_active = ($tab_index === 0) ? 'active' : '';
                  echo '<button class="nucleus-tabs-button ' . $is_active . '" data-tab-idx="' . esc_attr($tab_index) . '">' . esc_html($tab_title) . '</button>';
                }

                echo '</div>'; // .nucleus-tabs-sidebar
                echo '<div class="nucleus-tabs-content">';

                foreach ($val as $tab_index => $tab) {
                  $tab_content = isset($tab['content']) ? $tab['content'] : '';
                  $is_active = ($tab_index === 0) ? 'active' : '';
                  echo '<div class="nucleus-tab-pane ' . $is_active . '" data-tab-idx="' . esc_attr($tab_index) . '">' . wp_kses_post($tab_content) . '</div>';
                }

                echo '</div>'; // .nucleus-tabs-content
                echo '</div>'; // .nucleus-tabs
              }
            } elseif ($type === 'carousel') {
              // Render carousel component
              if (is_array($val) && !empty($val)) {
                echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-carousel-wrapper" data-comp-id="' . esc_attr($full_id) . '">';
                echo '<div class="nucleus-carousel-inner">';

                foreach ($val as $idx => $slide) {
                  $s_title = isset($slide['title']) ? $slide['title'] : '';
                  $s_content = isset($slide['content']) ? $slide['content'] : '';
                  $s_image = isset($slide['image']) ? $slide['image'] : '';
                  $s_link = isset($slide['link']) ? $slide['link'] : '';

                  echo '<div class="nucleus-carousel-slide" data-slide-idx="' . esc_attr($idx) . '">';
                  echo '<div class="nucleus-carousel-slide-content">';

                  if (!empty($s_image)) {
                    echo '<div class="nucleus-carousel-slide-image"><img src="' . esc_url($s_image) . '" alt="' . esc_attr($s_title) . '" loading="lazy" /></div>';
                  }

                  echo '<div class="nucleus-carousel-slide-text">';
                  if (!empty($s_title)) echo '<h3 class="nucleus-carousel-slide-title">' . esc_html($s_title) . '</h3>';
                  if (!empty($s_content)) echo '<div class="nucleus-carousel-slide-desc">' . wpautop(wp_kses_post($s_content)) . '</div>';
                  if (!empty($s_link)) echo '<a href="' . esc_url($s_link) . '" class="nucleus-btn-primary" style="margin-top: 20px; align-self: flex-start;">Learn More</a>';
                  echo '</div>'; // .slide-text

                  echo '</div>'; // .slide-content
                  echo '</div>'; // .slide
                }
                echo '</div>'; // .nucleus-carousel-inner

                if (count($val) > 1) {
                  echo '<div class="nucleus-carousel-controls">';
                  echo '<button type="button" class="nucleus-carousel-btn nucleus-carousel-prev">&#10094;</button>';
                  echo '<button type="button" class="nucleus-carousel-btn nucleus-carousel-next">&#10095;</button>';
                  echo '</div>';

                  echo '<div class="nucleus-carousel-dots">';
                  foreach ($val as $idx => $slide) {
                    $active_class = ($idx === 0) ? 'active' : '';
                    echo '<button type="button" class="nucleus-carousel-dot ' . $active_class . '" aria-label="Slide ' . ($idx + 1) . '" data-slide-idx="' . esc_attr($idx) . '"></button>';
                  }
                  echo '</div>';
                }

                echo '</div>'; // .nucleus-carousel-wrapper
              }
            } elseif ($type === 'accordion') {
              if (is_array($val) && !empty($val)) {
                echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-accordion" data-comp-id="' . esc_attr($full_id) . '">';
                foreach ($val as $idx => $item) {
                  $item_title = isset($item['title']) ? $item['title'] : '';
                  $item_content = isset($item['content']) ? $item['content'] : '';
                  echo '<div class="nucleus-accordion-item">';
                  echo '<button class="nucleus-accordion-header">' . esc_html($item_title) . '<span class="nucleus-accordion-icon">+</span></button>';
                  echo '<div class="nucleus-accordion-content"><div class="nucleus-accordion-content-inner">' . wpautop(wp_kses_post($item_content)) . '</div></div>';
                  echo '</div>';
                }
                echo '</div>';
              }
            } elseif ($type === 'testimonial') {
              if (is_array($val)) {
                $quote = isset($val['quote']) ? $val['quote'] : '';
                $author = isset($val['author']) ? $val['author'] : '';
                $image = isset($val['image']) ? $val['image'] : '';
                echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-testimonial">';
                echo '<div class="nucleus-testimonial-quote">"' . esc_html($quote) . '"</div>';
                echo '<div class="nucleus-testimonial-author-area">';
                if (!empty($image)) {
                  echo '<img src="' . esc_url($image) . '" class="nucleus-testimonial-avatar" alt="' . esc_attr($author) . '" />';
                }
                if (!empty($author)) {
                  echo '<div class="nucleus-testimonial-author">' . esc_html($author) . '</div>';
                }
                echo '</div>';
                echo '</div>';
              }
            } elseif ($type === 'video') {
              $embed_url = $val;
              if (strpos($val, 'youtube.com/watch') !== false) {
                parse_str(parse_url($val, PHP_URL_QUERY), $vars);
                if (isset($vars['v'])) $embed_url = 'https://www.youtube.com/embed/' . $vars['v'];
              } elseif (strpos($val, 'youtu.be/') !== false) {
                $path = parse_url($val, PHP_URL_PATH);
                $embed_url = 'https://www.youtube.com/embed' . $path;
              } elseif (strpos($val, 'vimeo.com/') !== false) {
                $path = parse_url($val, PHP_URL_PATH);
                $embed_url = 'https://player.vimeo.com/video' . $path;
              }

              echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-video-wrapper">';
              echo '<iframe src="' . esc_url($embed_url) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width:100%; aspect-ratio:16/9;"></iframe>';
              echo '</div>';
            } elseif ($type === 'code') {
              echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-code-block">';
              echo '<pre><code>' . esc_html($val) . '</code></pre>';
              echo '</div>';
            } elseif ($type === 'shortcode') {
              echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-shortcode">';
              echo do_shortcode($val);
              echo '</div>';
            } elseif ($type === 'checklist') {
              if (is_array($val) && !empty($val)) {
                echo '<ul id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-checklist">';
                foreach ($val as $item_text) {
                  if (!empty(trim($item_text))) {
                    echo '<li class="nucleus-checklist-item">' . esc_html($item_text) . '</li>';
                  }
                }
                echo '</ul>';
              }
            } elseif ($type === 'stats') {
              $label = !empty($comp['meta']) ? $comp['meta'] : '';
              echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-stats">';
              echo '<div class="nucleus-stats-number">' . wp_kses_post($val) . '</div>';
              if (!empty($label)) {
                echo '<div class="nucleus-stats-label">' . wp_kses_post($label) . '</div>';
              }
              echo '</div>';
            } elseif ($type === 'heading') {
              $level = !empty($comp['meta']) ? $comp['meta'] : 'h2';
              echo '<' . esc_attr($level) . ' id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-heading">' . wp_kses_post($val) . '</' . esc_attr($level) . '>';
            } elseif ($type === 'wysiwyg') {
              echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-text nucleus-wysiwyg">' . wp_kses_post($val) . '</div>';
            } elseif ($type === 'html') {
              echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-html">' . $val . '</div>';
            } else {
              if (strpos($comp_id, 'title') !== false && strpos($comp_id, 'subtitle') === false) {
                echo '<h2 id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-title">' . wp_kses_post($val) . '</h2>';
              } elseif (strpos($comp_id, 'subtitle') !== false) {
                echo '<h4 id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-subtitle">' . wp_kses_post($val) . '</h4>';
              } else {
                echo '<div id="' . esc_attr($full_id) . '" class="nucleus-component nucleus-text">' . wp_kses_post($val) . '</div>';
              }
            }
          }

          // Close wrapper div
          if ($is_group) {
            echo '</div>'; // .nucleus-group
          }
        }
      }

      echo '</div>'; // .nucleus-container
      echo '</section>';
    }

    echo '</div>'; // .nucleus-sections-root
  } else {
    // Debug: only show for admins
    if (current_user_can('edit_posts')) {
      echo '<div style="max-width:800px;margin:80px auto;padding:40px;background:#fff;border:2px dashed #ccc;text-align:center;color:#666;">';
      echo '<h3>No Sections Found</h3>';
      echo '<p>Add sections and components in the "Page Content & Style Builder" meta box, then click Update.</p>';
      echo '<small>Post ID: ' . intval($post_id) . ' | Shortcode active</small>';
      echo '</div>';
    }
  }

  echo '</div>'; // .nucleus-page-container

  // Add JavaScript for tabs and carousel interactivity
  echo "<script type='text/javascript'>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle tab button clicks
        const tabButtons = document.querySelectorAll('.nucleus-tabs-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const tabIdx = this.getAttribute('data-tab-idx');
                const tabComponent = this.closest('.nucleus-tabs');
                const contentWrapper = tabComponent.querySelector('.nucleus-tabs-content');

                // Remove active class from all buttons and panes
                tabComponent.querySelectorAll('.nucleus-tabs-button').forEach(btn => btn.classList.remove('active'));
                tabComponent.querySelectorAll('.nucleus-tab-pane').forEach(pane => pane.classList.remove('active'));

                // Add active class to clicked button and matching pane
                this.classList.add('active');
                const activePane = tabComponent.querySelector('.nucleus-tab-pane[data-tab-idx=\"' + tabIdx + '\"]');
                activePane.classList.add('active');

                // ── Mobile only: move content wrapper directly after clicked button ──
                // Desktop: do nothing — active class handles visibility via CSS
                if (window.innerWidth <= 768) {
                    this.insertAdjacentElement('afterend', contentWrapper);
                    contentWrapper.classList.add('is-mobile-moved');
                }
            });
        });

        // Resize: restore content wrapper back inside .nucleus-tabs on desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                document.querySelectorAll('.nucleus-tabs').forEach(function(tabComponent) {
                    const contentWrapper = tabComponent.querySelector('.nucleus-tabs-content');
                    const sidebar = tabComponent.querySelector('.nucleus-tabs-sidebar');
                    if (contentWrapper && sidebar && contentWrapper.parentElement !== tabComponent) {
                        tabComponent.appendChild(contentWrapper);
                        contentWrapper.classList.remove('is-mobile-moved');
                    }
                });
            }
        });

        // Handle carousels
        const carousels = document.querySelectorAll('.nucleus-carousel-wrapper');
        carousels.forEach(carousel => {
            const inner = carousel.querySelector('.nucleus-carousel-inner');
            const slides = carousel.querySelectorAll('.nucleus-carousel-slide');
            const prevBtn = carousel.querySelector('.nucleus-carousel-prev');
            const nextBtn = carousel.querySelector('.nucleus-carousel-next');
            const dots = carousel.querySelectorAll('.nucleus-carousel-dot');
            
            if (!inner || slides.length <= 1) return;
            
            let currentIndex = 0;
            const slideCount = slides.length;
            let autoPlayInterval;
            
            function showSlide(index) {
                if (index < 0) index = slideCount - 1;
                if (index >= slideCount) index = 0;
                currentIndex = index;
                
                inner.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
                
                dots.forEach((dot, i) => {
                    if (i === currentIndex) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showSlide(currentIndex - 1);
                    resetAutoPlay();
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showSlide(currentIndex + 1);
                    resetAutoPlay();
                });
            }
            
            dots.forEach((dot, i) => {
                dot.addEventListener('click', (e) => {
                    e.preventDefault();
                    showSlide(i);
                    resetAutoPlay();
                });
            });
            
            function startAutoPlay() {
                autoPlayInterval = setInterval(() => {
                    showSlide(currentIndex + 1);
                }, 5000);
            }
            
            function resetAutoPlay() {
                clearInterval(autoPlayInterval);
                startAutoPlay();
            }
            
            startAutoPlay();
            
            // Pause on hover
            carousel.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
            carousel.addEventListener('mouseleave', startAutoPlay);
        });

        // Handle Accordions
        const accordions = document.querySelectorAll('.nucleus-accordion-header');
        accordions.forEach(header => {
            header.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
                const content = this.nextElementSibling;
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                } else {
                    content.style.maxHeight = content.scrollHeight + 'px';
                }
            });
        });
    });
    </script>";

  return ob_get_clean();
}
add_shortcode('nucleus_page_content', 'nucleus_page_content_shortcode');

/**
 * =====================================
 * Auto-Setup Oxygen for New Nucleus Pages
 * =====================================
 * Automatically assigns the "Header Footer" template (ID: 36)
 * and sets our shortcode as the Oxygen content when a nucleus_page
 * is created or published. No manual Oxygen setup needed.
 */
function nucleus_auto_setup_oxygen_page_template($post_id)
{
  if (get_post_type($post_id) !== 'nucleus_page') return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

  // Auto-assign Header Footer template (only if not already set)
  if (!get_post_meta($post_id, 'ct_other_template', true)) {
    update_post_meta($post_id, 'ct_other_template', '36');
  }

  // Auto-set Oxygen content with our shortcode (only if not edited in Oxygen yet)
  if (!get_post_meta($post_id, 'ct_builder_shortcodes', true)) {
    update_post_meta($post_id, 'ct_builder_shortcodes', '[nucleus_page_content]');
  }
}
add_action('save_post', 'nucleus_auto_setup_oxygen_page_template', 20);

/**
 * Render Header or Footer set
 */
function nucleus_render_hf_set($post_id, $type = 'header')
{
  $data_meta = get_post_meta($post_id, '_nucleus_' . $type . '_components', true);
  $data = is_string($data_meta) ? json_decode(base64_decode($data_meta), true) : $data_meta;

  $css_meta = get_post_meta($post_id, '_nucleus_hf_css', true);
  $css = is_string($css_meta) ? json_decode(base64_decode($css_meta), true) : $css_meta;
  ob_start();

  // Render CSS once if header
  if ($type === 'header' && !empty($css) && is_array($css)) {
    echo "<style type='text/css' id='nucleus-hf-custom-css'>\n";
    foreach ($css as $section_id => $css_block) {
      if (!empty($css_block)) echo "/* HF Section: " . esc_html($section_id) . " */\n" . strip_tags($css_block) . "\n";
    }
    echo "</style>\n";
  }

  if (!empty($data) && is_array($data)) {
    echo '<div class="nucleus-hf-root nucleus-' . esc_attr($type) . '-root">';
    foreach ($data as $section) {
      $sec_id = sanitize_title(isset($section['section_id']) ? $section['section_id'] : 'sec');
      $bg_style = '';
      if (!empty($section['bg_value'])) {
        $bg_style = 'background-color: ' . esc_attr($section['bg_value']) . ';';
      }
      echo '<section id="' . esc_attr($type) . '-' . esc_attr($sec_id) . '" class="nucleus-hf-section" style="' . $bg_style . '">';
      echo '<div class="nucleus-container" style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; flex-wrap: wrap; padding: 15px 20px;">';

      if (!empty($section['components'])) {
        foreach ($section['components'] as $comp) {
          $comp_id = sanitize_title(isset($comp['id']) ? $comp['id'] : 'comp');
          $full_id = $type . '-' . $sec_id . '-' . $comp_id;
          $val = isset($comp['value']) ? $comp['value'] : '';
          $c_type = isset($comp['type']) ? $comp['type'] : 'text';

          if ($c_type === 'image' && !empty($val)) {
            echo '<img id="' . esc_attr($full_id) . '" class="nucleus-hf-comp" src="' . esc_url($val) . '" alt="" style="max-width: 200px; height: auto;" />';
          } elseif ($c_type === 'url' && !empty($val)) {
            echo '<a id="' . esc_attr($full_id) . '" class="nucleus-hf-comp nucleus-link" href="' . esc_url($val) . '">' . wp_kses_post($val) . '</a>';
          } elseif ($c_type === 'html') {
            echo '<div id="' . esc_attr($full_id) . '" class="nucleus-hf-comp nucleus-html">' . $val . '</div>';
          } elseif ($c_type === 'shortcode') {
            echo '<div id="' . esc_attr($full_id) . '" class="nucleus-hf-comp nucleus-shortcode">' . do_shortcode($val) . '</div>';
          } else {
            echo '<div id="' . esc_attr($full_id) . '" class="nucleus-hf-comp nucleus-text">' . wp_kses_post($val) . '</div>';
          }
        }
      }

      echo '</div></section>';
    }
    echo '</div>';
  }

  return ob_get_clean();
}

/**
 * =====================================
 * Disable Oxygen Global Templates if a Custom H&F Set is selected
 * =====================================
 */
function nucleus_disable_oxygen_hf_for_custom_sets()
{
  if (is_singular('nucleus_page') || is_singular('nucleus_product')) {
    $post_id = get_the_ID();
    $hf_set_id = get_post_meta($post_id, '_nucleus_selected_hf_set', true);

    // If a Nucleus H&F Set is selected, tell Oxygen to NOT load its global template
    if (!empty($hf_set_id)) {
      // Oxygen filter to disable the catch-all template
      add_filter('ct_use_inner_content', '__return_false');
      add_filter('oxygen_default_template_id', '__return_zero');

      // Override Oxygen's explicit template assignment
      add_filter('get_post_metadata', function($value, $object_id, $meta_key, $single) use ($post_id) {
          if ($meta_key === 'ct_other_template' && $object_id == $post_id) {
              return '0';
          }
          return $value;
      }, 10, 4);

      // Force Oxygen rendering to bail out to standard WP templating
      remove_action('template_redirect', 'ct_template_redirect', 1);
      remove_filter('template_include', 'ct_template_include', 10);
      remove_filter('template_include', 'ct_template_include', 1);
      remove_filter('template_include', 'oxygen_vsb_template_include', 10);
    }
  }
}
add_action('wp', 'nucleus_disable_oxygen_hf_for_custom_sets');

add_filter('template_include', function ($template) {
  if (is_singular('nucleus_page')) {
    $hf_set_id = get_post_meta(get_the_ID(), '_nucleus_selected_hf_set', true);
    if (!empty($hf_set_id)) {
      $custom_template = plugin_dir_path(dirname(__FILE__)) . 'templates/single-nucleus_page.php';
      if (file_exists($custom_template)) {
        return $custom_template;
      }
    }
  }
  return $template;
}, 99999);
add_action('wp', 'nucleus_disable_oxygen_hf_for_custom_sets', 9);
