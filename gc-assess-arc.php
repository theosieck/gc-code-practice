<?php
/*
   Plugin Name: GC Assess Arc
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
register_activation_hook(__FILE__,'gcaa_create_table'); // this function call has to happen here


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
          $review = sanitize_text_field(get_query_var('review'));;
          if($review == 'true') {
            $data_for_js = arc_pull_review_data_cpts($comp_num,$task_num);
          } else {
            $data_for_js = arc_pull_data_cpts($comp_num,$task_num);
          }
          $other_data = array(
              'compNum' => $comp_num,
              'taskNum' => $task_num,
              'review' => $review
            );
          $data_for_js = array_merge($data_for_js,$other_data);
        // pass exemplars, scenarios, and competencies to Judgment App
          wp_localize_script('gcaa-main-js', 'respObj', $data_for_js);

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

// Add review to url
function arc_review_query_vars( $qvars ) {
    $qvars[] = 'review';
    return $qvars;
}
add_filter( 'query_vars', 'arc_review_query_vars' );

// Genesis activation hook
add_action('wp_ajax_arc_save_data','arc_save_data');
/*
 * Calls the insert function from the class arc_judg_db to insert response data into the table
 */
function arc_save_data() {
    check_ajax_referer('gcaa_scores_nonce');
    global $current_user;
    $db = new arc_judg_db;
    // Get data from React components
    $sub_num = $_POST['sub_num'];
    $comp_num = $_POST['comp_num'];
    $task_num = $_POST['task_num'];
    $resp_id = $_POST['resp_id'];
    $judg_type = $_POST['judg_type'];
    $judg_level = $_POST['judg_level'];
    $judg_time = $_POST['judg_time'];
    $rationale = $_POST['rationale'];

    if($judg_time>=60) {
        $judg_time = date("H:i:s", mktime(0, 0, $judg_time));
    }
    if($ration_time>=60) {
        $ration_time = date("H:i:s", mktime(0, 0, $ration_time));
    }

    $db_data = array(
        'user_id' => $current_user->ID,
        'sub_num' => $sub_num,
        'comp_num' => $comp_num,
        'task_num' => $task_num,
        'resp_title' => get_the_title($resp_id),
        'judg_type' => $judg_type,
        'judg_level' => $judg_level,
        'judg_time'  => $judg_time,
        'rationale' => $rationale
    );
    $db->insert($db_data);
}


require_once( 'assets/lib/plugin-page.php' );


?>
