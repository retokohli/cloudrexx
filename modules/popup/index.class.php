<?php

/**
 * Block
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_block
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/popup/lib/popupLib.class.php';

/**
 * Block
 *
 * block module class
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_block
 */
class popup extends popupLibrary
{
	var $jsGeneral;

	/**
	* Set popup
	*
	* Parse popup placeholder
	*
	* @access public
	* @param string &$code
	*/
	function setPopup(&$code, $pageId)
	{
		global $objDatabase, $_LANGID;

		$this->jsGeneral .= "function closeLayer(e)
							 {
								layer = document.getElementById(e);
								layer.style.display = 'none';
							 }
							";

		/*$this->jsGeneral .= "function openPopup(href, title, resizable, adress, menu, scrollbars, status, width, height, left, top)
							 {
								window.open(href,title,'resizable='+resizable+',location='+adress+',menubar='+menu+',scrollbars='+scrollbars+',status='+status+',toolbar='+menu+',fullscreen=no,dependent=no,width='+width+',height='+height+',left='+left+',top='+top+'');
							 }
							";*/

		$popups=$this->getPopups($pageId);

		$code = str_replace("{POPUP}", $popups, $code);
	}

	/**
	* Set popup
	*
	* get popups general
	*
	* @access public
	* @param string $type
	* @param string $pageId
	*
	*/
	function getPopups($pageId)
	{
		global $objDatabase, $_LANGID;

		$arrSettings 	= $this->_getSettings();
		$today 			= date("Y-m-d");

		$query = "SELECT popup_id FROM ".DBPREFIX."module_popup_rel_pages WHERE popup_id!=''";
		$objCheck = $objDatabase->SelectLimit($query, 1);

		if ($objCheck) {
		    if ($objCheck->RecordCount() == 0) {
    			$tables = DBPREFIX."module_popup_rel_lang AS tblLang";
    			$where	= "";
    		} else {
    			$tables = DBPREFIX."module_popup_rel_lang AS tblLang,
    					".DBPREFIX."module_popup_rel_pages AS tblPage";
    			$where	= "AND 	((tblPage.page_id=".intval($pageId)." AND tblPage.popup_id=tblPopup.id) OR tblLang.all_pages='1')";
    		}
		} else {
		    return false;
		}

		$objPopup = $objDatabase->Execute("	SELECT 		tblPopup.id,
														tblPopup.type,
														tblPopup.name,
														tblPopup.content,
														tblPopup.width,
														tblPopup.height,
														tblPopup.top,
														tblPopup.left,
														tblPopup.scrollbars,
														tblPopup.adress_list,
														tblPopup.menu_list,
														tblPopup.status_list,
														tblPopup.resizeable,
														tblPopup.start,
														tblPopup.end
												FROM 	".DBPREFIX."module_popup AS tblPopup,
														".$tables."
												WHERE 	(tblLang.lang_id=".$_LANGID." AND tblLang.popup_id=tblPopup.id)
														".$where."
												AND 	tblPopup.active=1
												GROUP 	BY tblPopup.id
												");

		$popups = ''; // if (!$objPopup) return UNDEF without this!

		if ($objPopup !== false) {
			while (!$objPopup->EOF) {
				$start	= $objPopup->fields['start'];
				$end	= $objPopup->fields['end'];
				if ($start <= $today && ($end >= $today || $end == "0000-00-00")) {
					$content 		= $objPopup->fields['content'];
					$id	 			= $objPopup->fields['id'];
					$title	 		= $objPopup->fields['name'];
					$width			= $objPopup->fields['width'];
					$height			= $objPopup->fields['height'];
					$left			= $objPopup->fields['left'];
					$top			= $objPopup->fields['top'];
					$scrollbars		= $objPopup->fields['scrollbars'];
					$resizable		= $objPopup->fields['resizeable'];
					$status			= $objPopup->fields['status_list'];
					$menu			= $objPopup->fields['menu_list'];
					$adress			= $objPopup->fields['adress_list'];
					$type			= $objPopup->fields['type'];
					$borderSize		= $arrSettings['border_size'];
					$borderColor	= $arrSettings['border_color'];

					switch ($type) {
						case 1:
							$scrollbars 	= $scrollbars == 0 ? "no" : "yes";
							$resizable 		= $resizable == 0 ? "no" : "yes";
							$status 		= $status == 0 ? "no" : "yes";
							$menu 			= $menu == 0 ? "no" : "yes";
							$adress 		= $adress == 0 ? "no" : "yes";
							$name 			= str_replace(" ", "_", $title);
							$popups		   .= '<script language="JavaScript" type="text/javascript">
											   <!--
											  ';
							//$popups		   .= "this.openPopup('modules/popup/frontendPopup.php?id=".$id."&title=".$title."', '".$name."', '".$resizable."', '".$adress."', '".$menu."', '".$scrollbars."', '".$status."', '".$width."', '".$height."', '".$left."', '".$top."');";
							$popups		   .= "popup_".$id." = window.open('modules/popup/frontendPopup.php?id=".$id."&title=".$title."','".$name."','resizable=".$resizable.",location=".$adress.",menubar=".$menu.",scrollbars=".$scrollbars.",status=".$status.",toolbar=".$adress.",fullscreen=no,dependent=no,width=".$width.",height=".$height.",left=".$left.",top=".$top."')";
							$popups		   .= '
											  -->
											  </script>';
							break;

						case 2:
							$scrollbars 	= $scrollbars == 0 ? "hidden" : "scroll";

							$widthClose		= $objPopup->fields['width']+($arrSettings['border_size']*2);

							$popups 	.= '<div id="popupLayer_'.$id.'" style="position:absolute; left: '.$left.'px; top: '.$top.'px; z-index: 9999;">
											<div align="right" id="popupLayerClose_'.$id.'" style="width: '.$widthClose.'px; padding: 0px 0px 0px 0px; margin; 0px 0px 0px 0px; height: 11px;">
												<img onclick="closeLayer(\'popupLayer_'.$id.'\');" src="images/modules/popup/close.gif" alt="Close" title="Close" border="0" height="11" style="cursor:pointer; padding: 0px 0px 0px 0px; margin; 0px 0px 0px 0px;" />
											</div>
											<div id="popupLayerBody_'.$id.'" style="background-color: #ffffff; overflow: '.$scrollbars.'; width: '.$width.'px ; height: '.$height.'px; padding: 0px 0px 0px 0px; margin; 0px 0px 0px 0px; border: '.$borderSize.'px solid '.$borderColor.';">
												'.$content.'
											</div>
										</div>
									   ';
							break;

						case 3:
							break;
					}


				}

				$objPopup->MoveNext();
			}
		}

		return $popups;
	}


	/**
	* Set js
	*
	* parse general js for popus
	*
	* @access private
	* @param string &$code
	* @global object $objDatabase
	*/
	function _setJS(&$code)
	{
		global $objDatabase, $_LANGID;

		$jsHeader	=	'<script language="JavaScript" type="text/javascript">
						<!--
						';
		$jsFooter	=	'
						-->
						</script>';

		$JS 		=	$jsHeader.$this->jsGeneral.$jsFooter;


		$code = str_replace("{POPUP_JS_FUNCTION}", $JS, $code);
	}
}

?>
