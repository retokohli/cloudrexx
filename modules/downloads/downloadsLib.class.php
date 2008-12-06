    <?php
/**
 * Downloads library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  Library 4 downloads module
 */

class DownloadsLibrary
{

    protected $defaultCategoryImage = array();
    protected $arrPermissionTypes = array(
        'read',
        'add_subcategories',
        'manage_subcategories',
        'add_files',
        'manage_files'
    );

    var $_arrConfig = array();
    var $_arrLang   = array();

    function DownloadsLibrary()
    {
        $this->__constructor();
    }

    function __constructor()
    {
        $this->_init();
        $this->initDefaultCategoryImage();
    }

    protected function initDefaultCategoryImage()
    {
        $this->defaultCategoryImage['src'] = ASCMS_DOWNLOADS_IMAGES_WEB_PATH.'/no_picture.gif';

        $imageSize = getimagesize(ASCMS_PATH.$this->defaultCategoryImage['src']);

        $this->defaultCategoryImage['width'] = $imageSize[0];
        $this->defaultCategoryImage['height'] = $imageSize[1];
    }

    function _init()
    {
        global $objDatabase;
        $objResult = $objDatabase->Execute("SELECT `setting_name`, `setting_value` FROM ".DBPREFIX."module_downloads_settings");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->_arrConfig[$objResult->fields['setting_name']] = $objResult->fields['setting_value'];
                $objResult->MoveNext();
            }
        }

        $objResult = $objDatabase->Execute("SELECT `id`, `lang`, `name`, `frontend`, `is_default` FROM ".DBPREFIX."languages WHERE frontend=1 ORDER BY is_default");
        if ($objResult !== false) {
            while (!$objResult->EOF) {
                $this->_arrLang[$objResult->fields['id']] = array(
                    'lang'          => $objResult->fields['lang'],
                    'name'          => $objResult->fields['name'],
                    'frontend'      => $objResult->fields['frontend'],
                    'is_default'    => $objResult->fields['is_default']);
                $objResult->MoveNext();
            }
        }
    }

    /**
     * returns true if category exist
     *
     * @param $category
     * @param $language
     * @global object $objDatabase
     */
    function _CatLang($category, $language)
    {
        global $objDatabase;
        $objResult = $objDatabase->SelectLimit("SELECT category, language FROM ".DBPREFIX."module_downloads_cat_lang WHERE category = ".$category." AND language=".$language."", 1);
        if ($objResult !== false && $objResult->RecordCount() == 1) {
            return true;
        }else{
            return false;
        }

    }

    /**
     * returns language-tab-html
     *
     * @param $objId
     * @param $objStyle
     * @param $title
     * @param $fields
     */
    function _LangTabHTML($objId, $objStyle, $title, $fieldsArray)
    {
        if($objStyle!=''){
            $objStyle = 'style="'.$objStyle.'"';
        }


        $fieldsSource = '';
        foreach ($fieldsArray as $fieldName => $fieldInfos){

            if($fieldInfos["rte"] == 1){
                $fieldsSource .= '
                    <tr class="row1">
                        <td colspan="2">'.$fieldInfos["name"].'<br />'.get_wysiwyg_editor($fieldName,  $fieldInfos["value"], "category").'</td>
                    </tr>
                ';
            }elseif ($fieldInfos["rte"] == 2){
                $fieldsSource .= '
                    <tr class="row1">
                        <td width="200" valign="top">'.$fieldInfos["name"].'</td>
                        <td><textarea style="width: 30%; height: 100px;" name="'.$fieldName.'">'.$fieldInfos["value"].'</textarea></td>
                    </tr>
                ';
            }else{
                $fieldsSource .= '
                    <tr class="row1">
                        <td width="200" valign="top">'.$fieldInfos["name"].'</td>
                        <td><input type="text" name="'.$fieldName.'" value="'.$fieldInfos["value"].'" maxlength="250" style="width: 30%;" /></td>
                    </tr>
                ';
            }
        }

        return '
            <div id="'.$objId.'" class="addEntry" '.$objStyle.'>
                <table class="adminlist" align="top" border="0" cellpadding="3" cellspacing="0" width="100%">
                    <tr>
                        <th colspan="2">'.$title.'</th>
                    </tr>
                    '.$fieldsSource.'
                </table>
            </div>
        ';
    }

    /**
     * returns file-array
     *
     * @param $fileID
     * @global $objDatabase
     */
    function _FileInfo($fileID)
    {
        global $objDatabase, $objLanguage;

        $objResult = $objDatabase->SelectLimit("SELECT `file_id`, `file_name`, `file_type`, `file_size`, `file_source`, `file_img`, `file_autor`, `file_created`, `file_state`, `file_order`, `file_license`, `file_version`, `file_protected`, `file_access_id`, `file_url` FROM ".DBPREFIX."module_downloads_files WHERE file_id=".$fileID."", 1);
        if ($objResult !== false && $objResult->RecordCount() == 1) {

            $ArryObj = array();
            $ArryObj['file_id']         = $objResult->fields['file_id'];
            $ArryObj['file_name']       = $objResult->fields['file_name'];
            $ArryObj['file_type']       = $objResult->fields['file_type'];
            $ArryObj['file_size']       = $objResult->fields['file_size'];
            $ArryObj['file_source']     = $objResult->fields['file_source'];
            $ArryObj['file_img']        = $objResult->fields['file_img'];
            $ArryObj['file_autor']      = $objResult->fields['file_autor'];
            $ArryObj['file_created']    = $objResult->fields['file_created'];
            $ArryObj['file_state']      = $objResult->fields['file_state'];
            $ArryObj['file_order']      = $objResult->fields['file_order'];
            $ArryObj['file_license']    = $objResult->fields['file_license'];
            $ArryObj['file_version']    = $objResult->fields['file_version'];
            $ArryObj['file_protected']  = $objResult->fields['file_protected'];
            $ArryObj['file_access_id']  = $objResult->fields['file_access_id'];
            $ArryObj['file_url']        = $objResult->fields['file_url'];

            $query2 = "SELECT `file`, `language`
                        FROM ".DBPREFIX."module_downloads_files_lang WHERE `file`=".$fileID." ORDER BY `language`";

            $objResult2 = $objDatabase->Execute($query2);
            if ($objResult2){
                if (!isset($objLanguage)) {
                    $objLanguage = new FWLanguage();
                }

                $langCounter = 0;
                while (!$objResult2->EOF) {
                    // mit fortlaufendem id
                    $ArryObj['file_lang'][$langCounter]["id"] = $objResult2->fields['language'];
                    $ArryObj['file_lang'][$langCounter]["lang"] = $objLanguage->getLanguageParameter($objResult2->fields['language'], "lang");
                    $ArryObj['file_lang'][$langCounter]["name"] = $objLanguage->getLanguageParameter($objResult2->fields['language'], "name");

                    $objResult3 = $objDatabase->SelectLimit("SELECT `loc_id`, `loc_lang`, `loc_file`, `loc_name`, `loc_desc` FROM ".DBPREFIX."module_downloads_files_locales WHERE loc_lang=".$objResult2->fields['language']." AND loc_file=".$fileID."", 1);
                    if ($objResult3 !== false && $objResult3->RecordCount() == 1) {
                        $ArryObj['file_loc'][$langCounter]["name"] = $objResult3->fields['loc_name'];
                        $ArryObj['file_loc'][$langCounter]["desc"] = $objResult3->fields['loc_desc'];

                        // mit lang id
                        $ArryObj['file_loc']['lang'][$objResult2->fields['language']]["name"] = $objResult3->fields['loc_name'];
                        $ArryObj['file_loc']['lang'][$objResult2->fields['language']]["desc"] = $objResult3->fields['loc_desc'];
                    }

                    $langCounter++;
                    $objResult2->MoveNext();
                }
            }


            $query3 = "SELECT rel_file, rel_category
                        FROM ".DBPREFIX."module_downloads_rel_files_cat WHERE rel_file=".$fileID."";
            $objResult3 = $objDatabase->Execute($query3);
            $CatCounter = 0;
            if ($objResult3){
                while (!$objResult3->EOF) {
                    $ArryObj['file_categories'][$CatCounter]['id'] = $objResult3->fields['rel_category'];
                    $CatCounter++;
                    $objResult3->MoveNext();
                }
            }

            $query4 = "SELECT `rel_file`, `rel_related`
                        FROM ".DBPREFIX."module_downloads_rel_files_files WHERE `rel_file`=".$fileID."";
            $objResult4 = $objDatabase->Execute($query4);
            $RelatedCounter = 0;
            if ($objResult4){
                while (!$objResult4->EOF) {
                    $ArryObj['file_related_files'][$RelatedCounter]['id'] = $objResult4->fields['rel_related'];
                    $RelatedCounter++;
                    $objResult4->MoveNext();
                }
            }

            $query5 = "SELECT `access_id`, `group_id`
                        FROM ".DBPREFIX."access_group_dynamic_ids WHERE `access_id`=".$ArryObj['file_access_id']."";
            $objResult5 = $objDatabase->Execute($query5);
            $GroupsCounter = 0;
            if ($objResult5){
                while (!$objResult5->EOF) {
                    $ArryObj['file_access_groups'][$GroupsCounter]['id'] = $objResult5->fields['group_id'];
                    $GroupsCounter++;
                    $objResult5->MoveNext();
                }
            }

            return $ArryObj;

        }else{
            return 0;
        }

    }

    /**
     * returns category-array
     *
     * @param $category
     * @global $objDatabase
     */
    function _CategoryInfo($category)
    {
        global $objDatabase;
        $objResult = $objDatabase->SelectLimit("SELECT `category_id`, `category_img`, `category_author`, `category_created`, `category_state`, `category_order` FROM ".DBPREFIX."module_downloads_categories WHERE category_id=".$category."", 1);
        if ($objResult !== false && $objResult->RecordCount() == 1) {

            $ArryObj = array();
            $ArryObj['category_id']     = $objResult->fields['category_id'];
            $ArryObj['category_img']    = $objResult->fields['category_img'];
            $ArryObj['category_author'] = $objResult->fields['category_author'];
            $ArryObj['category_created']= $objResult->fields['category_created'];
            $ArryObj['category_state']  = $objResult->fields['category_state'];
            $ArryObj['category_order']  = $objResult->fields['category_order'];

            $query2 = "SELECT category, language
                        FROM ".DBPREFIX."module_downloads_cat_lang WHERE category=".$category." ORDER BY language";
            $objResult2 = $objDatabase->Execute($query2);

            if ($objResult2){
                if (!isset($objLanguage)) {
                    $objLanguage = new FWLanguage();
                }

                $langCounter = 0;
                while (!$objResult2->EOF) {
                    // mit fortlaufendem id
                    $ArryObj['category_lang'][$langCounter]["id"] = $objResult2->fields['language'];
                    $ArryObj['category_lang'][$langCounter]["lang"] = $objLanguage->getLanguageParameter($objResult2->fields['language'], "lang");
                    $ArryObj['category_lang'][$langCounter]["name"] = $objLanguage->getLanguageParameter($objResult2->fields['language'], "name");


                    $objResult3 = $objDatabase->SelectLimit("SELECT `loc_id`, `loc_lang`, `loc_cat`, `loc_name`, `loc_desc` FROM ".DBPREFIX."module_downloads_cat_locales WHERE loc_lang=".$objResult2->fields['language']." AND loc_cat=".$category."", 1);
                    if ($objResult3 !== false && $objResult3->RecordCount() == 1) {
                        $ArryObj['category_loc'][$langCounter]["name"] = $objResult3->fields['loc_name'];
                        $ArryObj['category_loc'][$langCounter]["desc"] = $objResult3->fields['loc_desc'];

                        // mit lang id
                        $ArryObj['category_loc']['lang'][$objResult2->fields['language']]["name"] = $objResult3->fields['loc_name'];
                        $ArryObj['category_loc']['lang'][$objResult2->fields['language']]["desc"] = $objResult3->fields['loc_desc'];
                    }

                    $langCounter++;
                    $objResult2->MoveNext();
                }
            }

            // Get Catpermissions
            // -------------------


            return $ArryObj;

        }else{
            return 0;
        }
    }

    /**
     * returns permissions/groups select-html
     *
     * @param $accessid
     * @global $objDatabase
     * @global $_ARRAYLANG
     */
    function _permissionsSelect($accessId='', $formName='')
    {
        global $objDatabase, $_ARRAYLANG;
        return '
            <table cellspacing="0" cellpaddng="0" border="0">
                <tr class="row1">
                    <td>
                        <select name="existingGroups[]" size="10" style="width:300px;" multiple="multiple">
                            <option value=""></option>
                        </select>
                        <br />
                        <font size="1">
                            <a href="javascript:void(0);" onclick="SelectAllList(document.'.$formName.'.elements[\'existingGroups[]\'])" style="color:blue;">{TXT_SELECT_ALL}</a><br />
                            <a href="javascript:void(0);" onclick="DeselectAllList(document.'.$formName.'.elements[\'existingGroups[]\'])" style="color:blue;">{TXT_DESELECT_ALL}</a>
                        </font>
                    </td>
                    <td>
                        <input type="button" value="&gt;&gt;" name="adduser" onclick="AddToTheList(document.'.$formName.'.elements[\'existingGroups[]\'],document.'.$formName.'.elements[\'assignedGroups[]\'],adduser,removeuser);" style="margin-bottom:1px;" /><br />
                        <input type="button" value="&lt;&lt;" name="removeuser" onclick="RemoveFromTheList(document.'.$formName.'.elements[\'existingGroups[]\'],document.'.$formName.'.elements[\'assignedGroups[]\'],adduser,removeuser);" />
                    </td>
                    <td>
                        <select name="assignedGroups[]" size="10" style="width:300px;" multiple="multiple">
                            <option value=""></option>
                        </select>
                        <br />
                        <font size="1">
                            <a href="javascript:void(0);" onclick="SelectAllList(document.'.$formName.'.elements[\'assignedGroups[]\'])" style="color:blue;">{TXT_SELECT_ALL}</a><br />
                            <a href="javascript:void(0);" onclick="DeselectAllList(document.'.$formName.'.elements[\'assignedGroups[]\'])" style="color:blue;">{TXT_DESELECT_ALL}</a>
                        </font>
                    </td>
                </tr>
            </table>
        ';
    }

    protected function GetCategory($categoryId, $active = null)
    {
        global $objDatabase, $objLanguage;

        $objFWUser = FWUser::getFWUserObject();

        if ($categoryId
            && ($objResult = $objDatabase->SelectLimit('
                SELECT
                    `id`,
                    `parent_id`,
                    `is_active`,
                    `visibility`,
                    `owner_id`,
                    `image`,
                    `order`,
                    `deletable_by_owner`,
                    `modify_access_by_owner`,
                    `read_access_id`,
                    `add_subcategories_access_id`,
                    `manage_subcategories_access_id`,
                    `add_files_access_id`,
                    `manage_files_access_id`
                FROM `'.DBPREFIX.'module_downloads_category`
                WHERE
                    `id` = '.$categoryId
                    .(isset($active) ? ' AND `is_active` = '.intval($active) : '')
                    .(
                        // if the user is a manager, then all categories are selected
                        Permission::checkAccess(142, 'static', true) ? ''
                        // if not, then only categories are selected that are either public, visible or the user has access permission to them
                        :   ' AND (`visibility` = 1 '.($objFWUser->objUser->login() ? ' OR `read_access_id` IN ('.implode(',', $objFWUser->objUser->getDynamicPermissionIds()).')' : '').')'
                     )
                , 1
            ))
            && $objResult->RecordCount()
        ) {
            $arrCategory = $objResult->fields;

            $objResult = $objDatabase->Execute('
                SELECT
                    `lang_id`,
                    `name`,
                    `description`
                FROM `'.DBPREFIX.'module_downloads_category_locale`
                WHERE `category_id` = '.$categoryId
            );
            if ($objResult) {
                while (!$objResult->EOF) {
                    $arrCategory['name'][$objResult->fields['lang_id']] = $objResult->fields['name'];
                    $arrCateogry['description'][$objResult->fields['lang_id']] = $objResult->fields['description'];
                    $objResult->MoveNext();
                }
            }
        } else {
            $arrCategory = array(
                'id'                                => 0,
                'parent_id'                         => 0,
                'is_active'                         => 1,
                'visibility'                        => 1,
                'owner_id'                          => $objFWUser->objUser->login() ? $objFWUser->objUser->getId() : 0,
                'image'                             => '',
                'order'                             => 0,
                'deletable_by_owner'                => 1,
                'modify_access_by_owner'            => 1,
                'read_access_id'                    => 0,
                'add_subcategories_access_id'       => 0,
                'manage_subcategories_access_id'    => 0,
                'add_files_access_id'               => 0,
                'manage_files_access_id'            => 0,
                'name'                              => array(),
                'description'                       => array()
             );
         }

         return $arrCategory;
    }

    /**
     * Return informations about categories.
     *
     * @param $active   Whether only active ($active=true) or inactive ($active=false) categories or all ($active=null) should be returned
     * @param $parentId Only the subcategories of the category spezified by $parentId will be returned.
     * @return mixed    Array on success, FALSE on failure
     */
    protected function GetCategories($active = null, $parentId = 0)
    {
        global $objDatabase, $_LANGID;

        $objFWUser = FWUser::getFWUserObject();
        // TODO: check for available languages first!
        $arrCategories = array();
        $objResult = $objDatabase->Execute('
            SELECT
                tblCat.`id`,
                tblCat.`parent_id`,
                tblCat.`is_active`,
                tblCat.`visibility`,
                tblCat.`owner_id`,
                tblCat.`order`,
                tblCat.`deletable_by_owner`,
                tblCat.`modify_access_by_owner`,
                tblCat.`read_access_id`,
                tblCat.`add_subcategories_access_id`,
                tblCat.`manage_subcategories_access_id`,
                tblCat.`add_files_access_id`,
                tblCat.`manage_files_access_id`,
                tblCat.`image`,
                tblLoc.`name`,
                tblLoc.`description`
            FROM `'.DBPREFIX.'module_downloads_category` AS tblCat
            INNER JOIN `'.DBPREFIX.'module_downloads_category_locale` AS tblLoc ON tblLoc.`category_id` = tblCat.`id`
            WHERE
                tblCat.`parent_id` = '.$parentId.'
                AND tblLoc.`lang_id` = '.$_LANGID
                .(isset($active) ? ' AND tblCat.`is_active` = '.intval($active) : '')
                .(
                    // if the user is a manager, then all categories are selected
                    Permission::checkAccess(142, 'static', true) ? ''
                    // if not, then only categories are selected that are either public, visible or the user has access permission to them
                    :   ' AND ( tblCat.`visibility` = 1 '.($objFWUser->objUser->login() ? ' OR tblCat.`read_access_id` IN ('.implode(',', $objFWUser->objUser->getDynamicPermissionIds()).')' : '').')'
                 )
             .' ORDER BY tblCat.`order`, tblLoc.`name`');
        if ($objResult === false) {
            return false;
        } else {
            while (!$objResult->EOF) {
                $arrCategories[] = $objResult->fields;
                $objResult->MoveNext();
            }
            return $arrCategories;
        }
    }



    protected function getParsedUsername($userId)
    {
        global $_ARRAYLANG;

        $objFWUser = FWUser::getFWUserObject();
        if ($objUser = $objFWUser->objUser->getUser($userId)) {
            if ($objUser->getProfileAttribute('firstname') || $objUser->getProfileAttribute('lastname')) {
                $author = $objUser->getProfileAttribute('firstname').' '.$objUser->getProfileAttribute('lastname').' ('.$objUser->getUsername().')';
            } else {
                $author = $objUser->getUsername();
            }
            $author = htmlentities($author, ENT_QUOTES, CONTREXX_CHARSET);
        } else {
            $author = $_ARRAYLANG['TXT_DOWNLOADS_UNKNOWN'];
        }

        return $author;
    }

    protected function getUserDropDownMenu($selectedUserId, $userId)
    {
        $menu = '<select name="downloads_category_owner_id" onchange="document.getElementById(\'downloads_category_owner_config\').style.display = this.value == '.$userId.' ? \'none\' : \'\'">';
        $objFWUser = FWUser::getFWUserObject();
        $objUser = $objFWUser->objUser->getUsers();
        while (!$objUser->EOF) {
            $menu .= '<option value="'.$objUser->getId().'"'.($objUser->getId() == $selectedUserId ? ' selected="selected"' : '').'>'.$this->getParsedUsername($objUser->getId()).'</option>';
            $objUser->next();
        }
        $menu .= '</select>';

        return $menu;
    }

    function _GetIconImage($img, $path=0, $imgname='')
    {
        if($imgname!=''){
            $imgname = 'name="'.$imgname.'" id="'.$imgname.'"';
        }

        if($path == 0){
            return '<img src="'.ASCMS_MODULE_WEB_PATH.'/downloads/images/icons/'.$this->_arrConfig["design"].'/'.$img.'" border="0" alt="" title="" '.$imgname.' />';
        }else{
            return '<img src="'.$img.'" border="0" alt="" title="" '.$imgname.' />';
        }
    }

    function _GetCategoriesOption($Category='', $width='150px')
    {
        global $objDatabase, $_LANGID, $_ARRAYLANG;

        if($Category!=''){
            $where = 'AND category_id='.$Category;
        }else{
            $where = '';
        }
        $Categories = $this->_GetCategories();
        $select = '<select name="category" style="width:'.$width.';">';
        $select .= '<option value=""></option>';

        for($x=0;$x<count($Categories); $x++){
            $CatInfo = $this->_CategoryInfo($Categories[$x]);

            if($Category!='' && intval($Category) == $Categories[$x]){
                $Selected = 'selected';
            }else{
                $Selected = '';
            }

            $select .= '<option value="'.$Categories[$x].'" '.$Selected.'>'.$CatInfo['category_loc']['lang'][$_LANGID]["name"].'</option>';
        }

        $select .= '</select>';
        return $select;
    }














}
?>
