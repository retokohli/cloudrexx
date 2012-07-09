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
use \Cx\Core\Json\Adapter\ContentManager\JsonPage;

require ASCMS_CORE_PATH . '/Module.class.php';
require_once ASCMS_CORE_PATH . '/json/adapter/contentmanager/JsonPage.class.php';

class ContentManagerException extends ModuleException {
    
}

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
        
        if ($this->act == 'new') {
            $this->act = ''; //default action;
        }

        $this->em = Env::em();
        $this->db = $db;
        $this->init = $init;
        $this->pageRepository = $this->em->getRepository('Cx\Model\ContentManager\Page');
        $this->nodeRepository = $this->em->getRepository('Cx\Model\ContentManager\Node');
        $this->defaultAct = 'actRenderCM';
    }

    protected function actRenderCM() {
        global $_ARRAYLANG, $_CORELANG, $_CONFIG;

        JS::activate('cx');
        JS::activate('ckeditor');
        JS::activate('cx-form');
        JS::activate('jstree');
        JS::registerJS('lib/javascript/lock.js');
        JS::registerJS('lib/javascript/jquery/jquery.history.js');

        $this->template->addBlockfile('ADMIN_CONTENT', 'content_manager', 'content_manager.html');
        $this->template->touchBlock('content_manager');
        $this->template->addBlockfile('CONTENT_MANAGER_MEAT', 'content_manager_meat', 'cm.html');
        $this->template->touchBlock('content_manager_meat');

        $_CORELANG['TXT_CORE_CM_SLUG_INFO'] = sprintf($_CORELANG['TXT_CORE_CM_SLUG_INFO'], $_CONFIG['domainUrl']);

        if (\Permission::checkAccess(78, 'static', true) &&
                \Permission::checkAccess(115, 'static', true)) {
            JS::registerCode("var publishAllowed = true;");
            $alias_permission = "block";
            $alias_denial = "none !important";
        } else {
            JS::registerCode("var publishAllowed = false;");
            $alias_permission = "none !important";
            $alias_denial = "block";
        }
        $this->template->setVariable(array(
            'ALIAS_PERMISSION' => $alias_permission,
            'ALIAS_DENIAL' => $alias_denial,
            'CONTREXX_BASE_URL' => ASCMS_PROTOCOL . '://' . $_CONFIG['domainUrl'] . ASCMS_PATH_OFFSET . '/',
        ));

        $this->setLanguageVars(array(
            //navi
            'TXT_NEW_PAGE', 'TXT_CONTENT_HISTORY', 'TXT_IMAGE_ADMINISTRATION',
            //site tree
            'TXT_CORE_CM_STATUS_PAGE', 'TXT_EXPAND_LINK', 'TXT_COLLAPS_LINK', 'TXT_CORE_CM_TRANSLATIONS', 'TXT_CORE_CM_SECTION_CMD', 'TXT_CORE_CM_DATE_USER',
            //multiple actions
            'TXT_SELECT_ALL', 'TXT_DESELECT_ALL', 'TXT_MULTISELECT_SELECT', 'TXT_MULTISELECT_PUBLISH', 'TXT_MULTISELECT_ACTIVATE', 'TXT_MULTISELECT_DEACTIVATE', 'TXT_MULTISELECT_SHOW', 'TXT_MULTISELECT_HIDE', 'TXT_MULTISELECT_DELETE',
            //type tab
            'TXT_CORE_CM_PAGE', 'TXT_CORE_CM_META', 'TXT_CORE_CM_ACCESS', 'TXT_CORE_CM_SETTINGS', 'TXT_CORE_CM_HISTORY', 'TXT_CORE_CM_PAGE_NAME', 'TXT_CORE_CM_PAGE_NAME_INFO', 'TXT_CORE_CM_PAGE_TITLE', 'TXT_CORE_CM_PAGE_TITLE_INFO', 'TXT_CORE_CM_TYPE', 'TXT_CORE_CM_TYPE_CONTENT', 'TXT_CORE_CM_TYPE_REDIRECT', 'TXT_CORE_CM_TYPE_APPLICATION', 'TXT_CORE_CM_TYPE_FALLBACK', 'TXT_CORE_CM_TYPE_CONTENT_INFO', 'TXT_CORE_CM_TYPE_REDIRECT_TARGET', 'TXT_CORE_CM_BROWSE', 'TXT_CORE_CM_TYPE_REDIRECT_INFO', 'TXT_CORE_CM_TYPE_APPLICATION', 'TXT_CORE_CM_TYPE_APPLICATION', 'TXT_CORE_CM_TYPE_APPLICATION_AREA', 'TXT_CORE_CM_TYPE_APPLICATION_INFO', 'TXT_CORE_CM_TYPE_FALLBACK_INFO', 'TXT_CORE_CM_SCHEDULED_PUBLISHING', 'TXT_CORE_CM_SCHEDULED_PUBLISHING_FROM', 'TXT_CORE_CM_SCHEDULED_PUBLISHING_TO', 'TXT_CORE_CM_SCHEDULED_PUBLISHING_INFO',
            //meta tab
            'TXT_CORE_CM_SE_INDEX', 'TXT_CORE_CM_METATITLE', 'TXT_CORE_CM_METATITLE_INFO', 'TXT_CORE_CM_METADESC', 'TXT_CORE_CM_METADESC_INFO', 'TXT_CORE_CM_METAKEYS', 'TXT_CORE_CM_METAKEYS_INFO',
            //access tab
            'TXT_CORE_CM_ACCESS_PROTECTION_FRONTEND', 'TXT_CORE_CM_ACCESS_PROTECTION_BACKEND',
            //advanced tab
            'TXT_CORE_CM_THEMES', 'TXT_CORE_CM_THEMES_INFO', 'TXT_CORE_CM_CUSTOM_CONTENT', 'TXT_CORE_CM_CUSTOM_CONTENT_INFO', 'TXT_CORE_CM_CSS_CLASS', 'TXT_CORE_CM_CSS_CLASS_INFO', 'TXT_CORE_CM_CACHE', 'TXT_CORE_CM_NAVIGATION', 'TXT_CORE_CM_LINK_TARGET', 'TXT_CORE_CM_LINK_TARGET_INO', 'TXT_CORE_CM_SLUG', 'TXT_CORE_CM_SLUG_INFO', 'TXT_CORE_CM_ALIAS', 'TXT_CORE_CM_ALIAS_INFO', 'TXT_CORE_CM_CSS_NAV_CLASS', 'TXT_CORE_CM_CSS_NAV_CLASS_INFO', 'TXT_CORE_CM_SOURCE_MODE',
            //settings tab
            'TXT_CORE_APPLICATION_AREA', 'TXT_CORE_APPLICATION', 'TXT_CORE_AREA', 'TXT_CORE_SKIN', 'TXT_CORE_CUSTOMCONTENT', 'TXT_CORE_REDIRECTION', 'TXT_CORE_CACHING', 'TXT_CORE_SLUG', 'TXT_CORE_CSSNAME',
            //bottom buttons
            'TXT_CORE_PREVIEW', 'TXT_CORE_SAVE_PUBLISH', 'TXT_CORE_SAVE', 'TXT_CORE_SUBMIT_FOR_RELEASE', 'TXT_CORE_REFUSE_RELEASE'
        ));

        $objCx = ContrexxJavascript::getInstance();
        $objCx->setVariable('TXT_CORE_CM_VIEW', $_CORELANG['TXT_CORE_CM_VIEW']);
        $objCx->setVariable('TXT_CORE_CM_ACTIONS', $_CORELANG['TXT_CORE_CM_ACTIONS']);

        $toggleTitles = !empty($_SESSION['contentManager']['toggleStatuses']['tabContent']['toggleTitles']) ? $_SESSION['contentManager']['toggleStatuses']['tabContent']['toggleTitles'] : 'block';
        $toggleType = !empty($_SESSION['contentManager']['toggleStatuses']['tabContent']['toggleType']) ? $_SESSION['contentManager']['toggleStatuses']['tabContent']['toggleType'] : 'block';
        $toggleThemes = !empty($_SESSION['contentManager']['toggleStatuses']['tabSettings']['toggleThemes']) ? $_SESSION['contentManager']['toggleStatuses']['tabSettings']['toggleThemes'] : 'block';
        $toggleNavigation = !empty($_SESSION['contentManager']['toggleStatuses']['tabSettings']['toggleNavigation']) ? $_SESSION['contentManager']['toggleStatuses']['tabSettings']['toggleNavigation'] : 'block';
        $toggleSidebar = !empty($_SESSION['contentManager']['toggleStatuses']['sidebar']) ? $_SESSION['contentManager']['toggleStatuses']['sidebar'] : 'block';
        $objCx->setVariable('toggleTitles', $toggleTitles);
        $objCx->setVariable('toggleType', $toggleType);
        $objCx->setVariable('toggleThemes', $toggleThemes);
        $objCx->setVariable('toggleNavigation', $toggleNavigation);
        $objCx->setVariable('sidebar', $toggleSidebar);

        if (!empty($_GET['act']) && ($_GET['act'] == 'new')) {
            $this->template->setVariable(array(
                'TITLES_DISPLAY_STYLE' => 'display: block;',
                'TITLES_TOGGLE_CLASS' => 'open',
                'TYPE_DISPLAY_STYLE' => 'display: block;',
                'TYPE_TOGGLE_CLASS' => 'open',
                'THEMES_DISPLAY_STYLE' => 'display: block;',
                'THEMES_TOGGLE_CLASS' => 'open',
                'NAVIGATION_DISPLAY_STYLE' => 'display: block;',
                'NAVIGATION_TOGGLE_CLASS' => 'open',
                'SIDEBAR_DISPLAY_STYLE' => 'display: block;',
                'SIDEBAR_CLASS_NAME' => 'hide',
                'MULTIPLE_ACTIONS_STRIKE_STYLE' => 'display: none;',
                'SELECT_MULTIPLE_ACTIONS_CLASS' => 'select-multiple-actions-shrunk',
            ));
        } else {
            $this->template->setVariable(array(
                'TITLES_DISPLAY_STYLE' => $toggleTitles == 'none' ? 'display: none;' : 'display: block;',
                'TITLES_TOGGLE_CLASS' => $toggleTitles == 'none' ? 'closed' : 'open',
                'TYPE_DISPLAY_STYLE' => $toggleType == 'none' ? 'display: none;' : 'display: block;',
                'TYPE_TOGGLE_CLASS' => $toggleType == 'none' ? 'closed' : 'open',
                'THEMES_DISPLAY_STYLE' => $toggleThemes == 'none' ? 'display: none;' : 'display: block;',
                'THEMES_TOGGLE_CLASS' => $toggleThemes == 'none' ? 'closed' : 'open',
                'NAVIGATION_DISPLAY_STYLE' => $toggleNavigation == 'none' ? 'display: none;' : 'display: block;',
                'NAVIGATION_TOGGLE_CLASS' => $toggleNavigation == 'none' ? 'closed' : 'open',
                'SIDEBAR_DISPLAY_STYLE' => $toggleSidebar == 'none' ? 'display: none;' : 'display: block;',
                'SIDEBAR_CLASS_NAME' => $toggleSidebar == 'none' ? 'show' : 'hide',
            ));
        }

        $modules = $this->db->Execute("SELECT * FROM " . DBPREFIX . "modules");
        while (!$modules->EOF) {
            $this->template->setVariable('MODULE_KEY', $modules->fields['name']);
//            $this->template->setVariable('MODULE_TITLE', $_CORELANG[$modules->fields['description_variable']]);
            $this->template->setVariable('MODULE_TITLE', ucwords($modules->fields['name']));
            $this->template->parse('module_option');
            $modules->MoveNext();
        }

        if (\Permission::checkAccess(78, 'static', true)) {
            $this->template->hideBlock('release_button');
        } else {
            $this->template->hideBlock('publish_button');
            $this->template->hideBlock('refuse_button');
        }

        $cm_hidden = '';
        $hide_list = '';
        if (isset($_GET['act']) && $_GET['act'] == 'new') {
            $hide_list = 'shrunk';
            $this->template->hideBlock('refuse_button');
        } else {
            $cm_hidden = ' style="display: none !important;"';
        }

        $cxjs = ContrexxJavascript::getInstance();
        $cxjs->setVariable('confirmDeleteQuestion', $_ARRAYLANG['TXT_CORE_CM_CONFIRM_DELETE']);
        $cxjs->setVariable('cleanAccessData', JsonPage::getAccessData());
        $cxjs->setVariable('contentTemplates', $this->getCustomContentTemplates());

        // TODO: move including of add'l JS dependencies to cx obj from /cadmin/index.html
        $this->template->setVariable('CXJS_INIT_JS', ContrexxJavascript::getInstance()->initJs());
        $this->template->setVariable('SKIN_OPTIONS', $this->getSkinOptions());
        $this->template->setVariable('LANGSWITCH_OPTIONS', $this->getLangOptions());
        $this->template->setVariable('LANGUAGE_ARRAY', json_encode($this->getLangArray()));
        $this->template->setVariable('FALLBACK_ARRAY', json_encode($this->getFallbackArray()));
        $this->template->setVariable('LANGUAGE_LABELS', json_encode($this->getLangLabels()));
        $this->template->setVariable('CM_HIDDEN', $cm_hidden);
        $this->template->setVariable('CM_HIDE_LIST', $hide_list);
        
        $this->template->setVariable(array(
            'TXT_EDITMODE_TITLE'   => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_TITLE'],
            'TXT_EDITMODE_TEXT'    => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_TEXT'],
            'TXT_EDITMODE_CODE'    => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_MODE_PAGE'],
            'TXT_EDITMODE_CONTENT' => $_CORELANG['TXT_FRONTEND_EDITING_SELECTION_MODE_CONTENT'],
        ));
    }

    /**
     * Sub of actRenderCM.
     * Renders the access tab.
     */
    protected function renderCMAccess() {
        $backendGroups = array();
        $frontendGroups = array();

        $objResult = $objDatabase->Execute("SELECT group_id, group_name FROM " . DBPREFIX . "access_user_groups");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $groupId = $objResult->fields['group_id'];
                $groupName = $objResult->fields['group_name'];
                $type = $objResult->fields['type'];
                if ($type == 'frontend')
                    $frontendGroups[$groupId] = $groupName;
                else
                    $backendGroups[$groupId] = $groupName;

                $objResult->MoveNext();
            }
        }
        return $arrGroups;
    }
    
    protected function getThemes() {
        $query = "SELECT id,themesname FROM " . DBPREFIX . "skins ORDER BY id";
        $rs = $this->db->Execute($query);

        $themes = array();
        while (!$rs->EOF) {
            $themes[$rs->fields['id']] = $rs->fields['themesname'];
            $rs->MoveNext();
        }
        return $themes;
    }

    protected function getSkinOptions() {
        $options = '';
        foreach ($this->getThemes() as $id=>$name) {
            $options .= '<option value="' . $id . '">' . $name . '</option>' . "\n";
        }
        return $options;
    }

    protected function getLangOptions() {
        $output = '';
        foreach (FWLanguage::getActiveFrontendLanguages() as $lang) {
            $selected = $lang['id'] == FRONTEND_LANG_ID ? ' selected="selected"' : '';
            $output .= '<option value="' . FWLanguage::getLanguageCodeById($lang['id']) . '"' . $selected . '>' . $lang['name'] . '</option>';
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
        foreach ($ids as $id) {
            $this->template->setVariable($id, $_CORELANG[$id]);
        }
    }

    protected function getCustomContentTemplates() {
        $templates = array();
        // foreach theme
        foreach ($this->getThemes() as $id=>$name) {
            $templates[$id] = $this->init->getCustomContentTemplatesForTheme($id);
        }
        return $templates;
        if (!isset($_GET['themeId']))
            throw new ContentManagerException('please provide a value for "themeId".');

        $module = isset($_GET['module']) ? $_GET['module'] : '';
        $themeId = intval($_GET['themeId']);
        $isHomeRequest = $module == 'home';

        $templates = $this->init->getCustomContentTemplatesForTheme($themeId);
        $matchingTemplates = array();

        foreach ($templates as $name) {
            $isHomeTemplate = substr($name, 0, 4) == 'home';
            if ($isHomeTemplate && $isHomeRequest)
                $matchingTemplates[] = $name;
            else if (!$isHomeTemplate && !$isHomeRequest)
                $matchingTemplates[] = $name;
        }

        return $matchingTemplates;
    }
}
