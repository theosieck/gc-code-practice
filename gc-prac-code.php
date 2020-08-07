<?php
/*
   Plugin Name: GC Code Practice
   Version: 0.1
   Author: Global Cognition
   Author URI: https://www.globalcognition.org
   Description: Serve up exemplars for feature coding
   Text Domain: gc-code-practice
   License: GPLv3
*/

defined( 'ABSPATH' ) or die( 'No direct access!' );

include_once 'assets/lib/cpt-setup.php';
include_once 'assets/lib/judgments-db.php';
include_once 'assets/lib/post-list-additions.php';

// Call gcpc_create_table on plugin activation.
register_activation_hook(__FILE__,'gcpc_create_table'); // this function call has to happen here

function gc_prac_code_enqueue_scripts() {

  if( is_page( 'coding/practice' ) ) {
      global $current_user;
      get_currentuserinfo();
      if ( $current_user->ID) {

          wp_enqueue_script(
              'gcpc-main-js',
              plugins_url('/assets/js/main.js', __FILE__),
              ['wp-element', 'wp-components', 'jquery'],
              time(),
              true
          );

          $comp_num = sanitize_text_field(get_query_var('comp_num'));
          $task_num = sanitize_text_field(get_query_var('task_num'));
          $data_for_js = gcpc_pull_data_cpts($comp_num, $task_num);
          $other_data = array(
              'compNum' => $comp_num,
              'taskNum' => $task_num
            );
          if(is_array($data_for_js)) {
            // there were no errors in pulling the data
            $data_for_js = array_merge($data_for_js,$other_data);
            // pass exemplars, scenarios, and competencies to Practice App
            wp_localize_script('gcpc-main-js', 'expObj', $data_for_js);
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
add_action( 'wp_enqueue_scripts', 'gc_prac_code_enqueue_scripts' );


function gcpc_enqueue_styles() {

  wp_enqueue_style(
    'gcpc-main-css',
    plugins_url( '/assets/css/main.css', __FILE__ ),
    [],
    time(),
    'all'
  );

}
add_action( 'wp_enqueue_scripts', 'gcpc_enqueue_styles' );

// Genesis activation hook
add_action('wp_ajax_save_prac_data','save_prac_data');
/*
 * Calls the insert function from the class ARCJudgDB to insert response data into the table
 */
function save_prac_data() {
    check_ajax_referer('gcpc_scores_nonce');
    global $current_user;

    $db = new PracJudgDB;

    // Get data from React components
    $sub_num = $_POST['sub_num'];
    $comp_num = $_POST['comp_num'];
    $task_num = $_POST['task_num'];
    $exp_id = $_POST['exp_id'];
    $judg_time = $_POST['judg_time'];
    $codes = $_POST['codes'];
    $code_scheme = $_POST['code_scheme'];
		$correct_codes = $_POST['correctCodes'];
		$missed_codes = $_POST['missedCodes'];
		$false_positives = $_POST['falsePositives'];

    if($judg_time>=60) {
        $judg_time = date("H:i:s", mktime(0, 0, $judg_time));
    }
    if($exp_id) {
      $title = get_the_title($exp_id);
    } else {
      $title = $_POST['exp_title'];
    }

    $db_data = array(
        'user_id' => $current_user->ID,
        'sub_num' => $sub_num,
        'comp_num' => $comp_num,
        'task_num' => $task_num,
        'exp_title' => $title,
        'judg_time'  => $judg_time,
        'code_scheme' => $code_scheme,
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
				'correct_codes' => implode(',',$correct_codes),
				'missed_codes' => implode(',',$missed_codes),
				'false_positives' => implode(',',$false_positives)
    );

    $success = $db->insert($db_data);
    if($success) {
      $response['type'] = 'success';
      $data = $db->get_all_obj("user_id = {$current_user->ID} AND exp_title = '{$title}'");
      $response['data'] = $data[count($data)-1];
    } else {
      $response['type'] = $success;
    }
    // $response['type'] = 'success';
    $response = json_encode($response);
    echo $response;
    die();
}

?>
