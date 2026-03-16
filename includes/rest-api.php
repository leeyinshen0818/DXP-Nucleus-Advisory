<?php
/**
 * Nucleus REST API — Leads Export Endpoint
 * 
 * Exposes form lead data from wp_nucleus_leads_testing via a secure REST API.
 * Used by Google Sheets (Apps Script) to auto-sync leads into Looker Studio.
 * 
 * Endpoint: GET /wp-json/nucleus/v1/leads?api_key=YOUR_KEY
 * 
 * No database credentials are exposed — WordPress $wpdb handles everything internally.
 */

if (!defined('ABSPATH'))
    exit;

// ─── Configuration ───────────────────────────────────────────────
// IMPORTANT: Change this key to a strong, random string before deploying.
// You can generate one at: https://api.wordpress.org/secret-key/1.1/salt/
define('NUCLEUS_API_KEY', 'vbskdvniaw8ry238');

// ─── Register REST Routes ────────────────────────────────────────
add_action('rest_api_init', 'nucleus_register_leads_routes');

function nucleus_register_leads_routes()
{
    // GET /wp-json/nucleus/v1/leads — Returns all leads as JSON
    register_rest_route('nucleus/v1', '/leads', array(
        'methods'             => 'GET',
        'callback'            => 'nucleus_rest_get_leads',
        'permission_callback' => 'nucleus_rest_verify_api_key',
    ));

    // GET /wp-json/nucleus/v1/leads/count — Returns lead count summary
    register_rest_route('nucleus/v1', '/leads/count', array(
        'methods'             => 'GET',
        'callback'            => 'nucleus_rest_get_leads_count',
        'permission_callback' => 'nucleus_rest_verify_api_key',
    ));
}

// ─── Permission Check ────────────────────────────────────────────
/**
 * Validates the API key passed as a query parameter.
 * This keeps the endpoint private without exposing DB credentials.
 */
function nucleus_rest_verify_api_key($request)
{
    $provided_key = $request->get_param('api_key');

    if (empty($provided_key) || $provided_key !== NUCLEUS_API_KEY) {
        return new WP_Error(
            'rest_forbidden',
            'Invalid or missing API key.',
            array('status' => 403)
        );
    }

    return true;
}

// ─── Endpoint Callbacks ──────────────────────────────────────────

/**
 * GET /wp-json/nucleus/v1/leads
 * 
 * Returns leads from wp_nucleus_leads_testing as a JSON array.
 * Supports optional query parameters:
 *   - limit (int)   : Max rows to return. Default 100, max 500.
 *   - offset (int)  : Starting row for pagination. Default 0.
 *   - since (string): Only return leads submitted after this date (YYYY-MM-DD).
 */
function nucleus_rest_get_leads($request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'nucleus_leads_testing';

    // Verify the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return new WP_Error(
            'table_not_found',
            'Leads table does not exist.',
            array('status' => 500)
        );
    }

    // Parse query parameters
    $limit  = min(absint($request->get_param('limit') ?: 100), 500);
    $offset = absint($request->get_param('offset') ?: 0);
    $since  = sanitize_text_field($request->get_param('since') ?: '');

    // Build query
    $where = '';
    $prepare_args = array();

    if (!empty($since)) {
        $where = 'WHERE submitted_at >= %s';
        $prepare_args[] = $since . ' 00:00:00';
    }

    $query = "SELECT id, name, email, company, phone, submitted_at 
              FROM $table_name $where 
              ORDER BY submitted_at DESC 
              LIMIT %d OFFSET %d";
    $prepare_args[] = $limit;
    $prepare_args[] = $offset;

    $leads = $wpdb->get_results($wpdb->prepare($query, $prepare_args), ARRAY_A);

    // Get total count for pagination info
    $count_query = "SELECT COUNT(*) FROM $table_name $where";
    if (!empty($since)) {
        $total = $wpdb->get_var($wpdb->prepare($count_query, $since . ' 00:00:00'));
    } else {
        $total = $wpdb->get_var($count_query);
    }

    return rest_ensure_response(array(
        'total'   => (int) $total,
        'limit'   => $limit,
        'offset'  => $offset,
        'count'   => count($leads),
        'leads'   => $leads,
    ));
}

/**
 * GET /wp-json/nucleus/v1/leads/count
 * 
 * Returns a summary of lead counts — useful for dashboard scorecards.
 */
function nucleus_rest_get_leads_count($request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'nucleus_leads_testing';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        return new WP_Error(
            'table_not_found',
            'Leads table does not exist.',
            array('status' => 500)
        );
    }

    $total     = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $today     = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE DATE(submitted_at) = %s",
        current_time('Y-m-d')
    ));
    $this_week = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE submitted_at >= %s",
        date('Y-m-d 00:00:00', strtotime('monday this week'))
    ));
    $this_month = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE submitted_at >= %s",
        date('Y-m-01 00:00:00')
    ));

    return rest_ensure_response(array(
        'total'      => $total,
        'today'      => $today,
        'this_week'  => $this_week,
        'this_month' => $this_month,
    ));
}
