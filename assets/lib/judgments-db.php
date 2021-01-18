<?php
/*
 * The base db functions for creating and interacting with the judgments db table, as well as the cpts.
 */
global $db_version;
$db_version = '1.0';

global $prac_table_postfix;
$prac_table_postfix = 'gc_prac_code';

// this function is called in the main plugin file, because otherwise it doesn't work.
/*
 * Creates the table "wp_gc_prac_code" in the database.
 */
function gcpc_create_table() {
    global $wpdb;
    global $db_version;
    global $prac_table_postfix;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $arc_table_name = $wpdb->prefix . $prac_table_postfix;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $arc_table_name (
        judg_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) UNSIGNED NOT NULL,
        sub_num smallint(5) UNSIGNED NOT NULL,
				comp_num smallint(2) UNSIGNED NOT NULL,
				task_num smallint(2) UNSIGNED NOT NULL,
				exp_title tinytext NOT NULL,
				judg_time time NOT NULL,
        code_scheme float unsigned NOT NULL,
        code1 smallint(1) UNSIGNED,
        code2 smallint(1) UNSIGNED,
        code3 smallint(1) UNSIGNED,
        code4 smallint(1) UNSIGNED,
        code5 smallint(1) UNSIGNED,
        code6 smallint(1) UNSIGNED,
        code7 smallint(1) UNSIGNED,
        code8 smallint(1) UNSIGNED,
        code9 smallint(1) UNSIGNED,
				code10 smallint(1) UNSIGNED,
				code11 smallint(1) UNSIGNED,
        code12 smallint(1) UNSIGNED,
        code13 smallint(1) UNSIGNED,
        code14 smallint(1) UNSIGNED,
        code15 smallint(1) UNSIGNED,
        excerpt1 longtext,
        excerpt2 longtext,
        excerpt3 longtext,
        excerpt4 longtext,
        excerpt5 longtext,
        excerpt6 longtext,
        excerpt7 longtext,
        excerpt8 longtext,
        excerpt9 longtext,
				excerpt10 longtext,
				excerpt11 longtext,
        excerpt12 longtext,
        excerpt13 longtext,
        excerpt14 longtext,
        excerpt15 longtext,
				correct_codes longtext,
				missed_codes longtext,
				false_positives longtext,
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
function gcpc_pull_data_cpts($comp_num, $task_num) {
		global $gc_project;
    global $current_user;
    global $wpdb;
    $db = new PracJudgDB;
		$judgments_table = $db->get_name();

		// set up meta query
		$meta_query = array(
				// 'relation' => 'AND',
				// array(
				// 		'key' => 'comp_num',
				// 		'value' => $comp_num,
				// 		'compare' => '=',
				// ),
				// array(
				// 	'relation' => 'AND',
					array(
						'key' => 'task_num',
						'value' => $task_num,
						'compare' => '=',
					),
				// ),
			);

		$exp_args = array(
			'numberposts' => -1,
			'post_type' => 'exemplar',
			'meta_query' => $meta_query
		);
    $all_exemplars = get_posts($exp_args);

    $exemplars = [];
    $total = 0;
    $for_assessment = 0;
    foreach($all_exemplars as $exemplar) {
        $total++;
        $exemplars[] = $exemplar;
    }

    shuffle($exemplars);
    foreach ($exemplars as $exemplar) {
        $exp_id = $exemplar->ID;
        $exp_ids[] = $exp_id;
        $sub_nums[] = $sub_num ? $sub_num : get_field('sub_num', $exp_id);
        $exp_contents[$exp_id] = trim($exemplar->post_content, '""');
				$gold_codes[$exp_id] = get_field('gold_codes',$exp_id);
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
        'nonce' => wp_create_nonce('gcpc_scores_nonce'),
        'sContent' => $s_content,
        'sTitle' => $s_title,
        'cDefinitions' => $c_defs,
        'cTitles' => $c_titles,
        'expIds' => $exp_ids,
        'exemplars' => $exp_contents,
				'goldCodes' => $gold_codes,
        'subNums' => $sub_nums,
        'codeLabels' => $code_labels,
        'numCodes' => $num_codes,
        'codeScheme' => $code_scheme
    );

    return $data_for_js;
}

/*
 * The class which defines the generic functions for working with the database
 */
class PracJudgDB {
    static $primary_key = 'id';

    // Private methods
    /*
     * Returns the name of the table
     */
    private static function _table() {
        global $wpdb;
        global $prac_table_postfix;
        return $wpdb->prefix . $prac_table_postfix;
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
