<?php
/**
 * my Administrator manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version	   1.0.1
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * my Administrator manager
 *
 * Class to show the my admin pages
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version	   1.0.1
 * @package     contrexx
 * @subpackage  core
 */
class myAdminManager {
    var $statusMessage;
    var $pageTitle;


    function myAdminManager()
    {
    	$this->__construct();
    }

    /**
    * Constructor
    *
    * @param  string
    * @access public
    */
    function __construct()
    {
    	global $_CORELANG, $objTemplate;

        $objFWUser = FWUser::getFWUserObject();
        $objTemplate->setVariable('CONTENT_NAVIGATION', $_CORELANG['TXT_WELCOME_MESSAGE'].". ".$_CORELANG['TXT_LOGGED_IN_AS']."<a href='?cmd=access&amp;act=user&amp;tpl=modify&amp;id=".$objFWUser->objUser->getId()."' title='".$objFWUser->objUser->getId()."'>".($objFWUser->objUser->getProfileAttribute('firstname') || $objFWUser->objUser->getProfileAttribute('lastname') ? htmlentities($objFWUser->objUser->getProfileAttribute('firstname'), ENT_QUOTES, CONTREXX_CHARSET).' '.htmlentities($objFWUser->objUser->getProfileAttribute('lastname'), ENT_QUOTES, CONTREXX_CHARSET) : htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET))."</a>");
    }

    function getPage()
    {
    	global $_CORELANG, $objTemplate;

		if (!isset($_GET['act'])) {
		    $_GET['act']='';
		}

        switch($_GET['act']) {
		    case "test":
			    //$this->deleteLog();
                //$action = $this->showLogs();
			    break;

			default:
                $this->getHomePage();
			    break;
		}

		$objTemplate->setVariable(array(
			'CONTENT_TITLE'				=> $this->pageTitle,
    		'CONTENT_STATUS_MESSAGE'	=> trim($this->statusMessage)
    	));
    }

    function getHomePage()
    {
    	global $_CORELANG, $_CONFIG, $objTemplate, $objDatabase;

    	$objFWUser = FWUser::getFWUserObject();
    	$i = 0;
    	$dbErrorMsg = '';
    	$class = '';
        $this->pageTitle = $_CORELANG['TXT_HOME'];
        $objTemplate->addBlockfile('ADMIN_CONTENT', 'content', 'index_home.html');
    	$objTemplate->setVariable(array(
			'TXT_LOGGED_IN_AS'				=> htmlentities($_CORELANG['TXT_LOGGED_IN_AS'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_HOSTNAME'					=> htmlentities($_CORELANG['TXT_HOSTNAME'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_USERNAME'					=> htmlentities($_CORELANG['TXT_USERNAME'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_LOGTIME'					=> htmlentities($_CORELANG['TXT_LOGTIME'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_LAST_LOG_SESSIONS'			=> htmlentities($_CORELANG['TXT_LAST_LOG_SESSIONS'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_RELEASE_DATE'				=> htmlentities($_CORELANG['TXT_RELEASE_DATE'], ENT_QUOTES, CONTREXX_CHARSET),
			'INDEX_HOME_CMS_NAME'			=> htmlentities($_CONFIG['coreCmsName'], ENT_QUOTES, CONTREXX_CHARSET),
			'INDEX_HOME_CMS_VERSION'		=> htmlentities(str_replace(' Service Pack 0', '', preg_replace('#^(\d+\.\d+)\.(\d+)$#', '$1 Service Pack $2', $_CONFIG['coreCmsVersion'])), ENT_QUOTES, CONTREXX_CHARSET),
			'INDEX_HOME_CMS_STATUS'			=> htmlentities($_CONFIG['coreCmsStatus'], ENT_QUOTES, CONTREXX_CHARSET),
			'INDEX_HOME_CMS_CODENAME'		=> htmlentities($_CONFIG['coreCmsCodeName'], ENT_QUOTES, CONTREXX_CHARSET),
			'INDEX_HOME_CMS_RELEASEDATE'	=> htmlentities($_CONFIG['coreCmsReleaseDate'], ENT_QUOTES, CONTREXX_CHARSET),
			'INDEX_HOME_CMS_EDITION'		=> htmlentities($_CONFIG['coreCmsEdition'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_NEW_SITE' 					=> htmlentities($_CORELANG['TXT_NEW_PAGE'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_CONTENT_MANAGER' 			=> htmlentities($_CORELANG['TXT_CONTENT_MANAGER'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_MEDIA_MANAGER' 			=> htmlentities($_CORELANG['TXT_MEDIA_MANAGER'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_IMAGE_ADMINISTRATION'		=> htmlentities($_CORELANG['TXT_IMAGE_ADMINISTRATION'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_NEWS_MAMAGER' 				=> htmlentities($_CORELANG['TXT_NEWS_MANAGER'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_SITE_PREVIEW'				=> htmlentities($_CORELANG['TXT_SITE_PREVIEW'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_SKINS' 					=> htmlentities($_CORELANG['TXT_DESIGN_MANAGEMENT'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_USER_MANAGER' 				=> htmlentities($_CORELANG['TXT_USER_ADMINISTRATION'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_SERVER_INFO' 				=> htmlentities($_CORELANG['TXT_SERVER_INFO'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_STATS' 					=> htmlentities($_CORELANG['TXT_STATS'], ENT_QUOTES, CONTREXX_CHARSET),
			'TXT_WORKFLOW'					=> htmlentities($_CORELANG['TXT_WORKFLOW'], ENT_QUOTES, CONTREXX_CHARSET)
		));
		$objTemplate->setGlobalVariable('TXT_LOGOUT', $_CORELANG['TXT_LOGOUT']);

		$objFWUser = FWUser::getFWUserObject();
		$objResult = $objDatabase->SelectLimit(
			"SELECT datetime,
					remote_host
			   FROM ".DBPREFIX."log
			  WHERE userid = ".$objFWUser->objUser->getId()."
			  ORDER BY id DESC", 5);

		if ($objResult !== false) {
			while (!$objResult->EOF) {
				$objTemplate->setVariable(array(
				    'INDEX_HOME_ROWCLASS' 		=> (($i % 2) == 0) ? "row1" : "row2",
				    'INDEX_HOME_USERNAME'	 	=> htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET),
				    'INDEX_HOME_TIME' 		 	=> $objResult->fields['datetime'],
				    'INDEX_HOME_REMOTE_HOST'	=> $objResult->fields['remote_host']
				));
				$objTemplate->parse('logRow');
				$objResult->MoveNext();
				$i++;
			}
			$objTemplate->parse('content');
		}
    }
}
?>
