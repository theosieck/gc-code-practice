<?php

include_once 'judgments-db.php';
// require_once 'export.php';

add_action( 'admin_menu', 'arc_data_export_menu' );
add_action( 'admin_enqueue_scripts', 'arc_backend_scripts' );
add_filter( 'wp_ajax_gcpc_do_export', 'gcpc_do_export' );


$main_menu_url = 'arc-data-export';

function arc_data_export_menu() {
  global $main_menu_url;
	add_menu_page(
    'ARC Assessment Data Export',
    'ARC Data Export',
    'manage_options',
    $main_menu_url . '.php',
    'arc_data_export_page',
    'dashicons-heart',
    76
  );
}

function arc_data_export_page(){
  // ob_start();
  // ob_end_clean();
  // $export = new GCPCExport;
  // $success = $export->gcpc_do_export();
  // var_dump( $success);
	?>
  <head><style>
     button {
       background-color: #333;
       border: 0;
       cursor: pointer;
       padding: 16px 24px;
       white-space: normal;
       width: auto;
       text-decoration: none;
       color: #fff;
       font-size: 16px;
       font-weight: 700;
     }
   </style></head>
   <body>
	 <div class="wrap">
	 	<h2><?php esc_html_e( 'ARC Assessment Data Export', 'arc-jquery-ajax' ); ?></h2>
     <h3>Select which competency you would like to export:</h3>
     <select name="arc-select" id="arc-select">
      <option value="all">All</option>
       <option value="1">Competency 1</option>
       <option value="2">Competency 2</option>
       <option value="3">Competency 3</option>
       <option value="4">Competency 4</option>
       <option value="5">Competency 5</option>
       <option value="6">Competency 6</option>
       <option value="7">Competency 7</option>
       <option value="8">Competency 8</option>
       <option value="9">Competency 9</option>
       <option value="10">Competency 10</option>
       <option value="11">Competency 11</option>
       <option value="12">Competency 12</option>
     </select>
     <button id="download-button" class="arc-download">Download</button>
	 </div>
   </body>
   <?php
}

function arc_backend_scripts( $hook ) {

  global $main_menu_url;
  if( $hook != 'toplevel_page_' . $main_menu_url ) {
    return;
  }

  $nonce = wp_create_nonce( 'arc_download' );

  wp_enqueue_script( 'arc-backend-js', plugins_url( '../js/backend-main.js', __FILE__ ), [], time(), true );
  wp_localize_script(
    'arc-backend-js',
    'arc_globals',
    [
      'ajax_url'    => admin_url( 'admin-ajax.php' ),
      'nonce'       => $nonce
    ]
  );
}

function gcpc_do_export( ) {
  // GCPCExport::gcpc_do_export();

  check_ajax_referer( 'arc_download' );

  $db = new arc_judg_db;

  if(!headers_sent()) {
    $filepath = 'C:\Users\JorieSieck\Local Sites\newscratch\app\public\wp-content\plugins\gc-prac-code\assets\lib\testtext.txt';
    // set headers
    $headers = array();
    $headers[] = "Content-Description: File Transfer";
    $headers[] = "Content-Disposition: attachment; filename=\"$filepath\"";
    $headers[] = "Content-Type: text/plain; charset=UTF-8";
    $headers[] = "Cache-Control: max-age=0, no-cache, no-store";
    $headers[] = "Pragma: no-cache";
    $headers[] = "Connection: close";

    // set default csv headers
    $columns = $db->get_columns();
    $csv_headers = "";
    foreach($columns as $column) {
      $csv_headers .= $column->Key == "PRI" ? $column->Field : ("," . $column->Field);
    }
    $csv_headers .= "\n";

    // get data from db
    // $comp_num = $_POST['comp_num'];
    // if($comp_num != 'all') {
    //   $all_rows = $db->get_all_arraya("comp_num = ${comp_num}");
    // } else {
    //   $all_rows = $db->get_all();
    // }

    // create temp dir/file
    $tmp_dir = sys_get_temp_dir();
    $filename = tempnam( $tmp_dir, 'gcpc_data_');

    // open file for appending
    $csv_file = fopen($filename, 'a');

    // write csv header to file
    fprintf($csv_file, '%s', $csv_headers);

    //* send data
    // close temp file
    fclose($csv_file);

    // set download size
    $headers[] = "Content-Length: " . filesize($filename);

    // send headers
    foreach($headers as $header) {
      header($header . "\r\n");
    }

    // read the file to output - if using on flywheel site, use readfile instead
    $fp = fopen($filename, 'rb');
    fpassthru($fp);
    fclose($fp);

    // remove temp file
    unlink($filename);

    // exit
    exit;
    
    // NEXT STEPS: FOLLOW WHAT PMPRO IS DOING IN C:\Users\JorieSieck\Documents\work\fix_pmpro_checkout\fresh_copy_plugins\paid-memberships-pro\adminpages\memberslist-csv.php

    // header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    // header("Cache-Control: post-check=0, pre-check=0", false);
    // header("Pragma: no-cache");

    // flush output - ob_end_clean() is the same as ob_flush() + flush()
    // ob_start();
    // while(ob_get_level()) {
    //   ob_end_clean();
    // }
    // $buffer_length = ob_get_length(); //length or false if no buffer
		// if ( $buffer_length > 1 ) {
		// 	ob_clean();
		// }

    // // get the data
    // $result = readfile($filepath);
    // // ob_end_clean();

    // if($result === false) {
    //   echo 'an error occurred while reading the file';
    // }

    // // ob_end_clean();
    // exit;
  }
  // $response['type'] = headers_list();

  // $response = json_encode($response);
  // echo $response;

  // die;

}
