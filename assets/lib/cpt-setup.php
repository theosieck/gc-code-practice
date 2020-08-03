<?php
/*
 * Sets up the Exemplar, Competency, and Scenario CPTs.
 */
namespace GC\Prac\Custom;

add_action('init',__NAMESPACE__ . '\gcpc_register_cpt_exemplar');
/*
 * Adds the "Exemplar" custom post type
 */
function gcpc_register_cpt_exemplar() {
    $labels = array(
        'name' => _x('Exemplars','exemplars'),
        'singular_name' => _x('Exemplar','exemplar'),
        'all_items' => ('All Exemplars'),
        'add_new_item' => ('Add New Exemplar'),
        'edit_item' => ('Edit Exemplar'),
        'search_items' => ('Search Exemplars'),
        'view_item' => ('View Exemplar'),
    );
    $args = array(
        'label' => __('Exemplars', 'exemplars'),
        'labels' => $labels,
        'public' => true,
        'taxonomies' => array('category'),
        'show_in_rest' => true,
        'supports' => array('editor', 'title')
    );
    register_post_type('exemplar',$args);
}

add_action('init',__NAMESPACE__ . '\gcpc_register_cpt_competency');
/*
 * Adds the "Competency" custom post type
 */
function gcpc_register_cpt_competency() {
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
            'supports' => array('editor', 'title')
        );

        register_post_type('competency',$args);
    }
}

add_action('init',__NAMESPACE__ . '\gcpc_register_cpt_scenario');
/*
 * Adds the "Scenario" custom post type
 */
function gcpc_register_cpt_scenario() {
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
            'supports' => array('editor', 'title')
        );

        register_post_type('scenario',$args);
    }
}

add_action('init',__NAMESPACE__ . '\gcpc_register_cpt_code');
/*
 * Adds the "Code" custom post type
 */
function gcpc_register_cpt_code() {
    $labels = array(
        'name' => _x('Codes','codes'),
        'singular_name' => _x('Code','code'),
        'all_items' => ('All Codes'),
        'add_new_item' => ('Add New Code'),
        'edit_item' => ('Edit Code'),
        'search_items' => ('Search Codes'),
        'view_item' => ('View Code'),
    );

    $args = array(
        'label' => __('Codes', 'codes'),
        'labels' => $labels,
        'public' => true,
        'taxonomies' => array('category'),
        'show_in_rest' => true,
        'supports' => array('title','editor')
    );

    register_post_type('code',$args);
}
