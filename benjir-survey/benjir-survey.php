<?php 
/*
Plugin Name: Dynamic Survey by Benjir
Plugin URI: #
Description: Online survey on dynamic questions
Version: 1.0
Author: Benjir Hasan
Author URI: https://www.linkedin.com/in/benjir/
License: GPLv2 or later
Text Domain: benjir-survey
*/

if ( ! defined( 'ABSPATH' ) ){
    exit;
}

class Benjir_Survey_shortcode {

    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'benjir_survey_iframe_css_js']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_benjir_survey_iframe_css_js']);
        add_action( 'admin_menu', [$this, 'benjir_survey_add_survey_page'] );
        add_shortcode( 'dynamic_survey', [$this, 'benjir_dynamic_survey_shortcode'] );
        register_activation_hook(__FILE__, [$this, 'benjir_survey_database_table']);
        // register_deactivation_hook(__FILE__, [$this, 'benjir_survey_delete_table']);
        add_action('init', [$this, 'benjir_survey_ajax_func']);
    }

    public function benjir_survey_ajax_func() {
        add_action('wp_ajax_benjir_survey_save_question', [$this, 'benjir_survey_save_question']);
        add_action('wp_ajax_nopriv_benjir_survey_save_question', [$this, 'benjir_survey_save_question']);
        add_action('wp_ajax_benjir_survey_delete_question', [$this, 'benjir_survey_delete_question']);
        add_action('wp_ajax_nopriv_benjir_survey_delete_question', [$this, 'benjir_survey_delete_question']);
        add_action('wp_ajax_benjir_survey_save_survey', [$this, 'benjir_survey_save_survey']);
        add_action('wp_ajax_nopriv_benjir_survey_save_survey', [$this, 'benjir_survey_save_survey']);
        add_action('wp_ajax_benjir_survey_delete_survey', [$this, 'benjir_survey_delete_survey']);
        add_action('wp_ajax_nopriv_benjir_survey_delete_survey', [$this, 'benjir_survey_delete_survey']);
    }

    public function benjir_survey_iframe_css_js() {
        wp_enqueue_style( 'iframecss', plugins_url( 'benjir-survey.css', __FILE__ ) );
        wp_enqueue_script( 'iframejs', plugins_url( 'benjir-survey.js', __FILE__ ), array( 'jquery' ),time() );

        wp_enqueue_style( 'modalcss', plugins_url( 'assets/jquery.modal.min.css', __FILE__ ) );
        wp_enqueue_script( 'modaljs', plugins_url( 'assets/jquery.modal.min.js', __FILE__ ), array( 'jquery' ) );

        wp_enqueue_style( 'select2css', plugins_url( 'assets/select2.min.css', __FILE__ ) );
        wp_enqueue_script( 'select2js', plugins_url( 'assets/select2.min.js', __FILE__ ), array( 'jquery' ) );

        wp_enqueue_style( 'datatablecss', plugins_url( 'assets/dataTables.dataTables.min.css', __FILE__ ) );
        wp_enqueue_script( 'datatablejs', plugins_url( 'assets/dataTables.min.js', __FILE__ ), array( 'jquery' ) );
        wp_localize_script( 'iframejs', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));
    }

    public function frontend_benjir_survey_iframe_css_js() {

        wp_enqueue_style( 'shortcodecss', plugins_url( 'includes/shortcode.css', __FILE__ ) );
        wp_enqueue_script( 'shortcodejs', plugins_url( 'includes/shortcode.js', __FILE__ ), array( 'jquery' ),time() );
    }

    public function benjir_dynamic_survey_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'id' => 1
            ),
            $atts
        );
        ob_start();

        $survey_id = $atts['id'];
        include( plugin_dir_path( __FILE__ ) . 'includes/shortcode.php');
        ?>

        <?php
        return ob_get_clean();
    }

    public function benjir_survey_database_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'dynamic_survey';
        $sql = "CREATE TABLE `$table_name` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `survey_name` VARCHAR(100) DEFAULT NULL,
            `question_ids` VARCHAR(200) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
      
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
          dbDelta($sql);
        }


        $table_name_questions = $wpdb->prefix . 'dynamic_survey_questions';
        $sql2 = "CREATE TABLE `$table_name_questions` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `question_name` VARCHAR(200) DEFAULT NULL,
            `question_type` VARCHAR(100) DEFAULT NULL,
            `question_answers` VARCHAR(500) DEFAULT NULL,
            `correct_answers` INT DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name_questions'") != $table_name_questions) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql2);
        }
    }

    // public function benjir_survey_delete_table() {
    //     global $wpdb;
    //     $table_name = $wpdb->prefix . 'dynamic_survey';
    //     $wpdb->query("DROP TABLE IF EXISTS $table_name");
    // }

    public function benjir_survey_add_survey_page() {
        add_submenu_page(
            'tools.php',
            __( 'Dynamic Survey', 'benjir-survey' ),
            'Dynamic Survey',
            'manage_options',
            'benjir-dynamic-survey',
            [$this, 'benjir_survey_mainpage'],
            'dashicons-shortcode'
        );
    }

    public function benjir_survey_mainpage() { ?>
        <div class="wrap crypto-wrap">
            <h1 class="wp-heading-inline">Dynamic Survey</h1>

            <div class="survey-and-question">
                <div class="survey-portion">
                    <h3>All Surveys</h3>
                    <a href="#survey-form" id="add_survey" class="page-title-action" rel="modal:open">Add new survey</a>

                    <table id="survey-table" class="display wp-list-table widefat survey-table">
                        <thead>
                            <tr>
                                <th>Servey Id</th>
                                <th>Servey Name</th>
                                <th>Question ID's</th>
                                <th>Shortcode</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                global $wpdb;
                                $survey_table = $wpdb->prefix . 'dynamic_survey';
                                $surveys = $wpdb->get_results( "SELECT * FROM $survey_table");
                                //$static_id = 0;
                                if ( $surveys ) {
                                    foreach ( $surveys as $survey ) {
                                        //$static_id++;
                                        $survey_id = $survey->id;
                                        $survey_name = $survey->survey_name;
                                        $question_ids = $survey->question_ids;
                                        ?>
                                        
                                        <tr>
                                            <td id="survey-id"><?php echo esc_html($survey_id); ?></td>
                                            <td><?php echo esc_html($survey_name); ?></td>
                                            <td><?php echo esc_html($question_ids); ?></td>
                                            <td>[dynamic_survey id="<?php echo esc_html($survey_id); ?>"]</td>
                                            <td>
                                                <span id="delete-survey" class="dashicons dashicons-trash" onclick="delete_survey('<?php echo esc_attr($survey_id); ?>')"></span>
                                            </td>
                                        </tr>

                                    <?php }
                                    
                                }
                            ?>
                            
                        </tbody>
                    </table>

                    <form id="survey-form" class="modal survey-form" action="" method="POST">

                        <h2 id="cmodal-title">Create Survey</h2>

                        <div class="each-questionadmin">
                            <label for="survey-name">Survey name here..</label><br>
                            <input type="text" id="survey-name" name="survey_name" value="" placeholder="HTML Test"><br>

                            <div class="selectquestions">
                                <h4>Select Questions</h4>
                                <?php
                                    $question_table_forsurvey = $wpdb->prefix . 'dynamic_survey_questions';
                                    $questions_forsurvey = $wpdb->get_results( "SELECT * FROM $question_table_forsurvey");
                                    if ( $questions_forsurvey ) {
                                        foreach ( $questions_forsurvey as $question_forsurvey ) {
                                            $question_id_forsurvey = $question_forsurvey->id;
                                            $question_name_forsurvey = $question_forsurvey->question_name;
                                            ?>

                                            <input type="checkbox" id="question-<?php echo esc_html($question_id_forsurvey); ?>" name="checkquestion" value="<?php echo esc_html($question_id_forsurvey); ?>">
                                            <label for="question-<?php echo esc_html($question_id_forsurvey); ?>"> <?php echo esc_html($question_name_forsurvey); ?></label><br>

                                        <?php }
                                        
                                    }
                                ?>
                            </div>

                        </div>
                        <?php wp_nonce_field('save_edit_delete', 'name_of_your_nonce_field'); ?>
                        <input type="button" class="save-data" value="Save" onclick="save_survey()">

                    </form>

                </div>
                <div class="question-portion">
                    <h3>All Questions</h3>
                    <a href="#question-form" id="add_question" class="page-title-action" rel="modal:open">Add new question</a>

                    <table id="questions-table" class="display wp-list-table widefat questions-table">
                        <thead>
                            <tr>
                                <th>Question Id</th>
                                <th>Question Name</th>
                                <th>Question Type</th>
                                <th>Question Answers</th>
                                <th>Correct answer</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $question_table = $wpdb->prefix . 'dynamic_survey_questions';
                                $questions = $wpdb->get_results( "SELECT * FROM $question_table");
                                //$static_id = 0;
                                if ( $questions ) {
                                    foreach ( $questions as $question ) {
                                        //$static_id++;
                                        $question_id = $question->id;
                                        $question_name = $question->question_name;
                                        $question_type = $question->question_type;
                                        $question_answers = $question->question_answers;
                                        $correct_answers = $question->correct_answers;
                                        ?>
                                        
                                        <tr>
                                            <td id="question-id"><?php echo esc_html($question_id); ?></td>
                                            <td><?php echo esc_html($question_name); ?></td>
                                            <td><?php echo esc_html($question_type); ?></td>
                                            <td><?php echo esc_html($question_answers); ?></td>
                                            <td><?php echo esc_html($correct_answers); ?></td>
                                            <td><span id="delete-question" class="dashicons dashicons-trash" onclick="delete_question('<?php echo esc_attr($question_id); ?>')"></span></td>
                                        </tr>

                                    <?php }
                                    
                                }
                            ?>
                            
                        </tbody>
                    </table>

                    <form id="question-form" class="modal question-form" action="" method="POST">

                        <h2 id="cmodal-title">Create Question</h2>

                        <div class="each-questionadmin">
                            <label for="question-name">Question Title here..</label><br>
                            <input type="text" id="question-name" name="question_name" value="" placeholder="What’s your favorite color?" required><br><br>

                            <label for="question-type">Select Question Type</label><br>
                            <select name="question_type" id="question-type" class="selec2plug">
                                <option value="radio">Radio</option>
                                <option value="selectbox">Selectbox</option>
                                <option value="checkbox">Checkbox</option>
                            </select><br><br>
                            <label for="question-total-answer">Select Question Answers fields</label><br>
                            <select name="question_total_answer" id="question-total-answer" class="selec2plug" onchange="getqans(this);">
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                            </select><br><br>
                            <div id="question-answers">
                                <input type="text" id="ans-one" name="ans_one" value="" placeholder="Answer one"><br>
                                <input type="text" id="ans-two" name="ans_two" value="" placeholder="Answer two"><br>
                            </div>
                            <label for="correct-ans">Correct answer: </label><br>
                            <select name="correct_ans" id="correct-ans" class="selec2plug">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                        <?php wp_nonce_field('save_edit_delete', 'name_of_your_nonce_field'); ?>
                        <input type="button" class="save-data" value="Save" onclick="save_question()">

                    </form>

                </div>
            </div>
            
            <div style="display: none;" id="totalshortcode"><?php echo esc_attr( $static_id ); ?></div>

        </div>
        
    <?php }

    public function benjir_survey_save_question() {
        // nonce verify
        if ( isset($_POST['save_nonce']) ) {
            if(wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['save_nonce'])), 'save_edit_delete')) {

                global $wpdb;
                $table_name = $wpdb->prefix . 'dynamic_survey_questions';

                $answers = array();

                if(isset($_POST['question_name'])) {
                    $question_name = sanitize_text_field(wp_unslash($_POST['question_name']));
                }
    
                if(isset($_POST['question_type'])) {
                    $question_type = sanitize_text_field(wp_unslash($_POST['question_type']));
                }
    
                if(isset($_POST['question_type'])) {
                    $question_total_answer = sanitize_text_field(wp_unslash($_POST['question_type']));
                }
    
                if(isset($_POST['ans_one'])) {
                    $ans_one = sanitize_text_field(wp_unslash($_POST['ans_one']));
                    array_push($answers, $ans_one);
                }
    
                if(isset($_POST['ans_two'])) {
                    $ans_two = sanitize_text_field(wp_unslash($_POST['ans_two']));
                    array_push($answers, $ans_two);
                }
    
                if(isset($_POST['ans_three'])) {
                    $ans_three = sanitize_text_field(wp_unslash($_POST['ans_three']));
                    array_push($answers, $ans_three);
                }

                if(isset($_POST['correct_ans'])) {
                    $correct_ans = sanitize_text_field(wp_unslash($_POST['correct_ans']));
                }

                if(isset($_POST['ans_four'])) {
                    $ans_four = sanitize_text_field(wp_unslash($_POST['ans_four']));
                    array_push($answers, $ans_four);
                }

                
                // $answers = serialize($answers);
                $answers = implode(",",$answers);
                
    
                $question_data = array(
                    'question_name' => $question_name,
                    'question_type' => $question_type,
                    'question_answers' => $answers,
                    'correct_answers' => $correct_ans
                );
                $format = array( '%s', '%s', '%s', '%d' );
                $inserted = $wpdb->insert( $table_name, $question_data, $format );
    
                echo wp_json_encode(['status'=>'ok', 'message' => 'Saved', 'data' => $inserted ]);
            } else {
                echo wp_json_encode(['status'=>'not-ok', 'message' => 'Nonce not verified', 'data' => 0 ]);
            }
        }
        
        exit();
    }


    public function benjir_survey_delete_question() {

        if ( isset($_POST['delete_nonce']) ) {
            if(wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['delete_nonce'])), 'save_edit_delete')) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'dynamic_survey_questions';
                
                if ( isset( $_POST['to_delete']) ) {
                    $to_delete = sanitize_text_field(wp_unslash($_POST['to_delete']));
                }
                $deleted = $wpdb->delete( $table_name, array( 'id' => $to_delete ) );
    
                echo wp_json_encode(['status'=>'ok', 'message' => 'Deleted', 'data' => $deleted ]);
            } else {
                echo wp_json_encode(['status'=>'not-ok', 'message' => 'Nonce not verified', 'data' => 0 ]);
            }
        }
        
        exit();
    }

    public function benjir_survey_save_survey() {
        // nonce verify
        if ( isset($_POST['save_nonce']) ) {
            if(wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['save_nonce'])), 'save_edit_delete')) {

                global $wpdb;
                $table_name = $wpdb->prefix . 'dynamic_survey';

                // $answers = array();

                if(isset($_POST['survey_name'])) {
                    $survey_name = sanitize_text_field(wp_unslash($_POST['survey_name']));
                }
    
                if(isset($_POST['allquestions'])) {
                    $allquestions = sanitize_text_field(wp_unslash($_POST['allquestions']));
                }

                
                // $answers = serialize($answers);
                // $allquestions = implode(",",$allquestions);
                
    
                $question_data = array(
                    'survey_name' => $survey_name,
                    'question_ids' => $allquestions
                );
                $format = array( '%s', '%s');
                $inserted = $wpdb->insert( $table_name, $question_data, $format );
    
                echo wp_json_encode(['status'=>'ok', 'message' => 'Saved', 'data' => $inserted ]);
            } else {
                echo wp_json_encode(['status'=>'not-ok', 'message' => 'Nonce not verified', 'data' => 0 ]);
            }
        }
        
        exit();
    }

    public function benjir_survey_delete_survey() {

        if ( isset($_POST['delete_nonce']) ) {
            if(wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['delete_nonce'])), 'save_edit_delete')) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'dynamic_survey';
                
                if ( isset( $_POST['to_delete']) ) {
                    $to_delete = sanitize_text_field(wp_unslash($_POST['to_delete']));
                }
                $deleted = $wpdb->delete( $table_name, array( 'id' => $to_delete ) );
    
                echo wp_json_encode(['status'=>'ok', 'message' => 'Deleted', 'data' => $deleted ]);
            } else {
                echo wp_json_encode(['status'=>'not-ok', 'message' => 'Nonce not verified', 'data' => 0 ]);
            }
        }
        
        exit();
    }
        
}

new Benjir_Survey_shortcode();