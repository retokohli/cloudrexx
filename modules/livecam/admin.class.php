<?php
/**
 * Livecam
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_livecam
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Includes
 */
require_once ASCMS_MODULE_PATH.'/livecam/lib/livecamLib.class.php';

/**
 * Livecam
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  module_livecam
 */
class LivecamManager extends LivecamLibrary
{
	var $_objTpl;
	var $_pageTitle;
	var $_strErrMessage = '';
	var $_strOkMessage = '';

	/**
	* Constructor
	*/
	function LivecamManager()
	{
		$this->__construct();
	}

	/**
	* PHP5 constructor
	*
	* @global object $objTemplate
	* @global array $_ARRAYLANG
	*/
	function __construct()
	{

		global $objTemplate, $_ARRAYLANG, $_CONFIG;

		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_MODULE_PATH.'/livecam/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

		$this->getSettings();

		if (isset($_POST['saveSettings'])) {
			$arrSettings = array(
				'blockStatus'	=> isset($_POST['blockUseBlockSystem']) ? intval($_POST['blockUseBlockSystem']) : 0
			);
			$this->_saveSettings($arrSettings);
		}

    	$objTemplate->setVariable("CONTENT_NAVIGATION", "<a href='index.php?cmd=livecam'>".$_ARRAYLANG['TXT_SETTINGS']."</a>");

	}

	/**
	 * Get page
	 *
	 * Get a page of the block system administration
	 *
	 * @access public
	 * @global object $objTemplate
	 */
	function getPage()
	{
		global $objTemplate, $_CONFIG;

		if (!isset($_REQUEST['act'])) {
			$_REQUEST['act'] = '';
		}

		switch ($_REQUEST['act']) {
			case 'settings':
				$this->_saveSettings();
				$this->_showSettings();
			break;
			default:
				$this->_showSettings();
		}

		$objTemplate->setVariable(array(
			'CONTENT_TITLE'				=> $this->_pageTitle,
			'CONTENT_OK_MESSAGE'		=> $this->_strOkMessage,
			'CONTENT_STATUS_MESSAGE'	=> $this->_strErrMessage,
			'ADMIN_CONTENT'				=> $this->_objTpl->get()
		));
	}

	/**
	 * Show settings
	 *
	 * Show the settings page
	 *
	 * @access private
	 * @global array $_ARRAYLANG
	 */
	function _showSettings()
	{
		global $_ARRAYLANG, $_CONFIG, $_CORELANG;

		$this->_pageTitle = $_ARRAYLANG['TXT_SETTINGS'];
		$this->_objTpl->loadTemplateFile('module_livecam_settings.html');

		if($this->arrSettings['lightboxActivate'] == 1) {
			$lightboxActive = 'checked="checked"';
			$lightboxInctive = '';
		} else {
			$lightboxActive = '';
			$lightboxInctive = 'checked="checked"';
		}

		$this->_objTpl->setVariable(array(
			'TXT_SETTINGS'			=> $_ARRAYLANG['TXT_SETTINGS'],
			'TXT_CURRENT_IMAGE_URL'	=> $_ARRAYLANG['TXT_CURRENT_IMAGE_URL'],
			'CURRENT_IMAGE_URL'		=> $this->arrSettings['currentImageUrl'],
			'TXT_ARCHIVE_PATH'		=> $_ARRAYLANG['TXT_ARCHIVE_PATH'],
			'ARCHIVE_PATH'			=> $this->arrSettings['archivePath'],
			'TXT_SAVE'				=> $_ARRAYLANG['TXT_SAVE'],
			'TXT_THUMBNAIL_PATH'	=> $_ARRAYLANG['TXT_THUMBNAIL_PATH'],
			'THUMBNAIL_PATH'		=> $this->arrSettings['thumbnailPath'],
			'TXT_LIGHTBOX_ACTIVE'	=> $_CORELANG['TXT_ACTIVATED'],
			'TXT_LIGHTBOX_INACTIVE'	=> $_CORELANG['TXT_DEACTIVATED'],
			'TXT_ACTIVATE_LIGHTBOX'	=> $_ARRAYLANG['TXT_ACTIVATE_LIGHTBOX'],
			'LIGHTBOX_ACTIVE'		=> $lightboxActive,
			'LIGHTBOX_INACTIVE'		=> $lightboxInctive
		));

		$this->_objTpl->setVariable('BLOCK_USE_BLOCK_SYSTEM', $_CONFIG['blockStatus'] == '1' ? 'checked="checked"' : '');
	}


	/**
	 * Save Settings
	 *
	 * @access private
	 * @global objDatabase, $_ARRAYLANG
	 */
	function _saveSettings()
	{
		global $objDatabase, $_ARRAYLANG, $_CORELANG;

		$currentImageUrl = $_POST['currentImageUrl'];
		$archivePath = $_POST['archivePath'];
		$thumbnailPath = $_POST['thumbnailPath'];
		$lightboxStatus = $_POST['activateLightbox'];

		$error = false;

		if (empty($currentImageUrl) || empty($archivePath) || empty($thumbnailPath)) {
			$this->_statusMessage =  $_ARRAYLANG['TXT_EMPTY_FIELDS'];
		} else {
			if(!$this->_save("currentImageUrl", $currentImageUrl)) {
				$error = true;
			}

			if (!$this->_save("archivePath", $archivePath)) {
				$error = true;
			}

			if (!$this->_save("thumbnailPath", $thumbnailPath)) {
				$error = true;
			}

			if (!$this->_save("lightboxActivate", $lightboxStatus)) {
				$error = true;
			}
		}

		if ($error) {
			$this->_strErrMessage = $_ARRAYLANG['TXT_UPDATE_FAILED'];
		} else {
			$this->_strOkMessage = $_CORELANG['TXT_SETTINGS_UPDATED'];
		}

		$this->getSettings();
	}

	/**
	 * Save
	 *
	 * Saves one option
	 *
	 * @access private
	 * @global objDatabase
	 */
	function _save($setname, $setval)
	{
		global $objDatabase;

		$setval = addslashes($setval);
		$setname = addslashes($setname);

		$query = "UPDATE ".DBPREFIX."module_livecam_settings
				SET setvalue = '$setval'
				WHERE setname = '$setname'";

		if (!$objDatabase->Execute($query)) {
			return false;
		} else {
			return true;
		}
	}
}
?>
