<?php

/**
 * Class SurveyAdmin
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@comvation.com>
 * @version       $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_survey
 * @todo        Edit PHP DocBlocks!
 */

require_once ASCMS_MODULE_PATH.'/survey/lib/surveyLib.class.php';

/**
 * Class SurveyAdmin
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@comvation.com>
 * @version       $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_survey
 * @todo        Edit PHP DocBlocks!
 */
class SurveyAdmin extends SurveyLibrary {

    var $_objTpl;
    var $_strPageTitle    = '';
    var $_strErrMessage = '';
    var $_strOkMessage     = '';


    /**
     * Constructor
     *
     * Create the module-menu and an internal template-object
     * @global     object        $objInit
     * @global    object        $objTemplate
     * @global    array        $_ARRAYLANG
     * @global     array        $_CORELANG
     */
    function __construct()
    {
        global $objInit, $objTemplate, $_ARRAYLANG, $_CORELANG;

        SurveyLibrary::__construct();
        $this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/survey/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
        $this->_intLangId = $objInit->userFrontendLangId;

        $objTemplate->setVariable('CONTENT_NAVIGATION','    <a href="?cmd=survey">'.$_CORELANG['TXT_SURVEY_MENU_OVERVIEW'].'</a>
                                                            <a href="?cmd=survey&amp;act=add">'.$_CORELANG['TXT_SURVEY_MENU_ADD'].'</a>
                                                            <a href="?cmd=survey&amp;act=settings">'.$_CORELANG['TXT_SURVEY_MENU_SETTINGS'].'</a>
                                                    ');
    }


    /**
    * Perform the right operation depending on the $_GET-params
    *
    * @global     object        $objTemplate
    */
    function getPage() {
        global $objTemplate;

        if(!isset($_GET['act'])) {
            $_GET['act']='';
        }

        switch($_GET['act']){
            case 'add':
                Permission::checkAccess(112, 'static');
                $this->addSurvey();
                break;
            case 'insert':
                Permission::checkAccess(112, 'static');
                $this->insertSurvey();
                $this->showOverview();
                break;
            case 'settings':
                Permission::checkAccess(113, 'static');
                $this->showSettings();
                break;
            case 'settings_update':
                Permission::checkAccess(113, 'static');
                $this->saveSettings();
                $this->showSettings();
                break;
            case 'status':
                Permission::checkAccess(111, 'static');
                $this->setStatus($_GET['id']);
                $this->showOverview();
                break;
            case 'delete':
                Permission::checkAccess(114, 'static');
                $this->deleteSurvey($_GET['id']);
                $this->showOverview();
                break;
            case 'multiaction':
                Permission::checkAccess(111, 'static');
                $this->doMultiAction();
                $this->showOverview();
                break;
            case 'edit':
                Permission::checkAccess(111, 'static');
                $this->editSurvey($_GET['id']);
                break;
            default:
                Permission::checkAccess(111, 'static');
                $this->showOverview();
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'                => $this->_strPageTitle,
            'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
            'ADMIN_CONTENT'                => $this->_objTpl->get()
        ));
    }


     /**
     * Show an overview of all existing votings / surveys.
     *
     * @global     array        $_CORELANG
     * @global     array        $_ARRAYLANG
     */
    function showOverview() {
        global $_CORELANG, $_ARRAYLANG;

        $this->_strPageTitle = $_CORELANG['TXT_SURVEY_MENU_OVERVIEW'];
        $this->_objTpl->loadTemplateFile('module_survey_overview.html',true,true);

        $this->_objTpl->setVariable(array(
            'TXT_SUBTITLE_STATUS'            =>    $_CORELANG['TXT_STATUS'],
            'TXT_SUBTITLE_TYPE'                =>    $_CORELANG['TXT_TYPE'],
            'TXT_SUBTITLE_PLACEHOLDER'        =>    $_ARRAYLANG['TXT_SURVEY_PLACEHOLDER'],
            'TXT_SUBTITLE_DATE'                =>    $_CORELANG['TXT_DATE'],
            'TXT_SUBTITLE_NAME'                =>    $_CORELANG['TXT_NAME'],
            'TXT_SUBTITLE_PARTICIPANT'        =>    $_ARRAYLANG['TXT_SURVEY_PARTICIPANT'],
            'TXT_SUBTITLE_PARTICIPANT_LAST'    =>    $_ARRAYLANG['TXT_SURVEY_PARTICIPANT_LAST'],
            'TXT_SUBTITLE_ACTIONS'            =>    $_ARRAYLANG['TXT_SURVEY_ACTIONS'],
            'TXT_JS_DELETE_MSG'                =>    $_ARRAYLANG['TXT_SURVEY_DELETE_JS'],
            'TXT_JS_DELETE_ALL_MSG'            =>    $_ARRAYLANG['TXT_SURVEY_DELETE_ALL_JS'],
            'TXT_BUTTON_SAVESORT'            =>    $_ARRAYLANG['TXT_SAVE'],
            'TXT_SELECT_ALL'                =>    $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL'                =>    $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_SUBMIT_SELECT'                =>    $_ARRAYLANG['TXT_MULTIACTION_SELECT'],
            'TXT_SUBMIT_DELETE'                =>    $_ARRAYLANG['TXT_MULTIACTION_DELETE'],
            'TXT_SUBMIT_ACTIVATE'            =>    $_ARRAYLANG['TXT_MULTIACTION_ACTIVATE'],
            'TXT_SUBMIT_DEACTIVATE'            =>    $_ARRAYLANG['TXT_MULTIACTION_DEACTIVATE'],
        ));

        if (count($this->_arrSurveyValues) > 0) {
            $this->_objTpl->hideBlock('noSurveys');

            foreach ($this->_arrSurveyValues as $intIndex => $arrValues) {
                   $this->_objTpl->setVariable(array(
                    'TXT_IMGALT_STATUS'    =>    $_ARRAYLANG['TXT_SURVEY_STATUS_CHANGE'],
                    'TXT_IMGALT_EDIT'    =>    $_ARRAYLANG['TXT_SURVEY_EDIT'],
                    'TXT_IMGALT_DELETE'    =>    $_CORELANG['TXT_SURVEY_DELETE']
                ));

                $this->_objTpl->setVariable(array(
                       'SURVEY_ROWCLASS'            =>    'row'.($intIndex % 2),
                       'SURVEY_ID'                    =>    $arrValues['id'],
                       'SURVEY_STATUS_ICON'        =>    ($arrValues['isActive'] == 1) ? 'led_green' : 'led_red',
                       'SURVEY_TYPE_ICON'            =>    ($arrValues['isExtended'] == 1) ? '<img src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/survey/survey.gif" border="0" alt="'.$_ARRAYLANG['TXT_SURVEY_TYPE_SURVEY'].'" title="'.$_ARRAYLANG['TXT_SURVEY_TYPE_SURVEY'].'" />' : '<img src="'.ASCMS_MODULE_IMAGE_WEB_PATH.'/survey/voting.gif" border="0" alt="'.$_ARRAYLANG['TXT_SURVEY_TYPE_VOTING'].'" title="'.$_ARRAYLANG['TXT_SURVEY_TYPE_VOTING'].'" />',
                       'SURVEY_HOME_ICON'            =>    ($arrValues['isHomeBox'] == 1) ? '<img src="images/icons/check.gif" border="0" alt="'.$_ARRAYLANG['TXT_SURVEY_PLACEHOLDER_ACTIVE'].'" title="'.$_ARRAYLANG['TXT_SURVEY_PLACEHOLDER_ACTIVE'].'" />' : '',
                       'SURVEY_DATE'                =>    $arrValues['created'],
                       'SURVEY_NAME'                =>    $this->_arrSurveyTranslations[$arrValues['id']][$this->_intLangId],
                       'SURVEY_LAST_VOTE'            =>    $arrValues['lastvote'],
                       'SURVEY_VOTE_COUNT'            =>    $arrValues['participant']
                   ));
                   $this->_objTpl->parse('showSurveys');
            }
        } else {
            //No surveys entered
               $this->_objTpl->setVariable(array(
                   'TXT_NO_CATEGORIES'    =>    $_ARRAYLANG['TXT_SURVEY_NO_SURVEYS'],
               ));
            $this->_objTpl->parse('noSurveys');
            $this->_objTpl->hideBlock('showSurveys');
        }
    }


    /**
     * Show settings of the survey module.
     *
     * @global     array        $_CORELANG
     * @global     array        $_ARRAYLANG
     */
    function showSettings() {
        global $_CORELANG, $_ARRAYLANG;

        $this->_strPageTitle = $_CORELANG['TXT_SURVEY_MENU_SETTINGS'];
        $this->_objTpl->loadTemplateFile('module_survey_settings.html',true,true);

        $this->_objTpl->setVariable(array(
            'TXT_ACTIVATED'                    =>    $_CORELANG['TXT_ACTIVATED'],
            'TXT_DEACTIVATED'                =>    $_CORELANG['TXT_DEACTIVATED'],
            'TXT_LOGFILE'                    =>    $_ARRAYLANG['TXT_SETTINGS_LOGFILE'],
            'TXT_LOGFILE_HELP'                =>    $_ARRAYLANG['TXT_SETTINGS_LOGFILE_HELP'],
            'TXT_ANONYMOUS'                    =>    $_ARRAYLANG['TXT_SETTINGS_ANONYMOUS'],
            'TXT_ANONYMOUS_HELP'            =>    $_ARRAYLANG['TXT_SETTINGS_ANONYMOUS_HELP'],
            'TXT_BUTTON_SAVE'                =>    $_CORELANG['TXT_SAVE'],
           ));

           $this->_objTpl->setVariable(array(
               'SETTINGS_LOGFILE_ON_CHECKED'        =>    ($this->_arrSettings['logVotes'] == 1) ? 'checked' : '',
               'SETTINGS_LOGFILE_OFF_CHECKED'        =>    ($this->_arrSettings['logVotes'] == 0) ? 'checked' : '',
               'SETTINGS_ANONYMOUS_ON_CHECKED'        =>    ($this->_arrSettings['allowAnonymous'] == 1) ? 'checked' : '',
               'SETTINGS_ANONYMOUS_OFF_CHECKED'    =>    ($this->_arrSettings['allowAnonymous'] == 0) ? 'checked' : '',
           ));
    }


    /**
     * Save settings into database.
     *
     * @global     object        $objDatabase
     * @global     array        $_ARRAYLANG
     */
    function saveSettings() {
        global $objDatabase, $_ARRAYLANG;

        foreach($_POST['setValue'] as $intKey => $strValue) {
            switch ($intKey) {
                case 1:
                case 2:
                    $strValue = (intval($strValue) == 1) ? 1 : 0;
                    break;
                default:
                    //do nothing
            }

            $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_survey_settings
                                    SET        value="'.$strValue.'"
                                    WHERE    id='.$intKey.'
                                    LIMIT    1
                                ');
        }

        $this->_arrSettings = $this->createSettingsArray();
        $this->_strOkMessage = $_ARRAYLANG['TXT_SETTINGS_UPDATE_SUCCESSFULL'];
    }


     /**
     * Add a new survey to the database
     *
     * @global     array        $_CORELANG
     * @global     array        $_ARRAYLANG
     */
    function addSurvey() {
        global $_CORELANG, $_ARRAYLANG;

        $this->_strPageTitle = $_CORELANG['TXT_SURVEY_MENU_ADD'];
        $this->_objTpl->loadTemplateFile('module_survey_modify.html',true,true);

        $this->_objTpl->setVariable(array(
            'TXT_TITLE_ADD_SURVEY'             =>    $_CORELANG['TXT_SURVEY_MENU_ADD'],
            'TXT_TITLE_TYPE'                =>    $_ARRAYLANG['TXT_SURVEY_TYPESELECTION'],
            'TXT_TITLE_VOTING'                =>    $_ARRAYLANG['TXT_SURVEY_TYPE_VOTING'],
            'TXT_TITLE_SURVEY'                =>    $_ARRAYLANG['TXT_SURVEY_TYPE_SURVEY'],
            'TXT_JS_HELP_REDIRECT'            =>    $_ARRAYLANG['TXT_SURVEY_REDIRECT_HELP'],
            'TXT_JS_HELP_COMMENTS'            =>    $_ARRAYLANG['TXT_SURVEY_COMMENTS_HELP'],
            'TXT_JS_HELP_HOMEBOX'            =>    $_ARRAYLANG['TXT_SURVEY_HOMEBOX_HELP'],
            'TXT_JS_QUESTION'                =>    $_ARRAYLANG['TXT_SURVEY_QUESTION'],
            'TXT_JS_QUESTION_REMOVE'        =>    $_ARRAYLANG['TXT_SURVEY_QUESTION_REMOVE'],
            'TXT_JS_ANSWER_REMOVE'            =>    $_ARRAYLANG['TXT_SURVEY_ANSWER_REMOVE'],
            'TXT_EXTENDED'                    =>    $_ARRAYLANG['TXT_SURVEY_EXTENDED'],
            'TXT_ANSWERS'                    =>    $_ARRAYLANG['TXT_SURVEY_ANSWERS'],
            'TXT_ANSWER_ADD'                =>    $_ARRAYLANG['TXT_SURVEY_ANSWER_ADD'],
            'TXT_MOVE_UP'                    =>    $_ARRAYLANG['TXT_SURVEY_MOVE_UP'],
            'TXT_MOVE_DOWN'                    =>    $_ARRAYLANG['TXT_SURVEY_MOVE_DOWN'],
            'TXT_ANSWER_DELETE'                =>    $_ARRAYLANG['TXT_SURVEY_ANSWER_DELETE'],
            'TXT_NAME'                        =>    $_CORELANG['TXT_NAME'],
            'TXT_ACTIVATED_IN'                =>    $_ARRAYLANG['TXT_SURVEY_ACTIVATED_IN'],
            'TXT_STATUS'                    =>    $_CORELANG['TXT_STATUS'],
            'TXT_ACTIVE'                    =>    $_CORELANG['TXT_ACTIVATED'],
            'TXT_INACTIVE'                    =>    $_CORELANG['TXT_DEACTIVATED'],
            'TXT_REDIRECT'                    =>    $_CORELANG['TXT_REDIRECT'],
            'TXT_BROWSE'                    =>    $_CORELANG['TXT_BROWSE'],
            'TXT_TYPE'                        =>    $_CORELANG['TXT_TYPE'],
            'TXT_VOTING'                    =>    $_ARRAYLANG['TXT_SURVEY_TYPE_VOTING'],
            'TXT_SURVEY'                    =>    $_ARRAYLANG['TXT_SURVEY_TYPE_SURVEY'],
            'TXT_PLACEHOLDER'                =>    $_ARRAYLANG['TXT_SURVEY_PLACEHOLDER'],
            'TXT_COMMENTABLE'                =>    $_ARRAYLANG['TXT_SURVEY_COMMENTABLE'],
            'TXT_BUTTON_QUESTION_ADD'        =>    $_ARRAYLANG['TXT_SURVEY_QUESTION_ADD'],
            'TXT_BUTTON_QUESTION_REMOVE'    =>    $_ARRAYLANG['TXT_SURVEY_QUESTION_DELETE'],
            'TXT_BUTTON_SUBMIT'                =>    $_CORELANG['TXT_ADD'],
           ));

           $strJsOnLoad =     '<script language="JavaScript" type="text/javascript">';
           $strJsOnLoad .=    'addQuestion(\'Voting\', 3); addQuestion(\'Survey\', 3);';
           $strJsOnLoad .= '</script>';

           $this->_objTpl->setVariable(array(
               'GENERAL_JS_ONLOAD'                            =>    $strJsOnLoad,
               'GENERAL_FORM_ACTION'                        =>    '?cmd=survey&amp;act=insert',
               'GENERAL_NAME'                                =>    '',
               'GENERAL_STATUS_ACTIVATED_CHECKED'            =>    'checked="checked"',
               'GENERAL_STATUS_INACTIVATED_CHECKED'        =>    '',
               'GENERAL_REDIRECT'                            =>    '',
               'GENERAL_TYPE_VOTING_CHECKED'                =>    'checked="checked"',
               'GENERAL_TYPE_SURVEY_CHECKED'                =>    '',
               'GENERAL_TYPE_VOTING_DIV'                    =>    'display: block;',
               'GENERAL_TYPE_SURVEY_DIV'                    =>    'display: none;',
               'GENERAL_HOMEBOX_ACTIVATED_CHECKED'            =>    'checked="checked"',
               'GENERAL_HOMEBOX_INACTIVATED_CHECKED'        =>    '',
               'GENERAL_HOMEBOX_ACTIVATED_DISABLED'        =>    '',
               'GENERAL_HOMEBOX_INACTIVATED_DISABLED'        =>    '',
               'GENERAL_COMMENTABLE_ACTIVATED_CHECKED'        =>    'checked="checked"',
               'GENERAL_COMMENTABLE_INACTIVATED_CHECKED'    =>    '',
               'GENERAL_COMMENTABLE_ACTIVATED_DISABLED'    =>    'disabled="disabled"',
               'GENERAL_COMMENTABLE_INACTIVATED_DISABLED'    =>    'disabled="disabled"',
           ));

           $this->_objTpl->setVariable(array(
               'VOTING_QUESTIONS'    =>    '',
               'VOTING_JS_ARRAY'    =>    '',
               'SURVEY_QUESTIONS'    =>    '',
               'SURVEY_JS_ARRAY'    =>    ''
           ));

           if (count($this->_arrLanguages) > 0) {
               $intCounter = 0;
               $arrLanguages = array();
               $strJSLanguages = 'var arrLanguages = new Array();';

               foreach ($this->_arrLanguages as $intLangId => $arrValues) {
                   $arrLanguages[$intCounter%3] .= '<input type="checkbox" name="frmModifySurvey_Languages[]" value="'.$intLangId.'" checked="checked" />'.$arrValues['long'].' ['.$arrValues['short'].']<br />';
                   $strJSLanguages .= 'arrLanguages['.$intCounter.'] = new Object();';
                   $strJSLanguages .= 'arrLanguages['.$intCounter.']["id"] = "'.$intLangId.'";';
                   $strJSLanguages .= 'arrLanguages['.$intCounter.']["name"] = "'.$arrValues['long'].' ['.$arrValues['short'].']'.'";';

                   $this->_objTpl->setVariable(array(
                       'MODIFY_NAME_LANGID'    =>    $intLangId,
                       'MODIFY_NAME_LANG'        =>    $arrValues['long'].' ['.$arrValues['short'].']',
                       'MODIFY_NAME_VALUE'        =>    ''
                   ));
                   $this->_objTpl->parse('nameFields');

                   ++$intCounter;
               }

               $this->_objTpl->setVariable(array(
                   'MODIFY_LANGUAGES_1'    =>    $arrLanguages[0],
                   'MODIFY_LANGUAGES_2'    =>    $arrLanguages[1],
                   'MODIFY_LANGUAGES_3'    =>    $arrLanguages[2],
                   'MODIFY_LANGUAGES_JS'    =>    $strJSLanguages
               ));
           }
    }


     /**
     * Insert new voting / survey into the database
     *
     * @global     object        $objDatabase
     * @global     array        $_CORELANG
     * @global     array        $_ARRAYLANG
     */
    function insertSurvey() {
        global $objDatabase, $_CORELANG, $_ARRAYLANG;

        $strType         = ($_POST['frmModifySurvey_Type'] == 'Voting') ? 'Voting' : 'Survey';
        $strRedirect     = addslashes(strip_tags($_POST['frmModifySurvey_Redirect']));
        $boolActive     = (intval($_POST['frmModifySurvey_Status']) == 1) ? '1' : '0';
        $boolComment     = (intval($_POST['frmModifySurvey_Commentable']) == 1) ? '1' : '0';
        $boolHomebox     = (intval($_POST['frmModifySurvey_Homebox']) == 0) ? '0' : '1';

        $objDatabase->Execute('    INSERT
                                INTO    '.DBPREFIX.'module_survey_groups
                                SET        redirect="'.$strRedirect.'",
                                        created='.time().',
                                        lastvote=0,
                                        participant=0,
                                        isActive="'.$boolActive.'",
                                        isExtended="'.(($_POST['frmModifySurvey_Type'] == 'Voting') ? 0 : 1).'",
                                        isCommentable="'.$boolComment.'",
                                        isHomeBox="'.$boolHomebox.'"
                            ');
        $intGroupId = $objDatabase->insert_id();

        $arrLanguages = array();
        foreach ($_POST as $strKey => $strValue) {
            if (substr($strKey,0,strlen('frmModifySurvey_Name_')) == 'frmModifySurvey_Name_') {
                $arrLanguages[intval(substr($strKey,strlen('frmModifySurvey_Name_')))] = true;
                $objDatabase->Execute('    INSERT
                                        INTO    '.DBPREFIX.'module_survey_groups_lang
                                        SET        group_id='.$intGroupId.',
                                                lang_id='.intval(substr($strKey,strlen('frmModifySurvey_Name_'))).',
                                                subject="'.addslashes(strip_tags($strValue)).'"
                                    ');
            }
        }

        foreach($_POST as $strKey => $strValue) {
            if (substr($strKey,0,strlen('frmModifySurvey_'.$strType.'_Question_')) == 'frmModifySurvey_'.$strType.'_Question_' &&
                count(explode('_',$strKey)) == 4 )
            {
                $intQuestionNumber = intval(substr($strKey,strlen('frmModifySurvey_'.$strType.'_Question_')));

                $objDatabase->Execute('    INSERT
                                        INTO    '.DBPREFIX.'module_survey_questions
                                        SET        group_id='.$intGroupId.',
                                                sorting='.$intQuestionNumber.'
                                    ');
                $intQuestionId = $objDatabase->insert_id();

                foreach ($arrLanguages as $intLangId => $boolValue) {
                    $objDatabase->Execute('    INSERT
                                            INTO    '.DBPREFIX.'module_survey_questions_lang
                                            SET        question_id='.$intQuestionId.',
                                                    lang_id='.$intLangId.',
                                                    question="'.addslashes(strip_tags($_POST['frmModifySurvey_'.$strType.'_Question_'.$intQuestionNumber.'_'.$intLangId])).'"
                                        ');
                }

                foreach ($_POST as $strInnerKey => $strInnerValue) {
                    if (substr($strInnerKey,0,strlen('frmModifySurvey_'.$strType.'_Answer_'.$intQuestionNumber.'_')) == 'frmModifySurvey_'.$strType.'_Answer_'.$intQuestionNumber.'_') {
                        $arrExplode = explode('_',$strInnerKey);
                        if (count($arrExplode) == 5) {
                            $intAnswerNumber = $arrExplode[4];

                            $objDatabase->Execute('    INSERT
                                                    INTO    '.DBPREFIX.'module_survey_answers
                                                    SET        question_id='.$intQuestionId.',
                                                            sorting='.$intAnswerNumber.',
                                                            votes=0
                                                ');
                            $intAnswerId = $objDatabase->insert_id();

                            foreach ($arrLanguages as $intLangId => $boolValue) {
                                $objDatabase->Execute('    INSERT
                                                        INTO    '.DBPREFIX.'module_survey_answers_lang
                                                        SET        answer_id='.$intAnswerId.',
                                                                lang_id='.$intLangId.',
                                                                answer="'.addslashes(strip_tags($_POST['frmModifySurvey_'.$strType.'_Answer_'.$intQuestionNumber.'_'.$intAnswerNumber.'_'.$intLangId])).'"
                                                    ');
                            }
                        }
                    }
                }
            }
        }

        $this->_arrSurveyTranslations = $this->createSurveyTranslationArray();
        $this->_arrSurveyValues = $this->createSurveyValuesArray();

        if ($strType == 'Voting') {
            $this->_strOkMessage = $_ARRAYLANG['TXT_SURVEY_INSERT_SUCCESS_VOTING'];
        } else {
            $this->_strOkMessage = $_ARRAYLANG['TXT_SURVEY_INSERT_SUCCESS_SURVEY'];
        }
    }


     /**
     * Set a new status for a voting / survey.
     *
     * @global     object        $objDatabase
     * @global     array        $_ARRAYLANG
     * @param     integer        $intSurveyId: The entry with this id will be updated
     * @param     integer        $intNewState: The entry will be set to this value. If it is empty, the current state will be inverted.
     */
    function setStatus($intSurveyId, $intNewState = '') {
        global $objDatabase, $_ARRAYLANG;

        $intSurveyId = intval($intSurveyId);

        if ($intSurveyId == 0) {
            $this->_strErrMessage = $_ARRAYLANG['TXT_SURVEY_STATUS_CHANGE_ERROR'];
            return;
        }

        if (empty($intNewState)) {
            $objResult = $objDatabase->Execute('SELECT    isActive
                                                FROM    '.DBPREFIX.'module_survey_groups
                                                WHERE    id='.$intSurveyId.'
                                                LIMIT    1
                                            ');
            if ($objResult->RecordCount() == 1) {
                $intNewState = ($objResult->fields['isActive'] == 1) ? 0 : 1;
            } else {
                $this->_strErrMessage = $_ARRAYLANG['TXT_SURVEY_STATUS_CHANGE_ERROR'];
                return;
            }
        } else {
            $intNewState = (intval($intNewState) == 1) ? 1 : 0;
        }

        $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_survey_groups
                                SET        isActive="'.$intNewState.'"
                                WHERE    id='.$intSurveyId.'
                                LIMIT    1
                            ');

        $this->_arrSurveyValues = $this->createSurveyValuesArray();

        $this->_strOkMessage = $_ARRAYLANG['TXT_SURVEY_STATUS_CHANGE_SUCCESS'];
    }


     /**
     * Remove a voting / survey from database.
     *
     * @global     object        $objDatabase
     * @global     array        $_ARRAYLANG
     * @param     integer        $intSurveyId: The entry with this id will be removed
     */
    function deleteSurvey($intSurveyId) {
        global $objDatabase, $_ARRAYLANG;

        $intSurveyId = intval($intSurveyId);

        if ($intSurveyId == 0) {
            $this->_strErrMessage = $_ARRAYLANG['TXT_SURVEY_DELETE_ERROR'];
            return;
        }

        $strName = $this->_arrSurveyTranslations[$intSurveyId][$this->_intLangId];

        $objDatabase->Execute('    DELETE
                                FROM    '.DBPREFIX.'module_survey_groups
                                WHERE    id='.$intSurveyId.'
                                LIMIT    1
                            ');

        $objDatabase->Execute('    DELETE
                                FROM    '.DBPREFIX.'module_survey_groups_lang
                                WHERE    group_id='.$intSurveyId.'
                            ');

        $objResult = $objDatabase->Execute('SELECT    id
                                            FROM    '.DBPREFIX.'module_survey_questions
                                            WHERE    group_id='.$intSurveyId.'
                                        ');
        while(!$objResult->EOF) {
            $intQuestionId = $objResult->fields['id'];

            $objSubResult = $objDatabase->Execute('    SELECT    id
                                                    FROM    '.DBPREFIX.'module_survey_answers
                                                    WHERE    question_id='.$intQuestionId.'
                                                ');
            while (!$objSubResult->EOF) {
                $intAnswerId = $objSubResult->fields['id'];

                $objDatabase->Execute('    DELETE
                                        FROM    '.DBPREFIX.'module_survey_answers_lang
                                        WHERE    answer_id='.$intAnswerId.'
                                    ');

                $objDatabase->Execute('    DELETE
                                        FROM    '.DBPREFIX.'module_survey_votes
                                        WHERE    answer_id='.$intAnswerId.'
                                    ');

                $objSubResult->MoveNext();
            }

            $objDatabase->Execute('    DELETE
                                    FROM    '.DBPREFIX.'module_survey_answers
                                    WHERE    question_id='.$intQuestionId.'
                                ');

            $objDatabase->Execute('    DELETE
                                    FROM    '.DBPREFIX.'module_survey_questions_lang
                                    WHERE    question_id='.$intQuestionId.'
                                ');

            $objResult->MoveNext();
        }

        $objDatabase->Execute('    DELETE
                                FROM    '.DBPREFIX.'module_survey_questions
                                WHERE    group_id='.$intSurveyId.'
                            ');


        $this->_arrSurveyTranslations = $this->createSurveyTranslationArray();
        $this->_arrSurveyValues = $this->createSurveyValuesArray();

        $this->_strOkMessage = str_replace('[NAME]',$strName,$_ARRAYLANG['TXT_SURVEY_DELETE_SUCCESS']);
    }


     /**
     * Perform "multiaction" for all selected surveys.
     *
     */
    function doMultiAction() {
        if (is_array($_POST['selectedSurveyId'])) {
            foreach ($_POST['selectedSurveyId'] as $intKey => $intSurveyId) {
                switch ($_POST['frmShowSurveys_MultiAction']) {
                    case 'activate':
                            $this->setStatus($intSurveyId,1);
                        break;
                    case 'deactivate':
                            $this->setStatus($intSurveyId,0);
                        break;
                    case 'delete':
                            $this->deleteSurvey($intSurveyId);
                        break;
                }
            }
        }
    }


     /**
     * Show edit-form for an existing survey.
     *
     * @global     array        $_CORELANG
     * @global     array        $_ARRAYLANG
     * @global     object        $objDatabase
     * @param     integer        $intSurveyId
     */
    function editSurvey($intSurveyId) {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;

        $intSurveyId = intval($intSurveyId);
        $intIndexId = $this->getSurveyArrayIndex($intSurveyId);
        if ($intIndexId == -1) {
            //Error!
        }

        $this->_strPageTitle = $_CORELANG['TXT_SURVEY_MENU_ADD'];
        $this->_objTpl->loadTemplateFile('module_survey_modify.html',true,true);

        $this->_objTpl->setVariable(array(
            'TXT_TITLE_ADD_SURVEY'             =>    $_CORELANG['TXT_SURVEY_MENU_ADD'],
            'TXT_TITLE_TYPE'                =>    $_ARRAYLANG['TXT_SURVEY_TYPESELECTION'],
            'TXT_TITLE_VOTING'                =>    $_ARRAYLANG['TXT_SURVEY_TYPE_VOTING'],
            'TXT_TITLE_SURVEY'                =>    $_ARRAYLANG['TXT_SURVEY_TYPE_SURVEY'],
            'TXT_JS_HELP_REDIRECT'            =>    $_ARRAYLANG['TXT_SURVEY_REDIRECT_HELP'],
            'TXT_JS_HELP_COMMENTS'            =>    $_ARRAYLANG['TXT_SURVEY_COMMENTS_HELP'],
            'TXT_JS_HELP_HOMEBOX'            =>    $_ARRAYLANG['TXT_SURVEY_HOMEBOX_HELP'],
            'TXT_JS_QUESTION'                =>    $_ARRAYLANG['TXT_SURVEY_QUESTION'],
            'TXT_JS_QUESTION_REMOVE'        =>    $_ARRAYLANG['TXT_SURVEY_QUESTION_REMOVE'],
            'TXT_JS_ANSWER_REMOVE'            =>    $_ARRAYLANG['TXT_SURVEY_ANSWER_REMOVE'],
            'TXT_EXTENDED'                    =>    $_ARRAYLANG['TXT_SURVEY_EXTENDED'],
            'TXT_ANSWERS'                    =>    $_ARRAYLANG['TXT_SURVEY_ANSWERS'],
            'TXT_ANSWER_ADD'                =>    $_ARRAYLANG['TXT_SURVEY_ANSWER_ADD'],
            'TXT_MOVE_UP'                    =>    $_ARRAYLANG['TXT_SURVEY_MOVE_UP'],
            'TXT_MOVE_DOWN'                    =>    $_ARRAYLANG['TXT_SURVEY_MOVE_DOWN'],
            'TXT_ANSWER_DELETE'                =>    $_ARRAYLANG['TXT_SURVEY_ANSWER_DELETE'],
            'TXT_NAME'                        =>    $_CORELANG['TXT_NAME'],
            'TXT_ACTIVATED_IN'                =>    $_ARRAYLANG['TXT_SURVEY_ACTIVATED_IN'],
            'TXT_STATUS'                    =>    $_CORELANG['TXT_STATUS'],
            'TXT_ACTIVE'                    =>    $_CORELANG['TXT_ACTIVATED'],
            'TXT_INACTIVE'                    =>    $_CORELANG['TXT_DEACTIVATED'],
            'TXT_REDIRECT'                    =>    $_CORELANG['TXT_REDIRECT'],
            'TXT_BROWSE'                    =>    $_CORELANG['TXT_BROWSE'],
            'TXT_TYPE'                        =>    $_CORELANG['TXT_TYPE'],
            'TXT_VOTING'                    =>    $_ARRAYLANG['TXT_SURVEY_TYPE_VOTING'],
            'TXT_SURVEY'                    =>    $_ARRAYLANG['TXT_SURVEY_TYPE_SURVEY'],
            'TXT_PLACEHOLDER'                =>    $_ARRAYLANG['TXT_SURVEY_PLACEHOLDER'],
            'TXT_COMMENTABLE'                =>    $_ARRAYLANG['TXT_SURVEY_COMMENTABLE'],
            'TXT_BUTTON_QUESTION_ADD'        =>    $_ARRAYLANG['TXT_SURVEY_QUESTION_ADD'],
            'TXT_BUTTON_QUESTION_REMOVE'    =>    $_ARRAYLANG['TXT_SURVEY_QUESTION_DELETE'],
            'TXT_BUTTON_SUBMIT'                =>    $_CORELANG['TXT_ADD'],
           ));

           $this->_objTpl->setVariable(array(
               'GENERAL_JS_ONLOAD'                            =>    '',
               'GENERAL_FORM_ACTION'                        =>    '?cmd=survey&amp;act=update',
               'GENERAL_NAME'                                =>    $this->_arrSurveyTranslations[$intSurveyId][$this->_intLangId],
               'GENERAL_STATUS_ACTIVATED_CHECKED'            =>    ($this->_arrSurveyValues[$intIndexId]['isActive']) ? 'checked="checked"' : '',
               'GENERAL_STATUS_INACTIVATED_CHECKED'        =>    ($this->_arrSurveyValues[$intIndexId]['isActive']) ? '' : 'checked="checked"',
               'GENERAL_REDIRECT'                            =>    $this->_arrSurveyValues[$intIndexId]['redirect'],
               'GENERAL_TYPE_VOTING_CHECKED'                =>    ($this->_arrSurveyValues[$intIndexId]['isExtended']) ? '' : 'checked="checked"',
               'GENERAL_TYPE_SURVEY_CHECKED'                =>    ($this->_arrSurveyValues[$intIndexId]['isExtended']) ? 'checked="checked"' : '',
               'GENERAL_TYPE_VOTING_DIV'                    =>    ($this->_arrSurveyValues[$intIndexId]['isExtended']) ? 'display: none;' : 'display: block;',
               'GENERAL_TYPE_SURVEY_DIV'                    =>    ($this->_arrSurveyValues[$intIndexId]['isExtended']) ? 'display: block;' : 'display: none;',
               'GENERAL_HOMEBOX_ACTIVATED_CHECKED'            =>    ($this->_arrSurveyValues[$intIndexId]['isHomeBox']) ? 'checked="checked"' : '',
               'GENERAL_HOMEBOX_INACTIVATED_CHECKED'        =>    ($this->_arrSurveyValues[$intIndexId]['isHomeBox']) ? '' : 'checked="checked"',
               'GENERAL_HOMEBOX_ACTIVATED_DISABLED'        =>    ($this->_arrSurveyValues[$intIndexId]['isExtended']) ? 'disabled="disabled"' : '',
               'GENERAL_HOMEBOX_INACTIVATED_DISABLED'        =>    ($this->_arrSurveyValues[$intIndexId]['isExtended']) ? 'disabled="disabled"' : '',
               'GENERAL_COMMENTABLE_ACTIVATED_CHECKED'        =>    ($this->_arrSurveyValues[$intIndexId]['isCommentable']) ? 'checked="checked"' : '',
               'GENERAL_COMMENTABLE_INACTIVATED_CHECKED'    =>    ($this->_arrSurveyValues[$intIndexId]['isCommentable']) ? '' : 'checked="checked"',
               'GENERAL_COMMENTABLE_ACTIVATED_DISABLED'    =>    ($this->_arrSurveyValues[$intIndexId]['isExtended']) ? '' : 'disabled="disabled"',
               'GENERAL_COMMENTABLE_INACTIVATED_DISABLED'    =>    ($this->_arrSurveyValues[$intIndexId]['isExtended']) ? '' : 'disabled="disabled"',
           ));

           if (count($this->_arrLanguages) > 0) {
               $intCounter = 0;
               $arrLanguages = array();
               $strJSLanguages = 'var arrLanguages = new Array();';

               foreach ($this->_arrLanguages as $intLangId => $arrValues) {
                   $arrLanguages[$intCounter%3] .= '<input type="checkbox" name="frmModifySurvey_Languages[]" value="'.$intLangId.'" checked="checked" />'.$arrValues['long'].' ['.$arrValues['short'].']<br />';
                   $strJSLanguages .= 'arrLanguages['.$intCounter.'] = new Object();';
                   $strJSLanguages .= 'arrLanguages['.$intCounter.']["id"] = "'.$intLangId.'";';
                   $strJSLanguages .= 'arrLanguages['.$intCounter.']["name"] = "'.$arrValues['long'].' ['.$arrValues['short'].']'.'";';

                   $this->_objTpl->setVariable(array(
                       'MODIFY_NAME_LANGID'    =>    $intLangId,
                       'MODIFY_NAME_LANG'        =>    $arrValues['long'].' ['.$arrValues['short'].']',
                       'MODIFY_NAME_VALUE'        =>    $this->_arrSurveyTranslations[$intSurveyId][$intLangId]
                   ));
                   $this->_objTpl->parse('nameFields');

                   ++$intCounter;
               }

               $this->_objTpl->setVariable(array(
                   'MODIFY_LANGUAGES_1'    =>    $arrLanguages[0],
                   'MODIFY_LANGUAGES_2'    =>    $arrLanguages[1],
                   'MODIFY_LANGUAGES_3'    =>    $arrLanguages[2],
                   'MODIFY_LANGUAGES_JS'    =>    $strJSLanguages
               ));
           }

           $strVotingValues    = '';
           $strSurveyValues    = '';
           $strVotingJsArray     = '';
           $strSurveyJsArray     = '';

           $objResult = $objDatabase->Execute('SELECT        id
                                               FROM        '.DBPREFIX.'module_survey_questions
                                               WHERE        group_id='.$intSurveyId.'
                                               ORDER BY    sorting ASC
                                           ');
           $intQuestionCount = 0;
           while (!$objResult->EOF) {

               $objSubResult = $objDatabase->Execute('    SELECT        id
                                                       FROM        '.DBPREFIX.'module_survey_answers
                                                       WHERE        question_id='.$objResult->fields['id'].'
                                                       ORDER BY    sorting ASC
                                                   ');

               if ($this->_arrSurveyValues[$intIndexId]['isExtended']) {
                   $strSurveyValues    .= '<table id="id_Survey_Questions_'.$intQuestionCount.'" width="100%" cellspacing="0" cellpadding="3" border="0" align="top" class="adminlist">';
                   $strSurveyValues    .= '<tr class="row1">';
                   $strSurveyValues    .= '<td width="10%" valign="top">'.($intQuestionCount + 1).'. '.$_ARRAYLANG['TXT_SURVEY_QUESTION'].'</td>';
                   $strSurveyValues    .= '<td width="90%"><input type="text" id="frmModifySurvey_Survey_Question_'.$intQuestionCount.'" name="frmModifySurvey_Survey_Question_'.$intQuestionCount.'" onchange="copyText(\'frmModifySurvey_Survey_Question_'.$intQuestionCount.'\');" style="width:400px;" />&nbsp;<a href="javascript:showOrHide(\'divAddSurvey_Survey_Question_'.$intQuestionCount.'\');">'.$_ARRAYLANG['TXT_SURVEY_EXTENDED'].'</a>';

                   //Different languages for Question
                   $strSurveyValues    .= '<div id="divAddSurvey_Survey_Question_'.$intQuestionCount.'" style="display: none;">';
                   foreach ($this->_arrLanguages as $intLangId => $arrValues) {
                       $strSurveyValues .= '<div style="display: block;"><input type="text" name="frmModifySurvey_Survey_Question_'.$intQuestionCount.'_'.$intLangId.'" value="" style="width: 400px; margin-top: 1px;" />&nbsp;<label for="frmModifySurvey_Survey_Question_'.$intQuestionCount.'_'.$intLangId.'">'.$arrValues['long'].' ['.$arrValues['short'].']'.'</label><br /></div>';
                   }
                   $strSurveyValues    .= '</div>';

                   $strSurveyValues    .= '</td>';
                   $strSurveyValues    .= '</tr>';
                   $strSurveyValues    .= '<tr class="row2">';
                   $strSurveyValues    .= '<td valign="top" nowrap="nowrap">'.$_ARRAYLANG['TXT_SURVEY_ANSWERS'].'</td>';
                   $strSurveyValues    .= '<td><div id="id_Survey_Answers_'.$intQuestionCount.'"></div><input type="button" name="frmModifySurvey_Survey_Answer_Add_'.$intQuestionCount.'" onclick="addAnswer(\'Survey\','.$intQuestionCount.')" value="'.$_ARRAYLANG['TXT_SURVEY_ANSWER_ADD'].'" /></td>';
                   $strSurveyValues    .= '</tr>';
                   $strSurveyValues    .= '</table>';

                   $strSurveyJsArray     .= 'arrSurvey_Questions['.$intQuestionCount.'] = '.$objSubResult->RecordCount().';'."\n";
               } else {
                   $strVotingValues    .= '';
                   $strVotingJsArray     .= 'arrVote_Questions['.$intQuestionCount.'] = '.$objSubResult->RecordCount().';'."\n";
               }


               ++$intQuestionCount;
               $objResult->MoveNext();
           }

           $this->_objTpl->setVariable(array(
               'VOTING_QUESTIONS'    =>    $strVotingValues,
               'VOTING_JS_ARRAY'    =>    $strVotingJsArray,
               'SURVEY_QUESTIONS'    =>    $strSurveyValues,
               'SURVEY_JS_ARRAY'    =>    $strSurveyJsArray
           ));

    }
}
