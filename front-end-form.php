<?php 

// Define a function to render the form using a shortcode
function render_goat_form($atts) {
    // Extract shortcode attributes, e.g., [goat-form id=123]
    $atts = shortcode_atts(array(
        'id' => 0, // Default form ID
    ), $atts);

    // Get the form ID from the shortcode attributes
    $form_id = intval($atts['id']);

    // Check if a valid form ID is provided
    if ($form_id > 0) {
        // Generate and return the form HTML here based on the form ID
        return generate_goat_form_html($form_id);
    } else {
        return 'Invalid form ID.';
    }
}

// Register the shortcode [goat-form]
add_shortcode('goat-form', 'render_goat_form');
  
// Function to generate HTML for a specific form
function generate_goat_form_html($form_id) {
    // Fetch the form fields based on the form ID
    $fields = custom_forms_get_fields($form_id);

    // Build and return the HTML form
    $html = '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    $html .= '<input type="hidden" name="action" value="process_goat_form">';
    $html .= '<input type="hidden" name="form_id" value="' . $form_id . '">';

    foreach ($fields as $field) {
        $field_name = esc_html($field->field_name);
        $field_type = esc_html($field->field_type);

        // Customize the HTML structure based on field type
        if ($field_type === 'text') {
            $html .= '<label for="' . $field_name . '">' . $field_name . ':</label>';
            $html .= '<input type="text" id="' . $field_name . '" name="' . $field_name . '" required><br>';
        } elseif ($field_type === 'textarea') {
            $html .= '<label for="' . $field_name . '">' . $field_name . ':</label>';
            $html .= '<textarea id="' . $field_name . '" name="' . $field_name . '" required></textarea><br>';
        } elseif ($field_type === 'number') {
            $html .= '<label for="' . $field_name . '">' . $field_name . ':</label>';
            $html .= '<input type="number" id="' . $field_name . '" name="' . $field_name . '" required><br>';
        }

        // You can customize the HTML structure for other field types as needed
    }

    $html .= '<input type="submit" value="Submit">';
    $html .= '</form>';

    return $html;
}



 
// Function to process form submissions
function process_goat_form() {
    if (isset($_POST['action']) && $_POST['action'] === 'process_goat_form') {
        // Sanitize and validate form data
        $form_id = intval($_POST['form_id']);
        $fields = custom_forms_get_fields($form_id);

        // Initialize submission data
        $submission_data = array();

        foreach ($fields as $field) {
            $field_name = $field->field_name;
            $field_type = $field->field_type;

            // Check if 'id' property exists before accessing it
            $field_id = isset($field->id) ? $field->id : 0;

            // Customize the data processing based on field type
            if ($field_type === 'text' || $field_type === 'textarea') {
                // Check if the field name exists in the POST data
                if (isset($_POST[$field_name])) {
                    $field_value = sanitize_text_field($_POST[$field_name]);
                } else {
                    $field_value = ''; // Set a default value or handle the missing field as needed
                }
            } elseif ($field_type === 'number') {
                // Check if the field name exists in the POST data
                if (isset($_POST[$field_name])) {
                    $field_value = intval($_POST[$field_name]);
                } else {
                    $field_value = 0; // Set a default value or handle the missing field as needed
                }
            }

            // Store the field data in the submission_data array
            $submission_data[] = array(
                'field_id' => $field_id,
                'value' => $field_value,
            );
        }

        // Insert the form submission data into the new submissions table
        global $wpdb;
        $table_name_submissions = $wpdb->prefix . 'goat_form_submissions';

        foreach ($submission_data as $data) {
            $result = $wpdb->insert(
                $table_name_submissions,
                array(
                    'form_id' => $form_id,
                    'field_id' => $data['field_id'],
                    'value' => $data['value'],
                    'submission_datetime' => current_time('mysql'),
                )
            );

            if ($result === false) {
                error_log('Failed to insert submission data: ' . $wpdb->last_error);
            } else {
                error_log('Successfully inserted submission data for field ID: ' . $data['field_id']);
            }
        }

        // Check if '_wp_http_referer' is set in POST, otherwise, use a default URL
        $redirect_url = isset($_POST['_wp_http_referer']) ? $_POST['_wp_http_referer'] : home_url(); // Change 'home_url()' to your desired default URL
        wp_redirect($redirect_url);
        exit;
    }
}



// Register the admin-post.php action
add_action('admin_post_process_goat_form', 'process_goat_form');
add_action('admin_post_nopriv_process_goat_form', 'process_goat_form');
?>
