<?php
/**
 * Admin Dashboard: View Leads
 * Adds a 'Testing Dashboard' menu in WP Admin
 */

if (!defined('ABSPATH'))
    exit;

function nucleus_core_add_admin_menu()
{
    add_menu_page(
        'Nucleus Testing',
        'Testing Dashboard',
        'manage_options',
        'nucleus-leads',
        'nucleus_core_render_leads_page',
        'dashicons-beaker',
        6
    );
}
add_action('admin_menu', 'nucleus_core_add_admin_menu');

// Hook CSV export action to admin_init to ensure headers are sent before any output
function nucleus_core_handle_csv_export()
{
    if (isset($_POST['nucleus_export_csv']) && check_admin_referer('nucleus_export_csv_action')) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nucleus_leads_testing';
        $filename = 'nucleus-leads-' . date('Y-m-d') . '.csv';

        if (ob_get_level())
            ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // 1. Fetch ALL data
        $all_leads = $wpdb->get_results("SELECT * FROM $table_name ORDER BY submitted_at DESC");

        // 2. Scan for ALL unique dynamic columns
        $dynamic_headers = [];
        foreach ($all_leads as $lead) {
            $data = json_decode($lead->form_data, true);
            if (is_array($data)) {
                foreach (array_keys($data) as $key) {
                    // Skip internal fields (starting with _)
                    if (strpos($key, '_') !== 0 && !in_array($key, $dynamic_headers)) {
                        $dynamic_headers[] = $key;
                    }
                }
            }
        }

        // 3. Build Header Row (Static + Dynamic)
        $headers = array_merge(['ID', 'Date'], $dynamic_headers);
        fputcsv($output, $headers);

        // 4. Output Rows
        foreach ($all_leads as $row) {
            $data = json_decode($row->form_data, true);
            $csv_row = [
                $row->id,
                $row->submitted_at
            ];

            // For each dynamic header, check if this row has a value
            foreach ($dynamic_headers as $header) {
                $value = isset($data[$header]) ? $data[$header] : '';
                // Handle arrays (e.g. checkbox)
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $csv_row[] = $value;
            }

            fputcsv($output, $csv_row);
        }

        fclose($output);
        exit;
    }
}
add_action('admin_init', 'nucleus_core_handle_csv_export');

// Hook Delete action to admin_init
function nucleus_core_handle_delete_action()
{
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['page']) && $_GET['page'] === 'nucleus-leads') {
        if (check_admin_referer('nucleus_delete_lead_' . $_GET['id'])) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nucleus_leads_testing';
            $wpdb->delete($table_name, array('id' => intval($_GET['id'])));

            wp_redirect(admin_url('admin.php?page=nucleus-leads&deleted=1'));
            exit;
        }
    }
}
add_action('admin_init', 'nucleus_core_handle_delete_action');

function nucleus_core_render_leads_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'nucleus_leads_testing';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        echo '<div class="wrap"><h1>No Leads Yet</h1><p>Test table does not exist yet. Submit a form to create it.</p></div>';
        return;
    }

    // --- CHECK IF WE ARE IN "HISTORY" MODE (viewing a specific person's submissions) ---
    $view_email = isset($_GET['view_email']) ? sanitize_email($_GET['view_email']) : '';

    if ($view_email) {
        // ======================================================================
        // HISTORY VIEW: Show all submissions from this specific email
        // ======================================================================
        $submissions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE email = %s ORDER BY submitted_at DESC",
            $view_email
        ));

        // Get the person's name from the latest submission
        $contact_name = '';
        if (!empty($submissions)) {
            $first_data = json_decode($submissions[0]->form_data, true);
            if (is_array($first_data) && isset($first_data['your-name'])) {
                $contact_name = $first_data['your-name'];
            } elseif (!empty($submissions[0]->name)) {
                $contact_name = $submissions[0]->name;
            }
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <a href="<?php echo admin_url('admin.php?page=nucleus-leads'); ?>" style="text-decoration: none;">&larr; Back to
                    All Contacts</a>
            </h1>

            <div style="background: #fff; border: 1px solid #ccd0d4; padding: 20px; margin: 15px 0; border-radius: 4px;">
                <h2 style="margin:0 0 5px 0;"><?php echo esc_html($contact_name ?: 'Unknown'); ?></h2>
                <p style="margin:0; color: #666; font-size: 14px;">
                    <span class="dashicons dashicons-email" style="font-size:16px; vertical-align:middle;"></span>
                    <a href="mailto:<?php echo esc_attr($view_email); ?>"><?php echo esc_html($view_email); ?></a>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <strong><?php echo count($submissions); ?></strong> submission(s)
                </p>
            </div>

            <h3 style="margin-top:20px;">Submission History</h3>

            <?php
            // Detect all dynamic columns from this person's submissions
            $history_columns = [];
            foreach ($submissions as $sub) {
                $d = json_decode($sub->form_data, true);
                if (is_array($d)) {
                    foreach (array_keys($d) as $k) {
                        if (strpos($k, '_') !== 0 && !in_array($k, $history_columns)) {
                            $history_columns[] = $k;
                        }
                    }
                }
            }
            ?>

            <div style="overflow-x: auto;">
                <table class="wp-list-table widefat fixed striped" style="min-width: 100%;">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th width="160">Date Submitted</th>
                            <?php foreach ($history_columns as $col): ?>
                                <?php
                                $display_col = str_ireplace(['your-', 'your_'], '', $col);
                                $display_col = str_replace(['-', '_'], ' ', $display_col);
                                ?>
                                <th style="white-space: nowrap; text-transform: capitalize;">
                                    <?php echo esc_html($display_col); ?>
                                </th>
                            <?php endforeach; ?>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $index => $sub): ?>
                            <?php $sub_data = json_decode($sub->form_data, true); ?>
                            <tr>
                                <td>#<?php echo esc_html($sub->id); ?></td>
                                <td>
                                    <?php echo date('M j, Y g:i a', strtotime($sub->submitted_at)); ?>
                                    <?php if ($index === 0): ?>
                                        <br><span
                                            style="background: #2271b1; color: #fff; font-size: 10px; padding: 1px 6px; border-radius: 3px;">Latest</span>
                                    <?php endif; ?>
                                </td>
                                <?php foreach ($history_columns as $col): ?>
                                    <td>
                                        <?php
                                        if (is_array($sub_data) && isset($sub_data[$col])) {
                                            $val = $sub_data[$col];
                                            if (is_array($val)) {
                                                echo esc_html(implode(', ', $val));
                                            } else {
                                                echo esc_html(mb_strimwidth($val, 0, 50, "..."));
                                            }
                                        } else {
                                            echo '<span style="color:#ddd;">—</span>';
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <?php
                                    $delete_url = wp_nonce_url(
                                        admin_url('admin.php?page=nucleus-leads&action=delete&id=' . $sub->id),
                                        'nucleus_delete_lead_' . $sub->id
                                    );
                                    ?>
                                    <a href="<?php echo $delete_url; ?>" onclick="return confirm('Delete this submission?');"
                                        style="color: #a00; text-decoration: none;" title="Delete">
                                        <span class="dashicons dashicons-trash"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return; // Stop here, don't render the main table
    }

    // ======================================================================
    // MAIN CONTACTS VIEW: Grouped by email, one row per person
    // ======================================================================
    $search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $filter_field = isset($_GET['filter_field']) ? sanitize_text_field($_GET['filter_field']) : '';
    $date_start = isset($_GET['date_start']) ? sanitize_text_field($_GET['date_start']) : '';
    $date_end = isset($_GET['date_end']) ? sanitize_text_field($_GET['date_end']) : '';

    // Pagination Params
    $items_per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    // Build WHERE clauses
    $where_clauses = array("1=1");

    if ($search_query) {
        if ($filter_field && $filter_field !== 'all') {
            $where_clauses[] = $wpdb->prepare("form_data LIKE %s", '%"' . $wpdb->esc_like($filter_field) . '":"%' . $wpdb->esc_like($search_query) . '%"');
        } else {
            $like = '%' . $wpdb->esc_like($search_query) . '%';
            $where_clauses[] = $wpdb->prepare("(name LIKE %s OR email LIKE %s OR company LIKE %s OR form_data LIKE %s)", $like, $like, $like, $like);
        }
    }

    if ($date_start) {
        $where_clauses[] = $wpdb->prepare("submitted_at >= %s", $date_start . ' 00:00:00');
    }
    if ($date_end) {
        $where_clauses[] = $wpdb->prepare("submitted_at <= %s", $date_end . ' 23:59:59');
    }

    $where_sql = implode(' AND ', $where_clauses);

    // COUNT unique contacts (grouped by email)
    $total_items = $wpdb->get_var("SELECT COUNT(DISTINCT email) FROM $table_name WHERE $where_sql");
    $total_pages = max(1, ceil($total_items / $items_per_page));

    // FETCH grouped contacts: latest submission per email
    // The WHERE filtering happens INSIDE the subquery, so we don't need it in the outer query
    // (having it in both caused "Column 'email' is ambiguous" error)
    $contacts = $wpdb->get_results(
        "SELECT t1.* FROM $table_name t1
         INNER JOIN (
             SELECT email, MAX(submitted_at) as max_date 
             FROM $table_name 
             WHERE $where_sql 
             GROUP BY email
         ) t2 ON t1.email = t2.email AND t1.submitted_at = t2.max_date
         GROUP BY t1.email
         ORDER BY t1.submitted_at DESC
         LIMIT " . intval($items_per_page) . " OFFSET " . intval($offset)
    );


    // Build "Available Fields" list for the filter dropdown
    $recent_leads = $wpdb->get_results("SELECT form_data FROM $table_name ORDER BY submitted_at DESC LIMIT 50");
    $all_possible_fields = [];
    foreach ($recent_leads as $l) {
        $d = json_decode($l->form_data, true);
        if (is_array($d)) {
            foreach (array_keys($d) as $k) {
                if (strpos($k, '_') !== 0 && !in_array($k, $all_possible_fields)) {
                    $all_possible_fields[] = $k;
                }
            }
        }
    }
    sort($all_possible_fields);

    // Only show CORE fields in the main view — any new/extra fields only appear in History view
    $main_view_fields = ['your-name', 'your-email', 'your-company', 'your-phone'];
    $dynamic_columns = array_filter($all_possible_fields, function ($field) use ($main_view_fields) {
        return in_array($field, $main_view_fields);
    });

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Nucleus Advisory Leads (Testing Lab)</h1>

        <!-- FILTERS BAR -->
        <div class="tablenav top"
            style="height: auto; padding: 15px; background: #fff; border: 1px solid #ccd0d4; margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: space-between; box-shadow: 0 1px 1px rgba(0,0,0,0.04);">
            <form method="get" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="page" value="nucleus-leads" />

                <!-- Field Selector -->
                <select name="filter_field" style="max-width: 150px;">
                    <option value="all" <?php selected($filter_field, 'all'); ?>>All Fields</option>
                    <option value="your-name" <?php selected($filter_field, 'your-name'); ?>>Name</option>
                    <option value="your-email" <?php selected($filter_field, 'your-email'); ?>>Email</option>
                    <option value="your-company" <?php selected($filter_field, 'your-company'); ?>>Company</option>
                    <?php foreach ($all_possible_fields as $field): ?>
                        <?php if (!in_array($field, ['your-name', 'your-email', 'your-company'])): ?>
                            <?php
                            $display_field = str_ireplace(['your-', 'your_'], '', $field);
                            $display_field = str_replace(['-', '_'], ' ', $display_field);
                            ?>
                            <option value="<?php echo esc_attr($field); ?>" <?php selected($filter_field, $field); ?>>
                                <?php echo esc_html(ucwords($display_field)); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <!-- Search Input -->
                <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>"
                    placeholder="Search keywords..." style="width: 200px;">

                <!-- Date Range -->
                <span style="color: #666; margin-left:10px;">Date:</span>
                <input type="date" name="date_start" value="<?php echo esc_attr($date_start); ?>">
                <span style="color: #666;">to</span>
                <input type="date" name="date_end" value="<?php echo esc_attr($date_end); ?>">

                <button type="submit" class="button button-primary">Filter</button>
                <a href="<?php echo admin_url('admin.php?page=nucleus-leads'); ?>" class="button">Reset</a>
            </form>

            <!-- Export Button -->
            <form method="post" style="margin:0;">
                <?php wp_nonce_field('nucleus_export_csv_action'); ?>
                <button type="submit" name="nucleus_export_csv" class="button button-secondary"
                    style="border-color: #2271b1; color: #2271b1;">
                    <span class="dashicons dashicons-download" style="vertical-align: middle;"></span> Export CSV
                </button>
            </form>
        </div>

        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <p style="color: #666; font-style: italic; margin:0;">
                <strong><?php echo intval($total_items); ?></strong> contacts found.
                Page <?php echo intval($current_page); ?> of <?php echo intval($total_pages); ?>.
                <?php if ($search_query): ?> <a href="<?php echo admin_url('admin.php?page=nucleus-leads'); ?>">Clear
                        Search</a><?php endif; ?>
            </p>

            <!-- PAGINATION LINKS -->
            <?php
            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total' => $total_pages,
                'current' => $current_page
            ));
            if ($page_links) {
                echo '<div class="tablenav-pages">' . $page_links . '</div>';
            }
            ?>
        </div>

        <div style="overflow-x: auto;">
            <table class="wp-list-table widefat fixed striped" style="min-width: 100%;">
                <thead>
                    <tr>
                        <th width="40">#</th>
                        <!-- DYNAMIC HEADERS (Cleaned up) -->
                        <?php foreach ($dynamic_columns as $col): ?>
                            <?php
                            $display_col = str_ireplace(['your-', 'your_'], '', $col);
                            $display_col = str_replace(['-', '_'], ' ', $display_col);
                            ?>
                            <th style="white-space: nowrap; text-transform: capitalize;">
                                <?php echo esc_html($display_col); ?>
                            </th>
                        <?php endforeach; ?>

                        <th width="120">History</th>
                        <th width="160">Last Contact</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contacts)): ?>
                        <tr>
                            <td colspan="<?php echo count($dynamic_columns) + 4; ?>"
                                style="text-align: center; padding: 20px; color: #666;">
                                No contacts found matching your criteria.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $row_number = $offset + 1; ?>
                        <?php foreach ($contacts as $contact): ?>
                            <?php
                            $data = json_decode($contact->form_data, true);

                            // Count total submissions for this email
                            $history_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE email = %s", $contact->email));
                            ?>
                            <tr>
                                <td><?php echo $row_number++; ?></td>
                                <!-- DYNAMIC DATA -->
                                <?php foreach ($dynamic_columns as $col): ?>
                                    <td>
                                        <?php
                                        if (isset($data[$col])) {
                                            $val = $data[$col];
                                            if (is_array($val)) {
                                                echo esc_html(implode(', ', $val));
                                            } else {
                                                // Make email clickable
                                                if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                                                    echo '<a href="mailto:' . esc_attr($val) . '">' . esc_html($val) . '</a>';
                                                } else {
                                                    echo esc_html(mb_strimwidth($val, 0, 50, "..."));
                                                }
                                            }
                                        } else {
                                            echo '<span style="color:#ddd;">—</span>';
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>

                                <!-- HISTORY COLUMN -->
                                <td>
                                    <a
                                        href="<?php echo admin_url('admin.php?page=nucleus-leads&view_email=' . urlencode($contact->email)); ?>">
                                        <?php echo intval($history_count); ?> submission<?php echo $history_count > 1 ? 's' : ''; ?>
                                    </a>
                                </td>

                                <!-- LAST CONTACT -->
                                <td>
                                    <?php echo date('Y/m/d \a\t g:i a', strtotime($contact->submitted_at)); ?>
                                </td>

                                <!-- ACTIONS -->
                                <td>
                                    <!-- View History -->
                                    <a href="<?php echo admin_url('admin.php?page=nucleus-leads&view_email=' . urlencode($contact->email)); ?>"
                                        style="color: #2271b1; text-decoration: none; margin-right: 8px;"
                                        title="View Submission History">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </a>
                                    <!-- Delete ALL for this contact -->
                                    <?php
                                    $delete_url = wp_nonce_url(
                                        admin_url('admin.php?page=nucleus-leads&action=delete&id=' . $contact->id),
                                        'nucleus_delete_lead_' . $contact->id
                                    );
                                    ?>
                                    <a href="<?php echo $delete_url; ?>"
                                        onclick="return confirm('Delete the latest submission for this contact?');"
                                        style="color: #a00; text-decoration: none;" title="Delete Latest">
                                        <span class="dashicons dashicons-trash"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
