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

        // Map CF7 fields to Database columns (Keep these for sorting/main view)
        $name = isset($posted_data['your-name']) ? sanitize_text_field($posted_data['your-name']) : (isset($posted_data['your-email']) ? 'Visitor' : 'Anonymous');
        $email = isset($posted_data['your-email']) ? sanitize_email($posted_data['your-email']) : '';
        $company = isset($posted_data['your-company']) ? sanitize_text_field($posted_data['your-company']) : '';
        $phone = isset($posted_data['your-phone']) ? sanitize_text_field($posted_data['your-phone']) : '';

        // DYNAMIC STORAGE: Save EVERYTHING to form_data
        $form_data = json_encode($posted_data);

        // Save even if basic fields are missing (since it's dynamic now), provided we have *something*
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
                    'form_data' => $form_data, // <--- SAVING ALL FIELDS HERE
                    'submitted_at' => current_time('mysql')
                )
            );
        }
    }
}
