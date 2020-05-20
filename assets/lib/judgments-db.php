<?php
/*
 * The base db functions for creating and interacting with the judgments db table, as well as the cpts.
 */
global $db_version;
$db_version = '1.0';

global $arc_table_postfix;
$arc_table_postfix = 'gc_apply_judgments';

// this function is called in the main plugin file, because otherwise it doesn't work.
/*
 * Creates the table "wp_gc_indep_judgments" in the database.
 */
function gcaa_create_table() {
    global $wpdb;
    global $db_version;
    global $arc_table_postfix;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $arc_table_name = $wpdb->prefix . $arc_table_postfix;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $arc_table_name (
        judg_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) UNSIGNED NOT NULL,
        sub_num smallint(5) UNSIGNED NOT NULL,
		comp_num smallint(2) UNSIGNED NOT NULL,
		task_num smallint(2) UNSIGNED NOT NULL,
		resp_title tinytext NOT NULL,
        judg_type tinytext NOT NULL,
		judg_level smallint(1) UNSIGNED NOT NULL,
		judg_time time NOT NULL,
	    rationale longtext NOT NULL,
        PRIMARY KEY (judg_id)
	) $charset_collate;";

    dbDelta($sql);
    $success = empty( $wpdb->last_error );
    update_option($arc_table_name . '_db_version',$db_version);
    return $success;
}

/*
 * Pulls relevant data from the CPTs using given $comp_num, $task_num, and $block_num.
 */
function arc_pull_data_cpts($comp_num, $task_num, $block_num) {
    global $current_user;
    $resp_args = array(
        'numberposts' => -1,
        'post_type' => 'response',
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'block_num',
                'value' => $block_num,
                'compare' => '=',
            ),
            array(
                'relation' => 'AND',
                array(
                    'key' => 'comp_num',
                    'value' => $comp_num,
                    'compare' => '=',
                ),
                array(
                    'key' => 'task_num',
                    'value' => $task_num,
                    'compare' => '=',
                ),
            )
        )
    );
    $responses = get_posts($resp_args);
    shuffle($responses);
    foreach ($responses as $response) {
        $resp_id = $response->ID;
        $resp_ids[] = $resp_id;
        $sub_nums[] = get_field('sub_num', $resp_id);
        $resp_contents[$resp_id] = trim($response->post_content, '""');
    }

    $s_args = array(
        'post_type' => 'scenario',
        'meta_key' => 'task_num',
        'meta_value' => $task_num
    );

    $scenario = get_posts($s_args);
    $s_content = trim($scenario[0]->post_content, '""');
    $s_title = $scenario[0]->post_title;

    $c_args = array(
        'post_type' => 'competency',
        'meta_key' => 'comp_num',
        'meta_value' => $comp_num
    );

    $competencies = get_posts($c_args);
    foreach ($competencies as $competency) {
        $j = get_field('comp_part',$competency->ID);
        $c_defs[$j] = trim($competency->post_content, '""');
        $c_titles[$j] = $competency->post_title;
    }

    $data_for_js = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gcaa_scores_nonce'),
        'sContent' => $s_content,
        'sTitle' => $s_title,
        'cDefinitions' => $c_defs,
        'cTitles' => $c_titles,
        'respIds' => $resp_ids,
        'responses' => $resp_contents,
        'subNums' => $sub_nums
    );

    return $data_for_js;
}

/*
 * Pulls relevant data from the CPTs using given $comp_num, $task_num, $block_num, user ids.
 */
function arc_pull_review_data_cpts($judge1, $judge2, $comp_num, $task_num, $block_num) {
    global $current_user;
    global $wpdb;
    $db = new arc_judg_db;
    // get all the data for the given comp and task nums
    $where = "comp_num = {$comp_num} AND task_num = {$task_num} AND judg_type = 'ind'";
    $all_data = $db->get_all($where);
    // organize the data into a multi-dimensional array where the key is the subject number and the value is
    // an array of all the db lines for that subject, indexed by user id
    $all_subs = [];
    foreach ($all_data as $sub) {
        // filter by block_num
        if(get_page_by_title($sub->resp_title, 'OBJECT', 'response')->block_num == $block_num) {
            // this only keeps the most recent judgement for the given user id
            $all_subs[$sub->sub_num][$sub->user_id] = $sub;
        }
    }
    if(empty($all_subs)) {
        return "Need assessments from both raters";
    }
    // filter the db lines so that the subjects that have not yet been reviewed and have more than one
    // response are added to the review set
    $review_set = [];
    $review_titles = [];
    foreach($all_subs as $sub) {
        // check that both raters/judges have completed the relevant block of cases
        // -- entire block must be completed by each judge
        if(($sub[$judge1]==NULL) || ($sub[$judge2]==NULL)) {
            // one or both judges have not completed this block
            return "Need assessments from both raters";
        } else {
            // both judges have completed this block
            // only add to the set the pairs of judg_levels that are different
            if($sub[$judge1]->judg_level != $sub[$judge2]->judg_level) {
                $sub_num = $sub[$judge1]->sub_num;
                $review_set[$sub_num] = array(
                    'sub_num' => $sub_num,
                    'judg_level_1' => $sub[$judge1]->judg_level,
                    'judg_level_2' => $sub[$judge2]->judg_level,
                    'rationale_1' => $sub[$judge1]->rationale,
                    'rationale_2' => $sub[$judge2]->rationale
                );
                $response = get_page_by_title($sub[$judge1]->resp_title, 'OBJECT', 'response');
                $resp_id = $response->ID;
                $resp_ids[] = $resp_id;
                $sub_nums[] = $sub_num;
                $resp_contents[$resp_id] = trim($response->post_content, '""');
            } else {   
                // add a 'rev' line to the db for this sub, since both judges agreed
                $db_data = array(
                    'user_id' => $judge1,
                    'sub_num' => $sub[$judge1]->sub_num,
                    'comp_num' => $sub[$judge1]->comp_num,
                    'task_num' => $sub[$judge1]->task_num,
                    'resp_title' => $sub[$judge1]->resp_title,
                    'judg_type' => 'rev',
                    'judg_level' => $sub[$judge1]->judg_level,
                    'judg_time'  => $sub[$judge1]->judg_time,
                    'rationale' => $sub[$judge1]->rationale
                );
                $db->insert($db_data);
            }
        }
    }
    if(empty($review_set)) {
        return "All level ratings for these two judges matched. No disagreements found.";
    }

    $s_args = array(
        'post_type' => 'scenario',
        'meta_key' => 'task_num',
        'meta_value' => $task_num
    );

    $scenario = get_posts($s_args);
    $s_content = trim($scenario[0]->post_content, '""');
    $s_title = $scenario[0]->post_title;

    $c_args = array(
        'post_type' => 'competency',
        'meta_key' => 'comp_num',
        'meta_value' => $comp_num
    );

    $competencies = get_posts($c_args);
    foreach ($competencies as $competency) {
        $j = get_field('comp_part',$competency->ID);
        $c_defs[$j] = trim($competency->post_content, '""');
        $c_titles[$j] = $competency->post_title;
    }

    $data_for_js = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gcaa_scores_nonce'),
        'sContent' => $s_content,
        'sTitle' => $s_title,
        'cDefinitions' => $c_defs,
        'cTitles' => $c_titles,
        'respIds' => $resp_ids,
        'responses' => $resp_contents,
        'subNums' => $sub_nums,
        'reviewSet' => $review_set
    );
    return $data_for_js;
}

/*
 * The class which defines the generic functions for working with the database
 */
class arc_judg_db {
    static $primary_key = 'id';

    // Private methods
    /*
     * Returns the name of the table
     */
    private static function _table() {
        global $wpdb;
        global $arc_table_postfix;
        return $wpdb->prefix . $arc_table_postfix;
    }

    /*
     * Returns the row with the given key
     */
    private static function _fetch_sql($value) {
        global $wpdb;
        $sql = sprintf("SELECT * FROM %s WHERE %s = %%s",self::_table(),static::$primary_key);
        return $wpdb->prepare($sql,$value);
    }

    // Public methods
    /*
     * Returns the table name
     */
    static function get_name() {
        return self::_table();
    }

    /*
     * Returns the row with the given key
     */
    static function get($value) {
        global $wpdb;
        return $wpdb->get_row( self::_fetch_sql( $value ) );
    }

    /*
     * Inserts a row
     */
    static function insert($data) {
        global $wpdb;
        $wpdb->insert(self::_table(),$data);
    }

    /*
     * Updates the specified row
     */
    static function update($data,$where) {
        global $wpdb;
        $wpdb->update(self::_table(),$data,$where);
    }

    /*
     * Deletes the specified row
     */
    static function delete($value) {
        global $wpdb;
        $sql = sprintf('DELETE FROM %s WHERE %s = %%s',self::_table(),static::$primary_key);
        return $wpdb->query($wpdb->prepare($sql,$value));
    }

    /*
     * Retrieves the specified data
     */
    static function fetch($value) {
        global $wpdb;
        $value = intval($value);
        $sql   = "SELECT * FROM " . self::_table() . " WHERE id = {$value}";
        return $wpdb->get_results( $sql );
    }

    static function get_all($where) {
        global $wpdb;
        $sql   = "SELECT * FROM " . self::_table() . " WHERE {$where}";
        return $wpdb->get_results( $sql );
    }

    /*
     * Returns an array of the columns and their formats
     */
    public function get_columns() {
        return array(
            'judg_id' => '%d', 	
            'user_id' => '%d',	
            'sub_num' => '%d',	
            'comp_num' => '%d', 	
            'task_num' => '%d', 	
            'resp_title' => '%s',
            'judg_type' => '%s',
            'judg_level' => '%d', 	
            'judg_time' => '%s', 	
            'rationale' => '%s'
        );
    }

    /*
     * Returns an array with all results from the database with the given parameter
     * If $count is set to true, just returns the number of results
     */
    public function get_judgments($args=array(),$count=false) {
        global $wpdb;
        $defaults = array(
            'learner_id' => 0,
            'trial_num' => 0,
            'comp_num' => 0,
            'task_num' => 0,
            'ex_title' => '',
            'learner_level' => 0,
            'judg_corr' => 0,
            'offset' => 0,
            'order_by' => 'learner_id',
            'order' => 'DESC',
            'number' => PHP_INT_MAX,
        );
        $args = wp_parse_args($args,$defaults);
        $where = '';
        if(!empty($args['learner_id'])) {
            if(is_array($args['learner_id'])) {
                $where .= " trial_num IN ('{$args['learner_id'][0]}'";
                for($i=1;$i<sizeof($args['learner_id']);$i++) {
                    $where .= ", '{$args['learner_id'][$i]}'";
                }
                $where .= ")";
            } else {
                $learner_ids = $args['learner_id'];
                $where .= " learner_id = '{$learner_ids}'";
            }
        }
        if(!empty($args['trial_num'])) {
            if(empty($where)) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }
            if(is_array($args['trial_num'])) {
                $where .= " trial_num IN ('{$args['trial_num'][0]}'";
                for($i=1;$i<sizeof($args['trial_num']);$i++) {
                    $where .= ", '{$args['trial_num'][$i]}'";
                }
                $where .= ")";
            } else {
                $trial_nums = $args['trial_num'];
                $where .= " trial_num = '{$trial_nums}'";
            }
        }
        if(!empty($args['comp_num'])) {
            if(empty($where)) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }
            if(is_array($args['comp_num'])) {
                $where .= " comp_num IN ('{$args['comp_num'][0]}'";
                for($i=1;$i<sizeof($args['comp_num']);$i++) {
                    $where .= ", '{$args['comp_num'][$i]}'";
                }
                $where .= ")";
            } else {
                $comp_nums = $args['comp_num'];
                $where .= " comp_num = '{$comp_nums}'";
            }
        }

        if(!empty($args['task_num'])) {
            if(empty($where)) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }
            if(is_array($args['task_num'])) {
                $where .= " task_num IN ('{$args['task_num'][0]}'";
                for($i=1;$i<sizeof($args['task_num']);$i++) {
                    $where .= ", '{$args['task_num'][$i]}'";
                }
                $where .= ")";
            } else {
                $task_nums = $args['task_num'];
                $where .= " task_num = '{$task_nums}'";
            }
        }

        if(!empty($args['ex_title'])) {
            if(empty($where)) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }
            if(is_array($args['ex_title'])) {
                $where .= " ex_title IN ('{$args['ex_title'][0]}'";
                for($i=1;$i<sizeof($args['ex_title']);$i++) {
                    $where .= ", '{$args['ex_title'][$i]}'";
                }
                $where .= ")";
            } else {
                $ex_titles = $args['ex_title'];
                $where .= " ex_title = '{$ex_titles}'";
            }
        }

        if(!empty($args['learner_level'])) {
            if(empty($where)) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }
            if(is_array($args['learner_level'])) {
                $where .= " learner_level IN ('{$args['learner_level'][0]}'";
                for($i=1;$i<sizeof($args['learner_level']);$i++) {
                    $where .= ", '{$args['learner_level'][$i]}'";
                }
                $where .= ")";
            } else {
                $learner_levels = $args['learner_level'];
                $where .= " learner_level = '{$learner_levels}'";
            }
        }

        if(!empty($args['judg_corr'])) {
            if(empty($where)) {
                $where .= " WHERE";
            } else {
                $where .= " AND";
            }
            if(is_array($args['judg_corr'])) {
                $where .= " judg_corr IN ('{$args['judg_corr'][0]}'";
                for($i=1;$i<sizeof($args['judg_corr']);$i++) {
                    $where .= ", '{$args['judg_corr'][$i]}'";
                }
                $where .= ")";
            } else {
                $judg_corrs = $args['judg_corr'];
                $where .= " judg_corr = '{$judg_corrs}'";
            }
        }

        $args['order_by'] = ! array_key_exists($args['order_by'],$this->get_columns()) ? static::$primary_key :
            $args['order_by'];

        $cache_key = (true === $count) ? md5('judg_count' . serialize($args)) :
            md5('judg_' . serialize($args));

        $results = wp_cache_get($cache_key,'judgments');
        if(false === $results) {
            if(true === $count) {
                $results = absint($wpdb->get_var("SELECT COUNT(" . static::$primary_key . ") FROM ". self::_table() .
                    "{$where};"));
            } else {
                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM " . self::_table() . " {$where} ORDER BY %s %s LIMIT %d,%d;",
                    $args['order_by'], $args['order'], absint($args['offset']), absint($args['number'])
                ));
            }
        }

        wp_cache_set($cache_key,$results,'judgments',3600);
        return $results;
    }
}