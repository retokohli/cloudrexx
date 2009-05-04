<?php
	/**
	 * Standalon search script - handles search in content pages and news article
	 *
	 * @author  <ben@comvation.com>
	 * @copyright Comvation AG <info@comvation.com>
	 */
	
	require_once("../../config/configuration.php");
	require_once("../../config/set_constants.php");
	require_once("../../core/validator.inc.php");
	require_once("../../core/init.class.php");
	require_once(ASCMS_LIBRARY_PATH.'/adodb/adodb.inc.php');
	require_once('../../core/API.php');
	
	$objDb       = ADONewConnection($_DBCONFIG['dbType']);
	@$objDb->Connect($_DBCONFIG['host'], $_DBCONFIG['user'], $_DBCONFIG['password'], $_DBCONFIG['database']);
	$objDatabase = $objDb;
	
	$objInit = new InitCMS();
	$objFWUser = FWUser::getFWUserObject();
	$_LANGID = $objInit->getFrontendLangId();

	if (strlen($_GET['st']) > 2) {
		//CONTENT
		$query="SELECT n.catid AS id,
		                    m.name AS section,
		                    n.cmd AS cmd,
		                    n.changelog AS date,
		                    c.id AS contentid,
		                    c.content AS content,
		                    c.title AS title,		                    
                      MATCH (content,title) AGAINST ('%.".htmlentities($_GET['st'], ENT_QUOTES, CONTREXX_CHARSET)."%') AS score
                       FROM ".DBPREFIX."content AS c,
                            ".DBPREFIX."content_navigation AS n,
                            ".DBPREFIX."modules AS m
                      WHERE (content LIKE ('%".htmlentities($_GET['st'], ENT_QUOTES, CONTREXX_CHARSET)."%')
                      	OR title LIKE ('%".$_GET['st']."%'))
                        ".(($_CONFIG['searchVisibleContentOnly'] == "on") ? "AND n.displaystatus = 'on'" : "")."
                        AND activestatus='1'
                        AND is_validated='1'
                        ".(
						!$objFWUser->objUser->login() ?
							// user is not authenticated
							($_CONFIG['coreListProtectedPages'] == 'off' ? 'AND n.protected=0' : '') :
							// user is authenticated
							(
								!$objFWUser->objUser->getAdminStatus() ?
									 // user is not administrator
									'AND (n.protected=0'.(count($objFWUser->objUser->getDynamicPermissionIds()) ? ' OR n.frontend_access_id IN ('.implode(', ', $objFWUser->objUser->getDynamicPermissionIds()).')' : '').')' :
									// user is administrator
									''
							)
						)."
						AND (n.startdate<=CURDATE() OR n.startdate='0000-00-00')
						AND (n.enddate>=CURDATE() OR n.enddate='0000-00-00')
                        AND n.module =m.id
                        AND n.catid = c.id
                        AND n.cmd = ''
                        AND n.lang=".$_LANGID;
						
		$objResult = $objDatabase->Execute($query);
		if ($objResult !== false) {
			while (!$objResult->EOF) {
				echo '<li><a href="index.php?page='.$objResult->fields['id'].'">» '.$objResult->fields['title'].'</a></li>';
				$objResult->MoveNext();
			}
		}		
		
		//NEWS
		$queryN		= "SELECT * FROM ".DBPREFIX."module_news WHERE text LIKE '%".$_GET['st']."%' OR title LIKE '%".$_GET['st']."%' OR teaser_text LIKE '%".$_GET['st']."%' AND lang=".$_LANGID." AND status=1 AND (startdate<=CURDATE() OR startdate='0000-00-00') AND (enddate>=CURDATE() OR enddate='0000-00-00')";
		$objResultN = $objDatabase->Execute($queryN);
		if ($objResultN !== false) {
			while (!$objResultN->EOF) {
				echo '<li><a href="index.php?section=news&cmd=details&amp;newsid='.$objResultN->fields['id'].'">» '.$objResultN->fields['title'].'</a></li>';
				$objResultN->MoveNext();
			}
		}		
	}
?>