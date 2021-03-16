<?php
/*
Plugin Name: Smart Prev Next Buttons
Version: 0.5
Plugin URI: http://toolstack.com/smart-prev-next
Author: Greg Ross
Author URI: http://toolstack.com
Text Domain: smart-prev-next
Domain Path: /languages/
Description: Adds smart previous/next buttons to the editor, supports both Classic and Gutenberg!
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Compatible with WordPress 3.5+.

Read the accompanying readme.txt file for instructions and documentation.

Copyright (c) 2021 by Greg Ross

This software is released under the GPL v2.0, see license.txt for details
*/

define( 'SmartPrevNext', '0.5' );

include_once( 'smart-prev-next-functions.php' );

// Add translation action.
add_action( 'plugins_loaded', 'SmartPrevNextLanguage' );
	
// Handle the post screens.
add_action( 'admin_head-post.php', 'SmartPrevNextEditor' );

// Handle altering the post/page rows to add the filter parameters to them.
add_filter( 'post_row_actions', 'SmartPrevNextLinkRow', 999, 2 );
add_filter( 'page_row_actions', 'SmartPrevNextLinkRow', 999, 2 );
add_filter( 'get_edit_post_link', 'SmartPrevNextEditPostLink', 999, 3 );

// Handle the admin menu.	
add_action( 'admin_menu', 'SmartPrevNextAdminMenu' );

?>