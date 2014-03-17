<?php
/*
/*
Plugin Name: Shoprocket
Plugin URI: https://github.com/ClickHereMedia/Shoprocket
Description: Here goes description
Version: 1.0
Author: Shashikant Vaishnav
Author URI: http://www.clickheremedia.co.uk
License: GPL2
*/
/*
Copyright 2014  Shashikant Vaishnav  (email : shashikantvaishnaw@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if(!class_exists('shoprocket')) {
  ob_start();
  
  // Discover plugin path and url even if symlinked
  if(!defined('SHOPROCKET_PATH')) {
    $mj_plugin_file = __FILE__;
    if (isset($plugin)) {
      $plugin_file = $plugin;
    }
    elseif (isset($mu_plugin)) {
      $plugin_file = $mu_plugin;
    }
    elseif (isset($network_plugin)) {
      $plugin_file = $network_plugin;
    }
    define('SHOPROCKET_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($mj_plugin_file)) . '/');
    define('SHOPROCKET_URL', plugin_dir_url(SHOPROCKET_PATH) . basename(dirname($plugin_file)));
  }

  require_once(SHOPROCKET_PATH. "/models/shoprocket.php");
  require_once(SHOPROCKET_PATH. "/models/ShoprocketCommon.php");


  define('SHOPROCKET_VERSION_NUMBER', shoprocket_plugin_version());
  define("WPCURL", ShoprocketCommon::getWpContentUrl());
  define("WPURL", ShoprocketCommon::getWpUrl());
   define("SR_REST", 'http://www.shoprocket.co/index.php?/rest/');

  // IS_ADMIN is true when the dashboard or the administration panels are displayed
  if(!defined("IS_ADMIN")) {
    define("IS_ADMIN",  is_admin());
  }

  /* Uncomment this block of code for load time debugging
  $filename = SHOPROCKET_PATH . "/log.txt"; 
  if(file_exists($filename) && is_writable($filename)) {
    file_put_contents($filename, "\n\n\n================= Loading Shoprocket Main File [" . date('m/d/Y g:i:s a') . "] " . 
      $_SERVER['REMOTE_ADDR'] . " " . $_SERVER['REQUEST_URI'] . " =================\n\n", FILE_APPEND);
  }
  */
  
  $shoprocket = new shoprocket();
  load_plugin_textdomain( 'shoprocket', false, '/' . basename(dirname(__FILE__)) . '/languages/' );
  
  // Register activation hook to install Shoprocket database tables and system code
  register_activation_hook(SHOPROCKET_PATH . '/shoprocket.php', array($shoprocket, 'install'));
  
  if(FALSE) {
    register_activation_hook(SHOPROCKET_PATH . '/shoprocket.php', array($shoprocket, 'scheduledEvents'));
  }
  
  // Check for WordPress 3.1 auto-upgrades
  if(function_exists('register_update_hook')) {
    register_update_hook(SHOPROCKET_PATH . '/shoprocket.php', array($shoprocket, 'install'));
  }

  add_action('init',  array($shoprocket, 'init'));
  // Add settings link to plugin page
  add_filter('plugin_action_links', 'ShoprocketSettingsLink',10,2);
 // shoprocket_check_mail_plugins();
}

 /*function shoprocket_check_mail_plugins() {
  $wp_mail = true;
  $start = WP_PLUGIN_DIR;
  $plugin_files = array(
    'wpmandrill.php',
    'wp-ses.php'
  );
  if($dir_start = @scandir($start)) {
    foreach($dir_start as $key => $dir) {
      if(!is_dir($start . '/' . $dir) || $dir == '.' || $dir == '..') {
        continue;
      }
      if($new_dir = @scandir($start . '/' . $dir)) {
        foreach($new_dir as $key => $dir2) {
          include_once(ABSPATH . 'wp-admin/includes/plugin.php');
          if(in_array($dir2, $plugin_files) && is_plugin_active($dir . '/' . $dir2)) {
            $wp_mail = false;
          }
        }
      }
    }
  }
  define('Shoprocket_WPMAIL', $wp_mail);
  if(Shoprocket_WPMAIL) {
    include('wp_mail.php');
  }
}   */

function ShoprocketSettingsLink($links, $file) {
  if($file == basename(SHOPROCKET_PATH) . '/shoprocket.php') {
    $settings = '<a href="' . admin_url("admin.php?page=shoprocket-settings") . '">' . __('Settings', 'shoprocket') . '</a>';
    array_unshift($links, $settings);
  }
  return $links;
}

/**
 * Prevent the link rel="next" content from showing up in the wordpress header 
 * because it can potentially prefetch a page with a [clearcart] shortcode
 */
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

if(FALSE) {
  register_deactivation_hook(SHOPROCKET_PATH . '/shoprocket.php', 'deactivation');
}
function shoprocket_deactivation() {
  require_once(SHOPROCKET_PATH. "/pro/models/ShoprocketMembershipReminders.php");
  require_once(SHOPROCKET_PATH. "/pro/models/ShoprocketGravityReader.php");
  wp_clear_scheduled_hook('daily_subscription_reminder_emails');
  wp_clear_scheduled_hook('daily_followup_emails');
  wp_clear_scheduled_hook('daily_gravity_forms_entry_removal');
  wp_clear_scheduled_hook('daily_prune_pending_paypal_orders');
}

function shoprocket_plugin_version() {
  if(!function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
  }
  $plugin_data = get_plugin_data(SHOPROCKET_PATH . '/shoprocket.php');
  return $plugin_data['Version'];
}
