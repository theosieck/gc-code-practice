<?php
/*
 * Sets up the Response, Competency, and Scenario CPTs.
 */
namespace GC\ARC\Custom;
add_action('init',__NAMESPACE__ . '\arc_register_cpt_response');
/*
 * Adds the "Response" custom post type
 */
function arc_register_cpt_response() {
    $labels = array(
        'name' => _x('Responses','responses'),
        'singular_name' => _x('Response','response'),
        'all_items' => ('All Responses'),
        'add_new_item' => ('Add New Response'),
        'edit_item' => ('Edit Response'),
        'search_items' => ('Search Responses'),
        'view_item' => ('View Response'),
    );
    $args = array(
        'label' => __('Responses', 'responses'),
        'labels' => $labels,
        'public' => true,
        'taxonomies' => array('category'),
        'show_in_rest' => true,
        'supports' => array('editor')
    );
    register_post_type('response',$args);
}

add_action('init',__NAMESPACE__ . '\arc_register_cpt_competency');
/*
 * Adds the "Competency" custom post type
 */
function arc_register_cpt_competency() {
    if(!post_type_exists('competency')) {
        $labels = array(
            'name' => _x('Competencies','competencies'),
            'singular_name' => _x('Competency','competency'),
            'all_items' => ('All Competencies'),
            'add_new_item' => ('Add New Competency'),
            'edit_item' => ('Edit Competency'),
            'search_items' => ('Search Competencies'),
            'view_item' => ('View Competency'),
        );
    
        $args = array(
            'label' => __('Competencies', 'competencies'),
            'labels' => $labels,
            'public' => true,
            'taxonomies' => array('category'),
            'show_in_rest' => true,
            'supports' => array('editor')
        );
    
        register_post_type('competency',$args);
    }
}

add_action('init',__NAMESPACE__ . '\arc_register_cpt_scenario');
/*
 * Adds the "Scenario" custom post type
 */
function arc_register_cpt_scenario() {
    if(!post_type_exists('scenario')) {
        $labels = array(
            'name' => _x('Scenarios','scenarios'),
            'singular_name' => _x('Scenario','scenario'),
            'all_items' => ('All Scenarios'),
            'add_new_item' => ('Add New Scenario'),
            'edit_item' => ('Edit Scenario'),
            'search_items' => ('Search Scenarios'),
            'view_item' => ('View Scenario'),
        );
    
        $args = array(
            'label' => __('Scenarios', 'scenarios'),
            'labels' => $labels,
            'public' => true,
            'taxonomies' => array('category'),
            'show_in_rest' => true,
            'supports' => array('editor')
        );
    
        register_post_type('scenario',$args);
    }
}