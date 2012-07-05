<?php
/**
 * Partners
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Sureshkumar.C
 * @version     v 1.0
 * @package     contrexx
 * @subpackage  module_Partners
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/partners/lib/partnersLib.class.php';


class PartnersAdmin extends PartnersLibrary
{

    var $_objTpl;
    var $_strPageTitle  = '';
    var $_strErrMessage = '';
    var $_strOkMessage  = '';

    private $act = '';

    /**
     * Constructor   -> Create the module-menu and an internal template-object
     * @global   object      $objInit
     * @global   object      $objTemplate
     * @global   array       $_CORELANG
     */
    function __construct()
    {
        global $objInit, $objTemplate, $_ARRAYLANG;

        PartnersLibrary::__construct();
        $this->_objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/partners/template');
        CSRF::add_placeholder($this->_objTpl);
        $this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

        $this->_intLanguageId = $objInit->userFrontendLangId;

        $objFWUser = FWUser::getFWUserObject();
        $this->_intCurrentUserId = $objFWUser->objUser->getId();

    }
    private function setNavigation()
    {
        global $objTemplate, $_ARRAYLANG;
        
        $objTemplate->setVariable('CONTENT_NAVIGATION','
            <a href="?cmd=partners" class="'.($this->act == '' ? 'active' : '').'">'.$_ARRAYLANG['TXT_PARTNERS_OVERVIEW_TITLE'].'</a>
            <a href="?cmd=partners&amp;act=addPartners" class="'.($this->act == 'addPartners' ? 'active' : '').'">'.$_ARRAYLANG['TXT_PARTNERS_SETTINGS_CPARTNERS'].'</a>
            <a href="?cmd=partners&amp;act=manageCategory" class="'.($this->act == 'manageCategory' ? 'active' : '').'">'.$_ARRAYLANG['TXT_PARTNERS_SETTINGS_CATEGORY'].'</a>
            <a href="?cmd=partners&amp;act=settings" class="'.($this->act == 'settings' ? 'active' : '').'">'.$_ARRAYLANG['TXT_PARTNERS_SETTINGS_TITLE'].'</a>
            <a href="?cmd=partners&amp;act=users" class="'.($this->act == 'users' ? 'active' : '').'">'.$_ARRAYLANG['TXT_PARTNERS_IMPORT_TITLE'].'</a>');
    }

    /**
     * Perform the right operation depending on the $_GET-params
     *
     * @global   object      $objTemplate
     */
    function getPage()
    {
        global $objTemplate;

        if(!isset($_GET['act'])) {
            $_GET['act']='';
        }

        switch($_GET['act']) {

            case 'addPartners':
                Permission::checkAccess(121, 'static');
                $this->addPartners();
                break;

            case 'insertPartners':
                Permission::checkAccess(121, 'static');
                $this->insertPartners();
                break;

            case 'deletePartners':
                Permission::checkAccess(120, 'static');
                $this->deletePartners($_GET['id']);
                $this->showOverview();
                break;

            case 'editPartners':
                Permission::checkAccess(120, 'static');
                $this->editPartners($_GET['id']);
                break;

            case 'updatePartners':
                Permission::checkAccess(120, 'static');
                $this->updatePartners();
                break;

            case 'multiactionEntry':
                Permission::checkAccess(120, 'static');
                $this->doEntryMultiAction($_POST['frmShowEntries_MultiAction']);
                $this->showOverview();
                break;

            case 'manageCategory':
                Permission::checkAccess(122, 'static');
                $this->subCategories();
                $this->showCategories();
                break;

            case 'insertCategory':
                Permission::checkAccess(123, 'static');
                $this->subCategories();
                $this->insertCategory();
                break;

            case 'editCategory':
                Permission::checkAccess(122, 'static');
                $this->subCategories();
                $this->editCategory($_REQUEST['id'],$_REQUEST['catnames']);
                break;

            case 'updateCategory':
                Permission::checkAccess(122, 'static');
                $this->subCategories();
                $this->updateCategory($_POST['cat_name_edit']);
                break;

            case 'deleteCategory':
                Permission::checkAccess(122, 'static');
                $this->subCategories();
                $this->deleteCategory($_GET['id'],$_GET['catnames']);
                break;

            case 'multiactionCategory':
                Permission::checkAccess(122, 'static');
                $this->subCategories();
                $this->doCategoryMultiAction($_REQUEST['frmShowCategories_MultiAction'],$_REQUEST['cat_name_del'],$_REQUEST['cat_id']);
                break;

            case 'sortCategory':
                Permission::checkAccess(122, 'static');
                $this->subCategories();
                $this->sortCategories();
                break;

            case 'insertSort':
                Permission::checkAccess(122, 'static');
                $this->subCategories();
                $this->insertSort();
                break;

            case 'settings':
                Permission::checkAccess(122, 'static');
                $this->showSettings();
                break;

            case 'saveSettings':
                Permission::checkAccess(122, 'static');
                $this->saveSettings();
                $this->showSettings();
                break;
            case 'getCSV':
                Permission::checkAccess(122, 'static');
                $id = intval($_REQUEST['id']);
                $this->showOverview();
                $this->getCsv($id,$_REQUEST['sub']);
                break;
            case 'getALLCSV':
                Permission::checkAccess(122, 'static');
                $this->showOverview();
                $this->getAllcsv();
                break;
            case 'getMultiplecsv':
                Permission::checkAccess(122, 'static');
                $this->showOverview();
                $this->getMultiplecsv($_POST['frmExportpartners_MultiAction']);
                break;
            case 'addRegions':
                Permission::checkAccess(122, 'static');
                $this->subCategories();
                $this->addRegions($_REQUEST['category'],$_REQUEST['id']);
                break;
            case 'ajaxRequest':
                $this->ajaxRequest();
                break;
            case 'users':
                Permission::checkAccess(122, 'static');
                $this->_users();
                break;
            case 'csvsubmit':
                Permission::checkAccess(122, 'static');
                $this->csvsubmit();
                break;

            default:
                Permission::checkAccess(120, 'static');
                $this->showOverview();
        }

        $objTemplate->setVariable(array(
                'CONTENT_TITLE'             => $this->_strPageTitle,
                'CONTENT_OK_MESSAGE'        => $this->_strOkMessage,
                'CONTENT_STATUS_MESSAGE'    => $this->_strErrMessage,
                'ADMIN_CONTENT'             => $this->_objTpl->get()
        ));

        $this->act = $_REQUEST['act'];
        $this->setNavigation();
    }


    /**
     Showing the main category menus like certificate,profile,level etc
     - Main Template for the Category Part
     *
     * @global	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     */
    function subCategories() {
        global $_ARRAYLANG;

        $this->_objTpl->loadTemplateFile('module_partners_subcategories.html',true,true);
        $this->_objTpl->setVariable(array(
                'TXT_CATEGORY'        =>  $this->_getCategoryname('1'),
        ));
        $arrSettings   = $this->_getSettings();
        foreach($arrSettings as $setValue) {
            if($arrSettings['lis_active']!=0) {
                $this->_objTpl->setVariable(array(
                        'TXT_LEVEL'           =>  '<li><a href="index.php?cmd=partners&amp;act=manageCategory&amp;category=level" title="'.$this->_getCategoryname('2').'">'.$this->_getCategoryname('2').'</a></li>'
                ));
                // print $rowClass;
            }else {
                //do Nothing

            }
            if($arrSettings['pis_active']!=0) {
                $this->_objTpl->setVariable(array(
                        'TXT_PROFILE'           =>  '<li><a href="index.php?cmd=partners&amp;act=manageCategory&amp;category=profile" title="'.$this->_getCategoryname('3').'">'.$this->_getCategoryname('3').'</a></li>'
                ));
            }else {
                //doNothing
            }
            if($arrSettings['cis_active']!=0) {
                $this->_objTpl->setVariable(array(
                        'TXT_COUNTRY'           =>  '<li><a href="index.php?cmd=partners&amp;act=manageCategory&amp;category=country" title="'.$this->_getCategoryname('4').'">'.$this->_getCategoryname('4').'</a></li>'
                ));
            }else {

            }
            if($arrSettings['vis_active']!=0) {
                $this->_objTpl->setVariable(array(
                        'TXT_VERTICAL'           =>  '<li><a href="index.php?cmd=partners&amp;act=manageCategory&amp;category=vertical" title="'.$this->_getCategoryname('5').'">'.$this->_getCategoryname('5').'</a></li>'
                ));
            }else {

            }

        }


    }

    /**
     Showing the regions for the country category
     - Ajax function to display the regions for the countries
     *
     * @global	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     */

    function ajaxRequest() {
        global $_ARRAYLANG;

        $ajaxRequest = intval($_REQUEST['ajax']);
        $regval  = intval($_REQUEST['regval']);
        $intLanguageId = intval($_REQUEST['lang']);
        $ajaxResult = new PartnersLibrary();
        $lang_multiple = '';
        $intLanguageCounterMultiple = 0;
        foreach(array_keys($this->_arrLanguages) as $intLanguageIdajax) {
            if($intLanguageCounterMultiple >= 1) {

                if($intLanguageId == $intLanguageIdajax) {
                    $functionId = 1;
                }
                $lang_multiple .= $intLanguageIdajax."-";
            }
            $intLanguageCounterMultiple++;
        }
        print $ajaxResult->_getListLevelMenu(0,"regions",'name="region_'.$regval.'_'.$intLanguageId.'[]" id="region_'.$regval.'_'.$intLanguageId.'[]" multiple size="10" style="width:153px;" onchange=changeInAllLang_ListBox'.$functionId.'(document.frmEditEntry.elements["region_'.$regval.'_'.$intLanguageId.'[]"],"region_'.$regval.'_",this.value,"'.$lang_multiple.'",this.options,selectedIndex,"region_'.$regval.'_") ','backend',$intLanguageId,"Select"."&nbsp;".$this->_getCategoryname('6'),$ajaxRequest);
        die();
    }

    /**
     * Shows the values from the csv file in the list box  of the Partners details.
     *
     * @global	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     * @global  object      $objDatabase
     */

    function showImport() {
        global $_ARRAYLANG;

        $csvarr = array();
        $readf = fopen(ASCMS_MODULE_PATH."/partners/upload/".$_FILES['Browse_MultiImport']['name'], "r");
        $i = 0;
        while (($listf = fgetcsv($readf,1000, ","))!= FALSE) {
            $csvarr['title'][$i] = $listf[0];
            $csvarr['status'][$i] = $listf[1];
            $csvarr['certificate'][$i] = $listf[2];
            $csvarr['level'][$i] = $listf[3];
            $csvarr['profile'][$i] = $listf[4];
            $csvarr['country'][$i] = $listf[5];
            $csvarr['region'][$i] = $listf[6];
            $csvarr['vertical'][$i] = $listf[7];
            $csvarr['name'][$i] = $listf[8];
            $csvarr['email'][$i] = $listf[9];
            $csvarr['website'][$i] = $listf[10];
            $csvarr['addr1'][$i] = $listf[11];
            $csvarr['addr2'][$i] = $listf[12];
            $csvarr['city'][$i] = $listf[13];
            $csvarr['zipcode'][$i] = $listf[14];
            $csvarr['phone'][$i] = $listf[15];
            $csvarr['fax'][$i] = $listf[16];
            $i++;
        }
        $Listval="<select name='Importlist' multiple  style='width:200px;' size='10'>";
        $j = 0;
        foreach ($csvarr as $value) {
            if($j==0) {
                if($csvarr['title'][$j]!="") {
                    $Listval.='<option value='.$csvarr['title'][$j].'>'.$csvarr['title'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['status'][$j].'>'.$csvarr['status'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['certificate'][$j].'>'.$csvarr['certificate'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['level'][$j].'>'.$csvarr['level'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['profile'][$j].'>'.$csvarr['profile'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['country'][$j].'>'.$csvarr['country'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['region'][$j].'>'.$csvarr['region'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['vertical'][$j].'>'.$csvarr['vertical'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['name'][$j].'>'.$csvarr['name'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['email'][$j].'>'.$csvarr['email'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['website'][$j].'>'.$csvarr['website'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['addr1'][$j].'>'.$csvarr['addr1'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['addr2'][$j].'>'.$csvarr['addr2'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['city'][$j].'>'.$csvarr['city'][$j].'</option>';
                    $Listval.='<option value='.$csvarr['zipcode'][$j].'>'.$csvarr['zipcode'][$j].'</option>';
                }
                $j++;
            }

        }
        $Listval.='</select>';
        return $Listval;
    }


    /**
     * Shows the fieldnames in the list box  of the Partners details.

     */
    function showImportfields() {
        $ListFldval="<select name='ImportFldnam' multiple size='10' style='width:200px;'>";
        $ListFldval.='<option value=title>title</option>';
        $ListFldval.='<option value=status>status</option>';
        $ListFldval.='<option value=certificate>certificate</option>';
        $ListFldval.='<option value=level>level</option>';
        $ListFldval.='<option value=profile>profile</option>';
        $ListFldval.='<option value=country>country</option>';
        $ListFldval.='<option value=region>region</option>';
        $ListFldval.='<option value=vertical>vertical</option>';
        $ListFldval.='<option value=name>name</option>';
        $ListFldval.='<option value=email>email</option>';
        $ListFldval.='<option value=website>website</option>';
        $ListFldval.='<option value=addr1>addr1</option>';
        $ListFldval.='<option value=addr1>addr1</option>';
        $ListFldval.='<option value=addr2>addr2</option>';
        $ListFldval.='<option value=city>city</option>';
        $ListFldval.='<option value=zipcode>zipcode</option>';
        $ListFldval.='</select>';
        return $ListFldval;
    }

    /**
     * Shows the categories.
     * @global	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     */

    function showCategories($category,$cat_id) {
        global $_CORELANG, $_ARRAYLANG;

        $this->_strPageTitle = $_CORELANG['TXT_PARTNERS_CATEGORY_MANAGE_TITLE'];
        $this->_objTpl->addBlockfile('PARTNERS_SUBCATEGORY_FILE', 'settings_block', 'module_partners_categories.html');
        $this->_objTpl->setVariable(array(
                'TXT_OVERVIEW_SUBTITLE_NAME'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_NAME'],
                'TXT_OVERVIEW_SUBTITLE_ID'		    =>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_ID'],
                'TXT_OVERVIEW_SUBTITLE_ACTIVE'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ACTIVE_LANGUAGES'],
                'TXT_OVERVIEW_SUBTITLE_ACTIONS'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ACTIONS'],
                'TXT_OVERVIEW_DELETE_CATEGORY_JS'	=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_JS'],
                'TXT_OVERVIEW_SORT'                 =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ORDER'],
                'TXT_OVERVIEW_MARKED'				=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_MARKED'],
                'TXT_OVERVIEW_SELECT_ALL'			=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_SELECT'],
                'TXT_OVERVIEW_DESELECT_ALL'			=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_DESELECT'],
                'TXT_OVERVIEW_SUBMIT_SELECT'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_ACTION'],
                'TXT_OVERVIEW_SUBMIT_DELETE'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_DELETE'],
                'TXT_OVERVIEW_SUBMIT_DELETE_JS'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_DELETE_JS'],
                'TXT_OVERVIEW_CHANGES'              =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_CHANGES'],
                'TXT_ADD_NAME'						=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_NAME'],
                'TXT_ADD_EXTENDED'					=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_EXTENDED'],
                'TXT_ADD_LANGUAGES'					=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_LANGUAGES'],
                'TXT_ADD_SUBMIT'					=>	$_CORELANG['TXT_SAVE']
        ));

        $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
        if(empty($category))
            $category = isset($_GET['category']) ? $_GET['category'] : '';
        if($category == "level") {
            $arrRenameCategories = $this->CreateRegionArray(2);
        }
        else if($category == "profile") {
            $arrRenameCategories = $this->CreateRegionArray(3);
            $this->_objTpl->setVariable(array('PARTNERS_CAT_NAME'     => "profile",
                    'PARTNERS_CAT_NAME_DEL' => "profile",
                    'TXT_OVERVIEW_TITLE'    => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('3'),
                    'TXT_RENAME_TITLE'      => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('3'),
                    'TXT_ADD_TITLE'         => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('3')
            ));
        }
        else if($category == "country") {
            $arrRenameCategories = $this->CreateRegionArray(4);
            $this->_objTpl->setVariable(array('PARTNERS_CAT_NAME'                       => "country",
                    'PARTNERS_CAT_NAME_DEL'                   => "country",
                    'TXT_OVERVIEW_SUBTITLE_ADD_REGIONS'		=> $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ADD_REGIONS'],
                    'TXT_OVERVIEW_TITLE'                      => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('4'),
                    'TXT_RENAME_TITLE'                        => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('4'),
                    'TXT_ADD_TITLE'                           => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('4')
            ));
        }
        else if($category == "vertical") {
            $arrRenameCategories = $this->CreateRegionArray(5);
            $this->_objTpl->setVariable(array('PARTNERS_CAT_NAME'     => "vertical",
                    'PARTNERS_CAT_NAME_DEL' => "vertical",
                    'TXT_OVERVIEW_TITLE'    => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('5'),
                    'TXT_RENAME_TITLE'      => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('5'),
                    'TXT_ADD_TITLE'         => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('5')
            ));
        }
        else {
            $category = "certificate";
            $arrRenameCategories = $this->CreateRegionArray(1);
            $this->_objTpl->setVariable(array('PARTNERS_CAT_NAME'     => "",
                    'PARTNERS_CAT_NAME_DEL' => $_ARRAYLANG['PARTNERS_CATNAME_CATEGORY'],
                    'TXT_OVERVIEW_TITLE'    => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('1'),
                    'TXT_ADD_TITLE'         => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('1'),
                    'TXT_RENAME_TITLE'      => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('1'),
                    'PARTNERS_LEVEL_IMG'    => $_ARRAYLANG['PARTNERS_LEVEL_IMAGE']
            ));
        }
        $arrLanguagesName = array(0 => '', 1 => '', 2 => '');
        $intCounter = 0;
        foreach ($arrRenameCategories as $intCategoryId => $arrLanguages) {
            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                $arrLanguagesName[$intCounter%3] .= '<input '.(($arrRenameCategories[$intCategoryId][$intLanguageId]['is_active'] == 1) ? 'checked="checked"' : '').' type="checkbox" name="frmRenameCategory_Languages[]" value="'.$intLanguageId.'" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';
                ++$intCounter;
            }
            $this->_objTpl->setVariable(array(
                    'PARTNERS_OLD_NAME'		=>	$arrLanguages[$this->_intLanguageId]['name']
            ));
        }
        $arrCategories = $this->createCategoryArray($intPagingPosition, $this->getPagingLimit(),$category,$intCategoryId=0,$cat_id);

        if (count($arrCategories) > 0) {
            $intRowClass = 1;
            foreach ($arrCategories as $intCategoryId => $arrLanguages) {
                $this->_objTpl->setVariable(array(
                        'TXT_OVERVIEW_IMGALT_MESSAGES'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ASSIGNED_MESSAGES'],
                        'TXT_OVERVIEW_IMGALT_EDIT'			=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_EDIT_TITLE'],
                        'TXT_OVERVIEW_IMGALT_DELETE'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_TITLE']
                ));
                $strActivatedLanguages = '';
                foreach($arrLanguages as $intLanguageId => $arrValues) {
                    if ($arrValues['is_active'] == 1 && array_key_exists($intLanguageId,$this->_arrLanguages)) {
                        $strActivatedLanguages .= $this->_arrLanguages[$intLanguageId]['long'].' ['.$this->_arrLanguages[$intLanguageId]['short'].'], ';
                    }
                }
                $strActivatedLanguages = substr($strActivatedLanguages,0,-2);
                $this->_objTpl->setVariable(array(
                        'OVERVIEW_CATEGORY_ROWCLASS'	=>	($intRowClass % 2 == 0) ? 'row1' : 'row2',
                        'OVERVIEW_CATEGORY_ID'			=>	$intCategoryId,
                        'OVERVIEW_CATEGORY_NAME'		=>	$arrLanguages[$this->_intLanguageId]['name'],
                        'OVERVIEW_SORT_VALUE'           =>  $arrLanguages[$this->_intLanguageId]['sort_id'],
                        'OVERVIEW_CATEGORY_REGIONS'		=>	$arrLanguages[$this->_intLanguageId]['regions'],
                        'PARTNERS_CAT_SEARCH'           =>  $category,
                        'OVERVIEW_CATEGORY_LANGUAGES'	=>	$strActivatedLanguages
                ));
                $this->_objTpl->parse('showCategories');
                $intRowClass++;
            }

            //Show paging if needed
        }
        else {
            $this->_objTpl->setVariable('TXT_OVERVIEW_NO_CATEGORIES_FOUND',$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_NO_CATEGORIES']);
            $this->_objTpl->parse('noCategories');
        }

        //Show Add-Category Form
        if (count($this->_arrLanguages) > 0) {
            $intCounter = 0;
            $arrLanguages = array(0 => '', 1 => '', 2 => '');
            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                $arrLanguages[$intCounter%3] .= '<input checked="checked" type="checkbox" name="frmAddCategory_Languages[]" value="'.$intLanguageId.'" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';
                $this->_objTpl->setVariable(array(
                        'ADD_NAME_LANGID'	=>	$intLanguageId,
                        'ADD_NAME_LANG'		=>	$arrTranslations['long'].' ['.$arrTranslations['short'].']'
                ));
                $this->_objTpl->parse('addCategoryNameFields');
                $this->_objTpl->setVariable(array(
                        'RE_NAME_LANGID'	=>	$intLanguageId,
                        'RE_NAME_LANG'		=>	$arrTranslations['long'].' ['.$arrTranslations['short'].']'
                ));
                $this->_objTpl->parse('reCategoryNameFields');
                ++$intCounter;
            }
            $this->_objTpl->setVariable(array(
                    'ADD_LANGUAGES_1'	=>	$arrLanguages[0],
                    'ADD_LANGUAGES_2'	=>	$arrLanguages[1],
                    'ADD_LANGUAGES_3'	=>	$arrLanguages[2],
                    'RENAME_LANGUAGES_1'	=>	$arrLanguagesName[0],
                    'RENAME_LANGUAGES_2'	=>	$arrLanguagesName[1],
                    'RENAME_LANGUAGES_3'	=>	$arrLanguagesName[2]
            ));
        }
        $this->_objTpl->parse('Display_level');
    }

    /**
     * Shows the Regions-page(country) of the Partners-module.
     *
     * @global	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     * @global  object      $objDatabase
     */
    function addRegions($category, $id) {
        global $_CORELANG, $_ARRAYLANG;

        $cat_id = intval(trim(strip_tags($id)));
        $this->_strPageTitle = $_CORELANG['TXT_PARTNERS_CATEGORY_MANAGE_TITLE'];
        $this->_objTpl->addBlockfile('PARTNERS_SUBCATEGORY_FILE', 'settings_block', 'module_partners_categories_regions.html');
        $this->_objTpl->setVariable(array(
                'TXT_OVERVIEW_SUBTITLE_NAME'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_NAME'],
                'TXT_OVERVIEW_SUBTITLE_ID'		    =>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_ID'],
                'TXT_OVERVIEW_SUBTITLE_ACTIVE'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ACTIVE_LANGUAGES'],
                'TXT_OVERVIEW_SUBTITLE_ACTIONS'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ACTIONS'],
                'TXT_OVERVIEW_DELETE_CATEGORY_JS'	=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_JS'],
                'TXT_OVERVIEW_SORT'                 =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ORDER'],
                'TXT_OVERVIEW_MARKED'				=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_MARKED'],
                'TXT_OVERVIEW_SELECT_ALL'			=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_SELECT'],
                'TXT_OVERVIEW_DESELECT_ALL'			=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_DESELECT'],
                'TXT_OVERVIEW_SUBMIT_SELECT'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_ACTION'],
                'TXT_OVERVIEW_SUBMIT_DELETE'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_DELETE'],
                'TXT_OVERVIEW_SUBMIT_DELETE_JS'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_DELETE_JS'],
                'TXT_OVERVIEW_CHANGES'              =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_CHANGES'],
                'TXT_ADD_NAME'						=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_NAME'],
                'TXT_ADD_EXTENDED'					=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_EXTENDED'],
                'CAT_DEL_ID'                        =>  $cat_id,
                'TXT_ADD_LANGUAGES'					=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_LANGUAGES'],
                'TXT_ADD_SUBMIT'					=>	$_CORELANG['TXT_SAVE']
        ));
        $this->_objTpl->setVariable(array(
        ));
        $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;
        if(empty($category))
            $category = isset($_GET['category']) ? $_GET['category'] : '';

        //Show Categories
        $arrCategories = $this->createCategoryArray($intPagingPosition, $this->getPagingLimit(),$category,$intCategoryId=0,$cat_id);
        $arrRenameCategories = $this->CreateRegionArray(6);
        $arrLanguagesName = array(0 => '', 1 => '', 2 => '');
        $intCounter = 0;
        foreach ($arrRenameCategories as $intCategoryId => $arrLanguages) {
            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                $arrLanguagesName[$intCounter%3] .= '<input '.(($arrRenameCategories[$intCategoryId][$intLanguageId]['is_active'] == 1) ? 'checked="checked"' : '').' type="checkbox" name="frmRenameCategory_Languages[]" value="'.$intLanguageId.'" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';
                $this->_objTpl->setVariable(array(
                        'RE_NAME_LANGID'	=>	$intLanguageId,
                        'RE_NAME_LANG'		=>	$arrTranslations['long'].' ['.$arrTranslations['short'].']'
                ));
                $this->_objTpl->parse('reCategoryNameFields');
                ++$intCounter;
            }
            $this->_objTpl->setVariable(array(
                    'PARTNERS_OLD_NAME'		=>	$arrLanguages[$this->_intLanguageId]['name']
            ));
        }
        if (count($this->_arrLanguages) > 0) {

            $intCounter = 0;
            $arrLanguages = array(0 => '', 1 => '', 2 => '');
            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                $arrLanguages[$intCounter%3].= '<input checked="checked" type="checkbox" name="frmAddCategory_Languages[]" value="'.$intLanguageId.'" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';
                $this->_objTpl->setVariable(array(
                        'ADD_NAME_LANGID'	=>	$intLanguageId,
                        'ADD_NAME_LANG'		=>	$arrTranslations['long'].' ['.$arrTranslations['short'].']'
                ));
                $this->_objTpl->parse('addCategoryNameFields');
                ++$intCounter;
            }
            $this->_objTpl->setVariable(array(
                    'ADD_LANGUAGES_1'	    =>	$arrLanguages[0],
                    'ADD_LANGUAGES_2'	    =>	$arrLanguages[1],
                    'ADD_LANGUAGES_3'	    =>	$arrLanguages[2],
                    'CAT_ADD_ID'        =>  $cat_id,
                    'RENAME_LANGUAGES_1'	=>	$arrLanguagesName[0],
                    'RENAME_LANGUAGES_2'	=>	$arrLanguagesName[1],
                    'RENAME_LANGUAGES_3'	=>	$arrLanguagesName[2],
                    'CAT_RE_ID'            =>  $cat_id
            ));
        }
        if (count($arrCategories) > 0) {
            $intRowClass = 1;
            foreach ($arrCategories as $intCategoryId => $arrLanguages) {
                $this->_objTpl->setVariable(array(
                        'TXT_OVERVIEW_IMGALT_MESSAGES'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ASSIGNED_MESSAGES'],
                        'TXT_OVERVIEW_IMGALT_EDIT'			=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_EDIT_TITLE'],
                        'TXT_OVERVIEW_IMGALT_DELETE'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_TITLE']
                ));
                $strActivatedLanguages = '';
                foreach($arrLanguages as $intLanguageId => $arrValues) {
                    if ($arrValues['is_active'] == 1 && array_key_exists($intLanguageId,$this->_arrLanguages)) {
                        $strActivatedLanguages .= $this->_arrLanguages[$intLanguageId]['long'].' ['.$this->_arrLanguages[$intLanguageId]['short'].'], ';
                    }
                }

                $strActivatedLanguages = substr($strActivatedLanguages,0,-2);
                $this->_objTpl->setVariable(array(
                        'OVERVIEW_CATEGORY_ROWCLASS'	=>	($intRowClass % 2 == 0) ? 'row1' : 'row2',
                        'OVERVIEW_CATEGORY_ID'			=>	$intCategoryId,
                        'OVERVIEW_CATEGORY_NAME'		=>	$arrLanguages[$this->_intLanguageId]['name'],
                        'OVERVIEW_SORT_VALUE'           =>  $arrLanguages[$this->_intLanguageId]['sort_id'],
                        'OVERVIEW_CATEGORY_REGIONS'		=>	$arrLanguages[$this->_intLanguageId]['regions'],
                        'CAT_ID'                        =>  $cat_id,
                        'OVERVIEW_CATEGORY_LANGUAGES'	=>	$strActivatedLanguages
                ));
                $this->_objTpl->parse('showCategories');
                $intRowClass++;


            }
            //Show Add-Category Form


            //Show paging if needed
        }
        else {
            $this->_objTpl->setVariable('TXT_OVERVIEW_NO_CATEGORIES_FOUND',$_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_NO_CATEGORIES']);
            $this->_objTpl->parse('noCategories');
        }
        $this->_objTpl->parse('Display_level');
    }

    /**
     * inserts/updating the sorting order of categories for Partners module
     *
     * @global 	array		$_ARRAYLANG
     * @global  object      $objDatabase
     */

    function insertSort() {
        global $objDatabase, $_ARRAYLANG;
        $cat_name = isset($_POST['cat_name_del']) ? $_POST['cat_name_del'] : '';

        switch($cat_name) {

            case 'level':
                if ($_POST['frmAddCategory_Submit']) {
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('selectedSortvalue')) == 'selectedSortvalue') {
                            $strkey_exploded     = explode("selectedSortvalue",$strKey);
                            $strkeyexplodeResult = $strkey_exploded[1];
                            $strvalueResult = contrexx_addslashes(strip_tags($strValue));
                            $objResult = $objDatabase->Execute('SELECT level
         		        								FROM '.DBPREFIX.'module_partners_user_level
                                                        WHERE id =  "'.$strkeyexplodeResult.'" AND sort_id = "'.$strvalueResult.'"
    					    	                        ');

                            if($objResult->RecordCount() <= 0) {
                                $objUpdate = $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_user_level`
    				                               			SET `sort_id` = "'.$strvalueResult.'" WHERE `id` = "'.$strkeyexplodeResult.'"'
                                );
                            }
                        }
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_LEVEL_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_LEVEL_ERROR'];
                }
                $this->showCategories($cat_name);
                break;

            case 'profile':
                if ($_POST['frmAddCategory_Submit']) {
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('selectedSortvalue')) == 'selectedSortvalue') {
                            $strkey_exploded     = explode("selectedSortvalue",$strKey);
                            $strkeyexplodeResult = $strkey_exploded[1];
                            $strvalueResult = contrexx_addslashes(strip_tags($strValue));
                            $objResult = $objDatabase->Execute('SELECT profile
    											            FROM '.DBPREFIX.'module_partners_user_profile
                                                            WHERE id =  "'.$strkeyexplodeResult.'" AND sort_id = "'.$strvalueResult.'"
    									           ');

                            if($objResult->RecordCount() <= 0) {
                                $objUpdate = $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_user_profile`
    									   SET `sort_id` = "'.$strvalueResult.'" WHERE `id` = "'.$strkeyexplodeResult.'"');
                            }
                        }
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_PROFILE_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_PROFILE_ERROR'];
                }
                $this->showCategories($cat_name);
                break;

            case 'country':
                if ($_POST['frmAddCategory_Submit']) {
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('selectedSortvalue')) == 'selectedSortvalue') {
                            $strkey_exploded = explode("selectedSortvalue",$strKey);
                            $strkeyexplodeResult = $strkey_exploded[1];
                            $strvalueResult = contrexx_addslashes(strip_tags($strValue));
                            $objResult = $objDatabase->Execute('SELECT country
    											            FROM '.DBPREFIX.'module_partners_user_country
                                                            WHERE id =  "'.$strkeyexplodeResult.'" AND sort_id = "'.$strvalueResult.'"
    									   ');
                            if($objResult->RecordCount() <= 0) {
                                $objUpdate = $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_user_country`
    									       SET `sort_id` = "'.$strvalueResult.'" WHERE `id` = "'.$strkeyexplodeResult.'"');
                            }
                        }
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_COUNTRY_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_COUNTRY_LEVEL_ERROR'];
                }
                $this->showCategories($cat_name);
                break;

            case 'vertical':
                if ($_POST['frmAddCategory_Submit']) {
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('selectedSortvalue')) == 'selectedSortvalue') {
                            $strkey_exploded = explode("selectedSortvalue",$strKey);
                            $strkeyexplodeResult = $strkey_exploded[1];
                            $strvalueResult = contrexx_addslashes(strip_tags($strValue));
                            $objResult = $objDatabase->Execute('SELECT vertical
    											FROM '.DBPREFIX.'module_partners_user_vertical
                                                WHERE id =  "'.$strkeyexplodeResult.'" AND sort_id = "'.$strvalueResult.'"
    									');
                            if($objResult->RecordCount() <= 0) {
                                $objUpdate = $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_user_vertical`
    									       SET `sort_id` = "'.$strvalueResult.'" WHERE `id` = "'.$strkeyexplodeResult.'"');
                            }
                        }
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_VERTICAL_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_VERTICAL_ERROR'];
                }
                $this->showCategories($cat_name);
                break;

            case 'Regions':
                if ($_POST['frmAddCategory_Submit']) {
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('selectedSortvalue')) == 'selectedSortvalue') {
                            $strkey_exploded     = explode("selectedSortvalue",$strKey);
                            $strkeyexplodeResult = $strkey_exploded[1];
                            $strvalueResult      = contrexx_addslashes(strip_tags($strValue));
                            $objResult           = $objDatabase->Execute('SELECT name
    											             FROM '.DBPREFIX.'module_partners_user_region
                                                            WHERE id =  "'.$strkeyexplodeResult.'" AND sort_id = "'.$strvalueResult.'"
    									       ');

                            if($objResult->RecordCount() <= 0) {
                                $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_user_region`
    									           SET `sort_id` = "'.$strvalueResult.'" WHERE `id` = "'.$strkeyexplodeResult.'"');
                            }
                        }
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_REGION_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_REGION_ERROR'];
                }
                $this->showCategories($cat_name);
                break;

            default:
                if ($_POST['frmAddCategory_Submit']) {
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('selectedSortvalue')) == 'selectedSortvalue') {
                            $strkey_exploded     = explode("selectedSortvalue",$strKey);
                            $strkeyexplodeResult = $strkey_exploded[1];
                            $strvalueResult      = contrexx_addslashes(strip_tags($strValue));
                            $objResult           = $objDatabase->Execute('SELECT name
    											        FROM '.DBPREFIX.'module_partners_categories
                                                        WHERE category_id =  "'.$strkeyexplodeResult.'" AND sort_id = "'.$strvalueResult.'"
    									   ');

                            if($objResult->RecordCount() <= 0) {
                                $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_categories`
    									           SET `sort_id` = "'.$strvalueResult.'" WHERE `category_id` = "'.$strkeyexplodeResult.'"');
                            }
                        }
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_CERTIFICATE_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_SORT_CERTIFICATE_ERROR'];
                }
                $this->showCategories();

        }

    }

    /**
     * Adds a new category to the database. Collected data in POST is checked for valid values.
     *
     * @global 	array		$_ARRAYLANG
     * @global 	object		$objDatabase
     */

    function insertCategory() {
        global $objDatabase, $_ARRAYLANG;
        $cat_name = isset($_POST['cat_name']) ? $_POST['cat_name'] : '';

        switch($cat_name) {

            case 'level':

                if (isset($_POST['frmAddCategory_Languages']) && is_array($_POST['frmAddCategory_Languages'])) {
                    //Get next category-id
                    $objResult = $objDatabase->Execute('SELECT	MAX(id) AS currentId
    											FROM '.DBPREFIX.'module_partners_user_level
    											ORDER BY id DESC
    							');
                    $intNextCategoryId = ($objResult->RecordCount() == 1) ? $objResult->fields['currentId'] + 1 : 1;

                    //Collect data
                    $arrValues = array();
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmAddCategory_Name_')) == 'frmAddCategory_Name_') {
                            $intLanguageId = intval(substr($strKey,strlen('frmAddCategory_Name_')));
                            $arrValues[$intLanguageId] = array(	'name' 		=> contrexx_addslashes(strip_tags($strValue)),
                                    'imgpath'   => contrexx_addslashes(strip_tags($_POST['frmLevel_Image'])),
                                    'title'     => contrexx_addslashes(strip_tags($_POST['frmLevel_title'])),
                                    'content'   => contrexx_addslashes(strip_tags($_POST['frmLevel_content'])),
                                    'cname'     => contrexx_addslashes(strip_tags($_POST['frmLevel_cname'])),
                                    'country'   => contrexx_addslashes(strip_tags($_POST['frmLevel_country'])),
                                    'phone'     => contrexx_addslashes(strip_tags($_POST['frmLevel_phone'])),
                                    'address1'  => contrexx_addslashes(strip_tags($_POST['frmLevel_address1'])),
                                    'address2'  => contrexx_addslashes(strip_tags($_POST['frmLevel_address2'])),
                                    'city'      => contrexx_addslashes(strip_tags($_POST['frmLevel_city'])),
                                    'zipcode'   => contrexx_addslashes(strip_tags($_POST['frmLevel_zipcode'])),
                                    'logo'      => contrexx_addslashes(strip_tags($_POST['frmLevel_logo'])),
                                    'clogo'     => contrexx_addslashes(strip_tags($_POST['frmLevel_clogo'])),
                                    'llogo'     => contrexx_addslashes(strip_tags($_POST['frmLevel_llogo'])),
                                    'level'     => contrexx_addslashes(strip_tags($_POST['frmLevel_level'])),
                                    'quote'     => contrexx_addslashes(strip_tags($_POST['frmLevel_quote'])),
                                    'is_active'	=> intval(in_array($intLanguageId,$_POST['frmAddCategory_Languages']))
                            );
                        }
                    }
                    foreach ($arrValues as $intLanguageId => $arrCategoryValues) {
                        $objDatabase->Execute('	INSERT INTO `'.DBPREFIX.'module_partners_user_level`
    									SET	`id`       = '.$intNextCategoryId.',
    									    `lang_id`  = '.$intLanguageId.',
    									    `is_active`= "'.$arrCategoryValues['is_active'].'",
    										`sort_id`  = "'.$intLanguage.'",
    										`imgpath`  = "'.$arrCategoryValues['imgpath'].'",
              								`level`    = "'.$arrCategoryValues['name'].'"
    								');
                        $intLevelId = $intNextCategoryId;
                    }
                    $objDatabase->Execute('	INSERT INTO `'.DBPREFIX.'module_partners_display`
    									SET
                                        `display_id`          = "",
    									`display_level_id`    = "'.$intLevelId.'",
    									`display_title`       = "'.$arrCategoryValues['title'].'",
    									`display_content`     = "'.$arrCategoryValues['content'].'",
    									`display_contactname` = "'.$arrCategoryValues['cname'].'",
    									`display_country`     = "'.$arrCategoryValues['country'].'",
    									`display_phone`       = "'.$arrCategoryValues['phone'].'",
    									`display_address1`    = "'.$arrCategoryValues['address1'].'",
    									`display_address2`    = "'.$arrCategoryValues['address2'].'",
    									`display_city`        = "'.$arrCategoryValues['city'].'",
    									`display_zipcode`     = "'.$arrCategoryValues['zipcode'].'",
    									`display_certificate_logo` = "'.$arrCategoryValues['clogo'].'",
    									`display_logo`        = "'.$arrCategoryValues['logo'].'",
    									`display_level_logo`  = "'.$arrCategoryValues['llogo'].'",
    									`display_level_text`  = "'.$arrCategoryValues['level'].'",
    									`display_quote`       = "'.$arrCategoryValues['quote'].'"

    								');
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_SUCCESSFULL'];
                }
                else if (isset($_POST['frmRenameCategory_Languages']) && is_array($_POST['frmRenameCategory_Languages'])) {
                    $arrValues = array();
                    $arrActiveLanguages = array();
                    foreach ($_POST['frmRenameCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('RenameCategory_Rename_')) == 'RenameCategory_Rename_') {
                            $intLanguageId = intval(substr($strKey,strlen('RenameCategory_Rename_')));
                            $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_categories_name`
                                			SET `name`       =  "'.contrexx_addslashes(strip_tags($_POST['RenameCategory_Rename'])).'",
            			                        `is_active`  = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
                                                WHERE `id` = 2 and `lang_id` = '.$intLanguageId.' LIMIT 1
                                    ');
                        }
                    }
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_ERROR_ACTIVE'];
                }
                $this->showCategories($cat_name);
                break;

            case 'profile':
                if (isset($_POST['frmAddCategory_Languages']) && is_array($_POST['frmAddCategory_Languages'])) {

                    //Get next category-id
                    $objResult = $objDatabase->Execute('SELECT	MAX(id) AS currentId
    											FROM '.DBPREFIX.'module_partners_user_profile
    											ORDER BY id DESC
    										');
                    $intNextCategoryId = ($objResult->RecordCount() == 1) ? $objResult->fields['currentId'] + 1 : 1;

                    //Collect data
                    $arrValues = array();
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmAddCategory_Name_')) == 'frmAddCategory_Name_') {
                            $intLanguageId             = intval(substr($strKey,strlen('frmAddCategory_Name_')));
                            $arrValues[$intLanguageId] = array(	'name' 		=> contrexx_addslashes(strip_tags($strValue)),
                                    'is_active'	=> intval(in_array($intLanguageId,$_POST['frmAddCategory_Languages']))
                            );
                        }
                    }
                    foreach ($arrValues as $intLanguageId => $arrCategoryValues) {
                        $objDatabase->Execute('	INSERT INTO `'.DBPREFIX.'module_partners_user_profile`
    								 	SET	`id`      = "'.$intNextCategoryId.'",
    								    	`lang_id` = '.$intLanguageId.',
    									    `is_active` = "'.$arrCategoryValues['is_active'].'",
    										`sort_id` = "'.$intLanguage.'",
              								`profile` = "'.$arrCategoryValues['name'].'"
    								');
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_SUCCESSFULL'];
                }
                else if (isset($_POST['frmRenameCategory_Languages']) && is_array($_POST['frmRenameCategory_Languages'])) {
                    $arrValues = array();
                    $arrActiveLanguages = array();
                    foreach ($_POST['frmRenameCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('RenameCategory_Rename_')) == 'RenameCategory_Rename_') {
                            $intLanguageId = intval(substr($strKey,strlen('RenameCategory_Rename_')));
                            $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_categories_name`
            			                     SET `name`      =  "'.contrexx_addslashes(strip_tags($_POST['RenameCategory_Rename'])).'",
            			                         `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
                                            WHERE `id` = 3 and `lang_id` = '.$intLanguageId.' LIMIT 1
                                    ');
                        }
                    }
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_ERROR_ACTIVE'];
                }
                $this->showCategories($cat_name);
                break;

            case 'country':
                if (isset($_POST['frmAddCategory_Languages']) && is_array($_POST['frmAddCategory_Languages'])) {

                    //Get next category-id
                    $objResult = $objDatabase->Execute('SELECT	MAX(id) AS currentId
    											FROM '.DBPREFIX.'module_partners_user_country
    											ORDER BY id DESC
    										');
                    $intNextCategoryId = ($objResult->RecordCount() == 1) ? $objResult->fields['currentId'] + 1 : 1;

                    //Collect data
                    $arrValues = array();
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmAddCategory_Name_')) == 'frmAddCategory_Name_') {
                            $intLanguageId             = intval(substr($strKey,strlen('frmAddCategory_Name_')));
                            $arrValues[$intLanguageId] = array(	'name' 		=> contrexx_addslashes(strip_tags($strValue)),
                                    'is_active'	=> intval(in_array($intLanguageId,$_POST['frmAddCategory_Languages']))
                            );
                        }
                    }
                    foreach ($arrValues as $intLanguageId => $arrCategoryValues) {
                        $objDatabase->Execute('	INSERT INTO `'.DBPREFIX.'module_partners_user_country`
    									SET	`id`      = "'.$intNextCategoryId.'",
    									    `lang_id` = '.$intLanguageId.',
    									    `is_active` = "'.$arrCategoryValues['is_active'].'",
    										`sort_id` = "'.$intLanguage.'",
              								`country` = "'.$arrCategoryValues['name'].'"
    								');
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_SUCCESSFULL'];
                }
                else if (isset($_POST['frmRenameCategory_Languages']) && is_array($_POST['frmRenameCategory_Languages'])) {
                    $arrValues = array();
                    $arrActiveLanguages = array();
                    foreach ($_POST['frmRenameCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('RenameCategory_Rename_')) == 'RenameCategory_Rename_') {
                            $intLanguageId = intval(substr($strKey,strlen('RenameCategory_Rename_')));
                            $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_categories_name`
            		  	                   SET `name`      =  "'.contrexx_addslashes(strip_tags($_POST['RenameCategory_Rename'])).'",
            			                       `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
                                                WHERE `id` = 4 and `lang_id` = '.$intLanguageId.' LIMIT 1
                                 ');
                        }
                    }
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_ERROR_ACTIVE'];
                }
                $this->showCategories($cat_name);
                break;

            case 'vertical':

                if (isset($_POST['frmAddCategory_Languages']) && is_array($_POST['frmAddCategory_Languages'])) {
                    //Get next category-id
                    $objResult = $objDatabase->Execute('SELECT	MAX(id) AS currentId
    											FROM '.DBPREFIX.'module_partners_user_vertical
    											ORDER BY id DESC
    										');
                    $intNextCategoryId = ($objResult->RecordCount() == 1) ? $objResult->fields['currentId'] + 1 : 1;

                    //Collect data
                    $arrValues = array();
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmAddCategory_Name_')) == 'frmAddCategory_Name_') {
                            $intLanguageId = intval(substr($strKey,strlen('frmAddCategory_Name_')));
                            $arrValues[$intLanguageId] = array(	'name' 		=> contrexx_addslashes(strip_tags($strValue)),
                                    'is_active'	=> intval(in_array($intLanguageId,$_POST['frmAddCategory_Languages']))
                            );
                        }
                    }
                    foreach ($arrValues as $intLanguageId => $arrCategoryValues) {
                        $objDatabase->Execute('	INSERT INTO `'.DBPREFIX.'module_partners_user_vertical`
    									SET	`id` = "'.$intNextCategoryId.'",
    								    	`lang_id` = '.$intLanguageId.',
    									    `is_active` = "'.$arrCategoryValues['is_active'].'",
    										`sort_id` = "'.$intLanguage.'",
              								`vertical` = "'.$arrCategoryValues['name'].'"
    								');
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_SUCCESSFULL'];
                }
                else if (isset($_POST['frmRenameCategory_Languages']) && is_array($_POST['frmRenameCategory_Languages'])) {
                    $arrValues = array();
                    $arrActiveLanguages = array();
                    foreach ($_POST['frmRenameCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('RenameCategory_Rename_')) == 'RenameCategory_Rename_') {
                            $intLanguageId = intval(substr($strKey,strlen('RenameCategory_Rename_')));
                            $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_categories_name`
            			         SET `name`      =  "'.contrexx_addslashes(strip_tags($_POST['RenameCategory_Rename'])).'",
            			             `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
                                    WHERE `id` = 5 and `lang_id` = '.$intLanguageId.' LIMIT 1
                                ');
                        }
                    }
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_ERROR_ACTIVE'];
                }
                $this->showCategories($cat_name);
                break;

            case 'Regions':
                $cat_id = intval(trim(strip_tags($_POST['cat_id'])));
                if (isset($_POST['frmAddCategory_Languages']) && is_array($_POST['frmAddCategory_Languages'])) {

                    //Get next category-id
                    $objResult = $objDatabase->Execute('SELECT		MAX(id) AS currentId
    											FROM		'.DBPREFIX.'module_partners_user_region
    											ORDER BY	id DESC
    										');
                    $intNextCategoryId = ($objResult->RecordCount() == 1) ? $objResult->fields['currentId'] + 1 : 1;

                    //Collect data
                    $arrValues = array();
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmAddCategory_Name_')) == 'frmAddCategory_Name_') {
                            $intLanguageId = intval(substr($strKey,strlen('frmAddCategory_Name_')));
                            $arrValues[$intLanguageId] = array(	'name' 		=> contrexx_addslashes(strip_tags($strValue)),
                                    'cat_id' 		=> contrexx_addslashes(strip_tags($_POST['cat_id'])),
                                    'is_active'	    => intval(in_array($intLanguageId,$_POST['frmAddCategory_Languages']))
                            );
                        }
                    }
                    foreach ($arrValues as $intLanguageId => $arrCategoryValues) {
                        $objDatabase->Execute('	INSERT INTO `'.DBPREFIX.'module_partners_user_region`
    									SET	`id` = '.$intNextCategoryId.',
    										`lang_id` = '.$intLanguageId.',
    										`cat_id`  = '.$arrCategoryValues['cat_id'].',
    										`is_active` = "'.$arrCategoryValues['is_active'].'",
    										`name` = "'.$arrCategoryValues['name'].'"

    								');
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_SUCCESSFULL'];
                }
                else if (isset($_POST['frmRenameCategory_Languages']) && is_array($_POST['frmRenameCategory_Languages'])) {
                    $arrValues = array();
                    $arrActiveLanguages = array();
                    foreach ($_POST['frmRenameCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('RenameCategory_Rename_')) == 'RenameCategory_Rename_') {
                            $intLanguageId = intval(substr($strKey,strlen('RenameCategory_Rename_')));
                            $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_categories_name`
            			     SET `name`      =  "'.contrexx_addslashes(strip_tags($_POST['RenameCategory_Rename'])).'",
            			         `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
                                 WHERE `id` = 6 and `lang_id` = '.$intLanguageId.' LIMIT 1
                        ');
                        }
                    }
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_ERROR_ACTIVE'];
                }
                $this->addRegions($cat_name,$cat_id);
                break;

            default:

                if (isset($_POST['frmAddCategory_Languages']) && is_array($_POST['frmAddCategory_Languages'])) {

                    //Get next category-id
                    $objResult = $objDatabase->Execute('SELECT		MAX(category_id) AS currentId
    											FROM		'.DBPREFIX.'module_partners_categories
    											ORDER BY	category_id DESC
    										');
                    $intNextCategoryId = ($objResult->RecordCount() == 1) ? $objResult->fields['currentId'] + 1 : 1;

                    //Collect data
                    $arrValues = array();
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmAddCategory_Name_')) == 'frmAddCategory_Name_') {
                            $intLanguageId = intval(substr($strKey,strlen('frmAddCategory_Name_')));
                            $arrValues[$intLanguageId] = array(	'name' 		=> contrexx_addslashes(strip_tags($strValue)),
                                    'imgpath'   => contrexx_addslashes(strip_tags($_POST['frmLevel_Image'])),
                                    'is_active'	=> intval(in_array($intLanguageId,$_POST['frmAddCategory_Languages']))
                            );
                        }
                    }
                    foreach ($arrValues as $intLanguageId => $arrCategoryValues) {
                        $objDatabase->Execute('	INSERT INTO `'.DBPREFIX.'module_partners_categories`
    									SET	   `category_id` = '.$intNextCategoryId.',
    										   `lang_id`     = '.$intLanguageId.',
    										   `is_active`   = "'.$arrCategoryValues['is_active'].'",
    										   `name`        = "'.$arrCategoryValues['name'].'",
    										   `imgpath`     = "'.$arrCategoryValues['imgpath'].'"
    								');
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_SUCCESSFULL'];
                }
                else if (isset($_POST['frmRenameCategory_Languages']) && is_array($_POST['frmRenameCategory_Languages'])) {
                    $arrValues = array();
                    $arrActiveLanguages = array();
                    foreach ($_POST['frmRenameCategory_Languages'] as $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }
                    foreach($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('RenameCategory_Rename_')) == 'RenameCategory_Rename_') {
                            $intLanguageId = intval(substr($strKey,strlen('RenameCategory_Rename_')));
                            $objDatabase->Execute('UPDATE `'.DBPREFIX.'module_partners_categories_name`
            			     SET `name`      =  "'.contrexx_addslashes(strip_tags($_POST['RenameCategory_Rename'])).'",
            			         `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
                                WHERE `id` = 1 and `lang_id` = '.$intLanguageId.' LIMIT 1
                        ');
                        }
                    }
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_ERROR_ACTIVE'];
                }
                $this->showCategories();
        }
    }


    /**
     * Removes a category from the database.
     *
     * @param 	integer		$intCategoryId: This category will be deleted by the function.
     * @global 	array		$_ARRAYLANG
     * @global 	object		$objDatabase
     */
    function deleteCategory($intCategoryId,$cat_name,$strAction,$catId) {
        global $_ARRAYLANG, $objDatabase;
        $intCategoryId = intval($intCategoryId);

        switch($cat_name) {
            case 'level':

                if ($intCategoryId > 0) {
                    $objDatabase->Execute('	DELETE
    								FROM '.DBPREFIX.'module_partners_user_level
    								WHERE `id` = '.$intCategoryId.'
    							');
                    $objDatabase->Execute('	DELETE
    								FROM '.DBPREFIX.'module_partners_display
    								WHERE `display_level_id` = '.$intCategoryId.'
    							');
                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_ERROR'];
                }
                if($strAction=="")
                    $this->showCategories($cat_name);
                break;

            case 'profile':

                if ($intCategoryId > 0) {
                    $objDatabase->Execute('	DELETE
    								FROM '.DBPREFIX.'module_partners_user_profile
    								WHERE `id` = '.$intCategoryId.'
    							');

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_ERROR'];
                }
                if($strAction=="") {
                    $this->showCategories($cat_name);
                }
                break;

            case 'country':

                if ($intCategoryId > 0) {
                    $objDatabase->Execute('	DELETE
    								FROM '.DBPREFIX.'module_partners_user_country
    								WHERE `id` = '.$intCategoryId.'
    							');
                    $objDatabase->Execute('	DELETE
    								FROM '.DBPREFIX.'module_partners_user_region
    								WHERE `cat_id` = '.$intCategoryId.'
    							');

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_ERROR'];
                }
                if($strAction=="")
                    $this->showCategories($cat_name);
                break;

            case 'vertical':

                if ($intCategoryId > 0) {
                    $objDatabase->Execute('	DELETE
    								FROM '.DBPREFIX.'module_partners_user_vertical
    								WHERE `id` = '.$intCategoryId.'
    							');

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_ERROR'];
                }
                if($strAction=="")
                    $this->showCategories($cat_name);
                break;

            case 'Regions':
                $cat_id = intval(strip_tags(trim($_REQUEST['cat_id'])));
                if($cat_id == 0) {
                    $cat_id = $catId;
                }
                if ($intCategoryId > 0) {
                    $objDatabase->Execute('	DELETE
    								FROM '.DBPREFIX.'module_partners_user_region
    								WHERE `id` = '.$intCategoryId.'
    							');

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_SUCCESSFULL'];
                }
                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_ERROR'];
                }
                if($strAction=="")
                    $this->addRegions($cat_name,$cat_id);
                break;

            default:
                if ($intCategoryId > 0) {
                    $objDatabase->Execute('	DELETE
    								FROM '.DBPREFIX.'module_partners_categories
    								WHERE `category_id` = '.$intCategoryId.'
    							');

                    if (!$this->_boolInnoDb) {
                        $objDatabase->Execute('	DELETE
										FROM '.DBPREFIX.'module_partners_message_to_category
										WHERE `category_id` = '.$intCategoryId.'
									');
                    }

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_SUCCESSFULL'];
                }

                else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_DELETE_ERROR'];
                }
                if($strAction=="")
                    $this->showCategories();
        }
    }


    /**
     * Performs the action for the dropdown-selection on the category page. The behaviour depends on the parameter.
     *
     * @param	string		$strAction: the action passed by the formular.
     */
    function doCategoryMultiAction($strAction='',$cat_name,$catId) {
        switch ($strAction) {
            case 'delete':
                foreach($_POST['selectedCategoryId'] as $intKey => $intCategoryId) {
                    $this->deleteCategory($intCategoryId,$cat_name,$strAction='multidelete',$catId);
                }
                if($cat_name!="Regions") {
                    $this->showCategories($cat_name);
                }else {
                    $this->addRegions($cat_name,$catId);
                }
                break;
            default:
            //do nothing!
        }
    }


    /**
     * Shows the edit-page for a specific category.
     *
     * @global	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     * @global 	object		$objDatabase
     * @param 	integer		$intCategoryId: The category with this id will be loaded into the form.
     */
    function editCategory($intCategoryId,$category) {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $cat_id = intval(trim(strip_tags($_REQUEST['cat_id'])));
        $this->_strPageTitle = $_CORELANG['TXT_PARTNERS_CATEGORY_MANAGE_TITLE'];
        $this->_objTpl->addBlockfile('PARTNERS_SUBCATEGORY_FILE', 'settings_block', 'module_partners_categories_edit.htm');
        $this->_objTpl->setVariable(array(
                'TXT_EDIT_TITLE'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_EDIT_TITLE'],
                'TXT_EDIT_NAME'			=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_NAME'],
                'TXT_EDIT_EXTENDED'		=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_EXTENDED'],
                'TXT_EDIT_LANGUAGES'	=>	$_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_LANGUAGES'],
                'CAT_ADD_ID'            =>  $cat_id,
                'TXT_EDIT_SUBMIT'		=>	$_CORELANG['TXT_SAVE']
        ));

        $intCategoryId = intval($intCategoryId);

        if(trim($category)=="") {
            $category = isset($_GET['category']) ? $_GET['category'] : 0;
        }


        if($category == "level") {
            $category = $category;
        }

        else if($category == "profile") {

            $this->_objTpl->setVariable(array('PARTNERS_CAT_NAME'     => "profile",
                    'PARTNERS_CAT_NAME_DEL' => "profile",
                    'TXT_OVERVIEW_TITLE'    => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('3'),
                    'TXT_RENAME_TITLE'      => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('3'),
                    'TXT_ADD_TITLE'         => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('3')
            ));
        }
        else if($category == "country") {

            $this->_objTpl->setVariable(array('PARTNERS_CAT_NAME'                       => "country",
                    'PARTNERS_CAT_NAME_DEL'                   => "country",
                    'TXT_OVERVIEW_SUBTITLE_ADD_REGIONS'		=> $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ADD_REGIONS'],
                    'TXT_OVERVIEW_TITLE'                      => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('4'),
                    'TXT_RENAME_TITLE'                        => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('4'),
                    'TXT_ADD_TITLE'                           => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('4')
            ));
        }
        else if($category == "vertical") {
            // $arrRenameCategories = $this->CreateRegionArray($catId = 5);
            $this->_objTpl->setVariable(array('PARTNERS_CAT_NAME'     => "vertical",
                    'PARTNERS_CAT_NAME_DEL' => "vertical",
                    'TXT_OVERVIEW_TITLE'    => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('5'),
                    'TXT_RENAME_TITLE'      => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('5'),
                    'TXT_ADD_TITLE'         => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('5')
            ));
        }
        else if($category == "Regions") {
            $category = $category;
        }
        else {
            $category = "certificate";

            $this->_objTpl->setVariable(array('PARTNERS_CAT_NAME'     => "",
                    'PARTNERS_CAT_NAME_DEL' => $_ARRAYLANG['PARTNERS_CATNAME_CATEGORY'],
                    'TXT_OVERVIEW_TITLE'    => $_ARRAYLANG['PARTNERS_CATNAME_MANAGE'].$this->_getCategoryname('1'),
                    'TXT_ADD_TITLE'         => $_ARRAYLANG['PARTNERS_CATNAME_ADD'].$this->_getCategoryname('1'),
                    'TXT_RENAME_TITLE'      => $_ARRAYLANG['PARTNERS_CATNAME_RENAME'].$this->_getCategoryname('1'),
                    'PARTNERS_LEVEL_IMG'    => $_ARRAYLANG['PARTNERS_LEVEL_IMAGE']
            ));
        }

        $arrCategories = $this->createCategoryArray($intPagingPosition=0, $intLimitIndex=0,$category,$intCategoryId,$cat_id);
        if (array_key_exists($intCategoryId,$arrCategories)) {

            $intCounter = 0;
            $arrLanguages = array(0 => '', 1 => '', 2 => '');

            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                $arrLanguages[$intCounter%3] .= '<input '.(($arrCategories[$intCategoryId][$intLanguageId]['is_active'] == 1) ? 'checked="checked"' : '').' type="checkbox" name="frmEditCategory_Languages[]" value="'.$intLanguageId.'" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';

                $this->_objTpl->setVariable(array(
                        'EDIT_NAME_LANGID'	=>	$intLanguageId,
                        'EDIT_NAME_LANG'	=>	$arrTranslations['long'].' ['.$arrTranslations['short'].']',
                        'EDIT_NAME_VALUE'	=>	$arrCategories[$intCategoryId][$intLanguageId]['name']
                ));

                $this->_objTpl->parse('editCategoryNameFields');

                ++$intCounter;
            }

            $this->_objTpl->setVariable(array(
                    'EDIT_CATEGORY_ID'	=>	$intCategoryId,
                    'EDIT_NAME'			=>	$arrCategories[$intCategoryId][$this->_intLanguageId]['name'],
                    'EDIT_LANGUAGES_1'	=>	$arrLanguages[0],
                    'EDIT_LANGUAGES_2'	=>	$arrLanguages[1],
                    'EDIT_LANGUAGES_3'	=>	$arrLanguages[2]
            ));
        } else {
            //Wrong category-id
            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_EDIT_ERROR_ID'];
        }
    }

    /**
     * Updates an existing category.
     *
     * @global 	array		$_ARRAYLANG
     * @global 	object		$objDatabase
     */
    function updateCategory($cat_name) {
        global $_ARRAYLANG, $objDatabase;
        $intSortId = 0;
        switch($cat_name) {
            case 'level':
                if (isset($_POST['frmEditCategory_Languages']) && is_array($_POST['frmEditCategory_Languages'])) {
                    $intCategoryId = intval($_POST['frmEditCategory_Id']);

                    //Collect active-languages
                    foreach ($_POST['frmEditCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }
                    $imgpath        = $_POST['frmLevel_Image'];
                    $Level_title    = $_POST['frmLevel_title'];
                    $Level_content  = $_POST['frmLevel_content'];
                    $Level_cname    = $_POST['frmLevel_cname'];
                    $Level_country  = $_POST['frmLevel_country'];
                    $Level_phone    = $_POST['frmLevel_phone'];
                    $Level_address1 = $_POST['frmLevel_address1'];
                    $Level_address2 = $_POST['frmLevel_address2'];
                    $Level_city     = $_POST['frmLevel_city'];
                    $Level_zipcode  = $_POST['frmLevel_zipcode'];
                    $Level_logo     = $_POST['frmLevel_logo'];
                    $Level_clogo    = $_POST['frmLevel_clogo'];
                    $Level_llogo    = $_POST['frmLevel_llogo'];
                    $Level_level    = $_POST['frmLevel_level'];
                    $Level_quote    = $_POST['frmLevel_quote'];

                    //Collect names & check for existing database-entry
                    foreach ($_POST as $strKey => $strValue) {

                        if (substr($strKey,0,strlen('frmEditCategory_Name_')) == 'frmEditCategory_Name_') {

                            $intLanguageId = substr($strKey,strlen('frmEditCategory_Name_'));

                            $objResult = $objDatabase->Execute('SELECT level
    													FROM	'.DBPREFIX.'module_partners_user_level
                                                        WHERE `id` = '.$intCategoryId.' AND
    													`lang_id` = '.$intLanguageId.'
    													LIMIT	1
    												');


                            $restoreValue;
                            if ($objResult->RecordCount() == 0) {
                                //We have to create a new entry first
                                //print $strValue;
                                $objDatabase->Execute('	INSERT
		    									INTO	`'.DBPREFIX.'module_partners_user_level`
		    									SET		`id` = '.$intCategoryId.',
               											`lang_id` = '.$intLanguageId.',
		    									        `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'",
		    											`sort_id` = '.$intSortId.',
                 										`level`   = "'.contrexx_addslashes(strip_tags($restoreValue)).'",
                 										`imgpath` = "'.$imgpath.'"
		    								');
                            }
                            else {
                                //We can update the existing entry
                                $restoreValue =  $objResult->fields['level'];
                                $objDatabase->Execute('	UPDATE	`'.DBPREFIX.'module_partners_user_level`
		    									SET     `level`      = "'.contrexx_addslashes(strip_tags($strValue)).'",
              	    									`is_active`  = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'",
		    									        `imgpath`    =  "'.$imgpath.'"
                                                         WHERE	`id` = '.$intCategoryId.' AND
			    										`lang_id`    = '.$intLanguageId.'
			    								LIMIT	1
		    								');
                                $objDisplaycheck = $objDatabase->Execute('SELECT display_id
    													FROM	'.DBPREFIX.'module_partners_display
														WHERE	`display_level_id` = '.$intCategoryId.'
    													LIMIT	1
    												');
                                if ($objDisplaycheck->RecordCount() == 0) {
                                    $objDatabase->Execute('	INSERT INTO	`'.DBPREFIX.'module_partners_display`
		    									SET
                                                        `display_id` = "",
                                                        `display_level_id` = "'.$intCategoryId.'",
                                                        `display_title` = "'.contrexx_addslashes(strip_tags($Level_title)).'",
                                                        `display_content` = "'.contrexx_addslashes(strip_tags($Level_content)).'",
                                                        `display_contactname` = "'.contrexx_addslashes(strip_tags($Level_cname)).'",
                                                        `display_country` = "'.contrexx_addslashes(strip_tags($Level_country)).'",
                                                        `display_phone` = "'.contrexx_addslashes(strip_tags($Level_phone)).'",
                                                        `display_address1` = "'.contrexx_addslashes(strip_tags($Level_address1)).'",
                                                        `display_address2` = "'.contrexx_addslashes(strip_tags($Level_address2)).'",
                                                        `display_city` = "'.contrexx_addslashes(strip_tags($Level_city)).'",
                                                        `display_zipcode` = "'.contrexx_addslashes(strip_tags($Level_zipcode)).'",
                                                        `display_certificate_logo` = "'.contrexx_addslashes(strip_tags($Level_clogo)).'",
                                                        `display_logo` = "'.contrexx_addslashes(strip_tags($Level_logo)).'",
                                                        `display_level_logo` = "'.contrexx_addslashes(strip_tags($Level_llogo)).'",
                                                        `display_level_text` = "'.contrexx_addslashes(strip_tags($Level_level)).'",
                                                        `display_quote` = "'.contrexx_addslashes(strip_tags($Level_quote)).'"

		    								');
                                }
                                else {

                                    $objDatabase->Execute(' UPDATE	`'.DBPREFIX.'module_partners_display`
		    									SET     `display_title` = "'.contrexx_addslashes(strip_tags($Level_title)).'",
                                                        `display_content` = "'.contrexx_addslashes(strip_tags($Level_content)).'",
                                                        `display_contactname` = "'.contrexx_addslashes(strip_tags($Level_cname)).'",
                                                        `display_country` = "'.contrexx_addslashes(strip_tags($Level_country)).'",
                                                        `display_phone` = "'.contrexx_addslashes(strip_tags($Level_phone)).'",
                                                        `display_address1` = "'.contrexx_addslashes(strip_tags($Level_address1)).'",
                                                        `display_address2` = "'.contrexx_addslashes(strip_tags($Level_address2)).'",
                                                        `display_city` = "'.contrexx_addslashes(strip_tags($Level_city)).'",
                                                        `display_zipcode` = "'.contrexx_addslashes(strip_tags($Level_zipcode)).'",
                                                        `display_certificate_logo` = "'.contrexx_addslashes(strip_tags($Level_clogo)).'",
                                                        `display_logo` = "'.contrexx_addslashes(strip_tags($Level_logo)).'",
                                                        `display_level_logo` = "'.contrexx_addslashes(strip_tags($Level_llogo)).'",
                                                        `display_level_text` = "'.contrexx_addslashes(strip_tags($Level_level)).'",
                                                        `display_quote` = "'.contrexx_addslashes(strip_tags($Level_quote)).'"
                                                         WHERE	`display_level_id` = '.$intCategoryId.'

		    								');
                                }
                            }

                        }
                    }

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_SUCCESSFULL'];
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_ERROR_ACTIVE'];
                }

                $this->showCategories($cat_name);
                break;

            case 'profile':

                if(isset($_POST['frmEditCategory_Submit'])) {

                    $intCategoryId = intval($_POST['frmEditCategory_Id']);

                    //Collect active-languages
                    foreach ($_POST['frmEditCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }

                    //Collect names & check for existing database-entry
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmEditCategory_Name_')) == 'frmEditCategory_Name_') {
                            $intLanguageId = substr($strKey,strlen('frmEditCategory_Name_'));

                            $objResult = $objDatabase->Execute('SELECT profile
    													FROM	'.DBPREFIX.'module_partners_user_profile
														WHERE	`id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
    													LIMIT	1
    												');
                            $restoreValue;
                            if ($objResult->RecordCount() == 0) {
                                //We have to create a new entry first

                                $objDatabase->Execute('INSERT
		    									INTO	`'.DBPREFIX.'module_partners_user_profile`
		    									SET		`id` = '.$intCategoryId.',
		    									        `lang_id` = '.$intLanguageId.',
		    									        `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'",
		    											`sort_id` = '.$intSortId.',
                 										`profile` = "'.contrexx_addslashes(strip_tags($restoreValue)).'"
		    								');
                            } else {
                                //We can update the existing entry
                                $restoreValue =  $objResult->fields['profile'];
                                $objDatabase->Execute('	UPDATE	`'.DBPREFIX.'module_partners_user_profile`
		    									SET     `profile` = "'.contrexx_addslashes(strip_tags($strValue)).'",
		    									`is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
												WHERE	`id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
			    								LIMIT	1
		    								');
                            }

                        }
                    }

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_SUCCESSFULL'];
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_ERROR_ACTIVE'];
                }
                $this->showCategories($cat_name);
                break;

            case 'country':

                if(isset($_POST['frmEditCategory_Submit'])) {

                    $intCategoryId = intval($_POST['frmEditCategory_Id']);

                    //Collect active-languages
                    foreach ($_POST['frmEditCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }

                    //Collect names & check for existing database-entry
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmEditCategory_Name_')) == 'frmEditCategory_Name_') {
                            $intLanguageId = substr($strKey,strlen('frmEditCategory_Name_'));

                            $objResult = $objDatabase->Execute('SELECT country
    													FROM	'.DBPREFIX.'module_partners_user_country
														WHERE	`id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
    													LIMIT	1
    												');
                            $restoreValue;
                            if ($objResult->RecordCount() == 0) {
                                //We have to create a new entry first
                                $objDatabase->Execute('INSERT
		    									INTO	`'.DBPREFIX.'module_partners_user_country`
		    									SET		`id` = '.$intCategoryId.',
		    									        `lang_id` = '.$intLanguageId.',
		    									        `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'",
		    											`sort_id` = '.$intSortId.',
                 										`country` = "'.contrexx_addslashes(strip_tags($restoreValue)).'"
		    								');
                            } else {
                                //We can update the existing entry
                                $restoreValue =  $objResult->fields['country'];
                                $objDatabase->Execute('	UPDATE	`'.DBPREFIX.'module_partners_user_country`
		    									SET     `country` = "'.contrexx_addslashes(strip_tags($strValue)).'",
		    									`is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
												WHERE	`id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
			    								LIMIT	1
		    								');
                            }

                        }
                    }

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_SUCCESSFULL'];
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_ERROR_ACTIVE'];
                }
                $this->showCategories($cat_name);
                break;

            case 'vertical':

                if(isset($_POST['frmEditCategory_Submit'])) {

                    $intCategoryId = intval($_POST['frmEditCategory_Id']);

                    //Collect active-languages
                    foreach ($_POST['frmEditCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }

                    //Collect names & check for existing database-entry
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmEditCategory_Name_')) == 'frmEditCategory_Name_') {
                            $intLanguageId = substr($strKey,strlen('frmEditCategory_Name_'));

                            $objResult = $objDatabase->Execute('SELECT vertical
    													FROM	'.DBPREFIX.'module_partners_user_vertical
														WHERE	`id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
    													LIMIT	1
    												');
                            $restoreValue;
                            if ($objResult->RecordCount() == 0) {
                                //We have to create a new entry first
                                $restoreValue;
                                $objDatabase->Execute('	INSERT
		    									INTO	`'.DBPREFIX.'module_partners_user_vertical`
		    									SET		`id` = '.$intCategoryId.',
		    									        `lang_id` = '.$intLanguageId.',
		    								        	`is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'",
		    											`sort_id` = '.$intSortId.',
                 										`vertical` = "'.contrexx_addslashes(strip_tags($restoreValue)).'"
		    								');
                            } else {
                                //We can update the existing entry
                                $restoreValue =  $objResult->fields['vertical'];
                                $objDatabase->Execute('	UPDATE	`'.DBPREFIX.'module_partners_user_vertical`
		    									SET     `vertical` = "'.contrexx_addslashes(strip_tags($strValue)).'",
		    									`is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
												WHERE	`id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
			    								LIMIT	1
		    								');
                            }

                        }
                    }

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_SUCCESSFULL'];
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_ERROR_ACTIVE'];
                }
                $this->showCategories($cat_name);
                break;

            case 'Regions':
                $cat_id = intval(trim(strip_tags($_REQUEST['cat_id'])));
                if(isset($_POST['frmEditCategory_Submit'])) {
                    $intCategoryId = intval($_POST['frmEditCategory_Id']);


                    //Collect active-languages
                    foreach ($_POST['frmEditCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }

                    //Collect names & check for existing database-entry
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmEditCategory_Name_')) == 'frmEditCategory_Name_') {
                            $intLanguageId = substr($strKey,strlen('frmEditCategory_Name_'));

                            $objResult = $objDatabase->Execute('SELECT name
    													FROM	'.DBPREFIX.'module_partners_user_region
														WHERE	`id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
    													LIMIT	1
    												');
                            $restoreValue;
                            if ($objResult->RecordCount() == 0) {
                                //We have to create a new entry first
                                $objDatabase->Execute('	INSERT
		    									INTO	`'.DBPREFIX.'module_partners_user_region`
		    									SET		`id` = '.$intCategoryId.',
		    									        `lang_id` = '.$intLanguageId.',
		    								        	`is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'",
		    								        	`cat_id`  = '.$cat_id.',
		    											`sort_id` = '.$intSortId.',
                 										`name` = "'.contrexx_addslashes(strip_tags($restoreValue)).'"
		    								');
                            } else {
                                //We can update the existing entry
                                $restoreValue = $objResult->fields['name'];
                                $objDatabase->Execute('	UPDATE	`'.DBPREFIX.'module_partners_user_region`
		    									SET     `name` = "'.contrexx_addslashes(strip_tags($strValue)).'",
		    									        `is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'"
												WHERE	`id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
			    								LIMIT	1
		    								');
                            }

                        }
                    }

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_SUCCESSFULL'];
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_ERROR_ACTIVE'];
                }
                $this->addRegions($cat_name,$cat_id);
                break;

            default:

                if (isset($_POST['frmEditCategory_Languages']) && is_array($_POST['frmEditCategory_Languages'])) {
                    $intCategoryId = intval($_POST['frmEditCategory_Id']);
                    $imgpath = $_POST['frmLevel_Image'];
                    //Collect active-languages
                    foreach ($_POST['frmEditCategory_Languages'] as $intKey => $intLanguageId) {
                        $arrActiveLanguages[$intLanguageId] = true;
                    }

                    //Collect names & check for existing database-entry
                    foreach ($_POST as $strKey => $strValue) {
                        if (substr($strKey,0,strlen('frmEditCategory_Name_')) == 'frmEditCategory_Name_') {
                            $intLanguageId = substr($strKey,strlen('frmEditCategory_Name_'));

                            $objResult = $objDatabase->Execute('SELECT name
    													FROM	'.DBPREFIX.'module_partners_categories
														WHERE	`category_id` = '.$intCategoryId.' AND
    															`lang_id` = '.$intLanguageId.'
    													LIMIT	1
    												');
                            $restoreValue;
                            if ($objResult->RecordCount() == 0) {
                                //We have to create a new entry first
                                $objDatabase->Execute('	INSERT
		    									INTO	`'.DBPREFIX.'module_partners_categories`
		    									SET		`category_id` = '.$intCategoryId.',
		    											`lang_id` = '.$intLanguageId.',
		    											`is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'",
		    											`name` = "'.contrexx_addslashes(strip_tags($restoreValue)).'",
		    											`imgpath` = "'.$imgpath.'"
		    								');
                            } else {
                                //We can update the existing entry
                                $restoreValue =  $objResult->fields['name'];
                                $objDatabase->Execute('	UPDATE	`'.DBPREFIX.'module_partners_categories`
		    									SET		`is_active` = "'.(array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0').'",
		    											`name` = "'.contrexx_addslashes(strip_tags($strValue)).'",
		    											`imgpath` = "'.$imgpath.'"
												WHERE	`category_id` = '.$intCategoryId.' AND
			    										`lang_id` = '.$intLanguageId.'
			    								LIMIT	1
		    								');
                            }

                        }
                    }

                    $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_SUCCESSFULL'];
                } else {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_CATEGORY_UPDATE_ERROR_ACTIVE'];
                }
                $this->showCategories();
        }
    }
    /**
     * Shows an overview of all entries.
     *
     * @global  array       $_CORELANG
     * @global  array       $_ARRAYLANG
     */
    function showOverview() {
        global $_CORELANG, $_ARRAYLANG;

        $recipientTitle     = 0;
        $titleName_level    = "level";
        $titleName_profile  = "profile";
        $titleName_country  = "country";
        $titleName_vertical = "vertical";

        $this->_strPageTitle = $_CORELANG['TXT_PARTNERS_OVERVIEW_TITLE'];
        $this->_objTpl->loadTemplateFile('module_partners_entries.html',true,true);

        $arrSettings   = $this->_getSettings();

        foreach($arrSettings as $setKey => $setValue) {
            if($arrSettings['lis_active']!=0) {
                $this->_objTpl->setVariable(array(
                        'TXT_ENTRIES_LEVEL'                 =>  "<td>".$this->_getCategoryname('2')."</td>",
                        'PARTNERS_LEVEL'    		        =>  "<td>".$this->_getListLevelMenu($recipientTitle,$titleName_level,'name="level" style="width:120px;overflow:scroll" size="1"','All')."</td>"
                ));
                // print $rowClass;
            }else {
                //do Nothing

            }
            if($arrSettings['pis_active']!=0) {
                $this->_objTpl->setVariable(array(
                        'TXT_ENTRIES_PROFILE'               =>  "<td>".$this->_getCategoryname('3'),
                        'PARTNERS_PROFILE'    		        =>  "<td>".$this->_getListLevelMenu($recipientTitle,$titleName_profile,'name="profile" style="width:120px;overflow:scroll" size="1"','All')."</td>"
                ));
            }else {
                //doNothing
            }
            if($arrSettings['cis_active']!=0) {
                $this->_objTpl->setVariable(array(
                        'TXT_ENTRIES_COUNTRY'               =>  "<td>".$this->_getCategoryname('4')."</td>",
                        'PARTNERS_COUNTRY'    		        =>  "<td>".$this->_getListLevelMenu($recipientTitle,$titleName_country,'name="country" style="width:120px;overflow:scrolloverflow-x:scroll" size="1"','All')."</td>"
                ));
            }else {

            }
            if($arrSettings['vis_active']!=0) {
                $this->_objTpl->setVariable(array(
                        'TXT_ENTRIES_VERTICAL'              =>  $this->_getCategoryname('5'),
                        'PARTNERS_VERTICAL'    		        =>  $this->_getListLevelMenu($recipientTitle,$titleName_vertical,'name="vertical" style="width:120px;overflow:scroll" size="1"','All')
                ));
            }else {
                $this->_objTpl->setVariable(array(
                        'ROW_DISPLAY'                       =>  "display:none"
                ));
            }

        }


        $this->_objTpl->setVariable(array(
                'TXT_ENTRIES_TITLE'                 =>  $_CORELANG['TXT_PARTNERS_ENTRY_MANAGE_TITLE'],
                'TXT_ENTRIES_SUBTITLE_DATE'         =>  $_ARRAYLANG['TXT_PARTNERS_ENTRY_MANAGE_DATE'],
                'TXT_ENTRIES_SUBTITLE_SUBJECT'      =>  $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_SUBJECT'],
                'TXT_ENTRIES_SUBTITLE_LANGUAGES'    =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_LANGUAGES'],
                'TXT_ENTRIES_SUBTITLE_HITS'         =>  $_ARRAYLANG['TXT_PARTNERS_ENTRY_MANAGE_HITS'],
                'TXT_ENTRIES_SUBTITLE_COMMENTS'     =>  $_ARRAYLANG['TXT_PARTNERS_ENTRY_MANAGE_COMMENTS'],
                'TXT_ENTRIES_SUBTITLE_VOTES'        =>  $_ARRAYLANG['TXT_PARTNERS_ENTRY_MANAGE_VOTE'],
                'TXT_ENTRIES_SUBTITLE_USER'         =>  $_CORELANG['TXT_USER'],
                'TXT_ENTRIES_SUBTITLE_EXCEL'        =>  $_ARRAYLANG['TXT_PARTNERS_CSV'],
                'TXT_ENTRIES_SUBTITLE_EDITED'       =>  $_ARRAYLANG['TXT_PARTNERS_ENTRY_MANAGE_UPDATED'],
                'TXT_ENTRIES_SUBTITLE_ACTIONS'      =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_ACTIONS'],
                'TXT_ENTRIES_DELETE_ENTRY_JS'       =>  $_ARRAYLANG['TXT_PARTNERS_ENTRY_DELETE_JS'],
                'TXT_ENTRIES_MARKED'                =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_MARKED'],
                'TXT_ENTRIES_SELECT_ALL'            =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_SELECT'],
                'TXT_ENTRIES_DESELECT_ALL'          =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_DESELECT'],
                'TXT_ENTRIES_SUBMIT_SELECT'         =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_ACTION'],
                'TXT_ENTRIES_SUBMIT_DELETE'         =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_MANAGE_SUBMIT_DELETE'],
                'TXT_ENTRIES_SUBTITLE_STATUS'       =>  $_ARRAYLANG['TXT_PARTNERS_STATUS'],
                'TXT_ENTRIES_SUBMIT_DELETE_JS'      =>  $_ARRAYLANG['TXT_PARTNERS_ENTRY_MANAGE_SUBMIT_DELETE_JS'],
                'TXT_ENTRIES_SUBJECT'               =>  $_ARRAYLANG['TXT_PARTNERS_SUBJECT'],
                'TXT_ENTRIES_SEARCH'                =>  $_ARRAYLANG['TXT_PARTNERS_SEARCH'],
                'TXT_SHOW'                          =>  $_ARRAYLANG['TXT_PARTNERS_SHOW'],
                'TXT_EXPORT'                        =>  $_ARRAYLANG['TXT_PARTNERS_EXPORT_MULTIPLE'],
                'TXT_PARTNERS_CSV_FILE_ALL'         =>  $_ARRAYLANG['TXT_PARTNERS_EXPORT_ALL'],
                'TXT_IMPORT'                        =>  $_ARRAYLANG['TXT_IMPORT_ENTRIES'],
                'TXT_PARTNERS_IMPORT'               =>  $_ARRAYLANG['TXT_PARTNERS_IMPORT'],
                'TXT_PARTNERS_LANGUAGES_EXPORT'     =>  $_ARRAYLANG['TXT_PARTNERS_LANGUAGES_EXPORT'],
                'IMPORT_PARTNERS_LIST'              =>  $this->showImport(),
                'IMPORT_PARTNERS_LIST_FIELDS'       =>  $this->showImportfields()
        ));

        $intSelectedCategory = (isset($_GET['catId'])) ? intval($_GET['catId']) : 0;
        $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

        $intLanguageCounter = 0;
        $arrEntries = $this->createEntryArray(0, $intPagingPosition, $this->getPagingLimit());
        $arrLanguagesName = array(0 => '', 1 => '', 2 => '');
        foreach ($arrRenameCategories as $intCategoryId => $arrLanguages) {
            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                $arrLanguagesName[$intCounter%3] .= '<input '.(($arrRenameCategories[$intCategoryId][$intLanguageId]['is_active'] == 1) ? 'checked="checked"' : '').' type="checkbox" name="frmRenameCategory_Languages[]" value="'.$intLanguageId.'" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';
                $this->_objTpl->setVariable(array(
                        'RE_NAME_LANGID'	=>	$intLanguageId,
                        'RE_NAME_LANG'		=>	$arrTranslations['long'].' ['.$arrTranslations['short'].']'
                ));
                $this->_objTpl->parse('reCategoryNameFields');
                ++$intCounter;
            }
            $this->_objTpl->setVariable(array(
                    'PARTNERS_OLD_NAME'		=>	$arrLanguages[$this->_intLanguageId]['name']

            ));
        }


        $arrLanguages = array(0 => '', 1 => '', 2 => '');
        foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
            $arrLanguages[$intLanguageCounter%3] .= '<option value="'.$intLanguageId.'">'.$arrTranslations['long']."[".$arrTranslations['short']."]".'</option>';
            ++$intLanguageCounter;
        }

        $this->_objTpl->setVariable(array(
                'EDIT_LANGUAGES_ALL'  =>   $_ARRAYLANG['EDIT_LANGUAGES_ALL'],
                'EDIT_LANGUAGES_1'    =>   $arrLanguages[0],
                'EDIT_LANGUAGES_2'    =>   $arrLanguages[1],
                'EDIT_LANGUAGES_3'    =>   $arrLanguages[2]
        ));

        if (count($arrEntries) > 0) {
            $intRowClass = 0;
            foreach ($arrEntries as $intEntryId => $arrEntryValues) {
                if ($intSelectedCategory > 0) {

                    //Filter for a specific category. If the category doesn't match: skip.
                    if (!$this->categoryMatches($intSelectedCategory, $arrEntryValues['categories'][$this->_intLanguageId])) {
                        continue;
                    }
                }
                $this->_objTpl->setVariable(array(
                        'TXT_IMGALT_EDIT'		=>	$_ARRAYLANG['TXT_BLOG_ENTRY_EDIT_TITLE'],
                        'TXT_IMGALT_DELETE'		=>	$_ARRAYLANG['TXT_BLOG_ENTRY_DELETE_TITLE']
                ));

                //Check active languages
                $strActiveLanguages = '';
                foreach ($arrEntryValues['translation'] as $intLangId => $arrEntryTranslations) {

                    $this->_objTpl->setVariable(array('ENTRY_STATUS' =>  $this->_getStatus($arrEntryTranslations['status'])));
                    if ($arrEntryTranslations['is_active'] && key_exists($intLangId,$this->_arrLanguages)) {
                        $strActiveLanguages .= '['.$this->_arrLanguages[$intLangId]['short'].']&nbsp;&nbsp;';
                    }
                }

                $strActiveLanguages = substr($strActiveLanguages,0,-12);

                $this->_objTpl->setVariable(array(
                        'ENTRY_ROWCLASS'		=>	($intRowClass % 2 == 0) ? 'row1' : 'row2',
                        'ENTRY_ID'				=>	$intEntryId,
                        'ENTRY_DATE'			=>	$arrEntryValues['time_created'],
                        'ENTRY_EDITED'			=>	$arrEntryValues['time_edited'],
                        'ENTRY_SUBJECT'         =>  $arrEntryValues['subject'],
                        'ENTRY_LEVEL'           =>  $arrEntryValues['level'],
                        'ENTRY_COUNTRY'         =>  $arrEntryValues['country'],
                        'ENTRY_VERTICAL'        =>  $arrEntryValues['vertical'],
                        'ENTRY_PROFILE'         =>  $arrEntryValues['profile'],
                        'ENTRY_CONTACTNAME'     =>  $arrEntryValues['contactname'],
                        'ENTRY_EMAIL'           =>  $arrEntryValues['email'],
                        'ENTRY_WEBSITE'         =>  $arrEntryValues['website'],
                        'ENTRY_ADDRESS1'        =>  $arrEntryValues['address1'],
                        'ENTRY_ADDRESS2'        =>  $arrEntryValues['address2'],
                        'ENTRY_CITY'            =>  $arrEntryValues['city'],
                        'ENTRY_ZIPCODE'         =>  $arrEntryValues['zipcode'],
                        'ENTRY_PHONE'           =>  $arrEntryValues['phone'],
                        'ENTRY_FAX'             =>  $arrEntryValues['fax'],
                        'ENTRY_REFERENCE'       =>  $arrEntryValues['reference'],
                        'ENTRY_LOGO_PATH'       =>  $arrEntryValues['image'],
                        'ENTRY_CONTENT'			=>	$arrEntryValues['content'],
                        'ENTRY_PAGING'          =>  $intPagingPosition,
                        'ENTRY_LANGUAGES'		=>	$strActiveLanguages,
                        'TXT_PARTNERS_CSV_FILE' =>  $_ARRAYLANG['TXT_PARTNERS_EXPORT'],
                        'ENTRY_USER'			=>	$arrEntryValues['user_name'],
                        'ENTRY_USERNO'          =>  $arrEntryValues['condition']
                ));
                $this->_objTpl->parse('showEntries');
                $intRowClass++;
            }
            // print $intRowClass;
            //	Show paging if needed

            //  echo "entries".$this->PaginactionCount;
            //  echo "Page Limit".$this->getPagingLimit();
            if ($this->PaginactionCount > $this->getPagingLimit()) {
                if(!empty($_REQUEST['subject']) || !empty($_REQUEST['level'])
                        || !empty($_REQUEST['profile']) || !empty($_REQUEST['country']) || !empty($_REQUEST['vertical'])) {

                    $searchSubject = contrexx_addslashes(strip_tags($_REQUEST['subject']));
                    $searchLevel =contrexx_addslashes(strip_tags($_REQUEST['level']));
                    $searchProfile =contrexx_addslashes(strip_tags($_REQUEST['profile']));
                    $searchCountry =contrexx_addslashes(strip_tags($_REQUEST['country']));
                    $searchVertical =contrexx_addslashes(strip_tags($_REQUEST['vertical']));
                    $searchText ="&amp;cmd=partners&subject=".$searchSubject."&level=".$searchLevel."&profile=".$searchProfile."&country=".$searchCountry."&vertical=".$searchVertical;
                    ;
                }
                else {
                    $searchText ="&amp;cmd=partners";
                }
                $strPaging = getPaging($this->PaginactionCount, $intPagingPosition,$searchText, '<strong>'.$_ARRAYLANG['TXT_PARTNERS_ENTRY_MANAGE_PAGING'].'</strong>', true, $this->getPagingLimit());
                // echo $strPaging;
                $this->_objTpl->setVariable('ENTRIES_PAGING', $strPaging);
            }
        }
        else {
            $this->_objTpl->setVariable('TXT_ENTRIES_NO_ENTRIES_FOUND',$_ARRAYLANG['TXT_ENTRIES_NO_ENTRIES_FOUND']);
            $this->_objTpl->parse('noEntries');
        }
    }



    /**
     * Shows the "Add Partners" page.
     *
     * @global	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     * @global 	object		$_objLanguage
     * @global 	array		$_CONFIG
     */
    function addPartners($errArray) {
        global $_CORELANG, $_ARRAYLANG, $_CONFIG;

        $recipientTitle = 0;
        $titleName_level = "level";
        $titleName_profile = "profile";
        $titleName_country = "country";
        $titleName_vertical = "vertical";
        $titleName_region = "regions";

        $this->_strPageTitle = $_CORELANG['TXT_PARTNERS_OVERVIEW_TITLE'];
        $this->_objTpl->loadTemplateFile('module_partners_create.html',true,true);

        $arrCategories = $this->createCategoryArray();
        $arrSettings   = $this->_getSettings();


        //Show language-selection
        if (count($this->_arrLanguages) > 0) {
            $intLanguageCounter = 0;
            $intLanguageCounterMultiple = 0;
            $intLanguageCounterExtend=0;
            $arrLanguages = array(0 => '', 1 => '', 2 => '');
            $arrLanguagesExtend = array(0 => '', 1 => '', 2 => '');
            $strJsTabToDiv = '';
            $lang_multiple = '';
            $strJsTabToDivExtend = '';
            $rowClass = "row2";

            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                if($intLanguageCounterMultiple >= 1) {
                    $lang_multiple .= $intLanguageId."-";
                }
                $intLanguageCounterMultiple++;
            }
            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                if($intLanguageCounter >= 1) {
                    $functionId = 1;
                }else {
                    //doNothing
                }

                $arrLanguages[$intLanguageCounter%3] .= '<input checked="checked" type="checkbox" name="frmEditEntry_Languages[]" id="EditEntry_languages_'.$intLanguageId.'" value="'.$intLanguageId.'" onclick="switchBoxAndTab(this, \'addEntry_'.$arrTranslations['long'].'\');" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';

                $strJsTabToDiv .= 'arrTabToDiv["addEntry_'.$arrTranslations['long'].'"] = "'.$arrTranslations['long'].'";'."\n";

                //Parse the TABS at the top of the language-selection
                $this->_objTpl->setVariable(array(
                        'TABS_LINK_ID'			=>	'addEntry_'.$arrTranslations['long'],
                        'TABS_DIV_ID'			=>	$arrTranslations['long'],
                        'TABS_CLASS'			=>	($intLanguageCounter == 0) ? 'active' : 'inactive',
                        'TABS_DISPLAY_STYLE'	=>	'display: inline;',
                        'PARTNERS_TITLE'        =>  $errArray["title"][$intLanguageId],
                        'TABS_NAME'				=>	$arrTranslations['long']

                ));
                $this->_objTpl->parse('showLanguageTabs');

                $this->_objTpl->setVariable(array(
                        'TXT_PARTNERS_MESSAGE' =>  $_ARRAYLANG['TXT_PARTNERS_ADD'],
                        'TXT_TITLE'            =>  $_ARRAYLANG['TXT_PARTNERS_TITLE'],
                        'TXT_CATEGORIES'       =>  $this->_getCategoryname('1'),
                        'TXT_LCATEGORY'        =>  $this->_getCategoryname('2'),
                        'TXT_PCATEGORY'        =>  $this->_getCategoryname('3'),
                        'TXT_CCATEGORY'        =>  $this->_getCategoryname('4'),
                        'TXT_VCATEGORY'        =>  $this->_getCategoryname('5'),
                        'TXT_REGION'           =>  $this->_getCategoryname('6'),
                        'TXT_DIV_LOGO'         =>  $_ARRAYLANG['TXT_PARTNERS_LOGO_TITLE'],
                        'TXT_CONTACTNAME'      =>  $_ARRAYLANG['TXT_PARTNERS_CONTACTNAME'],
                        'TXT_EMAIL'            =>  $_ARRAYLANG['TXT_PARTNERS_EMAIL'],
                        'TXT_WEBSITE'          =>  $_ARRAYLANG['TXT_PARTNERS_WEBSITE'],
                        'TXT_ADDRESS1'         =>  $_ARRAYLANG['TXT_PARTNERS_ADDRESS1'],
                        'TXT_ADDRESS2'         =>  $_ARRAYLANG['TXT_PARTNERS_ADDRESS2'],
                        'TXT_PHONE'            =>  $_ARRAYLANG['TXT_PARTNERS_PHONE'],
                        'TXT_CITY'             =>  $_ARRAYLANG['TXT_PARTNERS_CITY'],
                        'TXT_ZIPCODE'          =>  $_ARRAYLANG['TXT_PARTNERS_ZIPCODE'],
                        'TXT_FAX'              =>  $_ARRAYLANG['TXT_PARTNERS_FAX'],
                        'TXT_REFERENCE'        =>  $_ARRAYLANG['TXT_PARTNERS_REFERENCE'],
                        'TXT_QUOTE'            =>  $_ARRAYLANG['TXT_PARTNERS_QUOTE'],
                        'TXT_STATUS'           =>  $_ARRAYLANG['TXT_PARTNERS_STATUS'],
                        'TXT_STORE'            =>  $_CORELANG['TXT_SAVE'],
                        'TXT_EDIT_LANGUAGES'   =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_LANGUAGES'],
                        'TXT_DIV_IMAGE_BROWSE' =>  $_ARRAYLANG['TXT_PARTNERS_BROWSE']
                ));


                //Filter out active categories for this language
                $intCategoriesCounter = 0;
                $arrCategoriesContent = array(0 => '', 1 => '', 2 => '');
                $chkArray = array();

                foreach ($arrCategories as $intCategoryId => $arrCategoryValues) {

                    if ($arrCategoryValues[$intLanguageId]['is_active']) {
                        foreach ($errArray["category"][$intLanguageId] as $intKeyId => $arrId) {
                            $chkArray[$arrId] = true;
                        }

                        $arrCategoriesContent[$intCategoriesCounter%3] .= '<input type="checkbox" name="frmEditEntry_Categories_'.$intLanguageId.'[]" id="frmEditEntry_Categories_'.$intLanguageId.'[]" onClick=chgcheckAllLang'.$functionId.'("frmEditEntry_Categories_",this.value,"'.$lang_multiple.'",this.checked); value="'.$intCategoryId.'" '.(key_exists($intCategoryId,$chkArray) ? 'checked="checked"' : '').' />'.$arrCategoryValues[$intLanguageId]['name'].'<br />';
                        ++$intCategoriesCounter;

                    }
                }
                foreach($arrSettings as $setKey => $setValue) {
                    if($arrSettings['lis_active']!=0) {
                        //do Nothing
                        // print $rowClass;
                    }else {

                        $this->_objTpl->setVariable(array(
                                'PARTNERS_DISPLAY_LEVEL' => 'display:none'
                        ));
                    }
                    if($arrSettings['pis_active']!=0) {
                        //do Nothing
                    }else {
                        $this->_objTpl->setVariable(array(
                                'PARTNERS_DISPLAY_PROFILE' => 'display:none',
                        ));
                    }
                    if($arrSettings['cis_active']!=0) {
                        //do Nothing
                    }else {
                        $this->_objTpl->setVariable(array(
                                'PARTNERS_DISPLAY_COUNTRY' => 'display:none'
                        ));
                    }
                    if($arrSettings['vis_active']!=0) {
                        //do Nothing
                    }else {
                        $this->_objTpl->setVariable(array(
                                'PARTNERS_DISPLAY_VERTICAL' => 'display:none'
                        ));
                    }

                }

                if(($arrSettings['lis_active']==0) && ($arrSettings['pis_active']==0) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==0)) {

                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row2',
                            'PARTNERS_CLASS_LEVEL1' => 'row2',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row2',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row2',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));

                }
                if(($arrSettings['lis_active']==0) && ($arrSettings['pis_active']==0) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==1)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row2',
                            'PARTNERS_CLASS_LEVEL1' => 'row2',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row2',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row2',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));

                }
                if(($arrSettings['lis_active']==0) && ($arrSettings['pis_active']==0) && ($arrSettings['cis_active']==1)&& ($arrSettings['cis_active']==0)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row2',
                            'PARTNERS_CLASS_LEVEL1' => 'row2',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row2',
                            'PARTNERS_CLASS_REGION' => 'row1',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));
                }
                if(($arrSettings['lis_active']==0) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==0)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row1',
                            'PARTNERS_CLASS_LEVEL1' => 'row1',
                            'PARTNERS_CLASS_PROFILE' => 'row1',
                            'PARTNERS_CLASS_COUNTRY' => 'row2',
                            'PARTNERS_CLASS_REGION' => 'row1',
                            'PARTNERS_CLASS_VERTICAL' => 'row2',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row1',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row2',
                            'PARTNERS_CLASS_ONE' => 'row2',
                            'PARTNERS_CLASS_TWO' => 'row1'
                    ));
                }
                if(($arrSettings['lis_active']==0) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==0)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row2',
                            'PARTNERS_CLASS_LEVEL1' => 'row2',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));
                }
                if(($arrSettings['lis_active']==0) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==1)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row2',
                            'PARTNERS_CLASS_LEVEL1' => 'row2',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row2',
                            'PARTNERS_CLASS_TWO' => 'row1'
                    ));
                }
                if(($arrSettings['lis_active']==0) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==1)&& ($arrSettings['cis_active']==0)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row2',
                            'PARTNERS_CLASS_LEVEL1' => 'row2',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));
                }
                if(($arrSettings['lis_active']==0) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==1)&& ($arrSettings['cis_active']==1)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row2',
                            'PARTNERS_CLASS_LEVEL1' => 'row2',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row2',
                            'PARTNERS_CLASS_TWO' => 'row1'
                    ));
                }
                if(($arrSettings['lis_active']==1) && ($arrSettings['pis_active']==0) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==0)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row1',
                            'PARTNERS_CLASS_LEVEL1' => 'row1',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));
                }
                if(($arrSettings['lis_active']==1) && ($arrSettings['pis_active']==0) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==1)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row1',
                            'PARTNERS_CLASS_LEVEL1' => 'row1',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row2',
                            'PARTNERS_CLASS_TWO' => 'row1'
                    ));
                }
                if(($arrSettings['lis_active']==1) && ($arrSettings['pis_active']==0) && ($arrSettings['cis_active']==1)&& ($arrSettings['cis_active']==0)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row1',
                            'PARTNERS_CLASS_LEVEL1' => 'row1',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));
                }
                if(($arrSettings['lis_active']==1) && ($arrSettings['pis_active']==0) && ($arrSettings['cis_active']==1)&& ($arrSettings['cis_active']==1)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row1',
                            'PARTNERS_CLASS_LEVEL1' => 'row1',
                            'PARTNERS_CLASS_PROFILE' => 'row2',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row2',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));
                }
                if(($arrSettings['lis_active']==1) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==0)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row1',
                            'PARTNERS_CLASS_LEVEL1' => 'row1',
                            'PARTNERS_CLASS_PROFILE' => 'row1',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row1',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row2',
                            'PARTNERS_CLASS_TWO' => 'row1'
                    ));
                }
                if(($arrSettings['lis_active']==1) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==0)&& ($arrSettings['cis_active']==1)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row1',
                            'PARTNERS_CLASS_LEVEL1' => 'row1',
                            'PARTNERS_CLASS_PROFILE' => 'row1',
                            'PARTNERS_CLASS_COUNTRY' => 'row1',
                            'PARTNERS_CLASS_REGION' => 'row2',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row1',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));
                }
                if(($arrSettings['lis_active']==1) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==1)&& ($arrSettings['cis_active']==0)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row1',
                            'PARTNERS_CLASS_LEVEL1' => 'row1',
                            'PARTNERS_CLASS_PROFILE' => 'row1',
                            'PARTNERS_CLASS_COUNTRY' => 'row2',
                            'PARTNERS_CLASS_REGION' => 'row1',
                            'PARTNERS_CLASS_VERTICAL' => 'row1',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row1',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row1',
                            'PARTNERS_CLASS_ONE' => 'row2',
                            'PARTNERS_CLASS_TWO' => 'row1'
                    ));
                }
                if(($arrSettings['lis_active']==1) && ($arrSettings['pis_active']==1) && ($arrSettings['cis_active']==1)&& ($arrSettings['cis_active']==1)) {
                    $this->_objTpl->setVariable(array(
                            'PARTNERS_CLASS_LEVEL' => 'row2',
                            'PARTNERS_CLASS_LEVEL1' => 'row2',
                            'PARTNERS_CLASS_PROFILE' => 'row1',
                            'PARTNERS_CLASS_COUNTRY' => 'row2',
                            'PARTNERS_CLASS_REGION' => 'row1',
                            'PARTNERS_CLASS_VERTICAL' => 'row2',
                            'PARTNERS_CLASS_PROFILE_ONE' => 'row1',
                            'PARTNERS_CLASS_VERTICAL_ONE' => 'row2',
                            'PARTNERS_CLASS_ONE' => 'row1',
                            'PARTNERS_CLASS_TWO' => 'row2'
                    ));
                }


                $this->_objTpl->setVariable(array(
                        'DIV_ID'			=>	$arrTranslations['long'],
                        'DIV_LANGUAGE_ID'	=>	$intLanguageId,
                        'DIV_DISPLAY_STYLE'	=>	($intLanguageCounter == 0) ? 'display: block;' : 'display: none;',
                        'DIV_TITLE'			=>	$arrTranslations['long'],
                        'DIV_CATEGORIES_1'	=>	$arrCategoriesContent[0],
                        'DIV_CATEGORIES_2'	=>	$arrCategoriesContent[1],
                        'DIV_CATEGORIES_3'	=>	$arrCategoriesContent[2],
                        'FUNCTION_ID'       =>  $functionId,
                        'PARTNERS_IS_ACTIVE'    => $this->_checkStatus($errArray["active"][$intLanguageId],'name="active_'.$intLanguageId.'[]"','id="active_'.$intLanguageId.'[]"','onClick=chgRadioInAllLang'.$functionId.'("active_",this.value,"'.$lang_multiple.'",this.checked);'),
                        'PARTNERS_LEVEL'        => $this->_getListLevelMenu($errArray["level"][$intLanguageId],$titleName_level,'name="level_'.$intLanguageId.'[]" multiple size="10" style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('2')),
                        'PARTNERS_PROFILE'      => $this->_getListLevelMenu($errArray["profile"][$intLanguageId],$titleName_profile,'name="profile_'.$intLanguageId.'[]" multiple size="10" style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('3')),
                        'PARTNERS_COUNTRY'      => $this->_getListLevelMenu($errArray["country"][$intLanguageId],$titleName_country,'name="country_'.$intLanguageId.'[]"  id="country_'.$intLanguageId.'"  style="width:150px;" onchange=PopulateRegions'.$functionId.'(0,this.value,'.$intLanguageId.',"'.$lang_multiple.'","country_");','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('4')),
                        'PARTNERS_COUNTRY1'      => $this->_getListLevelMenu($errArray["country"][$intLanguageId],$titleName_country,'name="country1_'.$intLanguageId.'[]"  id="country1_'.$intLanguageId.'"  style="width:150px;" onchange=PopulateRegions'.$functionId.'(1,this.value,'.$intLanguageId.',"'.$lang_multiple.'","country1_");','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('4')),
                        'PARTNERS_COUNTRY2'      => $this->_getListLevelMenu($errArray["country"][$intLanguageId],$titleName_country,'name="country2_'.$intLanguageId.'[]"  id="country2_'.$intLanguageId.'"  style="width:150px;" onchange=PopulateRegions'.$functionId.'(2,this.value,'.$intLanguageId.',"'.$lang_multiple.'","country2_");','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('4')),
                        'PARTNERS_VERTICAL'     => $this->_getListLevelMenu($errArray["vertical"][$intLanguageId],$titleName_vertical,'name="vertical_'.$intLanguageId.'[]" multiple size="10" style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('5')),
                        'TXT_ADD_EXTENDED'		=> $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_EXTENDED'],
                        'PARTNERS_CONTACTNAME'  => $errArray["contactname"][$intLanguageId],
                        'PARTNERS_EMAIL'        => $errArray["email"][$intLanguageId],
                        'PARTNERS_WEBSITE'      => $errArray["website"][$intLanguageId],
                        'PASS_LANG_MULTIPLE'    => $lang_multiple,
                        'PARTNERS_TEXT'         => get_wysiwyg_editor('partnersText_'.$intLanguageId)
                ));

                $pass_java_language .= $intLanguageId."-";
                $this->_objTpl->parse('showLanguageDivs');
                ++$intLanguageCounter;
            }


            $this->_objTpl->setVariable(array(
                    'PARTNERS_FORM_ACTION'  => $_ARRAYLANG['PARTNERS_FORM_ACTION'],
                    'EDIT_POST_ACTION'		=>	'?cmd=partners&amp;act=insertPartners',
                    'PASS_JAVA_LANGUAGE'    =>  $pass_java_language,
                    'EDIT_MESSAGE_ID'		=>	0,
                    'EDIT_LANGUAGES_1'		=>	$arrLanguages[0],
                    'EDIT_LANGUAGES_2'		=>	$arrLanguages[1],
                    'EDIT_LANGUAGES_3'		=>	$arrLanguages[2],
                    'EDIT_JS_TAB_TO_DIV'	=>	$strJsTabToDiv
            ));

        }
    }


    /**

     * Insert the new partners into the database
     * @global	object		$objDatabase
     * @global 	array		$_ARRAYLANG
     */

    function insertPartners() {
        global $_ARRAYLANG, $objDatabase;

        if (isset($_POST['frmEditEntry_Languages']) && is_array($_POST['frmEditEntry_Languages'])) {
            $maincount = 0;
            $count = 0;
            foreach ($_POST['frmEditEntry_Languages'] as $intKey => $intLanguageId) {
                $arrActiveLanguages[$intLanguageId] = true;
            }
            $errArray = array();
            $catArray = array();
            foreach ($_POST as $intKey => $intLanguageId) {
                if (substr($intKey,0,strlen('partnersTitle_')) == 'partnersTitle_') {
                    $intLanguageId = intval(substr($intKey,strlen('partnersTitle_')));
                    if($errorMessage != true) {
                        if(!array_key_exists($intLanguageId,$arrActiveLanguages)) {
                            $count++;
                        }
                        else if(trim($_POST['email_'.$intLanguageId])=="") {
                            $errArray["title"][$intLanguageId]     = trim($_POST['partnersTitle_'.$intLanguageId]);
                            $errArray["active"][$intLanguageId]    = trim($_POST['active_'.$intLanguageId]);
                            $errArray["level"][$intLanguageId]     = trim($_POST['level_'.$intLanguageId]);
                            $errArray["profile"][$intLanguageId]   = trim($_POST['profile_'.$intLanguageId]);
                            $errArray["country"][$intLanguageId]   = trim($_POST['country_'.$intLanguageId]);
                            $errArray["region"][$intLanguageId]    = trim($_POST['region_'.$intLanguageId]);
                            $errArray["vertical"][$intLanguageId]  = trim($_POST['vertical_'.$intLanguageId]);
                            $errArray["contactname"][$intLanguageId]  = trim($_POST['contactname_'.$intLanguageId]);
                            $errArray["category"][$intLanguageId]  = (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array();
                            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_EMAIL'];
                            $errorMessage = true;
                        }
                        else if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",trim($_POST['email_'.$intLanguageId]))) {
                            $errArray["title"][$intLanguageId]        = trim($_POST['partnersTitle_'.$intLanguageId]);
                            $errArray["active"][$intLanguageId]       = trim($_POST['active_'.$intLanguageId]);
                            $errArray["level"][$intLanguageId]        = trim($_POST['level_'.$intLanguageId]);
                            $errArray["profile"][$intLanguageId]      = trim($_POST['profile_'.$intLanguageId]);
                            $errArray["country"][$intLanguageId]      = trim($_POST['country_'.$intLanguageId]);
                            $errArray["region"][$intLanguageId]       = trim($_POST['region_'.$intLanguageId]);
                            $errArray["vertical"][$intLanguageId]     = trim($_POST['vertical_'.$intLanguageId]);
                            $errArray["contactname"][$intLanguageId]  = trim($_POST['contactname_'.$intLanguageId]);
                            $errArray["email"][$intLanguageId]        = trim($_POST['email_'.$intLanguageId]);
                            $errArray["category"][$intLanguageId]  = (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array();
                            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_EMAIL_VALID'];
                            $errorMessage = true;
                        }
                        else if(trim($_POST['website_'.$intLanguageId])=="") {
                            $errArray["title"][$intLanguageId]        = trim($_POST['partnersTitle_'.$intLanguageId]);
                            $errArray["active"][$intLanguageId]       = trim($_POST['active_'.$intLanguageId]);
                            $errArray["level"][$intLanguageId]        = trim($_POST['level_'.$intLanguageId]);
                            $errArray["profile"][$intLanguageId]      = trim($_POST['profile_'.$intLanguageId]);
                            $errArray["country"][$intLanguageId]      = trim($_POST['country_'.$intLanguageId]);
                            $errArray["region"][$intLanguageId]       = trim($_POST['region_'.$intLanguageId]);
                            $errArray["vertical"][$intLanguageId]     = trim($_POST['vertical_'.$intLanguageId]);
                            $errArray["contactname"][$intLanguageId]  = trim($_POST['contactname_'.$intLanguageId]);
                            $errArray["email"][$intLanguageId]        = trim($_POST['email_'.$intLanguageId]);
                            $errArray["category"][$intLanguageId]  = (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array();
                            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_WEBSITE'];
                            $errorMessage = true;
                        }
                        else if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', trim($_POST['website_'.$intLanguageId]))) {
                            $errArray["title"][$intLanguageId]        = trim($_POST['partnersTitle_'.$intLanguageId]);
                            $errArray["active"][$intLanguageId]       = trim($_POST['active_'.$intLanguageId]);
                            $errArray["level"][$intLanguageId]        = trim($_POST['level_'.$intLanguageId]);
                            $errArray["profile"][$intLanguageId]      = trim($_POST['profile_'.$intLanguageId]);
                            $errArray["country"][$intLanguageId]      = trim($_POST['country_'.$intLanguageId]);
                            $errArray["region"][$intLanguageId]       = trim($_POST['region_'.$intLanguageId]);
                            $errArray["vertical"][$intLanguageId]     = trim($_POST['vertical_'.$intLanguageId]);
                            $errArray["contactname"][$intLanguageId]  = trim($_POST['contactname_'.$intLanguageId]);
                            $errArray["email"][$intLanguageId]        = trim($_POST['email_'.$intLanguageId]);
                            $errArray["website"][$intLanguageId]      = trim($_POST['website_'.$intLanguageId]);
                            $errArray["category"][$intLanguageId]  = (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array();
                            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_WEBSITE_VALID'].$_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_WEBSITE_EXAMPLE'];
                            $errorMessage = true;
                        }
                        else {
                            $count++;
                        }

                        $maincount++;
                    }
                }
            }
            if($count == $maincount) {
                $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_create
    								SET `user_id` = '.$this->_intCurrentUserId.',
    									`time_created` = '.time().',
    									`time_edited` = '.time().',
    									`hits` = 0
    							');
                $intMessageId = $objDatabase->insert_id();
                $this->insertEntryData($intMessageId,$intLanguageId);
                $this->showOverview();
                $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_SUCCESSFULL'];
            }
            else {
                $this->addPartners($errArray);
            }
        } else {
            $this->showOverview();
            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_LANGUAGES'];
        }
    }

    /**
     * This function is used by the "insertEntry()" and "updateEntry()" function. It collects all values from
     * $_POST and creates the new entries in the database. This function was extracted from original source to be as
     * DRY/SPOT as possible.
     *
     * @global 	object		$objDatabase
     * @param	integer		$intMessageId: This is the id of the message which the new values will be linked to.
     */
    function insertEntryData($intMessageId) {
        global $objDatabase;

        $intMessageId = intval($intMessageId);

        foreach ($_POST['frmEditEntry_Languages'] as $intKey => $intLanguageId) {
            $arrActiveLanguages[$intLanguageId] = true;
        }
        //Collect data for every language
        $arrValues = array();
        $activeValues = '1';
        foreach ($_POST as $strKey => $strValue) {

            if (substr($strKey,0,strlen('partnersTitle_')) == 'partnersTitle_') {
                $intLanguageId = intval(substr($strKey,strlen('partnersTitle_')));

                $arrValues[$intLanguageId] = array(	'subject' 		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['partnersTitle_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'level'		    => contrexx_addslashes(strip_tags(trim(htmlentities($_POST['level_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'profile'		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['profile_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'country'		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['country_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'region'		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['region_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'vertical'		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['vertical_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'contactname'   => contrexx_addslashes(strip_tags(trim(htmlentities($_POST['contactname_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'email'	    	=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['email_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'website'		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['website_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'address1'		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['address1_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'address2'		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['address2_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'city'	    	=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['city_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'zipcode'		=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['zipcode_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'phone'		    => contrexx_addslashes(strip_tags(trim(htmlentities($_POST['phone_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'fax'	    	=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['fax_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'reference'	  	=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['reference_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'quote'	  	    => contrexx_addslashes(strip_tags(trim(htmlentities($_POST['quote_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'content'		=> contrexx_addslashes($_POST['partnersText_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET),
                        'is_active'     => (array_key_exists($intLanguageId,$arrActiveLanguages) ? '1' : '0'),
                        'status'		=> $_POST['active_'.$intLanguageId][0],
                        'categories'	=> (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array(),
                        'image'			=> contrexx_addslashes(strip_tags(trim(htmlentities($_POST['frmEditEntry_Image_'.$intLanguageId],ENT_QUOTES,CONTREXX_CHARSET)))),
                        'country'       => (isset($_POST['country_'.$intLanguageId])) ? $_POST['country_'.$intLanguageId] : array(),
                        'country1'      => (isset($_POST['country1_'.$intLanguageId])) ? $_POST['country1_'.$intLanguageId] : array(),
                        'country2'      => (isset($_POST['country2_'.$intLanguageId])) ? $_POST['country2_'.$intLanguageId] : array(),
                        'level_multi'   => (isset($_POST['assignedLevel_'.$intLanguageId])) ? $_POST['assignedLevel_'.$intLanguageId] : array(),
                        'profile_multi' => (isset($_POST['assignedProfile_'.$intLanguageId])) ? $_POST['assignedProfile_'.$intLanguageId] : array(),
                        'vertical_multi'=> (isset($_POST['assignedVertical_'.$intLanguageId])) ? $_POST['assignedVertical_'.$intLanguageId] : array(),
                        'region_multi_0'=> (isset($_POST['region_0_'.$intLanguageId])) ? $_POST['region_0_'.$intLanguageId] : array(),
                        'region_multi_1'=> (isset($_POST['region_1_'.$intLanguageId])) ? $_POST['region_1_'.$intLanguageId] : array(),
                        'region_multi_2'=> (isset($_POST['region_2_'.$intLanguageId])) ? $_POST['region_2_'.$intLanguageId] : array()
                );
                //print_r($_POST['active_'.$intLanguageId]);
            }
        }

        //Insert collected data
        foreach ($arrValues as $intLanguageId => $arrEntryValues) {

            $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_create_lang
									SET	`message_id` = '.$intMessageId.',
										`lang_id`    = '.$intLanguageId.',
										`is_active`  = "'.$arrEntryValues['is_active'].'",
										`status`     = "'.$arrEntryValues['status'].'",
										`subject`    = "'.$arrEntryValues['subject'].'",
										`level`      = "'.$arrEntryValues['level'].'",
										`profile`    = "'.$arrEntryValues['profile'].'",
										`country`    = "'.$arrEntryValues['country'].'",
										`region`     = "'.$arrEntryValues['region'].'",
										`vertical`   = "'.$arrEntryValues['vertical'].'",
										`contactname`= "'.$arrEntryValues['contactname'].'",
										`email`      = "'.$arrEntryValues['email'].'",
										`website`    = "'.$arrEntryValues['website'].'",
										`address1`   = "'.$arrEntryValues['address1'].'",
										`address2`   = "'.$arrEntryValues['address2'].'",
										`city`       = "'.$arrEntryValues['city'].'",
										`zipcode`    = "'.$arrEntryValues['zipcode'].'",
										`phone`      = "'.$arrEntryValues['phone'].'",
										`fax`        = "'.$arrEntryValues['fax'].'",
										`reference`  = "'.$arrEntryValues['reference'].'",
										`quote`      = "'.$arrEntryValues['quote'].'",
										`content`    = "'.$arrEntryValues['content'].'",
          								`image`      = "'.$arrEntryValues['image'].'"
								');

            //Assign message to categories
            if ((is_array($arrEntryValues['categories'])) && (count($arrEntryValues['profile_multi'])!=0)) {
                foreach ($arrEntryValues['categories'] as $intKey => $intCategoryId) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_category
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intCategoryId.',
												`lang_id`     = '.$intLanguageId.'
										');
                }
            } else {
                $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_category
											SET `message_id`  = '.$intMessageId.',
												`category_id` = 0,
												`lang_id`     = '.$intLanguageId.'
										');
            }

            if (is_array($arrEntryValues['country'])) {
                $position = 1;
                foreach ($arrEntryValues['country'] as $intKey => $intCountryId) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_country
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intCountryId.',
												`lang_id`     = '.$intLanguageId.',
												`pos_id`      = '.$position.'
										');
                }
            }
            if (is_array($arrEntryValues['country1'])) {
                $position = 2;
                foreach ($arrEntryValues['country1'] as $intKey => $intCountryId1) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_country
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intCountryId1.',
												`lang_id`     = '.$intLanguageId.',
												`pos_id`      = '.$position.'
										');
                }
            }
            if (is_array($arrEntryValues['country2'])) {
                $position = 3;
                foreach ($arrEntryValues['country2'] as $intKey => $intCountryId2) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_country
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intCountryId2.',
												`lang_id`     = '.$intLanguageId.',
												`pos_id`      = '.$position.'
										');
                }
            }
            if((count($arrEntryValues['country'])==0) && (count($arrEntryValues['country1'])==0) && (count($arrEntryValues['country2'])==0)) {
                $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_country
											SET `message_id`  = '.$intMessageId.',
												`category_id` = 0,
												`lang_id`     = '.$intLanguageId.',
												`pos_id`      = 1
										');
            }
            if ((is_array($arrEntryValues['level_multi'])) && (count($arrEntryValues['level_multi'])!=0)) {
                foreach ($arrEntryValues['level_multi'] as $intKey => $intLevelId) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_level
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intLevelId.',
												`lang_id`     = '.$intLanguageId.'
										');
                }
            }else {

                $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_level
											SET `message_id`  = '.$intMessageId.',
												`category_id` = 0,
												`lang_id`     = '.$intLanguageId.'
										');

            }
            if ((is_array($arrEntryValues['profile_multi'])) && (count($arrEntryValues['profile_multi'])!=0)) {
                foreach ($arrEntryValues['profile_multi'] as $intKey => $intProfileId) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_profile
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intProfileId.',
												`lang_id`     = '.$intLanguageId.'
										');
                }
            } else {
                $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_profile
											SET `message_id`  = '.$intMessageId.',
												`category_id` = 0,
												`lang_id`     = '.$intLanguageId.'
										');

            }
            if ((is_array($arrEntryValues['vertical_multi'])) && (count($arrEntryValues['vertical_multi'])!=0)) {
                foreach ($arrEntryValues['vertical_multi'] as $intKey => $intVerticalId) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_vertical
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intVerticalId.',
												`lang_id`     = '.$intLanguageId.'
										');
                }
            } else {
                $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_vertical
											SET `message_id`  = '.$intMessageId.',
												`category_id` = 0,
												`lang_id`     = '.$intLanguageId.'
										');
            }
            if (is_array($arrEntryValues['region_multi_0'])) {
                foreach ($arrEntryValues['region_multi_0'] as $intKey => $intRegionId_0) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_region
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intRegionId_0.',
												`lang_id`     = '.$intLanguageId.'
										');
                }
            }
            if (is_array($arrEntryValues['region_multi_1'])) {
                foreach ($arrEntryValues['region_multi_1'] as $intKey => $intRegionId_1) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_region
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intRegionId_1.',
												`lang_id`     = '.$intLanguageId.'
										');
                }
            }
            if (is_array($arrEntryValues['region_multi_2'])) {
                foreach ($arrEntryValues['region_multi_2'] as $intKey => $intRegionId_2) {
                    $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_region
											SET `message_id`  = '.$intMessageId.',
												`category_id` = '.$intRegionId_2.',
												`lang_id`     = '.$intLanguageId.'
										');
                }
            }

            if((count($arrEntryValues['region_multi_0'])==0) && (count($arrEntryValues['region_multi_1'])==0) && (count($arrEntryValues['region_multi_2'])==0)) {
                $objDatabase->Execute('	INSERT INTO '.DBPREFIX.'module_partners_message_to_region
											SET `message_id`  = '.$intMessageId.',
												`category_id` = 0,
												`lang_id`     = '.$intLanguageId.'

										');
            }
        }
        return true;
    }




    /**
     * Shows the "Edit Entry" page.
     *
     * @global	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     * @global 	array		$_CONFIG
     * @param 	integer		$intEntryId: The values of this entry will be loaded into the form.
     */
    function editPartners($intEntryId) {
        global $_CORELANG, $_ARRAYLANG, $_CONFIG;

        $recipientTitle = 0;
        $titleName_level = "level";
        $titleName_profile = "profile";
        $titleName_country = "country";
        $titleName_region  = "regions";
        $titleName_vertical = "vertical";

        $this->_strPageTitle = $_ARRAYLANG['TXT_PARTNERS_ENTRY_EDIT_TITLE'];
        $this->_objTpl->loadTemplateFile('module_partners_create.html',true,true);
        $intEntryId = intval($intEntryId);
        $intPagingPosition = (isset($_GET['pos'])) ? intval($_GET['pos']) : 0;

        $arrCategories = $this->createCategoryArray();
        $arrSettings = $this->_getSettings();
        $arrEntries = $this->createEntryArray(0, $intPagingPosition, $this->getPagingLimit());

        if ($intEntryId > 0 && key_exists($intEntryId,$arrEntries)) {
            if (count($this->_arrLanguages) > 0) {
                $intLanguageCounter = 0;
                $boolFirstLanguage = true;
                $arrLanguages = array(0 => '', 1 => '', 2 => '');
                $strJsTabToDiv = '';
                $intRegLang=0;
                $intLanguageCounterMultiple=0;
                $lang_multiple;
                foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                    if($intLanguageCounterMultiple >= 1) {
                        $lang_multiple .= $intLanguageId."-";
                    }
                    $intLanguageCounterMultiple++;
                }
                foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                    if($intLanguageCounter >= 1) {
                        $functionId = 1;
                    }else {
                        //doNothing
                    }

                    $boolLanguageIsActive = $arrEntries[$intEntryId]['translation'][$intLanguageId]['is_active'];
                    $arrLanguages[$intLanguageCounter%3] .= '<input '.(($boolLanguageIsActive) ? 'checked="checked"' : '').' type="checkbox" name="frmEditEntry_Languages[]" id="EditEntry_languages_'.$intLanguageId.'" value="'.$intLanguageId.'" onclick="switchBoxAndTab(this, \'addEntry_'.$arrTranslations['long'].'\');" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';
                    $strJsTabToDiv .= 'arrTabToDiv["addEntry_'.$arrTranslations['long'].'"] = "'.$arrTranslations['long'].'";'."\n";

                    //Parse the TABS at the top of the language-selection
                    $this->_objTpl->setVariable(array(
                            'TABS_LINK_ID'			=>	'addEntry_'.$arrTranslations['long'],
                            'TABS_DIV_ID'			=>	$arrTranslations['long'],
                            'TABS_CLASS'			=>	($boolFirstLanguage && $boolLanguageIsActive) ? 'active' : 'inactive',
                            'TABS_DISPLAY_STYLE'	=>	($boolLanguageIsActive) ? 'display: inline;' : 'display: none;',
                            'TABS_NAME'				=>	$arrTranslations['long']

                    ));
                    $this->_objTpl->parse('showLanguageTabs');

                    //Parse the DIVS for every language
                    $this->_objTpl->setVariable(array(
                            'TXT_PARTNERS_MESSAGE' =>  $_ARRAYLANG['TXT_PARTNERS_ADD'],
                            'TXT_TITLE'            =>  $_ARRAYLANG['TXT_PARTNERS_TITLE'],
                            'TXT_CATEGORIES'       =>  $this->_getCategoryname('1'),
                            'TXT_LCATEGORY'        =>  $this->_getCategoryname('2'),
                            'TXT_PCATEGORY'        =>  $this->_getCategoryname('3'),
                            'TXT_CCATEGORY'        =>  $this->_getCategoryname('4'),
                            'TXT_VCATEGORY'        =>  $this->_getCategoryname('5'),
                            'TXT_REGION'           =>  $this->_getCategoryname('6'),
                            'TXT_DIV_LOGO'         =>  $_ARRAYLANG['TXT_PARTNERS_LOGO_TITLE'],
                            'TXT_CONTACTNAME'      =>  $_ARRAYLANG['TXT_PARTNERS_CONTACTNAME'],
                            'TXT_EMAIL'            =>  $_ARRAYLANG['TXT_PARTNERS_EMAIL'],
                            'TXT_WEBSITE'          =>  $_ARRAYLANG['TXT_PARTNERS_WEBSITE'],
                            'TXT_ADDRESS1'         =>  $_ARRAYLANG['TXT_PARTNERS_ADDRESS1'],
                            'TXT_ADDRESS2'         =>  $_ARRAYLANG['TXT_PARTNERS_ADDRESS2'],
                            'TXT_PHONE'            =>  $_ARRAYLANG['TXT_PARTNERS_PHONE'],
                            'TXT_CITY'             =>  $_ARRAYLANG['TXT_PARTNERS_CITY'],
                            'TXT_ZIPCODE'          =>  $_ARRAYLANG['TXT_PARTNERS_ZIPCODE'],
                            'TXT_FAX'              =>  $_ARRAYLANG['TXT_PARTNERS_FAX'],
                            'TXT_REFERENCE'        =>  $_ARRAYLANG['TXT_PARTNERS_REFERENCE'],
                            'TXT_QUOTE'            =>  $_ARRAYLANG['TXT_PARTNERS_QUOTE'],
                            'TXT_STATUS'           =>  $_ARRAYLANG['TXT_PARTNERS_STATUS'],
                            'TXT_STORE'            =>  $_CORELANG['TXT_SAVE'],
                            'TXT_EDIT_LANGUAGES'   =>  $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_LANGUAGES'],
                            'TXT_DIV_IMAGE_BROWSE' =>  $_ARRAYLANG['TXT_PARTNERS_BROWSE']
                    ));



                    //Filter out active categories for this language

                    $intCategoriesCounter = 0;
                    $arrCategoriesContent = array(0 => '', 1 => '', 2 => '');
                    foreach ($arrCategories as $intCategoryId => $arrCategoryValues) {
                        if ($arrCategoryValues[$intLanguageId]['is_active']) {
                            $arrCategoriesContent[$intCategoriesCounter%3] .= '<input type="checkbox" name="frmEditEntry_Categories_'.$intLanguageId.'[]" value="'.$intCategoryId.'" '.(key_exists($intCategoryId, $arrEntries[$intEntryId]['categories'][$intLanguageId]) ? 'checked="checked"' : '').' />'.$arrCategoryValues[$intLanguageId]['name'].'<br />';
                            ++$intCategoriesCounter;
                        }


                        foreach($arrSettings as $setKey => $setValue) {
                            if($arrSettings['lis_active']!=0) {
                                //do Nothing
                                // print $rowClass;
                            }else {

                                $this->_objTpl->setVariable(array(
                                        'PARTNERS_DISPLAY_LEVEL' => 'display:none'
                                ));
                            }
                            if($arrSettings['pis_active']!=0) {
                                //do Nothing
                            }else {
                                $this->_objTpl->setVariable(array(
                                        'PARTNERS_DISPLAY_PROFILE' => 'display:none'
                                ));
                            }
                            if($arrSettings['cis_active']!=0) {
                                //do Nothing
                            }else {
                                $this->_objTpl->setVariable(array(
                                        'PARTNERS_DISPLAY_COUNTRY' => 'display:none'
                                ));
                            }
                            if($arrSettings['vis_active']!=0) {
                                //do Nothing
                            }else {
                                $this->_objTpl->setVariable(array(
                                        'PARTNERS_DISPLAY_VERTICAL' => 'display:none'
                                ));
                            }

                        }


                    }
                    $this->_objTpl->setVariable(array(
                            'DIV_ID'			    =>	$arrTranslations['long'],
                            'DIV_LANGUAGE_ID'	    =>	$intLanguageId,
                            'DIV_DISPLAY_STYLE' 	=>  ($boolFirstLanguage && $boolLanguageIsActive) ? 'display: block;' : 'display: none;',
                            'FUNCTION_ID'           =>  $functionId,
                            'PARTNERS_TITLE'		=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['subject'],
                            'PARTNERS_CONTACTNAME'	=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['contactname'],
                            'PARTNERS_EMAIL'		=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['email'],
                            'PARTNERS_WEBSITE'		=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['website'],
                            'PARTNERS_ADDRESS1'		=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['address1'],
                            'PARTNERS_ADDRESS2'		=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['address2'],
                            'PARTNERS_CITY'		    =>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['city'],
                            'PARTNERS_ZIPCODE'		=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['zipcode'],
                            'PARTNERS_PHONE'		=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['phone'],
                            'PARTNERS_FAX'		    =>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['fax'],
                            'PARTNERS_REFERENCE'	=>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['reference'],
                            'PARTNERS_QUOTE'	    =>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['quote'],
                            'PARTNERS_IS_ACTIVE'    =>  $this->_checkStatus($arrEntries[$intEntryId]['translation'][$intLanguageId]['status'],'name="active_'.$intLanguageId.'"'),
                            'PARTNERS_LEVEL'     	=>  $this->_getEditOtherLevelMenu($intEntryId,$titleName_level,'name="level_'.$intLanguageId.'[]" multiple size="10" style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('2')),
                            'PARTNERS_LEVEL_MULTI'  =>  $this->_getEditLevelMenu($intEntryId,$titleName_level,'name="editlevel_'.$intLanguageId.'[]" multiple size="10" style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('2')),
                            'PARTNERS_PROFILE'    	=>  $this->_getEditOtherLevelMenu($intEntryId,$titleName_profile,'name="profile_'.$intLanguageId.'[]" multiple size="10" style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('3')),
                            'PARTNERS_PROFILE_MULTI'=>  $this->_getEditLevelMenu($intEntryId,$titleName_profile,'name="editprofile_'.$intLanguageId.'[]" multiple size="10" style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('3')),
                            'PARTNERS_COUNTRY'      =>  $this->_getEditOtherLevelMenu($intEntryId,$titleName_country,'name="country_'.$intLanguageId.'[]" id="country_'.$intLanguageId.'" style="width:150px;" onchange=PopulateRegions'.$functionId.'(0,this.value,'.$intLanguageId.',"'.$lang_multiple.'","country_");','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('4'),$ajaxRequest=0,$intRegionId=0,$posId = 1),
                            'PARTNERS_COUNTRY1'     =>  $this->_getEditOtherLevelMenu($intEntryId,$titleName_country,'name="country1_'.$intLanguageId.'[]" id="country1_'.$intLanguageId.'" style="width:150px;" onchange=PopulateRegions'.$functionId.'(1,this.value,'.$intLanguageId.',"'.$lang_multiple.'","country1_");','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('4'),$ajaxRequest=0,$intRegionId=0,$posId = 2),
                            'PARTNERS_COUNTRY2'     =>  $this->_getEditOtherLevelMenu($intEntryId,$titleName_country,'name="country2_'.$intLanguageId.'[]" id="country2_'.$intLanguageId.'" style="width:150px;" onchange=PopulateRegions'.$functionId.'(2,this.value,'.$intLanguageId.',"'.$lang_multiple.'","country2_");','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('4'),$ajaxRequest=0,$intRegionId=0,$posId = 3),
                            'PARTNERS_COUNTRY_MULTI'=>  $this->_getEditLevelMenu($intEntryId,$titleName_country,'name="editcountry_'.$intLanguageId.'[]" id="editcountry_'.$intLanguageId.'" multiple size="10" style="width:200px;" onChange="PopulateRegions'.$functionId.'('.$intLanguageId.')";','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('4')),
                            'REGION_MULTI_1'        =>  $this->_getEditRegionMenu($intEntryId,$titleName_region,'name="region_0_'.$intLanguageId.'[]" id="editregion_'.$intLanguageId.'" multiple size="10" style="width:152px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('6'),'1'),
                            'REGION_MULTI_2'        =>  $this->_getEditRegionMenu($intEntryId,$titleName_region,'name="region_1_'.$intLanguageId.'[]" id="editregion_'.$intLanguageId.'" multiple size="10" style="width:152px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('6'),'2'),
                            'REGION_MULTI_3'        =>  $this->_getEditRegionMenu($intEntryId,$titleName_region,'name="region_2_'.$intLanguageId.'[]" id="editregion_'.$intLanguageId.'" multiple size="10" style="width:152px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('6'),'3'),
                            'PARTNERS_REGION'       =>  $this->_getListLevelMenu($arrEntries[$intEntryId]['translation'][$intLanguageId]['region'],$titleName_region,'name="region_'.$intLanguageId.'" id = "region_'.$intLanguageId.'" multiple size="10" style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('6'),$arrEntries[$intEntryId]['translation'][$intLanguageId]['country'],$arrEntries[$intEntryId]['translation'][$intLanguageId]['region']),
                            'PARTNERS_VERTICAL'     =>  $this->_getEditOtherLevelMenu($intEntryId,$titleName_vertical,'name="vertical_'.$intLanguageId.'[]" size="10" multiple style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('5')),
                            'PARTNERS_VERTICAL_MULTI'=> $this->_getEditLevelMenu($intEntryId,$titleName_vertical,'name="editvertical_'.$intLanguageId.'[]" size="10" multiple style="width:200px;"','backend',$intLanguageId,$_ARRAYLANG['TXT_PARTNERS_SELECT']."&nbsp;".$this->_getCategoryname('5')),
                            'PARTNERS_TEXT'         =>  get_wysiwyg_editor('partnersText_'.$intLanguageId,$arrEntries[$intEntryId]['translation'][$intLanguageId]['content']),
                            'DIV_IMAGE'			    =>	$arrEntries[$intEntryId]['translation'][$intLanguageId]['image'],
                            'PASS_LANG_MULTIPLE'    => $lang_multiple,
                            'DIV_CATEGORIES_1'	    =>	$arrCategoriesContent[0],
                            'DIV_CATEGORIES_2'	    =>	$arrCategoriesContent[1],
                            'DIV_CATEGORIES_3'	    =>	$arrCategoriesContent[2]
                    ));
                    $pass_java_language .= $intLanguageId."-";
                    $this->_objTpl->parse('showLanguageDivs');

                    if ($boolLanguageIsActive) {
                        $boolFirstLanguage = false;
                    }
                    if($intRegLang==0) {
                        $intRegLangval=$intLanguageId;
                    }
                    ++$intLanguageCounter;
                    ++$intRegLang;
                }

                $this->_objTpl->setVariable(array(
                        'JAVASRIPT_REGION'      =>  $javascript,
                        'PARTNERS_FORM_ACTION'  =>  $_ARRAYLANG['PARTNERS_FORM_ACTION_UPDATE'],
                        'PASS_JAVA_LANGUAGE'    =>  $pass_java_language,
                        'EDIT_MESSAGE_ID'		=>	$intEntryId,
                        'EDIT_LANGUAGES_1'		=>	$arrLanguages[0],
                        'EDIT_LANGUAGES_2'		=>	$arrLanguages[1],
                        'EDIT_LANGUAGES_3'		=>	$arrLanguages[2],
                        'EDIT_JS_TAB_TO_DIV'	=>	$strJsTabToDiv
                ));
            }
        } else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_EDIT_ERROR_ID'];
        }
    }

    /**
     * Collects and validates all values from the edit-partners-form. Updates values in database.
     *
     * @global 	array		$_ARRAYLANG
     * @global 	object		$objDatabase
     */
    function updatePartners() {
        global $_ARRAYLANG, $objDatabase;

        $intMessageId = intval($_POST['frmEditCategory_MessageId']);
        if (isset($_POST['frmEditEntry_Languages']) && is_array($_POST['frmEditEntry_Languages'])) {
            foreach ($_POST['frmEditEntry_Languages'] as $intKey => $intLanguageId) {
                $arrActiveLanguages[$intLanguageId] = true;
            }
            $maincount = 0;
            $count = 0;
            foreach ($_POST as $intKey => $intLanguageId) {
                if (substr($intKey,0,strlen('partnersTitle_')) == 'partnersTitle_') {
                    $intLanguageId = intval(substr($intKey,strlen('partnersTitle_')));
                    if($errorMessage != true) {

                        if(!array_key_exists($intLanguageId,$arrActiveLanguages)) {
                            $count++;
                        }
                        else if(trim($_POST['email_'.$intLanguageId])=="") {
                            $errArray["title"][$intLanguageId]     = trim($_POST['partnersTitle_'.$intLanguageId]);
                            $errArray["active"][$intLanguageId]    = trim($_POST['active_'.$intLanguageId]);
                            $errArray["level"][$intLanguageId]     = trim($_POST['level_'.$intLanguageId]);
                            $errArray["profile"][$intLanguageId]   = trim($_POST['profile_'.$intLanguageId]);
                            $errArray["country"][$intLanguageId]   = trim($_POST['country_'.$intLanguageId]);
                            $errArray["region"][$intLanguageId]    = trim($_POST['region_'.$intLanguageId]);
                            $errArray["vertical"][$intLanguageId]  = trim($_POST['vertical_'.$intLanguageId]);
                            $errArray["contactname"][$intLanguageId]  = trim($_POST['contactname_'.$intLanguageId]);
                            $errArray["category"][$intLanguageId]  = (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array();
                            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_EMAIL'];
                            $errorMessage = true;
                        }
                        else if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",trim($_POST['email_'.$intLanguageId]))) {
                            $errArray["title"][$intLanguageId]        = trim($_POST['partnersTitle_'.$intLanguageId]);
                            $errArray["active"][$intLanguageId]       = trim($_POST['active_'.$intLanguageId]);
                            $errArray["level"][$intLanguageId]        = trim($_POST['level_'.$intLanguageId]);
                            $errArray["profile"][$intLanguageId]      = trim($_POST['profile_'.$intLanguageId]);
                            $errArray["country"][$intLanguageId]      = trim($_POST['country_'.$intLanguageId]);
                            $errArray["region"][$intLanguageId]       = trim($_POST['region_'.$intLanguageId]);
                            $errArray["vertical"][$intLanguageId]     = trim($_POST['vertical_'.$intLanguageId]);
                            $errArray["contactname"][$intLanguageId]  = trim($_POST['contactname_'.$intLanguageId]);
                            $errArray["email"][$intLanguageId]        = trim($_POST['email_'.$intLanguageId]);
                            $errArray["category"][$intLanguageId]  = (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array();
                            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_EMAIL_VALID'];
                            $errorMessage = true;
                        }
                        else if(trim($_POST['website_'.$intLanguageId])=="") {
                            $errArray["title"][$intLanguageId]        = trim($_POST['partnersTitle_'.$intLanguageId]);
                            $errArray["active"][$intLanguageId]       = trim($_POST['active_'.$intLanguageId]);
                            $errArray["level"][$intLanguageId]        = trim($_POST['level_'.$intLanguageId]);
                            $errArray["profile"][$intLanguageId]      = trim($_POST['profile_'.$intLanguageId]);
                            $errArray["country"][$intLanguageId]      = trim($_POST['country_'.$intLanguageId]);
                            $errArray["region"][$intLanguageId]       = trim($_POST['region_'.$intLanguageId]);
                            $errArray["vertical"][$intLanguageId]     = trim($_POST['vertical_'.$intLanguageId]);
                            $errArray["contactname"][$intLanguageId]  = trim($_POST['contactname_'.$intLanguageId]);
                            $errArray["email"][$intLanguageId]        = trim($_POST['email_'.$intLanguageId]);
                            $errArray["category"][$intLanguageId]  = (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array();
                            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_WEBSITE'];
                            $errorMessage = true;
                        }
                        else if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', trim($_POST['website_'.$intLanguageId]))) {
                            $errArray["title"][$intLanguageId]        = trim($_POST['partnersTitle_'.$intLanguageId]);
                            $errArray["active"][$intLanguageId]       = trim($_POST['active_'.$intLanguageId]);
                            $errArray["level"][$intLanguageId]        = trim($_POST['level_'.$intLanguageId]);
                            $errArray["profile"][$intLanguageId]      = trim($_POST['profile_'.$intLanguageId]);
                            $errArray["country"][$intLanguageId]      = trim($_POST['country_'.$intLanguageId]);
                            $errArray["region"][$intLanguageId]       = trim($_POST['region_'.$intLanguageId]);
                            $errArray["vertical"][$intLanguageId]     = trim($_POST['vertical_'.$intLanguageId]);
                            $errArray["contactname"][$intLanguageId]  = trim($_POST['contactname_'.$intLanguageId]);
                            $errArray["email"][$intLanguageId]        = trim($_POST['email_'.$intLanguageId]);
                            $errArray["website"][$intLanguageId]      = trim($_POST['website_'.$intLanguageId]);
                            $errArray["category"][$intLanguageId]  = (isset($_POST['frmEditEntry_Categories_'.$intLanguageId])) ? $_POST['frmEditEntry_Categories_'.$intLanguageId] : array();
                            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_WEBSITE_VALID'].$_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_WEBSITE_EXAMPLE'];
                            $errorMessage = true;
                        }
                        else {
                            $count++;
                        }

                        $maincount++;
                    }
                }
            }

            if($count == $maincount) {
                $objDatabase->Execute('	UPDATE	'.DBPREFIX.'module_partners_create
    								SET 	`user_id` = '.$this->_intCurrentUserId.',
    										`time_edited` = '.time().'
    								WHERE	message_id='.$intMessageId.'
    								LIMIT	1
    							');


                //Remove existing data for all languages
                $objDatabase->Execute('	DELETE
    								FROM	'.DBPREFIX.'module_partners_create_lang
    								WHERE	message_id='.$intMessageId.'
    							');

                $objDatabase->Execute('	DELETE
    								FROM	'.DBPREFIX.'module_partners_message_to_category
    								WHERE	message_id='.$intMessageId.'
    							');

                $objDatabase->Execute('	DELETE
    								FROM	'.DBPREFIX.'module_partners_message_to_level
    								WHERE	message_id='.$intMessageId.'
    							');
                $objDatabase->Execute('	DELETE
    								FROM	'.DBPREFIX.'module_partners_message_to_profile
    								WHERE	message_id='.$intMessageId.'
    							');
                $objDatabase->Execute('	DELETE
    								FROM	'.DBPREFIX.'module_partners_message_to_country
    								WHERE	message_id='.$intMessageId.'
    							');
                $objDatabase->Execute('	DELETE
    								FROM	'.DBPREFIX.'module_partners_message_to_vertical
    								WHERE	message_id='.$intMessageId.'
    							');
                $objDatabase->Execute('	DELETE
    								FROM	'.DBPREFIX.'module_partners_message_to_region
    								WHERE	message_id='.$intMessageId.'
    							');
                //Now insert new data
                $this->insertEntryData($intMessageId);
                $this->showOverview();
                $this->_strOkMessage =  $_ARRAYLANG['TXT_PARTNERS_ENTRY_UPDATE_SUCCESSFULL'];
            }
            else {
                $this->editPartners($intMessageId);
            }
        } else {
            $this->showOverview();
            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_UPDATE_ERROR_LANGUAGES'];
        }
    }





    /**
     * Removes the entry for partners with id = $intEntry from database.
     *
     * @global 	array		$_ARRAYLANG
     * @global 	object		$objDatabase
     */
    function deletePartners($intEntryId) {
        global $_ARRAYLANG, $objDatabase;

        $intEntryId = intval($intEntryId);

        if ($intEntryId > 0) {


            $objDatabase->Execute('	DELETE
	    								FROM	'.DBPREFIX.'module_partners_create
	    								WHERE	message_id='.$intEntryId.'
	    								LIMIT	1
	    							');


            $objDatabase->Execute('	DELETE
										FROM	'.DBPREFIX.'module_partners_create_lang
										WHERE	message_id='.$intEntryId.'
									');

            $objDatabase->Execute('	DELETE
										FROM	'.DBPREFIX.'module_partners_message_to_category
										WHERE	message_id='.$intEntryId.'
									');
            $objDatabase->Execute('	DELETE
										FROM	'.DBPREFIX.'module_partners_message_to_level
										WHERE	message_id='.$intEntryId.'
									');
            $objDatabase->Execute('	DELETE
										FROM	'.DBPREFIX.'module_partners_message_to_profile
										WHERE	message_id='.$intEntryId.'
									');
            $objDatabase->Execute('	DELETE
										FROM	'.DBPREFIX.'module_partners_message_to_country
										WHERE	message_id='.$intEntryId.'
									');
            $objDatabase->Execute('	DELETE
										FROM	'.DBPREFIX.'module_partners_message_to_region
										WHERE	message_id='.$intEntryId.'
									');
            $objDatabase->Execute('	DELETE
										FROM	'.DBPREFIX.'module_partners_message_to_vertical
										WHERE	message_id='.$intEntryId.'
									');

            $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_DELETE_SUCCESSFULL'];
        } else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_DELETE_ERROR_ID'];
        }
    }


    /**
     * Performs the action for the dropdown-selection on the entry page. The behaviour depends on the parameter.
     *
     * @param	string		$strAction: the action passed by the formular.
     */
    function doEntryMultiAction($strAction='') {
        switch ($strAction) {
            case 'delete':
                foreach($_POST['selectedEntriesId'] as $intKey => $intEntryId) {
                    $this->deletePartners($intEntryId);
                }
                break;
            default:
            //do nothing!
        }
    }

    /**
     * Settings module for partners
     * @global 	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     * @global 	object		$objDatabase
     */

    function showSettings() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;

        $this->_objTpl->loadTemplateFile('module_partners_settings.htm',true,true);
        $this->_objTpl->setVariable(array(
                'TXT_GENERAL_TITLE'             => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_GENERAL_TITLE'],
                'TXT_SORT_TITLE'                => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_SORT'],
                'TXT_IMAGE_TITLE'               => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_DIMENSION'],
                'TXT_IMAGE_LEVEL_TITLE'         => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_DIMENSION_LEVEL'],
                'TXT_IMAGE_CERT_TITLE'          => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_DIMENSION_CERT'],
                'TXT_WIDTH_TITLE'               => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_WIDTH'],
                'TXT_HEIGHT_TITLE'              => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_HEIGHT'],
                'TXT_COMMENTS_ALLOW_SORT_HELP'  => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_SORT'],
                'TXT_COMMENTS_ALLOW_WIDTH_HELP' => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_WIDTH'],
                'TXT_COMMENTS_ALLOW_HEIGHT_HELP'=> $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_HEIGHT'],
                'TXT_COMMENTS_ALLOW_WIDTH_HELP_LEVEL' => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_WIDTH_LEVEL'],
                'TXT_COMMENTS_ALLOW_HEIGHT_HELP_LEVEL'=> $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_HEIGHT_LEVEL'],
                'TXT_COMMENTS_ALLOW_WIDTH_HELP_CERT' =>  $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_WIDTH_CERT'],
                'TXT_COMMENTS_ALLOW_HEIGHT_HELP_CERT'=>  $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_HEIGHT_CERT'],
                'TXT_COMMENTS_ALLOW_ACTIVE_HELP_LEVEL'=>  $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_ACTIVE_LEVEL'],
                'TXT_COMMENTS_ALLOW_ACTIVE_HELP_PROFILE'=>  $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_ACTIVE_PROFILE'],
                'TXT_COMMENTS_ALLOW_ACTIVE_HELP_COUNTRY'=>  $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_ACTIVE_COUNTRY'],
                'TXT_COMMENTS_ALLOW_ACTIVE_HELP_VERTICAL'=>  $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_ACTIVE_VERTICAL'],
                'TXT_COMMENTS_ALLOW_ACTIVE_HELP_CERTIFICATE'=>  $_ARRAYLANG['TXT_PARTNERS_SETTINGS_TOOLTIP_ACTIVE_CERTIFICATE'],
                'TXT_SAVE'                      => $_CORELANG['TXT_SAVE'],
                'TXT_ACTIVE_TITLE'  => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_ACTIVE_TITLE'],
                // 'TXT_LEVEL_TITLE'   => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_LEVEL'],
                'TXT_LEVEL_TITLE'   => $this->_getCategoryname('2'),
                // 'TXT_PROFILE_TITLE' => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_PROFILE'],
                'TXT_PROFILE_TITLE' => $this->_getCategoryname('3'),
                // 'TXT_COUNTRY_TITLE' => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_COUNTRY'],
                'TXT_COUNTRY_TITLE' => $this->_getCategoryname('4'),
                //'TXT_VERTICAL_TITLE' => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_VERTICAL'],
                'TXT_VERTICAL_TITLE' => $this->_getCategoryname('5'),
                //'TXT_CERTIFICATE_TITLE' => $_ARRAYLANG['TXT_PARTNERS_SETTINGS_CERTIFICATE']
                'TXT_CERTIFICATE_TITLE' => $this->_getCategoryname('1')
        ));
        $arrSettings = $this->_getSettings();

        foreach($arrSettings as $setKey => $setValue) {

            $this->_objTpl->setVariable(array(
                    'PARTNERS_SETTINGS_SORT'         => $arrSettings['sortorder'],
                    'PARTNERS_SETTINGS_WIDTH'        => $arrSettings['width'],
                    'PARTNERS_SETTINGS_HEIGHT'       => $arrSettings['height'],
                    'PARTNERS_SETTINGS_WIDTH_LEVEL'  => $arrSettings['lwidth'],
                    'PARTNERS_SETTINGS_HEIGHT_LEVEL' => $arrSettings['lheight'],
                    'PARTNERS_SETTINGS_WIDTH_CERT'   => $arrSettings['cwidth'],
                    'PARTNERS_SETTINGS_HEIGHT_CERT'  => $arrSettings['cheight']
            ));
        }

        //echo "array value".$arrSettings['ctis_active'];

        $this->_objTpl->setVariable(array(
                'PARTNERS_SETTINGS_ORDER_ACTIVE' => $this->_getsettingsActive($arrSettings['sortorder']),
                'PARNTERS_ACTIVE_LEVEL' => $this->_getactiveProperties($arrSettings['lis_active'],'frmSettings_act_level'),
                'PARNTERS_ACTIVE_PROFILE' => $this->_getactiveProperties($arrSettings['pis_active'],'frmSettings_act_profile'),
                'PARNTERS_ACTIVE_COUNTRY' => $this->_getactiveProperties($arrSettings['cis_active'],'frmSettings_act_country'),
                'PARNTERS_ACTIVE_VERTICAL' => $this->_getactiveProperties($arrSettings['vis_active'],'frmSettings_act_vertical'),
                'PARNTERS_ACTIVE_CERTIFICATE' => $this->_getactiveProperties($arrSettings['ctis_active'],'frmSettings_act_certificate')
        ));
    }

    /**
     * Settings are saved here
     * @global 	array		$_CORELANG
     * @global 	array		$_ARRAYLANG
     * @global 	object		$objDatabase
     */

    function saveSettings() {
        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        $intMessageId = 1;
        if($_POST['frmSettings_submit']) {
            $settings =  array();
            foreach($_POST as $strKey => $strValue) {
                if (substr($strKey,0,strlen('frmSettings_sort')) == 'frmSettings_sort') {
                    $intLanguageId = intval(substr($strKey,strlen('frmSettings_sort')));
                    $settings[$intLanguageId] = array('sortorder'  => contrexx_addslashes(strip_tags($_POST['frmSettings_sort'])),
                            'width' 	    => contrexx_addslashes(strip_tags($_POST['frmSettings_width'])),
                            'height' 	=> contrexx_addslashes(strip_tags($_POST['frmSettings_height'])),
                            'lwidth' 	=> contrexx_addslashes(strip_tags($_POST['frmSettings_widthlevel'])),
                            'lheight' 	=> contrexx_addslashes(strip_tags($_POST['frmSettings_heightlevel'])),
                            'cwidth' 	=> contrexx_addslashes(strip_tags($_POST['frmSettings_widthcert'])),
                            'cheight'    => contrexx_addslashes(strip_tags($_POST['frmSettings_heightcert'])),
                            'lis_active'    => contrexx_addslashes(strip_tags($_POST['frmSettings_act_level'])),
                            'pis_active'    => contrexx_addslashes(strip_tags($_POST['frmSettings_act_profile'])),
                            'cis_active'    => contrexx_addslashes(strip_tags($_POST['frmSettings_act_country'])),
                            'vis_active'    => contrexx_addslashes(strip_tags($_POST['frmSettings_act_vertical'])),
                            'ctis_active'    => contrexx_addslashes(strip_tags($_POST['frmSettings_act_certificate']))
                    );
                }
            }

            foreach ($settings as $intLanguageId => $arrEntryValues) {

                $objDatabase->Execute(' UPDATE '.DBPREFIX.'module_partners_settings
									SET `sortorder`  = "'.$arrEntryValues['sortorder'].'",
										`width`      = "'.$arrEntryValues['width'].'",
										`height`     = "'.$arrEntryValues['height'].'",
										`lwidth`      = "'.$arrEntryValues['lwidth'].'",
										`lheight`     = "'.$arrEntryValues['lheight'].'",
										`cwidth`      = "'.$arrEntryValues['cwidth'].'",
										`cheight`     = "'.$arrEntryValues['cheight'].'",
										`lis_active`     = "'.$arrEntryValues['lis_active'].'",
										`pis_active`     = "'.$arrEntryValues['pis_active'].'",
										`cis_active`     = "'.$arrEntryValues['cis_active'].'",
										`vis_active`     = "'.$arrEntryValues['vis_active'].'",
										`ctis_active`     = "'.$arrEntryValues['ctis_active'].'"
										 WHERE `id` = '.$intMessageId.' LIMIT 1');

            }
            $this->_strOkMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_SUCCESSFULL'];
        } else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_ENTRY_ADD_ERROR_LANGUAGES'];
        }

    }

    /*
    * This function is used to concandinate the different Categories..
    * @global 	array		$_CORELANG
    * @global 	array		$_ARRAYLANG
    * @global 	object		$objDatabase
    */

    function csvCategory($msgID,$tableName,$mainTable,$fiedlName,$langExport) {

        global $_CORELANG, $_ARRAYLANG, $objDatabase;
        /**Selecting the Coutries..*/


        $queryCategories 		= "SELECT category_id,lang_id FROM ".DBPREFIX.$tableName." WHERE message_id=".$msgID."";
        $objResultCategories 	= $objDatabase->Execute($queryCategories);

        while(!$objResultCategories->EOF) {


            /** Extracig the Language Id..*/

            $Lang_ID=$objResultCategories->fields["lang_id"];
            ///echo "Language Id".$Lang_ID;

            /** This should be checked because it contains not id field name,it contain category_id...
             * When the Person certicates option to be exported into the csv
             */

            if($mainTable =="module_partners_categories") {

                $fieldName="category_id";
            }
            else {
                $fieldName="id";
            }


            $selCountry       = "SELECT ".$fiedlName." FROM ".DBPREFIX.$mainTable."
                                WHERE ".$fieldName."='".$objResultCategories->fields['category_id']."'
                                AND lang_id='".$Lang_ID."'";

            $objResultCountry = $objDatabase->Execute($selCountry);



            /** Concadinating the Countires into Different Languages.... */

            while(!$objResultCountry->EOF) {

                $arrCountry["catagory"][$Lang_ID][]= $objResultCountry->fields[$fiedlName];
                $objResultCountry->MoveNext();

            }

            $objResultCategories->MoveNext();
        }

        $countCountry=count($arrCountry["catagory"][$langExport]);
        $concadinatedCountry="";

        /**Concadinating the & with two or more Country... */

        for($k=0;$k<$countCountry;$k++) {

            $concadinatedCountry.=$arrCountry["catagory"][$langExport][$k];
            if(($countCountry-1)!=$k) {

                $concadinatedCountry.="&";
            }
        }
        return $concadinatedCountry;

    }

    /*
    * This function is used to export individual partners entries
    * @global 	array		$_CORELANG
    * @global 	array		$_ARRAYLANG
    * @global 	object		$objDatabase
    */

    function getCsv($id,$subject) {
        global $_CORELANG,$_ARRAYLANG,$objDatabase;
        unset($data);
        $data = array();
        $Head_data = array("Language","Status","Title","Certificate","Level","Profile","Country","Region","Vertical","Contact-Name","E-Mail","Website","Address1","Address2","City","Zipcode","Phone","Fax","Reference","Quote","Content","Image");
        $titles=array();
        $all_values=array();
        $filename;

        if (empty($id)) {
            return;
        }
        //note title
        $queryNote 		= "SELECT `subject` FROM ".DBPREFIX."module_partners_create_lang WHERE message_id=".$id." ";
        $objResultNote 	= $objDatabase->SelectLimit($queryNote, 1);

        $sub=explode(" ",$subject);
        $cnt=count($sub);
        $subject1="";
        for($k=0;$k<=$cnt-1;$k++) {
            $subject1.=$sub[$k]."_";
        }
        $ranname=rand();
        $filename = strip_tags($subject1.$ranname.".xls");
        $queryExport 		= "SELECT * FROM ".DBPREFIX."module_partners_create_lang WHERE message_id=".$id."";
        $objResultExport 	= $objDatabase->Execute($queryExport);
        $i=0;

        while(!$objResultExport->EOF) {
            if($objResultExport->fields['status']!=0) {
                $status = "active";
            }
            else {
                $status = "inActive";
            }
            $j=0;
            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                if(strip_tags($objResultExport->fields['lang_id']) == $intLanguageId) {
                    $language_export = $intLanguageId;
                    $arrLanguagesExport = $arrTranslations['long'];
                }
            }


            /**Calling the Category two merge it.. */

            $concadinatedCertificate   = $this->csvCategory($id,"module_partners_message_to_category","module_partners_categories","name",$language_export);
            $concadinatedCountry       = $this->csvCategory($id,"module_partners_message_to_country","module_partners_user_country","country",$language_export);
            $concadinatedLevel         = $this->csvCategory($id,"module_partners_message_to_level","module_partners_user_level","level",$language_export);
            $concadinatedProfile       = $this->csvCategory($id,"module_partners_message_to_profile","module_partners_user_profile","profile",$language_export);
            $concadinatedVertical      = $this->csvCategory($id,"module_partners_message_to_vertical","module_partners_user_vertical","vertical",$language_export);
            $concadinatedRegion        = $this->csvCategory($id,"module_partners_message_to_region","module_partners_user_region","name",$language_export);

            $data[$i][$j]=$arrLanguagesExport." ";
            $j++;

            if($objResultExport->fields['is_active']!=0) {
                $data[$i][$j]=$status." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['subject'])." ";
                $j++;
                $data[$i][$j]=strip_tags($concadinatedCertificate)." ";
                $j++;
                $data[$i][$j]=strip_tags($concadinatedLevel)." ";
                $j++;
                $data[$i][$j]=strip_tags($concadinatedProfile)." ";
                $j++;
                $data[$i][$j]=strip_tags($concadinatedCountry)." ";
                //echo "data".$data[$i][$j];
                // echo "concadinated".$concadinatedCountry;
                $j++;
                $data[$i][$j]=strip_tags($concadinatedRegion)." ";
                $j++;
                $data[$i][$j]=strip_tags($concadinatedVertical)." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['contactname'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['email'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['website'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['address1'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['address2'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['city'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['zipcode'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['phone'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['fax'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['reference'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['quote'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['content'])." ";
                $j++;
                $data[$i][$j]=strip_tags($objResultExport->fields['image'])." ";
                $j++;

                ++$i;
            }
            $objResultExport->MoveNext();
        }
        // print "<pre>";
        //print_r($data);
        //print "</pre>";
        $this->setHeadersAndValues($Head_data,$data);
        //now generate the excel file with the data and headers set
        $partnersExport =  $this->GenerateExcelFile();
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Expires: 0");

        print "$partnersExport";

        exit;
    }


    /*
    * This function is used to export whole partners entries
    * @global 	array		$_CORELANG
    * @global 	array		$_ARRAYLANG
    * @global 	object		$objDatabase
    */

    function getMultiplecsv($Expval) {
        global $_CORELANG,$_ARRAYLANG,$objDatabase;
        unset($data);
        $data = array();
        $heading = array("Language","Status","Title","Certificate","Level","Profile","Country","Region","Vertical","Contact-Name","E-Mail","Website","Address1","Address2","City","Zipcode","Phone","Fax","Reference","Quote","Content","Image");
        $titles=array();
        $all_values=array();
        $filename;
        $valueIsZero;
        $i = 0;

        $arrLanguagesExport = array(0 => '', 1 => '', 2 => '');
        $intLanguageCounter=0;
        foreach($Expval as $value) {
            if($value=='0') {
                $queryExport 		= "SELECT * FROM ".DBPREFIX."module_partners_create_lang ";
                $objResultExport 	= $objDatabase->Execute($queryExport);
                $valueIsZero = true;
            }
            else if($valueIsZero == false) {
                $queryExport 		= "SELECT * FROM ".DBPREFIX."module_partners_create_lang WHERE lang_id=".$value."";
                $objResultExport 	= $objDatabase->Execute($queryExport);
            }

            if($objResultExport->RecordCount() > 0) {



                //echo "concadinated Certificates".$concadinatedCertificate."message Id".$objResultExport->fields['message_id']."<br>";
                //echo "inside";
                while (!$objResultExport->EOF) {
                    if($objResultExport->fields['status']!=0) {
                        $status = "active";
                    }
                    else {
                        $status = "inActive";
                    }
                    $j=0;
                    foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                        if(strip_tags($objResultExport->fields['lang_id']) == $intLanguageId) {
                            $language_export = $intLanguageId;
                            $arrLanguagesExport = $arrTranslations['long'];
                        }
                    }

                    if($objResultExport->fields['is_active']!=0) {

                        /**Calling the Category two merge it.. */

                        $concadinatedCertificate   = $this->csvCategory($objResultExport->fields['message_id'],"module_partners_message_to_category","module_partners_categories","name",$language_export);
                        $concadinatedCountry       = $this->csvCategory($objResultExport->fields['message_id'],"module_partners_message_to_country","module_partners_user_country","country",$language_export);
                        $concadinatedLevel         = $this->csvCategory($objResultExport->fields['message_id'],"module_partners_message_to_level","module_partners_user_level","level",$language_export);
                        $concadinatedProfile       = $this->csvCategory($objResultExport->fields['message_id'],"module_partners_message_to_profile","module_partners_user_profile","profile",$language_export);
                        $concadinatedVertical      = $this->csvCategory($objResultExport->fields['message_id'],"module_partners_message_to_vertical","module_partners_user_vertical","vertical",$language_export);
                        $concadinatedRegion        = $this->csvCategory($objResultExport->fields['message_id'],"module_partners_message_to_region","module_partners_user_region","name",$language_export);

                        $data[$i][$j]=$arrLanguagesExport." ";
                        $j++;
                        $data[$i][$j]=$status." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['subject'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($concadinatedCertificate)." ";
                        $j++;
                        $data[$i][$j]=strip_tags($concadinatedLevel)." ";
                        $j++;
                        $data[$i][$j]=strip_tags($concadinatedProfile)." ";
                        $j++;
                        $data[$i][$j]=strip_tags($concadinatedCountry)." ";
                        $j++;
                        $data[$i][$j]=strip_tags($concadinatedRegion)." ";
                        $j++;
                        $data[$i][$j]=strip_tags($concadinatedVertical)." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['contactname'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['email'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['website'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['address1'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['address2'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['city'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['zipcode'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['phone'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['fax'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['reference'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['quote'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['content'])." ";
                        $j++;
                        $data[$i][$j]=strip_tags($objResultExport->fields['image'])." ";
                        $j++;

                        ++$i;
                    }
                    $objResultExport->MoveNext();

                }

            }

        }
        $ranname=rand();
        $filename = "Partners".$ranname.".xls";

        $this->setHeadersAndValues($heading,$data);
        //now generate the excel file with the data and headers set
        $partnersExport =  $this->GenerateExcelFile();
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Expires: 0");

        print "$partnersExport";
        exit();
    }


    /*
    * This function is used to Import new partners entries
    * @global 	array		$_CORELANG
    * @global 	array		$_ARRAYLANG
    * @global 	object		$objDatabase
    */

    function _users() {
        global $objDatabase, $_ARRAYLANG;

        $this->_pageTitle = $_ARRAYLANG['TXT_NEWSLETTER_RECIPIENTS'];
        $this->_objTpl->loadTemplateFile('module_partners_user.html');

        if (!isset($_REQUEST['tpl'])) {
            $_REQUEST['tpl'] = '';
        }

        switch ($_REQUEST['tpl']) {
            default:
                $this->importuser();
                break;
        }
    }

    /*
    * This function is used to Importing csv file
    * @global 	array		$_CORELANG
    * @global 	array		$_ARRAYLANG
    * @global 	object		$objDatabase
    */

    function importuser() {
        global $objDatabase, $_ARRAYLANG,$_CORELANG;
        $objTpl = new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/partners/template');
        CSRF::add_placeholder($objTpl);
        $objTpl->setErrorHandling(PEAR_ERROR_DIE);

        require_once ASCMS_LIBRARY_PATH . "/importexport/import.class.php";
        $arrCategories = $this->createCategoryArray();
        $objImport = new Import();
        $arrFields = array(
                'subject'            => $_ARRAYLANG['TXT_PARTNERS_TITLE'],
                'is_active'        => $_ARRAYLANG['TXT_PARTNERS_ACTIVE'],
                'status'           => $_ARRAYLANG['TXT_PARTNERS_STATUS'],
                'certificate'      => $_ARRAYLANG['TXT_PARTNERS_CERTIFICATE'],
                'level'            => $_ARRAYLANG['TXT_PARTNERS_LEVEL'],
                'profile'          => $_ARRAYLANG['TXT_PARTNERS_PROFILE'],
                'country'          => $_ARRAYLANG['TXT_PARTNERS_COUNTRY'],
                'regions'          => $_ARRAYLANG['TXT_PARTNERS_REGIONS'],
                'vertical'         => $_ARRAYLANG['TXT_PARTNERS_VERTICAL'],
                'contactname'      => $_ARRAYLANG['TXT_NEWSLETTER_CITY'],
                'email'            => $_ARRAYLANG['TXT_PARTNERS_EMAIL_ADDRESS'],
                'website'          => $_ARRAYLANG['TXT_PARTNERS_WEB'],
                'address1'         => $_ARRAYLANG['TXT_PARTNERS_ADDR1'],
                'address2'         => $_ARRAYLANG['TXT_PARTNERS_ADDR2'],
                'city'             => $_ARRAYLANG['TXT_PARTNERS_CITY'],
                'zipcode'          => $_ARRAYLANG['TXT_PARTNERS_ZIP'],
                'phone'            => $_ARRAYLANG['TXT_PARTNERS_PHONE'],
                'fax'              => $_ARRAYLANG['TXT_PARTNERS_FAX'],
                'reference'        => $_ARRAYLANG['TXT_PARTNERS_REFERENCE'],
                'quote'            => $_ARRAYLANG['TXT_PARTNERS_QUOTE'],
                'image'            => $_ARRAYLANG['TXT_PARTNERS_ULOGO'],
                'content'          => $_ARRAYLANG['TXT_PARTNERS_CONTENT']
        );

        if (isset($_POST['import_cancel'])) {
            // Abbrechen. Siehe Abbrechen
            $objImport->cancel();
            CSRF::header("Location: index.php?cmd=partners&act=users&tpl=import");
            exit;
        } elseif ($_POST['fieldsSelected']) {
            // Speichern der Daten. Siehe Final weiter unten.
            if(isset($_POST['pairs_left_keys']) && isset($_POST['pairs_right_keys'])) {
                $rightval=$_POST['pairs_right_keys'];
                $rightval=substr($rightval,0,-1);
                $leftval=$_POST['pairs_left_keys'];
                $leftval=substr($leftval,0,-1);
                $flname=$_REQUEST['csvFilename'];
                $this->_objTpl->loadTemplateFile('module_partners_import_lang.html',true,true);
                $this->_objTpl->setVariable(array(
                        'IMPORT_PARTNERS_LANG'  => $_ARRAYLANG['TXT_PARTNERS_CATEGORY_ADD_LANGUAGES'],
                        'TXT_IMPORT_PARTNERS_LANG'  => $_ARRAYLANG['TXT_IMPORT_PARTNERS_LANGUAGES'],
                        'IMPORT_LANGUAGE_BUTTON'  => $_ARRAYLANG['IMPORT_LANGUAGE_BUTTON'],
                        'HIDDEN_IMPORT_RIGHTKEYS'  => $rightval,
                        'HIDDEN_IMPORT_LEFTKEYS'  => $leftval,
                        'HIDDEN_IMPORT_PARTNERS_CSV'  => $flname
                ));


                if (count($this->_arrLanguages) > 0) {


                    $intLanguageCounter = 0;
                    $intLanguageCounterMultiple = 0;
                    $intLanguageCounterExtend=0;
                    $arrLanguages = array(0 => '', 1 => '', 2 => '');
                    $arrLanguagesExtend = array(0 => '', 1 => '', 2 => '');
                    $strJsTabToDiv = '';
                    $lang_multiple = '';
                    $strJsTabToDivExtend = '';
                    $rowClass = "row2";

                    foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {
                        if($intLanguageCounterMultiple >= 1) {
                            $lang_multiple .= $intLanguageId."-";
                        }

                        $intLanguageCounterMultiple++;
                    }
                    foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {

                        if($intLanguageCounter >= 1) {
                            $functionId = 1;
                        }else {
                            //doNothing
                        }
                        $intCategoriesCounter = 0;
                        $arrCategoriesContent = array(0 => '', 1 => '', 2 => '');
                        $chkArray = array();
                        $arrLanguages[$intLanguageCounter%3] .= '<input checked="checked" type="checkbox" name="frmEditEntry_Languages[]" id="EditEntry_languages_'.$intLanguageId.'" value="'.$intLanguageId.'" onclick="switchBoxAndTab(this, \'addEntry_'.$arrTranslations['long'].'\');" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';
                        //echo $intLanguageCounter;

                        $intLanguageCounter++;
                    }

                    $this->_objTpl->setVariable(array(
                            'IMPORT_LANGUAGES_1'	=>	$arrLanguages[0],
                            'IMPORT_LANGUAGES_2'	=>	$arrLanguages[1],
                            'IMPORT_LANGUAGES_3'	=>	$arrLanguages[2]
                    ));

                }

            }
            $arrRecipients = $objImport->getFinalData($arrFields);

            if ($_POST['category'] == '') {

            } else {
                print $arrLists = array(intval($_POST['category']));
            }

            $EmailCount = 0;
            $arrBadEmails = array();
            $ExistEmails = 0;
            $NewEmails = 0;


        } elseif ($_FILES['importfile']['size'] == 0 || (isset($_POST['imported']) && $_REQUEST['category'] == 'selectcategory')) {
            // Dateiauswahldialog. Siehe Fileselect
            $this->_pageTitle = $_ARRAYLANG['TXT_IMPORT'];
            $this->_objTpl->addBlockfile('PARTNERS_USER_FILE', 'module_partners_user_import', 'module_partners_user_import.html');
            if (isset($_POST['imported']) && $_REQUEST['category'] == 'selectcategory') {
                $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_SELECT_CATEGORY'];

            }

            $objImport->initFileSelectTemplate($objTpl);

            $objTpl->setVariable(array(
                    "IMPORT_ACTION"    => "?cmd=partners&amp;act=users&amp;tpl=import",
                    'TXT_FILETYPE'    => 'Datatype',
                    'TXT_HELP'        => 'Wahlen Sie hier eine Datei aus, deren Inhalt importiert werden soll:',
                    'IMPORT_ADD_NAME'    => 'List',
                    'IMPORT_ADD_VALUE'   => $this->CategoryDropDown(),
                    'IMPORT_ROWCLASS'    => 'row2'
            ));


            $this->_objTpl->setVariable(array(
                    'TXT_PARTNERS_IMPORT_FROM_FILE'    => $_ARRAYLANG['TXT_PARTNERS_IMPORT_FROM_FILE'],
                    //'TXT_IMPORT'                => $_ARRAYLANG['TXT_IMPORT'],
                    'TXT_IMPORT_IN_CATEGORY'    => $_ARRAYLANG['TXT_IMPORT_IN_CATEGORY'],
                    'TXT_ENTER_EMAIL_ADDRESS'     => $_ARRAYLANG['TXT_ENTER_EMAIL_ADDRESS'],
            ));

            $this->_objTpl->setVariable(array(
                    'PARTNERS_CATEGORY_MENU'     => $this->CategoryDropDown(),
                    'PARTNERS_IMPORT_FRAME'    => $objTpl->get()
            ));





            if (isset($_POST['partners_import_plain'])) {
                if ($_REQUEST['category'] == 'selectcategory') {
                    $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_SELECT_CATEGORY'];
                } else {
                    if ($_REQUEST['category'] == '') {
                        $arrLists = array_keys($this->_getLists());
                    } else {
                        $arrLists = array(intval($_REQUEST['category']));
                    }

                    $NLine = chr(13).chr(10);

                    $EmailList = str_replace(array(']','[',"\t","\n","\r"), ' ', $_REQUEST["Emails"]);
                    $EmailArray = split("[ '\",;:<>".$NLine."]", contrexx_stripslashes($EmailList));
                    $EmailCount = 0;
                    $arrBadEmails = array();
                    $ExistEmails = 0;
                    $NewEmails = 0;
                    foreach ($EmailArray as $email) {
                        if (empty($email)) {
                            continue;
                        }
                        if (!strpos($email,'@')) {
                            continue;
                        }

                        if ($this->check_email($email) != 1) {
                            array_push($arrBadEmails, $email);
                        } else {
                            $EmailCount++;
                            $objRecipient = $objDatabase->SelectLimit("SELECT `id` FROM `".DBPREFIX."module_newsletter_user` WHERE `email` = '".addslashes($email)."'", 1);
                            if ($objRecipient->RecordCount() == 1) {
                                foreach ($arrLists as $listId) {
                                    $this->_addRecipient2List($objRecipient->fields['id'], $listId);
                                }
                                $ExistEmails++;
                            } else {
                                $NewEmails ++;
                                if ($objDatabase->Execute("
                                    INSERT INTO `".DBPREFIX."module_newsletter_user` (
                                        `code`, `email`, `status`, `emaildate`
                                    ) VALUES (
                                        '".$this->_emailCode()."', '".addslashes($email)."', 1, ".time()."
                                    )"
                                        ) !== false) {
                                    $this->_setRecipientLists($objDatabase->Insert_ID(), $arrLists);
                                } else {
                                    array_push($arrBadEmails, $email);
                                }
                            }
                        }
                    }
                    $this->_strOkMessage = $_ARRAYLANG['TXT_DATA_IMPORT_SUCCESSFUL']."<br/>".$_ARRAYLANG['TXT_CORRECT_EMAILS'].": ".$EmailCount."<br/>".$_ARRAYLANG['TXT_NOT_VALID_EMAILS'].": ".implode(', ', $arrBadEmails)."<br/>".$_ARRAYLANG['TXT_EXISTING_EMAILS'].": ".$ExistEmails."<br/>".$_ARRAYLANG['TXT_NEW_ADDED_EMAILS'].": ".$NewEmails;
                }

            }

            $this->_objTpl->parse('module_partners_user_import');
        } else {
            copy($_FILES["importfile"]["tmp_name"],ASCMS_MODULE_PATH."/partners/upload/". $_FILES["importfile"]["name"]);
            // Felderzuweisungsdialog. Siehe Fieldselect
            $objImport->initFieldSelectTemplate($objTpl, $arrFields);
            $objTpl->setVariable('TXT_REMOVE_PAIR', 'Delete a pair');
            $fname=  $_FILES["importfile"]["name"];
            //echo $fname;
            $objTpl->setVariable('IMPORT_HIDDEN_FILE',$fname);
            $objTpl->setVariable(array(
                    'IMPORT_HIDDEN_NAME'    => 'category',
                    'IMPORT_HIDDEN_VALUE'   => !empty($_POST['category']) ? intval($_POST['category']) : '',
                    'IMPORT_ACTION' => '?cmd=partners&amp;act=users&amp;tpl=import&amp;csvFilename='.$fname
            ));

            $this->_objTpl->setVariable('PARTNERS_USER_FILE', $objTpl->get());
        }
    }

    /*
    * This function is used to choose the category when Importing csv file
    * @global 	array		$_CORELANG
    * @global 	array		$_ARRAYLANG
    * @global 	object		$objDatabase
    */
    function CategoryDropDown($name = 'category', $selected = 0, $attrs = '') {
        global $objDatabase, $_ARRAYLANG;
        $ReturnVar = '<select name="'.$name.'"'.(!empty($attrs) ? ' '.$attrs : '').'>
        <option value="selectcategory">'.$_ARRAYLANG['TXT_PARTNERS_SELECT_CATEGORY'].'</option>
        <option value="">'.$_ARRAYLANG['TXT_PARTNERS_ALL'].'</option>';
        $queryCS         = "SELECT id, name FROM ".DBPREFIX."module_newsletter_category";
        $objResultCS     = $objDatabase->Execute($queryCS);
        if ($objResultCS !== false) {
            $CategorysFounded = 1;
            while (!$objResultCS->EOF) {
                $ReturnVar .= '<option value="'.$objResultCS->fields['id'].'"'.($objResultCS->fields['id'] == $selected ? 'selected="selected"' : '').'>'.htmlentities($objResultCS->fields['name'], ENT_QUOTES, CONTREXX_CHARSET).'</option>';
                $objResultCS->MoveNext();
            }
        }
        $ReturnVar .= '</select>';
        if($CategorysFounded!=1) {
            $ReturnVar = '';
        }
        return $ReturnVar;
    }



    /*
    * This function is used to insert the values of Imported csv file into the database
    * @global 	array		$_CORELANG
    * @global 	array		$_ARRAYLANG
    * @global 	object		$objDatabase
    */

    function csvsubmit() {
        global $objDatabase,$_ARRAYLANG;
        //echo "coming inisde";
        $rightkeys=$_REQUEST['hidden_rkeys'];
        $leftkeys=$_REQUEST['hidden_lkeys'];
        $csvfile=$_REQUEST['hidden_csv'];
        $actlang = $_REQUEST['frmEditEntry_Languages'];
        $rvalue = explode(";",$rightkeys);
        //print_r($rvalue);
        $lvalue = explode(";",$leftkeys);
        $rcnt=count($rvalue);
        $lcnt=count($lvalue);
        $cntlang=count($actlang);
        $tfile = fopen(ASCMS_MODULE_PATH."/partners/upload/".$csvfile, "r");






        if (count($this->_arrLanguages) > 0) {


            $intLanguageCounter = 0;
            $intLanguageCounterMultiple = 0;
            $intLanguageCounterExtend=0;
            $arrLanguages = array(0 => '', 1 => '', 2 => '');
            $arrLanguagesExtend = array(0 => '', 1 => '', 2 => '');
            $strJsTabToDiv = '';
            $lang_multiple = '';
            $strJsTabToDivExtend = '';
            $rowClass = "row2";
            $arraylang= array();


            foreach($this->_arrLanguages as $intLanguageId => $arrTranslations) {

                if($intLanguageCounter >= 1) {
                    $functionId = 1;
                }else {
                    //doNothing
                }
                $intCategoriesCounter = 0;
                $arrCategoriesContent = array(0 => '', 1 => '', 2 => '');
                $chkArray = array();
                // $arrLanguages[$intLanguageCounter%3] .= '<input checked="checked" type="checkbox" name="frmEditEntry_Languages[]" id="EditEntry_languages_'.$intLanguageId.'" value="'.$intLanguageId.'" onclick="switchBoxAndTab(this, \'addEntry_'.$arrTranslations['long'].'\');" />'.$arrTranslations['long'].' ['.$arrTranslations['short'].']<br />';

                $intLanguageCounter++;
            }
        }


        for($L=0;$L<$intLanguageCounter;$L++) {

            for($z=0;$z<count($actlang);$z++) {

                // echo "L".($L+1)."active:".$actlang[$z]."<br><br>";
                if(($L+1)==$actlang[$z]) {

                    $arrayLangActive[$L]="1";
                    break;
                }
                else {

                    $arrayLangActive[$L]="0";

                }
            }
        }

        //echo "count Language".$cntlang;
        if($cntlang!=0) {
            while (($flist = fgetcsv($tfile, 1000, ",")) !== FALSE) {
                if($rcnt==$lcnt) {
                    $createPartners='INSERT INTO '.DBPREFIX.'module_partners_create
    			     				        	SET `user_id`      = "'.$this->_intCurrentUserId.'",
    				    					        `time_created` = '.time().',
    					       				        `time_edited`  = '.time().',
    						      			         `hits`        = 0
    							                 ';

                    $objDatabase->Execute($createPartners);
                    $intMessageId = $objDatabase->insert_id();
                    for($M=0;$M<$intLanguageCounter;$M++) {

                        $createLangEnglish='INSERT INTO '.DBPREFIX.'module_partners_create_lang
                                                        SET `message_id`  = "'.$intMessageId.'",
                                                            `lang_id`     = "'.($M+1).'",
                                                             `is_active`  = "'.$arrayLangActive[$M].'" ';
                        $inserted_ID[$M]=$intMessageId;
                        $objDatabase->Execute($createLangEnglish);


                        /**Message to Level... */

                        $createMessage='INSERT INTO '.DBPREFIX.'module_partners_message_to_level
											                 SET `message_id` = '.$intMessageId.',
												                 `lang_id`     = "'.($M+1).'"';
                        $objDatabase->Execute($createMessage);

                        /** Category....*/

                        $createCategory='INSERT INTO '.DBPREFIX.'module_partners_message_to_category
											                 SET `message_id`  = '.$intMessageId.',
												                 `lang_id`    = "'.($M+1).'"';
                        $objDatabase->Execute($createCategory);


                        /**Profile.... */

                        $createProfile='INSERT INTO '.DBPREFIX.'module_partners_message_to_profile
											                 SET `message_id`  = '.$intMessageId.',
												                 `lang_id`    = "'.($M+1).'"';

                        $objDatabase->Execute($createProfile);

                        /**Message to Vertical.. */

                        $createVeritical='INSERT INTO '.DBPREFIX.'module_partners_message_to_vertical
											                 SET `message_id`  = '.$intMessageId.',
												                 `lang_id`     = "'.($M+1).'"';

                        $objDatabase->Execute($createVeritical);


                        /**Country ..*/
                        $createCountry='INSERT INTO '.DBPREFIX.'module_partners_message_to_country
											                 SET `message_id`  = '.$intMessageId.',
												                 `lang_id`    = "'.($M+1).'"';

                        $objDatabase->Execute($createCountry);

                        /**Region.. */

                        $createRegion='INSERT INTO '.DBPREFIX.'module_partners_message_to_region
											                 SET `message_id`  = '.$intMessageId.',
												                 `lang_id`     = "'.($M+1).'"';

                        $objDatabase->Execute($createRegion);

                    }
                    for($i=0;$i<$rcnt;$i++) {
                        for($j=0;$j<$intLanguageCounter;$j++) {
                            if($rvalue[$i]=='level') {
                                $createMessageUpdate='UPDATE '.DBPREFIX.'module_partners_message_to_level
											                 SET
                                                            `category_id`= "'.$flist[$lvalue[$i]].'"
                                                             WHERE `message_id`  = '.$inserted_ID[$j].' and
                                                             `lang_id`     = "'.($j+1).'"';
                                $objDatabase->Execute($createMessageUpdate);
                            }

                            if($rvalue[$i]=='category') {
                                $createCategoryUpdate='UPDATE '.DBPREFIX.'module_partners_message_to_category
                                                            SET
                                                            `category_id`= "'.$flist[$lvalue[$i]].'"
                                                             WHERE `message_id`  = '.$inserted_ID[$j].' and
                                                             `lang_id`     = "'.($j+1).'"';
                                $objDatabase->Execute($createCategoryUpdate);
                            }

                            if($rvalue[$i]=='profile') {
                                $createProfileUpdate='	UPDATE '.DBPREFIX.'module_partners_message_to_profile
                                                            SET
                                                            `category_id`= "'.$flist[$lvalue[$i]].'"
                                                             WHERE `message_id`  = '.$inserted_ID[$j].' and
                                                             `lang_id`     = "'.($j+1).'"';

                                $objDatabase->Execute($createProfileUpdate);
                            }
                            if($rvalue[$i]=='vertical') {
                                $createVeriticalUpdate='UPDATE '.DBPREFIX.'module_partners_message_to_vertical
                                                            SET
                                                            `category_id`= "'.$flist[$lvalue[$i]].'"
                                                             WHERE `message_id`  = '.$inserted_ID[$j].' and
                                                             `lang_id`     = "'.($j+1).'"';

                                $objDatabase->Execute($createVeriticalUpdate);
                            }

                            if($rvalue[$i]=='country') {
                                $createCountryUpdate='UPDATE '.DBPREFIX.'module_partners_message_to_country
											                 SET
                                                            `category_id`= "'.$flist[$lvalue[$i]].'",
                                                            `pos_id`     = 1
                                                             WHERE `message_id`  = '.$inserted_ID[$j].' and
                                                             `lang_id`     = "'.($j+1).'"';
                                $objDatabase->Execute($createCountryUpdate);
                            }

                            if($rvalue[$i]=='region') {
                                $createRegionUpdate='UPDATE'.DBPREFIX.'module_partners_message_to_region
											                 SET
                                                            `category_id`= "'.$flist[$lvalue[$i]].'"
                                                             WHERE `message_id`  = '.$inserted_ID[$j].' and
                                                             `lang_id`     = "'.($j+1).'"';

                                $objDatabase->Execute($createRegionUpdate);
                            }
                            else {
                                if($rvalue[$i]!='is_active') {
                                    $createCatLang='UPDATE '.DBPREFIX.'module_partners_create_lang
                                                            SET '.$rvalue[$i].'="'.$flist[$lvalue[$i]].'"
                                                            WHERE `message_id`  = '.$inserted_ID[$j].' and
                                                                  `lang_id`     = "'.($j+1).'"';

                                    $objDatabase->Execute($createCatLang);
                                }
                            }
                        }
                    }
                }
            }
        }
        else {
            $this->_strErrMessage = $_ARRAYLANG['TXT_PARTNERS_IMPORT_SELECT_LANGUAGE'];
        }

        $this->showOverview();
        $this->_strOkMessage =  $_ARRAYLANG['TXT_PARTNERS_ENTRY_IMPORT_SUCCESSFULL'];

    }

}

?>
