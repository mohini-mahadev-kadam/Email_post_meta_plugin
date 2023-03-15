Create  a plugin to email the admin about details of posts published in a day

It should contain
Post Title
Post URL
Meta Title
Meta Description
Meta keyword
Google page speed score 


apikey ==> AIzaSyDs8OKjg59eVibwEb8PoCOFvDNVlbRv8xA


<?php
/*
Plugin Name: Daily Posts Email
Description: Sends an email to the admin every day with details of the posts published that day.
*/

// Schedule the daily email
add_action( 'wp', 'dpe_schedule_daily_email' );
function dpe_schedule_daily_email() {
    if ( ! wp_next_scheduled( 'dpe_send_daily_email' ) ) {
        wp_schedule_event( strtotime( 'tomorrow midnight' ), 'daily', 'dpe_send_daily_email' );
    }
}

// Send the daily email
add_action( 'dpe_send_daily_email', 'dpe_send_daily_email_callback' );
function dpe_send_daily_email_callback() {
    // Get the posts published today
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'date_query'     => array(
            array(
                'after'     => 'today midnight',
                'before'    => 'tomorrow midnight',
                'inclusive' => true,
            ),
        ),
    );
    $posts = get_posts( $args );
    
    // Prepare the email content
    $content = '';
    foreach ( $posts as $post ) {
        $post_url = get_permalink( $post->ID );
        $meta_title = get_post_meta( $post->ID, '_yoast_wpseo_title', true );
        $meta_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
        $meta_keywords = get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true );
        $page_speed_score = dpe_get_page_speed_score( $post_url );
        $content .= '<p><strong>' . $post->post_title . '</strong><br>'
            . 'URL: ' . $post_url . '<br>'
            . 'Meta Title: ' . $meta_title . '<br>'
            . 'Meta Description: ' . $meta_description . '<br>'
            . 'Meta Keywords: ' . $meta_keywords . '<br>'
            . 'Google PageSpeed Score: ' . $page_speed_score . '</p>';
    }
    
    // Send the email
    $subject = 'New Posts Published Today';
    $headers = 'Content-Type: text/html; charset=UTF-8';
    $message = '<html><body>' . $content . '</body></html>';
    wp_mail( get_option( 'admin_email' ), $subject, $message, $headers );
}

// Get the Google PageSpeed score using the PageSpeed API
function dpe_get_page_speed_score( $url ) {
    $api_key = 'YOUR_API_KEY_HERE';
    $api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . urlencode( $url ) . '&key=' . $api_key;
    $response = wp_remote_get( $api_url );
    if ( is_wp_error( $response ) ) {
        return 'Error: ' . $response->get_error_message



