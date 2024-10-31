<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

global $wpdb;
$wpdb_collate = $wpdb->collate;

$table_name = $wpdb->prefix . 'rsc_queues';
$sql =
    "CREATE TABLE {$table_name} (
         `id` int(10) NOT NULL AUTO_INCREMENT,
          `listing_id` int(10) NOT NULL,
          `post_id` int(10) NULL,
          `listing_system_modtime` int(20) NULL,
          `jsonstring` longtext NULL,
          `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `type` enum('manual','auto') DEFAULT NULL,
          `status` enum('cancel', 'fail', 'pending','done') DEFAULT NULL,
          `status_message` text NULL, 
         PRIMARY KEY  (id),
         KEY rsc_queues_paging2 (listing_id, jsonstring(500), `status`)
         )
         COLLATE {$wpdb_collate}";


dbDelta( $sql );