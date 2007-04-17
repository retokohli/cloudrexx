<?php
// Checks empty entries
function checkEntryData($var)
{
    return (trim($var)!="");
}

/**
 * Class voting manager
 *
 * Class for the voting system
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Contrexx Dev Team <thun@astalavista.ch>
 * @version	   1.1
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
    * Constructor
    *
    * @param  string
    * @access public
    */
    function votingmanager ()
    {
    	global $_ARRAYLANG, $objTemplate;


    	$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/voting/template');
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

			case "code":
				$action = $this->votingCode();
			break;
			default:
                $action = $this->showCurrent();
		}

		$objTemplate->setVariable(array(
			'CONTENT_OK_MESSAGE'		=> $this->strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->strErrMessage,
			'ADMIN_CONTENT'				=> $this->_objTpl->get()
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

    			$objDatabase->Execute('DELETE FROM `'.DBPREFIX.'voting_rel_email_system` WHERE `email_id` = '.intval($_GET['delete']));
    			$objDatabase->Execute('DELETE FROM `'.DBPREFIX.'voting_email` WHERE `id` = '.intval($_GET['delete']));
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
			'VOTING_POS'	=> $pos,
			'VOTING_ID'		=> $systemId,
			'TXT_VOTING_FUNCTIONS'					=> $_ARRAYLANG['TXT_VOTING_FUNCTIONS'],
			'TXT_VOTING_EMAIL_ADRESSE_OF_QUESTION'	=> sprintf($_ARRAYLANG['TXT_VOTING_EMAIL_ADRESSE_OF_QUESTION'], htmlentities($title, ENT_QUOTES).' ('.htmlentities($question, ENT_QUOTES).')'),
			'TXT_VOTING_EMAIL'						=> $_ARRAYLANG['TXT_VOTING_EMAIL'],
			'TXT_VOTING_VALID'					=> $_ARRAYLANG['TXT_VOTING_VALID'],
			'TXT_VOTING_FUNCTIONS'					=> $_ARRAYLANG['TXT_VOTING_FUNCTIONS'],
			'TXT_VOTING_CONFIRM_DELETE_EMAIL'		=> $_ARRAYLANG['TXT_VOTING_CONFIRM_DELETE_EMAIL'],
			'TXT_VOTING_CONFIRM_VERIFY_EMAIL'		=> $_ARRAYLANG['TXT_VOTING_CONFIRM_VERIFY_EMAIL']
		));

		$this->_objTpl->setGlobalVariable(array(
			'TXT_VOTING_VERIFY_EMAIL'				=> $_ARRAYLANG['TXT_VOTING_VERIFY_EMAIL'],
			'TXT_VOTING_DELETE_EMAIL'				=> $_ARRAYLANG['TXT_VOTING_DELETE_EMAIL'],
			'TXT_VOTING_WRITE_EMAIL'				=> $_ARRAYLANG['TXT_VOTING_WRITE_EMAIL']
		));

		$objMails = $objDatabase->SelectLimit('SELECT e.id,e.email,e.valid FROM `'.DBPREFIX.'voting_rel_email_system` AS s INNER JOIN `'.DBPREFIX.'voting_email` AS e ON e.id=s.email_id WHERE s.system_id='.$systemId.' ORDER BY e.email', $_CONFIG['corePagingLimit'], $pos);
		if ($objMails !== false) {
			$row = 1;
			while (!$objMails->EOF) {
				$this->_objTpl->setVariable(array(
					'VOTING_ROW_NR'		=> $row = $row % 2 == 1 ? 2 : 1,
					'VOTING_EMAIL'		=> htmlentities($objMails->fields['email'], ENT_QUOTES),
					'VOTING_EMAIL_ID'	=> $objMails->fields['id'],
					'VOTING_VALID'	=> $objMails->fields['valid'] == '1' ? '<img src="images/icons/check_mark.gif" width="16" height="16" alt="'.$_ARRAYLANG['TXT_VOTING_EMAIL_IS_VAILD'].'" />' : '<img src="images/icons/question_mark.gif" width="16" height="16" alt="'.$_ARRAYLANG['TXT_VOTING_EMAIL_ISNT_VAILD'].'" />'
				));

				if ($objMails->fields['valid'] == '1') {
					$this->_objTpl->hideBlock('voting_verify_email');
				} else {
					$this->_objTpl->touchBlock('voting_verify_email');
				}

				$objMails->MoveNext();

				$this->_objTpl->parse('voting_emails');
			}
		}
    }

    function showCurrent()
    {
    	global $objDatabase, $_ARRAYLANG;

    	$this->_objTpl->loadTemplateFile('voting_results.html');

    	$query = "SELECT COUNT(1) as `count` FROM ".DBPREFIX."voting_system";
    	$objResult = $objDatabase->Execute($query);
    	if ($objResult) {
    		$totalrows = $objResult->fields['count'];
    	}

    	$votingId = ((!isset($_GET['act']) || $_GET['act'] != "delete") && isset($_GET['votingid'])) ? intval($_GET['votingid']) : 0;

   	    $query= "SELECT id, UNIX_TIMESTAMP(date) as datesec, question, votes FROM ".DBPREFIX."voting_system where ".($votingId > 0 ? "id=".$votingId : "status=1");
		$objResult = $objDatabase->SelectLimit($query, 1);
		if ($objResult->RecordCount()==0 && $totalrows==0) {
		   header("Location: ?cmd=voting&act=add");
		   exit;
		} else {
			$votingId=$objResult->fields['id'];
			$votingTitle=stripslashes($objResult->fields['question']);
			$votingVotes=$objResult->fields['votes'];
			$votingDate=$objResult->fields['datesec'];

			$images = 1;
			$query="SELECT id, question, votes FROM ".DBPREFIX."voting_results WHERE voting_system_id='$votingId' ORDER BY id";
			$objResult = $objDatabase->Execute($query);

			$votingResultText = '';
			while (!$objResult->EOF) {
				$votes=intval($objResult->fields['votes']);
				$percentage = 0;
				$imagewidth = 1; //Mozilla Bug if image width=0
				if($votes>0) {
				    $percentage = (round(($votes/$votingVotes)*10000))/100;
				    $imagewidth = round($percentage,0);
				}
				$votingResultText .= stripslashes($objResult->fields['question'])."<br />\n";
				$votingResultText .= "<img src='images/icons/$images.gif' width='$imagewidth%' height=\"10\" alt=\"$votes ".$_ARRAYLANG['TXT_VOTES']." / $percentage %\" />";
				$votingResultText .= "&nbsp;<font size='1'>$votes ".$_ARRAYLANG['TXT_VOTES']." / $percentage %</font><br />\n";
				$objResult->MoveNext();
			}

			$this->_objTpl->setVariable(array(
			    'VOTING_TITLE'               => $votingTitle,
			    'VOTING_DATE'                => showFormattedDate($votingDate),
				'VOTING_RESULTS_TEXT'	       => $votingResultText,
				'VOTING_RESULTS_TOTAL_VOTES' => $votingVotes,
				'VOTING_TOTAL_TEXT'          => $_ARRAYLANG['TXT_VOTING_TOTAL'],
				'TXT_DATE'                   => $_ARRAYLANG['TXT_DATE'],
				'TXT_TITLE'                  => $_ARRAYLANG['TXT_TITLE'],
				'TXT_VOTES'                  => $_ARRAYLANG['TXT_VOTES'],
				'TXT_ACTION'	               => $_ARRAYLANG['TXT_ACTION'],
				'TXT_ACTIVATION'             => $_ARRAYLANG['TXT_ACTIVATION'],
				'TXT_CREATE_HTML'		       => $_ARRAYLANG['TXT_CREATE_HTML'],
				'TXT_CONFIRM_DELETE_DATA'    => $_ARRAYLANG['TXT_CONFIRM_DELETE_DATA'],
				'TXT_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_ACTION_IS_IRREVERSIBLE']
			));

			$this->_objTpl->setGlobalVariable('TXT_HTML_CODE', $_ARRAYLANG['TXT_HTML_CODE']);

			// show other Voting entries
			$query="SELECT id,status,submit_check, UNIX_TIMESTAMP(date) as datesec, title, votes FROM ".DBPREFIX."voting_system order by id desc";
			$objResult = $objDatabase->Execute($query);

			$i = 0;
			while(!$objResult->EOF) {
			    $votingid=$objResult->fields['id'];
				$votingTitle=stripslashes($objResult->fields['title']);
				$votingVotes=$objResult->fields['votes'];
				$votingDate=$objResult->fields['datesec'];
				$votingStatus=$objResult->fields['status'];

				if ($votingStatus==0) {
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
					'VOTING_OLDER_TEXT'	   => "<a href='?cmd=voting&amp;votingid=$votingid'>".$votingTitle."</a>",
					'VOTING_OLDER_DATE'      => showFormattedDate($votingDate),
					'VOTING_OLDER_VOTES'     => ($votingVotes > 0 && $objResult->fields['submit_check'] == 'email') ? '<a href="?cmd=voting&amp;act=detail&amp;id='.$votingid.'" title="'.$_ARRAYLANG['TXT_VOTING_SHOW_EMAIL_ADRESSES'].'">'.$votingVotes.'</a>' : $votingVotes,
					'VOTING_ID'              => $votingid,
					'VOTING_LIST_CLASS'      => $class,
					'VOTING_RADIO'           => "<input type='radio' name='voting_selected' value='radiobutton'".$radio
				));
				$this->_objTpl->parse("votingRow");
				$i++;
				$objResult->MoveNext();
			}
		}
	}

    function votingAddSubmit()
    {
    	global $objDatabase, $_ARRAYLANG;

    	if (empty($_POST['votingquestion']) || empty($_POST['votingname'])) return false;
    	$options= explode ("\n", $_POST['votingoptions']);

    	if (count(array_filter($options,'checkEntryData'))<2) {
        	return false;
    	}

    	$method = isset($_POST['votingRestrictionMethod']) ? contrexx_addslashes($_POST['votingRestrictionMethod']) : 'cookie';

    	$query="UPDATE ".DBPREFIX."voting_system set status=0,date=date";
		$objDatabase->Execute($query);
       	$query="INSERT INTO ".DBPREFIX."voting_system (title,question,status,submit_check,votes)  values ('".htmlspecialchars(addslashes($_POST['votingname']), ENT_QUOTES, CONTREXX_CHARSET)."','".htmlspecialchars(addslashes($_POST['votingquestion']), ENT_QUOTES, CONTREXX_CHARSET)."','1','".$method."','0')";
		$objDatabase->Execute($query);
    	$query = "SELECT MAX(id) as max_id FROM ".DBPREFIX."voting_system";
    	$objResult = $objDatabase->Execute($query);
        if (!$objResult->EOF) {
        	$latestid=$objResult->fields["max_id"];
        } else {
    		$this->errorHandling();
    		$this->strErrMessage = $_ARRAYLANG['TXT_SUBMIT_ERROR'];
    	    return false;
    	}

    	for ($i=0;$i<count($options);$i++) {
           $query="INSERT INTO ".DBPREFIX."voting_results (voting_system_id,question,votes)  values ($latestid,'".htmlspecialchars(addslashes(trim($options[$i])), ENT_QUOTES, CONTREXX_CHARSET)."',0)";
           if (trim($options[$i])!="") {
           		$objDatabase->Execute($query);
           }
    	}
    	$this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_STORED_SUCCESSFUL'];
    	return true;
	}

	function votingEditSubmit()
    {
    	global $objDatabase,$_ARRAYLANG;
		$deleted_votes=0;

    	if (empty($_POST['votingquestion']) || empty($_POST['votingname'])) {
    		return false;
    	}

    	$options= explode ("\n", $_POST['votingoptions']);
    	$optionsid= explode (";", $_POST['votingresults']);
    	$looptimes=max(count($options),count($optionsid));
    	$method = isset($_POST['votingRestrictionMethod']) ? contrexx_addslashes($_POST['votingRestrictionMethod']) : 'cookie';

    	if (count(array_filter($options,"checkEntryData"))<2) {
        	return false;
    	}

    	for ($i=0;$i<$looptimes;$i++) {
    		if (trim($options[$i])!="") {
            	if ($optionsid[$i]!="") {
            		$query="UPDATE ".DBPREFIX."voting_results set question='".htmlspecialchars(addslashes(trim($options[$i])), ENT_QUOTES, CONTREXX_CHARSET)."' WHERE id='".intval($optionsid[$i])."'";
		    		$objDatabase->Execute($query);
            	} else {
 					$query="INSERT INTO ".DBPREFIX."voting_results (voting_system_id,question,votes) values ('".intval($_POST['votingid'])."','".htmlspecialchars(addslashes(trim($options[$i])), ENT_QUOTES, CONTREXX_CHARSET)."',0)";
            		$objDatabase->Execute($query);
            	}
            } elseif ($optionsid[$i]!="") {
            	$query="SELECT votes FROM ".DBPREFIX."voting_results WHERE id='".intval($optionsid[$i])."'";
            	$objDatabase->Execute($query);
            	if (!$objResult->EOF) {
            		$deleted_votes=$objResult->fields["votes"];
            	}

 				$query="DELETE FROM ".DBPREFIX."voting_results WHERE id='".intval($optionsid[$i])."'";
            	$objDatabase->Execute($query);
            }
    	}

    	$query="UPDATE ".DBPREFIX."voting_system set date=date, title='".htmlspecialchars(addslashes($_POST['votingname']), ENT_QUOTES, CONTREXX_CHARSET)."',question='".htmlspecialchars(addslashes($_POST['votingquestion']), ENT_QUOTES, CONTREXX_CHARSET)."',votes=votes-".$deleted_votes.", submit_check='".$method."' WHERE id='".intval($_POST['votingid'])."'";
		if ($objDatabase->Execute($query)) {
			$this->strOkMessage = $_ARRAYLANG['TXT_DATA_RECORD_STORED_SUCCESSFUL'];
    		return true;
		} else {
			$this->strErrMessage = $_ARRAYLANG['TXT_SUBMIT_ERROR'];
			return false;
		}
	}

	function votingDelete()
    {
    	global $objDatabase;

    	// Case when deleting the status active, it has to set another
		$query="SELECT id FROM ".DBPREFIX."voting_system WHERE status=1";
		$objResult = $objDatabase->Execute($query);
		if(!$objResult->EOF && $_GET['votingid']==$objResult->fields["id"]) {
			$query="SELECT  MAX(id) as maxid FROM ".DBPREFIX."voting_system WHERE status=0";
			$objResult = $objDatabase->query($query);
			if(!$objResult->EOF) {
			   $maxid=$objResult->fields["maxid"];
			   if (!is_null($maxid)) {
			   		$query="UPDATE ".DBPREFIX."voting_system set status=1,date=date WHERE id=$maxid";
		       		$objDatabase->Execute($query);
			   }
			}

		}

		$objDatabase->Execute("DELETE FROM `".DBPREFIX."voting_rel_email_system` WHERE system_id=".intval($_GET['votingid']));
		$this->_cleanUpEmails();

      	$query="DELETE FROM ".DBPREFIX."voting_system WHERE id=".intval($_GET['votingid'])." ";
		$objDatabase->Execute($query);
  		$query="DELETE FROM ".DBPREFIX."voting_results WHERE voting_system_id=".intval($_GET['votingid'])." ";
		$objDatabase->Execute($query);
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
			'TXT_VOTING_METHOD_OF_RESTRICTION_TXT'	=> $_ARRAYLANG['TXT_VOTING_METHOD_OF_RESTRICTION_TXT'],
			'TXT_VOTING_COOKIE_BASED'				=> $_ARRAYLANG['TXT_VOTING_COOKIE_BASED'],
			'TXT_VOTING_EMAIL_BASED'				=> $_ARRAYLANG['TXT_VOTING_EMAIL_BASED'],
		    'TXT_VOTING_ADD'						=> $_ARRAYLANG['TXT_VOTING_ADD'],
			'TXT_NAME'								=> $_ARRAYLANG['TXT_NAME'],
			'TXT_VOTING_QUESTION'					=> $_ARRAYLANG['TXT_VOTING_QUESTION'],
			'TXT_VOTING_ADD_OPTIONS'				=> $_ARRAYLANG['TXT_VOTING_ADD_OPTIONS'],
			'TXT_STORE'								=> $_ARRAYLANG['TXT_STORE'],
			'TXT_RESET'								=> $_ARRAYLANG['TXT_RESET']
		));
	}

	function votingEdit()
    {
    	global $objDatabase, $_ARRAYLANG;

    	$this->_objTpl->loadTemplateFile('voting_edit.html');
    	$query="SELECT * FROM ".DBPREFIX."voting_system where id=".intval($_GET['votingid'])." ";
		$objResult = $objDatabase->Execute($query);
		if(!$objResult->EOF) {
			$votingname=stripslashes($objResult->fields["title"]);
			$votingquestion=stripslashes($objResult->fields["question"]);
			$votingid=$objResult->fields["id"];
			$votingmethod = $objResult->fields['submit_check'];
		}

		$query="SELECT question,id FROM ".DBPREFIX."voting_results WHERE voting_system_id='$votingid' ORDER BY id";
		$objResult = $objDatabase->Execute($query);
		$i=0;
		while (!$objResult->EOF) {
			$votingoptions .= stripslashes($objResult->fields['question'])."\n";
			$voltingresults[$i]=$objResult->fields['id'];
			$i++;
			$objResult->MoveNext();
		}

		$this->_objTpl->setVariable(array(
			'TXT_VOTING_METHOD_OF_RESTRICTION_TXT'	=> $_ARRAYLANG['TXT_VOTING_METHOD_OF_RESTRICTION_TXT'],
			'TXT_VOTING_COOKIE_BASED'				=> $_ARRAYLANG['TXT_VOTING_COOKIE_BASED'],
			'TXT_VOTING_EMAIL_BASED'				=> $_ARRAYLANG['TXT_VOTING_EMAIL_BASED'],
			'VOTING_METHOD_OF_RESTRICTION_COOKIE'	=> $votingmethod == 'cookie' ? 'checked="checked"' : '',
			'VOTING_METHOD_OF_RESTRICTION_EMAIL'	=> $votingmethod == 'email' ? 'checked="checked"' : '',
		    'TXT_VOTING_EDIT'    				    => $_ARRAYLANG['TXT_VOTING_EDIT'],
			'TXT_NAME'	        					=> $_ARRAYLANG['TXT_NAME'],
			'TXT_VOTING_QUESTION'  					=> $_ARRAYLANG['TXT_VOTING_QUESTION'],
			'TXT_VOTING_ADD_OPTIONS' 				=> $_ARRAYLANG['TXT_VOTING_ADD_OPTIONS'],
			'TXT_STORE'          				    => $_ARRAYLANG['TXT_STORE'],
			'TXT_RESET'         				    => $_ARRAYLANG['TXT_RESET'],
			'EDIT_NAME'								=> $votingname,
			'EDIT_QUESTION'							=> $votingquestion,
			'EDIT_OPTIONS'							=> $votingoptions,
			'VOTING_ID'								=> $votingid,
			'VOTING_RESULTS'						=> implode($voltingresults,";")
		));
	}

	function votingCode()
    {
    	global $objDatabase, $_ARRAYLANG;

    	$this->_objTpl->loadTemplateFile('voting_code.html');

    	$query= "SELECT id,
    	                status,
    	                UNIX_TIMESTAMP(date) as datesec,
    	                question,
    	                votes
    	           FROM ".DBPREFIX."voting_system
    	          WHERE id=".intval($_GET['votingid']);

		$objResult = $objDatabase->Execute($query);
		if (!$objResult->EOF) {
			$votingId=$objResult->fields['id'];
			$votingTitle=stripslashes($objResult->fields['question']);
			$votingVotes=$objResult->fields['votes'];
			$votingDate=$objResult->fields['datesec'];
			$votingStatus=$objResult->fields['status'];
		} else {
	    	$this->errorHandling();
	        return false;
	    }

		$query="SELECT id, question, votes FROM ".DBPREFIX."voting_results WHERE voting_system_id='$votingId' ORDER BY id";
		$objResult = $objDatabase->Execute($query);

		while (!$objResult->EOF) {
			$votingResultText .= '<input type="radio" name="votingoption" value="'.$objResult->fields['id'].'" />';
		    $votingResultText .= $objResult->fields['question']."<br />\n";
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
	}


    function errorHandling()
    {
    	global $_ARRAYLANG;
        $this->strErrMessage.= " ".$_ARRAYLANG['TXT_DATABASE_QUERY_ERROR']." ";
    }

}
?>
