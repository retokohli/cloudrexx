<?php
/**
 * Media  Directory Library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_mediadir
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */

class mediaDirectoryLibrary
{
	public $_objTpl;
	
    public $arrFrontendLanguages = array();
    public $arrSettings = array();
    public $arrCommunityGroups = array();

    public $strJavascript;
    
    public $moduleName = "mediadir";
    public $moduleTablePrefix = "mediadir";
    public $moduleLangVar = "MEDIADIR";

    /**
     * Constructor
     */
    function __construct($tplPath)
    {
    	$this->_objTpl = new HTML_Template_Sigma($tplPath);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);
    	
    	$this->_objTpl->setGlobalVariable(array(
            'MODULE_NAME' =>  $this->moduleName,
            'CSRF' =>  'csrf='.CSRF::code(),
        ));
    }


    function checkDisplayduration()
    {
        self::getSettings();
        
        if($this->arrSettings['settingsEntryDisplaydurationNotification'] >= 1) {
            include_once ASCMS_MODULE_PATH . '/mediadir/lib/entry.class.php';
            include_once ASCMS_MODULE_PATH . '/mediadir/lib/mail.class.php';
            
	        $objEntries = new mediaDirectoryEntry();
	        $objEntries->getEntries(null, null, null, null, null, null, true);
	        
            $intDaysbefore = intval($this->arrSettings['settingsEntryDisplaydurationNotification']);
            $intToday = mktime();
            
            foreach ($objEntries->arrEntries as $intEntryId => $arrEntry) {
                $intWindowEnd =  $arrEntry['entryDurationEnd'];
            	$intWindowEndDay =  date("d", $intWindowEnd);
                $intWindowEndMonth =  date("m", $intWindowEnd);
                $intWindowEndYear =  date("Y", $intWindowEnd);
                
                $intWindowStartDay = $intWindowEndDay-$intDaysbefore;
                $intWindowStart = mktime(0,0,0,$intWindowEndMonth,$intWindowStartDay,$intWindowEndYear);
                
	            if(($intWindowStart <= $intToday && $intToday <= $intWindowEnd) && $arrEntry['entryDurationNotification'] == 0) {
	            	$objMail = new mediaDirectoryMail(9, $intEntryId);
	            	$objEntries->setDisplaydurationNotificationStatus($intEntryId, 1);
	            }
            } 
        }
    }
    
    

    function checkAccess($strAction)
    {
        global $objInit;

        if($objInit->mode == 'backend') {
            //backend access
        } else {
            //frontend access

            $objFWUser  = FWUser::getFWUserObject();

            //get user attributes
            $objUser 		= $objFWUser->objUser;
            $intUserId      = intval($objUser->getId());
            $intUserName    = $objUser->getUsername();
            $bolUserLogin   = $objUser->login();
            $intUserIsAdmin = $objUser->getAdminStatus();
            $intSelectedFormId = intval($_REQUEST['selectedFormId']);

            if(!$intUserIsAdmin) {
                self::getSettings();

                switch($strAction) {
                    case 'add_entry':
                        if($this->arrSettings['settingsAllowAddEntries']) {
                            if($this->arrSettings['settingsAddEntriesOnlyCommunity'] == 1) {
                                if($bolUserLogin) {
                                    $bolAdd = true;
                                } else {
                                    $bolAdd = false;
                                }
                            } else {
                                $bolAdd = true;
                            }

                            if($bolAdd) {
                                //get groups attributes
                                $arrUserGroups  = array();
                                $objGroup = $objFWUser->objGroup->getGroups($filter = array('is_active' => true, 'type' => 'frontend'));

                                while (!$objGroup->EOF) {
                                    if(in_array($objGroup->getId(), $objUser->getAssociatedGroupIds())) {
                                        $arrUserGroups[] = $objGroup->getId();
                                    }
                                    $objGroup->next();
                                } 

                                self::getCommunityGroups();
                                $strMaxEntries = 0;
                                $bolFormAlowed = false;

                                //check max entries
                                foreach ($arrUserGroups as $intKey => $intGroupId) {
                                    $strNewMaxEntries = $this->arrCommunityGroups[$intGroupId]['num_entries'];

                                    if(($strNewMaxEntries === 'n') || ($strMaxEntries === 'n')) {
                                        $strMaxEntries = 'n';
                                    } else {
                                        if(($strNewMaxEntries >= $strMaxEntries)) {
                                            $strMaxEntries = $strNewMaxEntries;
                                        }
                                    }

                                    if($this->arrCommunityGroups[$intGroupId]['status_group'][$intSelectedFormId] == 1 && !$bolFormAlowed) {
                                        $bolFormAlowed = true;
                                    }
                                }

                                $objEntries = new mediaDirectoryEntry();
                                $objEntries->getEntries(null, null, null, null, null, null, null, null, null, $intUserId);

                                if($strMaxEntries <= intval(count($objEntries->arrEntries)) && $strMaxEntries !== 'n') {
                                    $strStatus = 'redirect';
                                }

                                //check from type
                                if(!$bolFormAlowed && $intSelectedFormId != 0) {
                                    $strStatus = 'no_access';
                                }
                            } else {
                                $strStatus = 'login';
                            }
                        } else {
                            $strStatus = 'redirect';
                        }
                        break;
                    case 'edit_entry':
                        if($this->arrSettings['settingsAllowEditEntries']) {
                            if($bolUserLogin) {
                                $objEntries = new mediaDirectoryEntry();
                                
	                            if(isset($_POST['submitEntryModfyForm'])) {
                                    $intEntryId = intval($_POST['entryId']);
                                } else {
                                    $intEntryId = intval($_GET['eid']);
                                }
                                
                                $objEntries->getEntries($intEntryId);
                                
                                if($objEntries->arrEntries[$intEntryId]['entryAddedBy'] !== $intUserId) {
                                    $strStatus = 'no_access';
                                }
                            } else {
                                $strStatus = 'login';
                            }
                        } else {
                            $strStatus = 'redirect';
                        }
                        break;
                    case 'delete_entry':
                        if($this->arrSettings['settingsAllowDelEntries']) {
                            if($bolUserLogin) {
                                $objEntries = new mediaDirectoryEntry();
                                $objEntries->getEntries(intval($_GET['eid']));

                                if($objEntries->arrEntries[intval($_GET['eid'])]['entryAddedBy'] !== $intUserId) {
                                    $strStatus = 'no_access';
                                }
                            } else {
                                $strStatus = 'login';
                            }
                        } else {
                            $strStatus = 'redirect';
                        }
                        break;
                    case 'show_entry':
                        //no access rules define
                        break;
                }

                switch($strStatus) {
                    case 'no_access':
                        header('Location: '.CONTREXX_SCRIPT_PATH.'?section=login&cmd=noaccess');
                        exit;
                        break;
                    case 'login':
                        $link = base64_encode(CONTREXX_SCRIPT_PATH.'?'.$_SERVER['QUERY_STRING']);
                        header("Location: ".CONTREXX_SCRIPT_PATH."?section=login&redirect=".$link);
                        exit;
                        break;
                    case 'redirect':
                        header('Location: '.CONTREXX_SCRIPT_PATH.'?section=mediadir');
                        exit;
                        break;
                }
            }
        }
    }



    function getFrontendLanguages()
    {
        global $_ARRAYLANG, $_CORELANG, $objDatabase;

        $arrLanguages = array();

        $objLanguages = $objDatabase->Execute("SELECT id,lang,name,frontend,is_default FROM ".DBPREFIX."languages WHERE frontend = '1' ORDER BY is_default ASC");
        if ($objLanguages !== false) {
            while (!$objLanguages->EOF) {
                $arrData = array();

                $arrData['id'] = intval($objLanguages->fields['id']);
                $arrData['lang'] = htmlspecialchars($objLanguages->fields['lang'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrData['name'] = htmlspecialchars($objLanguages->fields['name'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrData['frontend'] = intval($objLanguages->fields['frontend']);
                $arrData['is_default'] = htmlspecialchars($objLanguages->fields['is_default'], ENT_QUOTES, CONTREXX_CHARSET);

                $arrLanguages[] = $arrData;

                $objLanguages->MoveNext();
            }
        }

        // return $arrLanguages;
        $this->arrFrontendLanguages = $arrLanguages;
    }



    function getSettings()
    {
        global $objDatabase;

        $arrSettings = array();

        $objSettings = $objDatabase->Execute("SELECT id,name,value FROM ".DBPREFIX."module_mediadir_settings ORDER BY name ASC");
        if ($objSettings !== false) {
            while (!$objSettings->EOF) {
                if($objSettings->fields['id'] == 9 || $objSettings->fields['id'] == 10) {
                    $arrOrders = $this->getSelectorOrder($objSettings->fields['value']);
                    $arrSettings[htmlspecialchars($objSettings->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)] = $arrOrders;
                } else if($objSettings->fields['id'] == 42 || $objSettings->fields['id'] == 43) {
                    $arrExpSearch = $this->getSelectorSearch($objSettings->fields['value']);
                    $arrSettings[htmlspecialchars($objSettings->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)] = $arrExpSearch;
                } else{
                    $arrSettings[htmlspecialchars($objSettings->fields['name'], ENT_QUOTES, CONTREXX_CHARSET)] = htmlspecialchars($objSettings->fields['value'], ENT_QUOTES, CONTREXX_CHARSET);
                }
                $objSettings->MoveNext();
            }
        }
        // return $arrSettings;
        $this->arrSettings = $arrSettings;
    }



    function getSelectorOrder($intSelectorId)
    {
        global $objDatabase;

        $arrOrder = array();

        $objSelectorOrder = $objDatabase->Execute("SELECT form_id,selector_order FROM ".DBPREFIX."module_mediadir_order_rel_forms_selectors WHERE selector_id='".intval($intSelectorId)."'");
        if ($objSelectorOrder !== false) {
            while (!$objSelectorOrder->EOF) {
                $arrOrder[intval($objSelectorOrder->fields['form_id'])] = intval($objSelectorOrder->fields['selector_order']);
                $objSelectorOrder->MoveNext();
            }
        }

        return $arrOrder;
    }
    
    
    
    function getSelectorSearch($intSelectorId)
    {
        global $objDatabase;

        $arrExpSearch = array();

        $objSelectorSearch = $objDatabase->Execute("SELECT form_id, exp_search FROM ".DBPREFIX."module_mediadir_order_rel_forms_selectors WHERE selector_id='".intval($intSelectorId)."'");
        if ($objSelectorSearch !== false) {
            while (!$objSelectorSearch->EOF) {
                $arrExpSearch[intval($objSelectorSearch->fields['form_id'])] = intval($objSelectorSearch->fields['exp_search']);
                $objSelectorSearch->MoveNext();
            }
        }

        return $arrExpSearch;
    }



    function getCommunityGroups()
    {
        global $objDatabase;

        $arrCommunityGroups = array();

        $objCommunityGroups = $objDatabase->Execute("SELECT
                                                        `group`.`group_id` AS group_id,
                                                        `group`.`group_name` AS group_name,
                                                        `group`.`is_active` AS is_active,
                                                        `group`.`type` AS `type`,
                                                        `num_entry`.`num_entries` AS num_entries,
                                                        `num_category`.`num_categories` AS num_categories,
                                                        `num_level`.`num_levels` AS num_levels
                                                      FROM
                                                        ".DBPREFIX."access_user_groups AS `group`
                                                      LEFT JOIN ".DBPREFIX."module_mediadir_settings_num_entries AS `num_entry` ON `num_entry`.`group_id` = `group`.`group_id`
                                                      LEFT JOIN ".DBPREFIX."module_mediadir_settings_num_categories AS `num_category` ON `num_category`.`group_id` = `group`.`group_id`
                                                      LEFT JOIN ".DBPREFIX."module_mediadir_settings_num_levels AS `num_level` ON `num_level`.`group_id` = `group`.`group_id`");
        if ($objCommunityGroups !== false) {
            while (!$objCommunityGroups->EOF) {
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['name'] = htmlspecialchars($objCommunityGroups->fields['group_name'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['active'] = intval($objCommunityGroups->fields['is_active']);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['type'] = htmlspecialchars($objCommunityGroups->fields['type'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['num_entries'] = htmlspecialchars($objCommunityGroups->fields['num_entries'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['num_categories'] = htmlspecialchars($objCommunityGroups->fields['num_categories'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['num_levels'] = htmlspecialchars($objCommunityGroups->fields['num_levels'], ENT_QUOTES, CONTREXX_CHARSET);
                $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['status_group'] = htmlspecialchars($objCommunityGroups->fields['status_group'], ENT_QUOTES, CONTREXX_CHARSET);

                $objCommunityGroupPermForms = $objDatabase->Execute("SELECT
                                                        `perm_group_form`.`form_id` AS form_id ,
                                                        `perm_group_form`.`status_group` AS status_group
                                                      FROM
                                                        ".DBPREFIX."module_mediadir_settings_perm_group_forms AS `perm_group_form`
                                                      WHERE
                                                        `perm_group_form`.`group_id` = '".intval($objCommunityGroups->fields['group_id'])."'");
                if ($objCommunityGroupPermForms !== false) {
                    while (!$objCommunityGroupPermForms->EOF) {
                        $arrCommunityGroups[intval($objCommunityGroups->fields['group_id'])]['status_group'][intval($objCommunityGroupPermForms->fields['form_id'])] = htmlspecialchars($objCommunityGroupPermForms->fields['status_group'], ENT_QUOTES, CONTREXX_CHARSET);
                        $objCommunityGroupPermForms->MoveNext();
                    }
                }

                $objCommunityGroups->MoveNext();
            }
        }

        // return $arrCommunityGroups;
        $this->arrCommunityGroups = $arrCommunityGroups;
    }



    function buildDropdownmenu($arrOptions, $intSelected=null)
    {
        foreach ($arrOptions as $intValue => $strName) {
            $checked = $intValue==$intSelected ? 'selected="selected"' : '';
            $strOptions .= "<option value='".$intValue."' ".$checked.">".htmlspecialchars($strName, ENT_QUOTES, CONTREXX_CHARSET)."</option>";
        }

        return $strOptions;
    }



    function getSelectorJavascript(){
        global $objInit;

        if($objInit->mode == 'frontend') {
            self::getSettings();
            if($this->arrSettings['settingsAddEntriesOnlyCommunity'] == 1) {
                $objFWUser  	= FWUser::getFWUserObject();
                $objUser 		= $objFWUser->objUser;
                $intUserId      = intval($objUser->getId());
                $intUserName    = $objUser->getUsername();
                $bolUserLogin   = $objUser->login();
                $bolUserIsAdmin = $objUser->getAdminStatus();

                if(!$bolUserIsAdmin) {
                    if($bolUserLogin) {
                        $arrUserGroups  = array();
                        $objGroup = $objFWUser->objGroup->getGroups($filter = array('is_active' => true, 'type' => 'frontend'));

                        while (!$objGroup->EOF) {
                            if(in_array($objGroup->getId(), $objUser->getAssociatedGroupIds())) {
                                $arrUserGroups[] = $objGroup->getId();
                            }
                            $objGroup->next();
                        }

                        self::getCommunityGroups();
                        $strMaxCategorySelect = 0;
                        $strMaxLevelSelect = 0;

                        foreach ($arrUserGroups as $intKey => $intGroupId) {
                            $strNewMaxCategorySelect = $this->arrCommunityGroups[$intGroupId]['num_categories'];
                            $strNewMaxLevelSelect = $this->arrCommunityGroups[$intGroupId]['num_levels'];

                            if(($strNewMaxCategorySelect === 'n') || ($strMaxCategorySelect === 'n')) {
                                $strMaxCategorySelect = 'n';
                            } else {
                                if(($strNewMaxCategorySelect >= $strMaxCategorySelect)) {
                                    $strMaxCategorySelect = $strNewMaxCategorySelect;
                                }
                            }

                            if(($strNewMaxLevelSelect === 'n') || ($strMaxLevelSelect === 'n')) {
                                $strMaxLevelSelect = 'n';
                            } else {
                                if(($strNewMaxLevelSelect >= $strMaxLevelSelect)) {
                                    $strMaxLevelSelect = $strNewMaxLevelSelect;
                                }
                            }
                        }
                    }
                } else {
                    $strMaxCategorySelect = 'n';
                    $strMaxLevelSelect = 'n';
                }
            }
        } else {
            $strMaxCategorySelect = 'n';
            $strMaxLevelSelect = 'n';
        }


        $strSelectorJavascript = <<< EOF


function moveElement(from, dest, add, remove) {
    if(checkNum(dest)) {
        if (from.selectedIndex < 0) {
            if (from.options[0] != null) from.options[0].selected = true;
                from.focus();
                return false;
            } else {
                for (i = 0; i < from.length; ++i) {
                    if (from.options[i].selected) {
                        dest.options[dest.options.length] = new Option(from.options[i].text, from.options[i].value, false, false);
                    }
                }
                for (i = from.options.length-1; i >= 0; --i) {
                    if (from.options[i].selected) {
                    from.options[i] = null;
                }
            }
        }
    }

    disableButtons(from, dest, add, remove);
}

function disableButtons(from, dest, add, remove) {
    if (from.options.length > 0) {
        add.disabled = 0;
    } else {
        add.disabled = 1;
    }
    if (dest.options.length > 0) {
        remove.disabled = 0;
    } else {
        remove.disabled = 1;
    }
}

function checkNum(dest){
    if(dest.id == 'selectedCategories' || dest.id == 'selectedLevels') {
        if(dest.id == 'selectedCategories') {
            maxLength = '$strMaxCategorySelect';
        } else {
            maxLength = '$strMaxLevelSelect';
        }

        if(maxLength != 'n') {
            if(dest.options.length < maxLength) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    } else {
        return true;
    }
}


function selectAll(control){
    for (i = 0; i < control.length; ++i) {
        control.options[i].selected = true;
    }
}

function deselectAll(control){
    for (i = 0; i < control.length; ++i) {
        control.options[i].selected = false;
    }
}

EOF;

        return $strSelectorJavascript;
    }



    function getFormOnSubmit(){
        $strFormOnSubmit  = "selectAll(document.entryModfyForm.elements['selectedCategories[]']); ";

        $this->getSettings();
        if($this->arrSettings['settingsShowLevels'] == 1) {
            $strFormOnSubmit  .= "selectAll(document.entryModfyForm.elements['selectedLevels[]']); ";
        }

        $strFormOnSubmit   .= "return checkAllFields();";

        return $strFormOnSubmit;
    }


    function setJavascript($strJavascript){
        $this->strJavascript .= $strJavascript;
    }



    function getJavascript(){
        $strJavascript = <<< EOF
<script language="JavaScript" type="text/javascript">
/* <![CDATA[ */

EOF;

        $strJavascript .= $this->strJavascript;
        $strJavascript .= <<< EOF

/* ]]> */
</script>

EOF;
        return $strJavascript;
    }
}
?>