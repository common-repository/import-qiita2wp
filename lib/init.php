<?php

/*
    Init Functions
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// QiitaのPostのパーマリンクを変更
function append_query_string( $url, $post, $leavename=false ) {

    $options = get_option( 'import_qiita2wp_settings', [] );

    // IS SET QIITA_CATEGORY ?
    if($options['set_category']) {
        // IS THIS QIITA_POST?
        if(in_array(intval($options['set_category']), array_column(get_the_category($post->ID), 'term_id'))) {
            $qiita_url = get_post_meta($post->ID, "import_qiita2wp_url", TRUE);
            if($qiita_url) return $qiita_url;
        }
    }

    return $url;

}
add_filter( 'post_link', 'append_query_string', 10, 3 );

// ログメッセージの保存
function import_qiita2wp_add_log_message($message) {
    // READ
    $log_array = get_option( 'import_qiita2wp_logs', [] );
    // ADD
    $log_array[wp_date('Y-m-d H:i:s')] = $message;
    // DELETE(LIMIT)
    while(count($log_array) > 50) array_shift($log_array);
    // SAVE(UPDATE)
    update_option('import_qiita2wp_logs', $log_array );
}