<?php
/*
Plugin Name: GOAT FORM
Description: Enables non-technical WordPress creators to create Forms
Version: 1.3
Author: Gustavo Iosimura
*/

global $wpdb; // Ensure $wpdb is global

// Define database table names
$table_name_forms = $wpdb->prefix . 'custom_forms';
$table_name_fields = $wpdb->prefix . 'form_fields'; // New table for form fields

// Register a custom post type for forms
function custom_forms_register_post_type() {
    $args = array(
        'public' => true,
        'label'  => 'Custom Forms',
        'menu_icon' => 'dashicons-feedback', // Icon for the custom forms menu item
    );
    register_post_type('custom_form', $args);
}
add_action('init', 'custom_forms_register_post_type');

// Create the database tables when the plugin is activated
function custom_forms_install() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Create table for form definitions (if not exists)
    $table_name_forms = $wpdb->prefix . 'custom_forms';
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
    $table_name_fields = $wpdb->prefix . 'form_fields';
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
    $table_name_submissions = $wpdb->prefix . 'form_submissions';
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_submissions'") != $table_name_submissions) {
        $sql = "CREATE TABLE $table_name_submissions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_id mediumint(9) NOT NULL,
            submission_data longtext NOT NULL,
            submission_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

register_activation_hook(__FILE__, 'custom_forms_install');

// Add a menu item to the WordPress admin for form creation and list forms
function custom_forms_menu() {
    add_menu_page(
        'Custom Forms',
        'Custom Forms',
        'manage_options',
        'custom-forms',
        'custom_form_builder_page'
    );

    add_submenu_page(
        'custom-forms',
        'Form Builder',
        'Form Builder',
        'manage_options',
        'custom-form-builder',
        'custom_form_builder_page'
    );

    // Add more submenu items if needed
}
add_action('admin_menu', 'custom_forms_menu');

// Create the admin page for form creation
function custom_form_builder_page() {
    ?>
    <div class="wrap">
        <h2>Create New Form</h2>
        <form method="post" action="">
            <label for="form-name">Form Name:</label>
            <input type="text" id="form-name" name="form_name" required>
            <input type="submit" name="create_form" value="Create Form">
        </form>

        <?php
        if (isset($_POST['create_form'])) {
            $form_name = sanitize_text_field($_POST['form_name']);

            // Create a new form post
            $form_id = wp_insert_post(array(
                'post_title' => $form_name,
                'post_type' => 'custom_form',
                'post_status' => 'publish',
            ));

            if ($form_id) {
                echo '<p>Form created successfully with ID: ' . esc_html($form_id) . '</p>';
            } else {
                echo '<p>Failed to create the form.</p>';
            }
        }
        ?>
    </div>

    <div class="wrap">
        <h2>Manage Forms</h2>
        <?php
        $forms = get_posts(array(
            'post_type' => 'custom_form',
            'posts_per_page' => -1,
        ));

        if (!empty($forms)) {
            echo '<ul>';
            foreach ($forms as $form) {
                echo '<li>';
                echo '<strong>' . esc_html($form->post_title) . '</strong>';
                echo ' [<a href="' . get_edit_post_link($form->ID) . '">Edit</a>]';
                echo ' [<a href="#" onclick="generateShortcode(' . $form->ID . '); return false;">Generate Shortcode</a>]';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No forms found.</p>';
        }
        ?>
    </div>

    <script>
        function generateShortcode(formId) {
            alert('[custom-form id=' + formId + ']');
        }
    </script>
    <?php
}

// Function to get form fields for a specific form
function custom_forms_get_fields($form_id) {
    global $wpdb;
    $table_name_fields = $wpdb->prefix . 'form_fields';
    
    $fields = $wpdb->get_var($wpdb->prepare(
        "SELECT field_name FROM $table_name_fields WHERE form_id = %d",
        $form_id
    ));

    return $fields;
}
 
// Function to update form fields for a specific form
function custom_forms_update_fields($form_id, $fields) {
    global $wpdb;
    $table_name_fields = $wpdb->prefix . 'form_fields';

    // Delete existing fields for the form
    $wpdb->delete(
        $table_name_fields,
        array('form_id' => $form_id),
        array('%d')
    );

    // Insert the updated fields
    $fields = explode("\n", $fields);
    foreach ($fields as $field) {
        $field = trim($field);
        if (!empty($field)) {
            $wpdb->insert(
                $table_name_fields,
                array(
                    'form_id' => $form_id,
                    'field_name' => $field,
                    'field_type' => 'text', // You can customize this as needed
                ),
                array('%d', '%s', '%s')
            );
        }
    }
}
