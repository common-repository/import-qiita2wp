<?php

/*
    Register update-function to WP_CRON
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'import_qiita2wp_update_cron', 'import_qiita2wp_update' );

$options = get_option( 'import_qiita2wp_settings', [] );

if( $cron_interval = $options['cron_interval'] ) {
    if ( !wp_next_scheduled( 'import_qiita2wp_update_cron' ) ) {
        wp_schedule_event( strtotime('2022-09-04 00:00:00'), $cron_interval, 'import_qiita2wp_update_cron' );
    }   
}