<?php
/*
Plugin Name: Sync My Rex
Plugin URI: https://rex-sync.com
Description: Providing tool to sync all listings, listing agents from Rex Software to WordPress
Version: 2.2.2
Author: Phuc Pham
Author URI: https://rex-sync.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require __DIR__.'/loader.php';
require __DIR__.'/upgrade.php';


register_activation_hook(__FILE__, 'Rex_Sync_activation');
function Rex_Sync_activation() {
    require 'db.php';

}

register_deactivation_hook(__FILE__, 'Rex_Sync_deactivation');
function Rex_Sync_deactivation() {

}

if(class_exists('\Rex\Sync\Loader'))
    \Rex\Sync\Loader::load();