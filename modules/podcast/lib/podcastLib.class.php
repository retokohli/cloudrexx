<?php
/**
 * Class podcast library
 *
 * podcast library class
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_podcast
 * @todo        Edit PHP DocBlocks!
 */

class podcastLib
{
	function _getMedia($ofCategory = false, $isActive = false)
	{
		global $objDatabase, $_CONFIG;

		$arrMedia = array();
		$pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;

		$objMedium = $objDatabase->SelectLimit("
			SELECT tblMedium.id,
				   tblMedium.title,
				   tblMedium.author,
				   tblMedium.description,
				   tblMedium.source,
				   tblMedium.width,
				   tblMedium.height,
				   tblMedium.playlenght,
				   tblMedium.size,
				   tblMedium.status,
				   tblMedium.date_added,
				   tblMedium.template_id
			FROM ".DBPREFIX."module_podcast_medium AS tblMedium".
			($ofCategory !== false ? ", ".DBPREFIX."module_podcast_rel_medium_category AS tblRel WHERE tblMedium.id=tblRel.medium_id AND tblRel.category_id=".$ofCategory : "").
			($isActive ? ($ofCategory !== false ? " AND " : " WHERE ")."tblMedium.status=1" : "").
			" ORDER BY tblMedium.date_added DESC", $_CONFIG['corePagingLimit'], $pos);
		if ($objMedium != false) {
			while (!$objMedium->EOF) {
				$arrMedia[$objMedium->fields['id']] = array(
					'title'			=> $objMedium->fields['title'],
					'author'		=> $objMedium->fields['author'],
					'description'	=> $objMedium->fields['description'],
					'source'		=> str_replace(array('%domain%', '%offset%'), array($_CONFIG['domainUrl'], ASCMS_PATH_OFFSET), $objMedium->fields['source']),
					'width'			=> $objMedium->fields['width'],
					'height'		=> $objMedium->fields['height'],
					'playlenght'	=> $objMedium->fields['playlenght'],
					'size'			=> $objMedium->fields['size'],
					'status'		=> $objMedium->fields['status'],
					'date_added'	=> $objMedium->fields['date_added'],
					'template_id'	=> $objMedium->fields['template_id']
				);

				$objMedium->MoveNext();
			}
			return $arrMedia;
		} else {
			return false;
		}
	}

	function _getMedium($mediumId, $isActive = false)
	{
		global $objDatabase, $_CONFIG;

		$objMedium = $objDatabase->SelectLimit("
			SELECT
				   title,
				   author,
				   description,
				   source,
				   width,
				   height,
				   playlenght,
				   size,
				   status,
				   date_added,
				   template_id
			FROM ".DBPREFIX."module_podcast_medium
			WHERE id=".$mediumId.
			($isActive ? " AND status=1" : ""), 1);
		if ($objMedium !== false && $objMedium->RecordCount() == 1) {
			$arrMedium = array(
				'title'			=> $objMedium->fields['title'],
				'author'		=> $objMedium->fields['author'],
				'description'	=> $objMedium->fields['description'],
				'source'		=> str_replace(array('%domain%', '%offset%'), array($_CONFIG['domainUrl'], ASCMS_PATH_OFFSET), $objMedium->fields['source']),
				'width'			=> $objMedium->fields['width'],
				'height'		=> $objMedium->fields['height'],
				'playlenght'	=> $objMedium->fields['playlenght'],
				'size'			=> $objMedium->fields['size'],
				'status'		=> $objMedium->fields['status'],
				'date_added'	=> $objMedium->fields['date_added'],
				'template_id'	=> $objMedium->fields['template_id'],
				'category'		=> array()
			);

			$objCategory = $objDatabase->Execute("SELECT category_id FROM ".DBPREFIX."module_podcast_rel_medium_category WHERE medium_id=".$mediumId);
			if ($objCategory !== false) {
				while (!$objCategory->EOF) {
					array_push($arrMedium['category'], $objCategory->fields['category_id']);
					$objCategory->MoveNext();
				}
			}

			return $arrMedium;
		} else {
			return false;
		}
	}

	/**
	 * Get media count
	 *
	 * Return the count of media in the system.
	 * If $ofCategory is specified, then the count of media of the specified category is returned.
	 *
	 * @param mixed $ofCategory
	 * @return mixed media count on successfull, false on failure
	 */
	function _getMediaCount($ofCategory = false, $isActive = false)
	{
		global $objDatabase;

		$objCount = $objDatabase->Execute("SELECT COUNT(1) AS media_count FROM ".DBPREFIX."module_podcast_medium AS tblMedium".
			($ofCategory !== false ? " ,".DBPREFIX."module_podcast_rel_medium_category AS tblRel WHERE tblRel.medium_id=tblMedium.id AND tblRel.category_id=".$ofCategory : "").
			($isActive ? ($ofCategory !== false ? " AND " : " WHERE ")."tblMedium.status=1" : "").
			($ofCategory !== false ? " GROUP BY tblRel.category_id" : ""));

		if ($objCount !== false) {
			return intval($objCount->fields['media_count']);
		} else {
			return false;
		}
	}

	function _getCategories($isActive = false, $limit = false, $langId = false)
	{
		global $objDatabase, $_CONFIG, $_LANGID;

		$arrCategory = array();

		if ($limit === false) {
			$objCategory = $objDatabase->Execute("SELECT id, title, description, status FROM ".DBPREFIX."module_podcast_category".($isActive ? " WHERE status=1" : "" ));
		} else {
			$pos = isset($_GET['pos']) ? intval($_GET['pos']) : 0;
			$objCategory = $objDatabase->SelectLimit("SELECT id, title, description, status FROM ".DBPREFIX."module_podcast_category".($isActive ? " WHERE status=1" : "" ), $_CONFIG['corePagingLimit'], $pos);
		}
		if ($objCategory !== false) {
			while (!$objCategory->EOF) {
				if ($langId !== false) {
					$arrLangIds = &$this->_getLangIdsOfCategory($objCategory->fields['id']);
					if (!in_array($_LANGID, $arrLangIds)) {
						$objCategory->MoveNext();
						continue;
					}
				}
				$arrCategory[$objCategory->fields['id']] = array(
					'title'			=> $objCategory->fields['title'],
					'description'	=> $objCategory->fields['description'],
					'status'		=> $objCategory->fields['status']
				);
				$objCategory->MoveNext();
			}

			return $arrCategory;
		} else {
			return false;
		}
	}

	function _getCategory($categoryId, $isActive = false)
	{
		global $objDatabase;

		$objCategory = $objDatabase->SelectLimit("SELECT title, description, status FROM ".DBPREFIX."module_podcast_category WHERE id=".$categoryId.($isActive ? " AND status=1" : ""), 1);
		if ($objCategory !== false && $objCategory->RecordCount() == 1) {
			$arrCategory = array(
				'title'			=> $objCategory->fields['title'],
				'description'	=> $objCategory->fields['description'],
				'status'		=> $objCategory->fields['status']
			);

			return $arrCategory;
		} else {
			return false;
		}
	}

	function _getCategoriesCount($isActive = false)
	{
		global $objDatabase;

		$objCount = $objDatabase->Execute("SELECT COUNT(1) AS categories_count FROM ".DBPREFIX."module_podcast_category".($isActive ? " WHERE status=1" : ""));
		if ($objCount !== false) {
			return $objCount->fields['categories_count'];
		} else {
			return false;
		}
	}

	function _getCategoriesMenu($selectedCategoryId = 0, $attrs = '', $areActive = false, $langId = false)
	{
		global $_ARRAYLANG;

		$menu = "<select ".$attrs.">\n";
		$menu .= "<option value=\"0\">".$_ARRAYLANG['TXT_PODCAST_SELECT_CATEGORY']."</option>\n";
		$menu .= "<option value=\"0\">".$_ARRAYLANG['TXT_PODCAST_ALL']."</option>\n";

		if (($arrCategories = &$this->_getCategories($areActive, false, $langId)) !== false && count($arrCategories) > 0) {

			foreach ($arrCategories as $categoryId => $arrCategory) {
				$menu .= "<option value=\"".$categoryId.($categoryId == $selectedCategoryId ? "\" selected=\"selected\"" : "\"").">".htmlentities($arrCategory['title'], ENT_QUOTES, CONTREXX_CHARSET)."</option>\n";
			}
			$menu .= "</select>\n";

			return $menu;
		} else {
			return false;
		}
	}

	function _isUniqueCategoryTitle($title, $categoryId)
	{
		global $objDatabase;

		$objCount = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_podcast_category WHERE title='".contrexx_addslashes($title)."' AND id!=".$categoryId, 1);
		if ($objCount !== false && $objCount->RecordCount() == 0) {
			return true;
		} else {
			return false;
		}
	}

	function _addCategory($title, $description, $arrLangIds, $status)
	{
		global $objDatabase;

		if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_podcast_category (`title`, `description`, `status`) VALUES ('".contrexx_addslashes($title)."', '".contrexx_addslashes($description)."', ".$status.")") !== false) {
			return $this->_setCategoryLangIds($objDatabase->Insert_ID(), $arrLangIds);
		} else {
			return false;
		}
	}

	function _updateCategory($id, $title, $description, $arrLangIds, $status)
	{
		global $objDatabase;

		if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_podcast_category SET `title`='".contrexx_addslashes($title)."', `description`='".contrexx_addslashes($description)."', `status`=".$status." WHERE id=".$id) !== false) {
			return $this->_setCategoryLangIds($id, $arrLangIds);
		} else {
			return false;
		}
	}

	function _deleteCategory($id)
	{
		global $objDatabase;

		if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_podcast_category WHERE id=".$id) !== false) {
			if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_podcast_rel_category_lang WHERE category_id=".$id) !== false) {
				return true;
			}
		}
		return false;
	}

	function _getLangIdsOfCategory($categoryId)
	{
		global $objDatabase;

		$arrLangIds = array();
		$objLang = $objDatabase->Execute("SELECT lang_id FROM ".DBPREFIX."module_podcast_rel_category_lang WHERE category_id=".$categoryId);
		if ($objLang !== false) {
			while (!$objLang->EOF) {
				array_push($arrLangIds, $objLang->fields['lang_id']);
				$objLang->MoveNext();
			}

			return $arrLangIds;
		} else {
			return false;
		}
	}

	function _setCategoryLangIds($categoryId, $arrLangIds)
	{
		global $objDatabase;

		$arrCurrentLangIds = array();

		$objLang = $objDatabase->Execute("SELECT lang_id FROM ".DBPREFIX."module_podcast_rel_category_lang WHERE category_id=".$categoryId);
		if ($objLang !== false) {
			while (!$objLang->EOF) {
				array_push($arrCurrentLangIds, $objLang->fields['lang_id']);
				$objLang->MoveNext();
			}

			$arrAddedLangIds = array_diff($arrLangIds, $arrCurrentLangIds);
			$arrRemovedLangIds = array_diff($arrCurrentLangIds, $arrLangIds);

			foreach ($arrAddedLangIds as $langId) {
				$objDatabase->Execute("INSERT INTO ".DBPREFIX."module_podcast_rel_category_lang (`category_id`, `lang_id`) VALUES (".$categoryId.", ".$langId.")");
			}

			foreach ($arrRemovedLangIds as $langId) {
				$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_podcast_rel_category_lang WHERE category_id=".$categoryId." AND lang_id=".$langId);
			}

			return true;
		} else {
			return false;
		}
	}

	function _addMedium($title, $author, $description, $source, $template, $width, $height, $playlenght, $size, $arrCategories, $status)
	{
		global $objDatabase;

		if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_podcast_medium (`title`, `author`,`description`, `source`, `template_id`, `width`, `height`, `playlenght`, `size`, `status`, `date_added`) VALUES ('".contrexx_addslashes($title)."', '".contrexx_addslashes($author)."','".contrexx_addslashes($description)."', '".contrexx_addslashes($source)."', ".$template.", ".$width.", ".$height.", ".$playlenght.", ".$size.", ".$status.", ".time().")") !== false) {
			return $this->_setMediumCategories($objDatabase->Insert_ID(), $arrCategories);
		} else {
			return false;
		}
	}

	function _updateMedium($id, $title, $author, $description, $template, $width, $height, $playlenght, $size, $arrCategories, $status)
	{
		global $objDatabase;

		if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_podcast_medium SET `title`='".contrexx_addslashes($title)."', `author`='".contrexx_addslashes($author)."',`description`='".contrexx_addslashes($description)."', `template_id`=".$template.", `width`=".$width.", `height`=".$height.", `playlenght`=".$playlenght.", `size`=".$size.", `status`=".$status." WHERE id=".$id) !== false) {
			return $this->_setMediumCategories($id, $arrCategories);
		} else {
			return false;
		}
	}

	function _deleteMedium($id)
	{
		global $objDatabase;

		if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_podcast_rel_medium_category WHERE medium_id=".$id) !== false) {
			if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_podcast_medium WHERE id=".$id) !== false) {
				return true;
			}
		}
		return false;
	}

	function _setMediumCategories($mediumId, $arrCategories)
	{
		global $objDatabase;

		$arrCurrentCategories = array();

		$objCategorie = $objDatabase->Execute("SELECT category_id FROM ".DBPREFIX."module_podcast_rel_medium_category WHERE medium_id=".$mediumId);
		if ($objCategorie !== false) {
			while (!$objCategorie->EOF) {
				array_push($arrCurrentCategories, $objCategorie->fields['category_id']);
				$objCategorie->MoveNext();
			}

			$arrAddedCategories = array_diff($arrCategories, $arrCurrentCategories);
			$arrRemovedCategories = array_diff($arrCurrentCategories, $arrCategories);

			foreach ($arrAddedCategories as $categoryId) {
				$objDatabase->Execute("INSERT INTO ".DBPREFIX."module_podcast_rel_medium_category (`medium_id`, `category_id`) VALUES (".$mediumId.", ".$categoryId.")");
			}

			foreach ($arrRemovedCategories as $categoryId) {
				$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_podcast_rel_medium_category WHERE medium_id=".$mediumId." AND category_id=".$categoryId);
			}

			return true;
		} else {
			return false;
		}
	}

	function _isUniqueMediumTitle($title, $mediumId)
	{
		global $objDatabase;

		$objCount = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_podcast_medium WHERE title='".contrexx_addslashes($title)."' AND id!=".$mediumId, 1);
		if ($objCount !== false && $objCount->RecordCount() == 0) {
			return true;
		} else {
			return false;
		}
	}

	function _getTemplates($limit = false, $limitPos = false)
	{
		global $objDatabase, $_CONFIG;

		$arrTemplates = array();
		if ($limit) {
			$objTemplate = $objDatabase->SelectLimit("SELECT id, description, template, extensions FROM ".DBPREFIX."module_podcast_template ORDER BY description", $_CONFIG['corePagingLimit'], $limitPos);
		} else {
			$objTemplate = $objDatabase->Execute("SELECT id, description, template, extensions FROM ".DBPREFIX."module_podcast_template ORDER BY description");
		}
		if ($objTemplate !== false) {
			while (!$objTemplate->EOF) {
				$arrTemplates[$objTemplate->fields['id']] = array(
					'description'	=> $objTemplate->fields['description'],
					'template'		=> $objTemplate->fields['template'],
					'extensions'	=> $objTemplate->fields['extensions']
				);
				$objTemplate->MoveNext();
			}
		}

		return $arrTemplates;
	}

	function _getTemplateCount()
	{
		global $objDatabase;

		$objCount = $objDatabase->SelectLimit("SELECT COUNT(1) AS template_count FROM ".DBPREFIX."module_podcast_template", 1);
		if ($objCount !== false && $objCount->RecordCount() == 1) {
			return $objCount->fields['template_count'];
		} else {
			return false;
		}
	}

	function _isTemplateInUse($templateId)
	{
		global $objDatabase;

		$objCount = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_podcast_medium WHERE template_id=".$templateId, 1);
		if ($objCount !== false && $objCount->RecordCount() == 0) {
			return false;
		} else {
			return true;
		}
	}

	function _getTemplate($templateId)
	{
		global $objDatabase;

		$objTemplate = $objDatabase->SelectLimit("SELECT description, template, extensions FROM ".DBPREFIX."module_podcast_template WHERE id=".$templateId, 1);
		if ($objTemplate !== false && $objTemplate->RecordCount() == 1) {
			return array(
				'description'	=> $objTemplate->fields['description'],
				'template'		=> $objTemplate->fields['template'],
				'extensions'	=> $objTemplate->fields['extensions']
			);
		} else {
			return false;
		}
	}

	function _getTemplateMenu($selectedTemplateId, $attrs = '')
	{
		$arrTemplates = &$this->_getTemplates();

		$menu = "<select".(!empty($attrs) ? " ".$attrs : "").">\n";
		foreach ($arrTemplates as $templateId => $arrTemplate) {
			$menu .= "<option value=\"".$templateId."\" ".($templateId == $selectedTemplateId ? 'selected="selected"' : '').">".$arrTemplate['description']." (".$arrTemplate['extensions'].")</option>\n";
		}
		$menu .= "</select>";

		return $menu;
	}

	function _isUniqueTemplateDescription($templateId, $description)
	{
		global $objDatabase;

		$objCount = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_podcast_template WHERE description='".contrexx_addslashes($description)."' AND id!=".$templateId, 1);
		if ($objCount !== false && $objCount->RecordCount() == 0) {
			return true;
		} else {
			return false;
		}
	}

	function _updateTemplate($templateId, $description, $template, $extensions)
	{
		global $objDatabase;

		if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_podcast_template SET `description`='".contrexx_addslashes($description)."', template='".contrexx_addslashes($template)."', extensions='".contrexx_addslashes($extensions)."' WHERE id=".$templateId) !== false) {
			return true;
		} else {
			return false;
		}
	}

	function _addTemplate($description, $template, $extensions)
	{
		global $objDatabase;

		if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_podcast_template (`description`, `template`, `extensions`) VALUES ('".contrexx_addslashes($description)."', '".contrexx_addslashes($template)."', '".contrexx_addslashes($extensions)."')") !== false) {
			return true;
		} else {
			return false;
		}
	}

	function _getSuitableTemplate($fileName)
	{
		global $objDatabase;

		if (($extension = substr($fileName, strrpos($fileName, '.')+1)) !== false) {
			$objTemplate = $objDatabase->SelectLimit("SELECT id FROM ".DBPREFIX."module_podcast_template WHERE extensions LIKE '%".contrexx_addslashes($extension)."%'", 1);
			if ($objTemplate !== false && $objTemplate->RecordCount() == 1) {
				return $objTemplate->fields['id'];
			}
		}

		return false;
	}

	function _deleteTemplate($templateId)
	{
		global $objDatabase;

		if (!$this->_isTemplateInUse($templateId)) {
			if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_podcast_template WHERE id=".$templateId) !== false) {
				return true;
			}
		}
		return false;
	}

	function _getHtmlTag($arrMedium, $template)
	{
		$heightSpacer = '[[MEDIUM_HEIGHT]]';
		$widthSpacer = '[[MEDIUM_WIDTH]]';
		$modifiedWidth = 0;
		$modifiedHeight = 0;

		if (preg_match('/\[\[MEDIUM_HEIGHT\]\](\s*\+\s*([0-9]+))/', $template, $arrMatches)) {
			$modifiedHeight = $arrMedium['height'] + $arrMatches[2];
			$heightSpacer .= $arrMatches[1];
		}
		if (preg_match('/(([0-9]+)\s*\+\s*)\[\[MEDIUM_HEIGHT\]\]/', $template, $arrMatches)) {
			$modifiedHeight = $arrMedium['height'] + $arrMatches[2];
			$heightSpacer = $arrMatches[1].$heightSpacer;
		}
		if ($modifiedHeight > 0) {
			$template = str_replace($heightSpacer, $modifiedHeight, $template);
		}

		if (preg_match('/\[\[MEDIUM_WIDTH\]\](\s*\+\s*([0-9]+))/', $template, $arrMatches)) {
			$modifiedWidth = $arrMedium['width'] + $arrMatches[2];
			$widthSpacer .= $arrMatches[1];
		}
		if (preg_match('/(([0-9]+)\s*\+\s*)\[\[MEDIUM_WIDTH\]\]/', $template, $arrMatches)) {
			$modifiedWidth = $arrMedium['width'] + $arrMatches[2];
			$widthSpacer = $arrMatches[1].$widthSpacer;
		}
		if ($modifiedWidth > 0) {
			$template = str_replace($widthSpacer, $modifiedWidth, $template);
		}

		return str_replace(
			array('[[MEDIUM_WIDTH]]', '[[MEDIUM_HEIGHT]]', '[[MEDIUM_URL]]'),
			array($arrMedium['width'], $arrMedium['height'], $arrMedium['source']),
			$template);
	}

	function _getShortPlaylenghtFormatOfTimestamp($timestamp)
	{
		return sprintf('%02u', floor($timestamp / 3600)).":".sprintf('%02u', floor(($timestamp % 3600) / 60)).":".sprintf('%02u', $timestamp % 60);
	}

	function _getPlaylenghtFormatOfTimestamp($timestamp)
	{
		global $_ARRAYLANG;

		$format = '';

		if ($timestamp > 0) {
			$hours = floor($timestamp / 3600);
			$minutes = floor(($timestamp % 3600) / 60);
			$seconds =  $timestamp % 60;

			if ($hours > 0) {
				$format .= $hours." ".($hours > 1 ? $_ARRAYLANG['TXT_PODCAST_HOURS'] : $_ARRAYLANG['TXT_PODCAST_HOUR']);
			}
			if ($minutes > 0) {
				$format .= ($hours > 0 ? " " : "").$minutes." ".($minutes > 1 ? $_ARRAYLANG['TXT_PODCAST_MINUTES'] : $_ARRAYLANG['TXT_PODCAST_MINUTE']);
			}
			if ($seconds > 0) {
				$format .= (($hours > 0 || $minutes > 0) ? " " : "").$seconds." ".($seconds > 1 ? $_ARRAYLANG['TXT_PODCAST_SECONDS'] : $_ARRAYLANG['TXT_PODCAST_SECOND']);
			}
		} else {
			$format = '-';
		}

		return $format;
	}

	function _getSettings()
	{
		global $objDatabase;

		$arrSettings = array();
		$objSettings = $objDatabase->Execute("SELECT setname, setvalue FROM ".DBPREFIX."module_podcast_settings");
		if ($objSettings !== false) {
			while (!$objSettings->EOF) {
				$arrSettings[$objSettings->fields['setname']] = $objSettings->fields['setvalue'];
				$objSettings->MoveNext();
			}
		}

		return $arrSettings;
	}

	function _updateSettings($arrSettings)
	{
		global $objDatabase;

		$status = true;

		foreach ($arrSettings as $key => $value) {
			if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_podcast_settings SET `setvalue`='".$value."' WHERE `setname`='".$key."'") === false) {
				$status = false;
			}
		}

		return $status;
	}

	function _formatFileSize($size)
	{
		global $_ARRAYLANG;

		$exp = log($size, 1024);

		if ($exp < 1) {
			return $size.' '.$_ARRAYLANG['TXT_PODCAST_BYTES'];
		} elseif ($exp < 2) {
			return round($size/1024, 2).' '.$_ARRAYLANG['TXT_PODCAST_KBYTE'];
		} elseif ($exp < 3) {
			return round($size/pow(1024, 2), 2).' '.$_ARRAYLANG['TXT_PODCAST_MBYTE'];
		} else {
			return round($size/pow(1024, 3), 2).' '.$_ARRAYLANG['TXT_PODCAST_GBYTE'];
		}
	}
}
?>
