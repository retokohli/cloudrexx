<?php

/**
 * Content Manager 2 (Doctrine-based version)
 *
 * @copyright   Comvation AG
 * @author      Comvation Engineering Team
 * @package     contrexx
 * @subpackage  admin
 */

use Doctrine\Common\Util\Debug as DoctrineDebug;

require ASCMS_CORE_PATH.'/BackendTable.class.php';
require ASCMS_CORE_PATH.'/Module.class.php';
require ASCMS_CORE_PATH.'/routing/LanguageExtractor.class.php';

class ContentManagerException extends ModuleException {}

class ContentManager extends Module {
    //doctrine entity manager
	protected $em = null;
    //the mysql connection
    protected $db = null;
    //the init object
    protected $init = null;

    protected $pageRepository = null;
    protected $nodeRepository = null;

    //renderCM access state
    protected $backendGroups = array();
    protected $frontendGroups = array();
    protected $assignedBackendGroups = array();
    protected $assignedFrontendGroups = array();

    /**
     * @param string $act
     * @param $template
     * @param $db the ADODB db object
     * @param $init the Init object
     */
	public function __construct($act, $template, $db, $init) {
        parent::__construct($act, $template);
        if($this->act == 'new')
            $this->act = ''; //default action;
	
        $this->em = Env::em();
        $this->db = $db;
        $this->init = $init;
        $this->pageRepository = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->nodeRepository = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $this->defaultAct = 'actRenderCM';
	}

	protected function actRenderCM() {
        global $_ARRAYLANG;

        JS::activate('cx');
        JS::activate('ckeditor');
        JS::activate('cx-form');
        JS::activate('jstree');
        JS::activate('chosen');
        JS::registerJS('lib/javascript/lock.js');

		$this->template->addBlockfile('ADMIN_CONTENT', 'content_manager', 'content_manager.html');
		$this->template->touchBlock('content_manager');
		$this->template->addBlockfile('CONTENT_MANAGER_MEAT', 'content_manager_meat', 'cm.html');
		$this->template->touchBlock('content_manager_meat');

        $this->setLanguageVars(array(
            //categories
            'TXT_CORE_SITE_TYPE', 'TXT_CORE_SITE_CONTENT', 'TXT_CORE_SITE_ACCESS', 'TXT_CORE_SITE_SETTINGS', 'TXT_CORE_SITE_HISTORY',
            //type tab
            'TXT_CORE_CM_PAGE', 'TXT_CORE_CM_META', 'TXT_CORE_CM_ACCESS', 'TXT_CORE_CM_SETTINGS', 'TXT_CORE_CM_HISTORY', 'TXT_CORE_CM_PAGE_NAME', 'TXT_CORE_CM_PAGE_NAME_INFO', 'TXT_CORE_CM_PAGE_TITLE', 'TXT_CORE_CM_PAGE_TITLE_INFO', 'TXT_CORE_CM_TYPE', 'TXT_CORE_CM_TYPE_CONTENT', 'TXT_CORE_CM_TYPE_REDIRECT', 'TXT_CORE_CM_TYPE_APPLICATION', 'TXT_CORE_CM_TYPE_FALLBACK', 'TXT_CORE_CM_TYPE_CONTENT_INFO', 'TXT_CORE_CM_TYPE_REDIRECT_TARGET', 'TXT_CORE_CM_BROWSE', 'TXT_CORE_CM_TYPE_REDIRECT_INFO', 'TXT_CORE_CM_TYPE_APPLICATION', 'TXT_CORE_CM_TYPE_APPLICATION', 'TXT_CORE_CM_TYPE_APPLICATION_AREA', 'TXT_CORE_CM_TYPE_APPLICATION_INFO', 'TXT_CORE_CM_TYPE_FALLBACK_INFO', 'TXT_CORE_CM_SCHEDULED_PUBLISHING', 'TXT_CORE_CM_SCHEDULED_PUBLISHING_INFO',
            //meta tab
'TXT_CORE_CM_SE_INDEX', 'TXT_CORE_CM_METATITLE', 'TXT_CORE_CM_METATITLE_INFO', 'TXT_CORE_CM_METADESC', 'TXT_CORE_CM_METADESC_INFO', 'TXT_CORE_CM_METAKEYS', 'TXT_CORE_CM_METAKEYS_INFO',
            //access tab
'TXT_CORE_CM_ACCESS_PROTECTION_FRONTEND', 'TXT_CORE_CM_ACCESS_PROTECTION_BACKEND',
            //advanced tab
'TXT_CORE_CM_THEMES', 'TXT_CORE_CM_THEMES_INFO', 'TXT_CORE_CM_CUSTOM_CONTENT', 'TXT_CORE_CM_CUSTOM_CONTENT_INFO', 'TXT_CORE_CM_CSS_CLASS', 'TXT_CORE_CM_CSS_CLASS_INFO', 'TXT_CORE_CM_CACHE', 'TXT_CORE_CM_NAVIGATION', 'TXT_CORE_CM_LINK_TARGET', 'TXT_CORE_CM_LINK_TARGET_INO', 'TXT_CORE_CM_SLUG', 'TXT_CORE_CM_SLUG_INFO', 'TXT_CORE_CM_CSS_NAV_CLASS', 'TXT_CORE_CM_CSS_NAV_CLASS_INFO', 'TXT_CORE_CM_SOURCE_MODE',
            //settings tab
            'TXT_CORE_APPLICATION_AREA', 'TXT_CORE_APPLICATION', 'TXT_CORE_AREA', 'TXT_CORE_OPTICS_STYLE', 'TXT_CORE_SKIN', 'TXT_CORE_SPECIAL_CONTENT_PAGE', 'TXT_CORE_CUSTOMCONTENT', 'TXT_CORE_REDIRECTION', 'TXT_CORE_TARGET', 'TXT_CORE_PERFORMANCE_OPTIMIZATION', 'TXT_CORE_CACHING', 'TXT_CORE_LINK', 'TXT_CORE_SLUG', 'TXT_CORE_CSSNAME',
            //bottom buttons
            'TXT_CORE_PREVIEW', 'TXT_CORE_SAVE_PUBLISH', 'TXT_CORE_SAVE'
        ));

        $modules = $this->db->Execute("SELECT * FROM ".DBPREFIX."modules");
        while (!$modules->EOF) {
            $this->template->setVariable('MODULE_KEY', $modules->fields['name']);
//            $this->template->setVariable('MODULE_TITLE', $_CORELANG[$modules->fields['description_variable']]);
            $this->template->setVariable('MODULE_TITLE', ucwords($modules->fields['name']));
            $this->template->parse('module_option');
            $modules->MoveNext();
        }

        ContrexxJavascript::getInstance()->setVariable('confirmDeleteQuestion', $_ARRAYLANG['TXT_CORE_CM_CONFIRM_DELETE'] );
        
        // TODO: move including of add'l JS dependencies to cx obj from /cadmin/index.html
        $this->template->setVariable('CXJS_INIT_JS', ContrexxJavascript::getInstance()->initJs());


        $this->template->setVariable('SKIN_OPTIONS', $this->getSkinOptions());
        $this->template->setVariable('LANGSWITCH_OPTIONS', $this->getLangOptions());
        $this->template->setVariable('LANGUAGE_ARRAY', json_encode($this->getLangArray()));
        $this->template->setVariable('FALLBACK_ARRAY', json_encode($this->getFallbackArray()));
        $this->template->setVariable('LANGUAGE_LABELS', json_encode($this->getLangLabels()));

	}

    /**
     * Sub of actRenderCM.
     * Renders the access tab.
     */
    protected function renderCMAccess() {
        $backendGroups = array();
        $frontendGroups = array();

        $objResult = $objDatabase->Execute("SELECT group_id, group_name FROM ".DBPREFIX."access_user_groups");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $groupId = $objResult->fields['group_id'];
                $groupName = $objResult->fields['group_name'];
                $type = $objResult->fields['type'];
                if($type == 'frontend')
                    $frontendGroups[$groupId]=$groupName;
                else
                    $backendGroups[$groupId]=$groupName;

                $objResult->MoveNext();
            }
        }
        return $arrGroups;

        
    }

    protected function getSkinOptions() {
        $query = "SELECT id,themesname FROM ".DBPREFIX."skins ORDER BY id";
        $rs = $this->db->Execute($query);

        $options = '';
        while(!$rs->EOF) {
            $id = $rs->fields['id'];
            $name = $rs->fields['themesname'];
            $options .= "<option value=\"$id\">$name</option>\n";
            
            $rs->MoveNext();
        }
        return $options;
    }

    protected function getLangOptions() {
        $output = '';
        foreach (FWLanguage::getActiveFrontendLanguages() as $lang) {
            $selected = $lang['id'] == FRONTEND_LANG_ID ? ' selected="selected"' : '';
            $output .= '<option value="'.FWLanguage::getLanguageCodeById($lang['id']).'"'.$selected.'>'.$lang['name'].'</option>';
        }
        return $output;
    }

    protected function getLangLabels() {
        $output = array();
        foreach (FWLanguage::getActiveFrontendLanguages() as $lang) {
            $output[FWLanguage::getLanguageCodeById($lang['id'])] = $lang['name'];
        }
        return $output;
    }

    protected function getLangArray() {
        $output = array();
        // set selected frontend language as first language
        // jstree does display the tree of the first language
        $output[] = FWLanguage::getLanguageCodeById(FRONTEND_LANG_ID);
        foreach (FWLanguage::getActiveFrontendLanguages() as $lang) {
            if ($lang['id'] == FRONTEND_LANG_ID) {
                continue;
            }
            $output[] = FWLanguage::getLanguageCodeById($lang['id']);
        }
        return $output;
    }

    protected function getFallbackArray() {
        $fallbacks = FWLanguage::getFallbackLanguageArray();
        $output = array();
        foreach ($fallbacks as $key => $value) {
            $output[FWLanguage::getLanguageCodeById($key)] = FWLanguage::getLanguageCodeById($value);
        }
        return $output;
    }

    protected function setLanguageVars($ids) {
        global $_CORELANG;
        foreach($ids as $id) {
            $this->template->setVariable($id, $_CORELANG[$id]);
        }
    }

    protected function actAjaxGetHistoryTable() {       
        if(!isset($_GET['pageId']))
            throw new ContentManagerException('please provide a pageId');

        //(I) get the right page
        $id = $_GET['pageId'];
        $page = $this->pageRepository->findOneById($id);

        if(!$page) {
            throw new ContentManagerException("could not find page with id $id");
        }

        //(II) build the table with headers
        $table = new BackendTable(array('width' => '100%'));
        $table->setAutoGrow(true);

        $table->setHeaderContents(0,0,'Date');
        $table->setHeaderContents(0,1,'Title');
        $table->setHeaderContents(0,2,'Author');
        //make sure those are th's too
        $table->setHeaderContents(0,3,'');
        $table->setHeaderContents(0,4,'');

        //(III) collect page informations - path, virtual language directory
        $path = $this->getPageRepository()->getPath($page);

        $le = new \Cx\Core\Routing\LanguageExtractor($this->db, DBPREFIX);
        $langDir = $le->getShortNameOfLanguage($page->getLang());

        //(IV) add current entry to table
        $this->addHistoryEntries($page, $table, 1);
      
        //(V) add the history entries
        $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $logs = $logRepo->getLogEntries($page);
      
        $logCount = count($logs);
        for($i = 0; $i < $logCount; $i++) {
            $version = $logCount - ($i + 1);
            $row = $i + 2;
            try {
                $logRepo->revert($page, $version);
            }
            catch(\Gedmo\Exception\UnexpectedValueException $e) {
            }
            $this->addHistoryEntries($page, $table, $row, $version, $langDir.'/'.$path);
 
                
        }
       
        //(VI) render
        die($table->toHtml());
    }

    protected function addHistoryEntries($page, $table, $row, $version='', $path='') {
        global $_ARRAYLANG;

        $dateString = $page->getUpdatedAt()->format(ASCMS_DATE_FORMAT);

        if($row > 1) { //not the current page
            $table->setCellContents($row, 3, '<a href="javascript:loadHistoryVersion('.$page->getId().','.$version.')">'.$_ARRAYLANG['TXT_CORE_LOAD'].'</a>');
            $historyLink = ASCMS_PATH_OFFSET."/$path?history=$version";
            $table->setCellContents($row, 4, '<a href="'.$historyLink.'" target="_blank">'.$_ARRAYLANG['TXT_CORE_PREVIEW'].'</a>');
        }
        else { //current page state
            $dateString .= ' ('. $_ARRAYLANG['TXT_CORE_CURRENT'] . ')';
        }

        $table->setCellContents($row, 0, $dateString);
        $table->setCellContents($row, 1, $page->getTitle());
        $table->setCellContents($row, 2, $page->getUsername());
    }

    protected function actAjaxGetCustomContentTemplates() {
        if(!isset($_GET['themeId']))
            throw new ContentManagerException('please provide a value for "themeId".');

        $module = isset($_GET['module']) ? $_GET['module'] : '';
        $themeId = intval($_GET['themeId']);
        $isHomeRequest = $module == 'home';

        $templates = $this->init->getCustomContentTemplatesForTheme($themeId);
        $matchingTemplates = array();

        foreach($templates as $name) {
            $isHomeTemplate = substr($name,0,4) == 'home';
            if($isHomeTemplate && $isHomeRequest)
                $matchingTemplates[] = $name;
            else if(!$isHomeTemplate && !$isHomeRequest)
                $matchingTemplates[] = $name;              
        }
        
        die(json_encode($matchingTemplates));
    }

    protected function actAjaxRevert() {       
        if(!isset($_POST['pageId']))
            throw new ContentManagerException('please provide a pageId');
        if(!isset($_POST['version']))
            throw new ContentManagerException('please provide a version you want to revert to');

        $id = $_POST['pageId'];
        $version = $_POST['version'];
        $page = $this->getPageRepository()->findOneById($id);

        if(!$page)
            new ContentManagerException("could not find page with id $id");
       
        $logRepo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        
        $logRepo->revert($page, $version);

        $this->em->persist($page);
        $this->em->flush();
    }

    protected function actions()
    {
        require_once ASCMS_CORE_PATH."/ActionsRenderer.class.php";

        $nodeId = intval($_GET['node']);
        $langId = FWLanguage::getLanguageIdByCode($_GET['lang']);
        $node = $this->getNodeRepository()->find($nodeId);
        $page = $node->getPage($langId);
        if ($page != null) {
            echo ActionsRenderer::render($page);
        } else {
            echo ActionsRenderer::renderNew($nodeId, $langId);
        }

        exit(0);
    }

    protected function pageStatus()
    {
        header('Content-Type: application/json');

        $pageId = isset($_GET['page']) ? intval($_GET['page']) : null;

        if ($pageId != null) {
            $page = $this->pageRepository->find($pageId);
        }

        $action = $_GET['action'];
        switch ($action) {
            case 'publish':
                if (isset($page)) {
                    $page->setActive(true);
                } else {
                    $nodeId = intval($_GET['node']);
                    $langId = intval($_GET['lang']);
                    $arrFbLang = FWLanguage::getFallbackLanguageArray();
                    $fbLang = isset($arrFbLang[$langId]) ? $arrFbLang[$langId] : null;
                    if ($fbLang != null && $fbLang != $langId) {
                        $node = $this->getNodeRepository()->find($nodeId);
                        $page = new \Cx\Model\ContentManager\Page();
                        $page->setLang($langId);
                        $page->setNode($node);
                        $fbPage = $node->getPage($fbLang);
                        if ($fbPage) {
                            $page->setType('fallback');
                            $page->setTitle($fbPage->getTitle());
                            $page->setContentTitle($fbPage->getContentTitle());
                            $page->setSlug($fbPage->getSlug());
                            $page->setMetatitle($page->getMetatitle());
                            $page->setMetadesc($fbPage->getMetadesc());
                            $page->setMetakeys($fbPage->getMetakeys());
                            $page->setMetarobots($page->getMetarobots());
                            $this->em->persist($page);
                        }
                    } else {
                        echo json_encode(array(
                            'action' => 'new',
                            'node' =>  $nodeId,
                        ));
                        exit(0);
                    }
                }
                break;
            case 'unpublish':
                $page->setActive(false);
                break;
            case 'visible':
                $page->setDisplay(true);
                break;
            case 'hidden':
                $page->setDisplay(false);
                break;
            default:
                $action = 'error';
        }
        $this->em->flush();
        $result = array(
          'nodeId' => $page->getNode()->getId(),
          'pageId' => $page->getId(),
          'lang'   => FWLanguage::getLanguageCodeById($page->getLang()),
          'action' => $action,
        );
        echo json_encode($result);
        exit(0);
    }

    function getPageRepository()
    {
        return $this->pageRepository;
    }

    function getNodeRepository()
    {
        return $this->nodeRepository;
    }
}
?>
