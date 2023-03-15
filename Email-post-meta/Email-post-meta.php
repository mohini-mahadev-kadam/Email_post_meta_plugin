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



// add_filter( 'cron_schedules', 'add_every_minute_interval' );
// function add_every_minute_interval( $schedules ) {
//     $schedules['every_minute'] = array(
//         'interval' => 60,
//         'display'  => __( 'Every Minute' ),
//     );
//     return $schedules;
// }


// Schedule the daily email
add_action( 'wp', 'dpe_schedule_daily_email' );

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
         $meta_keywords = get_post_meta( $post->ID, '_yoast_wpseo_focuskeywords', true );
         
        //$page_speed_score = dpe_get_page_speed_score( $post_url );
         //$page_speed_score = get_page_speed_score($post_url);

        $content .= '<p><strong>' . $post->post_title . '</strong><br>'
             . 'URL: ' . $post_url . '<br>'
             . 'Met aTitle: ' . $meta_title . '<br>'
             . 'Meta Description: ' . $meta_description . '<br>'
             . 'Meta Keywords: ' . $meta_keywords . '<br>';
            //. 'Google PageSpeed Score: ' . $page_speed_score . '</p>';

        //echo "<script>alert('".$content."');</script>";


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


        /***arunesh docuement shared example START============================================================= */
        //$url = $post_url; // replace with your website URL
        $wpt_api_key = "416ca0ef-63e4-4caa-a047-ead672ecc874"; // replace with your WebPageTest API key
       // $location = "Test_Location"; // replace with the location you want to run the test from
        //$browser = "Chrome"; // replace with the browser you want to use for the test

        // Build the API request URL
        $request_url = "http://www.webpagetest.org/runtest.php?url=" . urlencode($url) .
                    "&k=" . $wpt_api_key .
                    "&f=json";
                    //"&location=" . urlencode($location) .
                    //"&browser=" . urlencode($browser);

        // Make the API request and get the test ID
        $response = file_get_contents($request_url);
       
        $json = json_decode($response);
        $test_id = $json->data->testId;

        // Poll the API until the test is complete
        $status_url = "http://www.webpagetest.org/testStatus.php?f=json&test=" . $test_id;
        $status = json_decode(file_get_contents($status_url));
        while ($status->statusCode != 200) {
        sleep(5);
        $status = json_decode(file_get_contents($status_url));
        }

        // Get the test results
        $results_url = "http://www.webpagetest.org/jsonResult.php?test=" . $test_id;
        $results = file_get_contents($results_url);
        $json = json_decode($results);
        return $json->data->runs[1]->firstView;;
        // Print the results
        /*
        return $json->data->runs[1]->firstView;*/
        
        /***arunesh docuement shared example END============================================================= */


}




// function get_page_speed_score($url) {
//     $api_key = 'AIzaSyAc0DiYuV2OUIIF6y7qLhu8zN5UMexOuA8'; //PageSpeed Insights API key
//     $url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . urlencode($url) . '&key=' . $api_key;
  
//     $ch = curl_init();
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     $response = curl_exec($ch);
//     curl_close($ch);
  
//     $data = json_decode($response, true);
//     return $data['lighthouseResult']['categories']['performance']['score'];
//   }


// // // Get the Google PageSpeed score using the PageSpeed API
// function dpe_get_page_speed_score( $url ) {
// //     $api_key = 'AIzaSyDs8OKjg59eVibwEb8PoCOFvDNVlbRv8xA'; //AIzaSyDs8OKjg59eVibwEb8PoCOFvDNVlbRv8xA
// //     $api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . urlencode( $url ) . '&key=' . $api_key;
// //     $response = wp_remote_get( $api_url );
// //     //echo "response <script>alert('".$response."');</script>";
// //    // print_r($response);

// //     if ( is_wp_error( $response ) ) {
// //         return 'Error: ' . $response->get_error_message();
// //     }
// //     $body = wp_remote_retrieve_body( $response );
// //     $data = json_decode( $body );
// //    // echo "<script>alert('".$data."');</script>";

//     //return $data->lighthouseResult->categories->performance->score * 100;
//     return '';
// }


// function get_page_speed_score($post_id) {
//     $url = get_permalink($post_id);
//     $api_key = 'AIzaSyDs8OKjg59eVibwEb8PoCOFvDNVlbRv8xA'; // Replace with your Google PageSpeed Insights API key
  
//     $request_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=' . urlencode("http://localhost:10015/maruti-suzuki/") . '&key=' . $api_key;
  
//     $response = file_get_contents($request_url);
//     $data = json_decode($response, true);
  
//     $page_speed_score = $data['lighthouseResult']['categories']['performance']['score'] * 100; // Get the performance score and multiply by 100 to get a percentage
  

//     //echo '<script>alert("'.$page_speed_score.'")</script>';

//     return $page_speed_score;
//   }
  



//Endpoints


// add_action( 'init', 'my_plugin_add_endpoint' );
// function my_plugin_add_endpoint() {
//     add_rewrite_rule( 'my-plugin-endpoint/?$', 'index.php?my_plugin_action=1', 'top' );
// }


// function my_plugin_function() {
//     echo "<script>alert('Sending mail to admin')</script>";
//     dpe_send_daily_email_callback();
// }

// add_action( 'template_redirect', 'my_plugin_process_request' );
// function my_plugin_process_request() {
//     if ( get_query_var( 'my_plugin_action' ) ) {
//         my_plugin_function();
//         exit;
//     }
// }




//initialize wordpress environment ===========================================================================

// // Check if the script is being run from the command line
// if (php_sapi_name() !== 'cli') {
//     die("Meant to be run from command line");
// }

// // Set the base path for the WordPress installation
// define('BASE_PATH', dirname(__FILE__) . '/');

// // Load the WordPress environment
// require(BASE_PATH . 'wp-load.php');

// // Your code with WordPress functions goes here













?>