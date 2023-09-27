<?php
/*
Plugin Name: GOAT FORM
Description: Enables non-technical WordPress creators to create Forms
Version: 1.0
Author: Gustavo Iosimura
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Register a custom post type for forms
function custom_forms_register_post_type() {
    $labels = array(
        'name'               => 'Custom Forms',
        'singular_name'      => 'Custom Form',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Custom Form',
        'edit_item'          => 'Edit Custom Form',
        'new_item'           => 'New Custom Form',
        'all_items'          => 'All Custom Forms',
        'view_item'          => 'View Custom Form',
        'search_items'       => 'Search Custom Forms',
        'not_found'          => 'No custom forms found',
        'not_found_in_trash' => 'No custom forms found in Trash',
        'menu_name'          => 'Custom Forms',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'custom-form' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'supports'           => array( 'title', 'editor' ), // You can customize this to include other features like custom fields
    );

    register_post_type( 'custom_form', $args );
}
add_action( 'init', 'custom_forms_register_post_type' );


// Include the files for different parts of the plugin
require_once plugin_dir_path(__FILE__) . 'admin-interface.php';
require_once plugin_dir_path(__FILE__) . 'form-processing.php';
require_once plugin_dir_path(__FILE__) . 'front-end-form.php';
?>
