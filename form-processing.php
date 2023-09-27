<?php 
 
// Create the database tables when the plugin is activated
function custom_forms_install() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Create table for form definitions (if not exists)
    $table_name_forms = $wpdb->prefix . 'goat_custom_forms';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_forms'") != $table_name_forms) {
        $sql = "CREATE TABLE $table_name_forms (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_name varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    // Create table for form fields (if not exists)
    $table_name_fields = $wpdb->prefix . 'goat_form_fields';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_fields'") != $table_name_fields) {
        $sql = "CREATE TABLE $table_name_fields (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            field_name varchar(255) NOT NULL,
            field_type varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    // Create table for form submissions (if not exists)
    $table_name_submissions = $wpdb->prefix . 'goat_form_submissions';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_submissions'") != $table_name_submissions) {
        $sql = "CREATE TABLE $table_name_submissions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            field_id mediumint(9) NOT NULL,
            value longtext NOT NULL,
            submission_datetime datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'custom_forms_install');

// Function to get form fields for a specific form
function custom_forms_get_fields($form_id) {
    global $wpdb;
    $table_name_fields = $wpdb->prefix . 'goat_form_fields';
    
    $fields = $wpdb->get_results($wpdb->prepare(
        "SELECT field_name, field_type FROM $table_name_fields WHERE form_id = %d",
        $form_id
    ));

    return $fields;
}

// Save custom form fields and shortcode to the database
function custom_forms_save_post($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['new-field-type'])) {
        $newFieldType = sanitize_text_field($_POST['new-field-type']);

        if (isset($_POST['new-field-name'])) {
            $newFieldName = sanitize_text_field($_POST['new-field-name']);

            // Retrieve existing fields
            $fields = custom_forms_get_fields($post_id);

            $fieldsArray = array();
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $fieldsArray[] = (array) $field;
                }
            }

            // Append the new field with name and type
            $newField = array(
                'field_name' => $newFieldName,
                'field_type' => $newFieldType,
            );

            $fieldsArray[] = $newField;

            // Save the updated fields to the database
            custom_forms_update_fields($post_id, $fieldsArray);
        }
    }

    // Save the form shortcode
    if (isset($_POST['form-shortcode'])) {
        $shortcode = sanitize_text_field($_POST['form-shortcode']);
        update_post_meta($post_id, '_form_shortcode', $shortcode);
    }
}
add_action('save_post_custom_form', 'custom_forms_save_post');

// Function to update form fields for a specific form
function custom_forms_update_fields($form_id, $new_fields) {
    global $wpdb;
    $table_name_fields = $wpdb->prefix . 'goat_form_fields';

    // Delete existing fields for this form
    $wpdb->delete($table_name_fields, array('form_id' => $form_id), array('%d'));

    // Insert the new fields
    foreach ($new_fields as $field) {
        $wpdb->insert(
            $table_name_fields,
            array(
                'form_id' => $form_id,
                'field_name' => $field['field_name'],
                'field_type' => $field['field_type'],
            ),
            array('%d', '%s', '%s')
        );
    }
}
?>
