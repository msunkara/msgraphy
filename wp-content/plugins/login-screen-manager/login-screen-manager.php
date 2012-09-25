<?php
/*
Plugin Name: Login Screen Manager
Plugin URI: http://wordpress.org/extend/plugins/login-screen-manager
Description: This plugin is for managing your login screen of WordPress site.
Tags: login screen,login logo,wp-login.php,coding war,Nazmul Hossain Nihal,codingwar.com,logos,login screen manager
Version: 1.1
Author:	Nazmul Hossain Nihal
Author URI: http://www.codingwar.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


/******************************
* global variables
******************************/

$cwlsm_options = get_option('cwlsm_settings');

/******************************
* includes
******************************/

include('inc/admin.php'); //Admin Panel

include('inc/display.php'); //Display

include('inc/meta.php'); //Meta Tags
