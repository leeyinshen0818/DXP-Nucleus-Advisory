<?php
/**
 * Contact Form 7 Integration Handler
 * Hooks into CF7 submission to save data to custom table
 */

if (!defined('ABSPATH'))
    exit;

// Hook into Contact Form 7 submission
add_action('wpcf7_before_send_mail', 'nucleus_save_cf7_lead');

function nucleus_save_cf7_lead($contact_form)
{
    // Get submission instance
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
        $posted_data = $submission->get_posted_data();

        // Check if this is the correct form (optional: you can add check for specific form ID if needed)
        // For now, we assume any CF7 form with these specific field names should be captured.

        // Map CF7 fields to Database columns
        // Supports both field name conventions (full-name / your-name) for compatibility
        $name = sanitize_text_field(
            $posted_data['full-name'] ??
            $posted_data['your-name'] ??
            'Anonymous'
        );
        $email = sanitize_email(
            $posted_data['work-email'] ??
            $posted_data['your-email'] ??
            ''
        );
        $company = sanitize_text_field(
            $posted_data['your-company'] ??
            $posted_data['company-name'] ??
            ''
        );
        $phone = sanitize_text_field(
            $posted_data['your-phone'] ??
            $posted_data['phone-number'] ??
            ''
        );

        // Reason for Contact — radio button field from CF7
        $reason_raw = $posted_data['reason-for-contact'] ?? $posted_data['reason'] ?? '';
        $reason = is_array($reason_raw)
            ? sanitize_text_field(implode(', ', $reason_raw))
            : sanitize_text_field($reason_raw);

        // If "Others" was selected, append the specifics from the text field
        if ($reason === 'Others') {
            $others_query = sanitize_text_field($posted_data['others-query'] ?? '');
            if (!empty($others_query)) {
                $reason = 'Others: ' . $others_query;
            }
        }

        // DYNAMIC STORAGE: Save EVERYTHING to form_data as JSON fallback
        $form_data = json_encode($posted_data);

        if (!empty($posted_data)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nucleus_leads_testing';

            // Ensure table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                return;
            }

            // Insert into Database
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'email' => $email,
                    'company' => $company,
                    'phone' => $phone,
                    'reason' => $reason,       // <-- new field
                    'form_data' => $form_data,
                    'submitted_at' => current_time('mysql')
                )
            );
        }
    }
}
