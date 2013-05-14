<?php
/*
Plugin Name: Wpoven-Manager
Plugin URI: https://github.com/baseapp/wpoven-manager
Description: A wordpress plugin for managing various tasks including chaching, chache purging, updating components, providing instant state info etc.
Version: 0.0.1
Author: Tarun Bansal
Author URI: http://github.com/noushter
License: GPL2
*/

defined('ABSPATH') or die();
define('WPOVEN_MANAGER_DIR', WP_PLUGIN_DIR.'/wpoven-manager');

require WPOVEN_MANAGER_DIR.'/nginx-compatibility.php';

require WPOVEN_MANAGER_DIR.'/wpoven-manager-admin.php';

require WPOVEN_MANAGER_DIR.'/wpoven-manager-maintenance.php';
