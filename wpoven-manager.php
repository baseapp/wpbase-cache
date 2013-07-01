<?php
/*
Plugin Name: WPbase-Cache
Plugin URI: https://github.com/baseapp/wpbase-cache
Description: A wordpress plugin for managing various tasks including chaching, chache purging, updating components, providing instant state info etc.
Version: 0.0.1
Author: Tarun Bansal
Author URI: http://noushter.wordpress.com
License: GPL2

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

defined('ABSPATH') or die();
define('WPOVEN_MANAGER_DIR', WP_PLUGIN_DIR.'/wpoven-manager');

require WPOVEN_MANAGER_DIR.'/wpoven-manager-admin.php';