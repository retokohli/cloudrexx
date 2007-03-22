<?php
/**
 * Voting Module
 *
 * Functions for the Voting
 *
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Leandro Nery <nery@astalavista.net>
 * @author      Ivan Schmid <ivan.schmid@astalavista.ch>               
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

	if ($_POST["votingoption"]){
		VotingSubmit();
	}
	
	if ($_GET['vid'] != '' && $_GET['act'] != 'delete'){
	    $query= "SELECT id, status, UNIX_TIMESTAMP(date) as datesec, question, votes FROM ".DBPREFIX."voting_system where id=".intval($_GET['vid']);
	} else {
		$query= "SELECT id, status, UNIX_TIMESTAMP(date) as datesec, question, votes  FROM ".DBPREFIX."voting_system where status=1";
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
				'VOTING_OLDER_TEXT'		=> '<a href="?section=voting&vid="'.$votingid.'" title="'.$votingTitle.'">'.$votingTitle.'</a>',
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
			$objResult->MoveNext();
		} else {
    		errorHandling();
    	    return false;	
    	}
		
		$images = 1;					
								
		$query = "SELECT id, question, votes FROM ".DBPREFIX."voting_results WHERE voting_system_id='$votingId' ORDER BY id";
		$objResult = $objDatabase->Execute($query);
		
		while (!$objResult->EOF) {
			if ($_COOKIE['votingcookie'] != '1' && $votingStatus==1){
				$votingResultText .= '<input type="radio" name="votingoption" value="'.$objResult->fields['id'].'" /> ';	
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
				$votingResultText .= '&nbsp;'.$votes.' '.$_ARRAYLANG['TXT_VOTES'].' / '.$percentage.' %<br />'."\n";				
			}
			$objResult->MoveNext();
		}

		if ($_COOKIE['votingcookie'] != '1' && $votingStatus == 1){
		   $votingVotes		= '';
		   $submitbutton	= '<input type="submit" value="'.$_ARRAYLANG['TXT_SUBMIT'].'" name="Submit" />';
		}else {
		   $votingVotes		= $_ARRAYLANG['TXT_VOTING_TOTAL'].":	".$votingVotes;
		   $submitbutton	='';
		}		
					
		$objTpl->setVariable(array(
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
				'VOTING_OLDER_TEXT'		=> '<a href="?section=voting&vid="'.$votingid.'" title="'.$votingTitle.'">'.$votingTitle.'</a>',
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