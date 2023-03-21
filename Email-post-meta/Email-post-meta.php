<?php

//require_once("wp-load.php");

///home/mohini/Local Sites/plugindevelop1/app/public/wp-content/plugins/Email-post-meta/
/*
Plugin Name: Email-post-meta Plugin
Plugin URI: https://www.mk.com/email-post-meta
Description: This is plugin will send daily email to site admin containing meta data of published post and speed details
Author: Mohini Kadam
Author URI: https://www.mk.com
Version: 1.0
*/


// Schedule the daily email
add_action( 'wp', 'dpe_schedule_daily_email' );

/*
add_filter( 'cron_schedules', 'add_every_minute_interval' );
function add_every_minute_interval( $schedules ) {
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display'  => __( 'Every Minute' ),
    );
    return $schedules;
}
*/

function dpe_schedule_daily_email() {

    if ( ! wp_next_scheduled( 'dpe_send_daily_email' ) ) {
        wp_schedule_event( strtotime('today 12:00am'), 'daily', 'dpe_send_daily_email' );
        //wp_schedule_event( strtotime( 'midnight' ), 'daily', 'dpe_send_daily_email' );
        //wp_schedule_event( time(), 'every_minute', 'dpe_send_daily_email' );
    }
}


// //Send the daily email
 add_action( 'dpe_send_daily_email', 'dpe_send_daily_email_callback' );

///home/mohini/Local Sites/plugindevelop1/app/public

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
                'inclusive' => false,
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
         
        //$page_speed_score = dpe_get_page_speed_score( $post_url );
         $page_speed_score = get_page_speed_score($post_url);

        $content .= '<p><strong>' . $post->post_title . '</strong><br>'
             . 'URL: ' . $post_url . '<br>'
             . 'Met aTitle: ' . $meta_title . '<br>'
             . 'Meta Description: ' . $meta_description . '<br>'
             . 'Meta Keywords: ' . $meta_keywords . '<br>'
             .'Google PageSpeed Score: ' . $page_speed_score . '</p>';

       // echo "<script>alert('".$content."');</script>";


    }
 
    // Send the email
    $subject = 'Posts Published Today';
     $headers = 'Content-Type: text/html; charset=UTF-8';
    $message = '<html><body>' . $content . '</body></html>';
    echo "<script>alert(".$message.")</script>";
    mail( get_option( 'admin_email' ), $subject, $message, $headers );

    // update_option( 'test1', 'Hello' );
    // echo "Hello we are here";
    // mail( 'mohini.kadam@wisdmlabs.com', 'testing the plugin', 'testing the plugin', $headers );
}



//dpe_send_daily_email_callback();


function get_page_speed_score($url)
{
   
    $wpt_api_key = "416ca0ef-63e4-4caa-a047-ead672ecc874"; // WebPageTest API key
    
    $new_url = "http://www.webpagetest.org/runtest.php?url=".$url."&runs=1&f=xml&k=".$wpt_api_key; 
    $run_result = simplexml_load_file($new_url);
    $test_id = $run_result->data->testId;

    $status_code=100;
    
    while( $status_code != 200){
        sleep(10);
        $xml_result = "http://www.webpagetest.org/xmlResult/".$test_id."/";
        $result = simplexml_load_file($xml_result);
        $status_code = $result->statusCode;
        $time = (float) ($result->data->median->firstView->loadTime)/1000;
    };

    return $time;
}

?>
