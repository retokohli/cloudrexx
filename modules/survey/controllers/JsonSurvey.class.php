<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

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
        return array('modifyQuestions', 'getSurveyQuestions', 'getSurveyQuestion', 'deleteQuestion', 'saveSorting');
    }

    /**
     * Returns all messages as string
     * @return String HTML encoded error messages
     */
    public function getMessagesAsString() {
        return implode('<br />', $this->messages);
    }
    
    public function modifyQuestions() {        
        
        $objQuestion = new \SurveyQuestion();
        
        $objQuestion->id              = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        $objQuestion->surveyId        = isset($_GET['surveyId']) ? (int) $_GET['surveyId'] : 0;
        $objQuestion->questionType    = isset($_POST['questionType']) ? (int) $_POST['questionType'] : 0;
        $objQuestion->question        = isset($_POST['Question']) ? contrexx_input2raw($_POST['Question']) : '';
        $objQuestion->questionRow     = isset($_POST['QuestionRow']) ? contrexx_input2raw($_POST['QuestionRow']) : '';
        $objQuestion->questionChoice  = isset($_POST['ColumnChoices']) ? contrexx_input2raw($_POST['ColumnChoices']) : '';
        $objQuestion->questionAnswers = isset($_POST['QuestionAnswers']) ? contrexx_input2raw($_POST['QuestionAnswers']) : '';        
        $objQuestion->isCommentable   = isset($_POST['Iscomment']) ? (int) $_POST['Iscomment'] : 0;
                
        $objQuestion->save();
        
    }
    
    public function getSurveyQuestions() 
    {
        $objQuestionManager = new \SurveyQuestionManager((int) $_GET['surveyId']);
        return $objQuestionManager->showQuestions();
    }
    
    public function getSurveyQuestion()
    {
        $objQuestion = new \SurveyQuestion();
        
        $objQuestion->id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        
        $objQuestion->get();
        
        return array(
            'id'              => (int) $objQuestion->id,
            'surveyId'        => (int) $objQuestion->surveyId,
            'questionType'    => (int) $objQuestion->questionType,
            'question'        => $objQuestion->question,
            'questionRow'     => $objQuestion->questionRow,
            'questionChoice'  => $objQuestion->questionChoice,
            'questionAnswers' => $objQuestion->questionAnswers,
            'isCommentable'   => (int) $objQuestion->isCommentable
        );
        
    }
    
    public function deleteQuestion()
    {
        $objQuestion = new \SurveyQuestion();
        
        $objQuestion->id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        
        $objQuestion->delete();
    }
    
    public function saveSorting()
    {
        $objQuestion = new \SurveyQuestion();
        
        if (!empty($_POST['questions'])) {
            foreach ($_POST['questions'] as $key => $questionId) {
                $objQuestion->id       = (int) $questionId;
                $objQuestion->position = $key;

                $objQuestion->updatePosition();
            }
        }
        
    }
}

