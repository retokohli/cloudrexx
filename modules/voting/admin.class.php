<?php
/**
 * Class voting manager
 *
 * Class for the voting system
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Contrexx Dev Team <info@comvation.com>
 * @version       1.1
 * @package     contrexx
 * @subpackage  module_voting
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Checks empty entries
 */
function checkEntryData($var)
{
    return (trim($var)!="");
}

/**
 * Class voting manager
 *
 * Class for the voting system
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Contrexx Dev Team <info@comvation.com>
 * @version       1.1
 * @package     contrexx
 * @subpackage  module_voting
 * @todo        Edit PHP DocBlocks!
 */
class votingmanager
{
    var $_objTpl;
    var $strErrMessage = '';
    var $strOkMessage = '';

    /**
     * The available languages
     */
    private $languages;

    /**
    * Constructor
    *
    * @param  string
    * @access public
    */
    function __construct()
    {
        global $_ARRAYLANG, $objTemplate;

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/voting/template');
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'      => $_ARRAYLANG['TXT_VOTING_MANAGER'],
            'CONTENT_NAVIGATION' => "<a href='?cmd=voting'>".$_ARRAYLANG['TXT_VOTING_RESULTS']."</a>
                                   <a href='?cmd=voting&amp;act=add'>".$_ARRAYLANG['TXT_VOTING_ADD']."</a>
                                   <a href='?cmd=voting&amp;act=disablestatus'>".$_ARRAYLANG['TXT_VOTING_DISABLE']."</a>"
           ));
    }


    function getVotingPage()
    {
        global $_ARRAYLANG, $objTemplate;

        if (empty($_GET['act'])) {
            $_GET['act'] = '';
        }

        switch($_GET['act']){
            case 'detail':
                $action = $this->_detail();
                break;
            case "add":
                $action = $this->votingAdd();
            break;
            case "edit":
                $action = $this->votingEdit();
            break;
            case "addsubmit":
                $action = $this->votingAddSubmit();
                if ($action){
                   $action = $this->showCurrent();
                }else{
                   $action = $this->votingAdd();
                }
            break;
            case "editsubmit":
                $action = $this->votingEditSubmit();
                $action = $this->showCurrent();
            break;
            case "changestatus":
                $action = $this->changeStatus();
                $action = $this->showCurrent();
            break;
             case "disablestatus":
                $action = $this->DisableStatus();
                $action = $this->showCurrent();
            break;
            case "delete":
                $action = $this->votingDelete();
                $action = $this->showCurrent();
            break;
            case 'additionalexport':
                $this->export_additional_data();
            break;
            case "code":
                $action = $this->votingCode();
            break;
            default:
                $action = $this->showCurrent();
        }

        $objTemplate->setVariable(array(
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->strErrMessage,
            'ADMIN_CONTENT'                => $this->_objTpl->get()
        ));
    }


    function _detail()
    {
        global $objDatabase, $_CONFIG, $_ARRAYLANG;

        $systemId = intval($_REQUEST['id']);
        $count = 0;
        $pos = 0;

        $objVoting = $objDatabase->SelectLimit('SELECT `title`, `question` FROM `'.DBPREFIX.'voting_system` WHERE `id` = '.$systemId, 1);
        if ($objVoting !== false && $objVoting->RecordCount() == 1) {
            $title = $objVoting->fields['title'];
            $question = $objVoting->fields['question'];
        } else {
            return $this->showCurrent();
        }

        if (!empty($_GET['delete'])) {
            $objMail = $objDatabase->Execute('SELECT system_id, voting_id FROM `'.DBPREFIX.'voting_rel_email_system` WHERE email_id = '.intval($_GET['delete']));
            if ($objMail !== false && $objMail->RecordCount() > 0) {
                while (!$objMail->EOF) {
                    $objDatabase->Execute('UPDATE `'.DBPREFIX.'voting_system` SET `votes` = `votes` - 1 WHERE `id` = '.$objMail->fields['system_id']);
                    $objDatabase->Execute('UPDATE `'.DBPREFIX.'voting_results` SET `votes` = `votes` - 1 WHERE `id` = '.$objMail->fields['voting_id'].' AND `voting_system_id` = '.$objMail->fields['system_id']);
                    $objMail->MoveNext();
                }

                $objDatabase->Execute('DELETE FROM `'.DBPREFIX.'voting_rel_email_system` WHERE `email_id`         = '.intval($_GET['delete']));
                $objDatabase->Execute('DELETE FROM `'.DBPREFIX.'voting_email`            WHERE `id`               = '.intval($_GET['delete']));
                $objDatabase->Execute('DELETE FROM `'.DBPREFIX.'voting_additionaldata`   WHERE `voting_system_id` = '.intval($_GET['delete']));
            }
        }

        if (!empty($_GET['verify'])) {
            $objDatabase->Execute('UPDATE `'.DBPREFIX.'voting_email` SET `valid` = \'1\' WHERE `id` = '.intval($_GET['verify']));
        }

        $objCount = $objDatabase->SelectLimit('SELECT COUNT(1) AS votecount FROM `'.DBPREFIX.'voting_rel_email_system` AS s INNER JOIN `'.DBPREFIX.'voting_email` AS e ON e.id=s.email_id WHERE s.system_id='.$systemId.' GROUP BY s.system_id', 1);
        if ($objCount !== false) {
            $count = $objCount->fields['votecount'];
        }

        if (!$count) {
            return $this->showCurrent();
        }

        $this->_objTpl->loadTemplatefile('voting_detail.html');

        if ($count > $_CONFIG['corePagingLimit']) {
            $pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
            $paging = getPaging($count, $pos, '&amp;cmd=voting&amp;act=detail&amp;id='.$systemId, ' E-Mails');
            $this->_objTpl->setVariable('VOTING_PAGING', '<br /><br />'.$paging."<br /><br />\n");
        }

        $this->_objTpl->setVariable(array(
            'VOTING_POS'    => $pos,
            'VOTING_ID'        => $systemId,
            'TXT_VOTING_FUNCTIONS'                    => $_ARRAYLANG['TXT_VOTING_FUNCTIONS'],
            'TXT_VOTING_EMAIL_ADRESSE_OF_QUESTION'    => sprintf($_ARRAYLANG['TXT_VOTING_EMAIL_ADRESSE_OF_QUESTION'], htmlentities($title, ENT_QUOTES).' ('.htmlentities($question, ENT_QUOTES).')'),
            'TXT_VOTING_EMAIL'                        => $_ARRAYLANG['TXT_VOTING_EMAIL'],
            'TXT_VOTING_VALID'                    => $_ARRAYLANG['TXT_VOTING_VALID'],
            'TXT_VOTING_FUNCTIONS'                    => $_ARRAYLANG['TXT_VOTING_FUNCTIONS'],
            'TXT_VOTING_CONFIRM_DELETE_EMAIL'        => $_ARRAYLANG['TXT_VOTING_CONFIRM_DELETE_EMAIL'],
            'TXT_VOTING_CONFIRM_VERIFY_EMAIL'        => $_ARRAYLANG['TXT_VOTING_CONFIRM_VERIFY_EMAIL']
        ));

        $this->_objTpl->setGlobalVariable(array(
            'TXT_VOTING_VERIFY_EMAIL'                => $_ARRAYLANG['TXT_VOTING_VERIFY_EMAIL'],
            'TXT_VOTING_DELETE_EMAIL'                => $_ARRAYLANG['TXT_VOTING_DELETE_EMAIL'],
            'TXT_VOTING_WRITE_EMAIL'                => $_ARRAYLANG['TXT_VOTING_WRITE_EMAIL']
        ));

        $objMails = $objDatabase->SelectLimit('SELECT e.id,e.email,e.valid FROM `'.DBPREFIX.'voting_rel_email_system` AS s INNER JOIN `'.DBPREFIX.'voting_email` AS e ON e.id=s.email_id WHERE s.system_id='.$systemId.' ORDER BY e.email', $_CONFIG['corePagingLimit'], $pos);
        if ($objMails !== false) {
            $row = 1;
            while (!$objMails->EOF) {
                $this->_objTpl->setVariable(array(
                    'VOTING_ROW_NR'        => $row = $row % 2 == 1 ? 2 : 1,
                    'VOTING_EMAIL'        => htmlentities($objMails->fields['email'], ENT_QUOTES),
                    'VOTING_EMAIL_ID'    => $objMails->fields['id'],
                    'VOTING_VALID'    => $objMails->fields['valid'] == '1' ? '<img src="images/icons/check_mark.gif" width="16" height="16" alt="'.$_ARRAYLANG['TXT_VOTING_EMAIL_IS_VAILD'].'" />' : '<img src="images/icons/question_mark.gif" width="16" height="16" alt="'.$_ARRAYLANG['TXT_VOTING_EMAIL_ISNT_VAILD'].'" />'
                ));

                if ($objMails->fields['valid'] == '1') {
                    $this->_objTpl->hideBlock('voting_verify_email');
                } else {
                    $this->_objTpl->touchBlock('voting_verify_email');
                }

                $objMails->MoveNext();

                $this->_objTpl->parse('voting_emails');
            }
            return true;
        }
        return false;
    }


    function showCurrent()
    {
        global $objDatabase, $_ARRAYLANG;

        $langID = BACKEND_LANG_ID;

        $this->_objTpl->loadTemplateFile('voting_results.html');

        // this gets the total count of polls.. but ain't we gonna get this
        // anways by selecting all polls??
        $query = "SELECT COUNT(1) as `count` FROM ".DBPREFIX."voting_system";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            $totalrows = $objResult->fields['count'];
        }

        $votingId = ((!isset($_GET['act']) || $_GET['act'] != "delete") && isset($_GET['votingid'])) ? intval($_GET['votingid']) : 0;

        // ok obviously we're selecting the first one here or the one given 
        // through ghet GET vars, to be able to display the results
        $query= "
            SELECT 
                    `vs`.`id`,
                    UNIX_TIMESTAMP(`vs`.`date`) AS `datesec`,
                    `vs`.`votes`,
                    `vl`.`question`
            FROM 
                    ".DBPREFIX."voting_system  AS `vs`
            LEFT JOIN
                    `".DBPREFIX."voting_lang`  AS `vl`
                ON
                    `vl`.`pollID` = `vs`.`id`
            WHERE 
                    ".($votingId > 0 ? "id=".$votingId : "status=1")."
            AND
                    `vl`.`langID` = ".$langID;
        $objResult = $objDatabase->SelectLimit($query, 1);

        if ($objResult->RecordCount()==0 && $totalrows==0) {
           header("Location: ?cmd=voting&act=add");
           exit;
        } else {
            $votingId       = $objResult->fields['id'];
            $votingTitle    = stripslashes($objResult->fields['question']);
            $votingVotes    = $objResult->fields['votes'];
            $votingDate     = $objResult->fields['datesec'];
            $images         = 1;

            // ok now we're getting all the answers
            $query = "
                SELECT 
                        `vs`.id,
                        `vs`.votes,
                        `va`.`answer`
                FROM 
                        `".DBPREFIX."voting_results` AS `vs`
                LEFT JOIN
                        `".DBPREFIX."voting_answer`    AS `va`
                    ON
                        `va`.`resultID` = `vs`.`id`
                WHERE 
                        voting_system_id = '$votingId'
                AND
                        `va`.`langID` = ".$langID."
                ORDER BY 
                        `vs`.`id`
                ";
            $answers = new DBIterator($objDatabase->Execute($query));

            $votingResultText = '';
            foreach ($answers as $answer) {
                $votes = intval($answer['votes']);
                $percentage = 0;
                $imagewidth = 1; //Mozilla Bug if image width=0
                if($votes > 0) {
                    $percentage = (round(($votes/$votingVotes)*10000))/100;
                    $imagewidth = round($percentage,0);
                }
                $votingResultText .= stripslashes($answer['answer'])."<br />\n";
                $votingResultText .= "
                    <img src='images/icons/$images.gif' width='$imagewidth%' height=\"10\" alt=\"$votes ".$_ARRAYLANG['TXT_VOTES']." / $percentage %\" />
                ";
                $votingResultText .= "&nbsp;<font size='1'>$votes ".$_ARRAYLANG['TXT_VOTES']." / $percentage %</font><br />\n";
            }

            $this->_objTpl->setVariable(array(
                'VOTING_TITLE'               => $votingTitle,
                'VOTING_DATE'                => showFormattedDate($votingDate),
                'VOTING_RESULTS_TEXT'         => $votingResultText,
                'VOTING_RESULTS_TOTAL_VOTES' => $votingVotes,
                'VOTING_TOTAL_TEXT'          => $_ARRAYLANG['TXT_VOTING_TOTAL'],
                'TXT_DATE'                   => $_ARRAYLANG['TXT_DATE'],
                'TXT_TITLE'                  => $_ARRAYLANG['TXT_TITLE'],
                'TXT_VOTES'                  => $_ARRAYLANG['TXT_VOTES'],
                'TXT_ACTION'                 => $_ARRAYLANG['TXT_ACTION'],
                'TXT_ACTIVATION'             => $_ARRAYLANG['TXT_ACTIVATION'],
                'TXT_CREATE_HTML'             => $_ARRAYLANG['TXT_CREATE_HTML'],
                'TXT_CONFIRM_DELETE_DATA'    => $_ARRAYLANG['TXT_CONFIRM_DELETE_DATA'],
                'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE'],
                'TXT_EXPORT_ADDITIONAL'      => $_ARRAYLANG['TXT_EXPORT_ADDITIONAL'],
            ));

            $this->_objTpl->setGlobalVariable('TXT_HTML_CODE', $_ARRAYLANG['TXT_HTML_CODE']);

            // show other Voting entries
            $query = "
                SELECT 
                        `vs`.id,
                        `vs`.status,
                        `vs`.submit_check,
                        UNIX_TIMESTAMP(`vs`.`date`) AS `datesec`,
                        `vs`.votes,
                        `vl`.`title`
                FROM 
                        ".DBPREFIX."voting_system AS `vs`
                LEFT JOIN
                        `".DBPREFIX."voting_lang` AS `vl`
                    ON
                        `vl`.`pollID` = `vs`.`id`
                WHERE
                    `vl`.`langID` = ".$langID."
                ORDER BY 
                        id 
                DESC
            ";
            $votings = new DBIterator($objDatabase->Execute($query));

            $i = 0;
            foreach ($votings as $voting) {
                $votingid       = $voting['id'];
                $votingTitle    = stripslashes($voting['title']);
                $votingVotes    = $voting['votes'];
                $votingDate     = $voting['datesec'];
                $votingStatus   = $voting['status'];

                if ($votingStatus == 0) {
                     $radio=" onclick=\"Javascript: window.location.replace('?cmd=voting&amp;act=changestatus&amp;votingid=$votingid');\" />";
                } else {
                     $radio=" checked=\"checked\" />";
                }

                if (($i % 2) == 0) {
                    $class="row1";
                } else {
                    $class="row2";
                }

                $this->_objTpl->setVariable(array(
                    'VOTING_OLDER_TEXT'       => "<a href='?cmd=voting&amp;votingid=$votingid'>".$votingTitle."</a>",
                    'VOTING_OLDER_DATE'      => showFormattedDate($votingDate),
                    'VOTING_OLDER_VOTES'     => ($votingVotes > 0 && $voting['submit_check'] == 'email') 
                                                ? '<a href="?cmd=voting&amp;act=detail&amp;id='.$votingid.'" 
                                                    title="'.$_ARRAYLANG['TXT_VOTING_SHOW_EMAIL_ADRESSES'].'">'.$votingVotes.'</a>' 
                                                    : $votingVotes,
                    'VOTING_ID'              => $votingid,
                    'VOTING_LIST_CLASS'      => $class,
                    'VOTING_RADIO'           => "<input type='radio' name='voting_selected' value='radiobutton'".$radio,
                    'TXT_EXPORT_CSV'         => $_ARRAYLANG['TXT_EXPORT_CSV']
                ));
                $this->_objTpl->parse("votingRow");
                $i++;
            }
        }
    }


    /**
     * Save the poll
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @return      boolean
     */
    protected function votingAddSubmit()
    {
        global $objDatabase, $_ARRAYLANG;
        
        // if not set, abort
        if (empty($_POST['votingquestions']) || empty($_POST['votingnames'])) return false;

        $langs = $this->returnLanguages();

        $id = $this->insertPoll();

        // insert the language specific stuff
        $questions = $_POST['votingquestions'];
        $titles = $_POST['votingnames'];

        foreach ($langs as $lang) {
            $lang = $lang['id'];
            $title = (!empty($titles[$lang])) ? $titles[$lang] : $titles[0];
            $question = (!empty($questions[$lang])) ? $questions[$lang] : $questions[0];
            $this->insertLang($id, $lang, $title, $question);
        }

        // insert the answers
        $postAnswers = $_POST['votingoptions'];
        $answers = $this->buildAnswers($postAnswers);

        foreach ($answers as $answer) {
            $this->insertAnswer($id, $answer);
        }

    }

    /**
     * Return the current vote values of the answers
     *
     * @author     Stefan Heinemann <sh@adfinis.com>
     * @param      int $pollID
     * @return     iterator
     */
    private function getResultVotes($pollID) {
        global $objDatabase;

        $query = '
            SELECT
                `votes`
            FROM
                `'.DBPREFIX.'voting_results`
            WHERE
                `voting_system_id` = 
            ORDER BY
                `id`
            '.$pollID;

        $res = new DBIterator($objDatabase->execute($query));

        return $res;
    }

    /**
     * Clear the answers of a poll
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $pollID
     */
    private function clearAnswers($pollID) {
        global $objDatabase;

        // delete the voting_answer table content
        $query = "
            DELETE 
                `va`
            FROM
                `".DBPREFIX."voting_answer`   AS `va`
            LEFT JOIN
                `".DBPREFIX."voting_results`  AS `vr`
            ON
                `va`.`resultID` = `vr`.`id`
            WHERE
                `vr`.`voting_system_id` = ".$pollID;
        $objDatabase->execute($query);

        // delete the voting_results table content
        $query = "
            DELETE FROM
                `".DBPREFIX."voting_results`
            WHERE
                `voting_system_id` = ".$pollID;
        $objDatabase->execute($query);
    }

    /**
     * Insert an answer in all languages
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $pollID
     * @param       array $answer
     */
    private function insertAnswer($pollID, array $answer) {
        global $objDatabase;

        $votes = (isset($answer['votes'])) ? intval($answer['votes']) : 0;

        $query = '
            INSERT INTO
                `'.DBPREFIX.'voting_results`
            (
                `voting_system_id`,
                `votes`
            )
            VALUES
            (
                '.$pollID.',
                '.$votes.'
            )
        ';

        $objDatabase->execute($query);
        $id = $objDatabase->insert_id();

        foreach ($answer['langs'] as $lang => $answer) {
            $this->insertSingleAnswer($id, $lang, $answer);
        }

    }

    /**
     * Insert single answer
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $id
     * @param       int $langID
     * @param       string $answer
     */
    private function insertSingleAnswer($id, $langID, $answer) {
        global $objDatabase;

        $query = '
            INSERT INTO
                `'.DBPREFIX.'voting_answer`
            (
                `resultID`,
                `langID`,
                `answer`
            )
            VALUES
            (
                '.$id.',
                '.$langID.',
                "'.contrexx_addslashes($answer).'"
            )
            ON DUPLICATE KEY UPDATE
                `answer` = "'.$answer.'"
        ';

        $objDatabase->execute($query);
    }

    /**
     * Build the answer array from the post variable
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       array $postAnswers
     * @return      array
     */
    private function buildAnswers($postAnswers) {
        $answers = array();

        $langs = $this->returnLanguages();

        foreach ($postAnswers as $lang => $content) {
            $lang = intval($lang);
            $counter = 0;
            foreach (explode("\n", $content) as $line) {
                $answers[$counter++]['langs'][$lang] = $line;
            }
        }

        foreach ($answers as $key => $answer) {
            foreach ($langs as $c) {
                $lang = $c['id'];
                if (empty($answers[$key]['langs'][$lang])) {
                    $answers[$key]['langs'][$lang] = $answers[$key]['langs'][0];
                }
            }
        }

        return $answers;
    }

    /**
     * Insert a poll
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @return      int
     */
    private function insertPoll() {
        global $objDatabase;

        $method = isset($_POST['votingRestrictionMethod']) 
            ? contrexx_addslashes($_POST['votingRestrictionMethod']) 
            : 'cookie';
        $query = '
            INSERT INTO
                `'.DBPREFIX.'voting_system`
                (
                    `status`,
                    `submit_check`,
                    `votes`,
                    `additional_nickname`,
                    `additional_forename`,
                    `additional_surname`,
                    `additional_phone`,
                    `additional_street`,
                    `additional_zip`,
                    `additional_city`,
                    `additional_email`,
                    `additional_comment`
                )
                VALUES
                (
                    1,
                    "'.$method.'",
                    0,
                    '.(($_POST['additional_nickname'] == 'on') ? 1 : 0).',
                    '.(($_POST['additional_forename'] == 'on') ? 1 : 0).',
                    '.(($_POST['additional_surname']  == 'on') ? 1 : 0).',
                    '.(($_POST['additional_phone']    == 'on') ? 1 : 0).',
                    '.(($_POST['additional_street']   == 'on') ? 1 : 0).',
                    '.(($_POST['additional_zip']      == 'on') ? 1 : 0).',
                    '.(($_POST['additional_city']     == 'on') ? 1 : 0).',
                    '.(($_POST['additional_email']    == 'on') ? 1 : 0).',
                    '.(($_POST['additional_comment']  == 'on') ? 1 : 0).'
                )
        ';

        $objDatabase->execute($query);
        $id = $objDatabase->insert_id();

        return $id;
    }

    /**
     * insert hte language values of a poll
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $pollID
     * @param       int $langID
     * @param       string $title
     * @param       string $question
     */
    private function insertLang($pollID, $langID, $title, $question) {
        global $objDatabase;

        $title = contrexx_addslashes($title);
        $question = contrexx_addslashes($question);

        $query = '
            INSERT INTO
                `'.DBPREFIX.'voting_lang`
            (
                `pollID`,
                `langID`,
                `title`,
                `question`
            )
            VALUES
            (
                '.$pollID.',
                '.$langID.',
                "'.contrexx_addslashes($title).'",
                "'.contrexx_addslashes($question).'"
            )
        ';

        $objDatabase->execute($query);
    }

    /**
     * Update the language values of a poll
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $pollID
     * @param       int $langID
     * @param       string $title
     * @param       string $question
     */
    private function updatelang($pollID, $langID, $title, $question) {
        global $objDatabase;

        $title = contrexx_addslashes($title);
        $question = contrexx_addslashes($question);

        $query = '
            UPDATE
                `'.DBPREFIX.'voting_lang`
            SET
                `title` = "'.$title.'",
                `question` = "'.$question.'"
            WHERE
                `pollID` = '.$pollID.'
            AND
                `langID` = '.$langID;

        $objDatabase->execute($query);
    }


    /**
     * Insert the edited version of the poll
     *
     * @author      Comvation AG
     * @author      Stefan Heinemann <sh@adfinis.com>
     */
    function votingEditSubmit()
    {
        global $objDatabase,$_ARRAYLANG;

        $id = intval($_POST['votingid']);

        if (empty($_POST['votingquestions']) || empty($_POST['votingnames']) || $id == 0) {
            return false;
        }

        $this->updatePoll($pollID);

        $langs = $this->returnLanguages();

        $questions = $_POST['votingquestions'];
        $titles = $_POST['votingnames'];

        // update the langauge secific stuff
        foreach ($langs as $lang) {
            $lang = $lang['id'];
            $title = (!empty($titles[$lang])) ? $titles[$lang] : $titles[0];
            $question = (!empty($questions[$lang])) ? $questions[$lang] : $questions[0];
            $this->updateLang($id, $lang, $title, $question);
        }

        // insert the answers
        $voteList = $this->getResultVotes($id);
        $votes = array();
        $counter = 0;
        foreach ($voteList as $vote) {
            $votes[$counter++] = $vote['votes'];
        }
        #print_r($_POST);

        // clear the current votes and their results
        $this->clearAnswers($id);

        // insert the answers
        $postAnswers = $_POST['votingoptions'];
        $answers = $this->buildAnswers($postAnswers);

        // add the votes to the answers and finally add it
        foreach ($answers as $key => $answer) {
            $answer['votes'] = $votes[$key];
            $this->insertAnswer($id, $answer);
        }
    }

    /**
     * Update a poll
     *
     * @author      Comvation AG
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $pollID
     */
    private function updatePoll($pollId) {
        global $objDatabase;
        $query="
            UPDATE 
                ".DBPREFIX."voting_system
            SET 
                submit_check        = '".$method."',
                additional_nickname = '".($_POST['additional_nickname'] == 'on' ? 1 : 0)."',
                additional_forename = '".($_POST['additional_forename'] == 'on' ? 1 : 0)."',
                additional_surname  = '".($_POST['additional_surname' ] == 'on' ? 1 : 0)."',
                additional_phone    = '".($_POST['additional_phone'   ] == 'on' ? 1 : 0)."',
                additional_street   = '".($_POST['additional_street'  ] == 'on' ? 1 : 0)."',
                additional_zip      = '".($_POST['additional_zip'     ] == 'on' ? 1 : 0)."',
                additional_city     = '".($_POST['additional_city'    ] == 'on' ? 1 : 0)."',
                additional_email    = '".($_POST['additional_email'   ] == 'on' ? 1 : 0)."',
                additional_comment  = '".($_POST['additional_comment' ] == 'on' ? 1 : 0)."'

            WHERE 
                id = ".intval($pollID);

        $objDatabase->query($query);
    }


    /**
     * Delete a poll
     *
     * @author      Comvation AG
     * @author      Stefan Heinemann <sh@adfinis.com>
     */
    function votingDelete()
    {
        global $objDatabase;

        $id = intval($_GET['votingid']);

        // delete the voting_lang table content
        $query = "
            DELETE FROM
                `".DBPREFIX."voting_lang`
            WHERE
                `pollID` = ".$id;
        $objDatabase->execute($query);

        $this->clearAnswers($id);

        // Delete the voting_rel_email_system table content
        $query = "
            DELETE FROM 
                `".DBPREFIX."voting_rel_email_system`
            WHERE
                system_id = ".intval($_GET['votingid']);

        $objDatabase->Execute($query);
        $this->_cleanUpEmails();

        // now delete the actual poll
        $query = "
            DELETE FROM
                `".DBPREFIX."voting_system`
            WHERE
                `id` = ".$id;
        $objDatabase->execute($query);


        // now set one of the polls active
        $this->setOnePollActive();

        // Case when deleting the status active, it has to set another
        // yeah! wait what??
        //
        //
        /*
        $query = "
            SELECT 
                id 
            FROM 
                ".DBPREFIX."voting_system 
            WHERE 
                status = 1
        ";
        $objResult = $objDatabase->Execute($query);
        if(!$objResult->EOF && $_GET['votingid']==$objResult->fields["id"]) {
            $query = "
                SELECT 
                     MAX(id) 
                AS
                    maxid 
                FROM 
                    ".DBPREFIX."voting_system 
                WHERE 
                    status = 0
            ";
            $objResult = $objDatabase->query($query);
            if(!$objResult->EOF) {
               $maxid=$objResult->fields["maxid"];
               if (!is_null($maxid)) {
                       $query = "
                            UPDATE 
                                ".DBPREFIX."voting_system 
                            SET 
                                status = 1,
                                date = date 
                            WHERE 
                                id = $maxid
                        ";
                       $objDatabase->Execute($query);
               }
            }

        }

        $objDatabase->Execute("
            DELETE FROM 
                `".DBPREFIX."voting_rel_email_system` 
            WHERE
                system_id = ".intval($_GET['votingid'])
        );
        $this->_cleanUpEmails();

        $query = "
            DELETE FROM
                ".DBPREFIX."voting_system 
            WHERE 
                id=".intval($_GET['votingid']);

        $objDatabase->Execute($query);
        $query = "
            DELETE FROM 
                ".DBPREFIX."voting_results 
            WHERE 
                voting_system_id = ".intval($_GET['votingid']);
        $objDatabase->Execute($query);
         */
    }


    /**
     * Set one poll active if there's none active
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     */
    private function setOnePollActive() {
        global $objDatabase;

        $query = "
            SELECT count(`status`) AS `count`
            FROM
                `".DBPREFIX."voting_system`
            WHERE
                `status` = 1
        ";
        $res = $objDatabase->SelectLimit($query, 1);

        if ($res !== false) {
            if ($res->field['count'] == 0) {
                $query = "
                    UPDATE
                        `".DBPREFIX."voting_system`
                    SET
                        `status` = 1
                    LIMIT 1
                ";
                $objDatabase->execute($query);
            }

        }
    }


    function _cleanUpEmails()
    {
        global $objDatabase;

        $arrEmailIds = array();

        $objEmails = $objDatabase->Execute("SELECT e.id FROM ".DBPREFIX."voting_email AS e INNER JOIN ".DBPREFIX."voting_rel_email_system AS s ON s.email_id=e.id");
        if ($objEmails !== false) {
            while (!$objEmails->EOF) {
                array_push($arrEmailIds, $objEmails->fields['id']);
                $objEmails->MoveNext();
            }

            $objDatabase->Execute("DELETE FROM ".DBPREFIX."voting_email".(count($arrEmailIds) > 0 ? " WHERE id!=".implode(' AND id!=', $arrEmailIds) : ''));
        }
    }


    function changeStatus()
    {
        global $objDatabase;

        $query="UPDATE ".DBPREFIX."voting_system set status=0, date=date";
        $objDatabase->Execute($query);
        $query="UPDATE ".DBPREFIX."voting_system set status=1,date=date where id=".intval($_GET['votingid'])." ";
        $objDatabase->Execute($query);
    }


    function DisableStatus()
    {
        global $objDatabase;

        $query="UPDATE ".DBPREFIX."voting_system set status=0, date=date";
        $objDatabase->Execute($query);
    }


    function votingAdd()
    {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile('voting_add.html');

        $this->_objTpl->setVariable(array(
            'TXT_VOTING_METHOD_OF_RESTRICTION_TXT' => $_ARRAYLANG['TXT_VOTING_METHOD_OF_RESTRICTION_TXT'],
            'TXT_VOTING_COOKIE_BASED'              => $_ARRAYLANG['TXT_VOTING_COOKIE_BASED'],
            'TXT_VOTING_EMAIL_BASED'               => $_ARRAYLANG['TXT_VOTING_EMAIL_BASED'],
            'TXT_VOTING_ADD'                       => $_ARRAYLANG['TXT_VOTING_ADD'],
            'TXT_NAME'                             => $_ARRAYLANG['TXT_NAME'],
            'TXT_VOTING_QUESTION'                  => $_ARRAYLANG['TXT_VOTING_QUESTION'],
            'TXT_VOTING_ADD_OPTIONS'               => $_ARRAYLANG['TXT_VOTING_ADD_OPTIONS'],
            'TXT_STORE'                            => $_ARRAYLANG['TXT_STORE'],
            'TXT_RESET'                            => $_ARRAYLANG['TXT_RESET'],
            'TXT_ADDITIONAL_NICKNAME'              => $_ARRAYLANG['TXT_ADDITIONAL_NICKNAME'],
            'TXT_ADDITIONAL_FORENAME'              => $_ARRAYLANG['TXT_ADDITIONAL_FORENAME'],
            'TXT_ADDITIONAL_SURNAME'               => $_ARRAYLANG['TXT_ADDITIONAL_SURNAME' ],
            'TXT_ADDITIONAL_PHONE'                 => $_ARRAYLANG['TXT_ADDITIONAL_PHONE'   ],
            'TXT_ADDITIONAL_STREET'                => $_ARRAYLANG['TXT_ADDITIONAL_STREET'  ],
            'TXT_ADDITIONAL_ZIP'                   => $_ARRAYLANG['TXT_ADDITIONAL_ZIP'     ],
            'TXT_ADDITIONAL_CITY'                  => $_ARRAYLANG['TXT_ADDITIONAL_CITY'    ],
            'TXT_ADDITIONAL_EMAIL'                 => $_ARRAYLANG['TXT_ADDITIONAL_EMAIL'   ],
            'TXT_ADDITIONAL_COMMENT'               => $_ARRAYLANG['TXT_ADDITIONAL_COMMENT' ],
            'TXT_ADDITIONAL'                       => $_ARRAYLANG['TXT_ADDITIONAL'         ],
            'TXT_EXTENDED'                                               => $_ARRAYLANG['TXT_VOTING_EXTENDED'],
        ));

        $this->parseLanguages('showNameFields');
        $this->parseLanguages('showQuestionFields');
        $this->parseLanguages('showVotingOptionFields');

    }

    /**
     * Parse the languages in the templates
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       string $block
     * @param       array $values
     */
    private function parseLanguages($block, $values = array()) {
        if (empty($this->languages)) {
            $this->languages = $this->returnLanguages();
        }

        foreach ($this->languages as $lang) {
            $value = @$values[$lang['id']];
            $this->_objTpl->setVariable(array(
                    'LANG_ID'       => $lang['id'],
                    'LANG_VALUE'    => $value,
                    'LANG_NAME'     => $lang['name']
                )
            );

            $this->_objTpl->parse($block);
        }
    }

    /**
     * Return the available languages
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @return      object
     */
    private function returnLanguages() {
        global $objDatabase;

        $query = '    
            SELECT 
                id,
                name
            FROM 
               '.DBPREFIX.'languages
            WHERE
                `frontend` = 1
            ORDER BY 
                id ASC';
        $res = $objDatabase->Execute($query);
        return new DBIterator($res);
    }


    /**
     * Show the edit page for polls
     *
     * @author      Comvation AG
     * @author      Stefan Heinemann <sh@adfinis.com>
     */
    private function votingEdit()
    {
        global $objDatabase, $_ARRAYLANG;

        $pollID = intval($_GET['votingid']);

        $this->_objTpl->loadTemplateFile('voting_edit.html');

        $this->_objTpl->setVariable(array(
            'TXT_ADDITIONAL_NICKNAME' => $_ARRAYLANG['TXT_ADDITIONAL_NICKNAME'],
            'TXT_ADDITIONAL_FORENAME' => $_ARRAYLANG['TXT_ADDITIONAL_FORENAME'],
            'TXT_ADDITIONAL_SURNAME'  => $_ARRAYLANG['TXT_ADDITIONAL_SURNAME' ],
            'TXT_ADDITIONAL_PHONE'    => $_ARRAYLANG['TXT_ADDITIONAL_PHONE'   ],
            'TXT_ADDITIONAL_STREET'   => $_ARRAYLANG['TXT_ADDITIONAL_STREET'  ],
            'TXT_ADDITIONAL_ZIP'      => $_ARRAYLANG['TXT_ADDITIONAL_ZIP'     ],
            'TXT_ADDITIONAL_CITY'     => $_ARRAYLANG['TXT_ADDITIONAL_CITY'    ],
            'TXT_ADDITIONAL_EMAIL'    => $_ARRAYLANG['TXT_ADDITIONAL_EMAIL'   ],
            'TXT_ADDITIONAL_COMMENT'  => $_ARRAYLANG['TXT_ADDITIONAL_COMMENT' ],
            'TXT_ADDITIONAL'          => $_ARRAYLANG['TXT_ADDITIONAL'         ],
        ));

        // get the shit
        $query = "
            SELECT 
                * 
            FROM 
                ".DBPREFIX."voting_system 
            WHERE 
                id=".$pollID;

        $objResult = $objDatabase->Execute($query);
        if(!$objResult->EOF) {
            $votingname          = stripslashes($objResult->fields["title"]);
            $votingquestion      = stripslashes($objResult->fields["question"]);
            $votingid            = $objResult->fields["id"];
            $votingmethod        = $objResult->fields['submit_check'];

            $additional_nickname = $objResult->fields['additional_nickname'] ;
            $additional_forename = $objResult->fields['additional_forename'] ;
            $additional_surname  = $objResult->fields['additional_surname'] ;
            $additional_phone    = $objResult->fields['additional_phone'] ;
            $additional_street   = $objResult->fields['additional_street'] ;
            $additional_zip      = $objResult->fields['additional_zip'] ;
            $additional_city     = $objResult->fields['additional_city'] ;
            $additional_email    = $objResult->fields['additional_email'] ;
            $additional_comment  = $objResult->fields['additional_comment'];

        }

        $this->_objTpl->setVariable(array(
            'TXT_VOTING_METHOD_OF_RESTRICTION_TXT'  => $_ARRAYLANG['TXT_VOTING_METHOD_OF_RESTRICTION_TXT'],
            'TXT_VOTING_COOKIE_BASED'               => $_ARRAYLANG['TXT_VOTING_COOKIE_BASED'],
            'TXT_VOTING_EMAIL_BASED'                => $_ARRAYLANG['TXT_VOTING_EMAIL_BASED'],
            'VOTING_METHOD_OF_RESTRICTION_COOKIE'   => $votingmethod == 'cookie' ? 'checked="checked"' : '',
            'VOTING_METHOD_OF_RESTRICTION_EMAIL'    => $votingmethod == 'email' ? 'checked="checked"' : '',
            'TXT_VOTING_EDIT'                       => $_ARRAYLANG['TXT_VOTING_EDIT'],
            'TXT_NAME'                              => $_ARRAYLANG['TXT_NAME'],
            'TXT_VOTING_QUESTION'                   => $_ARRAYLANG['TXT_VOTING_QUESTION'],
            'TXT_VOTING_ADD_OPTIONS'                => $_ARRAYLANG['TXT_VOTING_ADD_OPTIONS'],
            'TXT_STORE'                             => $_ARRAYLANG['TXT_STORE'],
            'TXT_RESET'                             => $_ARRAYLANG['TXT_RESET'],
            'TXT_EXTENDED'                          => $_ARRAYLANG['TXT_VOTING_EXTENDED'],
            'VOTING_ID'                             => $votingid,
            // carries the results on or something
            'VOTING_RESULTS'                        => implode($voltingresults,";"), 
            'VOTING_FLAG_ADDITIONAL_NICKNAME'       => $additional_nickname ? 'checked="checked"' : '',
            'VOTING_FLAG_ADDITIONAL_FORENAME'       => $additional_forename ? 'checked="checked"' : '',
            'VOTING_FLAG_ADDITIONAL_SURNAME'        => $additional_surname  ? 'checked="checked"' : '',
            'VOTING_FLAG_ADDITIONAL_PHONE'          => $additional_phone    ? 'checked="checked"' : '',
            'VOTING_FLAG_ADDITIONAL_STREET'         => $additional_street   ? 'checked="checked"' : '',
            'VOTING_FLAG_ADDITIONAL_ZIP'            => $additional_zip      ? 'checked="checked"' : '',
            'VOTING_FLAG_ADDITIONAL_CITY'           => $additional_city     ? 'checked="checked"' : '',
            'VOTING_FLAG_ADDITIONAL_EMAIL'          => $additional_email    ? 'checked="checked"' : '',
            'VOTING_FLAG_ADDITIONAL_COMMENT'        => $additional_comment  ? 'checked="checked"' : '',
        ));

        list($titles, $questions) = $this->getLangValues($pollID);
        $answers = $this->getAnswers($pollID);

        $this->_objTpl->setVariable(
            array(
                'EDIT_NAME'         => $titles[1],
                'EDIT_QUESTION'     => $questions[1],
                'EDIT_OPTIONS'      => $answers[1]
            )
        );

        $this->parseLanguages('showNameFields', $titles);
        $this->parseLanguages('showQuestionFields', $questions);
        $this->parseLanguages('showVotingOptionFields', $answers);
    }

    /**
     * Return the values of the languages
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $pollID
     * @return      array
     */
    private function getLangValues($pollID) {
        global $objDatabase;

        $query = sprintf('
            SELECT
                `langID`,
                `title`,
                `question`
            FROM
                `%svoting_lang`
            WHERE
                `pollID` = %s',
            DBPREFIX, $pollID);

        $res = new DBIterator($objDatabase->execute($query));
        $titles = array();
        $questions = array();
        foreach ($res as $row) {
            $lang = $row['langID'];
            $titles[$lang] = $row['title'];
            $questions[$lang] = $row['question'];
        }

        return array($titles, $questions);
    }

    /**
     * Get the answers of a poll
     * 
     * @author      Stefan Heinemann <sh@adfinis.com>
     * @param       int $pollID
     * @return      array
     */
    private function getAnswers($pollID) {
        global $objDatabase;

        $query = sprintf('
            SELECT
                    `va`.`answer`,
                    `va`.`langID`
            FROM
                    `%svoting_answer`  AS `va`
            LEFT JOIN
                    `%svoting_results` AS `vr`
                ON
                    `va`.`resultID` = `vr`.`id`
            WHERE
                `vr`.`voting_system_id` = %s

            ORDER BY
                `id`
            ',
            DBPREFIX, DBPREFIX, $pollID);

        $res = new DBIterator($objDatabase->execute($query));

        $answers = array();
        foreach ($res as $row) {
            $answers[$row['langID']] = $answers[$row['langID']]."\n".$row['answer'];
        }

        return $answers;
    }

    function votingCode()
    {
        global $objDatabase, $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile('voting_code.html');

        $query= "SELECT `vs`.id,
                        `vs`.status,
                        UNIX_TIMESTAMP(`vs`.date) as datesec,
                        `vl`.question,
                        `vs`.votes
                   FROM ".DBPREFIX."voting_system AS `vs`
                   LEFT JOIN
                        `".DBPREFIX."voting_lang` AS `vl`
                    ON
                        `vl`.`pollID` = `vs`.`id`
                    AND
                        `vl`.`langID` = ".BACKEND_LANG_ID."
                  WHERE id=".intval($_GET['votingid']);

        $objResult = $objDatabase->Execute($query);
        if (!$objResult->EOF) {
            $votingId=$objResult->fields['id'];
            $votingTitle=stripslashes($objResult->fields['question']);
// TODO: Never used
//            $votingVotes=$objResult->fields['votes'];
            $votingDate=$objResult->fields['datesec'];
// TODO: Never used
//            $votingStatus=$objResult->fields['status'];
        } else {
            $this->errorHandling();
            return false;
        }

        $query = "
            SELECT 
                `vr`.id,
                `va`.`answer`,
                `vr`.votes 
            FROM 
                `".DBPREFIX."voting_results` AS `vr`
            LEFT JOIN
                `".DBPREFIX."voting_answer` AS `va`
            ON
                `va`.`resultID` = `vr`.`id`
            AND
                `va`.`langID` = ".BACKEND_LANG_ID."
            WHERE 
                `voting_system_id` = ".$votingId."
            ORDER BY 
                id";
        $objResult = $objDatabase->Execute($query);

        while (!$objResult->EOF) {
            $votingResultText .= '<input type="radio" name="votingoption" value="'.$objResult->fields['id'].'" />';
            $votingResultText .= $objResult->fields['answer']."<br />\n";
            $objResult->MoveNext();
        }

        $submitbutton= '<input type="submit" value="'.$_ARRAYLANG['TXT_SUBMIT'].'" name="Submit" />';

        $this->_objTpl->setVariable(array(
            'VOTING_TITLE'             => htmlentities($votingTitle, ENT_QUOTES, CONTREXX_CHARSET)." - ".showFormattedDate($votingDate),
            'VOTING_CODE'              => $_ARRAYLANG['TXT_VOTING_CODE'],
            'VOTING_RESULTS_TEXT'      => htmlentities($votingResultText, ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_SUBMIT'               => htmlentities($submitbutton, ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_SELECT_ALL'           => $_ARRAYLANG['TXT_SELECT_ALL']

        ));
        return true;
    }


    function errorHandling()
    {
        global $_ARRAYLANG;
        $this->strErrMessage.= " ".$_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']." ";
    }


    function export_additional_data()
    {
        global $objDatabase;

        // Figure out which fields we need to export here
        $voting_id = intval($_GET['votingid']);
        $sql = "
            SELECT
                additional_nickname AS nickname,
                additional_forename AS forename,
                additional_surname  AS surname ,
                additional_phone    AS phone   ,
                additional_street   AS street  ,
                additional_zip      AS zip     ,
                additional_city     AS city    ,
                additional_email    AS email   ,
                additional_comment  AS comment
            FROM ".DBPREFIX."voting_system
            WHERE id = $voting_id
        ";
        $res = $objDatabase->Execute($sql);

        $fields = array();
        foreach ($res->fields as $field => $enabled) {
            if ($enabled) $fields[] = $field;
        }

        // Check if we have anything to export at all
        if (!sizeof($fields)) {
            // No export fields defined. Don't do export.
            $_GET['act'] = '';
            $_GET['votingid'] = '';
            return $this->showCurrent();
        }

        // Now select those fields from our table.
        $fields_txt = join(',', $fields);
        #echo "exporting $fields_txt...\n";

        $sql_export = "
            SELECT $fields_txt
            FROM ".DBPREFIX."voting_additionaldata
            WHERE voting_system_id = $voting_id
            ORDER BY date_entered
            ";
        $data = $objDatabase->Execute($sql_export);
        header("Content-Type: text/csv");
        header("Content-Disposition: Attachment; filename=\"export.csv\"");
        while (!$data->EOF) {
            print($this->_format_csv($data->fields) . "\r\n");
            $data->MoveNext();
        }
        exit;
    }


    /**
     * Returns a line suitable to put in a CSV file.
     * @param list array    The list to be put in CSV.
     * @param separator string [optional] Separator, defaults to ";"
     */
    function _format_csv($list, $separator=';')
    {
        // First, fix the data values if they
        // contain newlines or the separator.
        $printable = array();
        foreach ($list as $elem) {
            if (preg_match("/$separator/", $elem) or preg_match('/[\r\n]/', $elem)) {
                $printable[] = '"' . $elem . '"';
            }
            else {
                $printable[] = $elem;
            }
        }
        return join($separator, $printable);
    }
}

if (!class_exists('DBIterator')) {

    /**
     * Iterator wrapper for adodb result objects
     *
     * @author      Stefan Heinemann <sh@adfinis.com>
     */
    class DBIterator implements Iterator {
        /**
         * The result object of adodb
         */
        private $obj;

        /**
         * If the result was empty
         *
         * (To prevent illegal object access)
         */
        private $empty;

        /**
         * The position in the rows
         *
         * Mainly just to have something to return in the
         * key() method. 
         */
        private $position = 0;

        /**
         * Assign the object
         *
         * @param       object (adodb result object)
         */
        public function __construct($obj) {
            $this->empty = !($obj instanceof ADORecordSet);

            $this->obj = $obj;
        }

        /**
         * Go back to first position
         */
        public function rewind() {
            if (!$this->empty) {
                    $this->obj->MoveFirst();
            }

            $this->position = 0;
        }

        /**
         * Return the current object
         *
         * @return      array
         */
        public function current() {
            return $this->obj->fields;
            // if valid return false, this function should never be called, 
            // so no problem with illegal access here i guess
        }

        /**
         * Return the current key
         *
         * @return      int
         */
        public function key() {
            return $this->position;
        }

        /**
         * Go to the next item
         */
        public function next() {
            if (!$this->empty) {
                    $this->obj->MoveNext();
            }
            
            ++$this->position;
        }

        /**
         * Return if there are any items left
         *
         * @return      bool
         */
        public function valid() {
            if ($this->empty) {
                    return false;
            }

            return !$this->obj->EOF;
        }
    }
}
