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
    var $strErrMessage = '';
    var $strOkMessage = '';
    var $arrayInstalledModules = array();
    var $arrayRemovedModules = array();
    var $langId;
    var $defaultOrderValue = 111;

    private $act = '';
    
    /**
     * Constructor
     */
    function __construct()
    {
        global $objInit;

        $this->langId = $objInit->userFrontendLangId;
    }
    private function setNavigation()
    {
        global $objTemplate, $_CORELANG;

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'      => $_CORELANG['TXT_MODULE_MANAGER'],
            'CONTENT_NAVIGATION' => "<a href='index.php?cmd=modulemanager' class='".($this->act == '' ? 'active' : '')."'>".$_CORELANG['TXT_MODULE_MANAGER']."</a>"
                                     //<a href='index.php?cmd=modulemanager&act=manage' class='".($this->act == 'manage' ? 'active' : '')."'>[".$_CORELANG['TXT_MODULE_ACTIVATION']."]</a>"
        ));
    }


    function getModulesPage()
    {
        global $_CORELANG, $objTemplate;        

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

        if (isset($_REQUEST['act'])) {
            $this->act = $_REQUEST['act'];
        } else {
            $this->act = '';
        }
        $this->setNavigation();
    }


    function getModules()
    {
        global $objDatabase;

        $arrayInstalledModules = array();
        
        $qb = \Env::em()->createQueryBuilder();

        $qb->addSelect('p')
                ->from('Cx\Model\ContentManager\Page', 'p')
                ->where('p.module IS NOT NULL')
                ->andWhere($qb->expr()->eq('p.lang', $this->langId));
        $pages   = $qb->getQuery()->getResult();
        
        foreach ($pages as $page) {
            if (!in_array($page->getModule(), $arrayInstalledModules)) {
                $query = "
                    SELECT id
                    FROM ".DBPREFIX."modules
                    WHERE name='" . $page->getModule() . "'
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    if (!$objResult->EOF) {
                        $module_id = $objResult->fields['id'];
                    }
                } else {
                    $this->errorHandling();
                    return false;
                }
                $arrayInstalledModules[] = $module_id;
            }
        }
        
        return $arrayInstalledModules;
    }


    function showModules()
    {
        global $objDatabase, $_CORELANG, $objTemplate;

        $objTemplate->setVariable('MODULE_ACTION', 'edit');
        $arrayInstalledModules = $this->getModules();
        $query = "
            SELECT id, name, description_variable,
                   is_core, is_required
              FROM ".DBPREFIX."modules
             WHERE status='y'
             ORDER BY is_required DESC, name ASC
        ";
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

        //$i = 1;
        if (empty($_POST['installModule']) || !is_array($_POST['installModule'])) {
            return false;
        }
        //$currentTime = time();
        $paridarray = array();
        foreach (array_keys($_POST['installModule']) as $moduleId) {
            //$alreadyexist = false;
            $id = intval($moduleId);
            $objResult = $objDatabase->Execute("
                SELECT name
                  FROM ".DBPREFIX."modules
                 WHERE id=$id
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
                SELECT 
                    count(lang) as langcount,
                    lang
                FROM ".DBPREFIX."module_repository
                WHERE moduleid=$id
                GROUP BY lang
                HAVING langcount > 0
                ORDER BY langcount ASC
            ";
            $check_repo_lang = $objDatabase->Execute($q_check_repo_lang);

            // figure out what repository langid to use and store
            // it in $repo_lang_id
            while ($check_repo_lang and !$check_repo_lang->EOF) {
                $repo_lang_id = $check_repo_lang->fields['lang'];
                // preference in this order: current language id, default id, lowest id
                if ($this->langId                   == $repo_lang_id) break;
                if ($objInit->defaultFrontendLangId == $repo_lang_id) break;
            
                // lowest id is the last, so we just loop till the 
                // end (or until we find something better). 
                $check_repo_lang->MoveNext();
            }
            unset($check_repo_lang);

            $query = "SELECT *
                     FROM ".DBPREFIX."module_repository
                     WHERE moduleid=$id
                     AND lang='$repo_lang_id'
                     ORDER BY parid ASC";


            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                while (!$objResult->EOF) {
                    $em = \Env::get('em');
                    $nodeRepo = $em->getRepository('\Cx\Model\ContentManager\Node');
                    $root = false;
                    if (isset($paridarray[$objResult->fields['parid']])) {
                        $parcat = $paridarray[$objResult->fields['parid']];
                    } else {
                        $root = true;
                        $parcat = $nodeRepo->getRoot();
                    }
                    $this->arrayInstalledModules[$module_name] = true;
                    //$moduleid = $objResult->fields['moduleid'];
                    $content = addslashes($objResult->fields['content']);
                    $title = addslashes($objResult->fields['title']);
                    $cmd = $objResult->fields['cmd'];

                    $newnode = new \Cx\Model\ContentManager\Node();
                    $newnode->setParent($parcat); // replace root node by parent!
                    $em->persist($newnode);
                    $em->flush();
                    $nodeRepo->moveDown($newnode, true); // move to the end of this level
                    $paridarray[$objResult->fields['id']] = $newnode;
                    
                    $page = new \Cx\Model\ContentManager\Page();
                    $page->setNode($newnode);
                    $page->setNodeIdShadowed($newnode->getId());
                    $page->setLang($this->langId);
                    $page->setTitle($title);
                    $page->setModule($module_name);
                    $page->setCmd($cmd);
                    $page->setActive(true);
                    $page->setDisplay(!$root); // pages on root level are not active
                    $page->setContent($content);
                    $page->setMetatitle($title);
                    $page->setMetadesc($title);
                    $page->setMetakeys($title);
                    $page->setMetarobots('index');
                    $page->setMetatitle($title);
                    $em->persist($page);
                    $em->flush();
                    $objResult->MoveNext();
                }
            } else {
                $this->errorHandling();
                return false;
            }
        } // end foreach

        return true;
    }


    function removeModules()
    {
        global $objDatabase;

        if (isset($_POST['removeModule']) && is_array($_POST['removeModule'])) {
            foreach (array_keys($_POST['removeModule']) as $moduleId) {
                
                $query = "
                    SELECT name
                    FROM ".DBPREFIX."modules
                    WHERE id='" . $moduleId . "'
                ";
                $objResult = $objDatabase->Execute($query);
                if ($objResult) {
                    if (!$objResult->EOF) {
                        $moduleName = $objResult->fields['name'];
                    }
                } else {
                    $this->errorHandling();
                    return false;
                }

                $em = \Env::get('em');
                $pageRepo = $em->getRepository('Cx\Model\ContentManager\Page');
                $pages = $pageRepo->findBy(array(
                    'module' => $moduleName,
                    'lang' => $this->langId,
                ));
                $em->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
                foreach ($pages as $page) {
                    $em->remove($page->getNode());
                    $em->flush();
                }
                $em->getConnection()->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
            }
            return true;
        } else {
            return false;
        }

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
