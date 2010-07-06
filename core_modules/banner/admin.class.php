<?php

$_ARRAYLANG['TXT_BANNER_ADD_RELATION_BLOG'] = "Blog-Rubriken";
$_ARRAYLANG['TXT_BANNER_EDIT_RELATION_BLOG'] = "Blog-Rubriken";
$_ARRAYLANG['TXT_BANNER_SETTINGS_BLOG'] = "Blog-Banner aktiviert";

/**
 * Banner management
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_banner
 * @todo        Edit PHP DocBlocks!
 */

require_once ASCMS_CORE_MODULE_PATH . '/banner/bannerLib.class.php';
require_once ASCMS_CORE_PATH.'/Tree.class.php';

/**
 * Banner management
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_module_banner
 */
class Banner extends bannerLibrary {
    public $_objTpl;
    public $pageTitle;
    public $pageContent;
    public $strErrMessage = '';
    public $strOkMessage = '';
    public $_selectedLang;
    public $langId;
    public $arrSettings = array();

    /**
    * Teaser object
    *
    * @access private
    * @var object
    */
    public $_objTeaser;


    /**
    * Constructor
    *
    * @param  string
    * @access public
    */
    function __construct()
    {
        global  $_ARRAYLANG, $objInit, $objTemplate;

        $this->_objTpl = new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/banner/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $objTemplate->setVariable('CONTENT_NAVIGATION','<a href="?cmd=banner">'.$_ARRAYLANG['TXT_BANNER_MENU_OVERVIEW'].'</a>
                                                        <a href="?cmd=banner&amp;act=banner_add">'.$_ARRAYLANG['TXT_BANNER_MENU_BANNER_NEW'].'</a>
                                                        <a href="?cmd=banner&amp;act=settings">'.$_ARRAYLANG['TXT_BANNER_MENU_SETTINGS'].'</a>');

        $this->pageTitle = $_ARRAYLANG['TXT_BANNER_ADMINISTRATION'];
        $this->langId=$objInit->userFrontendLangId;
        $this->getSettings();
    }


    /**
    * Perform the requested banner-action
    *
    * @global     object        $objTemplate
    * @return    string        parsed content
    */
    function getPage(){
        global $objTemplate;

        if(!isset($_GET['act'])){
            $_GET['act'] = '';
        }

        switch($_GET['act']){
            case 'banner_add':
                $this->addBanner();
            break;
            case 'banner_insert':
                $this->insertBanner();
                $this->addBanner();
            break;
            case 'banner_delete':
                $intGroupId = $this->deleteBanner($_GET['id']);
                $this->showGroupDetails($intGroupId);
            break;
            case 'banner_status':
                $intGroupId = $this->changeBannerStatus($_GET['id']);
                $this->showGroupDetails($intGroupId);
            break;
            case 'banner_edit':
                $this->editBanner($_GET['id']);
            break;
            case 'banner_update':
                $intGroupId = $this->updateBanner();
                $this->showGroupDetails($intGroupId);
            break;
            case 'group_details':
                $this->showGroupDetails($_GET['id']);
            break;
            case 'group_status':
                $this->changeGroupStatus($_GET['id']);
                $this->showGroups();
            break;
            case 'group_empty':
                $this->emptyGroup($_GET['id']);
                $this->showGroups();
            break;
            case 'group_edit':
                $this->editGroup($_GET['id']);
            break;
            case 'group_update':
                $this->updateGroup();
                $this->showGroups();
            break;
            case 'settings':
                $this->showSettings();
            break;
            default:
                $this->showGroups();
        }

        $objTemplate->setVariable(array(
            'CONTENT_TITLE'                => $this->pageTitle,
            'CONTENT_OK_MESSAGE'        => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE'    => $this->strErrMessage,
            'ADMIN_CONTENT'                => $this->_objTpl->get()
        ));
    }


    /**
    * Get settings and store in obj-array
    *
    * @global    object        $objDatabase
    */
    function getSettings() {
        global $objDatabase;

        $objResult = $objDatabase->Execute('SELECT name,value FROM '.DBPREFIX.'module_banner_settings');
        while (!$objResult->EOF) {
            $this->arrSettings[$objResult->fields['name']] = $objResult->fields['value'];
            $objResult->MoveNext();
        }
    }

    /**
    * Show settings for banner-modul
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    */
    function showSettings() {
        global $objDatabase, $_ARRAYLANG;

        if (isset($_POST['frmSettings_Active'])) {
            $intStatus = intval($_POST['frmSettings_Active']);
            if ($intStatus == 1 || $intStatus == 0) {
                $objDatabase->Execute('    UPDATE    '.DBPREFIX.'settings
                                        SET        setvalue="'.$intStatus.'"
                                        WHERE    setname="bannerStatus"
                                        LIMIT    1
                                    ');

                require_once(ASCMS_CORE_PATH.'/settings.class.php');
                $objSettings = new settingsManager();
                $objSettings->writeSettingsFile();
            }

            $intNewsStatus = intval($_POST['frmSettings_News']);
            if ($intNewsStatus == 1 || $intNewsStatus == 0) {
                $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_settings
                                        SET        value="'.$intNewsStatus.'"
                                        WHERE    name="news_banner"
                                        LIMIT    1
                                    ');
            }

            $intContentStatus = intval($_POST['frmSettings_Content']);
            if ($intContentStatus == 1 || $intContentStatus == 0) {
                $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_settings
                                        SET        value="'.$intContentStatus.'"
                                        WHERE    name="content_banner"
                                        LIMIT    1
                                    ');
            }

            $intTeaserStatus = intval($_POST['frmSettings_Teaser']);
            if ($intTeaserStatus == 1 || $intTeaserStatus == 0) {
                $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_settings
                                        SET        value="'.$intTeaserStatus.'"
                                        WHERE    name="teaser_banner"
                                        LIMIT   1
                                    '); 
            }   
            
            $intTeaserStatus = intval($_POST['frmSettings_Level']);
            if ($intLevelStatus == 1 || $intLevelStatus == 0) {
                $objDatabase->Execute(' UPDATE  '.DBPREFIX.'module_banner_settings
                                        SET     value="'.$intTeaserStatus.'"
                                        WHERE   name="level_banner"
                                        LIMIT    1
                                    ');
            }

            $intContentStatus = intval($_POST['frmSettings_Blog']);
            if ($intContentStatus == 1 || $intContentStatus == 0) {
                $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_settings
                                        SET        value="'.$intContentStatus.'"
                                        WHERE    name="blog_banner"
                                        LIMIT    1
                                    ');
            }

            $this->strOkMessage = $_ARRAYLANG['TXT_BANNER_SETTINGS_SAVED'];

            $this->getSettings();
        }


        // initialize variables
        $this->_objTpl->loadTemplateFile('module_banner_settings.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_BANNER_MENU_SETTINGS'];
        $this->_objTpl->setVariable(array(
            'TXT_BANNER_SETTINGS_TITLE'        =>    $_ARRAYLANG['TXT_BANNER_MENU_SETTINGS'],
            'TXT_BANNER_SETTINGS_ACTIVE'    =>    $_ARRAYLANG['TXT_BANNER_SETTINGS_ACTIVE'],
            'TXT_BANNER_SETTINGS_ON'        =>    $_ARRAYLANG['TXT_BANNER_SETTINGS_ON'],
            'TXT_BANNER_SETTINGS_OFF'        =>    $_ARRAYLANG['TXT_BANNER_SETTINGS_OFF'],
            'TXT_BANNER_SETTINGS_NEWS'        =>    $_ARRAYLANG['TXT_BANNER_SETTINGS_NEWS'],
            'TXT_BANNER_SETTINGS_CONTENT'    =>    $_ARRAYLANG['TXT_BANNER_SETTINGS_CONTENT'],
            'TXT_BANNER_SETTINGS_TEASER'    =>    $_ARRAYLANG['TXT_BANNER_SETTINGS_TEASER'],
            'TXT_BANNER_SETTINGS_LEVEL'     =>  "Verzeichnis-Banner aktiviert",
            'TXT_BANNER_SETTINGS_SAVE'        =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION_SAVE'],
            'TXT_BANNER_SETTINGS_BLOG'  => $_ARRAYLANG['TXT_BANNER_SETTINGS_BLOG']
            ));

        $objResult = $objDatabase->Execute('SELECT    setvalue
                                            FROM    '.DBPREFIX.'settings
                                            WHERE    setname="bannerStatus"
                                            LIMIT    1
                                        ');
        $this->_objTpl->setVariable(array(
            'SETTINGS_ACTIVE_1'        =>    ($objResult->fields['setvalue'] == 1) ? 'checked' : '',
            'SETTINGS_ACTIVE_0'        =>    ($objResult->fields['setvalue'] == 0) ? 'checked' : '',
            'SETTINGS_NEWS_1'        =>    ($this->arrSettings['news_banner'] == 1) ? 'checked' : '',
            'SETTINGS_NEWS_0'        =>    ($this->arrSettings['news_banner'] == 0) ? 'checked' : '',
            'SETTINGS_CONTENT_1'    =>    ($this->arrSettings['content_banner'] == 1) ? 'checked' : '',
            'SETTINGS_CONTENT_0'    =>    ($this->arrSettings['content_banner'] == 0) ? 'checked' : '',
            'SETTINGS_TEASER_1'        =>    ($this->arrSettings['teaser_banner'] == 1) ? 'checked' : '',
            'SETTINGS_TEASER_0'        =>    ($this->arrSettings['teaser_banner'] == 0) ? 'checked' : '',
            'SETTINGS_LEVEL_1'      =>  ($this->arrSettings['level_banner'] == 1) ? 'checked' : '',
            'SETTINGS_LEVEL_0'      =>  ($this->arrSettings['level_banner'] == 0) ? 'checked' : '',
            'SETTINGS_BLOG_1'       => ($this->arrSettings['blog_banner'] == 1) ? 'checked=\"checked"' : '',
            'SETTINGS_BLOG_0'       => ($this->arrSettings['blog_banner'] == 0) ? 'checked=\"checked"' : '',
        ));
    }


    /**
    * Show overview of all groups
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    * @global     array        $_CONFIG
    * @access      private
    */
    function showGroups()
    {
        global $objDatabase, $_ARRAYLANG;

        switch ($_POST['frmShowOverview_MultiAction']) {
            case 'activate':
                if (isset($_POST['selectedGroupsId'])) {
                    foreach($_POST['selectedGroupsId'] as $intGroupId) {
                        $this->changeGroupStatus($intGroupId,1);
                    }
                }
            break;
            case 'deactivate':
                if (isset($_POST['selectedGroupsId'])) {
                    foreach($_POST['selectedGroupsId'] as $intGroupId) {
                        $this->changeGroupStatus($intGroupId,0);
                    }
                }
            break;
            default: //do nothing
        }

        // initialize variables
        $this->_objTpl->loadTemplateFile('module_banner_overview.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_OVERVIEW'];
        $this->_objTpl->setVariable(array(
            'TXT_BANNER_GROUP_STATUS'                =>    $_ARRAYLANG['TXT_BANNER_ADD_STATUS'],
            'TXT_BANNER_GROUP_STATUS_2'                =>    $_ARRAYLANG['TXT_BANNER_ADD_STATUS'],
            'TXT_BANNER_GROUP_GROUPS'                =>    $_ARRAYLANG['TXT_BANNER_ADD_GROUP'],
            'TXT_BANNER_GROUP_DESC'                    =>    $_ARRAYLANG['TXT_BANNER_GROUP_DESC'],
            'TXT_BANNER_GROUP_PLACEHOLDER'            =>    $_ARRAYLANG['TXT_BANNER_GROUP_PLACEHOLDER'],
            'TXT_BANNER_GROUP_BANCOUNT'                =>    $_ARRAYLANG['TXT_BANNER_GROUP_BANCOUNT'],
            'TXT_BANNER_GROUP_FUNCTIONS'            =>    $_ARRAYLANG['TXT_BANNER_GROUP_DETAILS_FUNCTIONS'],
            'TXT_BANNER_GROUP_EDIT'                    =>    $_ARRAYLANG['TXT_BANNER_GROUP_EDIT'],
            'TXT_BANNER_GROUP_EMPTY'                =>    $_ARRAYLANG['TXT_BANNER_GROUP_EMPTY'],
            'TXT_BANNER_GROUP_EMPTY_JS'                =>    $_ARRAYLANG['TXT_BANNER_GROUP_EMPTY_JS'],
            'TXT_BANNER_GROUP_DELETE_JS'            =>  $_ARRAYLANG['TXT_BANNER_GROUP_DELETE_JS'],
            'TXT_BANNER_GROUP_SELECT_ALL'            =>    $_ARRAYLANG['TXT_BANNER_SELECT_ALL'],
            'TXT_BANNER_GROUP_DESELECT_ALL'            =>    $_ARRAYLANG['TXT_BANNER_DESELECT_ALL'],
            'TXT_BANNER_GROUP_SUBMIT_SELECT'        =>    $_ARRAYLANG['TXT_BANNER_SUBMIT_SELECT'],
            'TXT_BANNER_GROUP_SUBMIT_ACTIVATE'        =>    $_ARRAYLANG['TXT_BANNER_SUBMIT_ACTIVATE'],
            'TXT_BANNER_GROUP_SUBMIT_DEACTIVATE'    =>    $_ARRAYLANG['TXT_BANNER_SUBMIT_DEACTIVATE'],
            ));

        $objResult = $objDatabase->Execute('SELECT        id,
                                                        name,
                                                        description,
                                                        placeholder_name,
                                                        status
                                            FROM        '.DBPREFIX.'module_banner_groups
                                            ORDER BY    id ASC');
        $i = 0;
        while (!$objResult->EOF) {
            $objSubResult = $objDatabase->Execute('    SELECT    id
                                                    FROM    '.DBPREFIX.'module_banner_system
                                                    WHERE    parent_id='.$objResult->fields['id'].'
                                                ');
            $intBannerCount = $objSubResult->RecordCount();
            $strStatusIcon = ($objResult->fields['status'] == 0) ? 'status_red.gif' : 'status_green.gif';

            $this->_objTpl->setVariable(array(
                'BANNER_GROUP_STYLE'             => ($i % 2)+1,
                'BANNER_GROUP_STATUS_PICTURE'     => $strStatusIcon,
                'BANNER_GROUP_ID'                => $objResult->fields['id'],
                'BANNER_GROUP_NAME'             => stripslashes($objResult->fields['name']),
                'BANNER_GROUP_DESCRIPTION'         => stripslashes($objResult->fields['description']),
                'BANNER_GROUP_PLACEHOLDER'         => stripslashes($objResult->fields['placeholder_name']),
                'BANNER_GROUP_BANCOUNT'            => $intBannerCount
            ));

            $this->_objTpl->parse('banner_group_row');

            $i++;
            $objResult->MoveNext();
        }
    }


    /**
    * Show the "add new Banner"-Template
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    * @global     array        $_CONFIG
    */
    function addBanner()
    {
        global $objDatabase, $_ARRAYLANG;

        // initialize variables
        $this->_objTpl->loadTemplateFile('module_banner_add.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_BANNER_ADD_TITLE'];

        // create new ContentTree instance
        $objContentTree = new ContentTree();
        foreach ($objContentTree->getTree() as $arrData) {
            $strSpacer     = '';
            $intLevel    = intval($arrData['level']);
            for ($i = 0; $i < $intLevel; $i++) {
                $strSpacer .= '&nbsp;&nbsp;';
            }
            $strPages .= '<option value="'.$arrData['catid'].'">'.$strSpacer.$arrData['catname'].' ('.$arrData['catid'].') </option>'."\n";
        }

        //get news-categories
        $objResult = $objDatabase->Execute('SELECT         catid,
                                                        name
                                            FROM        '.DBPREFIX.'module_news_categories
                                            ORDER BY    name ASC
                                        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $strNews .= '<option value="'.$objResult->fields['catid'].'">'.$objResult->fields['name'].' ('.$objResult->fields['catid'].') </option>'."\n";
                $objResult->MoveNext();
            }
        }

        //get news-teaser
        $objResult = $objDatabase->Execute('SELECT         id,
                                                        name
                                            FROM        '.DBPREFIX.'module_news_teaser_frame
                                            ORDER BY    name ASC
                                        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $strTeaser .= '<option value="'.$objResult->fields['id'].'">'.$objResult->fields['name'].' ('.$objResult->fields['id'].') </option>'."\n";
                $objResult->MoveNext();
            }
        }

        // get blog-categories
        $objResult = $objDatabase->Execute('
            SELECT
                `category_id` AS `id`,
                `name`
            FROM
                `'.DBPREFIX.'module_blog_categories`
            WHERE
                `lang_id` = '.BACKEND_LANG_ID.'
        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $strBlog .= '<option value="'.$objResult->fields['id'].'">'.$objResult->fields['name'].' ('.$objResult->fields['id'].') </option>'."\n";
                $objResult->MoveNext();
            }
        }
        
        //get directory-levels
        $strLevel = $this->getLevels('',1);

        $this->_objTpl->setVariable(array(
            'TXT_BANNER_ADD_TITLE'                      => $_ARRAYLANG['TXT_BANNER_ADD_TITLE'],
            'TXT_BANNER_ADD_NAME'                       => $_ARRAYLANG['TXT_BANNER_ADD_NAME'],
            'TXT_BANNER_ADD_GROUP'                      => $_ARRAYLANG['TXT_BANNER_ADD_GROUP'],
            'TXT_BANNER_ADD_GROUP_SELECT'               => $_ARRAYLANG['TXT_BANNER_ADD_GROUP_SELECT'],
            'TXT_BANNER_ADD_STATUS'                     => $_ARRAYLANG['TXT_BANNER_ADD_STATUS'],
            'TXT_BANNER_ADD_CODE'                       => $_ARRAYLANG['TXT_BANNER_ADD_CODE'],
            'TXT_BANNER_ADD_RELATION'                   => $_ARRAYLANG['TXT_BANNER_ADD_RELATION'],
            'TXT_BANNER_ADD_RELATION_CONTENT'           => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_CONTENT'],
            'TXT_BANNER_ADD_RELATION_NEWS'              => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_NEWS'],
            'TXT_BANNER_ADD_RELATION_TEASER'            => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_TEASER'],
            'TXT_BANNER_ADD_RELATION_LEVEL'             => "Verzeichnis-Ebenen",
            'TXT_BANNER_ADD_RELATION_SELECT'            => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_SELECT'],
            'TXT_BANNER_ADD_RELATION_DESELECT'          => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_DESELECT'],
            'TXT_BANNER_ADD_RELATION_SAVE'              => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_SAVE'],
            'TXT_BANNER_ADD_RELATION_BLOG'              => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_BLOG'],
            'BANNER_ADD_GROUP_MENU'                     => $this->getBannerGroupMenu(1, ''),
            'BANNER_ADD_RELATION_PAGES_UNSELECTED'      => $strPages,
            'BANNER_ADD_RELATION_NEWS_UNSELECTED'       => $strNews,
            'BANNER_ADD_RELATION_TEASER_UNSELECTED'     => $strTeaser,
            'BANNER_ADD_RELATION_LEVEL_UNSELECTED'      => $strLevel,
            'BANNER_ADD_RELATION_BLOG_UNSELECTED'       => $strBlog,
        ));
    }

    /**
    * Insert a banner into database
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    */
    function insertBanner() {
        global $objDatabase, $_ARRAYLANG;

        $strName     = htmlspecialchars(addslashes($_POST['bannerName']), ENT_QUOTES, CONTREXX_CHARSET);
        $intGroupId = intval($_POST['bannerGroupId']);
        $intStatus    = intval($_POST['bannerStatus']);
        $strCode    = contrexx_addslashes($_POST['bannerCode']);

        if (!empty($strName)    &&
            $intGroupId    != 0) {
            $objDatabase->Execute('    INSERT
                                    INTO    '.DBPREFIX.'module_banner_system
                                    SET        parent_id='.$intGroupId.',
                                            name="'.$strName.'",
                                            banner_code="'.$strCode.'",
                                            status='.$intStatus.'');

            $intInsertedId = $objDatabase->insert_id();

            if (is_array($_POST['selectedPages'])) {
                foreach ($_POST['selectedPages'] as $intPageId) {
                    $objDatabase->Execute('    INSERT
                                            INTO    '.DBPREFIX.'module_banner_relations
                                            SET        banner_id='.$intInsertedId.',
                                                    group_id='.$intGroupId.',
                                                    page_id='.$intPageId.',
                                                    type="content"
                                        ');
                }
            }

            if (is_array($_POST['selectedNews'])) {
                foreach ($_POST['selectedNews'] as $intPageId) {
                    $objDatabase->Execute('    INSERT
                                            INTO    '.DBPREFIX.'module_banner_relations
                                            SET        banner_id='.$intInsertedId.',
                                                    group_id='.$intGroupId.',
                                                    page_id='.$intPageId.',
                                                    type="news"
                                        ');
                }
            }

            if (is_array($_POST['selectedTeaser'])) {
                foreach ($_POST['selectedTeaser'] as $intPageId) {
                    $objDatabase->Execute('    INSERT
                                            INTO    '.DBPREFIX.'module_banner_relations
                                            SET        banner_id='.$intInsertedId.',
                                                    group_id='.$intGroupId.',
                                                    page_id='.$intPageId.',
                                                    type="teaser"
                                        ');
                }
            }

            if (is_array($_POST['selectedLevel'])) {
                foreach ($_POST['selectedLevel'] as $intPageId) {
                    $objDatabase->Execute(' INSERT
                                            INTO    '.DBPREFIX.'module_banner_relations
                                            SET     banner_id='.$intInsertedId.',
                                                    group_id='.$intGroupId.',
                                                    page_id='.$intPageId.',
                                                    type="level"
                                        ');
                }                   
            }

            if (is_array($_POST['selectedBlog'])) {
                foreach ($_POST['selectedBlog'] as $intBlogId) {
                    $objDatabase->execute('
                        INSERT INTO
                            `'.DBPREFIX.'module_banner_relations`
                        SET
                            `banner_id`='.$intInsertedId.',
                            `group_id`='.$intGroupId.',
                            `page_id`='.$intBlogId.',
                            type = "blog"
                    ');
                }
            }

            $this->setDefaultBanner($intGroupId);

            $this->strOkMessage = $_ARRAYLANG['TXT_BANNER_INSERT_DONE'];
        }
    }

    /**
    * Remove a banner from database
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    * @param     integer        $intBannerId: The banner with this id will be deleted

    * @return     integer        $intReturn: The old group of the deleted banner
    */
    function deleteBanner($intBannerId) {
        global $objDatabase,$_ARRAYLANG;

        $intBannerId = intval($intBannerId);

        $objResult = $objDatabase->Execute('SELECT    parent_id
                                            FROM    '.DBPREFIX.'module_banner_system
                                            WHERE    id='.$intBannerId.'
                                            LIMIT    1
                                        ');
        if ($objResult->RecordCount() == 1) {
            $intReturn = $objResult->fields['parent_id'];

            $objDatabase->Execute('    DELETE
                                    FROM    '.DBPREFIX.'module_banner_system
                                    WHERE    id='.$intBannerId.'
                                    LIMIT    1
                                ');

            $objDatabase->Execute('    DELETE
                                    FROM    '.DBPREFIX.'module_banner_relations
                                    WHERE    banner_id='.$intBannerId.'
                                ');

            $this->setDefaultBanner($intReturn);

            $this->strOkMessage = $_ARRAYLANG['TXT_BANNER_DELETE_DONE'];
        }

        return intval($intReturn);
    }

    /**
    * Change the status field of a banner (0 -> 1, 1 -> 0)
    *
    * @global    object        $objDatabase
    * @param     integer        $intBannerId: The banner with this id will be changed
    * @param     integer        $intStatus: If this isn't empty, the status from this value is set.
    * @return     integer        $intReturn: The old group of the changed banner
    */
    function changeBannerStatus($intBannerId,$intStatus='') {
        global $objDatabase;

        $intBannerId = intval($intBannerId);

        $objResult = $objDatabase->Execute('SELECT    parent_id,
                                                    status
                                            FROM    '.DBPREFIX.'module_banner_system
                                            WHERE    id='.$intBannerId.'
                                            LIMIT    1');
        if ($objResult->RecordCount() == 1) {
            $intReturn = $objResult->fields['parent_id'];

            if (!empty($intStatus)) {
                $intNewStatus = intval($intStatus);
            } else { //just invert status
                $intNewStatus = ($objResult->fields['status'] == 0) ? 1 : 0;
            }

            $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_system
                                    SET        status='.$intNewStatus.'
                                    WHERE    id='.$intBannerId.'
                                    LIMIT    1
                                ');
        }
        return intval($intReturn);
    }


    /**
    * Edit a banner
    *
    * @global    object        $objDatabase
    * @param     integer        $intBannerId: The banner with this id will be changed
    */
    function editBanner($intBannerId) {
        global $objDatabase, $_ARRAYLANG;

        $intBannerId = intval($intBannerId);

        // initialize variables
        $this->_objTpl->loadTemplateFile('module_banner_edit.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_BANNER_EDIT_TITLE'];
        $this->_objTpl->setVariable(array(
            'TXT_BANNER_EDIT_TITLE'                =>    $_ARRAYLANG['TXT_BANNER_EDIT_TITLE'],
            'TXT_BANNER_EDIT_NAME'                =>    $_ARRAYLANG['TXT_BANNER_ADD_NAME'],
            'TXT_BANNER_EDIT_GROUP'                =>    $_ARRAYLANG['TXT_BANNER_ADD_GROUP'],
            'TXT_BANNER_EDIT_GROUP_SELECT'        =>    $_ARRAYLANG['TXT_BANNER_ADD_GROUP_SELECT'],
            'TXT_BANNER_EDIT_STATUS'            =>    $_ARRAYLANG['TXT_BANNER_ADD_STATUS'],
            'TXT_BANNER_EDIT_CODE'                =>    $_ARRAYLANG['TXT_BANNER_ADD_CODE'],
            'TXT_BANNER_EDIT_RELATION'            =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION'],
            'TXT_BANNER_EDIT_RELATION_CONTENT'    =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION_CONTENT'],
            'TXT_BANNER_EDIT_RELATION_NEWS'        =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION_NEWS'],
            'TXT_BANNER_EDIT_RELATION_TEASER'    =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION_TEASER'],
            'TXT_BANNER_EDIT_RELATION_SELECT'    =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION_SELECT'],
            'TXT_BANNER_EDIT_RELATION_DESELECT'    =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION_DESELECT'],
            'TXT_BANNER_EDIT_RELATION_SAVE'        =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION_SAVE'],
            'TXT_BANNER_EDIT_RELATION_LEVEL'    =>  "Verzeichnis-Ebenen",
            'TXT_BANNER_EDIT_RELATION_BLOG'     => $_ARRAYLANG['TXT_BANNER_EDIT_RELATION_BLOG']
        ));

        //relation
        $objResult = $objDatabase->Execute('SELECT    page_id,
                                                    type
                                            FROM    '.DBPREFIX.'module_banner_relations
                                            WHERE    banner_id='.$intBannerId.'
                                        ');
        $arrRelationContent = array();
        $arrRelationNews     = array();
        $arrRelationTeaser    = array();
        $arrRelationBlog = array();

        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                switch($objResult->fields['type']) {
                    case 'news':
                        $arrRelationNews[$objResult->fields['page_id']] = '';
                    break;
                    case 'teaser':
                        $arrRelationTeaser[$objResult->fields['page_id']] = '';
                    break;
                    case 'blog'.
                        $arrRelationBlog[$objResult->fields['page_id']] = "";
                    break;
                    default:
                        $arrRelationContent[$objResult->fields['page_id']] = '';
                }
                $objResult->MoveNext();
            }
        }

        // create new ContentTree instance
        $objContentTree = new ContentTree();
        foreach ($objContentTree->getTree() as $arrData) {
            $strSpacer     = '';
            $intLevel    = intval($arrData['level']);
            for ($i = 0; $i < $intLevel; $i++) {
                $strSpacer .= '&nbsp;&nbsp;';
            }

            if (array_key_exists($arrData['catid'],$arrRelationContent)) {
                $strSelectedPages .= '<option value="'.$arrData['catid'].'">'.$strSpacer.htmlentities($arrData['catname'], ENT_QUOTES, CONTREXX_CHARSET).' ('.$arrData['catid'].') </option>'."\n";
            } else {
                $strUnselectedPages .= '<option value="'.$arrData['catid'].'">'.$strSpacer.htmlentities($arrData['catname'], ENT_QUOTES, CONTREXX_CHARSET).' ('.$arrData['catid'].') </option>'."\n";
            }
           }

        //get news-categories
        $objResult = $objDatabase->Execute('SELECT         catid,
                                                        name
                                            FROM        '.DBPREFIX.'module_news_categories
                                            ORDER BY    name ASC
                                        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                if (array_key_exists($objResult->fields['catid'],$arrRelationNews)) {
                    $strSelectedNews .= '<option value="'.$objResult->fields['catid'].'">'.htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET).' ('.$objResult->fields['catid'].') </option>'."\n";
                } else {
                    $strUnselectedNews .= '<option value="'.$objResult->fields['catid'].'">'.htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET).' ('.$objResult->fields['catid'].') </option>'."\n";
                }
                $objResult->MoveNext();
            }
        }

        //get teaser-categories
        $objResult = $objDatabase->Execute('SELECT         id,
                                                        name
                                            FROM        '.DBPREFIX.'module_news_teaser_frame
                                            ORDER BY    name ASC
                                        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                if (array_key_exists($objResult->fields['id'],$arrRelationTeaser)) {
                    $strSelectedTeaser .= '<option value="'.$objResult->fields['id'].'">'.htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET).' ('.$objResult->fields['id'].') </option>'."\n";
                } else {
                    $strUnselectedTeaser .= '<option value="'.$objResult->fields['id'].'">'.htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET).' ('.$objResult->fields['id'].') </option>'."\n";
                }
                $objResult->MoveNext();
            }
        }

        // get blog-categories
        $objResult = $objDatabase->Execute('
            SELECT
                `category_id` AS `id`,
                `name`
            FROM
                `'.DBPREFIX.'module_blog_categories`
            WHERE
                `lang_id` = '.BACKEND_LANG_ID.'
        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                if (array_key_exists($objResult->fields['id'], $arrRelationBlog)) {
                    $strSelectedBlog .= '<option value="'.$objResult->fields['id'].'">'.htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET).' ('.$objResult->fields['id'].') </option>'."\n";
                } else {
                    $strUnselectedBlog .= '<option value="'.$objResult->fields['id'].'">'.htmlentities($objResult->fields['name'], ENT_QUOTES, CONTREXX_CHARSET).' ('.$objResult->fields['id'].') </option>'."\n";
                }
                $objResult->MoveNext();
            }
        }
        
        //get directory-levels
        $strUnselectedLevel     = $this->getLevels($intBannerId,1);
        $strSelectedLevel       = $this->getLevels($intBannerId,2);

           //values
        $objResult = $objDatabase->Execute('SELECT    parent_id,
                                                    name,
                                                    banner_code,
                                                    status
                                            FROM    '.DBPREFIX.'module_banner_system
                                            WHERE    id='.$intBannerId.'
                                            LIMIT    1
                                        ');
        $this->_objTpl->setVariable(array(
            'BANNER_EDIT_ID'                            =>    $intBannerId,
            'BANNER_EDIT_NAME'                            =>    stripslashes($objResult->fields['name']),
            'BANNER_EDIT_GROUP_MENU'                    =>    $this->getBannerGroupMenu(1,$objResult->fields['parent_id']),
            'BANNER_EDIT_STATUS'                        =>    ($objResult->fields['status'] == 1) ? 'checked' : '',
            'BANNER_EDIT_CODE'                            =>    htmlentities($objResult->fields['banner_code'], ENT_QUOTES, CONTREXX_CHARSET),
            'BANNER_EDIT_RELATION_PAGES_UNSELECTED'        =>    $strUnselectedPages,
            'BANNER_EDIT_RELATION_PAGES_SELECTED'        =>    $strSelectedPages,
            'BANNER_EDIT_RELATION_NEWS_UNSELECTED'        =>    $strUnselectedNews,
            'BANNER_EDIT_RELATION_NEWS_SELECTED'        =>    $strSelectedNews,
            'BANNER_EDIT_RELATION_TEASER_UNSELECTED'    =>    $strUnselectedTeaser,
            'BANNER_EDIT_RELATION_TEASER_SELECTED'      =>  $strSelectedTeaser,
            'BANNER_EDIT_RELATION_LEVEL_UNSELECTED'     =>  $strUnselectedLevel,
            'BANNER_EDIT_RELATION_LEVEL_SELECTED'       =>  $strSelectedLevel,
            'BANNER_EDIT_RELATION_BLOG_UNSELECTED'      => $strUnselectedBlog,
            'BANNER_EDIT_RELATION_BLOG_SELECTED'        => $strSelectedBlog
        ));         
    }


    /**
    * Update values for a banner
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    * @return     integer        $intReturn: The old group of the changed banner
    */
    function updateBanner() {
        global $objDatabase,$_ARRAYLANG;

        $intBannerId = intval($_POST['bannerId']);
        $strName     = htmlspecialchars(addslashes($_POST['bannerName']), ENT_QUOTES, CONTREXX_CHARSET);
        $intGroupId = intval($_POST['bannerGroupId']);
        $intStatus    = intval($_POST['bannerStatus']);
        $strCode    = contrexx_addslashes($_POST['bannerCode']);

        if (!empty($strName)    &&
            $intGroupId    != 0) {
            $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_system
                                    SET        parent_id='.$intGroupId.',
                                            name="'.$strName.'",
                                            banner_code="'.$strCode.'",
                                            status='.$intStatus.'
                                    WHERE    id='.$intBannerId.'
                                    LIMIT    1
                                ');

            $objDatabase->Execute('    DELETE
                                    FROM    '.DBPREFIX.'module_banner_relations
                                    WHERE    banner_id='.$intBannerId.'
                                ');

            if (is_array($_POST['selectedPages'])) {
                foreach ($_POST['selectedPages'] as $intPageId) {
                    $objDatabase->Execute('    INSERT
                                            INTO    '.DBPREFIX.'module_banner_relations
                                            SET        banner_id='.$intBannerId.',
                                                    group_id='.$intGroupId.',
                                                    page_id='.$intPageId.',
                                                    type="content"
                                        ');
                }
            }

            if (is_array($_POST['selectedNews'])) {
                foreach ($_POST['selectedNews'] as $intPageId) {
                    $objDatabase->Execute('    INSERT
                                            INTO    '.DBPREFIX.'module_banner_relations
                                            SET        banner_id='.$intBannerId.',
                                                    group_id='.$intGroupId.',
                                                    page_id='.$intPageId.',
                                                    type="news"
                                        ');
                }
            }

            if (is_array($_POST['selectedTeaser'])) {
                foreach ($_POST['selectedTeaser'] as $intPageId) {
                    $objDatabase->Execute('    INSERT
                                            INTO    '.DBPREFIX.'module_banner_relations
                                            SET        banner_id='.$intBannerId.',
                                                    group_id='.$intGroupId.',
                                                    page_id='.$intPageId.',
                                                    type="teaser"
                                        ');
                }
            }

            if (is_array($_POST['selectedLevel'])) {
                foreach ($_POST['selectedLevel'] as $intPageId) {
                    $objDatabase->Execute(' INSERT
                                            INTO    '.DBPREFIX.'module_banner_relations
                                            SET     banner_id='.$intBannerId.',
                                                    group_id='.$intGroupId.',
                                                    page_id='.$intPageId.',
                                                    type="level"
                                        ');
                }                   
            }       

            if (is_array($_POST['selectedBlog'])) {
                foreach ($_POST['selectedBlog'] as $intPageId) {
                    $objDatabase->Execute('
                        INSERT
                        INTO    '.DBPREFIX.'module_banner_relations
                        SET        banner_id='.$intBannerId.',
                                group_id='.$intGroupId.',
                                page_id='.$intPageId.',
                                type="blog"
                    ');
                }
            }
        
            $this->strOkMessage = $_ARRAYLANG['TXT_BANNER_UPDATE_DONE'];
        }

        return $intGroupId;
    }


    /**
    * Show all banners of a group
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    * @param     integer        $intGid: The group with this id will be shown
    */
    function showGroupDetails($intGid=0) {
        global $objDatabase,$_ARRAYLANG;

        switch ($_POST['frmShowBanner_MultiAction']) {
            case 'delete':
                if (isset($_POST['selectedBannerId'])) {
                    foreach($_POST['selectedBannerId'] as $intBannerId) {
                        $this->deleteBanner($intBannerId);
                    }
                }
            break;
            case 'activate':
                if (isset($_POST['selectedBannerId'])) {
                    foreach($_POST['selectedBannerId'] as $intBannerId) {
                        $this->changeBannerStatus($intBannerId,1);
                    }
                }
            break;
            case 'deactivate':
                if (isset($_POST['selectedBannerId'])) {
                    foreach($_POST['selectedBannerId'] as $intBannerId) {
                        $this->changeBannerStatus($intBannerId,0);
                    }
                }
            break;
            default: //do nothing
        }

        if (!empty($_POST['saveDefault'])) {
            $this->setDefaultBanner($intGid,$_POST['defaultBanner']);
        }

        $intGid = intval($intGid);

        $objContentTree = new ContentTree();

        $this->_objTpl->loadTemplateFile('module_banner_group_details.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_BANNER_GROUP_DETAILS_TITLE'];

        $this->_objTpl->setVariable(array(
            'BANNER_GROUP_ID'                               => $intGid,
            'TXT_BANNER_GROUP_DETAILS_STATUS'               => $_ARRAYLANG['TXT_BANNER_ADD_STATUS'],
            'TXT_BANNER_GROUP_DETAILS_STATUS_2'             => $_ARRAYLANG['TXT_BANNER_ADD_STATUS'],
            'TXT_BANNER_GROUP_DETAILS_DEFAULT'              => $_ARRAYLANG['TXT_BANNER_GROUP_DETAILS_DEFAULT'],
            'TXT_BANNER_GROUP_DETAILS_NAME'                 => $_ARRAYLANG['TXT_BANNER_ADD_NAME'],
            'TXT_BANNER_GROUP_DETAILS_RELATION_CONTENT'     => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_CONTENT'],
            'TXT_BANNER_GROUP_DETAILS_RELATION_NEWS'        => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_NEWS'],
            'TXT_BANNER_GROUP_DETAILS_RELATION_TEASER'      => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_TEASER'],
            'TXT_BANNER_GROUP_DETAILS_RELATION_BLOG'        => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_BLOG'],
            'TXT_BANNER_GROUP_DETAILS_FUNCTIONS'            => $_ARRAYLANG['TXT_BANNER_GROUP_DETAILS_FUNCTIONS'],
            'TXT_BANNER_GROUP_DETAILS_DELETE'               => $_ARRAYLANG['TXT_BANNER_GROUP_DETAILS_DELETE'],
            'TXT_BANNER_GROUP_DETAILS_DELETE_JS'            => $_ARRAYLANG['TXT_BANNER_GROUP_DETAILS_DELETE_JS'],
            'TXT_BANNER_GROUP_DETAILS_DELETE_ALL_JS'        => $_ARRAYLANG['TXT_BANNER_GROUP_DETAILS_DELETE_ALL_JS'],
            'TXT_BANNER_GROUP_DETAILS_EDIT'                 => $_ARRAYLANG['TXT_BANNER_GROUP_DETAILS_EDIT'],
            'TXT_BANNER_GROUP_DETAILS_SAVE'                 => $_ARRAYLANG['TXT_BANNER_ADD_RELATION_SAVE'],
            'TXT_BANNER_GROUP_DETAILS_SELECT_ALL'           => $_ARRAYLANG['TXT_BANNER_SELECT_ALL'],
            'TXT_BANNER_GROUP_DETAILS_DESELECT_ALL'         => $_ARRAYLANG['TXT_BANNER_DESELECT_ALL'],
            'TXT_BANNER_GROUP_DETAILS_SUBMIT_SELECT'        => $_ARRAYLANG['TXT_BANNER_SUBMIT_SELECT'],
            'TXT_BANNER_GROUP_DETAILS_SUBMIT_EXPAND'        => $_ARRAYLANG['TXT_BANNER_SUBMIT_EXPAND'],
            'TXT_BANNER_GROUP_DETAILS_SUBMIT_COMPRESS'      => $_ARRAYLANG['TXT_BANNER_SUBMIT_COMPRESS'],
            'TXT_BANNER_GROUP_DETAILS_SUBMIT_DELETE'        => $_ARRAYLANG['TXT_BANNER_SUBMIT_DELETE'],
            'TXT_BANNER_GROUP_DETAILS_SUBMIT_ACTIVATE'      => $_ARRAYLANG['TXT_BANNER_SUBMIT_ACTIVATE'],
            'TXT_BANNER_GROUP_DETAILS_SUBMIT_DEACTIVATE'    => $_ARRAYLANG['TXT_BANNER_SUBMIT_DEACTIVATE'],
            'TXT_BANNER_GROUP_DETAILS_RELATION_LEVEL'       => "Verzeichnis-Ebenen",
            ));

        $objResult = $objDatabase->Execute('SELECT        id,
                                                        name
                                            FROM        '.DBPREFIX.'module_banner_groups
                                            ORDER BY    id ASC
                                        ');
        if ($objResult->RecordCount() > 0) {
            $strDropDown = '<select name="ddGroup" onChange="window.location=this.options[this.selectedIndex].value">';
            while (!$objResult->EOF) {
                $strDropDown .= '<option value="'.CSRF::enhanceURI('index.php?cmd=banner').'&act=group_details&id='.$objResult->fields['id'].'"'.(($objResult->fields['id'] == $intGid) ? ' selected' : '').'>'.$objResult->fields['name'].'</option>';
                $objResult->MoveNext();
            }
            $strDropDown .= '</select>';
        }
        $this->_objTpl->setVariable('BANNER_GROUP_DROPDOWN',$strDropDown);

        //create news-cat-array
        $objResult = $objDatabase->Execute('SELECT         catid,
                                                        name
                                            FROM        '.DBPREFIX.'module_news_categories
                                            ORDER BY    name ASC
                                        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $arrNewsCategories[$objResult->fields['catid']] = $objResult->fields['name'];
                $objResult->MoveNext();
            }
        }

        //create teaser-cat-array ($arrTeaserCategories)
        $objResult = $objDatabase->Execute('SELECT         id,
                                                        name
                                            FROM        '.DBPREFIX.'module_news_teaser_frame
                                            ORDER BY    name ASC
                                        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                   $arrTeaserCategories[$objResult->fields['id']] = $objResult->fields['name'];
                $objResult->MoveNext();
            }
        }

        //create level-cat-array ($arrLevelCategories)
        $objResult = $objDatabase->Execute('SELECT      id,
                                                        name
                                            FROM        '.DBPREFIX.'module_directory_levels
                                            ORDER BY    name ASC
                                        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $arrLevelCategories[$objResult->fields['id']] = $objResult->fields['name'];
                $objResult->MoveNext();
            }
        }   

        $objResult = $objDatabase->Execute('
            SELECT
                `category_id` AS `id`,
                `name`
            FROM
                `'.DBPREFIX.'module_blog_categories`
            WHERE
                `lang_id` = '.BACKEND_LANG_ID.'
        ');
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $arrBlogCategories[$objResult->fields['id']] = $objResult->fields['name'];
                $objResult->MoveNext();
            }
        }
                
        $objResult = $objDatabase->Execute('SELECT        id,
                                                        name,
                                                        banner_code,
                                                        status,
                                                        is_default,
                                                        views,
                                                        clicks
                                            FROM        '.DBPREFIX.'module_banner_system
                                            WHERE        parent_id='.$intGid.'
                                            ORDER BY    is_default DESC,
                                                        name ASC
                                        ');

        $i = 0;
        if ($objResult->RecordCount() > 0) {
            while (!$objResult->EOF) {
                $objSubResult = $objDatabase->Execute('    SELECT    page_id,
                                                                type
                                                        FROM    '.DBPREFIX.'module_banner_relations
                                                        WHERE    banner_id='.$objResult->fields['id'].'
                                                    ');
                $x=0;
                $y=0;
                $z=0;
                $a = 0;
                $i=0;
                $strRelationsContent     = '';
                $strRelationsNews        = '';
                $strRelationsTeaser        = '';
                $strRelationsLevel      = '';
                $strRelationsBlog       = '';
                if ($objSubResult->RecordCount() > 0) {
                    while(!$objSubResult->EOF) {
                        switch ($objSubResult->fields['type']) {
                            case 'news':
                                if ($x <= 5) {
                                $strRelationsNews     .= '<a href="?cmd=news&amp;act=newscat">'.$arrNewsCategories[$objSubResult->fields['page_id']].' ('.$objSubResult->fields['page_id'].'</a>)<br />';
                                    $x++;
                                }
                            break;
                            case 'teaser':
                                if ($y <= 5) {
                                $strRelationsTeaser    .= '<a href="?cmd=news&amp;act=teasers&amp;tpl=editFrame&amp;frameId='.$objSubResult->fields['page_id'].'">'.$arrTeaserCategories[$objSubResult->fields['page_id']].' ('.$objSubResult->fields['page_id'].'</a>)<br />';
                                    $y++;
                                }
                            break;
                            case 'level':
                                if ($z <= 5) {
                                    $strRelationsLevel  .= '<a href="?cmd=directory&act=levels">'.$arrLevelCategories[$objSubResult->fields['page_id']].' ('.$objSubResult->fields['page_id'].'</a>)<br />';
                                    $z++;
                                }
                            break;
                            case 'blog':
                                if ($a <= 5) {
                                    $strRelationsBlog = '<a href="?cmd=blog&act=manageCategory">'.$arrBlogCategories[$objSubResult->fields['page_id']].' ('.$objSubResult->fields['page_id'].'</a>)<br />';
                                    $a++;
                                }
                            default:
                                if ($i <= 5) {
                                $arrValues = $objContentTree->getThisNode($objSubResult->fields['page_id']);
                                $strRelationsContent .= '<a href="?cmd=content&amp;act=edit&amp;pageId='.$arrValues['catid'].'">'.$arrValues['catname'].' ('.$arrValues['catid'].'</a>)<br />';
                                    $i++;
                                }
                        }
                        $objSubResult->MoveNext();
                    }
                }

                $strStatusIcon = ($objResult->fields['status'] == 0) ? 'status_red.gif' : 'status_green.gif';
                $this->_objTpl->setVariable(array(
                    'BANNER_ROWCLASS'             => ($objResult->fields['is_default'] == 0) ? 'row'.(($i % 2)+1) : 'rowWarn',
                    'BANNER_ID'                    => $objResult->fields['id'],
                    'BANNER_STATUS_ICON'         => $strStatusIcon,
                    'BANNER_DEFAULT'            => ($objResult->fields['is_default'] == 0) ? '' : 'checked',
                    'BANNER_NAME'                => stripslashes($objResult->fields['name']),
                    'BANNER_LIVE'                => stripslashes($objResult->fields['banner_code']),
                    'BANNER_CODE'                => htmlspecialchars($objResult->fields['banner_code'], ENT_QUOTES, CONTREXX_CHARSET),
                    'BANNER_RELATIONS_CONTENT'     => $strRelationsContent,
                    'BANNER_RELATIONS_NEWS'         => $strRelationsNews,
                    'BANNER_RELATIONS_TEASER'       => $strRelationsTeaser,
                    'BANNER_RELATIONS_LEVEL'        => $strRelationsLevel,
                    'BANNER_RELATIONS_BLOG'         => $strRelationsBlog,
                    'TXT_BANNER_STATS'          =>  "Statistik",
                    'TXT_BANNER_DIAGRAMM'       =>  "Diagramm",
                    'TXT_BANNER_VIEWS'          =>  "Anzeigen",
                    'BANNER_VIEWS'              =>  $objResult->fields['views'],
                    'TXT_BANNER_CLICKS'         =>  "Klicks",
                    'BANNER_CLICKS'             =>  $objResult->fields['clicks'],
                    'TXT_BANNER_RESTORE_STATS'  =>  "Statistik zurücksetzten",
                ));
                
            //  if (0>0) {
                    $this->_objTpl->setVariable(array(
                        'BANNER_GRAPH' => '<img style="border: 1px solid #000000;" src="'.ASCMS_PATH_OFFSET.'/core_modules/banner/graph.php?banner_id='.$objResult->fields['id'].'" width="200" height="100" />',
                    ));
            /*  } else {
                    $this->_objTpl->setVariable(array(
                        'BANNER_GRAPH' => "keine Daten verfügbar"
                    ));
                }*/
                

                $this->_objTpl->parse('showBanner');

                $i++;
                $objResult->MoveNext();
            }
        } else {
            $this->_objTpl->hideBlock('showBanner');
        }
    }


    /**
    * Change the status field of a group (0 -> 1, 1 -> 0)
    *
    * @global    object        $objDatabase
    * @param     integer        $intGroupId: The group with this id will be changed
    * @param     integer        $intStatus: If this isn't empty, the status from this value is set.
    */
    function changeGroupStatus($intGroupId,$intStatus='') {
        global $objDatabase;

        $intGroupId = intval($intGroupId);
        $objResult = $objDatabase->Execute('SELECT    status
                                            FROM    '.DBPREFIX.'module_banner_groups
                                            WHERE    id='.$intGroupId.'
                                            LIMIT    1');
        if ($objResult->RecordCount() == 1) {
            if (!empty($intStatus)) {
                $intNewStatus = intval($intStatus);
            } else { //just invert status
                $intNewStatus = ($objResult->fields['status'] == 0) ? 1 : 0;
            }

            $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_groups
                                    SET        status='.$intNewStatus.'
                                    WHERE    id='.$intGroupId.'
                                    LIMIT    1
                                ');
        }
    }


    /**
    * Delete all banners of a group
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    * @param     integer        $intGroupId: The banners of this group will be deleted
    */
    function emptyGroup($intGroupId) {
        global $objDatabase,$_ARRAYLANG;

        $intGroupId = intval($intGroupId);
        $objDatabase->Execute('    DELETE
                                FROM    '.DBPREFIX.'module_banner_system
                                WHERE    parent_id='.$intGroupId.'
                            ');
        $objDatabase->Execute('    DELETE
                                FROM    '.DBPREFIX.'module_banner_relations
                                WHERE    group_id='.$intGroupId.'
                            ');

        $this->strOkMessage = $_ARRAYLANG['TXT_BANNER_EMPTY_GROUP_DONE'];
    }


    /**
    * Show the edit-template for a group
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    * @param     integer        $intGroupId: This group will be loaded into the form
    */
    function editGroup($intGroupId) {
        global $objDatabase,$_ARRAYLANG;

        $intGroupId = intval($intGroupId);

        $this->_objTpl->loadTemplateFile('module_banner_group_edit.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_BANNER_GROUP_EDIT'];
        $this->_objTpl->setVariable(array(
            'TXT_GROUP_EDIT_TITLE'            =>    $_ARRAYLANG['TXT_BANNER_GROUP_EDIT'],
            'TXT_GROUP_EDIT_PLACEHOLDER'    =>    $_ARRAYLANG['TXT_BANNER_GROUP_PLACEHOLDER'],
            'TXT_GROUP_EDIT_NAME'            =>    $_ARRAYLANG['TXT_BANNER_ADD_NAME'],
            'TXT_GROUP_EDIT_DESC'            =>    $_ARRAYLANG['TXT_BANNER_GROUP_DESC'],
            'TXT_GROUP_EDIT_STATUS'            =>    $_ARRAYLANG['TXT_BANNER_ADD_STATUS'],
            'TXT_GROUP_EDIT_SAVE'            =>    $_ARRAYLANG['TXT_BANNER_ADD_RELATION_SAVE']
            ));

        $objResult = $objDatabase->Execute('SELECT    name,
                                                    description,
                                                    placeholder_name,
                                                    status
                                            FROM    '.DBPREFIX.'module_banner_groups
                                            WHERE    id='.$intGroupId.'
                                            LIMIT    1
                                        ');

        $this->_objTpl->setVariable(array(
            'GROUP_EDIT_ID'                =>    $intGroupId,
            'GROUP_EDIT_PLACEHOLDER'    =>    $objResult->fields['placeholder_name'],
            'GROUP_EDIT_NAME'            =>    stripslashes($objResult->fields['name']),
            'GROUP_EDIT_DESC'            =>    stripslashes($objResult->fields['description']),
            'GROUP_EDIT_STATUS'            =>    ($objResult->fields['status'] == 1) ? 'checked' : '',
            ));
    }

    /**
    * Save all values for a group
    *
    * @global    object        $objDatabase
    * @global     array        $_ARRAYLANG
    */
    function updateGroup() {
        global $objDatabase,$_ARRAYLANG;

        $intId         = intval($_POST['groupId']);
        $strName    = htmlspecialchars(addslashes($_POST['groupName']), ENT_QUOTES, CONTREXX_CHARSET);
        $strDesc    = htmlspecialchars(addslashes($_POST['groupDesc']), ENT_QUOTES, CONTREXX_CHARSET);
        $intStatus    = intval($_POST['groupStatus']);

        $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_groups
                                SET        name="'.$strName.'",
                                        description="'.$strDesc.'",
                                        status='.$intStatus.'
                                WHERE    id='.$intId.'
                                LIMIT    1
                            ');

        $this->strOkMessage = $_ARRAYLANG['TXT_BANNER_GROUP_UPDATE'];
    }


    /**
    * Define default banner
    *
    * @global    object        $objDatabase
    * @param     integer        $intGroupId: for this group the default should be set
    * @param     integer        $intBannerId: the banner with this id will be "defaulted". If it's empty, just take the first
    */
    function setDefaultBanner($intGroupId,$intBannerId=0) {
        global $objDatabase;

        $intGroupId     = intval($intGroupId);
        $intBannerId     = intval($intBannerId);

        if ($intGroupId != 0) {
            if ($intBannerId == 0) {
                //check for an existing "default", otherwise select the first alphabetically
                $objResult = $objDatabase->Execute('SELECT        id
                                                    FROM        '.DBPREFIX.'module_banner_system
                                                    WHERE        parent_id='.$intGroupId.'
                                                    ORDER BY    is_default DESC,
                                                                name ASC
                                                    LIMIT        1
                                                ');

                $intBannerId = intval($objResult->fields['id']);
            }

            if ($intBannerId != 0) {
                $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_system
                                        SET        is_default=1
                                        WHERE    id='.$intBannerId.'
                                        LIMIT    1
                                    ');

                $objDatabase->Execute('    UPDATE    '.DBPREFIX.'module_banner_system
                                        SET        is_default=0
                                        WHERE    id!='.$intBannerId.' AND
                                                parent_id='.$intGroupId.'
                                    ');
            }
        }
    }
}
?>
