<?php
/**
 * Gallery home content
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_gallery
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/gallery/Lib.class.php';

/**
 * Gallery home content
 *
 * Show Gallery Block Content (Random, Last)
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author      Astalavista Development Team <thun@astalvista.ch>
 * @access      public
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  module_gallery
 */
class GalleryHomeContent extends GalleryLibrary {
	var $_intLangId;
	var $_strWebPath;
	
	/**
	* Constructor php5
	*/
	function __construct() {
		global $_LANGID;
		
		$this->getSettings();
	    $this->_intLangId 	= $_LANGID;
		$this->_strWebPath 	= ASCMS_GALLERY_THUMBNAIL_WEB_PATH . '/';
	}
	
	/**
	 * Constructor php4
	 */
    function GalleryHomeContent() {
    	$this->__construct();    	
	}
	
	/**
	 * Check if the random-function is activated
	 *
	 * @return boolean
	 */
	function checkRandom() {
		if ($this->arrSettings['show_random'] == 'on') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Check if the latest-function is activated
	 *
	 * @return boolean
	 */
	function checkLatest() {
		if ($this->arrSettings['show_latest'] == 'on') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns an randomized image from database
	 *
	 * @global	array	$_CONFIG
	 * @global 	array	$_ARRAYLANG
	 * @global 	object	$objDatabase
	 * @return 	string 	Complete <img>-tag for a randomized image
	 */
	function getRandomImage() {
		global $objDatabase, $_CONFIG, $_ARRAYLANG;
		
		$objResult = $objDatabase->Execute('SELECT		pics.id as id
											FROM		'.DBPREFIX.'module_gallery_pictures		AS pics
											INNER JOIN	'.DBPREFIX.'module_gallery_categories 	AS categories	ON categories.id = pics.catid
											WHERE	categories.status="1" AND
													pics.validated="1" AND
													pics.status="1"
										');
		
		if ($objResult->RecordCount() == 0) {
			return '';
		} else {
			$arrValues = array();
			while (!$objResult->EOF) {
				$arrValues[count($arrValues)] = $objResult->fields['id'];
				$objResult->MoveNext();
			}
						
			mt_srand((double)microtime()*1000000);
			$intRandomId = $arrValues[mt_rand(0,count($arrValues)-1)];

			$objResult = $objDatabase->Execute('SELECT		pics.catid	AS CATID,
															pics.path	AS PATH,
															lang.name	AS NAME
												FROM		'.DBPREFIX.'module_gallery_pictures 		AS pics
												INNER JOIN	'.DBPREFIX.'module_gallery_language_pics 	AS lang	ON pics.id = lang.picture_id												
												WHERE	pics.id='.$intRandomId.'	AND
														lang.lang_id = '.$this->_intLangId.'
												LIMIT	1
											');
			
			if ($objResult->RecordCount() == 1) {
				$strReturn = 	'<a href="?section=gallery&amp;cid='.$objResult->fields['CATID'].'" target="_self">';
				$strReturn .=	'<img border="0" alt="'.$objResult->fields['NAME'].'" title="'.$objResult->fields['NAME'].'" src="'.$this->_strWebPath.$objResult->fields['PATH'].'" /></a>';
				return $strReturn;
			} else {
				return '';
			}
		}
	}
	
	
	/**
	 * Returns the last inserted image from database
	 *
	 * @global	array	$_CONFIG
	 * @global 	array	$_ARRAYLANG
	 * @global 	object	$objDatabase
	 * @return 	string 	Complete <img>-tag for a randomized image
	 */
	function getLastImage() {
		global $objDatabase, $_CONFIG, $_ARRAYLANG;
				
		$objResult = $objDatabase->Execute('SELECT		pics.catid	AS CATID,
														pics.path	AS PATH,
														lang.name	AS NAME
											FROM		'.DBPREFIX.'module_gallery_pictures 		AS pics
											INNER JOIN	'.DBPREFIX.'module_gallery_language_pics 	AS lang 		ON pics.id = lang.picture_id
											INNER JOIN 	'.DBPREFIX.'module_gallery_categories 		AS categories 	ON pics.catid = categories.id										
											WHERE		categories.status = "1"		AND
														pics.validated = "1"		AND
														pics.status = "1"			AND
														lang.lang_id = '.$this->_intLangId.'
											ORDER BY	pics.id DESC
											LIMIT		1
										');
		
		if ($objResult->RecordCount() == 1) {
			$strReturn = 	'<a href="?section=gallery&amp;cid='.$objResult->fields['CATID'].'" target="_self">';
			$strReturn .=	'<img border="0" alt="'.$objResult->fields['NAME'].'" title="'.$objResult->fields['NAME'].'" src="'.$this->_strWebPath.$objResult->fields['PATH'].'" /></a>';
			return $strReturn;
		} else {
			return '';
		}
	}
}

?>