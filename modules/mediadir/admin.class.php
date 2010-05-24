<?php

/**
 * Media  Directory
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH . '/mediadir/lib/lib.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/entry.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/category.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/level.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/inputfield.class.php';
require_once ASCMS_LIBRARY_PATH . "/importexport/import.class.php";
require_once ASCMS_MODULE_PATH . '/mediadir/lib/settings.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/form.class.php';
require_once ASCMS_MODULE_PATH . '/mediadir/lib/comment.class.php';


class mediaDirectoryManager extends mediaDirectoryLibrary
{
    private $strOkMessage;
    public $strErrMessage;
    private $pageTitle;

    /**
     * Constructor
     */
    function __construct()
    {
        global  $_ARRAYLANG, $_CORELANG, $objTemplate;

        //globals
        $_ARRAYLANG['TXT_MEDIADIR_SPEZ_FIELDS'] = "Spezialangaben";
		$_ARRAYLANG['TXT_MEDIADIR_CHOOSE_USER'] = "Besitzer wählen";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DEFAULT_DISPLAYDURATION'] = "Standard Anzeigedauer";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DEFAULT_DISPLAYDURATION_INFO'] = "Mit dieser Option definieren sie, wielange ein Eintrag standardmässig angezeigt wird. Diese Angaben können zusätzlich bei jedem Eintrag einzeln definiert werden. ";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NOTIFICATION_DISPLAYDURATION'] = "Benachrichtigung bei Ablauf";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NOTIFICATION_DISPLAYDURATION_INFO'] = "Benachrichtigung bei Ablauf fsf sdf sdf sdf ";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_NOTIFICATION_DISPLAYDURATION_DAYSBEFOR'] = "Tag(e) vor Ablauf";
        $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION'] = "Anzeigedauer";
        $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION_ALWAYS'] = "Unbegrenzt";
        $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION_PERIOD'] = "Zeitspanne";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_DAY'] = "Tag(e)";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_MONTH'] = "Monat(e)";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_YEAR'] = "Jahr(e)";
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_DISPLAYDURATION_VALUE_TYPE_YEAR'] = "Jahr(e)";
        $_ARRAYLANG['TXT_MEDIADIR_MAIL_ACTION_NOTIFICATIONDISPLAYDURATION'] = "Benachrichtigung fÃ¼r ablaufende Anzeigedauer";
        $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION_RESET_NOTIFICATION_STATUS'] = "Benachrichtigungsstatus zurÃ¼cksetzen?";
        $_ARRAYLANG['TXT_MEDIADIR_DISPLAYNAME'] = 'Anzeigename';
        $_ARRAYLANG['TXT_MEDIADIR_NO_ENTRIES_FOUND'] = 'keine Einträge gefunden';
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_TRANSLATION_STATUS'] = 'Ãœbersetzungsstatus';
        $_ARRAYLANG['TXT_MEDIADIR_TRANSLATION_STATUS'] = 'Ãœbersetzungsstatus';
        $_ARRAYLANG['TXT_MEDIADIR_SETTINGS_TRANSLATION_STATUS_INFO'] = 'Wird der Übersetzungsstatus aktiviert, kann bei den einzelnen Einträgen angegeben werden, ob eine Sprache fertig übersetzt ist. Sollte eine Sprache noch nicht übersetzt sein, wird jene Sprache angezeigt in welcher der Eintrag erfasst wurde.';
        $_ARRAYLANG['TXT_MEDIADIR_CMD'] = 'Parameter (cmd)';
        $_ARRAYLANG['TXT_MEDIADIR_CMD_INFO'] = 'Mit diesem Parameter kann auf der Modulseite die Auswahl der Einträge auf diese Formular Vorlage reduziert werden.';
        $_ARRAYLANG['TXT_MEDIADIR_USE_CATEGORY'] = 'Kategorien verwenden';
        $_ARRAYLANG['TXT_MEDIADIR_USE_CATEGORY_INFO'] = 'Mit dieser Option kann die Zuordnung der Kategorien bei dieser Formular Vorlage ein- bzw. asusgeschaltet werden.';
        $_ARRAYLANG['TXT_MEDIADIR_USE_LEVEL'] = 'Ebenen verwenden';
        $_ARRAYLANG['TXT_MEDIADIR_USE_LEVEL_INFO'] = 'Mit dieser Option kann die Zuordnung der Ebenen bei dieser Formular Vorlage ein- bzw. asusgeschaltet werden.';

        //globals
        parent::__construct(ASCMS_MODULE_PATH.'/mediadir/template');
        parent::getFrontendLanguages();

        $objTemplate->setVariable('CONTENT_NAVIGATION','<a href="index.php?cmd='.$this->moduleName.'">'.$_ARRAYLANG['TXT_MEDIADIR_OVERVIEW'].'</a>
                                                        <a href="index.php?cmd='.$this->moduleName.'&amp;act=modify_entry">'.$_ARRAYLANG['TXT_MEDIADIR_ADD_ENTRY'].'</a>
                                                        <a href="index.php?cmd='.$this->moduleName.'&amp;act=entries">'.$_ARRAYLANG['TXT_MEDIADIR_MANAGE_ENTRIES'].'</a>
                                                        <!-- <a href="index.php?cmd='.$this->moduleName.'&amp;act=interfaces">'.$_ARRAYLANG['TXT_MEDIADIR_INTERFACES'].'</a> -->
                                                        <a href="index.php?cmd='.$this->moduleName.'&amp;act=settings">'.$_CORELANG['TXT_SETTINGS'].'</a>');
        
        /*$this->_objTpl->setGlobalVariable(array(
            'MODULE_NAME' =>  $this->moduleName,
            'CSRF' =>  'csrf='.CSRF::code(),
        ));*/
        
        
        /*echo $this->moduleName."<br />";
        echo $this->moduleTablePrefix."<br />";
        echo $this->moduleLangVar."<br />";*/
    }

    /**
    * get page
    *
    * Reads the act and selects the right action
    *
    * @access   public
    * @return   string  parsed content
    */
    function getPage()
    {
        global  $_ARRAYLANG, $objTemplate;

        switch ($_GET['act']) {
            case 'modify_entry':
                $this->modifyEntry();
                break;
            case 'modify_category':
                $this->modifyCategory();
                break;
            case 'modify_level':
                $this->modifyLevel();
                break;
            case 'entries':
            case 'move_entry':
            case 'delete_entry':
            case 'restore_voting':
            case 'restore_comments':
            case 'confirm_entry':
                $this->manageEntries();
                break;
            case 'interfaces':
                $this->interfaces();
                break;
            case 'settings':
                $this->settings();
                break;
            case 'switchState':
    		    $this->switchState();
    		    break;
            case 'delete_comment':
            case 'comments':
    		    $this->manageComments();
    		    break;
    		case 'delete_level':
    		case 'delete_category':
            default:
                $this->overview();
                break;
        }

        $objTemplate->setVariable(array(
            'CONTENT_OK_MESSAGE'     => $this->strOkMessage,
            'CONTENT_STATUS_MESSAGE' => $this->strErrMessage,
            'CONTENT_TITLE'          => $this->pageTitle,
            'ADMIN_CONTENT'          => $this->_objTpl->get(),
        ));

        return $this->_objTpl->get();
    }

    function overview()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_overview.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_MEDIADIR_OVERVIEW'];

        switch ($_GET['act']) {
            case 'delete_level':
                $objLevel = new mediaDirectoryLevel();
                $strStatus = $objLevel->deleteLevel(intval($_GET['id']));

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_LEVEL']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_LEVEL']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
                }
                break;
            case 'order_level':
                $objLevel = new mediaDirectoryLevel();
                $strStatus = $objLevel->saveOrder($_POST);

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_LEVELS']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_EDITED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_LEVELS']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_EDITED'];
                }
                break;
            case 'delete_category':
                $objCategory = new mediaDirectoryCategory();
                $strStatus = $objCategory->deleteCategory(intval($_GET['id']));

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
                }
                break;
            case 'order_category':
                $objCategory = new mediaDirectoryCategory();
                $strStatus = $objCategory->saveOrder($_POST);

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_CATEGORIES']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_EDITED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_CATEGORIES']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_EDITED'];
                }
                break;
        }

        //get seting values
        parent::getSettings();

        //get search dropdowns
        $objCategories = new mediaDirectoryCategory();
        $catDropdown = $objCategories->listCategories(null, 3, $intCategoryId);

        $objLevels = new mediaDirectoryLevel();
        $levelDropdown = $objLevels->listLevels(null, 3, $intLevelId);

        //parse global variables
        $this->_objTpl->setGlobalVariable(array(
            'TXT_CONFIRM' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM'],
            'TXT_VIEW' => $_ARRAYLANG['TXT_MEDIADIR_VIEW'],
            'TXT_EDIT' => $_ARRAYLANG['TXT_MEDIADIR_EDIT'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_MEDIADIR_DELETE'],
            'TXT_ENTRY_SEARCH' => $_ARRAYLANG['TXT_MEDIADIR_ENTRY_SEARCH'],
            'TXT_SEARCH' => $_CORELANG['TXT_SEARCH'],
            'TXT_SELECT_ALL' => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL' => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_SELECT_ACTION' => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_CONFIRM_ALL' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_ALL'],
            'TXT_DELETE_ALL' => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'TXT_ALERT_DELETE_ALL' => $_CORELANG['TXT_DELETE_HISTORY_ALL'],
            'TXT_ALERT_ACTION_IS_IRREVERSIBLE' => $_CORELANG['TXT_ACTION_IS_IRREVERSIBLE'],
            'TXT_ALERT_MAKE_SELECTION' => $_ARRAYLANG['TXT_MEDIADIR_ALERT_MAKE_SELECTION'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_MEDIADIR_FUNCTIONS'],
            'TXT_CONFIRM_LIST' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_LIST'],
            'TXT_LATEST_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_LATEST_ENTRIES'],
            'TXT_EXPAND_LINK' => $_CORELANG['TXT_EXPAND_LINK'],
            'TXT_COLLAPS_LINK' => $_CORELANG['TXT_COLLAPS_LINK'], 
            'TXT_'.$this->moduleLangVar.'_NAME' => $_CORELANG['TXT_NAME'],
            'TXT_'.$this->moduleLangVar.'_DATE' => $_CORELANG['TXT_DATE'],
            'TXT_'.$this->moduleLangVar.'_AUTHOR' => $_ARRAYLANG['TXT_MEDIADIR_AUTHOR'],
            'TXT_'.$this->moduleLangVar.'_HITS' => $_ARRAYLANG['TXT_MEDIADIR_HITS'],
            'TXT_'.$this->moduleLangVar.'_ACTION' => $_CORELANG['TXT_HISTORY_ACTION'],
            'TXT_'.$this->moduleLangVar.'_CONFIRM_DELETE_DATA' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_DELETE_DATA'],
            'TXT_'.$this->moduleLangVar.'_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE'],
            'TXT_'.$this->moduleLangVar.'_MAKE_SELECTION' => $_ARRAYLANG['TXT_MEDIADIR_MAKE_SELECTION'],
            'TXT_'.$this->moduleLangVar.'_STATUS' => $_CORELANG['TXT_STATUS'],
            'TXT_'.$this->moduleLangVar.'_SUBMIT' =>  $_CORELANG['TXT_SAVE'],
            'TXT_'.$this->moduleLangVar.'_ID_OR_SEARCH_TERM' =>  $_ARRAYLANG['TXT_MEDIADIR_ID_OR_SEARCH_TERM'],
            'TXT_'.$this->moduleLangVar.'_ALL_LEVELS' => $_ARRAYLANG['TXT_MEDIADIR_ALL_LEVELS'],
            'TXT_'.$this->moduleLangVar.'_ALL_CATEGORIES' => $_ARRAYLANG['TXT_MEDIADIR_ALL_CATEGORIES'],
            $this->moduleLangVar.'_CATEGORIES_DROPDOWN_OPTIONS' => $catDropdown,
            $this->moduleLangVar.'_LEVELS_DROPDOWN_OPTIONS' => $levelDropdown,
        ));

        if($this->arrSettings['settingsShowLevels'] == 1) {
            $this->_objTpl->touchBlock($this->moduleName.'LevelDoropdown');
        } else {
            $this->_objTpl->hideBlock($this->moduleName.'LevelDoropdown');
        }

        //show unconfirmed entries (if activated)
        if($this->arrSettings['settingsConfirmNewEntries'] == 1) {
            $objUnconfirmedEntries = new mediaDirectoryEntry();
            $objUnconfirmedEntries->getEntries(null,null,null,null, null, 1,null,0,'n');
            $objUnconfirmedEntries->listEntries($this->_objTpl, 1);

            if(empty($objUnconfirmedEntries->arrEntries)) {
                $this->_objTpl->hideBlock('confirmBlock');
            }
        } else {
            $this->_objTpl->hideBlock('confirmBlock');
        }

        //show latest entries
        $objLatestEntries = new mediaDirectoryEntry();
        $objLatestEntries->getEntries(null,null,null,null, 1, null, null, 0, $this->arrSettings['settingsLatestNumBackend']);
        $objLatestEntries->listEntries($this->_objTpl, 1);

        if(empty($objLatestEntries->arrEntries)) {
            $this->_objTpl->hideBlock($this->moduleName.'LatestSelectAction');
        } else {
            $this->_objTpl->touchBlock($this->moduleName.'LatestSelectAction');
        }

        //show levels (if activated)
        if($this->arrSettings['settingsShowLevels'] == 1) {
            $objLevels = new mediaDirectoryLevel();
            $objLevels->listLevels($this->_objTpl, 1, null);


            if(isset($_GET['exp_cat']) || $_GET['act'] == 'order_category' || $_GET['act'] == 'delete_category') {
                $strTabLevelsDisplay = 'none';
                $strTabLevelsActive = '';
                $strTabCategoriesDisplay = 'block';
                $strTabCategoriesActive = 'class="active"';
            } else {
                $strTabLevelsDisplay = 'block';
                $strTabLevelsActive = 'class="active"';
                $strTabCategoriesDisplay = 'none';
                $strTabCategoriesActive = '';
            }

            $this->_objTpl->setVariable(array(
                'TXT_LEVELS' => $_ARRAYLANG['TXT_MEDIADIR_LEVELS'],
                'TAB_CATEGORIES_ACTIVE' => $strTabCategoriesActive,
                'TAB_LEVELS_ACTIVE' => $strTabLevelsActive,
            ));

            $this->_objTpl->parse('tabMenu');

            $this->_objTpl->setVariable(array(
                'TXT_LEVELS' => $_ARRAYLANG['TXT_MEDIADIR_LEVELS'],
                'TXT_ADD_LEVEL' => $_ARRAYLANG['TXT_MEDIADIR_LEVEL']. " ".$_ARRAYLANG['TXT_MEDIADIR_ADD'],
                'TAB_LEVELS_DISPLAY' => $strTabLevelsDisplay,
                'TAB_CATEGORIES_DISPLAY' => $strTabCategoriesDisplay,
            ));

           $this->_objTpl->parse('levelsTab');
        } else {
            $this->_objTpl->setVariable(array(
                'TAB_CATEGORIES_DISPLAY' => "block",
            ));

            $this->_objTpl->hideBlock('tabMenu');
            $this->_objTpl->hideBlock('levelsTab');
            $this->_objTpl->hideBlock($this->moduleName.'LevelsList');
        }


        //show categories
        $objCategories = new mediaDirectoryCategory();
        $objCategories->listCategories($this->_objTpl, 1, null);

        $this->_objTpl->setVariable(array(
            'TXT_CATEGORIES' => $_ARRAYLANG['TXT_MEDIADIR_CATEGORIES'],
            'TXT_ADD_CATEGORY' => $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']. " ".$_ARRAYLANG['TXT_MEDIADIR_ADD'],
        ));
    }



    function modifyEntry()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_modify_entry.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_MEDIADIR_ENTRIES'];
        
         //get seting values
        parent::getSettings();

        if(intval($_GET['id']) != 0) {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']. " ".$_ARRAYLANG['TXT_MEDIADIR_EDIT'];
            $intEntryId = intval($_GET['id']);
        } else {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']. " ".$_ARRAYLANG['TXT_MEDIADIR_ADD'];
            $intEntryId = null;
        }

        //count forms
        $objForms = new mediaDirectoryForm();
        $arrActiveForms = array();

        foreach ($objForms->arrForms as $intFormId => $arrForm) {
            if($arrForm['formActive'] == 1) {
                $arrActiveForms[] = $intFormId;
            }
        }

        $intCountForms = count($arrActiveForms);

        if($intCountForms > 0) {
            if(intval($intEntryId) == 0 && (intval($_POST['selectedFormId']) == 0 && intval($_POST['formId']) == 0) && $intCountForms > 1) {
                $intFormId = null;

                //get form selector
                $objForms->listForms($this->_objTpl, 2, $intFormId);

                //parse blocks
                $this->_objTpl->hideBlock($this->moduleName.'InputfieldList');
            } else {
                //save entry data
                if(isset($_POST['submitEntryModfyForm']) && intval($_POST['formId']) != 0) {
                    $objEntry = new mediaDirectoryEntry();
                    $status = $objEntry->saveEntry($_POST, intval($_POST['entryId']));

                    if(!empty($_POST['entryId'])) {
                        if($status == true) {
                            $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_EDITED'];
                        } else {
                            $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_EDITED'];
                        }
                    } else {
                        if($status == true) {
                            $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_ADDED'];
                        } else {
                            $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_ADDED'];
                        }
                    }
                }
                
                //get form id
                if(intval($intEntryId) != 0) {
                    //get entry data
                    $objEntry = new mediaDirectoryEntry();
                    $objEntry->getEntries($intEntryId, null, null, null, null, false,false);
                    
                    if(empty($objEntry->arrEntries)) {
                        $objEntry->getEntries($intEntryId, null, null, null, null, true,false);
                    }
                    
                    $intFormId = $objEntry->arrEntries[$intEntryId]['entryFormId'];
                } else {
                    //set form id
                    if($intCountForms == 1) {
                        $intFormId = intval($arrActiveForms[0]);
                    } else {
                        $intFormId = intval($_POST['selectedFormId']);
                    }

                    if(intval($_POST['formId']) != 0) {
                        $intFormId = intval($_POST['formId']);
                    }
                }

                //get inputfield object
                $objInputfields = new mediaDirectoryInputfield($intFormId);

                //list inputfields
                $objInputfields->listInputfields($this->_objTpl, 2, $intEntryId);
                
                //get translation status date
                if($this->arrSettings['settingsTranslationStatus'] == 1) {
                	$strUserRowClass = "row1";
                	
                	foreach ($this->arrFrontendLanguages as $key => $arrLang) {
                        if($intEntryId != 0) {
	                		if(in_array($arrLang['id'], $objEntry->arrEntries[$intEntryId]['entryTranslationStatus'])) {
	                			$strLangStatus = 'checked="checked"';
	                		} else {
	                            $strLangStatus = '';
	                		}
                        }
                		
	                    $this->_objTpl->setVariable(array(
	                        'TXT_'.$this->moduleLangVar.'_TRANSLATION_LANG_NAME' => htmlspecialchars($arrLang['name'], ENT_QUOTES, CONTREXX_CHARSET),
	                        $this->moduleLangVar.'_TRANSLATION_LANG_ID' => intval($arrLang['id']),
	                        $this->moduleLangVar.'_TRANSLATION_LANG_STATUS' => $strLangStatus,
	                    ));
	                    
                        $this->_objTpl->parse($this->moduleName.'TranslationLangList');
                	}
                    
                    $this->_objTpl->parse($this->moduleName.'TranslationStatus');
                } else {
                    $strUserRowClass = "row2";
                    $this->_objTpl->hideBlock($this->moduleName.'TranslationStatus');
                }
                
                //get user data
                if($intEntryId != 0) {
                    $intEntryDourationStart = 1;
                    $intEntryDourationEnd = 2;
                    
                	$this->_objTpl->setVariable(array(
		                'TXT_'.$this->moduleLangVar.'_CHOOSE_USER' => $_ARRAYLANG['TXT_MEDIADIR_CHOOSE_USER'],
                        $this->moduleLangVar.'_USER_ROW' => $strUserRowClass,
		                $this->moduleLangVar.'_USER_LIST' => $objEntry->getUsers($intEntryId),
		            ));
		            
		            $this->_objTpl->parse($this->moduleName.'UserList');
                } else { 
                	
                	$intEntryDourationStart = 1;
                    $intEntryDourationEnd = 2;
                	$this->_objTpl->hideBlock($this->moduleName.'UserList');
                }
                
                //get display duration  data
                switch($this->arrSettings['settingsEntryDisplaydurationValueType']) {
                    case 1:
                        $intDiffDay = $this->arrSettings['settingsEntryDisplaydurationValue'];
                        $intDiffMonth = 0;
                        $intDiffYear = 0;
                        break;
                    case 2:
                        $intDiffDay = 0;
                        $intDiffMonth = $this->arrSettings['settingsEntryDisplaydurationValue'];
                        $intDiffYear = 0;
                        break;
                    case 3:
                        $intDiffDay = 0;
                        $intDiffMonth = 0;
                        $intDiffYear = $this->arrSettings['settingsEntryDisplaydurationValue'];
                        break;
                }
                
                if($intEntryId != 0) {
                	if(intval($objEntry->arrEntries[$intEntryId]['entryDurationType']) == 1) {
                		$intEntryDourationAlways = 'selected="selected"';
                		$intEntryDourationPeriod = '';
                        $intEntryDourationShowPeriod = 'none';
	                    $intEntryDourationStart = date("Y-m-d", mktime());
                        $intEntryDourationEnd = date("Y-m-d", mktime(0,0,0,date("m")+$intDiffMonth,date("d")+$intDiffDay,date("Y")+$intDiffYear));
                	} else {
                        $intEntryDourationAlways = '';
                        $intEntryDourationPeriod = 'selected="selected"';
                        $intEntryDourationShowPeriod = 'inline';
	                    $intEntryDourationStart = date("Y-m-d", $objEntry->arrEntries[$intEntryId]['entryDurationStart']);
	                    $intEntryDourationEnd = date("Y-m-d", $objEntry->arrEntries[$intEntryId]['entryDurationEnd']);
                	}
                	
                	if(intval($objEntry->arrEntries[$intEntryId]['entryDurationNotification']) == 1) {
                		$this->_objTpl->setVariable(array(
	                        $this->moduleLangVar.'_DISPLAYDURATION_RESET_NOTIFICATION_STATUS' => '<br /><input type="checkbox" name="durationResetNotification" value="1" />&nbsp;'.$_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION_RESET_NOTIFICATION_STATUS'],
	                    ));
                	}
                } else { 
                    if(intval($this->arrSettings['settingsEntryDisplaydurationType']) == 1) {
                        $intEntryDourationAlways = 'selected="selected"';
                        $intEntryDourationPeriod = '';
                        $intEntryDourationShowPeriod = 'none';
                    } else {
                        $intEntryDourationAlways = '';
                        $intEntryDourationPeriod = 'selected="selected"';
                        $intEntryDourationShowPeriod = 'inline';
                    }
        
                    $intEntryDourationStart = date("Y-m-d", mktime());
                    $intEntryDourationEnd = date("Y-m-d", mktime(0,0,0,date("m")+$intDiffMonth,date("d")+$intDiffDay,date("Y")+$intDiffYear));
                }

                //generate javascript
                parent::setJavascript($this->getSelectorJavascript());
                parent::setJavascript($objInputfields->getInputfieldJavascript());

                //get form onsubmit
                $strOnSubmit = $this->getFormOnSubmit();

                //parse blocks
                $this->_objTpl->hideBlock($this->moduleName.'FormList');
            }

            //parse global variables
            $this->_objTpl->setGlobalVariable(array(
                'TXT_'.$this->moduleLangVar.'_PAGE_TITLE' => $pageTitle,
                $this->moduleLangVar.'_ENTRY_ID' => $intEntryId,
                $this->moduleLangVar.'_FORM_ID' => $intFormId,
                'TXT_'.$this->moduleLangVar.'_SUBMIT' =>  $_CORELANG['TXT_SAVE'],
                $this->moduleLangVar.'_JAVASCRIPT' =>  $this->getJavascript(),
                $this->moduleLangVar.'_FORM_ONSUBMIT' =>  $strOnSubmit,
                'TXT_'.$this->moduleLangVar.'_PLEASE_CHECK_INPUT' =>  $_ARRAYLANG['TXT_MEDIADIR_PLEASE_CHECK_INPUT'],
                $this->moduleLangVar.'_DEFAULT_LANG_ID' =>  $_LANGID,
                'TXT_'.$this->moduleLangVar.'_SPEZ_FIELDS' => $_ARRAYLANG['TXT_MEDIADIR_SPEZ_FIELDS'],
                'TXT_'.$this->moduleLangVar.'_DISPLAYDURATION' =>  $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION'],
                'TXT_'.$this->moduleLangVar.'_DISPLAYDURATION_ALWAYS' =>  $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION_ALWAYS'],
                'TXT_'.$this->moduleLangVar.'_DISPLAYDURATION_PERIOD' =>  $_ARRAYLANG['TXT_MEDIADIR_DISPLAYDURATION_PERIOD'],
                'TXT_'.$this->moduleLangVar.'_DISPLAYDURATION_FROM' =>  $_CORELANG['TXT_FROM'],
                'TXT_'.$this->moduleLangVar.'_DISPLAYDURATION_TO' =>  $_CORELANG['TXT_TO'],
                $this->moduleLangVar.'_DISPLAYDURATION_START' =>  $intEntryDourationStart,
                $this->moduleLangVar.'_DISPLAYDURATION_END' =>  $intEntryDourationEnd,
                $this->moduleLangVar.'_DISPLAYDURATION_SELECT_ALWAYS' =>  $intEntryDourationAlways,
                $this->moduleLangVar.'_DISPLAYDURATION_SELECT_PERIOD' =>  $intEntryDourationPeriod,
                $this->moduleLangVar.'_DISPLAYDURATION_SHOW_PERIOD' =>  $intEntryDourationShowPeriod,
                'TXT_'.$this->moduleLangVar.'_TRANSLATION_STATUS' => $_ARRAYLANG['TXT_MEDIADIR_TRANSLATION_STATUS'],
            ));
        } else {
			header("Location: index.php?cmd='.$this->moduleName.'&act=settings&tpl=forms");
			exit;
        }
    }



    function modifyCategory()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_modify_category.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_MEDIADIR_CATEGORIES'];

        //get category object
        $objCategories = new mediaDirectoryCategory();

        //save category data
        if(isset($_POST['submitCategoryModfyForm'])) {
            $status = $objCategories->saveCategory($_POST, intval($_POST['categoryId']));

            if(!empty($_POST['categoryId'])) {
                if($status == true) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_EDITED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_EDITED'];
                }
            } else {
                if($status == true) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_ADDED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_ADDED'];
                }
            }
        }

        //load category data
        if(isset($_GET['id']) && $_GET['id'] != 0) {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']. " ".$_ARRAYLANG['TXT_MEDIADIR_EDIT'];
            $intCategoryId = intval($_GET['id']);

            $objCategory = new mediaDirectoryCategory($intCategoryId, null, 0);

            if($objCategory->arrCategories[$intCategoryId]['catShowEntries'] == 1) {
                $showEntriesOn = 'checked="checked"';
                $showEntriesOff = '';
            } else {
                $showEntriesOn = '';
                $showEntriesOff = 'checked="checked"';
            }

            if($objCategory->arrCategories[$intCategoryId]['catShowSubcategories'] == 1) {
                $showCategoriesOn = 'checked="checked"';
                $showCategoriesOff = '';
            } else {
                $showCategoriesOn = '';
                $showCategoriesOff = 'checked="checked"';
            }

            if($objCategory->arrCategories[$intCategoryId]['catActive'] == 1) {
                $activeOn = 'checked="checked"';
                $activeOff = '';
            } else {
                $activeOn = '';
                $activeOff = 'checked="checked"';
            }

            if(empty($objCategory->arrCategories[$intCategoryId]['catPicture']) || !file_exists(ASCMS_PATH.$objLevel->arrCategories[$intCategoryId]['catPicture'])) {
                $catImage = '<img src="images/content_manager/no_picture.gif" style="border: 1px solid #0A50A1; margin: 0px 0px 3px 0px;" /><br />';
            } else {
                $catImage = '<img src="'.$objCategory->arrCategories[$intCategoryId]['catPicture'].'.thumb" style="border: 1px solid #0A50A1; margin: 0px 0px 3px 0px;" /><br />';
            }

            //parse data variables
            $this->_objTpl->setGlobalVariable(array(
                $this->moduleLangVar.'_CATEGORY_ID' => $intCategoryId,
                $this->moduleLangVar.'_CATEGORY_NAME_MASTER' => $objCategory->arrCategories[$intCategoryId]['catName'][0],
                $this->moduleLangVar.'_CATEGORY_DESCRIPTION_MASTER' => $objCategory->arrCategories[$intCategoryId]['catDescription'][0],
                $this->moduleLangVar.'_CATEGORY_PICTURE' => $objCategory->arrCategories[$intCategoryId]['catPicture'],
                $this->moduleLangVar.'_CATEGORY_SHOW_ENTRIES_ON' => $showEntriesOn,
                $this->moduleLangVar.'_CATEGORY_SHOW_ENTRIES_OFF' => $showEntriesOff,
                $this->moduleLangVar.'_CATEGORY_ACTIVE_ON' => $activeOn,
                $this->moduleLangVar.'_CATEGORY_ACTIVE_OFF' => $activeOff,
                $this->moduleLangVar.'_CATEGORY_PICTURE_THUMB' => $catImage,
                $this->moduleLangVar.'_CATEGORY_SHOW_SUBCATEGORIES_ON' => $showCategoriesOn,
                $this->moduleLangVar.'_CATEGORY_SHOW_SUBCATEGORIES_OFF' => $showCategoriesOff,
            ));
        } else {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_CATEGORY']. " ".$_ARRAYLANG['TXT_MEDIADIR_ADD'];
            $intCategoryId = null;
            
            //parse global variables
	        $this->_objTpl->setGlobalVariable(array(
	            $this->moduleLangVar.'_CATEGORY_SHOW_ENTRIES_ON' => 'checked="checked"',
	            $this->moduleLangVar.'_CATEGORY_SHOW_SUBCATEGORIES_ON' => 'checked="checked"',
	            $this->moduleLangVar.'_CATEGORY_ACTIVE_ON' => 'checked="checked"',
	        ));
        }

        //get category dropdown
        $catDropdown = $objCategories->listCategories($this->_objTpl, 3, $intCategoryId);

        //parse global variables
        $this->_objTpl->setGlobalVariable(array(
            'TXT_'.$this->moduleLangVar.'_ACTIVATE' =>  $_ARRAYLANG['TXT_MEDIADIR_ACTIVATE'],
            'TXT_'.$this->moduleLangVar.'_DEACTIVATE' =>  $_ARRAYLANG['TXT_MEDIADIR_DEAVTIVATE'],
            'TXT_'.$this->moduleLangVar.'_NAME' =>  $_CORELANG['TXT_NAME'],
            'TXT_'.$this->moduleLangVar.'_DESCRIPTION' =>  $_CORELANG['TXT_DESCRIPTION'],
            'TXT_'.$this->moduleLangVar.'_PICTURE' =>  $_CORELANG['TXT_IMAGE'],
            'TXT_'.$this->moduleLangVar.'_SHOW_SUBCATEGORIES' =>  $_ARRAYLANG['TXT_MEDIADIR_SHOW_SUBCATEGORIES'],
            'TXT_'.$this->moduleLangVar.'_SHOW_ENTRIES' =>  $_ARRAYLANG['TXT_MEDIADIR_SHOW_ENTRIES'],
            'TXT_'.$this->moduleLangVar.'_VISIBLE' =>  $_CORELANG['TXT_VISIBLE'],
            'TXT_'.$this->moduleLangVar.'_CATEGORY' =>  $_ARRAYLANG['TXT_MEDIADIR_CATEGORY'],
            'TXT_'.$this->moduleLangVar.'_PAGE_TITLE' =>  $pageTitle,
            'TXT_'.$this->moduleLangVar.'_BROWSE' =>  $_CORELANG['TXT_BROWSE'],
            'TXT_'.$this->moduleLangVar.'_MORE' =>  $_ARRAYLANG['TXT_MEDIADIR_MORE'],
            'TXT_'.$this->moduleLangVar.'_SUBMIT' =>  $_CORELANG['TXT_SAVE'],
            'TXT_'.$this->moduleLangVar.'_NEW_CATEGORY' =>  "--- ".$_ARRAYLANG['TXT_MEDIADIR_NEW_CATEGORY']." ---",
            'TXT_'.$this->moduleLangVar.'_VISIBLE_CATEGORY_INFO' =>  $_ARRAYLANG['TXT_MEDIADIR_VISIBLE_CATEGORY_INFO'],
            $this->moduleLangVar.'_CATEGORIES_DROPDOWN_OPTIONS' => $catDropdown,
            $this->moduleLangVar.'_CATEGORY_DEFAULT_LANG_ID' => $_LANGID,
        ));

        //category name language block
        foreach ($this->arrFrontendLanguages as $key => $arrLang) {
            if(isset($intCategoryId)){
                $strCategoryName = empty($objCategory->arrCategories[$intCategoryId]['catName'][$arrLang['id']]) ? $objCategory->arrCategories[$intCategoryId]['catName'][0] : $objCategory->arrCategories[$intCategoryId]['catName'][$arrLang['id']];
            } else {
                $strCategoryName = '';
            }

            $this->_objTpl->setVariable(array(
                $this->moduleLangVar.'_CATEGORY_NAME_LANG_ID' => $arrLang['id'],
                'TXT_'.$this->moduleLangVar.'_CATEGORY_NAME_LANG_NAME' => $arrLang['name'],
                'TXT_'.$this->moduleLangVar.'_CATEGORY_NAME_LANG_SHORTCUT' => $arrLang['lang'],
                $this->moduleLangVar.'_CATEGORY_NAME' => $strCategoryName,
            ));

            if(($key+1) == count($this->arrFrontendLanguages)) {
                $this->_objTpl->setVariable(array(
                    $this->moduleLangVar.'_MINIMIZE' =>  '<a href="javascript:ExpandMinimize(\'categoryName\');">&laquo;&nbsp;'.$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE'].'</a>',
                ));
            }

            $this->_objTpl->parse($this->moduleName.'CategoryNameList');
        }

        //category description language block
        foreach ($this->arrFrontendLanguages as $key => $arrLang) {
            if(isset($intCategoryId)){
                $strCategoryDescription = empty($objCategory->arrCategories[$intCategoryId]['catDescription'][$arrLang['id']]) ? $objCategory->arrCategories[$intCategoryId]['catDescription'][0] : $objCategory->arrCategories[$intCategoryId]['catDescription'][$arrLang['id']];
            } else {
                $strCategoryDescription = '';
            }

            $this->_objTpl->setVariable(array(
                $this->moduleLangVar.'_CATEGORY_DESCRIPTION_LANG_ID' => $arrLang['id'],
                'TXT_'.$this->moduleLangVar.'_CATEGORY_DESCRIPTION_LANG_NAME' => $arrLang['name'],
                'TXT_'.$this->moduleLangVar.'_CATEGORY_DESCRIPTION_LANG_SHORTCUT' => $arrLang['lang'],
                $this->moduleLangVar.'_CATEGORY_DESCRIPTION' => $strCategoryDescription,
            ));

            if(($key+1) == count($this->arrFrontendLanguages)) {
                $this->_objTpl->setVariable(array(
                    $this->moduleLangVar.'_MINIMIZE' =>  '<a href="javascript:ExpandMinimize(\'categoryDescription\');">&laquo;&nbsp;'.$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE'].'</a>',
                ));
            }

            $this->_objTpl->parse($this->moduleName.'CategoryDescriptionList');
        }
    }



    /**
     * Switch the state of an entry (active or inactive)
     * This function is called through ajax, hence the 'die' at the end.
     */
    function switchState()
    {
        global $objDatabase;

        if (!isset($_GET['id']) && !isset($_GET['state']) && !isset($_GET['type'])) {
            die();
        }

        $intId = intval($_GET['id']);
        $intState = intval($_GET['state']);

        switch ($_GET['type']){
            case 'category':
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_categories SET active = '".$intState."' WHERE id = ".$intId;
                break;
            case 'level':
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_levels SET active = '".$intState."' WHERE id = ".$intId;
                break;
            case 'mail_template':
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_mails SET active = '".$intState."' WHERE id = ".$intId;
                break;
            case 'form_template':
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_forms SET active = '".$intState."' WHERE id = ".$intId;
                break;
            case 'entry':
                $query = "UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_entries SET active = '".$intState."' WHERE id = ".$intId;
                break;
        }

        $objDatabase->Execute($query);

        die();
    }



    function modifyLevel()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_modify_level.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_MEDIADIR_LEVELS'];

        //get level object
        $objLevels = new mediaDirectoryLevel;

        //save level data
        if(isset($_POST['submitLevelModfyForm'])) {
            $status = $objLevels->saveLevel($_POST, intval($_POST['levelId']));

            if(!empty($_POST['levelId'])) {
                if($status == true) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_LEVEL']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_EDITED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_LEVEL']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_EDITED'];
                }
            } else {
                if($status == true) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_LEVEL']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_ADDED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_LEVEL']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_ADDED'];
                }
            }
        }

        //load level dat
        if(isset($_GET['id']) && $_GET['id'] != 0) {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_LEVEL']. " ".$_ARRAYLANG['TXT_MEDIADIR_EDIT'];
            $intLevelId = intval($_GET['id']);

            $objLevel = new mediaDirectoryLevel($intLevelId, null, 0);

            if($objLevel->arrLevels[$intLevelId]['levelShowEntries'] == 1) {
                $showEntriesOn = 'checked="checked"';
                $showEntriesOff = '';
            } else {
                $showEntriesOn = '';
                $showEntriesOff = 'checked="checked"';
            }

            if($objLevel->arrLevels[$intLevelId]['levelShowSublevels'] == 1) {
                $showSublevelsOn = 'checked="checked"';
                $showSublevelsOff = '';
            } else {
                $showSublevelsOn = '';
                $showSublevelsOff = 'checked="checked"';
            }

            if($objLevel->arrLevels[$intLevelId]['levelShowCategories'] == 1) {
                $showCategoriesOn = 'checked="checked"';
                $showCategoriesOff = '';
            } else {
                $showCategoriesOn = '';
                $showCategoriesOff = 'checked="checked"';
            }

            if($objLevel->arrLevels[$intLevelId]['levelActive'] == 1) {
                $activeOn = 'checked="checked"';
                $activeOff = '';
            } else {
                $activeOn = '';
                $activeOff = 'checked="checked"';
            }

            if(empty($objLevel->arrLevels[$intLevelId]['levelPicture']) || !file_exists(ASCMS_PATH.$objLevel->arrLevels[$intLevelId]['levelPicture'])) {
                $levelImage = '<img src="images/content_manager/no_picture.gif" style="border: 1px solid #0A50A1; margin: 0px 0px 3px 0px;" /><br />';
            } else {
                $levelImage = '<img src="'.$objLevel->arrLevels[$intLevelId]['levelPicture'].'.thumb" style="border: 1px solid #0A50A1; margin: 0px 0px 3px 0px;" /><br />';
            }

            //parse data variables
            $this->_objTpl->setGlobalVariable(array(
                $this->moduleLangVar.'_LEVEL_ID' => $intLevelId,
                $this->moduleLangVar.'_LEVEL_NAME_MASTER' => $objLevel->arrLevels[$intLevelId]['levelName'][0],
                $this->moduleLangVar.'_LEVEL_DESCRIPTION_MASTER' => $objLevel->arrLevels[$intLevelId]['levelDescription'][0],
                $this->moduleLangVar.'_LEVEL_PICTURE' => $objLevel->arrLevels[$intLevelId]['levelPicture'],
                $this->moduleLangVar.'_LEVEL_SHOW_ENTRIES_ON' => $showEntriesOn,
                $this->moduleLangVar.'_LEVEL_SHOW_ENTRIES_OFF' => $showEntriesOff,
                $this->moduleLangVar.'_LEVEL_ACTIVE_ON' => $activeOn,
                $this->moduleLangVar.'_LEVEL_ACTIVE_OFF' => $activeOff,
                $this->moduleLangVar.'_LEVEL_PICTURE_THUMB' => $levelImage,
                $this->moduleLangVar.'_LEVEL_SHOW_SUBLEVELS_ON' => $showSublevelsOn,
                $this->moduleLangVar.'_LEVEL_SHOW_SUBLEVELS_OFF' => $showSublevelsOff,
                $this->moduleLangVar.'_LEVEL_SHOW_CATEGORIES_ON' => $showCategoriesOn,
                $this->moduleLangVar.'_LEVEL_SHOW_CATEGORIES_OFF' => $showCategoriesOff,
            ));
        } else {
            $pageTitle = $_ARRAYLANG['TXT_MEDIADIR_LEVEL']. " ".$_ARRAYLANG['TXT_MEDIADIR_ADD'];
            $intLevelId = null;
            
            //parse data variables
            $this->_objTpl->setGlobalVariable(array(
	            $this->moduleLangVar.'_LEVEL_SHOW_ENTRIES_OFF' => 'checked="checked"',
	            $this->moduleLangVar.'_LEVEL_SHOW_SUBLEVELS_ON' => 'checked="checked"',
	            $this->moduleLangVar.'_LEVEL_SHOW_CATEGORIES_ON' => 'checked="checked"',
	            $this->moduleLangVar.'_LEVEL_ACTIVE_ON' => 'checked="checked"',
            ));
        }
        
        //parse global variables
        $this->_objTpl->setGlobalVariable(array(
            'TXT_'.$this->moduleLangVar.'_ACTIVATE' =>  $_ARRAYLANG['TXT_MEDIADIR_ACTIVATE'],
            'TXT_'.$this->moduleLangVar.'_DEACTIVATE' =>  $_ARRAYLANG['TXT_MEDIADIR_DEAVTIVATE'],
            'TXT_'.$this->moduleLangVar.'_NAME' =>  $_CORELANG['TXT_NAME'],
            'TXT_'.$this->moduleLangVar.'_DESCRIPTION' =>  $_CORELANG['TXT_DESCRIPTION'],
            'TXT_'.$this->moduleLangVar.'_PICTURE' =>  $_CORELANG['TXT_IMAGE'],
            'TXT_'.$this->moduleLangVar.'_SHOW_SUBLEVELS' =>  $_ARRAYLANG['TXT_MEDIADIR_SHOW_SUBLEVELS'],
            'TXT_'.$this->moduleLangVar.'_SHOW_CATEGORIES' =>  $_ARRAYLANG['TXT_MEDIADIR_SHOW_CATEGORIES'],
            'TXT_'.$this->moduleLangVar.'_SHOW_ENTRIES' =>  $_ARRAYLANG['TXT_MEDIADIR_SHOW_ENTRIES'],
            'TXT_'.$this->moduleLangVar.'_VISIBLE' =>  $_CORELANG['TXT_VISIBLE'],
            'TXT_'.$this->moduleLangVar.'_LEVEL' =>  $_ARRAYLANG['TXT_MEDIADIR_LEVEL'],
            'TXT_'.$this->moduleLangVar.'_PAGE_TITLE' =>  $pageTitle,
            'TXT_'.$this->moduleLangVar.'_BROWSE' =>  $_CORELANG['TXT_BROWSE'],
            'TXT_'.$this->moduleLangVar.'_MORE' =>  $_ARRAYLANG['TXT_MEDIADIR_MORE'],
            'TXT_'.$this->moduleLangVar.'_SUBMIT' =>  $_CORELANG['TXT_SAVE'],
            'TXT_'.$this->moduleLangVar.'_NEW_LEVEL' =>  "--- ".$_ARRAYLANG['TXT_MEDIADIR_NEW_LEVEL']." ---",
            'TXT_'.$this->moduleLangVar.'_VISIBLE_LEVEL_INFO' =>  $_ARRAYLANG['TXT_MEDIADIR_VISIBLE_LEVEL_INFO'],
            $this->moduleLangVar.'_LEVEL_DEFAULT_LANG_ID' => $_LANGID,
        ));

        //get level dropdown
        $levelDropdown = $objLevels->listLevels($this->_objTpl, 3, $intLevelId);

        //level name language block
        foreach ($this->arrFrontendLanguages as $key => $arrLang) {
            if(isset($intLevelId)){
                $strLevelName = empty($objLevel->arrLevels[$intLevelId]['levelName'][$arrLang['id']]) ? $objLevel->arrLevels[$intLevelId]['levelName'][0] : $objLevel->arrLevels[$intLevelId]['levelName'][$arrLang['id']];
            } else {
                $strLevelName = '';
            }

            $this->_objTpl->setVariable(array(
                $this->moduleLangVar.'_LEVEL_NAME_LANG_ID' => $arrLang['id'],
                'TXT_'.$this->moduleLangVar.'_LEVEL_NAME_LANG_NAME' => $arrLang['name'],
                'TXT_'.$this->moduleLangVar.'_LEVEL_NAME_LANG_SHORTCUT' => $arrLang['lang'],
                $this->moduleLangVar.'_LEVEL_NAME' => $strLevelName,
                $this->moduleLangVar.'_LEVELS_DROPDOWN_OPTIONS' => $levelDropdown,
            ));

            if(($key+1) == count($this->arrFrontendLanguages)) {
                $this->_objTpl->setVariable(array(
                    $this->moduleLangVar.'_MINIMIZE' =>  '<a href="javascript:ExpandMinimize(\'levelName\');">&laquo;&nbsp;'.$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE'].'</a>',
                ));
            }

            $this->_objTpl->parse($this->moduleName.'LevelNameList');
        }

        //levvel description language block
        foreach ($this->arrFrontendLanguages as $key => $arrLang) {
            if(isset($intLevelId)){
                $strLevelDescription = empty($objLevel->arrLevels[$intLevelId]['levelDescription'][$arrLang['id']]) ? $objLevel->arrLevels[$intLevelId]['levelDescription'][0] : $objLevel->arrLevels[$intLevelId]['levelDescription'][$arrLang['id']];
            } else {
                $strLevelDescription = '';
            }

            $this->_objTpl->setVariable(array(
                $this->moduleLangVar.'_LEVEL_DESCRIPTION_LANG_ID' => $arrLang['id'],
                'TXT_'.$this->moduleLangVar.'_LEVEL_DESCRIPTION_LANG_NAME' => $arrLang['name'],
                'TXT_'.$this->moduleLangVar.'_LEVEL_DESCRIPTION_LANG_SHORTCUT' => $arrLang['lang'],
                $this->moduleLangVar.'_LEVEL_DESCRIPTION' => $strLevelDescription,
            ));

            if(($key+1) == count($this->arrFrontendLanguages)) {
                $this->_objTpl->setVariable(array(
                    $this->moduleLangVar.'_MINIMIZE' =>  '<a href="javascript:ExpandMinimize(\'levelDescription\');">&laquo;&nbsp;'.$_ARRAYLANG['TXT_MEDIADIR_MINIMIZE'].'</a>',
                ));
            }

            $this->_objTpl->parse($this->moduleName.'LevelDescriptionList');
        }
    }



    /**
     * Switch the state of an entry (active or inactive)
     * This function is called through ajax, hence the 'die' at the end.
     */
    function switchLevelState()
    {
        global $objDatabase;

        if (!isset($_GET['levelid']) && !isset($_GET['state'])) {
            die();
        }

        $intId = intval($_GET['levelid']);
        $intState = intval($_GET['state']);

        $query = "  UPDATE ".DBPREFIX."module_".$this->moduleTablePrefix."_levels
                    SET active = '".$intState."'
                    WHERE id = ".$intId;
        $objDatabase->Execute($query);

        die();
    }



    function manageEntries()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_manage_entries.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_MEDIADIR_MANAGE_ENTRIES'];

        if($_REQUEST['cat_id'] != '' && $_REQUEST['cat_id'] != 0) {
            $intCategoryId = intval($_REQUEST['cat_id']);
        } else {
            $intCategoryId = null;
        }

        if($_REQUEST['level_id'] != '' && $_REQUEST['level_id'] != 0) {
            $intLevelId = intval($_REQUEST['level_id']);
        } else {
            $intLevelId = null;
        }

        if($_REQUEST['term'] != '') {
            $strTerm = $_REQUEST['term'];
        } else {
            $strTerm = null;
        }


        $objCategories = new mediaDirectoryCategory();
        $catDropdown = $objCategories->listCategories(null, 3, $intCategoryId);

        $objLevels = new mediaDirectoryLevel();
        $levelDropdown = $objLevels->listLevels(null, 3, $intLevelId);

        //parse global variables
        $this->_objTpl->setGlobalVariable(array(
            'TXT_'.$this->moduleLangVar.'_PAGE_TITLE' => $this->pageTitle,
            'TXT_'.$this->moduleLangVar.'_SUBMIT' =>  $_CORELANG['TXT_SAVE'],
            $this->moduleLangVar.'_FORM_ONSUBMIT' =>  $strOnSubmit,
            'TXT_EDIT' => $_ARRAYLANG['TXT_MEDIADIR_EDIT'],
            'TXT_ENTRY_SEARCH' => $_ARRAYLANG['TXT_MEDIADIR_ENTRY_SEARCH'],
            'TXT_SEARCH' => $_CORELANG['TXT_SEARCH'],
            'TXT_MEDIADIR_STATUS' => $_CORELANG['TXT_STATUS'],
            'TXT_SELECT_ALL' => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL' => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_SELECT_ACTION' => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_FUNCTIONS' => $_ARRAYLANG['TXT_MEDIADIR_FUNCTIONS'],
            'TXT_DELETE' => $_ARRAYLANG['TXT_MEDIADIR_DELETE'],
            'TXT_DELETE_ALL' => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'TXT_'.$this->moduleLangVar.'_VOTING' => $_ARRAYLANG['TXT_MEDIADIR_VOTING'],
            'TXT_'.$this->moduleLangVar.'_COMMENTS' => $_ARRAYLANG['TXT_MEDIADIR_COMMENTS'],
            'TXT_'.$this->moduleLangVar.'_NAME' => $_CORELANG['TXT_NAME'],
            'TXT_'.$this->moduleLangVar.'_DATE' => $_CORELANG['TXT_DATE'],
            'TXT_'.$this->moduleLangVar.'_AUTHOR' => $_ARRAYLANG['TXT_MEDIADIR_AUTHOR'],
            'TXT_'.$this->moduleLangVar.'_HITS' => $_ARRAYLANG['TXT_MEDIADIR_HITS'],
            'TXT_'.$this->moduleLangVar.'_ACTION' => $_CORELANG['TXT_HISTORY_ACTION'],
            $this->moduleLangVar.'_SEARCH_TERM' => $strTerm != null ? $strTerm : $_ARRAYLANG['TXT_MEDIADIR_ID_OR_SEARCH_TERM'],
            $this->moduleLangVar.'_SEARCH_CATEGORY_ID' => $intCategoryId,
            $this->moduleLangVar.'_SEARCH_LEVEL_ID' => $intLevelId,
            'TXT_'.$this->moduleLangVar.'_MOVE_ALL' => $_ARRAYLANG['TXT_MEDIADIR_MOVE_ALL'],
            'TXT_'.$this->moduleLangVar.'_RESTORE_VOTING_ALL' => $_ARRAYLANG['TXT_MEDIADIR_RESTORE_VOTING_ALL'],
            'TXT_'.$this->moduleLangVar.'_RESTORE_COMMENTS_ALL' => $_ARRAYLANG['TXT_MEDIADIR_RESTORE_COMMENTS_ALL'],
            'TXT_'.$this->moduleLangVar.'_CONFIRM_DELETE_DATA' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_DELETE_DATA'],
            'TXT_'.$this->moduleLangVar.'_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE'],
            'TXT_'.$this->moduleLangVar.'_MAKE_SELECTION' => $_ARRAYLANG['TXT_MEDIADIR_MAKE_SELECTION'],
            'TXT_SELECT_ALL' => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL' => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_SELECT_ACTION' => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_DELETE_ALL' => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'TXT_'.$this->moduleLangVar.'_MOVE_ALL' => $_ARRAYLANG['TXT_MEDIADIR_MOVE_ALL'],
            'TXT_'.$this->moduleLangVar.'_ALL_LEVELS' => $_ARRAYLANG['TXT_MEDIADIR_ALL_LEVELS'],
            'TXT_'.$this->moduleLangVar.'_ALL_CATEGORIES' => $_ARRAYLANG['TXT_MEDIADIR_ALL_CATEGORIES'],
            $this->moduleLangVar.'_CATEGORIES_DROPDOWN_OPTIONS' => $catDropdown,
            $this->moduleLangVar.'_LEVELS_DROPDOWN_OPTIONS' => $levelDropdown,
        ));

        //get seting values
        parent::getSettings();

        if($this->arrSettings['settingsShowLevels'] == 1) {
            $this->_objTpl->touchBlock($this->moduleName.'LevelDoropdown');
        } else {
            $this->_objTpl->hideBlock($this->moduleName.'LevelDoropdown');
        }

        $objEntries = new mediaDirectoryEntry();

        switch ($_GET['act']) {
            case 'move_entry':
                $this->strErrMessage = "Diese Funktion ist zurzeit noch nicht implementiert.";
                break;
            case 'delete_entry':
                if (!isset($_GET['id'])) {
                    foreach ($_POST["entriesFormSelected"] as $intEntryId) {
                        $strStatus = $objEntries->deleteEntry(intval($intEntryId));
                    }
                } else {
                    $strStatus = $objEntries->deleteEntry(intval($_GET['id']));
                }

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
                }
                break;
            case 'restore_voting':
                $objVotes = new mediaDirectoryVoting();


                if (!isset($_GET['id'])) {
                    foreach ($_POST["entriesFormSelected"] as $intEntryId) {
                        $strStatus = $objVotes->restoreVoting(intval($intEntryId));
                    }
                } else {
                    $strStatus = $objVotes->restoreVoting(intval($_GET['id']));
                }

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_VOTING']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_VOTING']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
                }
                break;
            case 'restore_comments':
                $objComments = new mediaDirectoryComment();


                if (!isset($_GET['id'])) {
                    foreach ($_POST["entriesFormSelected"] as $intEntryId) {
                        $strStatus = $objComments->restoreComments(intval($intEntryId));
                    }
                } else {
                    $strStatus = $objComments->restoreComments(intval($_GET['id']));
                }

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_COMMENTS']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_COMMENTS']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
                }
                break;
                break;
            case 'confirm_entry':
                if (!isset($_GET['id'])) {
                    foreach ($_POST["entriesFormSelected"] as $intEntryId) {
                        $strStatus = $objEntries->confirmEntry(intval($intEntryId));
                    }
                } else {
                    $strStatus = $objEntries->confirmEntry(intval($_GET['id']));
                }

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_CONFIRM'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_ENTRY']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_CONFIRM'];
                }
                break;
        }

        $objEntries->getEntries(null,$intLevelId,$intCategoryId,$strTerm,null,null,null,null,'n');
        $objEntries->listEntries($this->_objTpl, 1);

        if(empty($objEntries->arrEntries)) {
             $this->_objTpl->hideBlock($this->moduleName.'EntriesSelectAction');
        } else {
             $this->_objTpl->touchBlock($this->moduleName.'EntriesSelectAction');
        }
    }



    function interfaces()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_interfaces.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_MEDIADIR_INTERFACES'];

        $this->_objTpl->setGlobalVariable(array(
            'TXT_'.$this->moduleLangVar.'_IMPORT' => $_ARRAYLANG['TXT_MEDIADIR_IMPORT'],
            'TXT_'.$this->moduleLangVar.'_EXPORT' => $_ARRAYLANG['TXT_MEDIADIR_EXPORT'],
        ));

        switch ($_GET['tpl']) {
            case 'exoprt':
                //$this->interfaces_export();
                //$this->_objTpl->parse('interfaces_content');
                break;
            case 'import':
            default:
                $this->interfaces_import();
        }

        $this->_objTpl->parse('interfaces_content');
    }



    function interfaces_import()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->addBlockfile($this->moduleLangVar.'_INTERFACES_CONTENT', 'interfaces_content', 'module_'.$this->moduleName.'_interfaces_import.html');

        $objImport = new Import();
    }



    function _importSelectFile()
    {
		/*$this->_objTpl->setVariable(array(
			"IMPORT_ACTION"		=> "?cmd=directory&amp;act=import",
			'TXT_FILETYPE'		=> 'Dateityp',
			"IMPORT_ADD_NAME"	=> 'Kategorie',
			"IMPORT_ADD_VALUE"	=>  "<select name=\"directory_category\" style=\"width:200px;\">\n".$this->getSearchCategories(0)."</select>",
			"IMPORT_ROWCLASS"	=> "row1",
			'TXT_HELP'			=> 'Wählen Sie hier eine Datei aus, deren Inhalt importiert werden soll:'
		));
		$this->_objTpl->parse("additional");
		$this->_objTpl->setVariable(array(
			"IMPORT_ADD_NAME"    => 'Ebene',
			"IMPORT_ADD_VALUE"   =>  "<select name=\"directory_level\" style=\"width:200px;\">".$this->getSearchLevels(0)."</select>",
			"IMPORT_ROWCLASS"    => "row2",
		));
		$this->_objTpl->parse("additional");*/
    }



    function manageComments()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase, $_LANGID;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_manage_comments.html',true,true);
        $this->pageTitle = $_ARRAYLANG['TXT_MEDIADIR_MANAGE_COMMENTS'];

        //parse global variables
        $this->_objTpl->setGlobalVariable(array(
            'TXT_DELETE' => $_ARRAYLANG['TXT_MEDIADIR_DELETE'],
            'TXT_SELECT_ALL' => $_CORELANG['TXT_SELECT_ALL'],
            'TXT_DESELECT_ALL' => $_CORELANG['TXT_DESELECT_ALL'],
            'TXT_SELECT_ACTION' => $_CORELANG['TXT_MULTISELECT_SELECT'],
            'TXT_CONFIRM_ALL' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_ALL'],
            'TXT_DELETE_ALL' => $_CORELANG['TXT_MULTISELECT_DELETE'],
            'TXT_'.$this->moduleLangVar.'_NAME' => $_CORELANG['TXT_NAME'],
            'TXT_'.$this->moduleLangVar.'_DATE' => $_CORELANG['TXT_DATE'],
            'TXT_'.$this->moduleLangVar.'_ADDED_BY' => $_ARRAYLANG['TXT_MEDIADIR_ADDED_BY'],
            'TXT_'.$this->moduleLangVar.'_ACTION' => $_CORELANG['TXT_HISTORY_ACTION'],
            'TXT_'.$this->moduleLangVar.'_CONFIRM_DELETE_DATA' => $_ARRAYLANG['TXT_MEDIADIR_CONFIRM_DELETE_DATA'],
            'TXT_'.$this->moduleLangVar.'_ACTION_IS_IRREVERSIBLE' => $_ARRAYLANG['TXT_MEDIADIR_ACTION_IS_IRREVERSIBLE'],
            'TXT_'.$this->moduleLangVar.'_MAKE_SELECTION' => $_ARRAYLANG['TXT_MEDIADIR_MAKE_SELECTION'],
            'TXT_'.$this->moduleLangVar.'_SUBMIT' =>  $_CORELANG['TXT_SAVE'],
            'TXT_'.$this->moduleLangVar.'_IP' =>  $_ARRAYLANG['TXT_MEDIADIR_IP'],
            'TXT_'.$this->moduleLangVar.'_COMMENT' =>  $_ARRAYLANG['TXT_MEDIADIR_COMMENT'],
            'TXT_'.$this->moduleLangVar.'_PAGE_TITLE' =>  $_ARRAYLANG['TXT_MEDIADIR_MANAGE_COMMENTS'],
            $this->moduleLangVar.'_ENTRY_ID' =>  intval($_GET['id']),
        ));

        $objComment = new mediaDirectoryComment();

        switch ($_GET['act']) {
            case 'delete_comment':
                if (!isset($_GET['cid'])) {
                    foreach ($_POST["commentsFormSelected"] as $intCommentId) {
                        $strStatus = $objComment->deleteComment(intval($intCommentId));
                    }
                } else {
                    $strStatus = $objComment->deleteComment(intval($_GET['cid']));
                }

                if($strStatus) {
                    $this->strOkMessage = $_ARRAYLANG['TXT_MEDIADIR_COMMENTS']." ".$_ARRAYLANG['TXT_MEDIADIR_SUCCESSFULLY_DELETED'];
                } else {
                    $this->strErrMessage = $_ARRAYLANG['TXT_MEDIADIR_COMMENTS']." ".$_ARRAYLANG['TXT_MEDIADIR_CORRUPT_DELETED'];
                }
                break;
        }

        //get comments
        $objComment->getComments($this->_objTpl, $_GET['id']);
    }



    function settings()
    {
        global $_ARRAYLANG, $_CORELANG;

        $this->_objTpl->loadTemplateFile('module_'.$this->moduleName.'_settings.html',true,true);
        $this->pageTitle = $_CORELANG['TXT_SETTINGS'];

        $objSettings = new mediaDirectorySettings();

        //save settings global
        if(isset($_POST['submitSettingsForm'])) {
            switch ($_GET['tpl']) {
                case 'modify_form':
                    if(intval($_POST['formId']) != 0) {
                        $objInputfields = new mediaDirectoryInputfield(intval($_POST['formId']));
                        $strStatus = $objInputfields->saveInputfields($_POST);
                    }

                    $objForms = new mediaDirectoryForm();
                    $strStatus = $objForms->saveForm($_POST, intval($_POST['formId']));
                    break;
                case 'forms':
                    $objForms = new mediaDirectoryForm();
                    $strStatus = $objForms->saveOrder($_POST);
                    break;
                case 'mails':
                    $strStatus = $objSettings->settings_save_mail($_POST);
                    break;
                case 'map':
                    $strStatus = $objSettings->settings_save_map($_POST);
                    break;
                default:
                    $strStatus = $objSettings->saveSettings($_POST);
            }

            if($strStatus == true){
                $this->strOkMessage = $_CORELANG['TXT_SETTINGS_UPDATED'];
            } else {
                $this->strErrMessage = $_CORELANG['TXT_DATABASE_QUERY_ERROR'];
            }
        }

        $this->_objTpl->setGlobalVariable(array(
            'TXT_'.$this->moduleLangVar.'_ENTRIES' => $_ARRAYLANG['TXT_MEDIADIR_ENTRIES'],
            'TXT_'.$this->moduleLangVar.'_LEVELS_AND_CATEGORIES' => $_ARRAYLANG['TXT_MEDIADIR_LEVELS_AND_CATEGORIES'],
            'TXT_'.$this->moduleLangVar.'_SUBMIT' => $_CORELANG['TXT_SAVE'],
            'TXT_'.$this->moduleLangVar.'_DELETE' => $_CORELANG['TXT_DELETE'],
            'TXT_'.$this->moduleLangVar.'_ACTIVATE' => $_ARRAYLANG['TXT_MEDIADIR_ACTIVATE'],
            'TXT_'.$this->moduleLangVar.'_DEACTIVATE' => $_ARRAYLANG['TXT_MEDIADIR_DEAVTIVATE'],
            'TXT_'.$this->moduleLangVar.'_FORMS' => $_ARRAYLANG['TXT_MEDIADIR_FORMS'],
            'TXT_'.$this->moduleLangVar.'_MAIL_TEMPLATES' => $_ARRAYLANG['TXT_MEDIADIR_MAIL_TEMPLATES'],
            'TXT_'.$this->moduleLangVar.'_PICS_AND_FILES' => $_ARRAYLANG['TXT_MEDIADIR_PICS_AND_FILES'],
            'TXT_'.$this->moduleLangVar.'_GOOGLE' => $_ARRAYLANG['TXT_MEDIADIR_GOOGLE'],
            'TXT_'.$this->moduleLangVar.'_HITS_AND_LATEST' => $_ARRAYLANG['TXT_MEDIADIR_HITS_AND_LATEST'],
            'TXT_'.$this->moduleLangVar.'_COMMENTS_AND_VOTING' => $_ARRAYLANG['TXT_MEDIADIR_COMMENTS_AND_VOTING'],
            'TXT_'.$this->moduleLangVar.'_CLASSIFICATION' => $_ARRAYLANG['TXT_MEDIADIR_CLASSIFICATION'],
        ));

        switch ($_GET['tpl']) {
            case 'delete_form':
            case 'forms':
                $objSettings->settings_forms($this->_objTpl);
                break;
            case 'modify_form':
                $objSettings->settings_modify_form($this->_objTpl);
                break;
            case 'delete_template':
            case 'mails':
                $objSettings->settings_mails($this->_objTpl);
                break;
            case 'modify_mail':
                $objSettings->settings_modify_mail($this->_objTpl);
                break;
            case 'files':
                $objSettings->settings_files($this->_objTpl);
                break;
            case 'map':
                $objSettings->settings_map($this->_objTpl);
                break;
            case 'votes':
                $objSettings->settings_votes($this->_objTpl);
                break;
            case 'levels_categories':
                $objSettings->settings_levels_categories($this->_objTpl);
                break;
            case 'classification':
                $objSettings->settings_classification($this->_objTpl);
                break;
            case 'entries':
            default:
                $objSettings->settings_entries($this->_objTpl);
        }

        $this->_objTpl->parse('settings_content');
    }
}
?>