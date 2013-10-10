<?php

/**
 * JSON Adapter for Survey module
 * @copyright   Comvation AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  core_json
 */

namespace Cx\modules\survey\controllers;
use \Cx\Core\Json\JsonAdapter;

/**
 * JSON Adapter for Survey module
 * @copyright   Comvation AG
 * @author      ss4u <ss4ugroup@gmail.com>
 * @package     contrexx
 * @subpackage  core_json
 */
class JsonSurvey implements JsonAdapter {
    /**
     * List of messages
     * @var Array 
     */
    private $messages = array();
    
    /**
     * Returns the internal name used as identifier for this adapter
     * @return String Name of this adapter
     */
    public function getName() {
        return 'survey';
    }
    
    /**
     * Returns an array of method names accessable from a JSON request
     * @return array List of method names
     */
    public function getAccessableMethods() {
        return array('modifyQuestions');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }
    
    public function modifyQuestions() {
        global $objDatabase;
        
        //$surveyId       = contrexx_input2raw($_REQUEST['surveyId']);
        $surveyId       = 1;
        $questionType   = contrexx_input2raw($_POST['questionType']);
        $columnChoices  = contrexx_input2raw($_POST['ColumnChoices']);
        $questionAnswers= contrexx_input2raw($_POST['QuestionAnswers']);

        $vote = 0;
        $Question = ($questionType != 7)?contrexx_input2db($_POST['Question']):contrexx_input2db($_POST['QuestionRow']);

        if(($questionType == 3) || ($questionType == 4)) {
            $options       = explode ("\n", $columnChoices);
            $ColChoices    = explode ("\n", $questionAnswers);
            $colChoic      = implode($ColChoices,";");
            $vote = array();
            foreach ($ColChoices as $key => $value) {
                $vote[$key] = 0;
            }
            $vote = json_encode($vote);
        }else {
            $options       = explode ("\n", $questionAnswers);
            $ColChoices    = explode ("\n", $columnChoices);
            $colChoic      = "";
        }
        if($questionType == 5)
            $options[0] = "Answer";
            $commentable= contrexx_input2db($_POST['Iscomment']);
        if($questionType ==7) {
            $options[0] = "Answer";
            $commentable= contrexx_input2db($_POST['Iscomment']);
        }

        $sorting_id = 0;
        $objResult = $objDatabase->Execute('SELECT MAX(`pos`) as `pos` FROM `'.DBPREFIX.'module_survey_surveyQuestions` WHERE `survey_id` ='.$surveyId);
        $sorting_id = $objResult->fields['pos'] + 1;

        // Insert Query for Inserting the Fields Posted
        $insertSurvey = 'INSERT INTO `'.DBPREFIX.'module_survey_surveyQuestions`
                        SET `survey_id` = "'.contrexx_raw2db($surveyId).'",
                            `isCommentable` = "'.$commentable.'",
                            `QuestionType` = "'.contrexx_raw2db($questionType).'",
                            `Question` = "'.$Question.'",
                            `pos` = '.$sorting_id.',
                            `column_choice` = "'.contrexx_raw2db($colChoic).'" ';
        $objDatabase->Execute($insertSurvey);
        $lastId = mysql_insert_id();
        for ($i=0;$i<count($options);$i++) {
            if(trim($options[$i]) != "") {
                $insertSurvey = 'INSERT INTO `'.DBPREFIX.'module_survey_surveyAnswers`
                                        SET  `question_id` = "'.$lastId.'",
                                            `answer` = "'.contrexx_raw2db($options[$i]).'",
                                            `votes` = "'.contrexx_raw2db($vote).'"';
                $objDatabase->Execute($insertSurvey);
            }
        }
        // loop for inserting the column choices
        for ($i=0;$i<count($ColChoices);$i++) {
            if($ColChoices[$i] != "") {
                $insertSurvey = 'INSERT INTO `'.DBPREFIX.'module_survey_columnChoices`
                                        SET `question_id` = "'.$lastId.'",
                                            `choice` = "'.contrexx_raw2db($ColChoices[$i]).'"';
                $objDatabase->Execute($insertSurvey);
            }
        }
    }
}

