<?php

/*
    Update function of Qiita-articles
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function import_qiita2wp_getrequest2qiita($token = "", $page_num=1) {
    
    $url = "https://qiita.com/api/v2/authenticated_user/items?page=${page_num}&per_page=100";

    $args = [
        'headers' => [
            'Authorization' => "Bearer ${token}",
        ],
    ];

    $response = wp_remote_get($url, $args);
    $body = wp_remote_retrieve_body( $response );

    return json_decode($body);

}

function import_qiita2wp_update() {
    
    $options = get_option( 'import_qiita2wp_settings', [] );

    // TOKEN CHECK
    if(!$options['your_token']) {
        import_qiita2wp_add_log_message("Error!トークンが設定されていません。");
        return "Error!トークンが設定されていません。";
    }

    // CATEGORY CHECK
    if(!$options['set_category']) {
        import_qiita2wp_add_log_message("Error!カテゴリが設定されていません。");
        return "Error!カテゴリが設定されていません。";
    }

    // GET REQUEST TO QIITA API
    $data_title = [];
    $data_time = [];
    $data_url = [];
    $data_id = [];

    $i = 1;
    while($row_data = import_qiita2wp_getrequest2qiita($options['your_token'], $i++)) {
        $data_title += array_column($row_data, 'title', 'id');
        $data_time += array_column($row_data, 'updated_at', 'id');
        $data_url += array_column($row_data, 'url', 'id');
        $data_id += array_column($row_data, 'id');
    }

    // SAVE 

    foreach($data_id as $qiita_id) {

        $exist_posts = get_posts([
            'posts_per_page' => 1,
            'category' => $options['set_category'],
            'name' => $qiita_id,
            'post_type' => 'post',
        ]);
        if($exist_posts) {
            // Update new post
            $post_id = $exist_posts[0]->ID;
            $post = array(
                'ID' => $post_id,
                'post_name' => $qiita_id,
                'post_title' => $data_title[$qiita_id],
                'post_date' => str_replace(['T', '+09:00'], [' ', ''], $data_time[$qiita_id]),
                'post_category' => [$options['set_category']],
                'post_status' => 'publish',
            );
            $post_id = wp_update_post($post);
            update_post_meta( $post_id, 'qiita2wp_url', $data_url[$qiita_id] );
        }
        else {
            // Add new post
            $post = array(
                'post_name' => $qiita_id,
                'post_title' => $data_title[$qiita_id],
                'post_date' => str_replace(['T', '+09:00'], [' ', ''], $data_time[$qiita_id]),
                'post_category' => [$options['set_category']],
                'post_status' => 'publish',
            );
            $post_id = wp_insert_post($post);
            update_post_meta( $post_id, 'import_qiita2wp_url', $data_url[$qiita_id] );
        }

    }

    import_qiita2wp_add_log_message("記事の更新が完了しました。");
    return '';

}