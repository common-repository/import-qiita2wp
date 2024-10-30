<?php
/*
Plugin Name: Import Qiita2WP
Description: WordPress Plugin for Qiita Users. Automatically crawling your Qiita articles and place links (posts) on your WordPress site.
Author: Kai Sugahara
Author URI: https://fulfills.jp/
Domain Path: /languages/
Version: 1.0.0
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function import_qiita2wp_sanitize($obj, $is_textarea = 0) {
    if(is_array($obj)) {
        foreach($obj as $key => $val) $obj[$key] = import_qiita2wp_sanitize($val, $is_textarea);
        return $obj;
    }
    if($is_textarea) return sanitize_textarea_field($obj);
    return sanitize_text_field($obj);
}

// ADD SETTING PAGE
function import_qiita2wp_add_page() {
    include_once 'template/admin.php';
    add_submenu_page('options-general.php', __('Import Qiita2WP'), __('Import Qiita2WP'), 'publish_posts', 'import_qiita2wp_add_adminpage', 'import_qiita2wp_add_admin_page');
}
add_action('admin_menu', 'import_qiita2wp_add_page');

// ADD SETTING-PAGE-LINK to PLUGINS PAGE
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function($links){
    $url = admin_url( 'options-general.php?page=import_qiita2wp_add_adminpage' );
    $html = "<a href=\"${url}\">設定</a>";
    array_unshift($links, $html);
    return $links;
} );

// INCLUDE
include_once 'lib/init.php';
include_once 'lib/update.php';
include_once 'lib/cron.php';