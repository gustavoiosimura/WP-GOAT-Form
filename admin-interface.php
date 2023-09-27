<?php 

// Add a meta box to define form fields
function custom_forms_meta_box() {
    add_meta_box('custom-form-fields', 'Form Fields', 'custom_form_fields_callback', 'custom_form', 'normal', 'high');
}
add_action('add_meta_boxes', 'custom_forms_meta_box');


// Callback function for the meta box
function custom_form_fields_callback($post) {
    $form_id = $post->ID;
    $fields = custom_forms_get_fields($form_id);

    // Display existing fields
    echo '<h3>Existing Fields</h3>';
    if (!empty($fields)) {
        echo '<ul class="field-list">';
        foreach ($fields as $field) {
            echo '<li>' . esc_html($field->field_name) . ' (' . esc_html($field->field_type) . ')</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No fields defined yet.</p>';
    }

    // Create new fields
    echo '<h3>Create New Fields</h3>';
    echo '<label for="new-field-name">Field Name:</label>';
    echo '<input type="text" id="new-field-name" name="new-field-name">';
    echo '<label for="new-field-type">Field Type:</label>';
    echo '<select id="new-field-type" name="new-field-type">
            <option value="text">Text</option>
            <option value="textarea">Text Area</option>
            <option value="number">Number</option>
          </select>';
    echo '<input type="button" value="Add Field" id="add-field-button">';
    // Display the shortcode input field
    echo '<h3>Form Shortcode</h3>';
    echo '<p>Copy and paste this shortcode to display the form on your page:</p>';
    echo '<input type="text" id="form-shortcode" value="[goat-form id=' . $form_id . ']" readonly>';
}

// Add JavaScript to handle adding new fields and updating the post
function custom_form_fields_script() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Hide the default WordPress update button
            const updateButton = document.querySelector('#publish');
            if (updateButton) {
                updateButton.style.display = 'block';
            }

            const addFieldButton = document.getElementById('add-field-button');
            if (addFieldButton) {
                addFieldButton.addEventListener('click', function () {
                    const newFieldNameInput = document.getElementById('new-field-name');
                    const newFieldType = document.getElementById('new-field-type').value;
                    if (newFieldNameInput) {
                        const newFieldName = newFieldNameInput.value.trim();
                        if (newFieldName !== '') {
                            const fieldList = document.querySelector('.field-list');
                            const listItem = document.createElement('li');
                            listItem.textContent = `${newFieldName} (${newFieldType})`;
                            fieldList.appendChild(listItem);

                            // Copy the shortcode to the clipboard
                            const shortcodeInput = document.getElementById('form-shortcode');
                            shortcodeInput.select();
                            document.execCommand('copy');

                            // Trigger the save action when "Add Field" is clicked
                            if (typeof wp.data !== 'undefined') {
                                const { dispatch } = wp.data;
                                dispatch('core/editor').savePost();
                            }

                        }
                    }
                });
            }
        });
    </script>
    <?php
}
add_action('admin_footer', 'custom_form_fields_script');
?>
