<?php
/*
 * The base db functions for creating and interacting with the judgments db table, as well as the cpts.
 */
global $db_version;
$db_version = '1.0';

global $code_table_postfix;
$code_table_postfix = 'gc_apply_codes';

// this function is called in the main plugin file, because otherwise it doesn't work.
/*
 * Creates the table "wp_gc_apply_codes" in the database.
 */
function gcac_create_table() {
    global $wpdb;
    global $db_version;
    global $code_table_postfix;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $arc_table_name = $wpdb->prefix . $code_table_postfix;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $arc_table_name (
        judg_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) UNSIGNED NOT NULL,
				project tinytext NOT NULL,
        sub_num smallint(5) UNSIGNED NOT NULL,
				comp_num smallint(2) UNSIGNED NOT NULL,
				task_num smallint(2) UNSIGNED NOT NULL,
				resp_title tinytext NOT NULL,
        judg_type tinytext NOT NULL,
				judg_time time NOT NULL,
        code_scheme float unsigned NOT NULL,
        code1 smallint(1) UNSIGNED NOT NULL,
        code2 smallint(1) UNSIGNED NOT NULL,
        code3 smallint(1) UNSIGNED NOT NULL,
        code4 smallint(1) UNSIGNED NOT NULL,
        code5 smallint(1) UNSIGNED NOT NULL,
        code6 smallint(1) UNSIGNED NOT NULL,
        code7 smallint(1) UNSIGNED NOT NULL,
        code8 smallint(1) UNSIGNED,
        code9 smallint(1) UNSIGNED,
        excerpt1 longtext,
        excerpt2 longtext,
        excerpt3 longtext,
        excerpt4 longtext,
        excerpt5 longtext,
        excerpt6 longtext,
        excerpt7 longtext,
        excerpt8 longtext,
        excerpt9 longtext,
        judg_comments longtext,
        rater1 mediumint(9) UNSIGNED,
        rater2 mediumint(9) UNSIGNED,
        PRIMARY KEY (judg_id)
	) $charset_collate;";

    dbDelta($sql);
    $success = empty( $wpdb->last_error );
    update_option($arc_table_name . '_db_version',$db_version);
    return $success;
}

/*
 * Pulls relevant data from the CPTs using given $comp_num, $task_num.
 */
function arc_pull_data_cpts($comp_num, $task_num, $sub_num) {
    global $current_user;
    global $wpdb;
    $db = new ARCJudgDB;
		$judgments_table = $db->get_name();

		$results_obj = NULL;
		// check whether we want one post or all of them
		if($sub_num) {
			$sql = "SELECT * FROM `{$judgments_table}` WHERE `sub_num` = {$sub_num} AND `comp_num` = {$comp_num} AND `task_num` = {$task_num} AND `judg_type` = 'ind'";
			$results = $wpdb->get_results($sql);
			$results_obj = $results[count($results)-1];
			$meta_query = array(
					'relation' => 'AND',
					array(
							'key' => 'comp_num',
							'value' => $comp_num,
							'compare' => '=',
					),
					array(
						'relation' => 'AND',
						array(
								'key' => 'task_num',
								'value' => $task_num,
								'compare' => '=',
						),
						array(
							'key' => 'sub_num',
							'value' => $sub_num,
							'compare' => '=',
						),
					),
				);
		} else {
			$meta_query = array(
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
        );
		}

		$resp_args = array(
			'numberposts' => -1,
			'post_type' => 'exemplar',
			'meta_query' => $meta_query
		);
    $all_responses = get_posts($resp_args);
    $responses = [];
    $total = 0;
    $for_assessment = 0;
    foreach($all_responses as $response) {
        $total++;
        $responses[] = $response;
    }
    // if(empty($responses)) {
    //     return "You have assessed all the responses for this block.";
    // }
    shuffle($responses);
    foreach ($responses as $response) {
        $resp_id = $response->ID;
        $resp_ids[] = $resp_id;
        $sub_nums[] = $sub_num ? $sub_num : get_field('sub_num', $resp_id);
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
        if($j==0) {
            $code_scheme = get_field('code_scheme',$competency->ID);
        }
        $c_defs[$j] = trim($competency->post_content, '""');
        $c_titles[$j] = $competency->post_title;
    }

    $code_args = array(
        'numberposts' => -1,
        'post_type' => 'code',
        'meta_key' => 'comp_num',
        'meta_value' => $comp_num
    );

    $codes = get_posts($code_args);
    foreach($codes as $code) {
        $j = get_field('code_num',$code->ID);
        $code_labels[$j] = wp_strip_all_tags($code->post_content);
    }
    $num_codes = count($codes);


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
        'codeLabels' => $code_labels,
        'numCodes' => $num_codes,
        'codeScheme' => $code_scheme,
				'resultsObj' => $results_obj
    );

    return $data_for_js;
}

/*
 * Pulls relevant data from the CPTs using given $comp_num, $task_num, user ids.
 */
function arc_pull_review_data_cpts($judge1, $judge2, $comp_num, $task_num) {
    global $current_user;
    global $wpdb;

    $db = new ARCJudgDB;
    // echo 'new echo statement';
    // get all the data for the given comp and task nums
    $where = "comp_num = {$comp_num} AND task_num = {$task_num} AND judg_type = 'ind'";
    $all_data = $db->get_all_arraya($where);
    // organize the data into a multi-dimensional array where the key is the subject number and the value is
    // an array of all the db lines for that subject, indexed by user id
    $all_subs = [];
    foreach ($all_data as $sub) {
        // this only keeps the most recent judgement for the given user id
        $all_subs[$sub['sub_num']][$sub['user_id']] = $sub;
    }
    if(empty($all_subs)) {
        return "Need assessments from both raters";
    }
    // filter the db lines so that the subjects that have not yet been reviewed and have more than one
    // response are added to the review set
    $review_set = [];
    $matches = [];
    foreach($all_subs as $sub) {
        $sub_num = $sub[$judge1]['sub_num']; // get the subject number
        // check if both judges have assessed this subject
        if($sub[$judge1] && $sub[$judge2]) {
            // get the response information for this subject
            $title = $sub[$judge1]['resp_title'];
            $response = get_page_by_title($title, 'OBJECT', 'exemplar');
            $resp_id = $response->ID;
            $resp_ids[] = $resp_id;
            $sub_nums[] = $sub_num;
            $resp_contents[$resp_id] = trim($response->post_content, '""');
            $judge1_comments[$resp_id] = '';
            $judge2_comments[$resp_id] = '';

            // check for comments
            $j1_comment = $sub[$judge1]['judg_comments'];
            if($j1_comment) {
                $judge1_comments[$resp_id] = $j1_comment;
            }
            $j2_comment = $sub[$judge2]['judg_comments'];
            if($j2_comment) {
                $judge2_comments[$resp_id] = $j2_comment;
            }

            // sort codes for this subject into matches and reviews
            for($i=1;$i<10;$i++) {
                $code_num = 'code' . $i;
                // ignore codes that don't exist
                if($sub[$judge1][$code_num]==NULL) {
                break;
                }
                $excerpt_num = 'excerpt' . $i;

                if((intval($sub[$judge1][$code_num])===intval($sub[$judge2][$code_num])) && (intval($sub[$judge1][$code_num])===1)) {
                    $matches[$sub_num][$i] = [$sub[$judge1][$excerpt_num], $sub[$judge2][$excerpt_num]];
                } else if(intval($sub[$judge1][$code_num])===1) {
                    $review_set[$sub_num][$i] = $sub[$judge1][$excerpt_num];
                } else if(intval($sub[$judge2][$code_num])===1) {
                    $review_set[$sub_num][$i] = $sub[$judge2][$excerpt_num];
                }
            }
        } else {
            echo `Need ratings from both judges for subject {$sub_num}.`;
        }
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
        if($j==0) {
            $code_scheme = get_field('code_scheme',$competency->ID);
        }
        $c_defs[$j] = trim($competency->post_content, '""');
        $c_titles[$j] = $competency->post_title;
    }

    $code_args = array(
        'numberposts' => -1,
        'post_type' => 'code',
        'meta_key' => 'comp_num',
        'meta_value' => $comp_num
    );

    $codes = get_posts($code_args);
    foreach($codes as $code) {
        $j = get_field('code_num',$code->ID);
        $code_labels[$j] = wp_strip_all_tags($code->post_content);
    }
    $num_codes = count($codes);

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
        'reviewSet' => $review_set,
        'matches' => $matches,
        'codeLabels' => $code_labels,
        'numCodes' => $num_codes,
        'judges' => [$judge1,$judge2],
        'codeScheme' => $code_scheme,
        'judge1Comments' => $judge1_comments,
        'judge2Comments' => $judge2_comments
    );
    return $data_for_js;
}

/*
 * The class which defines the generic functions for working with the database
 */
class ARCJudgDB {
    static $primary_key = 'id';

    // Private methods
    /*
     * Returns the name of the table
     */
    private static function _table() {
        global $wpdb;
        global $code_table_postfix;
        return $wpdb->prefix . $code_table_postfix;
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
        return $wpdb->insert(self::_table(),$data);
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

    static function get_all_obj($where) {
        global $wpdb;
        $sql   = "SELECT * FROM " . self::_table() . " WHERE {$where}";
        return $wpdb->get_results( $sql );
    }

    static function get_all_arraya($where) {
        global $wpdb;
        $sql   = "SELECT * FROM " . self::_table() . " WHERE {$where}";
        return $wpdb->get_results( $sql, 'ARRAY_A' );
    }

    static function get_all() {
        global $wpdb;
        $sql   = "SELECT * FROM " . self::_table();
        return $wpdb->get_results( $sql, 'ARRAY_A' );
    }


    /*
     * Returns an array of the columns and their formats
     */
    public function get_columns() {
        global $wpdb;
        $sql = "SHOW columns FROM " . self::_table();
        return $wpdb->get_results($sql);
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
