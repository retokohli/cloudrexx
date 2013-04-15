<?php

/**
 * Modulemanager
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  core
 * @todo        Edit PHP DocBlocks!
 */

class ModuleManagerException extends \Exception {};

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
                ->from('Cx\Core\ContentManager\Model\Entity\Page', 'p')
                ->where('p.module IS NOT NULL');
//                ->andWhere($qb->expr()->eq('p.lang', $this->langId));
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
                   is_core, is_required, is_active
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
                        'MODULE_STATUS'  => ($objResult->fields['is_active'] ? "<img src='images/icons/led_green.gif' alt='' />" : "<img src='images/icons/led_orange.gif' alt='' />")
                    ));
                } else  {
                    $objTemplate->setVariable(array(
                        'MODULE_INSTALL' => ($objResult->fields['is_active'] ? "<input type='checkbox' name='installModule[".$objResult->fields['id']."]' value='1' />" : ''),
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
                    $objTemplate->setVariable('MODULE_REQUIRED', $objResult->fields['is_active'] ? $_CORELANG['TXT_OPTIONAL'] : $_CORELANG['TXT_LICENSE_NOT_LICENSED']);
                }

                $objTemplate->setVariable(array(
                    'MODULE_ROWCLASS'   => $class . (!$objResult->fields['is_active'] ? ' rowInactive' : ''),
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
        $em = \Env::get('em');
        $nodeRepo = $em->getRepository('\Cx\Core\ContentManager\Model\Entity\Node');

        //$i = 1;
        if (empty($_POST['installModule']) || !is_array($_POST['installModule'])) {
            return false;
        }
        //$currentTime = time();
        $paridarray = array();
        foreach (array_keys($_POST['installModule']) as $moduleId) {
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
            
            // get content from repo
            $query = "SELECT *
            FROM ".DBPREFIX."module_repository
            WHERE moduleid=$id
            ORDER BY parid ASC";


            $objResult = $objDatabase->Execute($query);
            if ($objResult) {
                while (!$objResult->EOF) {
                    // define parent node
                    $root = false;
                    if (isset($paridarray[$objResult->fields['parid']])) {
                        $parcat = $paridarray[$objResult->fields['parid']];
                    } else {
                        $root = true;
                        $parcat = $nodeRepo->getRoot();
                    }
                    $this->arrayInstalledModules[$module_name] = true;
                    
                    // create node
                    $newnode = new \Cx\Core\ContentManager\Model\Entity\Node();
                    $newnode->setParent($parcat); // replace root node by parent!
                    $em->persist($newnode);
                    $em->flush();
                    $nodeRepo->moveDown($newnode, true); // move to the end of this level
                    $paridarray[$objResult->fields['id']] = $newnode;
                    
                    // add content to default lang
                    // add content to all langs without fallback
                    // link content to all langs with fallback
                    foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
                        if ($lang['is_default'] === 'true' || $lang['fallback'] == null) {
                            $page = $this->createPage(
                                $newnode,
                                $lang['id'],
                                $objResult->fields['title'],
                                \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION,
                                $module_name,
                                $objResult->fields['cmd'],
                                !$root && $objResult->fields['displaystatus'],
                                $objResult->fields['content']
                            );
                        } else {
                            $page = $this->createPage(
                                $newnode,
                                $lang['id'],
                                $objResult->fields['title'],
                                \Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK,
                                $module_name,
                                $objResult->fields['cmd'],
                                !$root && $objResult->fields['displaystatus'],
                                ''
                            );
                        }
                        $em->persist($page);
                    }
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

    private function createPage($parentNode, $lang, $title, $type, $module, $cmd, $display, $content) {
        $page = new \Cx\Core\ContentManager\Model\Entity\Page();
        $page->setNode($parentNode);
        $page->setNodeIdShadowed($parentNode->getId());
        $page->setLang($lang);
        $page->setTitle($title);
        $page->setType($type);
        $page->setModule($module);
        $page->setCmd($cmd);
        $page->setActive(true);
        $page->setDisplay($display); // pages on root level are not active
        $page->setContent($content);
        $page->setMetatitle($title);
        $page->setMetadesc($title);
        $page->setMetakeys($title);
        $page->setMetarobots('index');
        $page->setMetatitle($title);
        $page->setUpdatedBy(\FWUser::getFWUserObject()->objUser->getUsername());
        return $page;
    }

    function removeModules()
    {
        global $objDatabase;
return false;
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
                $pageRepo = $em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
                $pages = $pageRepo->findBy(array(
                    'module' => $moduleName,
                    'lang' => $this->langId,
                ));
                foreach ($pages as $page) {
                    $em->remove($page->getNode());
                    $em->flush();
                }
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

    /**
     * @todo move to component handler
     * @param type $module
     * @param type $classLoader
     * @param type $objDatabase
     * @param type $coreLang
     * @param type $subMenuTitle
     * @param type $objTemplate
     * @param type $objFWUser
     * @param type $act
     * @param type $objInit
     * @throws ModuleManagerException 
     */
    public function loadModule(&$module, $classLoader, $objDatabase, &$coreLang, &$subMenuTitle, $objTemplate, $objFWUser, &$act, $objInit) {
        // exceptions which need to be load the legacy way:
        if (in_array($module, array(
            'forum', // because of /modules/example_module_template/admin.class.php
            'calendar', // because const CALENDAR_MANDATE needs to be set
        )) || $objInit->mode != 'backend') {
            throw new ModuleManagerException('Forcing legacy load');
        }
    	$result = $objDatabase->query('
    		SELECT DISTINCT
    			`ba`.`access_id`
    		FROM
    			`contrexx_modules` AS `m`,
    			`contrexx_backend_areas` AS `ba`
    		WHERE
    			`ba`.`module_id` = `m`.`id` AND
    			`m`.`name` = \'' . $module . '\'
    	');
        if (!$result) {
            throw new ModuleManagerException('Could not load access for module "' . $module . '"');
        }
    	$accessId = $result->fields['access_id'] + 0;
    	if (!\Permission::checkAccess($accessId, 'static', true)) {
            // This should never happen, page should have been resolved to 'login' before
            die("KLJ");\CSRF::header('Location: ?cmd=login');
        }
    	if (!$classLoader->loadFile(ASCMS_MODULE_PATH.'/' . $module . '/admin.class.php')) {
            throw new ModuleManagerException($coreLang['TXT_THIS_MODULE_DOESNT_EXISTS']);
        }
        if (!isset($coreLang['TXT_' . strtoupper($module) . '_MODULE_DESCRIPTION'])) {
            throw new ModuleManagerException('No title for module "' . $module . '"');
        }
        $subMenuTitle = $coreLang['TXT_' . strtoupper($module) . '_MODULE_DESCRIPTION'];
        $backendClassName = '\\Cx\\Module\\' . ucfirst($module) . '\\' . ucfirst($module) . 'Backend';
        if (!class_exists($backendClassName)) {
            // try legacy class names:
            $origBackendClassName = $backendClassName;
            $backendClassName = $module . 'manager';
            if (!class_exists($backendClassName)) {
                $backendClassName = $module . 'Manager';
                if (!class_exists($backendClassName)) {
                    $backendClassName = ucfirst($module) . 'Manager';
                    if (!class_exists($backendClassName)) {
                        throw new ModuleManagerException('Class "' . $origBackendClassName . '" for module "' . $module . '" not found');
                    }
                }
            }
        }
        if (!method_exists($backendClassName, 'getPage')) {
            throw new ModuleManagerException('Class "' . $backendClassName . '" for module "' . $module . '" has no method getPage($template)');
        }
        $backendClass = new $backendClassName($objInit->loadLanguageData($module));
        $backendClass->getPage($objTemplate);
        echo 'Could load nice';
    }

    /**
     * @todo move to legacycomponenthandler
     * @global type $page_content
     * @param type $plainCmd
     * @param type $cl
     * @param type $objDatabase
     * @param type $_CORELANG
     * @param type $subMenuTitle
     * @param type $objTemplate
     * @param type $objFWUser
     * @param type $act
     * @param type $objInit 
     */
    public function loadLegacyModule(&$plainCmd, $cl, $objDatabase, &$_CORELANG, &$subMenuTitle, $objTemplate, $objFWUser, &$act, $objInit) {
        global $page_content, $_CONFIG;
        
        switch ($plainCmd) {
            case 'login':
                if ($objFWUser->objUser->login(true)) {
                    header('location: index.php');
                }
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/login/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $objLoginManager = new LoginManager();
                $objLoginManager->getPage();
                break;
            case 'access':
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH."/access/admin.class.php"))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_COMMUNITY'];
                $objAccessManager = new AccessManager();
                $objAccessManager->getPage();
                break;
            case 'egov':
                Permission::checkAccess(109, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/egov/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_EGOVERNMENT'];
                $objEgov = new eGov();
                $objEgov->getPage();
                break;
            case 'banner':
                // Permission::checkAccess(??, 'static');
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/banner/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_BANNER_ADMINISTRATION'];
                $objBanner = new Banner();
                $objBanner->getPage();
                break;
            case 'jobs':
                Permission::checkAccess(11, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/jobs/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_JOBS_MANAGER'];
                $objJobs = new jobsManager();
                $objJobs->getJobsPage();
                break;
            case 'fileBrowser':
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/fileBrowser/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $objFileBrowser = new FileBrowser();
                $objFileBrowser->getPage();
                exit;
                break;
            case 'feed':
                Permission::checkAccess(27, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/feed/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_NEWS_SYNDICATION'];
                $objFeed = new feedManager();
                $objFeed->getFeedPage();
                break;
            case 'server':
                Permission::checkAccess(4, 'static');
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/serverSettings.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_SERVER_INFO'];
                $objServer = new serverSettings();
                $objServer->getPage();
                break;
            case 'log':
                Permission::checkAccess(18, 'static');
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/log.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_SYSTEM_LOGS'];
                $objLogManager = new logmanager();
                $objLogManager->getLogPage();
                break;
            case 'skins':
                //Permission::checkAccess(18, 'static');
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/skins.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_DESIGN_MANAGEMENT'];
                $objSkins = new skins();
                $objSkins->getPage();
                break;
            case 'content':
                $subMenuTitle = $_CORELANG['TXT_CONTENT_MANAGER'];
                $cm = new \Cx\Core\ContentManager\Controller\ContentManager($act, $objTemplate, $objDatabase, $objInit);
                $cm->getPage();
                break;
            case 'license':
                $subMenuTitle = $_CORELANG['TXT_LICENSE'];
                $lm = new \Cx\Core_Modules\License\LicenseManager($act, $objTemplate, $_CORELANG, $_CONFIG, $objDatabase);
                $lm->getPage($_POST);
                break;
        // TODO: handle expired sessions in any xhr callers.
            case 'jsondata':
                $json = new \Cx\Core\Json\JsonData();
        // TODO: Verify that the arguments are actually present!
                $adapter = contrexx_input2raw($_GET['object']);
                $method = contrexx_input2raw($_GET['act']);
        // TODO: Replace arguments by something reasonable
                $arguments = array('get' => $_GET, 'post' => $_POST);
                echo $json->jsondata($adapter, $method, $arguments);
                die();
            case 'workflow':
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/ContentWorkflow.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_CONTENT_HISTORY'];
                $wf = new ContentWorkflow($act, $objTemplate, $objDatabase, $objInit);
                $wf->getPage();
                break;
            case 'docsys':
                Permission::checkAccess(11, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/docsys/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_DOC_SYS_MANAGER'];
                $objDocSys = new docSysManager();
                $objDocSys->getDocSysPage();
                break;
            case 'news':
                Permission::checkAccess(10, 'static');
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/news/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_NEWS_MANAGER'];
                $objNews = new NewsManager();
                $objNews->getPage();
                break;
            case 'contact':
                // Permission::checkAccess(10, 'static');
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/contact/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_CONTACTS'];
                $objContact = new contactManager();
                $objContact->getPage();
                break;
            case 'immo':
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/immo/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_IMMO_MANAGEMENT'];
                $objImmo = new Immo();
                $objImmo->getPage();
                break;
                // dataviewer
            case 'dataviewer':
                Permission::checkAccess(9, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/dataviewer/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_DATAVIEWER'];
                $objDataviewer = new Dataviewer();
                $objDataviewer->getPage();
                break;
            case 'download':
                Permission::checkAccess(57, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/download/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_DOWNLOAD_MANAGER'];
                $objDownload = new DownloadManager();
                $objDownload->getPage();
                break;
            case 'media':
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/media/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_MEDIA_MANAGER'];
                $objMedia = new MediaManager();
                $objMedia->getMediaPage();
                break;
            case 'development':
                Permission::checkAccess(81, 'static');
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/development/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_DEVELOPMENT'];
                $objDevelopment = new Development();
                $objDevelopment->getPage();
                break;
            case 'dbm':
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/DatabaseManager.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_DATABASE_MANAGER'];
                $objDatabaseManager = new DatabaseManager();
                $objDatabaseManager->getPage();
                break;
            case 'stats':
                Permission::checkAccess(19, 'static');
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/stats/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_STATISTIC'];
                $statistic= new stats();
                $statistic->getContent();
                break;
            case 'alias':
                Permission::checkAccess(115, 'static');
                Permission::checkAccess(78, 'static');
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/alias/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_ALIAS_ADMINISTRATION'];
                $objAlias = new AliasAdmin();
                $objAlias->getPage();
                break;
            case 'nettools':
                Permission::checkAccess(54, 'static');
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/nettools/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_NETWORK_TOOLS'];
                $nettools = new netToolsManager();
                $nettools->getContent();
                break;
            case 'newsletter':
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/newsletter/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_CORE_EMAIL_MARKETING'];
                $objNewsletter = new newsletter();
                $objNewsletter->getPage();
                break;
            case 'settings':
                Permission::checkAccess(17, 'static');
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/settings.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_SYSTEM_SETTINGS'];
                $objSettings = new settingsManager();
                $objSettings->getPage();
                break;
            case 'language':
                Permission::checkAccess(22, 'static');
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/language.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_LANGUAGE_SETTINGS'];
                $objLangManager = new LanguageManager();
                $objLangManager->getLanguagePage();
                break;
            case 'modulemanager':
                Permission::checkAccess(23, 'static');
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/modulemanager.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_MODULE_MANAGER'];
                $objModuleManager = new modulemanager();
                $objModuleManager->getModulesPage();
                break;
            case 'ecard':
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/ecard/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_ECARD_TITLE'];
                $objEcard = new ecard();
                $objEcard->getPage();
                break;
            case 'voting':
                Permission::checkAccess(14, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/voting/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_CONTENT_MANAGER'];
                $objvoting = new votingmanager();
                $objvoting->getVotingPage();
                break;
            case 'survey':
                Permission::checkAccess(111, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/survey/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_SURVEY'];
                $objSurvey = new SurveyAdmin();
                $objSurvey->getPage();
                break;
            case 'calendar':
                Permission::checkAccess(16, 'static');
                define('CALENDAR_MANDATE', MODULE_INDEX);
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/calendar'.MODULE_INDEX.'/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_CALENDAR'];
                $objCalendar = new calendarManager();
                $objCalendar->getCalendarPage();
                break;
            case 'reservation':
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/reservation/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_RESERVATION_MODULE'];
                $objReservationModule = new reservationManager();
                $objReservationModule->getPage();
                break;
            case 'forum':
                Permission::checkAccess(106, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/forum/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_FORUM'];
                $objForum = new ForumAdmin();
                $objForum->getPage();
                break;
            case 'directory':
                //Permission::checkAccess(18, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/directory/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_LINKS_MODULE_DESCRIPTION'];
                $objDirectory = new rssDirectory();
                $objDirectory->getPage();
                break;
            case 'popup':
                Permission::checkAccess(117, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/popup/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_POPUP_SYSTEM'];
                $objPopup = new popupManager();
                $objPopup->getPage();
                break;
            case 'market':
                Permission::checkAccess(98, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/market/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_CORE_MARKET_TITLE'];
                $objMarket = new Market();
                $objMarket->getPage();
                break;
            case 'data':
                Permission::checkAccess(122, 'static'); // ID !!
                if (!$cl->loadFile(ASCMS_MODULE_PATH."/data/admin.class.php"))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_DATA_MODULE'];
                $objData = new DataAdmin();
                $objData->getPage();
                break;
            case 'support':
                // TODO: Assign a proper access ID to the support module
                //Permission::checkAccess(??, 'static');
                Permission::checkAccess(87, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/support/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_SUPPORT_SYSTEM'];
                $objSupport = new Support();
                $objSupport->getPage();
                break;
            case 'blog':
                Permission::checkAccess(119, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/blog/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_BLOG_MODULE'];
                $objBlog = new BlogAdmin();
                $objBlog->getPage();
                break;
            case 'knowledge':
                Permission::checkAccess(129, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/knowledge/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_KNOWLEDGE'];
                $objKnowledge = new KnowledgeAdmin();
                $objKnowledge->getPage();
                break;
            case 'u2u':
                Permission::checkAccess(141, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/u2u/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_U2U_MODULE'];
                $objU2u = new u2uAdmin();
                $objU2u->getPage();
                break;
            case 'partners':
                Permission::checkAccess(140, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/partners/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_PARTNERS_MODULE'];
                $objPartner = new PartnersAdmin();
                $objPartner->getPage();
                break;
            case 'auction':
                Permission::checkAccess(143, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/auction/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_AUCTION_TITLE'];
                $objAuction = new Auction();
                $objAuction->getPage();
                break;
            case 'upload':
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/upload/admin.class.php'))
                    die ($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $objUploadModule = new Upload();
                $objUploadModule->getPage();
                //execution never reaches this point
                break;
            case 'noaccess':
                //Temporary no-acces-file and comment
                $subMenuTitle = $_CORELANG['TXT_ACCESS_DENIED'];
                $objTemplate->setVariable(array(
                    'CONTENT_NAVIGATION' => '<span id="noaccess_title">'.contrexx_raw2xhtml($_CONFIG['coreCmsName']).'</span>',
                    'ADMIN_CONTENT' =>
                        '<img src="images/no_access.png" alt="" /><br /><br />'.
                        $_CORELANG['TXT_ACCESS_DENIED_DESCRIPTION'],
                ));
                break;
            case 'logout':
                $objFWUser->logout();
                exit;
            case 'downloads':
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/downloads/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_DOWNLOADS'];
                $objDownloadsModule = new downloads();
                $objDownloadsModule->getPage();
                break;
            case 'country':
        // TODO: Move this define() somewhere else, allocate the IDs properly
                define('PERMISSION_COUNTRY_VIEW', 145);
                define('PERMISSION_COUNTRY_EDIT', 146);
                Permission::checkAccess(PERMISSION_COUNTRY_VIEW, 'static');
                if (!$cl->loadFile(ASCMS_CORE_PATH.'/Country.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_CORE_COUNTRY'];
                Country::getPage();
                break;
            case 'mediadir':
                Permission::checkAccess(153, 'static');
                if (!$cl->loadFile(ASCMS_MODULE_PATH.'/mediadir/admin.class.php'))
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                $subMenuTitle = $_CORELANG['TXT_MEDIADIR_MODULE'];
                $objMediaDirectory = new mediaDirectoryManager();
                $objMediaDirectory->getPage();
                break;
            case 'search':
                if (!$cl->loadFile(ASCMS_CORE_MODULE_PATH.'/search/admin.class.php')) {
                    die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                }
                $subMenuTitle = $_CORELANG['TXT_SEARCH'];
                $objSearch    = new \Cx\Core\Search\SearchManager($act, $objTemplate, $objDatabase, $objInit, $license);
                $objSearch->getPage();
                break;
            default:
                if ($objInit->mode != 'backend') {
                    $objTemplate->setVariable('CONTENT_TEXT', $page_content);
                } else {
                    if (!$cl->loadFile(ASCMS_CORE_PATH.'/myAdmin.class.php'))
                        die($_CORELANG['TXT_THIS_MODULE_DOESNT_EXISTS']);
                    $objTemplate->setVariable('CONTAINER_DASHBOARD_CLASS', 'dashboard');
                    $objFWUser = FWUser::getFWUserObject();
                    $subMenuTitle = $_CORELANG['TXT_WELCOME_MESSAGE'].", <a href='index.php?cmd=access&amp;act=user&amp;tpl=modify&amp;id=".$objFWUser->objUser->getId()."' title='".$objFWUser->objUser->getId()."'>".($objFWUser->objUser->getProfileAttribute('firstname') || $objFWUser->objUser->getProfileAttribute('lastname') ? htmlentities($objFWUser->objUser->getProfileAttribute('firstname'), ENT_QUOTES, CONTREXX_CHARSET).' '.htmlentities($objFWUser->objUser->getProfileAttribute('lastname'), ENT_QUOTES, CONTREXX_CHARSET) : htmlentities($objFWUser->objUser->getUsername(), ENT_QUOTES, CONTREXX_CHARSET))."</a>";
                    $objAdminNav = new myAdminManager();
                    $objAdminNav->getPage();
                }
                break;
        }
    }

    function errorHandling() {
        global $_CORELANG;
        $this->strErrMessage.= " ".$_CORELANG['TXT_DATABASE_QUERY_ERROR']." ";
    }
}
