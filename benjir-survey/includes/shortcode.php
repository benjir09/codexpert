<?php
    global $wpdb;
    $survey_table = $wpdb->prefix . 'dynamic_survey';
    $survey_data = $wpdb->get_results( "SELECT * FROM $survey_table WHERE id=$survey_id");
    if ( $survey_data ) {
        $survey_name = $survey_data[0]->survey_name;
        $question_ids = $survey_data[0]->question_ids;
        $question_ids = explode(",",$question_ids);
        //var_dump($question_ids);
        ?>
        
        <div class="survey-page">
            <h3>Survey Name: <strong><?php echo esc_html($survey_name); ?></strong></h3>
            <div class="survey-container">

                <form action="">

                    <?php
                        $question_table = $wpdb->prefix . 'dynamic_survey_questions';
                        $question_no = 0;
                        foreach ( $question_ids as $question_id ) {
                            $question_no++;
                            $question_data = $wpdb->get_results( "SELECT * FROM $question_table WHERE id=$question_id");
                            $question_answers = $question_data[0]->question_answers;
                            $question_answers = explode(",",$question_answers);

                            if($question_data) {
                                ?>

                                <div class="each-question">
                                    <h4>Question No <?php echo esc_html($question_no); ?>: <?php echo esc_html($question_data[0]->question_name); ?></h4>
                                    <?php
                                        if ( $question_data[0]->question_type == 'selectbox' ) { ?>
                                            <select name="survey_selectbox" id="survey-selectbox" class="selec2plug">
                                                <?php foreach ( $question_answers as $question_answer ) { ?>
                                                    <option value="<?php echo esc_html($question_answer); ?>"><?php echo esc_html($question_answer); ?></option>
                                                <?php } ?>
                                            </select><br><br>
                                        <?php } else if ( $question_data[0]->question_type == 'radio' ) { ?>
                                            <?php foreach ( $question_answers as $question_answer ) { ?>
                                                <input type="radio" id="<?php echo esc_html($question_answer); ?><?php echo esc_html($question_id); ?>" name="survey_radio" value="<?php echo esc_html($question_answer); ?>">
                                                <label for="<?php echo esc_html($question_answer); ?><?php echo esc_html($question_id); ?>"><?php echo esc_html($question_answer); ?></label>
                                            <?php } ?>
                                        <?php } else if ( $question_data[0]->question_type == 'checkbox' ) { ?>
                                            <?php foreach ( $question_answers as $question_answer ) { ?>
                                                <input type="checkbox" id="<?php echo esc_html($question_answer); ?><?php echo esc_html($question_id); ?>" name="survey_checkbox" value="<?php echo esc_html($question_answer); ?>">
                                                    <label for="<?php echo esc_html($question_answer); ?><?php echo esc_html($question_id); ?>"> <?php echo esc_html($question_answer); ?></label><br>
                                            <?php } ?>
                                    <?php }
                                    ?>
                                </div>
                                
                            <?php
                            }
                        }
                    ?>
                    <input type="button" class="save-data" value="Submit Survey" onclick="submit_survey()">
                </form>

            </div>
        </div>

        <?php 
        //var_dump($survey_data);
    }
?>