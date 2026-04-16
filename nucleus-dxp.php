<?php
/**
 * Plugin Name: DXP Testing Version
 * Description: ISOLATED TESTING ENVIRONMENT. Adds Lead Capture System and Analytics features.
 * Version: 2.0
 * Author: DXP Team
 */

if (!defined('ABSPATH')) {
    exit;
}

// Plugin base path
define('NUCLEUS_DXP_PATH', plugin_dir_path(__FILE__));
define('NUCLEUS_DXP_URL', plugin_dir_url(__FILE__));

// Load modules
require_once NUCLEUS_DXP_PATH . 'includes/form-handler.php';
require_once NUCLEUS_DXP_PATH . 'includes/analytics.php';
require_once NUCLEUS_DXP_PATH . 'includes/admin-dashboard.php';
require_once NUCLEUS_DXP_PATH . 'includes/product-manager.php';
require_once NUCLEUS_DXP_PATH . 'includes/page-manager.php';
require_once NUCLEUS_DXP_PATH . 'includes/theme-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/program-manager.php';
require_once NUCLEUS_DXP_PATH . 'includes/rest-api.php';




// Database table creation on activation
function nucleus_core_activate_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'nucleus_leads_testing';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email varchar(100) NOT NULL,
        company varchar(100) DEFAULT '' NOT NULL,
        phone varchar(20) DEFAULT '' NOT NULL,
        reason varchar(255) DEFAULT '' NOT NULL,
        form_data longtext DEFAULT '' NOT NULL,
        submitted_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'nucleus_core_activate_table');

// Force schema update check on admin load (Temporary auto-fix for upgrade)
function nucleus_core_force_db_update()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'nucleus_leads_testing';

    // Check if table exists first
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {

        // Auto-add 'form_data' column if missing
        $row = $wpdb->get_results("SELECT * FROM $table_name LIMIT 1");
        if (empty($row) || !isset($row[0]->form_data)) {
            nucleus_core_activate_table();
        }

        // Auto-add 'reason' column if missing (non-destructive ALTER)
        $columns = $wpdb->get_col("SHOW COLUMNS FROM $table_name LIKE 'reason'");
        if (empty($columns)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN reason VARCHAR(255) DEFAULT '' NOT NULL AFTER phone");
        }
    }
}
add_action('admin_init', 'nucleus_core_force_db_update');

// Testing Page Shortcode — loads template from /templates/
function nucleus_testing_page_shortcode()
{
    ob_start();
    include NUCLEUS_DXP_PATH . 'templates/testing-page.php';
    return ob_get_clean();
}
add_shortcode('nucleus_testing_page', 'nucleus_testing_page_shortcode');

// Enqueue page CSS and tracking JS on testing page AND single product pages
function nucleus_dxp_enqueue_assets()
{
    $is_testing_lab = is_page('testing-lab');
    $is_product_page = is_singular('nucleus_product');       // single product CPT pages
    $is_products_page = nucleus_is_products_landing(); // detects [nucleus_products_landing] shortcode

    if ($is_testing_lab) {
        wp_enqueue_style('nucleus-testing-page', NUCLEUS_DXP_URL . 'assets/css/testing-page.css', array(), '4.5');
    }

    // Enqueue styles for Nucleus Page Template
    if (is_page_template('templates/single-nucleus_page.php') || is_page_template('single-nucleus_page.php')) {
        wp_enqueue_style('nucleus-page-style', NUCLEUS_DXP_URL . 'assets/css/nucleus-page.css', array(), '1.1');
    }

    if ($is_testing_lab || $is_product_page || $is_products_page) {
        wp_enqueue_script('nucleus-tracking', NUCLEUS_DXP_URL . 'assets/js/tracking.js', array(), '2.3', true);
    }
}
add_action('wp_enqueue_scripts', 'nucleus_dxp_enqueue_assets');
