<?php
/*
   Plugin Name: GC Code ARC
   Version: 1.0.0
   Author: Global Cognition
   Author URI: https://www.globalcognition.org
   Description: Serve up responses for assessment
   Text Domain: gc-assess-arc
   License: GPLv3
*/

defined( 'ABSPATH' ) or die( 'No direct access!' );

include_once 'assets/lib/cpt-setup.php';
include_once 'assets/lib/judgments-db.php';

// Call gcaa_create_table on plugin activation.
register_activation_hook(__FILE__,'gcpc_create_table'); // this function call has to happen here

function gc_assess_arc_enqueue_scripts() {

  if( is_page( 'competency-assessment' ) ) {

      global $current_user;
      get_currentuserinfo();
      if ( $current_user->ID) {

          wp_enqueue_script(
              'gcaa-main-js',
              plugins_url('/assets/js/main.js', __FILE__),
              ['wp-element', 'wp-components', 'jquery'],
              time(),
              true
          );

          $comp_num = sanitize_text_field(get_query_var('comp_num'));
          $task_num = sanitize_text_field(get_query_var('task_num'));
          $block_num = sanitize_text_field(get_query_var('block_num'));
          $review = sanitize_text_field(get_query_var('review'));;
          // $exemplar = sanitize_text_field(get_query_var('exemplar'));;
          if($review) {
            $judge1 = sanitize_text_field(get_query_var('judge1'));
            $judge2 = sanitize_text_field(get_query_var('judge2'));
            $data_for_js = arc_pull_review_data_cpts($judge1, $judge2, $comp_num, $task_num, $block_num);
          } else {
            $data_for_js = arc_pull_data_cpts($comp_num, $task_num, $block_num);
          }
          $other_data = array(
              'compNum' => $comp_num,
              'taskNum' => $task_num,
              'review' => $review
            );
          if(is_array($data_for_js)) {
            // there were no errors in pulling the data
            $data_for_js = array_merge($data_for_js,$other_data);
            d($data_for_js);
            // pass exemplars, scenarios, and competencies to Judgment App
            wp_localize_script('gcaa-main-js', 'respObj', $data_for_js);
          } else {
            // one of the pull_data functions returned an error message
            echo $data_for_js;
            // eventually, want to change this so it's not echoing where it currently is
          }
      } else {
          echo "please log in";
      }

  }
}
add_action( 'wp_enqueue_scripts', 'gc_assess_arc_enqueue_scripts' );


function gc_assess_arc_enqueue_styles() {

  wp_enqueue_style(
    'gcaa-main-css',
    plugins_url( '/assets/css/main.css', __FILE__ ),
    [],
    time(),
    'all'
  );

}
add_action( 'wp_enqueue_scripts', 'gc_assess_arc_enqueue_styles' );

// Add judge1 to url
function arc_judge1_query_vars( $qvars ) {
  $qvars[] = 'judge1';
  return $qvars;
}
add_filter( 'query_vars', 'arc_judge1_query_vars' );

// Add judge2 to url
function arc_judge2_query_vars( $qvars ) {
  $qvars[] = 'judge2';
  return $qvars;
}
add_filter( 'query_vars', 'arc_judge2_query_vars' );

// Add comp_num to url
function arc_comp_query_vars( $qvars ) {
    $qvars[] = 'comp_num';
    return $qvars;
}
add_filter( 'query_vars', 'arc_comp_query_vars' );

// Add task_num to url
function arc_task_query_vars( $qvars ) {
    $qvars[] = 'task_num';
    return $qvars;
}
add_filter( 'query_vars', 'arc_task_query_vars' );

// Add block_num to url
function arc_block_query_vars( $qvars ) {
  $qvars[] = 'block_num';
  return $qvars;
}
add_filter( 'query_vars', 'arc_block_query_vars' );

// Add review to url
function arc_review_query_vars( $qvars ) {
    $qvars[] = 'review';
    return $qvars;
}
add_filter( 'query_vars', 'arc_review_query_vars' );

// Add exemplar to url
// function arc_exemplar_query_vars( $qvars ) {
//   $qvars[] = 'exemplar';
//   return $qvars;
// }
// add_filter( 'query_vars', 'arc_exemplar_query_vars' );

// Genesis activation hook
add_action('wp_ajax_arc_save_data','arc_save_data');
/*
 * Calls the insert function from the class arc_judg_db to insert response data into the table
 */
function arc_save_data() {
    check_ajax_referer('gcaa_scores_nonce');
    global $current_user;
    // global $arc_table_postfix;
    // global $prac_table_postfix;

    // $postfix = $_POST['type'] == 'exemplar' ? $prac_table_postfix : $arc_table_postfix;
    $db = new arc_judg_db;

    // Get data from React components
    $sub_num = $_POST['sub_num'];
    $comp_num = $_POST['comp_num'];
    $task_num = $_POST['task_num'];
    $resp_id = $_POST['resp_id'];
    $judg_type = $_POST['judg_type'];
    $judg_time = $_POST['judg_time'];
    $codes = $_POST['codes'];
    $judges = $_POST['judges'];

    if($judg_time>=60) {
        $judg_time = date("H:i:s", mktime(0, 0, $judg_time));
    }
    if($ration_time>=60) {
        $ration_time = date("H:i:s", mktime(0, 0, $ration_time));
    }
    if($resp_id) {
      $title = get_the_title($resp_id);
    } else {
      $title = $_POST['resp_title'];
    }

    $db_data = array(
        'user_id' => $current_user->ID,
        'sub_num' => $sub_num,
        'comp_num' => $comp_num,
        'task_num' => $task_num,
        'resp_title' => $title,
        'judg_type' => $judg_type,
        'judg_time'  => $judg_time,
        'code1' => $codes[1][0],
        'excerpt1' => $codes[1][1],
        'code2' => $codes[2][0],
        'excerpt2' => $codes[2][1],
        'code3' => $codes[3][0],
        'excerpt3' => $codes[3][1],
        'code4' => $codes[4][0],
        'excerpt4' => $codes[4][1],
        'code5' => $codes[5][0],
        'excerpt5' => $codes[5][1],
        'code6' => $codes[6][0],
        'excerpt6' => $codes[6][1],
        'code7' => $codes[7][0],
        'excerpt7' => $codes[7][1],
        'code8' => $codes[8][0],
        'excerpt8' => $codes[8][1],
        'code9' => $codes[9][0],
        'excerpt9' => $codes[9][1],
        'rater1' => $judges[0],
        'rater2' => $judges[1]
    );

    $success = $db->insert($db_data);
    if($success) {
      $response['type'] = 'success';
      $data = $db->get_all("user_id = {$current_user->ID} AND resp_title = '{$title}'");
      $response['data'] = $data[count($data)-1];
    } else {
      $response['type'] = $success;
    }
    // $response['type'] = 'success';
    $response = json_encode($response);
    echo $response;
    die();
}


require_once( 'assets/lib/plugin-page.php' );


?>
