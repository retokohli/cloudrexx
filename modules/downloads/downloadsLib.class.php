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
	
	var $_arrConfig = array();
	var $_arrLang 	= array();
	
	function DownloadsLibrary() 
	{
		$this->__constructor();
	}
	
	function __constructor() 
	{
		$this->_init();
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
					'lang'			=> $objResult->fields['lang'],
					'name'			=> $objResult->fields['name'],
					'frontend'		=> $objResult->fields['frontend'],
					'is_default'	=> $objResult->fields['is_default']);
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
		global $objDatabase;
		global $objDatabase;

		$objResult = $objDatabase->SelectLimit("SELECT `file_id`, `file_name`, `file_type`, `file_size`, `file_source`, `file_img`, `file_autor`, `file_created`, `file_state`, `file_order`, `file_license`, `file_version`, `file_protected`, `file_access_id`, `file_url` FROM ".DBPREFIX."module_downloads_files WHERE file_id=".$fileID."", 1);
		if ($objResult !== false && $objResult->RecordCount() == 1) {
			
			$ArryObj = array();
			$ArryObj['file_id']			= $objResult->fields['file_id'];
			$ArryObj['file_name']		= $objResult->fields['file_name'];
			$ArryObj['file_type']		= $objResult->fields['file_type'];
			$ArryObj['file_size']		= $objResult->fields['file_size'];
			$ArryObj['file_source']		= $objResult->fields['file_source'];
			$ArryObj['file_img']		= $objResult->fields['file_img'];
			$ArryObj['file_autor']		= $objResult->fields['file_autor'];
			$ArryObj['file_created']	= $objResult->fields['file_created'];
			$ArryObj['file_state']		= $objResult->fields['file_state'];
			$ArryObj['file_order']		= $objResult->fields['file_order'];
			$ArryObj['file_license']	= $objResult->fields['file_license'];
			$ArryObj['file_version']	= $objResult->fields['file_version'];
			$ArryObj['file_protected']	= $objResult->fields['file_protected'];
			$ArryObj['file_access_id']	= $objResult->fields['file_access_id'];
			$ArryObj['file_url']		= $objResult->fields['file_url'];
			
	        $query2 = "SELECT `file`, `language`
	            		FROM ".DBPREFIX."module_downloads_files_lang WHERE `file`=".$fileID." ORDER BY `language`";

	        $objResult2 = $objDatabase->Execute($query2);
	        if ($objResult2){
	        	$langCounter = 0;
	        	while (!$objResult2->EOF) {
	        		// mit fortlaufendem id
	        		$ArryObj['file_lang'][$langCounter]["id"] = $objResult2->fields['language'];
	        		$ArryObj['file_lang'][$langCounter]["lang"] = $this->_GetLangInfo($objResult2->fields['language'], "lang");
	        		$ArryObj['file_lang'][$langCounter]["name"] = $this->_GetLangInfo($objResult2->fields['language'], "name");
	        		
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
			$ArryObj['category_id']		= $objResult->fields['category_id'];
			$ArryObj['category_img']	= $objResult->fields['category_img'];
			$ArryObj['category_author']	= $objResult->fields['category_author'];
			$ArryObj['category_created']= $objResult->fields['category_created'];
			$ArryObj['category_state']	= $objResult->fields['category_state'];
			$ArryObj['category_order']	= $objResult->fields['category_order'];
			
			$query2 = "SELECT category, language
	            		FROM ".DBPREFIX."module_downloads_cat_lang WHERE category=".$category." ORDER BY language";
	        $objResult2 = $objDatabase->Execute($query2);
	       
	        if ($objResult2){
	        	$langCounter = 0;
	        	while (!$objResult2->EOF) {
	        		// mit fortlaufendem id
	        		$ArryObj['category_lang'][$langCounter]["id"] = $objResult2->fields['language'];
	        		$ArryObj['category_lang'][$langCounter]["lang"] = $this->_GetLangInfo($objResult2->fields['language'], "lang");
	        		$ArryObj['category_lang'][$langCounter]["name"] = $this->_GetLangInfo($objResult2->fields['language'], "name");
	        		
	        		
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
	 * returns langinfo
	 *
	 * @param $id
	 * @param $field
	 * @global $objDatabase
	 */
	function _GetLangInfo($id, $field)
	{
		global $objDatabase;
		$objResult = $objDatabase->SelectLimit("SELECT `".$field."` FROM ".DBPREFIX."languages WHERE id=".$id."", 1);
		if ($objResult !== false && $objResult->RecordCount() == 1) {
			return $objResult->fields[$field];
		}else{
			return "";
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
	
	function _getAllGroups($groupType="frontend")
	{
		global $objDatabase;

        if ($groupType!="frontend") {
        	$groupType="backend";
        }

	    $arrGroups=array();
		$objResult = $objDatabase->Execute("SELECT group_id, group_name FROM ".DBPREFIX."access_user_groups WHERE type='".$groupType."'");
		if ($objResult !== false) {
	    	while (!$objResult->EOF) {
	    		$arrGroups[$objResult->fields['group_id']]=$objResult->fields['group_name'];
	    		$objResult->MoveNext();
	    	}
		}
    	return $arrGroups;
	}
	
	function _GetCategories($where = '')
	{
		global $objDatabase;
		$arrCategories=array();
		$objResult = $objDatabase->Execute("SELECT category_id FROM ".DBPREFIX."module_downloads_categories WHERE category_state=1 ".$where);
		if ($objResult !== false) {
			$counter = 0;
	    	while (!$objResult->EOF) {
	    		$arrCategories[$counter] = $objResult->fields['category_id'];
	    		$counter ++;
	    		$objResult->MoveNext();
	    	}
		}
    	return $arrCategories;
		
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
