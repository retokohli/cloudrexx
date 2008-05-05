<?php
/**
 * Voting Module
 *
 * Functions for the Voting
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Leandro Nery <nery@astalavista.net>
 * @author      Ivan Schmid <ivan.schmid@comvation.com>
 * @version	   $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_voting
 * @todo        Edit PHP DocBlocks!
 */

function votingShowCurrent($page_content){
	global $objDatabase, $_CONFIG, $_ARRAYLANG, $_COOKIE;

	$paging = '';

	$objTpl = &new HTML_Template_Sigma('.');
	$objTpl->setErrorHandling(PEAR_ERROR_DIE);
	$objTpl->setTemplate($page_content);

	$votingId = intval($_GET['vid']);
	$msg = '';
	$voted = false;

	if ($_POST["votingoption"]){
		$voteId = intval($_POST["votingoption"]);

    	$query="SELECT voting_system_id from ".DBPREFIX."voting_results WHERE id=".$voteId;
		$objResult = $objDatabase->SelectLimit($query, 1);
        if (!$objResult->EOF){
        	$votingId = $objResult->fields["voting_system_id"];
        }

		$objVoting = $objDatabase->SelectLimit("SELECT submit_check FROM `".DBPREFIX."voting_system` WHERE `id`=".$votingId, 1);
    	if ($objVoting !== false && $objVoting->RecordCount() == 1) {
    		if ($objVoting->fields['submit_check'] == 'email') {
    			$email = contrexx_addslashes($_POST['votingemail']);
    			$objValidator = &new FWValidator();
    			if ($objValidator->isEmail($email)) {
        			if (!_alreadyVotedWithEmail($votingId, $email)) {
        				if (($msg = VotingSubmitEmail($votingId, $voteId, $email)) === true) {
        					$msg = '';
	        				$voted = true;
		        		} else {
        					$msg = $_ARRAYLANG['TXT_VOTING_NONEXISTENT_EMAIL'].'<br /><br />';
        				}
        			} else {
        				$msg = $_ARRAYLANG['TXT_VOTING_ALREADY_VOTED'].'<br /><br />';
        			}
    			} else {
    				$msg = $_ARRAYLANG['TXT_VOTING_INVALID_EMAIL_ERROR'].'<br /><br />';
    			}
    		} else {
    			VotingSubmit();
    			$voted = true;
    		}
    	}
	}

	if ($_GET['vid'] != '' && $_GET['act'] != 'delete'){
	    $query= "SELECT id, status, UNIX_TIMESTAMP(date) as datesec, question, votes, submit_check FROM ".DBPREFIX."voting_system where id=".intval($_GET['vid']);
	} else {
		$query= "SELECT id, status, UNIX_TIMESTAMP(date) as datesec, question, votes, submit_check  FROM ".DBPREFIX."voting_system where status=1";
	}

	$objResult = $objDatabase->Execute($query);

	if ($objResult->RecordCount() == 0) {
		// Only show old records when no voting is set available
	   $objTpl->setVariable(array(
	   			'VOTING_TITLE'					=> $_ARRAYLANG['TXT_VOTING_NOT_AVAILABLE'],
	   			'VOTING_DATE'					=> '',
				'VOTING_OLDER_TEXT'				=> '',
				'VOTING_OLDER_DATE'				=> '',
				'VOTING_PAGING'					=> '',
				'TXT_DATE'						=> '',
				'TXT_TITLE'						=> '',
				'VOTING_RESULTS_TEXT'			=> '',
				'VOTING_RESULTS_TOTAL_VOTES'	=> '',
				'VOTING_OLDER_TITLE'			=> $_ARRAYLANG['TXT_VOTING_OLDER'],
				'TXT_SUBMIT'					=> ''
			));

		/** start paging **/
		$query="SELECT id, UNIX_TIMESTAMP(date) as datesec, title, votes FROM ".DBPREFIX."voting_system order by id desc";
		$objResult = $objDatabase->SelectLimit($query, 5);
		$count = $objResult->RecordCount();
		$pos = intval($_GET[pos]);
		if ($count > intval($_CONFIG['corePagingLimit'])){
			$paging= getPaging($count, $pos, "&section=voting", "<b>".$_ARRAYLANG['TXT_VOTING_ENTRIES']."</b>", true);
		}
		/** end paging **/

		$query="SELECT id, UNIX_TIMESTAMP(date) as datesec, title, votes FROM ".DBPREFIX."voting_system order by id desc ";
		$objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);

		while (!$objResult->EOF) {
		    $votingid=$objResult->fields['id'];
			$votingTitle=stripslashes($objResult->fields['title']);
			$votingVotes=$objResult->fields['votes'];
			$votingDate=$objResult->fields['datesec'];

			if (($i % 2) == 0) {$class="row1";} else {$class="row2";}
			$objTpl->setVariable(array(
				'VOTING_OLDER_TEXT'		=> '<a href="?section=voting&vid='.$votingid.'" title="'.$votingTitle.'">'.$votingTitle.'</a>',
				'VOTING_OLDER_DATE'		=> showFormattedDate($votingDate),
				'VOTING_VOTING_ID'		=> $votingid,
				'VOTING_LIST_CLASS'		=> $class,
				'VOTING_PAGING'			=> $paging
			));
			$objTpl->parse("votingRow");
			$i++;
			$objResult->MoveNext();
		}
	} else {
		if (!$objResult->EOF) {
			$votingId 		= $objResult->fields['id'];
			$votingTitle	= stripslashes($objResult->fields['question']);
			$votingVotes	= $objResult->fields['votes'];
			$votingDate		= $objResult->fields['datesec'];
			$votingStatus	= $objResult->fields['status'];
			$votingMethod	= $objResult->fields['submit_check'];
			$objResult->MoveNext();
		} else {
    		errorHandling();
    	    return false;
    	}

		$images = 1;

		$query = "SELECT id, question, votes FROM ".DBPREFIX."voting_results WHERE voting_system_id='$votingId' ORDER BY id";
		$objResult = $objDatabase->Execute($query);

		while (!$objResult->EOF) {
			if ($votingStatus==1 && (($votingMethod == 'email' && !$voted) || ($votingMethod == 'cookie' && $_COOKIE['votingcookie']!='1'))){
				$votingResultText .="<input type='radio' name='votingoption' value='".$objResult->fields['id']."' ".($_POST["votingoption"] == $objResult->fields['id'] ? 'checked="checked"' : '')." /> ";
			    $votingResultText .= stripslashes($objResult->fields['question'])."<br />\n";
			}else {
				$votes=intval($objResult->fields['votes']);
				$percentage = 0;
				$imagewidth = 1; //Mozilla Bug if image width=0
				if($votes>0){
				    $percentage = (round(($votes/$votingVotes)*10000))/100;
				    $imagewidth = round($percentage,0);
				}
				$votingResultText .= stripslashes($objResult->fields['question'])."<br />\n";
				$votingResultText .= '<img src="images/modules/voting/'.$images.'.gif" width="'.$imagewidth.'%" height="10" />';
				$votingResultText .= "&nbsp;".$percentage."% (".$votes. " " .$_ARRAYLANG['TXT_VOTES'].")<br /><br />\n";
			}
			$objResult->MoveNext();
		}

		if ($votingStatus==1 && (($votingMethod == 'email' && !$voted) || ($votingMethod == 'cookie' && $_COOKIE['votingcookie']!='1'))){
			$votingVotes		= '';

			if ($votingMethod == 'email') {
				$objTpl->setVariable('VOTING_EMAIL', !empty($_POST['votingemail']) ? htmlentities($_POST['votingemail'], ENT_QUOTES) : '');
				$objTpl->parse('voting_email_input');
			} else {
				if ($objTpl->blockExists('voting_email_input')) {
					$objTpl->hideBlock('voting_email_input');
				}
			}

			$submitbutton	= '<input type="submit" value="'.$_ARRAYLANG['TXT_SUBMIT'].'" name="Submit" />';
		} else {
			if ($objTpl->blockExists('voting_email_input')) {
				$objTpl->hideBlock('voting_email_input');
			}

			$votingVotes	= $_ARRAYLANG['TXT_VOTING_TOTAL'].":	".$votingVotes;
			$submitbutton	='';
		}

		$objTpl->setVariable(array(
			'VOTING_MSG'					=> $msg,
			'VOTING_TITLE'					=> $votingTitle,
		    'VOTING_DATE'					=> showFormattedDate($votingDate),
			'VOTING_RESULTS_TEXT'			=> $votingResultText,
			'VOTING_RESULTS_TOTAL_VOTES'	=> $votingVotes,
			'VOTING_OLDER_TITLE'			=> $_ARRAYLANG['TXT_VOTING_OLDER'],
			'TXT_DATE'						=> $_ARRAYLANG['TXT_DATE'],
			'TXT_TITLE'						=> $_ARRAYLANG['TXT_TITLE'],
			'TXT_VOTES'						=> $_ARRAYLANG['TXT_VOTES'],
			'TXT_SUBMIT'					=> $submitbutton
			));

		// show other Poll entries

		/** start paging **/
		$query="SELECT id, UNIX_TIMESTAMP(date) as datesec, title, votes FROM ".DBPREFIX."voting_system WHERE id<>$votingId order by id desc";
		$objResult = $objDatabase->SelectLimit($query, 5);
		$count = $objResult->RecordCount();
		$pos = intval($_GET[pos]);
		if ($count>intval($_CONFIG['corePagingLimit'])){
			$paging= getPaging($count, $pos, "&section=voting", "<b>".$_ARRAYLANG['TXT_VOTING_ENTRIES']."</b>", true);
		}
		/** end paging **/

		$query="SELECT id, UNIX_TIMESTAMP(date) as datesec, title, votes FROM ".DBPREFIX."voting_system WHERE id<>$votingId order by id desc ";

		$objResult = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pos);

		$objTpl->setVariable(array(
			'VOTING_OLDER_TEXT'		=> '',
			'VOTING_OLDER_DATE'		=> '',
			'VOTING_VOTING_ID'		=> '',
			'VOTING_PAGING'			=> '',
			'TXT_DATE'				=> '',
			'TXT_TITLE'				=> ''
		));

		while (!$objResult->EOF) {
		    $votingid=$objResult->fields['id'];
			$votingTitle=stripslashes($objResult->fields['title']);
			$votingVotes=$objResult->fields['votes'];
			$votingDate=$objResult->fields['datesec'];

			if (($i % 2) == 0) {$class="row1";} else {$class="row2";}
			$objTpl->setVariable(array(
				'VOTING_OLDER_TEXT'		=> '<a href="?section=voting&vid='.$votingid.'" title="'.$votingTitle.'">'.$votingTitle.'</a>',
				'VOTING_OLDER_DATE'		=> showFormattedDate($votingDate),
				'VOTING_VOTING_ID'		=> $votingid,
				'VOTING_LIST_CLASS'		=> $class,
				'VOTING_PAGING'			=> $paging
			));
			$objTpl->parse("votingRow");
			$i++;
			$objResult->MoveNext();
		}
	}
	return $objTpl->get();

}

function VotingSubmit(){
	global $objDatabase, $_COOKIE;

	if ($_COOKIE['votingcookie'] != '1') {
		setcookie ("votingcookie", '1', time()+3600*24); // 1 Day
		$votingOption = intval($_POST["votingoption"]);

		$query="SELECT voting_system_id from ".DBPREFIX."voting_results WHERE id=".$votingOption." ";
		$objResult = $objDatabase->Execute($query);
	    if (!$objResult->EOF){
	    	$query="UPDATE ".DBPREFIX."voting_system set votes=votes+1,date=date WHERE id=".$objResult->fields["voting_system_id"]." ";
			$objDatabase->Execute($query);
	        $query="UPDATE ".DBPREFIX."voting_results set votes=votes+1 WHERE id=".$votingOption." ";
	        $objDatabase->Execute($query);
	    }
	    header("Location: ?section=voting");
	}
}

function VotingSubmitEmail($systemId, $voteId, $email, $emailValidated)
{
	global $objDatabase;

	$query="UPDATE ".DBPREFIX."voting_system set votes=votes+1,date=date WHERE id=".$systemId." ";
	$objDatabase->Execute($query);
    $query="UPDATE ".DBPREFIX."voting_results set votes=votes+1 WHERE id=".$voteId." ";
    $objDatabase->Execute($query);

	$objEmail = $objDatabase->SelectLimit("SELECT `id` FROM `".DBPREFIX."voting_email` WHERE `email` = '".$email."'");
	if ($objEmail !== false) {
		if ($objEmail->RecordCount() == 0) {
			if (($arrResponse = _verifyEmail($email)) !== false) {
				if ($arrResponse[0] == 250) {
					$emailValidated = 1;
				} else {
					$emailValidated = 0;
				}
    		} else {
				//return $_ARRAYLANG['TXT_VOTING_NONEXISTENT_EMAIL'].'<br /><br />';
				$emailValidated = 0;
			}

			if ($objDatabase->Execute("INSERT INTO `".DBPREFIX."voting_email` SET `email` = '".$email."', `valid` = '".$emailValidated."'") !== false) {
				$emailId = $objDatabase->Insert_ID();
			}
		} else {
			$emailId = $objEmail->fields['id'];
		}

		$objDatabase->Execute("INSERT INTO `".DBPREFIX."voting_rel_email_system` (`email_id`, `system_id`, `voting_id`) VALUES (".$emailId.", ".$systemId.", ".$voteId.")");
	}

	return true;
}

function setVotingResult($template)
{
	global $objDatabase, $_CONFIG, $_ARRAYLANG;
	$paging="";

	$objTpl = &new HTML_Template_Sigma('.');
	$objTpl->setErrorHandling(PEAR_ERROR_DIE);
	$objTpl->setTemplate($template);

	    $query= "SELECT id, status, UNIX_TIMESTAMP(date) as datesec, question, votes FROM ".DBPREFIX."voting_system where status=1";
	$objResult = $objDatabase->SelectLimit($query, 1);


	if (!$objResult->EOF) {
		$votingId=$objResult->fields['id'];
		$votingTitle=stripslashes($objResult->fields['question']);
		$votingVotes=$objResult->fields['votes'];

		$objResult->MoveNext();
	} else {
	    return '';
	}

	$images = 1;

	$query="SELECT id, question, votes FROM ".DBPREFIX."voting_results WHERE voting_system_id='$votingId' ORDER BY id";
	$objResult = $objDatabase->Execute($query);

	$votingResultText = '';
	while (!$objResult->EOF) {
		$votes=intval($objResult->fields['votes']);
		$percentage = 0;
		$imagewidth = 1; //Mozilla Bug if image width=0
		if($votes>0){
		    $percentage = (round(($votes/$votingVotes)*10000))/100;
		    $imagewidth = round($percentage,0);
		}
		$votingResultText .= stripslashes($objResult->fields['question'])."<br />\n";
		$votingResultText .= "<img src='images/modules/voting/$images.gif' width='$imagewidth%' height='10' />";
		$votingResultText .= "&nbsp;$votes ".$_ARRAYLANG['TXT_VOTES']." / $percentage %<br />\n";
		$objResult->MoveNext();
	}

	$votingVotes= $_ARRAYLANG['TXT_VOTING_TOTAL'].":	".$votingVotes;

	$objTpl->setVariable(array(
		'VOTING_RESULTS_TOTAL_VOTES'	=> $votingVotes,
		'VOTING_TITLE'	=> $votingTitle,
		'VOTING_RESULTS_TEXT'	=> $votingResultText
	));
	$objTpl->parse();
	//$objTpl->parse('voting_result');
	return $objTpl->get();
}

function _alreadyVotedWithEmail($voteingId, $email)
{
	global $objDatabase;

	$objEmail = $objDatabase->SelectLimit("SELECT 1 FROM `".DBPREFIX."voting_email` AS e INNER JOIN `".DBPREFIX."voting_rel_email_system` AS s ON s.email_id=e.id WHERE `email` = '".$email."' AND system_id=".$voteingId, 1);
	if ($objEmail !== false) {
		if ($objEmail->RecordCount() == 0) {
			return false;
		}
	}
	return true;
}

function _verifyEmail($email)
{
	if ($arrMxRRs = _getMXHosts($email)) {
		require_once ASCMS_LIBRARY_PATH.'/PEAR/Net/SMTP.php';

		foreach ($arrMxRRs as $arrMxRR) {
			if (!PEAR::isError($objSMTP = new Net_SMTP($arrMxRR['EXCHANGE'])) && !PEAR::isError($objSMTP->connect(2)) && !PEAR::isError($e = $objSMTP->vrfy($email))) {
				return $objSMTP->getResponse();
			}
		}

		return 0;
	}

	return false;
}

function _getMXHosts($email)
{
	require_once ASCMS_FRAMEWORK_PATH.'/MXLookup.class.php';

	$objMXLookup = &new MXLookup();

	$host = substr($email, strrpos($email, '@') + 1);

	if ($objMXLookup->getMailServers($host)) {
		return $objMXLookup->arrMXRRs;
	} else {
		return false;
	}
}
?>
