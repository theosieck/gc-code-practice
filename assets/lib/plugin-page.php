<?php

include_once 'judgments-db.php';
// require_once 'export.php';

add_action( 'admin_menu', 'arc_data_export_menu' );

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

function arc_data_export_page(){;
	?>
  <style>
    .gcac-button {
       background-color: #333;
       border: 0;
       cursor: pointer;
       padding: 16px 24px;
       text-decoration: none;
       width: auto;
       color: #fff;
       font-size: 16px;
       margin-top: 25px;
    }
  </style>
  <h2 class="wp-heading-inline" style="margin-bottom:30px;"><?php esc_html_e( 'ARC Assessment Data Export', 'arc-jquery-ajax' ); ?></h2>
  <a href="<?php echo esc_url( admin_url( 'admin-ajax.php' ) . '?action=gcac_do_export' ); ?>" class="gcac-button"><?php esc_html_e( 'Download CSV File', 'arc-jquery-ajax' ); ?></a>
	<hr class="wp-header-end">
   <?php
}

function gcac_wp_ajax_do_export( ) {
  require_once 'export.php';
  exit;
}
add_action( 'wp_ajax_gcac_do_export', 'gcac_wp_ajax_do_export' );
