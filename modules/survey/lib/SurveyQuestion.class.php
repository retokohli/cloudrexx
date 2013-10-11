<?php

class SurveyQuestion
{
    public $id;
    public $surveyId;    
    public $questionType;
    public $isCommentable;
    public $question;
    public $questionRow;
    public $questionChoice;
    public $questionAnswers;
    public $position;
            
    function get()
    {
        
    }
    
    function save()
    {
        global $objDatabase;
        
        if (empty($this->surveyId)) {
            return false;
        }
        
        $this->question = $this->questionType != 7 ? $this->question : $this->questionRow;
        
        if (in_array($this->questionType, array(3, 4))) {
            $options    = explode ("\n", $this->questionChoice);
            $choices    = explode ("\n", $this->questionAnswers);
            $colChoices = implode($choices, ";");
            $vote       = json_encode($colChoices);
            
            $vote = array();
            foreach ($colChoices as $key => $value) {
                $vote[$key] = 0;
            }
            $vote = json_encode($vote);
        } else {
            $options    = explode ("\n", $this->questionAnswers);
            $choices    = explode ("\n", $this->questionChoice);
            $colChoices = "";
            $vote       = 0;            
        }
        
        if(in_array($this->questionType, array(5, 7))) {
            $options[0] = "Answer";
        }
        
        $arrFields = array(
            'survey_id'     => $this->surveyId,
            'isCommentable' => $this->isCommentable,
            'QuestionType'  => $this->questionType,
            'Question'      => $this->question,
            'pos'           => 0,
            'column_choice' => $colChoices
        );
        
        if (empty($this->id)) {
            $query = SQL::insert('module_survey_surveyQuestions', $arrFields, array('escape' => true));
        } else {
            $query = SQL::update('module_survey_surveyQuestions', $arrFields, array('escape' => true))." WHERE `id` = {$this->id}";
        }
        
        if ($objDatabase->Execute($query)) {
            if (empty($this->id)) {
                $this->id = $objDatabase->INSERT_ID();
            }
        }
        
        if (!empty($options)) {
            foreach (array_filter($options) as $option) {
                $arrFields = array(
                    'question_id' => $this->id,
                    'answer'      => $option,
                    'votes'       => $vote
                );
                $query = SQL::insert('module_survey_surveyAnswers', $arrFields, array('escape' => true));
                $objDatabase->Execute($query);
            }
        }
        
        if (!empty($colChoices)) {
            foreach (array_filter($colChoices) as $choice) {
                $arrFields = array(
                    'question_id' => $this->id,
                    'choice'      => $choice
                );
                $query = SQL::insert('module_survey_columnChoices', $arrFields, array('escape' => true));
                $objDatabase->Execute($query);
            }
        }
         
    }
}
