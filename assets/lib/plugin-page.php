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

function arc_data_export_page(){
  $csv_export_link = admin_url( 'admin-ajax.php' ) . '?action=gcac_do_export';
	?>
  <style>
    .gcac-button {
       background-color: #333;
       border: 0;
       cursor: pointer;
       padding: 16px 24px;
       text-decoration: none;
       max-width: 125px;
       width: auto;
       color: #fff;
       font-size: 16px;
       display: inline-block;
       margin-bottom: 10px;
    }
  </style>
  <h2 class="wp-heading-inline"><?php esc_html_e( 'ARC Assessment Data Export', 'arc-jquery-ajax' ); ?></h2>
  <h3>Select which competency you would like to export:</h3>
  <a href="<?php echo esc_url( $csv_export_link ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'All Competencies', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=1" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 1', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=2" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 2', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=3" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 3', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=4" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 4', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=5" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 5', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=6" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 6', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=7" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 7', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=8" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 8', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=9" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 9', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=10" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 10', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=11" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 11', 'arc-jquery-ajax' ); ?></a>
  <a href="<?php echo esc_url( $csv_export_link . "&comp_num=12" ); ?>" class="page-title-action gcac-button"><?php esc_html_e( 'Competency 12', 'arc-jquery-ajax' ); ?></a>
	<hr class="wp-header-end">
   <?php
}

function gcac_wp_ajax_do_export( ) {
  require_once 'export.php';
  exit;
}
add_action( 'wp_ajax_gcac_do_export', 'gcac_wp_ajax_do_export' );
