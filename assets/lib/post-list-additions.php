<?php
/**
 * Add selected custom post meta to Exemplar admin post list.
 */

/**
 * Add columns to admin post list
 */
function gcpc_exemplar_columns($defaults) {
    $defaults['comp_num'] = 'Competency Number';
    $defaults['task_num'] = 'Scenario ID';
    $defaults['sub_num'] = 'Participant ID';
    return $defaults;
}
add_filter('manage_exemplar_posts_columns','gcpc_exemplar_columns');

/**
 * Populate columns
 */
function gcpc_populate_columns($column_title, $post_id) {;
    echo get_post_meta($post_id,$column_title)[0];
}
add_action('manage_exemplar_posts_custom_column','gcpc_populate_columns', 10, 2);
