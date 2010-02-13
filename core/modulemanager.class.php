<?php
/**
 * Modulemanager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Modulemanager
 *
 * This class manages the CMS Modules
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 */
class modulemanager
{
    public $strErrMessage = '';
    public $strOkMessage = '';
    public $arrayInstalledModules = array();
    public $arrayRemovedModules = array();
    public $langId;
    public $defaultOrderValue = 111;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->langId = FRONTEND_LANG_ID;
    }


    function getModulesPage()
    {
        global $_CORELANG, $objTemplate;

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'      => $_CORELANG['TXT_MODULE_MANAGER'],
            'CONTENT_NAVIGATION' => "<a href='index.php?cmd=modulemanager'>".$_CORELANG['TXT_MODULE_MANAGER']."</a>"
                                     //<a href='index.php?cmd=modulemanager&act=manage'>[".$_CORELANG['TXT_MODULE_ACTIVATION']."]</a>"
        ));

        $objTemplate->addBlockfile('ADMIN_CONTENT', 'module_manager', 'module_manager.html');

        $objTemplate->setVariable(array(
            'TXT_NAME'                   => $_CORELANG['TXT_NAME'],
            'TXT_CONFIRM_DELETE_DATA'    => $_CORELANG['TXT_CONFIRM_DELETE_DATA'],
            'TXT_ACTION_IS_IRREVERSIBLE' => $_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_DESCRIPTION'            => $_CORELANG['TXT_DESCRIPTION'],
            'TXT_STATUS'                 => $_CORELANG['TXT_STATUS'],
            'TXT_INSTALL_MODULE'         => $_CORELANG['TXT_INSTALL_MODULE'],
            'TXT_PROVIDE_MODULE'         => $_CORELANG['TXT_PROVIDE_MODULE'],
            'TXT_REMOVE_MODULE'          => $_CORELANG['TXT_REMOVE_MODULE'],
            'TXT_ACCEPT_CHANGES'         => $_CORELANG['TXT_ACCEPT_CHANGES'],
            'TXT_APPLICATION' => $_CORELANG['TXT_APPLICATION']
        ));

        if (!isset($_GET['act'])) {
            $_GET['act'] = '';
        }

        switch($_GET['act']) {
            case "manage":
                Permission::checkAccess(51, 'static');
                $this->manageModules();
                break;
            case "edit":
                Permission::checkAccess(52, 'static');
                $this->modModules();
                $this->showModules();
                break;
            default:
                Permission::checkAccess(23, 'static');
                $this->showModules();
                break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->strErrMessage,
        ));
    }


    function getModules()
    {
        global $objDatabase;

        $arrayInstalledModules = array();
        $objResult = $objDatabase->Execute("
            SELECT module
              FROM ".DBPREFIX."content_navigation
             WHERE module!=0
               AND `lang`=$this->langId
             GROUP BY module
        ");
        if ($objResult) {
            $i = 0;
            while (!$objResult->EOF) {
                $arrayInstalledModules[$i] = $objResult->fields['module'];
                ++$i;
                $objResult->MoveNext();
            }
        }
        return $arrayInstalledModules;
    }


    function showModules()
    {
        global $objDatabase, $_CORELANG, $objTemplate;

        $objTemplate->setVariable('MODULE_ACTION', 'edit');

/*
        $objResult = $objDatabase->Execute("
            SELECT module, `lang`
              FROM ".DBPREFIX."content_navigation
             WHERE module!=0
             GROUP BY module");
        while(!$objResult->EOF){
// TODO: This whole array is never used!
//            $arrAvailableModulesInLang[$objResult->fields['lang']][] = $objResult->fields['module'];
            $objResult->MoveNext();
        }
*/

        $arrayInstalledModules = $this->getModules();
        $query = "
            SELECT id, name, description_variable, is_core, is_required
              FROM ".DBPREFIX."modules
             WHERE status='y'
             ORDER BY is_required DESC, name ASC";
        $i = 0;
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            while (!$objResult->EOF) {
                $class = (++$i % 2 ? 'row1' : 'row2');
                if (   in_array($objResult->fields['id'], $arrayInstalledModules)
                    || $objResult->fields['id'] == 6) {
                    $objTemplate->setVariable(array(
                        'MODULE_REMOVE'  => "<input type='checkbox' name='removeModule[".$objResult->fields['id']."]' value='0' />",
                        'MODULE_INSTALL' => "&nbsp;",
                        'MODULE_STATUS'  => "<img src='images/icons/led_green.gif' alt='' />"
                    ));
                } else  {
                    $objTemplate->setVariable(array(
                        'MODULE_INSTALL' => "<input type='checkbox' name='installModule[".$objResult->fields['id']."]' value='1' />",
                        'MODULE_REMOVE'  => "&nbsp;",
                        'MODULE_STATUS'  => "<img src='images/icons/led_red.gif' alt='' />"
                    ));
                }

                /*
                // core Modules
                if ($db->f('is_core')==1) {
                    $objTemplate->setVariable("MODULE_NAME", $db->f('name')." (core)");
                } else {
                    $objTemplate->setVariable("MODULE_NAME", $db->f('name'));
                }
                */

                $objTemplate->setVariable('MODULE_NAME', $objResult->fields['name']);

                // Required Modules
                if ($objResult->fields['is_required'] == 1) {
                    $class = 'highlighted';
                    $objTemplate->setVariable(array(
                        'MODULE_REQUIRED' => $_CORELANG['TXT_REQUIRED'],
                        'MODULE_REMOVE'   => '&nbsp;'
                    ));
                } else {
                    $objTemplate->setVariable('MODULE_REQUIRED', $_CORELANG['TXT_OPTIONAL']);
                }

                $objTemplate->setVariable(array(
                    'MODULE_ROWCLASS'   => $class,
                    'MODULE_DESCRIPTON' => $_CORELANG[$objResult->fields['description_variable']],
                    'MODULE_ID'         => $objResult->fields['id']
                ));
                $objTemplate->parse('moduleRow');
                $objResult->MoveNext();
            }
        }
    }


    function modModules()
    {
        global $_CORELANG;
        if ($this->installModules()) {
            $installedModules = '';
            foreach (array_keys($this->arrayInstalledModules) as $moduleName) {
                $installedModules .=
                    (empty($installedModules) ? '' : ', ').$moduleName;
            }
            $this->strOkMessage .= sprintf($_CORELANG['TXT_MODULES_INSTALLED_SUCCESFULL'], $installedModules);
        }
        if ($this->removeModules()) {
            $removedModules = '';
            foreach (array_keys($this->arrayRemovedModules) as $moduleName) {
                $removedModules .=
                    (empty($removedModules) ? '' : ', ').$moduleName;
            }
            $this->strOkMessage .= ' '.sprintf($_CORELANG['TXT_MODULES_REMOVED_SUCCESSFUL'], $removedModules);
        }
    }


    function installModules()
    {
        global $objDatabase, $_CORELANG, $objInit;

        $i = 1;
        if (empty($_POST['installModule']) || !is_array($_POST['installModule'])) {
            return false;
        }
        $currentTime = time();
        $paridarray = array();
        foreach (array_keys($_POST['installModule']) as $module_id) {
            $module_id = intval($module_id);
            $alreadyexist = false;
            $objResult = $objDatabase->Execute("
                SELECT name
                  FROM ".DBPREFIX."modules
                 WHERE id=$module_id
            ");
            if ($objResult) {
                if (!$objResult->EOF) {
                    $module_name = $objResult->fields['name'];
                }
            } else {
                $this->errorHandling();
                return false;
            }

            $q_check_repo_lang = "
                SELECT count(lang) as langcount, `lang`
                  FROM ".DBPREFIX."module_repository
                 WHERE moduleid=$module_id
                 GROUP BY `lang`
                HAVING langcount>0
                 ORDER BY langcount ASC
            ";
            $objResult = $objDatabase->Execute($q_check_repo_lang);
            // figure out what repository langid to use and store
            // it in $repo_lang_id.
            // Start with the default language, so it is initialized
            // in case there are no records
            $repo_lang_id = $objInit->defaultFrontendLangId;
            while ($objResult && !$objResult->EOF) {
                $repo_lang_id = $objResult->fields['lang'];
                // preference in this order: current language id, default id, lowest id
                if ($this->langId                   == $repo_lang_id) break;
                if ($objInit->defaultFrontendLangId == $repo_lang_id) break;
                // lowest id is the last, so we just loop till the
                // end (or until we find something better).
                $objResult->MoveNext();
            }
            unset($objResult);
            $query = "
                SELECT *
                  FROM ".DBPREFIX."module_repository
                 WHERE moduleid=$module_id
                   AND `lang`=$repo_lang_id
                 ORDER BY parid ASC";
            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                while (!$objResult->EOF) {
                    if (isset($paridarray[$objResult->fields['parid']])) {
                        $parcat = $paridarray[$objResult->fields['parid']];
                    } else {
                        if (!$alreadyexist) {
                            $objResult2 = $objDatabase->Execute("
                                SELECT catid
                                  FROM ".DBPREFIX."content_navigation
                                 WHERE module=$module_id
                                   AND `lang`=$this->langId
                            ");
                            if ($objResult2 && !$objResult2->EOF) {
                                $objDatabase->Execute("
                                    DELETE FROM ".DBPREFIX."content
                                     WHERE id='".$objResult2->fields['catid']."'
                                ");
                            }
                            $objDatabase->Execute("
                                DELETE FROM ".DBPREFIX."content_navigation
                                 WHERE module=$module_id
                                   AND `lang`=$this->langId
                            ");
                        }
                        $alreadyexist = true;
                        $parcat = 0;
                    }
                    $this->arrayInstalledModules[$module_name] = true;
                    $moduleid = $objResult->fields['moduleid'];
                    $content = addslashes($objResult->fields['content']);
                    $title = addslashes($objResult->fields['title']);
                    $cmd = $objResult->fields['cmd'];
                    $displaystatus = $objResult->fields['displaystatus'];
                    $expertmode = $objResult->fields['expertmode'];

                    // Set displayorder to a high value for the parent module page
                    $displayorder = ($i == 1 ? $this->defaultOrderValue : $objResult->fields['displayorder']);
                    $modulerepid = $objResult->fields['id'];
                    $username = $objResult->fields['username'];

                    $objRS = $objDatabase->SelectLimit('SELECT max(catid)+1 AS `nextId` FROM `'.DBPREFIX.'content_navigation`');
                    $catid = $objRS->fields['nextId'];

                    $query = "
                        INSERT INTO ".DBPREFIX."content_navigation
                         SET   catid='$catid',
                               parcat='$parcat',
                               catname='$title',
                               module='$moduleid',
                               cmd='$cmd',
                               displayorder='$displayorder',
                               username='$username',
                               changelog='$currentTime',
                               protected='0',
                               displaystatus='$displaystatus',
                               `lang`=$this->langId
                    ";
                    if ($objDatabase->Execute($query)) {
                        $paridarray[$modulerepid] = $catid;
                        $query = "
                            INSERT INTO ".DBPREFIX."content
                               SET id=$catid,
                                   lang_id=".$this->langId.",
                                   content='$content',
                                   title='$title',
                                   metatitle='$title',
                                   metadesc='$title',
                                   metakeys='$title',
                                   metarobots='index',
                                   expertmode='$expertmode'";
                        if ($objDatabase->Execute($query) === false) {
                            $this->errorHandling();
                            return false;
                        }
                        foreach (FWLanguage::getLanguageArray() as $arrLang) {
                        	if($arrLang['frontend'] < 1 || $arrLang['id'] == $this->langId){
                        	    continue;
                        	}
                        	$query = "
                                INSERT INTO ".DBPREFIX."content_navigation
                                 SET   catid='$catid',
                                       parcat='$parcat',
                                       catname='$title',
                                       module='$moduleid',
                                       cmd='$cmd',
                                       displayorder='$displayorder',
                                       username='$username',
                                       changelog='$currentTime',
                                       protected='0',
                                       displaystatus='$displaystatus',
                                       `lang`=".$arrLang['id'];
                        	if ($objDatabase->Execute($query)) {
                                $paridarray[$modulerepid] = $catid;
                                $query = "
                                    INSERT INTO ".DBPREFIX."content
                                       SET id=$catid,
                                           lang_id=".$arrLang['id'].",
                                           content='',
                                           title='$title',
                                           metatitle='$title',
                                           metadesc='$title',
                                           metakeys='$title',
                                           metarobots='index',
                                           useContentFromLang=".FWLanguage::getDefaultLangId().",
                                           expertmode='$expertmode'";
                                if ($objDatabase->Execute($query) === false) {
                                    $this->errorHandling();
                                    return false;
                                }
                            }
                        }
                        $parcat = $catid;
                    } else {
                        $this->errorHandling();
                        return false;
                    }
                    ++$i;
                    $objResult->MoveNext();
                }
            } else {
                $this->errorHandling();
                return false;
            }
            $i = 1;

            if (!$alreadyexist) {
                $objFWUser = FWUser::getFWUserObject();
                $objResult = $objDatabase->Execute("
                    SELECT name
                      FROM ".DBPREFIX."modules
                     WHERE id=$module_id
                ");
                if ($objResult && !$objResult->EOF) {
                    $name = $objResult->fields['name'];
                }
                $username = $objFWUser->objUser->getUsername();

                $objRS = $objDatabase->SelectLimit('SELECT max(catid)+1 AS `nextId` FROM `'.DBPREFIX.'content_navigation`');
                $catid = $objRS->fields['nextId'];

                $query = "
                    INSERT INTO ".DBPREFIX."content_navigation
                       SET catid='$catid',
                           parcat='0',
                           catname='$name',
                           module='$module_id',
                           username='$username',
                           changelog='$currentTime',
                           lang=$this->langId
                ";
                if ($objDatabase->Execute($query)) {
                    $query = "
                        INSERT INTO ".DBPREFIX."content
                           SET id=$catid,
                               title='$name',
                               lang_id=".$this->langId;
                    $objDatabase->Execute($query);
                    return true;
                } else {
                    $this->errorHandling();
                    return false;
                }
            }
        } // end foreach

        return true;
    }


    function removeModules()
    {
        global $objDatabase;

        if (isset($_POST['removeModule']) && is_array($_POST['removeModule'])) {
            foreach (array_keys($_POST['removeModule']) as $moduleId) {
                $query = "SELECT catid, name
                            FROM ".DBPREFIX."content_navigation
                           INNER JOIN ".DBPREFIX."modules
                              ON module=id
                           WHERE module='$moduleId'
                             AND `lang`=$this->langId";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    while (!$objResult->EOF) {
                        $this->arrayRemovedModules[$objResult->fields['name']] = true;
                        $catid = $objResult->fields['catid'];
                        $query = "
                            DELETE FROM ".DBPREFIX."content_navigation
                             WHERE catid='$catid' AND `lang`=".$this->langId;
                        if ($objDatabase->Execute($query) === false) {
                            $this->errorHandling();
                            return false;
                        }
                        $query = "
                            DELETE FROM ".DBPREFIX."content
                             WHERE id='$catid' AND `lang_id`=".$this->langId;
                        if ($objDatabase->Execute($query) === false) {
                            $this->errorHandling();
                            return false;
                        }
                        $objResult->MoveNext();
                    }
                } else {
                    $this->errorHandling();
                    return false;
                }
            }
            return true;
        }
        return false;
    }


    function manageModules()
    {
        global $objDatabase, $_CORELANG, $objTemplate;

        $objTemplate->setVariable("MODULE_ACTION", "manage");
        if (isset($_POST['installModule']) && is_array($_POST['installModule'])) {
            foreach ($_POST['installModule'] as $key => $elem) {
                $id = intval($key);
                $addOnValue = intval($elem);
                $query = "UPDATE ".DBPREFIX."modules SET is_required = ".$addOnValue." WHERE id = ".$id;
                $objDatabase->Execute($query);
            }
        }

        if (isset($_POST['removeModule']) && is_array($_POST['removeModule'])) {
            foreach ($_POST['removeModule'] as $key => $elem) {
                $id = intval($key);
                $addOnValue = intval($elem);
                $query = "UPDATE ".DBPREFIX."modules SET is_required = ".$addOnValue." WHERE id = ".$id;
                $objDatabase->Execute($query);
            }
        }
        $query = "SELECT id,name, description_variable,is_required FROM ".DBPREFIX."modules WHERE id<>0 GROUP BY id";
        $objResult = $objDatabase->Execute($query);
        if ($objResult) {
            $i = 0;
            while (!$objResult->EOF) {
               if ($objResult->fields['is_required'] == 1) {
                    $objTemplate->setVariable(array(
                        'MODULE_REMOVE'  => "<input type='checkbox' name='removeModule[".$objResult->fields['id']."]' value='0' />",
                        'MODULE_INSTALL' => "&nbsp;",
                        'MODULE_STATUS'  => "<img src='images/icons/led_green.gif' alt='' />"
                    ));
                } else {
                    $objTemplate->setVariable(array(
                        'MODULE_INSTALL' => "<input type='checkbox' name='installModule[".$objResult->fields['id']."]' value='1' />",
                        'MODULE_REMOVE'  => "&nbsp;",
                        'MODULE_STATUS'  => "<img src='images/icons/led_red.gif' alt='' />"
                    ));
                }
                $objTemplate->setVariable(array(
                    'MODULE_ROWCLASS'   => ($i % 2 ? 'row2' : 'row2'),
                    'MODULE_NAME'       => $objResult->fields['name'],
                    'MODULE_DESCRIPTON' => $_CORELANG[$objResult->fields['description_variable']],
                    'MODULE_ID'         => $objResult->fields['id']
                ));
                $objTemplate->parse("moduleRow");
                ++$i;
                $objResult->MoveNext();
            }
            return true;
        }
        $this->errorHandling();
        return false;
    }


    function errorHandling() {
        global $_CORELANG;
        $this->strErrMessage.= " ".$_CORELANG['TXT_DATABASE_QUERY_ERROR']." ";
    }
}

?>
