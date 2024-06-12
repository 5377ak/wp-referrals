<?php

/*
 * Plugin Name: WP Referrals
 * Description: WordPress pluign to manage referrals
 * Author: Akash Sharma
 * Version: 1.0
 * Text Domain: wp-referrals
 */


//Block direct access
if (!defined('ABSPATH')) {
	wp_die('Direct access not allowed!');
}

//Define plugin constants
define('WP_REFERRAL_PLUGIN_VERSION', '1.0');
define('WP_REFERRAL_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('WP_REFERRAL_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));

//Include necesssary files
require_once WP_REFERRAL_PLUGIN_DIR_PATH . 'includes/class-wp-referral.php';
require_once WP_REFERRAL_PLUGIN_DIR_PATH . 'includes/class-wp-referral-ajax.php';
require_once WP_REFERRAL_PLUGIN_DIR_PATH . 'includes/class-wp-referral-admin.php';
require_once WP_REFERRAL_PLUGIN_DIR_PATH . 'includes/class-wp-referral-code-generator.php';
require_once WP_REFERRAL_PLUGIN_DIR_PATH . 'includes/class-wp-referral-list-table.php';

//Initialize the plugin
add_action('plugins_loaded', array('WP_Referral', 'init'));
