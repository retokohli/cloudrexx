<?php

/**
 * my Administrator manager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.1
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * @ignore
 */
require_once(ASCMS_LIBRARY_PATH.'/PEAR/XML/RSS.class.php');

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


    /**
    * Constructor
    *
    * @param  string
    * @access public
    */
    function __construct()
    {
        global $_CORELANG, $objTemplate;

        if ($objUser = FWUser::getFWUserObject()->objUser->getUsers($filter = array('is_admin' => true, 'active' => true, 'last_activity' => array('>' => (time()-3600))))) {
            $arrAdministratorsOnline = array();
            $i = 0;
            while (!$objUser->EOF) {
                $arrAdministratorsOnline[$i]['id'] = $objUser->getId();
                $arrAdministratorsOnline[$i++]['username'] = $objUser->getUsername();
                $objUser->next();
            }
            $administratorsOnline = '';
            for ($i = 0; $i < count($arrAdministratorsOnline); $i++) {
                $administratorsOnline .= '<a href="index.php?cmd=access&amp;act=user&amp;tpl=modify&amp;id='.$arrAdministratorsOnline[$i]['id'].'">'.$arrAdministratorsOnline[$i]['username'].($i == (count($arrAdministratorsOnline)-1) ? '' : ',').'</a>';
            }
        }
        $objTemplate->setVariable('CONTENT_NAVIGATION', '<span id="administrators_online">'.$_CORELANG['TXT_ADMINISTSRATORS_ONLINE'].': </span>'.$administratorsOnline);
    }

    function getPage()
    {
        global $_CORELANG, $_CONFIG, $objTemplate;

        if (!isset($_GET['act'])) {
            $_GET['act']='';
        }

        switch($_GET['act']) {
            default:
                $this->getHomePage();
                break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'				=> 'Version: '.htmlentities($_CONFIG['coreCmsName'], ENT_QUOTES, CONTREXX_CHARSET).' '.htmlentities($_CONFIG['coreCmsEdition'], ENT_QUOTES, CONTREXX_CHARSET).' '.htmlentities(str_replace(' Service Pack 0', '', preg_replace('#^(\d+\.\d+)\.(\d+)$#', '$1 Service Pack $2', $_CONFIG['coreCmsVersion'])), ENT_QUOTES, CONTREXX_CHARSET).' '.htmlentities($_CONFIG['coreCmsStatus'], ENT_QUOTES, CONTREXX_CHARSET),
            'CONTENT_STATUS_MESSAGE'	=> trim($this->statusMessage),
        ));
    }

    function getHomePage()
    {
        global $_CORELANG, $_CONFIG, $objTemplate, $objDatabase;

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'content', 'index_home.html');


        $arrAccessIDs = array(5, 10, 76, '84_1', 6, 19, 75, '84_2', 17, 18, 7, 32, 21);
        foreach ($arrAccessIDs as $id) {
            $accessID = strpos($id, '_') ? substr($id, 0, strpos($id, '_')) : $id;
            if (Permission::checkAccess($accessID, 'static', true)) {
                $objTemplate->touchBlock('check_access_'.$id);
            } else {
                $objTemplate->hideBlock('check_access_'.$id);
            }
        }

        $objTemplate->setVariable(array(
            'TXT_LAST_LOGINS' 				=> htmlentities($_CORELANG['TXT_LAST_LOGINS'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_CONTREXX_NEWS' 			=> htmlentities($_CORELANG['TXT_CONTREXX_NEWS'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_CREATING_AND_PUBLISHING'   => htmlentities($_CORELANG['TXT_CREATING_AND_PUBLISHING'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_EVALUATE_AND_VIEW' 		=> htmlentities($_CORELANG['TXT_EVALUATE_AND_VIEW'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_MANAGE' 					=> htmlentities($_CORELANG['TXT_MANAGE'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_NEW_SITE' 					=> htmlentities($_CORELANG['TXT_NEW_PAGE'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_ADD_NEWS' 					=> htmlentities($_CORELANG['TXT_ADD_NEWS'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_ADD_BLOCK' 				=> htmlentities($_CORELANG['TXT_ADD_BLOCK'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_ADD_FORM' 					=> htmlentities($_CORELANG['TXT_ADD_FORM'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_SITE_PREVIEW'				=> htmlentities($_CORELANG['TXT_SITE_PREVIEW'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_CONTENT_MANAGER' 			=> htmlentities($_CORELANG['TXT_CONTENT_MANAGER'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_STATS' 					=> htmlentities($_CORELANG['TXT_STATS'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_WORKFLOW'					=> htmlentities($_CORELANG['TXT_WORKFLOW'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_FORMS' 					=> htmlentities($_CORELANG['TXT_FORMS'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_SYSTEM_SETTINGS' 			=> htmlentities($_CORELANG['TXT_SYSTEM_SETTINGS'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_USER_MANAGER' 				=> htmlentities($_CORELANG['TXT_USER_ADMINISTRATION'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_MEDIA_MANAGER' 			=> htmlentities($_CORELANG['TXT_MEDIA_MANAGER'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_IMAGE_ADMINISTRATION'		=> htmlentities($_CORELANG['TXT_IMAGE_ADMINISTRATION'], ENT_QUOTES, CONTREXX_CHARSET),
            'TXT_SKINS' 					=> htmlentities($_CORELANG['TXT_DESIGN_MANAGEMENT'], ENT_QUOTES, CONTREXX_CHARSET),
        ));
        $objTemplate->setGlobalVariable('TXT_LOGOUT', $_CORELANG['TXT_LOGOUT']);

        $objFWUser = FWUser::getFWUserObject();
        $objResult = $objDatabase->SelectLimit(
           'SELECT `logs`.`datetime`, `logs`.`remote_host`, `users`.`username`
            FROM `'.DBPREFIX.'log` as `logs` LEFT JOIN `'.DBPREFIX.'access_users` as `users` ON `users`.`id`=`logs`.`userid`
            WHERE `logs`.`userid` <> '.$objFWUser->objUser->getId().'
            ORDER BY `logs`.`id` DESC', 7);
        if ($objResult && $objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $objTemplate->setVariable(array(
                    'LOG_USERNAME'	 	=> htmlentities($objResult->fields['username'], ENT_QUOTES, CONTREXX_CHARSET),
                    'LOG_TIME' 		 	=> date('d.m.Y', strtotime($objResult->fields['datetime'])),
                ));
                $objTemplate->parse('logRow');
                $objResult->MoveNext();
            }
            $objTemplate->parse('logs');
        } else {
            $objTemplate->setVariable('LOG_ERROR_MESSAGE', $_CORELANG['TXT_NO_DATA_FOUND']);
        }

        $objRss = new XML_RSS('http://www.contrexx.com/feed/news_headlines_de.xml');
        $objRss->parse();
        $arrItems = $objRss->getItems();
        if (!empty($arrItems)) {
            $i = 0;
            foreach ($arrItems as $arrItem) {
                $objTemplate->setVariable(array(
                    'RSS_TITLE' => $arrItem['title'],
                    'RSS_LINK' => $arrItem['link'],
                ));
                $objTemplate->parse('rssRow');

                if (++$i > 5) {
                    break;
                }
            }
            $objTemplate->parse('rssFeeds');
        } else {
            $objTemplate->hideBlock('rssFeeds');
        }

        $objTemplate->parse('content');
    }
}
?>
