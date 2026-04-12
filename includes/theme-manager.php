<?php

/**
 * Theme Manager
 * ==================================
 * Provides a WordPress admin UI for managing site-wide CSS custom properties.
 * Saves theme values to wp_options and auto-injects them as :root variables
 * on every front-end page load — no manual copy-paste required.
 */

if (!defined('ABSPATH')) {
  exit;
}

// ─── Default theme values ────────────────────────────────────────────────────
// These are used on first install or if the admin has never saved a theme.
// Edit these to change the out-of-box defaults.

function ncl_theme_defaults()
{
  return array(

    // Brand
    'primary'           => '#3a5ba0',
    'primary_hover'     => '#2d4a8a',
    'primary_muted'     => '#bccdf0',

    'secondary'         => '#f7c873',
    'secondary_hover'   => '#e6b85e',
    'secondary_muted'   => '#faecc8',

    'tertiary'          => '#f43f5e',
    'tertiary_hover'    => '#d9294a',
    'tertiary_muted'    => '#fce4e8',

    // Surfaces
    'bg'                => '#f5f7fa',
    'bg_hover'          => '#e8ecf2',
    'bg_muted'          => '#f0f2f7',

    'surface'           => '#e3eaf2',
    'surface_hover'     => '#d4dde9',
    'surface_muted'     => '#edf1f7',

    'neutral'           => '#b0b8c1',
    'neutral_hover'     => '#8f9aa5',
    'neutral_muted'     => '#d8dde3',

    // Typography
    'text_heading'      => '#1a2238',
    'text_heading_hover' => '#0e1520',
    'text_heading_muted' => '#3a4a66',

    'text_body'         => '#1a2238',
    'text_body_hover'   => '#0e1520',
    'text_body_muted'   => '#4a5568',

    'text_muted'        => '#3a5ba0',
    'text_muted_hover'  => '#2d4a8a',
    'text_muted_muted'  => '#6ea3c1',

    // Accents — functional colors used across components
    'accent_green'      => '#37D097',
    'accent_green_hover' => '#2ab882',
    'accent_green_muted' => '#d0f5e8',
  );
}

// ─── Get saved theme (merged with defaults) ───────────────────────────────────

function ncl_get_theme()
{
  $saved = get_option('ncl_theme_vars', array());
  return wp_parse_args($saved, ncl_theme_defaults());
}

// ─── Hex to RGB helper ───────────────────────────────────────────────────────
// Returns "R, G, B" string for use inside rgba() in CSS.

function ncl_hex_to_rgb($hex)
{
  $hex = ltrim($hex, '#');
  if (strlen($hex) === 3) {
    $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
  }
  $r = hexdec(substr($hex, 0, 2));
  $g = hexdec(substr($hex, 2, 2));
  $b = hexdec(substr($hex, 4, 2));
  return "{$r}, {$g}, {$b}";
}

// ─── Compile theme to CSS string ─────────────────────────────────────────────

function ncl_compile_theme_css($theme)
{
  return "
:root {
  /* Brand — Primary */
  --ncl-primary:              {$theme['primary']};
  --ncl-primary-hover:        {$theme['primary_hover']};
  --ncl-primary-muted:        {$theme['primary_muted']};

  /* Brand — Secondary */
  --ncl-secondary:            {$theme['secondary']};
  --ncl-secondary-hover:      {$theme['secondary_hover']};
  --ncl-secondary-muted:      {$theme['secondary_muted']};

  /* Brand — Tertiary */
  --ncl-tertiary:             {$theme['tertiary']};
  --ncl-tertiary-hover:       {$theme['tertiary_hover']};
  --ncl-tertiary-muted:       {$theme['tertiary_muted']};

  /* Surfaces — Background */
  --ncl-bg:                   {$theme['bg']};
  --ncl-bg-hover:             {$theme['bg_hover']};
  --ncl-bg-muted:             {$theme['bg_muted']};

  /* Surfaces — Surface */
  --ncl-surface:              {$theme['surface']};
  --ncl-surface-hover:        {$theme['surface_hover']};
  --ncl-surface-muted:        {$theme['surface_muted']};

  /* Surfaces — Neutral */
  --ncl-neutral:              {$theme['neutral']};
  --ncl-neutral-hover:        {$theme['neutral_hover']};
  --ncl-neutral-muted:        {$theme['neutral_muted']};

  /* Typography — Heading */
  --ncl-text-heading:         {$theme['text_heading']};
  --ncl-text-heading-hover:   {$theme['text_heading_hover']};
  --ncl-text-heading-muted:   {$theme['text_heading_muted']};

  /* Typography — Body */
  --ncl-text-body:            {$theme['text_body']};
  --ncl-text-body-hover:      {$theme['text_body_hover']};
  --ncl-text-body-muted:      {$theme['text_body_muted']};

  /* Typography — Muted */
  --ncl-text-muted:           {$theme['text_muted']};
  --ncl-text-muted-hover:     {$theme['text_muted_hover']};
  --ncl-text-muted-muted:     {$theme['text_muted_muted']};

  /* Accents — Green */
  --ncl-accent-green:         {$theme['accent_green']};
  --ncl-accent-green-hover:   {$theme['accent_green_hover']};
  --ncl-accent-green-muted:   {$theme['accent_green_muted']};

  /* RGB variants — required for rgba() opacity calls in components */
  --ncl-primary-rgb:          " . ncl_hex_to_rgb($theme['primary']) . ";
  --ncl-secondary-rgb:        " . ncl_hex_to_rgb($theme['secondary']) . ";
  --ncl-tertiary-rgb:         " . ncl_hex_to_rgb($theme['tertiary']) . ";
  --ncl-bg-rgb:               " . ncl_hex_to_rgb($theme['bg']) . ";
  --ncl-surface-rgb:          " . ncl_hex_to_rgb($theme['surface']) . ";
  --ncl-accent-green-rgb:     " . ncl_hex_to_rgb($theme['accent_green']) . ";

  /* Shadcn/Tailwind bridge — maps existing component vars to ncl theme */
  --primary:                  var(--ncl-primary);
  --secondary:                var(--ncl-secondary);
  --background:               var(--ncl-bg);
  --foreground:               var(--ncl-text-heading);
  --card:                     var(--ncl-surface);
  --card-foreground:          var(--ncl-text-heading);
  --border:                   var(--ncl-neutral);
  --muted:                    var(--ncl-bg-muted);
  --muted-foreground:         var(--ncl-text-muted);
  --accent:                   var(--ncl-secondary);
  --accent-foreground:        var(--ncl-text-heading);
  --primary-foreground:       var(--ncl-bg);
  --secondary-foreground:     var(--ncl-text-heading);
  --ring:                     var(--ncl-secondary);
  --radius:                   0.5rem;
}
";
}

// ─── Front-end injection ──────────────────────────────────────────────────────

function ncl_inject_theme_variables()
{
  $theme      = ncl_get_theme();
  $css        = ncl_compile_theme_css($theme);
  $global_css = get_option('ncl_global_css', '');

  // Append additional global CSS after the :root variables
  if (!empty(trim($global_css))) {
    $css .= "\n/* ── Additional Global CSS ── */\n" . wp_strip_all_tags($global_css);
  }

  wp_register_style('ncl-theme-vars', false, array(), '1.0');
  wp_enqueue_style('ncl-theme-vars');
  wp_add_inline_style('ncl-theme-vars', $css);
}
add_action('wp_enqueue_scripts', 'ncl_inject_theme_variables');

// ─── Admin menu ───────────────────────────────────────────────────────────────

function ncl_theme_manager_menu()
{
  add_submenu_page(
    'edit.php?post_type=nucleus_page',  // Parent: Page Manager menu
    'Theme Manager',
    'Theme Manager',
    'manage_options',
    'ncl-theme-manager',
    'ncl_theme_manager_page'
  );
}
add_action('admin_menu', 'ncl_theme_manager_menu');

// ─── Save handler ─────────────────────────────────────────────────────────────

function ncl_theme_manager_save()
{
  if (
    !isset($_POST['ncl_theme_nonce']) ||
    !wp_verify_nonce($_POST['ncl_theme_nonce'], 'ncl_save_theme') ||
    !current_user_can('manage_options')
  ) {
    return;
  }

  $fields = array_keys(ncl_theme_defaults());
  $theme  = array();

  foreach ($fields as $field) {
    if (isset($_POST['ncl'][$field])) {
      $val = sanitize_hex_color($_POST['ncl'][$field]);
      if ($val) {
        $theme[$field] = $val;
      }
    }
  }

  update_option('ncl_theme_vars', $theme);

  // Save additional global CSS
  if (isset($_POST['ncl_global_css'])) {
    update_option('ncl_global_css', wp_unslash($_POST['ncl_global_css']));
  }

  wp_redirect(admin_url('edit.php?post_type=nucleus_page&page=ncl-theme-manager&saved=1'));
  exit;
}
add_action('admin_post_ncl_save_theme', 'ncl_theme_manager_save');

// ─── Reset handler ────────────────────────────────────────────────────────────

function ncl_theme_manager_reset()
{
  if (
    !isset($_POST['ncl_theme_nonce']) ||
    !wp_verify_nonce($_POST['ncl_theme_nonce'], 'ncl_save_theme') ||
    !current_user_can('manage_options')
  ) {
    return;
  }

  delete_option('ncl_theme_vars');
  delete_option('ncl_global_css');
  wp_redirect(admin_url('edit.php?post_type=nucleus_page&page=ncl-theme-manager&reset=1'));
  exit;
}
add_action('admin_post_ncl_reset_theme', 'ncl_theme_manager_reset');

// ─── Admin page render ────────────────────────────────────────────────────────

function ncl_theme_manager_page()
{
  $theme   = ncl_get_theme();
  $saved   = isset($_GET['saved']) ? true : false;
  $reset   = isset($_GET['reset']) ? true : false;
  $css_out = esc_textarea(trim(ncl_compile_theme_css($theme)));

  $groups = array(
    'Brand Colors' => array(
      array('key' => 'primary',   'label' => 'Primary'),
      array('key' => 'secondary', 'label' => 'Secondary'),
      array('key' => 'tertiary',  'label' => 'Tertiary'),
    ),
    'Surfaces' => array(
      array('key' => 'bg',      'label' => 'Background'),
      array('key' => 'surface', 'label' => 'Surface'),
      array('key' => 'neutral', 'label' => 'Neutral'),
    ),
    'Typography' => array(
      array('key' => 'text_heading', 'label' => 'Heading'),
      array('key' => 'text_body',    'label' => 'Body'),
      array('key' => 'text_muted',   'label' => 'Muted'),
    ),
    'Accents' => array(
      array('key' => 'accent_green', 'label' => 'Green'),
    ),
  );
?>
  <div class="wrap" id="ncl-tm-wrap">
    <h1 style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
      <span style="font-size:20px">🎨</span> Theme Manager
    </h1>
    <p style="color:#666;margin-bottom:24px">
      Set your site-wide color variables. Changes apply instantly to all Nucleus components on save.
    </p>

    <?php if ($saved): ?>
      <div class="notice notice-success is-dismissible">
        <p>Theme saved and applied site-wide.</p>
      </div>
    <?php endif; ?>
    <?php if ($reset): ?>
      <div class="notice notice-info is-dismissible">
        <p>Theme reset to defaults.</p>
      </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start">

      <!-- ── Left: Color pickers ── -->
      <div>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
          <?php wp_nonce_field('ncl_save_theme', 'ncl_theme_nonce'); ?>
          <input type="hidden" name="action" value="ncl_save_theme">

          <?php foreach ($groups as $group_label => $roles): ?>
            <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;margin-bottom:16px">
              <h3 style="margin:0 0 16px;font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#666;font-weight:600">
                <?php echo esc_html($group_label); ?>
              </h3>

              <!-- Column headers -->
              <div style="display:grid;grid-template-columns:100px 1fr 1fr 1fr;gap:8px;margin-bottom:6px">
                <div></div>
                <?php foreach (array('Base', 'Hover', 'Muted') as $v): ?>
                  <div style="font-size:11px;color:#999;text-align:center"><?php echo $v; ?></div>
                <?php endforeach; ?>
              </div>

              <?php foreach ($roles as $role):
                $k = $role['key'];
                $variants = array(
                  $k,
                  $k . '_hover',
                  $k . '_muted',
                );
              ?>
                <div style="display:grid;grid-template-columns:100px 1fr 1fr 1fr;gap:8px;align-items:center;margin-bottom:10px">
                  <span style="font-size:13px;color:#333"><?php echo esc_html($role['label']); ?></span>
                  <?php foreach ($variants as $vk): ?>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
                      <input
                        type="color"
                        name="ncl[<?php echo esc_attr($vk); ?>]"
                        value="<?php echo esc_attr($theme[$vk]); ?>"
                        style="width:100%;height:36px;border:1px solid #ddd;border-radius:6px;cursor:pointer;padding:2px">
                      <span class="ncl-hex-label" style="font-size:10px;color:#999;font-family:monospace">
                        <?php echo esc_html($theme[$vk]); ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>

          <!-- ── Additional Global CSS ── -->
          <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px;margin-bottom:16px">
            <h3 style="margin:0 0 6px;font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#666;font-weight:600">Additional Global CSS</h3>
            <p style="margin:0 0 12px;font-size:12px;color:#999">Paste site-wide CSS here — utility classes, resets, font rules. These are injected after the theme variables so you can use <code>var(--ncl-*)</code> directly.</p>
            <textarea
              name="ncl_global_css"
              rows="12"
              style="width:100%;font-family:monospace;font-size:12px;border:1px solid #ddd;border-radius:6px;padding:10px;resize:vertical;color:#333;background:#fafafa;line-height:1.6"
              placeholder="/* e.g. */&#10;.text-gradient {&#10;  background: linear-gradient(135deg, var(--ncl-primary-hover), var(--ncl-tertiary));&#10;  -webkit-background-clip: text;&#10;  background-clip: text;&#10;  color: transparent;&#10;  -webkit-text-fill-color: transparent;&#10;}"><?php echo esc_textarea(get_option('ncl_global_css', '')); ?></textarea>
          </div>

          <div style="display:flex;gap:10px;margin-top:8px">
            <?php submit_button('Save Theme', 'primary', 'submit', false); ?>

            <button
              type="submit"
              formaction="<?php echo admin_url('admin-post.php'); ?>"
              onclick="this.form.querySelector('[name=action]').value='ncl_reset_theme'"
              style="background:#fff;border:1px solid #ccc;border-radius:4px;padding:0 12px;cursor:pointer;color:#666"
              onclick="return confirm('Reset all colors to defaults?')">Reset to defaults</button>
          </div>
        </form>
      </div>

      <!-- ── Right: Preview + CSS output ── -->
      <div style="position:sticky;top:32px">

        <!-- Live preview chips -->
        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:16px;margin-bottom:16px">
          <h3 style="margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#666;font-weight:600">Preview</h3>
          <div id="ncl-preview-chips" style="display:flex;flex-wrap:wrap;gap:8px">
            <div class="ncl-chip" data-bg="primary" data-fg="bg"
              style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;background:<?php echo esc_attr($theme['primary']); ?>;color:<?php echo esc_attr($theme['bg']); ?>">
              Primary
            </div>
            <div class="ncl-chip" data-bg="secondary" data-fg="text_heading"
              style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;background:<?php echo esc_attr($theme['secondary']); ?>;color:<?php echo esc_attr($theme['text_heading']); ?>">
              Secondary
            </div>
            <div class="ncl-chip" data-bg="tertiary" data-fg="bg"
              style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;background:<?php echo esc_attr($theme['tertiary']); ?>;color:<?php echo esc_attr($theme['bg']); ?>">
              Tertiary
            </div>
            <div class="ncl-chip" data-bg="surface" data-fg="text_body"
              style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;background:<?php echo esc_attr($theme['surface']); ?>;color:<?php echo esc_attr($theme['text_body']); ?>;border:1px solid <?php echo esc_attr($theme['neutral']); ?>">
              Surface card
            </div>
            <div class="ncl-chip" data-bg="bg" data-fg="text_muted"
              style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;background:<?php echo esc_attr($theme['bg']); ?>;color:<?php echo esc_attr($theme['text_muted']); ?>;border:1px solid <?php echo esc_attr($theme['neutral']); ?>">
              Background
            </div>
            <div class="ncl-chip" data-bg="primary_muted" data-fg="text_heading"
              style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;background:<?php echo esc_attr($theme['primary_muted']); ?>;color:<?php echo esc_attr($theme['text_heading']); ?>">
              Muted tag
            </div>
            <div class="ncl-chip" data-bg="accent_green" data-fg="bg"
              style="padding:6px 14px;border-radius:20px;font-size:12px;font-weight:500;background:<?php echo esc_attr($theme['accent_green']); ?>;color:<?php echo esc_attr($theme['bg']); ?>">
              Accent green
            </div>
          </div>

          <!-- Typography preview -->
          <div style="margin-top:14px;padding-top:14px;border-top:1px solid #eee;background:<?php echo esc_attr($theme['bg']); ?>;border-radius:6px;padding:12px">
            <p style="margin:0 0 4px;font-size:16px;font-weight:600;color:<?php echo esc_attr($theme['text_heading']); ?>">Heading text</p>
            <p style="margin:0 0 4px;font-size:13px;color:<?php echo esc_attr($theme['text_body']); ?>">Body text — the quick brown fox jumps over the lazy dog.</p>
            <p style="margin:0;font-size:12px;color:<?php echo esc_attr($theme['text_muted']); ?>">Muted / supporting text</p>
          </div>
        </div>

        <!-- CSS output -->
        <div style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:16px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <h3 style="margin:0;font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#666;font-weight:600">Generated CSS</h3>
            <button id="ncl-copy-css" style="font-size:11px;padding:3px 10px;border:1px solid #ccc;border-radius:4px;background:#f9f9f9;cursor:pointer">Copy</button>
          </div>
          <textarea id="ncl-css-output" readonly
            style="width:100%;height:200px;font-family:monospace;font-size:10px;border:1px solid #eee;border-radius:4px;padding:8px;resize:vertical;color:#444;background:#fafafa"><?php echo $css_out; ?></textarea>
        </div>

      </div>
    </div>
  </div>

  <script>
    (function() {

      // Live hex label update + preview chip update on color input change
      var inputs = document.querySelectorAll('#ncl-tm-wrap input[type=color]');
      var themeMap = {};

      // Build initial theme map from PHP values
      <?php foreach ($theme as $k => $v): ?>
        themeMap[<?php echo json_encode($k); ?>] = <?php echo json_encode($v); ?>;
      <?php endforeach; ?>

      inputs.forEach(function(inp) {
        var nameAttr = inp.getAttribute('name'); // ncl[key]
        var key = nameAttr.replace('ncl[', '').replace(']', '');
        var label = inp.nextElementSibling;

        inp.addEventListener('input', function() {
          themeMap[key] = this.value;
          if (label) label.textContent = this.value;
          updateChips();
        });
      });

      function updateChips() {
        document.querySelectorAll('.ncl-chip').forEach(function(chip) {
          var bg = chip.getAttribute('data-bg');
          var fg = chip.getAttribute('data-fg');
          if (themeMap[bg]) chip.style.background = themeMap[bg];
          if (themeMap[fg]) chip.style.color = themeMap[fg];
        });
      }

      // Copy CSS button
      document.getElementById('ncl-copy-css').addEventListener('click', function() {
        var ta = document.getElementById('ncl-css-output');
        ta.select();
        document.execCommand('copy');
        this.textContent = 'Copied!';
        var btn = this;
        setTimeout(function() {
          btn.textContent = 'Copy';
        }, 1500);
      });

      // Reset button confirm
      var resetBtn = document.querySelector('[onclick*="ncl_reset_theme"]');
      if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
          if (!confirm('Reset all colors to defaults?')) e.preventDefault();
        });
      }

    })();
  </script>
<?php
}
