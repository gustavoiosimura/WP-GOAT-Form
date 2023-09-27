<?php
/*
Plugin Name: GOAT FORM
Description: Enables non-technical WordPress creators to create Forms
Version: 1.0
Author: Gustavo Iosimura
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the files for different parts of the plugin
require_once plugin_dir_path(__FILE__) . 'admin-interface.php';
require_once plugin_dir_path(__FILE__) . 'form-processing.php';
require_once plugin_dir_path(__FILE__) . 'front-end-form.php';
?>
