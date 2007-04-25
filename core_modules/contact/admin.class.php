<?php
/**
 * Contact
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_contact
 * @todo        Edit PHP DocBlocks!
 */
require_once ASCMS_CORE_MODULE_PATH.'/contact/lib/ContactLib.class.php';

/**
 * Contact manager
 * @copyright   CONTREXX CMS - ASTALAVISTA IT AG
 * @author		Astalavista Development Team <thun@astalvista.ch>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_contact
 */
class ContactManager extends ContactLib
{
	var $_objTpl;

	var $_statusMessageOk;
	var $_statusMessageErr;

	var $_arrFormFieldTypes;

	var $boolHistoryEnabled = false;
	var $boolHistoryActivate = false;

	var $_csvSeparator = ';';

	var $_pageTitle = '';

	/**
	* Constructor
	*/
	function ContactManager()
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
		global $objTemplate, $_ARRAYLANG, $objPerm, $_CONFIG;

		$this->_objTpl = &new HTML_Template_Sigma(ASCMS_CORE_MODULE_PATH.'/contact/template');
		$this->_objTpl->setErrorHandling(PEAR_ERROR_DIE);

    	$objTemplate->setVariable("CONTENT_NAVIGATION", "	<a href='index.php?cmd=contact' title=".$_ARRAYLANG['TXT_CONTACT_CONTACT_FORMS'].">".$_ARRAYLANG['TXT_CONTACT_CONTACT_FORMS']."</a>
    														<a href='index.php?cmd=contact&amp;act=settings' title=".$_ARRAYLANG['TXT_CONTACT_SETTINGS'].">".$_ARRAYLANG['TXT_CONTACT_SETTINGS']."</a>");

    	$this->_arrFormFieldTypes = array(
    		'text'			=> $_ARRAYLANG['TXT_CONTACT_TEXTBOX'],
    		'label'			=> $_ARRAYLANG['TXT_CONTACT_TEXT'],
			'checkbox'		=> $_ARRAYLANG['TXT_CONTACT_CHECKBOX'],
			'checkboxGroup'	=> $_ARRAYLANG['TXT_CONTACT_CHECKBOX_GROUP'],
			'date'			=> $_ARRAYLANG['TXT_CONTACT_DATE'],
			'file'			=> $_ARRAYLANG['TXT_CONTACT_FILE_UPLOAD'],
			'hidden'		=> $_ARRAYLANG['TXT_CONTACT_HIDDEN_FIELD'],
			'password'		=> $_ARRAYLANG['TXT_CONTACT_PASSWORD_FIELD'],
			'radio'			=> $_ARRAYLANG['TXT_CONTACT_RADIO_BOXES'],
			'select'		=> $_ARRAYLANG['TXT_CONTACT_SELECTBOX'],
			'textarea'		=> $_ARRAYLANG['TXT_CONTACT_TEXTAREA']
		);

    	$this->initContactForms(true);
    	$this->initCheckTypes();

    	$this->boolHistoryEnabled = ($_CONFIG['contentHistoryStatus'] == 'on') ? true : false;

    	if (is_array($_SESSION['auth']['static_access_ids'])) {
	        if (in_array(78,$_SESSION['auth']['static_access_ids']) || $objPerm->allAccess) {
				$this->boolHistoryActivate = true;
	        }
        }
	}

	/**
	* Get page
	*
	* Get the development page
	*
	* @access public
	* @global object $objTemplate
	*/
	function getPage()
	{
		global $objTemplate, $objPerm;

		if (!isset($_REQUEST['act'])) {
			$_REQUEST['act'] = '';
		}

		if (!isset($_REQUEST['tpl'])) {
			$_REQUEST['tpl'] = '';
		}

		switch ($_REQUEST['act']) {
		case 'settings':
			$objPerm->checkAccess(85, 'static');
			$this->_getSettingsPage();
			break;

		case 'entries':
			$this->_getEntriesPage();
			break;

		default:
			$this->_getContactFormPage();
			break;
		}

		$objTemplate->setVariable(array(
			'CONTENT_TITLE'				=> $this->_pageTitle,
			'CONTENT_OK_MESSAGE'		=> $this->_statusMessageOk,
			'CONTENT_STATUS_MESSAGE'	=> $this->_statusMessageErr,
			'ADMIN_CONTENT'				=> $this->_objTpl->get()
		));
	}

	function _getEntriesPage()
	{
		global $_ARRAYLANG;

		$entryId = isset($_REQUEST['entryId']) ? intval($_REQUEST['entryId']) : 0;
		$formId = isset($_REQUEST['formId']) ? intval($_REQUEST['formId']) : 0;

		$arrEntry = &$this->getFormEtry($entryId);

		if (is_array($arrEntry)) {

			$this->_objTpl->loadTemplateFile('module_contact_entries_details.html');
            $this->_pageTitle = $_ARRAYLANG['TXT_CONTACT_ENTRIE_DETAILS'];

			$this->_objTpl->setVariable(array(
				'CONTACT_FORM_ENTRY_ID'					=> $entryId,
				'CONTACT_ENTRY_TITLE'					=> str_replace('%DATE%', date(ASCMS_DATE_FORMAT, $arrEntry['time']), $_ARRAYLANG['TXT_CONTACT_ENTRY_OF_DATE']),
				'CONTACT_ENTRY'							=> $this->_getEntryDetails($arrEntry, $formId),
				'CONTACT_FORM_ID'						=> $formId
			));

			$this->_objTpl->setVariable(array(
				'TXT_CONTACT_BACK'						=> $_ARRAYLANG['TXT_CONTACT_BACK'],
				'TXT_CONTACT_DELETE'					=> $_ARRAYLANG['TXT_CONTACT_DELETE'],
				'TXT_CONTACT_CONFIRM_DELETE_ENTRY'		=> $_ARRAYLANG['TXT_CONTACT_CONFIRM_DELETE_ENTRY'],
				'TXT_CONTACT_ACTION_IS_IRREVERSIBLE'	=> $_ARRAYLANG['TXT_CONTACT_ACTION_IS_IRREVERSIBLE'],
				'TXT_CONTACT_CONFIRM_DELETE_ENTRIES'	=> $_ARRAYLANG['TXT_CONTACT_CONFIRM_DELETE_ENTRIES']
			));
		} else {
			$this->_contactFormEntries();
		}
	}

	function _getSettingsPage()
	{
		switch ($_REQUEST['tpl']) {
		case 'save':
			$this->_saveSettings();

		default:
			$this->_settings();
			break;
		}
	}

	function _settings()
	{
		global $_ARRAYLANG;

		$this->_objTpl->loadTemplateFile('module_contact_settings.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_CONTACT_SETTINGS'];

		$arrSettings = &$this->getSettings();

		$this->_objTpl->setVariable(array(
			'TXT_CONTACT_SETTINGS'							=> $_ARRAYLANG['TXT_CONTACT_SETTINGS'],
			'TXT_CONTACT_SAVE'								=> $_ARRAYLANG['TXT_CONTACT_SAVE'],
			'TXT_CONTACT_FILE_UPLOAD_DEPOSITION_PATH'		=> $_ARRAYLANG['TXT_CONTACT_FILE_UPLOAD_DEPOSITION_PATH'],
			'TXT_CONTACT_SPAM_PROTECTION_WORD_LIST'			=> $_ARRAYLANG['TXT_CONTACT_SPAM_PROTECTION_WORD_LIST'],
			'TXT_CONTACT_SPAM_PROTECTION_WW_DESCRIPTION'	=> $_ARRAYLANG['TXT_CONTACT_SPAM_PROTECTION_WW_DESCRIPTION'],
			'TXT_CONTACT_DATE'								=> $_ARRAYLANG['TXT_CONTACT_DATE'],
			'TXT_CONTACT_HOSTNAME'							=> $_ARRAYLANG['TXT_CONTACT_HOSTNAME'],
			'TXT_CONTACT_BROWSER_LANGUAGE'					=> $_ARRAYLANG['TXT_CONTACT_BROWSER_LANGUAGE'],
			'TXT_CONTACT_IP_ADDRESS'						=> $_ARRAYLANG['TXT_CONTACT_IP_ADDRESS'],
			'TXT_CONTACT_META_DATE_BY_EXPORT'				=> $_ARRAYLANG['TXT_CONTACT_META_DATE_BY_EXPORT']
		));

		$this->_objTpl->setVariable(array(
			'CONTACT_FILE_UPLOAD_DEPOSITION_PATH'	=> $arrSettings['fileUploadDepositionPath'],
			'CONTACT_SPAM_PROTECTION_WORD_LIST'		=> $arrSettings['spamProtectionWordList'],
			'CONTACT_FIELD_META_DATE'				=> $arrSettings['fieldMetaDate'] == '1' ? 'checked="checked"' : '',
			'CONTACT_FIELD_META_LANG'				=> $arrSettings['fieldMetaLang'] == '1' ? 'checked="checked"' : '',
			'CONTACT_FIELD_META_HOST'				=> $arrSettings['fieldMetaHost'] == '1' ? 'checked="checked"' : '',
			'CONTACT_FIELD_META_IP'					=> $arrSettings['fieldMetaIP'] == '1' ? 'checked="checked"' : '',
		));
	}

	function _saveSettings()
	{
		global $objDatabase, $_ARRAYLANG;

		$saveStatus = true;

		if (isset($_REQUEST['save'])) {
			$arrSettings = &$this->getSettings();

			$arrNewSettings = array(
				'fileUploadDepositionPath'	=> isset($_POST['contactFileUploadDepositionPath']) ? trim(contrexx_stripslashes($_POST['contactFileUploadDepositionPath'])) : '',
				'spamProtectionWordList'	=> isset($_POST['contactSpamProtectionWordList']) ? explode(',', $_POST['contactSpamProtectionWordList']) : '',
				'fieldMetaDate'				=> isset($_POST['contactFieldMetaDate']) ? intval($_POST['contactFieldMetaDate']) : 0,
				'fieldMetaHost'				=> isset($_POST['contactFieldMetaHost']) ? intval($_POST['contactFieldMetaHost']) : 0,
				'fieldMetaLang'				=> isset($_POST['contactFieldMetaLang']) ? intval($_POST['contactFieldMetaLang']) : 0,
				'fieldMetaIP'				=> isset($_POST['contactFieldMetaIP']) ? intval($_POST['contactFieldMetaIP']) : 0
			);

			if (strpos($arrNewSettings['fileUploadDepositionPath'], '..') || empty($arrNewSettings['fileUploadDepositionPath'])) {
				$arrNewSettings['fileUploadDepositionPath'] = $arrSettings['fileUploadDepositionPath'];
			}

			if (!empty($arrNewSettings['spamProtectionWordList'])) {
				$arrTmpWordList = array();
				foreach ($arrNewSettings['spamProtectionWordList'] as $word) {
					array_push($arrTmpWordList, contrexx_stripslashes(trim($word)));
				}
				$arrNewSettings['spamProtectionWordList'] = implode(',', $arrTmpWordList);
			} else {
				$arrNewSettings['spamProtectionWordList'] = $arrSettings['spamProtectionWordList'];
			}

			foreach ($arrNewSettings as $field => $status) {
				if ($status != $arrSettings[$field]) {
					if ($objDatabase->Execute("UPDATE ".DBPREFIX."module_contact_settings SET setvalue='".$status."' WHERE setname='".$field."'") === false) {
						$saveStatus = false;
					}
				}
			}

			if ($saveStatus) {
				$this->_statusMessageOk = $_ARRAYLANG['TXT_CONTACT_SETTINGS_UPDATED'];
			} else {
				$this->_statusMessageErr = $_ARRAYLANG['TXT_CONTACT_DATABASE_QUERY_ERROR'];
			}

			$this->initSettings();
		}
	}

	function _getContactFormPage()
	{
		switch ($_REQUEST['tpl']) {
		case 'edit':
			$this->_modifyForm();
			break;

		case 'copy':
			if (isset($_REQUEST['selectLang']) && $_REQUEST['selectLang'] == 'true') {
				$this->_selectFrontendLang();
			} else {
				$this->_modifyForm(true);
			}
			break;

		case 'save':
			$this->_saveForm();
			break;

		case 'deleteForm':
			$this->_deleteForm();
			break;

		case 'deleteEntry':
			$this->_deleteFormEntry();
			break;

		case 'code':
			$this->_sourceCode();
			break;

		case 'entries':
			$this->_contactFormEntries();
			break;

		case 'csv':
			$this->_getCsv();
			break;

		case 'newContent':
			$this->_createContentPage();
			break;

		case 'updateContent':
			$this->_updateContentSite();
			$this->_contactForms();
			break;

		default:
			$this->_contactForms();
			break;
		}
	}

	function _contactFormEntries()
	{
		global $_ARRAYLANG;

		$this->_objTpl->loadTemplateFile('module_contact_form_entries.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_CONTACT_FORM_ENTRIES'];

		$paging = '';
		$pos = 0;
		$maxFields = 3;
		$formId = isset($_GET['formId']) ? intval($_GET['formId']) : 0;

		if ($formId > 0) {

			if (isset($_GET['pos'])) {
				$pos = intval($_GET['pos']);
			}

			$arrEntries = &$this->getFormEntries($formId, $arrCols, $pos, $paging);
			if (count($arrEntries) > 0) {
				$arrFormFields = &$this->getFormFields($formId);
				$arrFormFieldNames = &$this->getFormFieldNames($formId);

				$this->_objTpl->setGlobalVariable(array(
					'TXT_CONTACT_DELETE_ENTRY'				=> $_ARRAYLANG['TXT_CONTACT_DELETE_ENTRY'],
					'TXT_CONTACT_DETAILS'					=> $_ARRAYLANG['TXT_CONTACT_DETAILS'],
					'CONTACT_FORM_ID'						=> $formId
				));

				$this->_objTpl->setVariable(array(
					'TXT_CONTACT_BACK'						=> $_ARRAYLANG['TXT_CONTACT_BACK'],
					'TXT_CONTACT_CONFIRM_DELETE_ENTRY'		=> $_ARRAYLANG['TXT_CONTACT_CONFIRM_DELETE_ENTRY'],
					'TXT_CONTACT_ACTION_IS_IRREVERSIBLE'	=> $_ARRAYLANG['TXT_CONTACT_ACTION_IS_IRREVERSIBLE'],
					'TXT_CONTACT_DATE'						=> $_ARRAYLANG['TXT_CONTACT_DATE'],
					'TXT_CONTACT_FUNCTIONS'					=> $_ARRAYLANG['TXT_CONTACT_FUNCTIONS'],
					'TXT_CONTACT_SELECT_ALL'				=> $_ARRAYLANG['TXT_CONTACT_SELECT_ALL'],
					'TXT_CONTACT_DESELECT_ALL'				=> $_ARRAYLANG['TXT_CONTACT_DESELECT_ALL'],
					'TXT_CONTACT_SUBMIT_SELECT'				=> $_ARRAYLANG['TXT_CONTACT_SUBMIT_SELECT'],
					'TXT_CONTACT_SUBMIT_DELETE'				=> $_ARRAYLANG['TXT_CONTACT_SUBMIT_DELETE'],
					'CONTACT_FORM_COL_NUMBER'				=> (count($arrCols) > $maxFields ? $maxFields+1 : count($arrCols)) + 3,
					'CONTACT_FORM_ENTRIES_TITLE'			=> str_replace('%NAME%', htmlentities($this->arrForms[$formId]['name'], ENT_QUOTES, CONTREXX_CHARSET), $_ARRAYLANG['TXT_CONTACT_ENTRIES_OF_NAME']),
					'CONTACT_FORM_PAGING'					=> $paging
				));

				$colNr = 0;
				foreach ($arrCols as $col) {
					if ($colNr == $maxFields) {
						break;
					}
					$this->_objTpl->setVariable('CONTACT_COL_NAME', $col);
					$this->_objTpl->parse('contact_col_names');
					$colNr++;
				}

				$rowNr = 0;
				foreach ($arrEntries as $entryId => $arrEntry) {
					$this->_objTpl->setVariable('CONTACT_FORM_ENTRIES_ROW_CLASS', $rowNr % 2 == 0 ? 'row2' : 'row1');

					$this->_objTpl->setVariable(array(
						'CONTACT_FORM_DATE'		=> '<a href="index.php?cmd=contact&amp;act=entries&amp;formId='.$formId.'&amp;entryId='.$entryId.'" title="'.$_ARRAYLANG['TXT_CONTACT_DETAILS'].'">'.date(ASCMS_DATE_FORMAT, $arrEntry['time']).'</a>',
						'CONTACT_FORM_ENTRY_ID'	=> $entryId
					));

					$this->_objTpl->parse('contact_form_entry_data');

					$colNr = 0;
					foreach ($arrCols as $col) {
						if ($colNr == $maxFields) {
							break;
						}

						if (isset($arrEntry['data'][$col])) {
							if (isset($arrFormFields[$arrFormFieldNames[$col]]) && $arrFormFields[$arrFormFieldNames[$col]]['type'] == 'file') {
								$value = '<a href="'.ASCMS_PATH_OFFSET.$arrEntry['data'][$col].'" target="_blank" onclick="return confirm(\''.$_ARRAYLANG['TXT_CONTACT_CONFIRM_OPEN_UPLOADED_FILE'].'\')">'.ASCMS_PATH_OFFSET.$arrEntry['data'][$col].'</a>';
							} else {
								$value = $arrEntry['data'][$col];
							}
						} else {
							$value = '&nbsp;';
						}
						if (empty($value)) {
							$value = '&nbsp;';
						}

						$this->_objTpl->setVariable('CONTACT_FORM_ENTRIES_CELL_CONTENT', $value);
						$this->_objTpl->parse('contact_form_entry_data');

						$colNr++;
					}
					$this->_objTpl->parse('contact_form_entries');

					$rowNr++;
				}
			} else {
				$this->_contactForms();
			}
		} else {
			$this->_contactForms();
		}
	}

	function _contactForms()
	{
		global $_ARRAYLANG, $objLanguage;

		$this->_objTpl->loadTemplateFile('module_contact_forms_overview.html');
		$this->_pageTitle = $_ARRAYLANG['TXT_CONTACT_CONTACT_FORMS'];

		$this->_objTpl->setVariable(array(
			'TXT_CONTACT_CONFIRM_DELETE_FORM'	=> $_ARRAYLANG['TXT_CONTACT_CONFIRM_DELETE_FORM'],
			'TXT_CONTACT_FORM_ENTRIES_WILL_BE_DELETED'	=> $_ARRAYLANG['TXT_CONTACT_FORM_ENTRIES_WILL_BE_DELETED'],
			'TXT_CONTACT_ACTION_IS_IRREVERSIBLE'		=> $_ARRAYLANG['TXT_CONTACT_ACTION_IS_IRREVERSIBLE'],
			'TXT_CONTACT_LATEST_ENTRY'					=> $_ARRAYLANG['TXT_CONTACT_LATEST_ENTRY'],
			'TXT_CONTACT_NUMBER_OF_ENTRIES'				=> $_ARRAYLANG['TXT_CONTACT_NUMBER_OF_ENTRIES'],
			'TXT_CONTACT_CONTACT_FORMS'					=> $_ARRAYLANG['TXT_CONTACT_CONTACT_FORMS'],
			'TXT_CONTACT_ID'							=> $_ARRAYLANG['TXT_CONTACT_ID'],
			'TXT_CONTACT_LANG'							=> $_ARRAYLANG['TXT_CONTACT_LANG'],
			'TXT_CONTACT_NAME'							=> $_ARRAYLANG['TXT_CONTACT_NAME'],
			'TXT_CONTACT_FUNCTIONS'						=> $_ARRAYLANG['TXT_CONTACT_FUNCTIONS'],
			'TXT_CONTACT_ADD_NEW_CONTACT_FORM'			=> $_ARRAYLANG['TXT_CONTACT_ADD_NEW_CONTACT_FORM'],
			'TXT_CONTACT_CSV_FILE'						=> $_ARRAYLANG['TXT_CONTACT_CSV_FILE'],
			'TXT_CONTACT_CONFIRM_DELETE_CONTENT_SITE'	=> $_ARRAYLANG['TXT_CONTACT_CONFIRM_DELETE_CONTENT_SITE']
		));

		$this->_objTpl->setGlobalVariable(array(
			'TXT_CONTACT_SHOW_ENTRIES'					=> $_ARRAYLANG['TXT_CONTACT_SHOW_ENTRIES'],
			'TXT_CONTACT_MODIFY'						=> $_ARRAYLANG['TXT_CONTACT_MODIFY'],
			'TXT_CONTACT_DELETE'						=> $_ARRAYLANG['TXT_CONTACT_DELETE'],
			'TXT_CONTACT_SHOW_SOURCECODE'				=> $_ARRAYLANG['TXT_CONTACT_SHOW_SOURCECODE'],
			'TXT_CONTACT_USE_AS_TEMPLATE'				=> $_ARRAYLANG['TXT_CONTACT_USE_AS_TEMPLATE'],
			'TXT_CONTACT_GET_CSV'						=> $_ARRAYLANG['TXT_CONTACT_GET_CSV'],
			'TXT_CONTACT_DOWNLOAD'						=> $_ARRAYLANG['TXT_CONTACT_DOWNLOAD']
		));

		$rowNr = 0;
		if (is_array($this->arrForms)) {
			foreach ($this->arrForms as $formId => $arrForm) {
				$pageId = $this->_getContentSiteId($formId);

				$this->_objTpl->setGlobalVariable('CONTACT_FORM_ID', $formId);

				$this->_objTpl->setVariable(array(
					'CONTACT_FORM_ROW_CLASS'			=> $rowNr % 2 == 1 ? 'row1' : 'row2',
					'CONTACT_FORM_NAME'					=> htmlentities($arrForm['name'], ENT_QUOTES, CONTREXX_CHARSET),
					'CONTACT_FORM_LAST_ENTRY'			=> $arrForm['last'] ? date(ASCMS_DATE_FORMAT, $arrForm['last']) : '&nbsp;',
					'CONTACT_FORM_NUMBER_OF_ENTRIES'	=> $arrForm['number'],
					'CONTACT_FORM_LANG'					=> $objLanguage->arrLanguage[$arrForm['lang']]['name'],
					'CONTACT_DELETE_CONTENT'			=> $pageId > 0 ? 'true' : 'false'
				));

				$this->_objTpl->parse('contact_contact_forms');

				$rowNr++;
			}
		}
	}

	function _selectFrontendLang()
	{
		global $_ARRAYLANG, $objLanguage, $_FRONTEND_LANGID;

		$formId = isset($_REQUEST['formId']) ? intval($_REQUEST['formId']) : 0;
		if ($formId > 0) {

			$this->_objTpl->loadTemplateFile('module_contact_form_selectFrontendLang.html');
			$this->_pageTitle = $_ARRAYLANG['TXT_CONTACT_COPY_FORM'];

			$menu = "<select name=\"userFrontendLangId\">\n";
			foreach ($objLanguage->arrLanguage as $langId => $arrLanguage) {
				if (intval($arrLanguage['frontend']) == 1) {
					$menu .= "<option value=\"".$langId."\"".($_FRONTEND_LANGID == $langId ? "selected=\"selected\"" : "").">".$arrLanguage['name']."</option>\n";
				}
			}
			$menu .= "</select>\n";

			$this->_objTpl->setVariable(array(
				'TXT_CONTACT_BACK'						=> $_ARRAYLANG['TXT_CONTACT_BACK'],
				'TXT_CONTACT_PROCEED'					=> $_ARRAYLANG['TXT_CONTACT_PROCEED'],
				'TXT_CONTACT_COPY_FORM'					=> $_ARRAYLANG['TXT_CONTACT_COPY_FORM'],
				'TXT_CONTACT_SELECT_FRONTEND_LANG_TXT'	=> $_ARRAYLANG['TXT_CONTACT_SELECT_FRONTEND_LANG_TXT']
			));

			$this->_objTpl->setVariable(array(
				'CONTACT_LANG_MENU'	=> $menu,
				'CONTACT_FORM_ID'	=> $formId
			));
		} else {
			$this->_contactForms();
		}
	}

	/**
	 * Modify Form
	 *
	 * Shows the modifying page.
	 * @access private
	 * @param bool $copy If the form should be copied or not
	 */
	function _modifyForm($copy = false)
	{
		global $_ARRAYLANG, $_CONFIG;

		if ($copy) {
			$this->initContactForms(true);
		}

		$formId = isset($_REQUEST['formId']) ? intval($_REQUEST['formId']) : 0;

		$this->_objTpl->loadTemplateFile('module_contact_form_modify.html');
		$this->_pageTitle = (!$copy && $formId != 0) ? $_ARRAYLANG['TXT_CONTACT_MODIFY_CONTACT_FORM'] : $_ARRAYLANG['TXT_CONTACT_ADD_NEW_CONTACT_FORM'];

		$this->_objTpl->setVariable(array(
			'TXT_CONTACT_ID'								=> $_ARRAYLANG['TXT_CONTACT_ID'],
			'TXT_CONTACT_NAME'								=> $_ARRAYLANG['TXT_CONTACT_NAME'],
			'TXT_CONTACT_RECEIVER_ADDRESSES'				=> $_ARRAYLANG['TXT_CONTACT_RECEIVER_ADDRESSES'],
			'TXT_CONTACT_ADD_OTHER_FIELD'					=> $_ARRAYLANG['TXT_CONTACT_ADD_OTHER_FIELD'],
			'TXT_CONTACT_SAVE'								=> $_ARRAYLANG['TXT_CONTACT_SAVE'],
			'TXT_CONTACT_SEPARATE_MULTIPLE_VALUES_BY_COMMA'	=> $_ARRAYLANG['TXT_CONTACT_SEPARATE_MULTIPLE_VALUES_BY_COMMA'],
			'TXT_CONTACT_SUBJECT'							=> $_ARRAYLANG['TXT_CONTACT_SUBJECT'],
			'TXT_CONTACT_FORM_DESC'							=> $_ARRAYLANG['TXT_CONTACT_FORM_DESC'],
			'TXT_CONTACT_FEEDBACK'							=> $_ARRAYLANG['TXT_CONTACT_FEEDBACK'],
			'TXT_CONTACT_VALUE_S'							=> $_ARRAYLANG['TXT_CONTACT_VALUE_S'],
			'TXT_CONTACT_FIELD_NAME'						=> $_ARRAYLANG['TXT_CONTACT_FIELD_NAME'],
			'TXT_CONTACT_TYPE'								=> $_ARRAYLANG['TXT_CONTACT_TYPE'],
			'TXT_CONTACT_MANDATORY_FIELD'					=> $_ARRAYLANG['TXT_CONTACT_MANDATORY_FIELD'],
			'TXT_CONTACT_FEEDBACK_EXPLANATION'				=> $_ARRAYLANG['TXT_CONTACT_FEEDBACK_EXPLANATION'],
			'TXT_CONTACT_CONFIRM_CREATE_CONTENT_SITE'		=> $_ARRAYLANG['TXT_CONTACT_CONFIRM_CREATE_CONTENT_SITE'],
			'TXT_CONTACT_CONFIRM_UPDATE_CONTENT_SITE'		=> $_ARRAYLANG['TXT_CONTACT_CONFIRM_UPDATE_CONTENT_SITE'],
			'TXT_CONTACT_SHOW_FORM_AFTER_SUBMIT'			=> $_ARRAYLANG['TXT_CONTACT_SHOW_FORM_AFTER_SUBMIT'],
			'TXT_CONTACT_YES'								=> $_ARRAYLANG['TXT_CONTACT_YES'],
			'TXT_CONTACT_NO'								=> $_ARRAYLANG['TXT_CONTACT_NO']
		));

		$this->_objTpl->setGlobalVariable(array(
			'TXT_CONTACT_FORM_FIELDS'						=> $_ARRAYLANG['TXT_CONTACT_FORM_FIELDS'],
			'TXT_CONTACT_DELETE'							=> $_ARRAYLANG['TXT_CONTACT_DELETE'],
			'TXT_CONTACT_MOVE_UP'							=> $_ARRAYLANG['TXT_CONTACT_MOVE_UP'],
			'TXT_CONTACT_MOVE_DOWN'							=> $_ARRAYLANG['TXT_CONTACT_MOVE_DOWN']
		));

		if (!$copy && $formId > 0 && $this->_getContentSiteId($formId)) {
			$jsSubmitFunction = "updateContentSite()";
		} else {
			$jsSubmitFunction = "createContentSite()";
		}

		$lastFieldId = 0;

		if (isset($_POST['saveForm'])) {
			$arrFields = $this->_getFormFieldsFromPost($null);
			$formName = isset($_POST['contactFormName']) ? htmlentities(strip_tags(contrexx_stripslashes($_POST['contactFormName'])), ENT_QUOTES, CONTREXX_CHARSET) : '';
			$formEmails = isset($_POST['contactFormEmail']) ? htmlentities(strip_tags(contrexx_stripslashes(trim($_POST['contactFormEmail']))), ENT_QUOTES, CONTREXX_CHARSET) : '';
			if (empty($formEmails)) {
				$formEmails = $_CONFIG['contactFormEmail'];
			}
			$formSubject = $_POST['contactFormSubject'];
			$formText = contrexx_stripslashes($_POST['contactFormText']);
			$formFeedback = contrexx_stripslashes($_POST['contactFormFeedback']);
			$formShowForm = intval($_POST['contactFormShowForm']);
		} elseif (isset($this->arrForms[$formId])) {
			$arrFields = &$this->getFormFields($formId);
			$formName = $this->arrForms[$formId]['name'];
			$formEmails = $this->arrForms[$formId]['emails'];
			$formSubject = $this->arrForms[$formId]['subject'];
			$formText = $this->arrForms[$formId]['text'];
			$formFeedback = stripslashes($this->arrForms[$formId]['feedback']);
			$formShowForm = $this->arrForms[$formId]['showForm'];
		} else {
			$formName = '';
			$formEmails = $_CONFIG['contactFormEmail'];
			$formSubject = '';
			$formText = '';
			$formShowForm = 0;
			$formFeedback = $_ARRAYLANG['TXT_CONTACT_DEFAULT_FEEDBACK_TXT'];

			$this->_objTpl->setVariable(array(
				'CONTACT_FORM_FIELD_NAME'				=> '',
				'CONTACT_FORM_FIELD_ID'					=> 1,
				'CONTACT_FORM_FIELD_TYPE_MENU'			=> $this->_getFormFieldTypesMenu('contactFormFieldType[1]', 'text', 'id="contactFormFieldType_1" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'),
				'CONTACT_FORM_FIELD_CHECK_MENU'			=> $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType[1]', 'contactFormFieldCheckType_1', 'text', 1),
				'CONTACT_FORM_FIELD_CHECK_BOX'			=> $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired[1]', 'contactFormFieldRequired_1', 'text', false),
				'CONTACT_FORM_FIELD_ATTRIBUTES'			=> $this->_getFormFieldAttribute(1, 'text', '')
			));
			$this->_objTpl->parse('contact_form_field_list');

			$lastFieldId = 1;
		}

		if ($copy) {
			$formId = 0;
		}


		if (isset($arrFields) && is_array($arrFields)) {
			foreach ($arrFields as $fieldId => $arrField) {
				if ($arrField['is_required'] == 1 ) {
					$checked = true;
				} else {
					$checked = false;
				}

				$this->_objTpl->setVariable(array(
					'CONTACT_FORM_FIELD_NAME'				=> $arrField['name'],
					'CONTACT_FORM_FIELD_ID'					=> $fieldId,
					'CONTACT_FORM_FIELD_TYPE_MENU'			=> $this->_getFormFieldTypesMenu('contactFormFieldType['.$fieldId.']', $arrField['type'], 'id="contactFormFieldType_'.$fieldId.'" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'),
					'CONTACT_FORM_FIELD_CHECK_MENU'			=> $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType['.$fieldId.']', 'contactFormFieldCheckType_'.$fieldId, $arrField['type'], $arrField['check_type']),
					'CONTACT_FORM_FIELD_CHECK_BOX'			=> $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired['.$fieldId.']', 'contactFormFieldRequired_'.$fieldId, $arrField['type'], $checked),
					'CONTACT_FORM_FIELD_ATTRIBUTES'			=> $this->_getFormFieldAttribute($fieldId, $arrField['type'], $arrField['attributes'])
				));
				$this->_objTpl->parse('contact_form_field_list');

				$lastFieldId = $fieldId > $lastFieldId ? $fieldId : $lastFieldId;
			}
		}

		if (isset($this->arrForms[$formId])) {
			$actionTitle = $_ARRAYLANG['TXT_CONTACT_MODIFY_CONTACT_FORM'];
		} else {
			$actionTitle = $_ARRAYLANG['TXT_CONTACT_ADD_NEW_CONTACT_FORM'];
		}

		$this->_objTpl->setVariable(array(
			'CONTACT_FORM_NAME'								=> $formName,
			'CONTACT_FORM_EMAIL'							=> $formEmails,
			'CONTACT_FORM_SUBJECT'							=> $formSubject,
			'CONTACT_FORM_FIELD_NEXT_ID'					=> $lastFieldId+1,
			'CONTACT_FORM_FIELD_NEXT_TEXT_TPL'				=> $this->_getFormFieldAttribute($lastFieldId+1, 'text', ''),
			'CONTACT_FORM_FIELD_LABEL_TPL'					=> $this->_getFormFieldAttribute($lastFieldId+1, 'label', ''),
			'CONTACT_FORM_FIELD_CHECK_MENU_NEXT_TPL'		=> $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType['.($lastFieldId+1).']', 'contactFormFieldCheckType_'.($lastFieldId+1), 'text', 1),
			'CONTACT_FORM_FIELD_CHECK_MENU_TPL'				=> $this->_getFormFieldCheckTypesMenu('contactFormFieldCheckType[0]', 'contactFormFieldCheckType_0', 'text', 1),
			'CONTACT_FORM_FIELD_CHECK_BOX_NEXT_TPL'			=> $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired['.($lastFieldId+1).']', 'contactFormFieldRequired_'.($lastFieldId+1), 'text', false),
			'CONTACT_FORM_FIELD_CHECK_BOX_TPL'				=> $this->_getFormFieldRequiredCheckBox('contactFormFieldRequired[0]', 'contactFormFieldRequired_0', 'text', false),
			'CONTACT_ACTION_TITLE'							=> $actionTitle,
			'CONTACT_FORM_ID'								=> $formId,
			'CONTACT_FORM_TEXT'								=> get_wysiwyg_editor('contactFormText', $formText, 'shop'),
			'CONTACT_FORM_FEEDBACK'							=> $formFeedback,
			'CONTACT_FORM_SHOW_FORM_YES'					=> $formShowForm ? 'checked="checked"' : '',
			'CONTACT_FORM_SHOW_FORM_NO'						=> $formShowForm ? '' : 'checked="checked"',
			'CONTACT_FORM_FIELD_TYPE_MENU_TPL'				=> $this->_getFormFieldTypesMenu('contactFormFieldType['.($lastFieldId+1).']', key($this->_arrFormFieldTypes), 'id="contactFormFieldType_'.($lastFieldId+1).'" onchange="setFormFieldAttributeBox(this.getAttribute(\'id\'), this.value)"'),
			'CONTACT_FORM_FIELD_TEXT_TPL'					=> $this->_getFormFieldAttribute(0, 'text', ''),
			'CONTACT_FORM_FIELD_CHECKBOX_TPL'				=> $this->_getFormFieldAttribute(0, 'checkbox', 0),
			'CONTACT_FORM_FIELD_CHECKBOX_GROUP_TPL'			=> $this->_getFormFieldAttribute(0, 'checkboxGroup', ''),
			'CONTACT_FORM_FIELD_DATE_TPL'					=> $this->_getFormFieldAttribute(0, 'date', ''),
			'CONTACT_FORM_FIELD_HIDDEN_TPL'					=> $this->_getFormFieldAttribute(0, 'hidden', ''),
			'CONTACT_FORM_FIELD_RADIO_TPL'					=> $this->_getFormFieldAttribute(0, 'radio', ''),
			'CONTACT_FORM_FIELD_SELECT_TPL'					=> $this->_getFormFieldAttribute(0, 'select', ''),
			'CONTACT_JS_SUBMIT_FUNCTION'					=> $jsSubmitFunction
		));
	}

	function _getContentSiteId($formId)
	{
		global $objDatabase;

		$objContentSite = $objDatabase->SelectLimit("SELECT catid FROM ".DBPREFIX."content_navigation AS n, ".DBPREFIX."modules AS m WHERE m.name='contact' AND n.module=m.id AND n.cmd='".$formId."'", 1);
		if ($objContentSite !== false) {
			if ($objContentSite->RecordCount() == 1) {
				return $objContentSite->fields['catid'];
			}
		}
		return false;
	}

	function _getFormFieldAttribute($id, $type, $attr)
	{
		global $_ARRAYLANG;

		switch ($type) {
		case 'text':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" />\n";
			break;

		case 'label':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" />\n";
			break;

		case 'checkbox':
			return "<select style=\"width:228px;\" name=\"contactFormFieldAttribute[".$id."]\">\n
						<option value=\"0\"".($attr == 0 ? ' selected="selected"' : '').">".$_ARRAYLANG['TXT_CONTACT_NOT_SELECTED']."</option>\n
						<option value=\"1\"".($attr == 1 ? ' selected="selected"' : '').">".$_ARRAYLANG['TXT_CONTACT_SELECTED']."</option>\n
					</select>";
			break;

		case 'checkboxGroup':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" /> *\n";
			break;

		case 'hidden':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" />\n";
			break;

		case 'select':
		case 'radio':
			return "<input style=\"width:228px;\" type=\"text\" name=\"contactFormFieldAttribute[".$id."]\" value=\"".$attr."\" /> *\n";
			break;

		default:
			return '';
			break;
		}
	}

	/**
	 * Save Form
	 *
	 * Saves the form data
	 *
	 * @access private
	 */
	function _saveForm()
	{
		global $_ARRAYLANG, $_CONFIG;

		if (isset($_POST['saveForm'])) {
			$formId = isset($_REQUEST['formId']) ? intval($_REQUEST['formId']) : 0;
			$formName = isset($_POST['contactFormName']) ? strip_tags(contrexx_addslashes($_POST['contactFormName'])) : '';
			$formSubject = isset($_POST['contactFormSubject']) ? strip_tags(contrexx_addslashes($_POST['contactFormSubject'])) : '';
			$formText = isset($_POST['contactFormText']) ? contrexx_addslashes($_POST['contactFormText']) : '';
			$formFeedback = isset($_POST['contactFormFeedback']) ? contrexx_addslashes($_POST['contactFormFeedback']) : '';
			$formShowForm = intval($_POST['contactFormShowForm']);
			if (!empty($formName)) {
				if ($this->isUniqueFormName($formName, $formId)) {
					$arrFields = $this->_getFormFieldsFromPost($uniqueFieldNames);
					if ($uniqueFieldNames) {
						$formEmailsTmp = isset($_POST['contactFormEmail']) ? explode(',', strip_tags(contrexx_stripslashes($_POST['contactFormEmail']))) : '';

						if (is_array($formEmailsTmp)) {
							$formEmails = array();
							foreach ($formEmailsTmp as $email) {
								$email = trim(contrexx_strip_tags($email));
								if (!empty($email)) {
									array_push($formEmails, $email);
								}
							}
							$formEmails = implode(',', $formEmails);
						} else {
							$formEmails = '';
						}
						if (empty($formEmails)) {
							$formEmails = $_CONFIG['contactFormEmail'];
						}

						if ($formId > 0) {
							// This updates the database
							$this->updateForm($formId, $formName, $formEmails, $formSubject, $formText, $formFeedback, $formShowForm, $arrFields);
						} else {
							$this->addForm($formName, $formEmails, $formSubject, $formText, $formFeedback, $formShowForm, $arrFields);
						}
						$this->_statusMessageOk .= $_ARRAYLANG['TXT_CONTACT_FORM_SUCCESSFULLY_SAVED']."<br />";

						if (isset($_POST['contentSiteAction'])) {
							switch ($_POST['contentSiteAction']) {
								case 'create':
									$this->_createContentPage();
									break;

								case 'update':
									$this->_updateContentSite();
									break;

								default:
									break;
							}
						}

						$this->_contactForms();
					} else {
						$this->_statusMessageErr .= $_ARRAYLANG['TXT_CONTACT_FORM_FIELD_UNIQUE_MSG'];
						$this->_modifyForm();
					}
				} else {
					$this->_statusMessageErr .= $_ARRAYLANG['TXT_CONTACT_FORM_NAME_IS_NOT_UNIQUE_MSG'];
					$this->_modifyForm();
				}
			} else {
				$this->_statusMessageErr .= $_ARRAYLANG['TXT_CONTACT_FORM_NAME_REQUIRED_MSG'];
				$this->_modifyForm();
			}
		} else {
			$this->_modifyForm();
		}
	}

	function _deleteFormEntry()
	{
		if (isset($_GET['entryId'])) {
			$entryId = intval($_GET['entryId']);
			$this->deleteFormEntry($entryId);
		} elseif (isset($_POST['selectedEntries']) && count($_POST['selectedEntries']) > 0) {
			foreach ($_POST['selectedEntries'] as $entryId) {
				$this->deleteFormEntry(intval($entryId));
			}
		}
		$this->initContactForms(true);
		$this->_contactFormEntries();
	}

	function _deleteForm()
	{
		global $_ARRAYLANG;

		if (isset($_GET['formId'])) {
			$formId = intval($_GET['formId']);

			if ($formId > 0) {
				if ($this->deleteForm($formId)) {
					$this->_statusMessageOk = $_ARRAYLANG['TXT_CONTACT_CONTACT_FORM_SUCCESSFULLY_DELETED'];

					if (isset($_GET['deleteContent']) && $_GET['deleteContent'] == 'true') {
						$this->_deleteContentSite($formId);
					}
				} else {
					$this->_statusMessageErr = $_ARRAYLANG['TXT_CONTACT_FAILED_DELETE_CONTACT_FORM'];
				}
			}
		}
		$this->_contactForms();
	}

	function _deleteContentSite($formId)
	{
		global $objPerm, $objDatabase, $_ARRAYLANG;

		$objPerm->checkAccess(26, 'static');

		$formId = intval($_REQUEST['formId']);
		$pageId = $this->_getContentSiteId($formId);

		if ($pageId != 0) {
			if ($this->boolHistoryEnabled) {
				$objResult = $objDatabase->Execute('SELECT	id
													FROM 	'.DBPREFIX.'content_navigation_history
													WHERE	is_active="1" AND
															catid='.$pageId.'
													LIMIT	1
												');
				$objDatabase->Execute('	INSERT
										INTO	'.DBPREFIX.'content_logfile
										SET		action="delete",
												history_id='.$objResult->fields['id'].',
												is_validated="'.(($this->boolHistoryActivate) ? 1 : 0).'"
									');
				$objDatabase->Execute('	UPDATE	'.DBPREFIX.'content_navigation_history
										SET		changelog='.time().'
										WHERE	catid='.$pageId.' AND
												is_active="1"
										LIMIT	1
									');
			}

			if ($this->boolHistoryEnabled) {
				if (!$this->boolHistoryActivate) {
					$boolDelete = false;
					$this->_statusMessageOk .= '<br />'.$_ARRAYLANG['TXT_CONTACT_DATA_RECORD_DELETED_SUCCESSFUL_VALIDATE'];
				} else {
					$boolDelete = true;
				}
			} else {
				$boolDelete = true;
			}

			if ($boolDelete) {
				$q1 = "DELETE FROM ".DBPREFIX."content WHERE id=".$pageId;
				$q2 = "DELETE FROM ".DBPREFIX."content_navigation WHERE catid=".$pageId;
				if ($objDatabase->Execute($q1) === false || $objDatabase->Execute($q2) === false) {
					$this->_statusMessageErr = $_ARRAYLANG['TXT_CONTACT_DATABASE_QUERY_ERROR'];
				} else {
				     $this->_statusMessageOk .= '<br />'.$_ARRAYLANG['TXT_CONTACT_DATA_RECORD_DELETED_SUCCESSFUL'];
				}
			}

			$this->_collectLostPages();
		}
	}

	/**
	* The function collects all categories without an existing parcat and assigns it to "lost and found"
	*
	* @global 	object		$objDatabase
	*/
	function _collectLostPages() {
		global $objDatabase;

		$objResult = $objDatabase->Execute('	SELECT	catid,
														parcat,
														lang
												FROM	'.DBPREFIX.'content_navigation
												WHERE	parcat <> 0
										');
		if ($objResult->RecordCount() > 0) {
			//subcategories have been found
			while ($row = $objResult->FetchRow()) {
				$objSubResult = $objDatabase->Execute('	SELECT	catid
														FROM	'.DBPREFIX.'content_navigation
														WHERE	catid='.$row['parcat'].'
														LIMIT 	1
													');
				if ($objSubResult->RecordCount() != 1) {
					//this is a "lost" category.. assign it to "lost and found"
					$objSubSubResult = $objDatabase->Execute('	SELECT	catid
																FROM	'.DBPREFIX.'content_navigation
																WHERE	module=1 AND
																		cmd="lost_and_found" AND
																		lang='.$row['lang'].'
																LIMIT	1
															');
					$subSubRow = $objSubSubResult->FetchRow();
					$objDatabase->Execute('	UPDATE	'.DBPREFIX.'content_navigation
											SET		parcat='.$subSubRow['catid'].'
											WHERE	catid='.$row['catid'].'
											LIMIT	1
										');
				}
			}
		}
	}

	function _getFormFieldsFromPost(&$uniqueFieldNames)
	{
		$uniqueFieldNames = true;
		$arrFields = array();
		$arrFieldNames = array();
		$orderId = 0;

		if (isset($_POST['contactFormFieldName']) && is_array($_POST['contactFormFieldName'])) {
			foreach ($_POST['contactFormFieldName'] as $id => $fieldName) {
				$fieldName = htmlentities(strip_tags(contrexx_stripslashes($fieldName)), ENT_QUOTES, CONTREXX_CHARSET);
				$type = isset($_POST['contactFormFieldType'][$id]) && array_key_exists(contrexx_stripslashes($_POST['contactFormFieldType'][$id]), $this->_arrFormFieldTypes) ? contrexx_stripslashes($_POST['contactFormFieldType'][$id]) : key($this->_arrFormFieldTypes);
				$attributes = isset($_POST['contactFormFieldAttribute'][$id]) && !empty($_POST['contactFormFieldAttribute'][$id]) ? ($type == 'text' || $type == 'label' || $type == 'file' || $type == 'textarea' || $type == 'hidden' || $type == 'radio' || $type == 'checkboxGroup' || $type == 'password' || $type == 'select' ? htmlentities(strip_tags(contrexx_stripslashes($_POST['contactFormFieldAttribute'][$id])), ENT_QUOTES, CONTREXX_CHARSET) : intval($_POST['contactFormFieldAttribute'][$id])) : '';
				$is_required = isset($_POST['contactFormFieldRequired'][$id]) ? 1 : 0;
				$checkType = isset($_POST['contactFormFieldCheckType'][$id]) ? intval($_POST['contactFormFieldCheckType'][$id]) : 1;

				if (!in_array($fieldName, $arrFieldNames)) {
					array_push($arrFieldNames, $fieldName);
				} else {
					$uniqueFieldNames = false;
				}

				switch ($type) {
					case 'checkboxGroup':
					case 'radio':
					case 'select':
						$arrAttributes = explode(',', $attributes);
						$arrNewAttributes = array();
						foreach ($arrAttributes as $strAttribute) {
							array_push($arrNewAttributes, trim($strAttribute));
						}
						$attributes = implode(',', $arrNewAttributes);
						break;

					default:
						break;
				}

				$arrFields[intval($id)] = array(
					'name'			=> $fieldName,
					'type'			=> $type,
					'attributes'	=> $attributes,
					'order_id'		=> $orderId,
					'is_required'	=> $is_required,
					'check_type'	=> $checkType
				);

				$orderId++;
			}
		}
		return $arrFields;
	}

	/**
	 * Field Types Menu
	 *
	 * Generates a xhtml selection list with all the field types
	 * @access private
	 */
	function _getFormFieldTypesMenu($name, $selectedType, $attrs = '')
	{

		$menu = "<select name=\"".$name."\" ".$attrs.">\n";

		foreach ($this->_arrFormFieldTypes as $type => $desc) {
			$menu .= "<option value=\"".$type."\"".($selectedType == $type ? 'selected="selected"' : '').">".$desc."</option>\n";
		}

		$menu .= "</select>\n";
		return  $menu;
	}

	/**
	 * Check Types Menu
	 *
	 * Generates a selection list with all possible types which can be checked
	 * @access private
	 * @param string $name Name of the selection list
	 * @param array $list List with all of the possible types (email, url, text, numbers...)
	 * @param int $selected Which option has to be selected
	 */
	function _getFormFieldCheckTypesMenu($name, $id,  $type, $selected)
	{
		global $_ARRAYLANG;

		switch ($type) {
			case 'checkbox':
			case 'checkboxGroup':
			case 'date':
			case 'hidden':
			case 'radio':
			case 'select':
			case 'label':
				$menu = '';
				break;

			case 'text':
			case 'file':
			case 'password':
			case 'textarea':
			default:
				$menu = "<select name=\"".$name."\" id=\"".$id."\">\n";
				foreach ($this->arrCheckTypes as $typeId => $type) {
					if ($selected == $typeId) {
						$select = "selected=\"selected\"";
					} else {
						$select = "";
					}

					$menu .= "<option value=\"".$typeId."\" $select>".$_ARRAYLANG[$type['name']]."</option>\n";
				}

				$menu .= "</select>\n";
			break;
		}
		return  $menu;
	}

	function _getFormFieldRequiredCheckBox($name, $id, $type, $selected)
	{
		global $_ARRAYLANG;

		switch ($type) {
			case 'hidden':
			case 'select':
			case 'label':
				return '';
				break;

			default:
				return '<input type="checkbox" name="'.$name.'" id="'.$id.'" '.($selected ? 'checked="checked"' : '').' />';
				break;
		}
	}

	/**
	 * Source Code page
	 *
	 * Gets the page for showing the source code
	 * @access public
	 * @global $_ARRAYLANG
	 */
	function _sourceCode($formId = NULL)
	{
		global $_ARRAYLANG;

		if (!isset($formId)) {
			$formId = isset($_REQUEST['formId']) ? intval($_REQUEST['formId']) : 0;
		}

		if ($formId > 0 && isset($this->arrForms[$formId])) {
			$this->_objTpl->loadTemplateFile('module_contact_form_code.html');
			$this->_pageTitle = $_ARRAYLANG['TXT_CONTACT_SOURCECODE'];

			$this->_objTpl->setVariable(array(
				'TXT_CONTACT_SOURCECODE'			=> $_ARRAYLANG['TXT_CONTACT_SOURCECODE'],
				'TXT_CONTACT_PREVIEW'				=> $_ARRAYLANG['TXT_CONTACT_PREVIEW'],
				'TXT_CONTACT_COPY_SOURCECODE_MSG'	=> $_ARRAYLANG['TXT_CONTACT_COPY_SOURCECODE_MSG'],
				'TXT_CONTACT_SELECT_ALL'			=> $_ARRAYLANG['TXT_CONTACT_SELECT_ALL'],
				'TXT_CONTACT_BACK'					=> $_ARRAYLANG['TXT_CONTACT_BACK']
			));

			$contentSiteExists = $this->_getContentSiteId($formId);

			$this->_objTpl->setVariable(array(
				'CONTACT_CONTENT_SITE_ACTION_TXT'	=> $contentSiteExists > 0 ? $_ARRAYLANG['TXT_CONTACT_UPDATE_CONTENT_SITE'] : $_ARRAYLANG['TXT_CONTACT_NEW_PAGE'],
				'CONTACT_CONTENT_SITE_ACTION'		=> $contentSiteExists > 0 ? 'updateContent' : 'newContent',
				'CONTACT_SOURCECODE_OF'				=> str_replace('%NAME%', $this->arrForms[$formId]['name'], $_ARRAYLANG['TXT_CONTACT_SOURCECODE_OF_NAME']),
				'CONTACT_PREVIEW_OF'				=> str_replace('%NAME%', $this->arrForms[$formId]['name'], $_ARRAYLANG['TXT_CONTACT_PREVIEW_OF_NAME']),
				'CONTACT_FORM_SOURCECODE'			=> htmlentities($this->_getSourceCode($formId, false, true), ENT_QUOTES, CONTREXX_CHARSET),
				'CONTACT_FORM_PREVIEW'				=> $this->_getSourceCode($formId, true),
				'FORM_ID'							=> $formId
			));
		} else {
			$this->_contactForms();
		}
	}

	function _getSourceCode($id, $preview = false, $show = false)
	{
		global $_ARRAYLANG;

		$arrFields = $this->getFormFields($id);

		if ($show) {
			$sourcecode = "[[CONTACT_FEEDBACK_TEXT]]\n";
		} else {
			$sourcecode = "{CONTACT_FEEDBACK_TEXT}\n";
		}
		$sourcecode .= $this->arrForms[$id]['text'] . "<br /><br />\n";
		$sourcecode .= "<div id=\"contactFormError\" style=\"color: red; display: none;\">";
		$sourcecode .= $_ARRAYLANG['TXT_NEW_ENTRY_ERORR'];
		$sourcecode .= "</div>\n<br />";
		$sourcecode .= "<!-- BEGIN contact_form -->\n";
		$sourcecode .= "<form action=\"".($preview ? '../' : '')."index.php?section=contact&amp;cmd=".$id."\" ";
		$sourcecode .= "method=\"post\" enctype=\"multipart/form-data\" onsubmit=\"return checkAllFields();\" id=\"contactForm\">\n";
		$sourcecode .= "<table border=\"0\">\n";

		foreach ($arrFields as $fieldId => $arrField) {
			if ($arrField['is_required']) {
				$required = "<span style=\"color: red;\">*</span>";
			} else {
				$required = "";
			}

			$sourcecode .= "<tr>\n";
			$sourcecode .= "<td style=\"width:100px;\">".(($arrField['type'] != 'hidden' && $arrField['type'] != 'label') ? $arrField['name'] : '&nbsp;')." ".$required."</td>\n";
			$sourcecode .= "<td>";

			switch ($arrField['type']) {
				case 'text':
					$sourcecode .= "<input style=\"width:300px;\" type=\"text\" name=\"contactFormField_".$fieldId."\" value=\"".($arrField['attributes'] == '' ? '{'.$fieldId.'_VALUE}' : $arrField['attributes'])."\" />\n";
					break;

				case 'label':
					$sourcecode .= $arrField['attributes'] == '' ? '{'.$fieldId.'_VALUE}' : $arrField['attributes']."\n";
					break;

				case 'checkbox':
					$sourcecode .= "<input type=\"checkbox\" name=\"contactFormField_".$fieldId."\" value=\"1\"".($arrField['attributes'] == '1' ? ' checked="checked"' : '')." />\n";
					break;

				case 'checkboxGroup':
					$options = explode(',', $arrField['attributes']);
					$nr = 0;
					foreach ($options as $option) {
						$sourcecode .= "<input type=\"checkbox\" name=\"contactFormField_".$fieldId."[]\" id=\"contactFormField_".$nr."_".$fieldId."\" value=\"".$option."\" /><label for=\"contactFormField_".$nr."_".$fieldId."\">".$option."</label>\n";
						$nr++;
					}
					break;

				case 'date':
					$sourcecode .= "<input style=\"width:300px;\" type=\"text\" name=\"contactFormField_".$fieldId."\" id=\"DPC_date".$fieldId."_YYYY-MM-DD\" />\n";
					break;

				case 'file':
					$sourcecode .= "<input style=\"width:300px;\" type=\"file\" name=\"contactFormField_".$fieldId."\" />\n";
					break;

				case 'hidden':
					$sourcecode .= "<input type=\"hidden\" name=\"contactFormField_".$fieldId."\" value=\"".($arrField['attributes'] == "" ? "{".$fieldId."_VALUE}" : $arrField['attributes'])."\" />\n";
					break;

				case 'password':
					$sourcecode .= "<input style=\"width:300px;\" type=\"password\" name=\"contactFormField_".$fieldId."\" value=\"\" />\n";
					break;

				case 'radio':
					$options = explode(',', $arrField['attributes']);
					$nr = 0;
					foreach ($options as $option) {
						$sourcecode .= "<input type=\"radio\" name=\"contactFormField_".$fieldId."\" id=\"contactFormField_".$nr."_".$fieldId."\" value=\"".$option."\" /><label for=\"contactFormField_".$nr."_".$fieldId."\">".$option."</label>\n";
						$nr++;
					}
					break;

				case 'select':
					$options = explode(',', $arrField['attributes']);
					$nr = 0;
					$sourcecode .= "<select style=\"width:300px;\" name=\"contactFormField_".$fieldId."\">\n";
					foreach ($options as $option) {
						$sourcecode .= "<option>".$option."</option>\n";
					}
					$sourcecode .= "</select>\n";
					break;

				case 'textarea':
					$sourcecode .= "<textarea style=\"width:300px; height:100px;\" name=\"contactFormField_".$fieldId."\">{".$fieldId."_VALUE}</textarea>\n";
					break;
			}

			$sourcecode .= "</td>\n";
			$sourcecode .= "</tr>\n";
		}
		$sourcecode .= "<tr>\n";
		$sourcecode .= "<td>&nbsp;</td>\n";
		$sourcecode .= "<td>\n";
		$sourcecode .= "<input type=\"reset\" value=\"".$_ARRAYLANG['TXT_CONTACT_DELETE']."\" /> <input type=\"submit\" name=\"submitContactForm\" value=\"".$_ARRAYLANG['TXT_CONTACT_SUBMIT']."\" />\n";
		$sourcecode .= "</td>\n";
		$sourcecode .= "</tr>\n";
		$sourcecode .= "</table>\n";

		$sourcecode .= "</form>";
		$sourcecode .= "<!-- END contact_form -->\n";

		$sourcecode .= $this->_getJsSourceCode($id, $arrFields, $preview, $show);

		return $sourcecode;
	}


	function _getEntryDetails($arrEntry, $formId)
	{
		global $_ARRAYLANG;

		$arrFormFields = $this->getFormFields($formId);
		$rowNr = 0;

		$sourcecode .= "<table border=\"0\" class=\"adminlist\" cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">\n";
		foreach ($arrFormFields as $arrField) {
			$sourcecode .= "<tr class=".($rowNr % 2 == 0 ? 'row1' : 'row2').">\n";
			$sourcecode .= "<td style=\"vertical-align:top;\" width=\"15%\">".$arrField['name'].($arrField['type'] == 'hidden' ? ' (hidden)' : '')."</td>\n";
			$sourcecode .= "<td width=\"85%\">";

			switch ($arrField['type']) {
				case 'checkbox':
					$sourcecode .= isset($arrEntry['data'][$arrField['name']]) && $arrEntry['data'][$arrField['name']] ? ' '.$_ARRAYLANG['TXT_CONTACT_YES'] : ' '.$_ARRAYLANG['TXT_CONTACT_NO'];
					break;

				case 'file':
					$sourcecode .= isset($arrEntry['data'][$arrField['name']]) ? '<a href="'.ASCMS_PATH_OFFSET.$arrEntry['data'][$arrField['name']].'" target="_blank" onclick="return confirm(\''.$_ARRAYLANG['TXT_CONTACT_CONFIRM_OPEN_UPLOADED_FILE'].'\')">'.ASCMS_PATH_OFFSET.$arrEntry['data'][$arrField['name']].'</a>' : '&nbsp;';
					break;

				case 'text':
				case 'checkboxGroup':
				case 'date':
				case 'hidden':
				case 'password':
				case 'radio':
				case 'select':
				case 'textarea':
					$sourcecode .= isset($arrEntry['data'][$arrField['name']]) ? nl2br($arrEntry['data'][$arrField['name']]) : '&nbsp;';
					break;
			}

			$sourcecode .= "</td>\n";
			$sourcecode .= "</tr>\n";

			$rowNr++;
		}
		$sourcecode .= "</table>\n";

		return $sourcecode;
	}

	/**
	 * Get CSV File
	 *
	 * @access private
	 * @global $objDatabase
	 * @global $_ARRAYLANG
	 */
	function _getCsv()
	{
		global $objDatabase, $_ARRAYLANG, $_CONFIG;

		$id = intval($_GET['formId']);

		if (empty($id)) {
			header("Location: index.php?cmd=contact");
			return;
		}

		$paging = '';
		$formEntries = &$this->getFormEntries($id, $arrCols, 0, $paging, false);
		$filename = $this->_replaceFilename($this->arrForms[$id]['name']. ".csv");
		$arrFormFields = $this->getFormFields($id);

		// Because we return a csv, we need to set the correct header
		header("Content-Type: text/comma-separated-values", true);
		header("Content-Disposition: inline; filename=$filename", true);

		$value = '';
		foreach ($arrFormFields as $arrField) {
			$value .= $this->_escapeCsvValue($arrField['name']).$this->_csvSeparator;
		}

		$arrSettings = $this->getSettings();

		$value .= ($arrSettings['fieldMetaDate'] == '1' ? $_ARRAYLANG['TXT_CONTACT_DATE'].$this->_csvSeparator : '')
				.($arrSettings['fieldMetaHost'] == '1' ? $_ARRAYLANG['TXT_CONTACT_HOSTNAME'].$this->_csvSeparator : '')
				.($arrSettings['fieldMetaLang'] == '1' ? $_ARRAYLANG['TXT_CONTACT_BROWSER_LANGUAGE'].$this->_csvSeparator : '')
				.($arrSettings['fieldMetaIP'] == '1' ? $_ARRAYLANG['TXT_CONTACT_IP_ADDRESS'] : '')
				."\r\n";

		foreach ($formEntries as $entryId => $arrEntry) {
			foreach ($arrFormFields as $arrField) {
				switch ($arrField['type']) {
					case 'checkbox':
						$value .= isset($arrEntry['data'][$arrField['name']]) && $arrEntry['data'][$arrField['name']] ? ' '.$_ARRAYLANG['TXT_CONTACT_YES'] : ' '.$_ARRAYLANG['TXT_CONTACT_NO'];
						break;

					case 'file':
						$value .= isset($arrEntry['data'][$arrField['name']]) ? ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.$arrEntry['data'][$arrField['name']] : '';
						break;

					case 'text':
					case 'checkboxGroup':
					case 'hidden':
					case 'password':
					case 'radio':
					case 'select':
					case 'textarea':
						$value .= isset($arrEntry['data'][$arrField['name']]) ? $this->_escapeCsvValue($arrEntry['data'][$arrField['name']]) : '';
						break;
				}

				$value .= $this->_csvSeparator;
			}
			$value .= ($arrSettings['fieldMetaDate'] == '1' ? date(ASCMS_DATE_FORMAT, $arrEntry['time']).$this->_csvSeparator : '')
					.($arrSettings['fieldMetaHost'] == '1' ? $this->_escapeCsvValue($arrEntry['host']).$this->_csvSeparator : '')
					.($arrSettings['fieldMetaLang'] == '1' ? $this->_escapeCsvValue($arrEntry['lang']).$this->_csvSeparator : '')
					.($arrSettings['fieldMetaIP'] == '1' ? $arrEntry['ipaddress'] : '')
					."\r\n";
		}

		print $value;
		exit();
	}

	/**
	 * Escape a value that it could be inserted into a csv file.
	 *
	 * @param string $value
	 * @return string
	 */
	function _escapeCsvValue(&$value)
	{
		$value = preg_replace('/\r\n/', "\n", $value);
		$valueModified = str_replace('"', '""', $value);

		if ($valueModified != $value || preg_match('/['.$this->_csvSeparator.'\n]+/', $value)) {
			$value = '"'.$valueModified.'"';
		}
		return $value;
	}

	/**
	 * Replaces the special characters
	 *
	 * Replaces the special characters in a filename like whitespaces or
	 * umlauts. Needed by the CSV generator.
	 *
	 * @access private
	 * @param $filename string Filename where the characters have
	 *						   to be replaced
	 */
	function _replaceFilename($filename)
	{
		$filename = strtolower($filename);

		// replace whitespaces
		$filename = preg_replace("%\ %", "_", $filename);

		// replace umlauts
		$filename = preg_replace("%ö%", "oe", $filename);
		$filename = preg_replace("%ü%", "ue", $filename);
		$filename = preg_replace("%ä%", "ae", $filename);

		return $filename;
	}

	/**
	 * Generates a new page in the content manager
	 *
	 * Adds a new page in the content manager with the source code
	 * of the form the user needs.
	 *
	 * @access private
	 * @global $_ARRAYLANG
	 * @global $objDatabase
	 */
	function _createContentPage()
	{
		global $_ARRAYLANG, $objDatabase, $_FRONTEND_LANGID, $objPerm, $_CONFIG;

		$objPerm->checkAccess(5, 'static');

		$formId = intval($_REQUEST['formId']);
		if ($formId > 0) {
			$objContactForm = $objDatabase->SelectLimit("SELECT name FROM ".DBPREFIX."module_contact_form WHERE id=".$formId, 1);
			if ($objContactForm !== false) {
				$catname = addslashes($objContactForm->fields['name']);
			}

			$currentTime = time();
			$content = addslashes($this->_getSourceCode($formId));

	  		$q1 = "INSERT INTO ".DBPREFIX."content_navigation (catid,
								  		catname,
								  		displayorder,
								  		displaystatus,
								  		username,
								  		changelog,
								  		cmd,
								  		lang,
								  		module
								  		) VALUES(
								  		'',
								  		'".$catname."',
								  		'1',
								  		'on',
								  		'".$_SESSION['auth']['username']."',
								  		'".$currentTime."',
								  		'".$formId."',
								  		'".$_FRONTEND_LANGID."',
								  		'6')";
			$objDatabase->Execute($q1);
			$pageId = $objDatabase->Insert_ID();

			$q2 ="INSERT INTO ".DBPREFIX."content (id,
													content,
													title,
													metatitle,
													metadesc,
													metakeys)
											VALUES (".$pageId.",
													'".$content."',
													'".$catname."',
													'".$catname."',
													'".$catname."',
													'".$catname."')";

			if ($objDatabase->Execute($q2) !== false) {
				//create backup for history
				if (!$this->boolHistoryActivate && $this->boolHistoryEnabled) {
					//user is not allowed to validated, so set if "off"
					$objDatabase->Execute('	UPDATE	'.DBPREFIX.'content_navigation
											SET		is_validated="0",
													activestatus="0"
											WHERE	catid='.$pageId.'
											LIMIT	1
										');
				}

				if ($this->boolHistoryEnabled) {
					$objResult = $objDatabase->Execute('SELECT	protected,
																frontend_access_id,
																backend_access_id
														FROM	'.DBPREFIX.'content_navigation
														WHERE	catid='.$pageId.'
														LIMIT	1
													');
					$objDatabase->Execute('	INSERT
											INTO	'.DBPREFIX.'content_navigation_history
											SET		is_active="1",
													catid='.$pageId.',
								                   	catname="'.$catname.'",
								                   	displayorder=1,
								                   	displaystatus="off",
								                   	username="'.$_SESSION['auth']['username'].'",
								                   	changelog="'.$currentTime.'",
								               	 	cmd="'.$formId.'",
								                  	lang="'.$_FRONTEND_LANGID.'",
								                   	module="6"');
					$intHistoryId = $objDatabase->insert_id();
					$objDatabase->Execute('	INSERT
											INTO	'.DBPREFIX.'content_history
								            SET 	id='.$intHistoryId.',
								            		page_id='.$pageId.',
								                   	content="'.$content.'",
								                   	title="'.$catname.'",
								                   	metatitle="'.$catname.'",
									                metadesc="'.$catname.'",
								                   	metakeys="'.$catname.'"');
					$objDatabase->Execute('	INSERT
											INTO	'.DBPREFIX.'content_logfile
											SET		action="new",
													history_id='.$intHistoryId.',
													is_validated="'.(($this->boolHistoryActivate) ? 1 : 0).'"
										');
				}

				header("Location: ".ASCMS_PROTOCOL.'://'.$_CONFIG['domainUrl'].ASCMS_PATH_OFFSET.ASCMS_BACKEND_PATH."/index.php?cmd=content&act=edit&pageId=".$pageId);
				exit;
			} else {
				$this->_statusMessageErr = $_ARRAYLANG['TXT_CONTACT_DATABASE_QUERY_ERROR'];
			}
		}
	}

	function _updateContentSite()
	{
		global $objDatabase, $_FRONTEND_LANGID, $objPerm, $_ARRAYLANG;

		$objPerm->checkAccess(35, 'static');

		$formId = intval($_REQUEST['formId']);
		$pageId = $this->_getContentSiteId($formId);
		if ($pageId > 0) {
			$objContactForm = $objDatabase->SelectLimit("SELECT name FROM ".DBPREFIX."module_contact_form WHERE id=".$formId, 1);
			if ($objContactForm !== false) {
				$catname = addslashes($objContactForm->fields['name']);
			}
			$content = addslashes($this->_getSourceCode($formId));
			$currentTime = time();

			//make sure the user is allowed to update the content
			if ($this->boolHistoryEnabled) {
				if ($this->boolHistoryActivate) {
					$boolDirectUpdate = true;
				} else {
					$boolDirectUpdate = false;
				}
			} else {
				$boolDirectUpdate = true;
			}

			if ($boolDirectUpdate) {
				$objDatabase->Execute("UPDATE 	".DBPREFIX."content
						               SET 		content='".$content."'
						             	WHERE 	id=".$pageId);
			}

			if ($parcat!=$pageId) {
				//create copy of parcat (for history)
				$intHistoryParcat = $parcat;
				if ($boolDirectUpdate) {
					$objDatabase->Execute("	UPDATE 	".DBPREFIX."content_navigation
							                SET 	username='".$_SESSION['auth']['username']."',
							                    	changelog='".$currentTime."'
							              	WHERE catid=".$pageId);
				}
			} else {
				//create copy of parcat (for history)
				$intHistoryParcat = 0;
				if ($boolDirectUpdate) {
				   	$objDatabase->Execute("	UPDATE 	".DBPREFIX."content_navigation
						                  	SET 	username='".$_SESSION['auth']['username']."',
											  		changelog='".$currentTime."'
											WHERE 	catid=".$pageId);
				}
			}

			if ($boolDirectUpdate) {
				$this->_statusMessageOk .= $_ARRAYLANG['TXT_CONTACT_CONTENT_PAGE_SUCCESSFULLY_UPDATED']."<br />";
			} else {
				$this->_statusMessageOk .= $_ARRAYLANG['TXT_CONTACT_DATA_RECORD_UPDATED_SUCCESSFUL_VALIDATE']."<br />";
			}

			//create backup for history
			if ($this->boolHistoryEnabled) {
				$objResult = $objDatabase->Execute('SELECT	displayorder,
															protected,
															frontend_access_id,
															backend_access_id
													FROM	'.DBPREFIX.'content_navigation
													WHERE	catid='.$pageId.'
													LIMIT	1
												');
				if ($boolDirectUpdate) {
					$objDatabase->Execute('	UPDATE	'.DBPREFIX.'content_navigation_history
											SET		is_active="0"
											WHERE	catid='.$pageId);
				}

				$objDatabase->Execute('	INSERT
										INTO	'.DBPREFIX.'content_navigation_history
										SET		is_active="'.(($boolDirectUpdate) ? 1 : 0).'",
												catid='.$pageId.',
												parcat="'.$intHistoryParcat.'",
						                    	catname="'.$catname.'",
						                    	username="'.$_SESSION['auth']['username'].'",
						                    	changelog="'.$currentTime.'",
						                    	lang="'.$_FRONTEND_LANGID.'",
						                    	cmd="'.$formId.'",
						                    	module="6"
							               ');
				$intHistoryId = $objDatabase->insert_id();
				$objDatabase->Execute('	INSERT
										INTO	'.DBPREFIX.'content_history
							            SET 	id='.$intHistoryId.',
							            		page_id='.$pageId.',
							                   	content="'.$content.'",
							                   	title="'.$catname.'",
							                   	metatitle="'.$catname.'",
								                metadesc="'.$catname.'",
							                   	metakeys="'.$catname.'"'
										);
				$objDatabase->Execute('	INSERT
										INTO	'.DBPREFIX.'content_logfile
										SET		action="update",
												history_id='.$intHistoryId.',
												is_validated="'.(($boolDirectUpdate) ? 1 : 0).'"
									');
			}
		}
	}

	function _createContactFeedbackSite()
	{
		global $objDatabase;

		// Check if the thanks page is already active
		$thxQuery = "SELECT catid FROM ".DBPREFIX."content_navigation
					 WHERE module=6 AND lang=".$_FRONTEND_LANGID;
		$objResult = $objDatabase->SelectLimit($thxQuery, 1);
		if ($objResult !== false) {
			if ($objResult->RecordCount() == 0) {
				// The thanks page doesn't exist, let's change that
				$thxQuery = "SELECT `content`,
									`title`, `cmd`, `expertmode`, `parid`,
									`displaystatus`, `displayorder`, `username`,
									`displayorder`
								  FROM ".DBPREFIX."module_repository
							 WHERE `moduleid` = 6 AND `lang`=".$_FRONTEND_LANGID;
				$objResult = $objDatabase->Execute($thxQuery);
				if ($objResult !== false) {
					$content = $objResult->fields['content'];
					$title = $objResult->fields['title'];
					$cmd = $objResult->fields['cmd'];
					$expertmode = $objResult->fields['expertmode'];
					$displaystatus = $objResult->fields['displaystatus'];
					$displayorder = $objResult->fields['displayorder'];
					$username = $objResult->fields['username'];
					$changelog = time();

					$thxQuery = "INSERT INTO ".DBPREFIX."content_navigation
								 (catname, username, changelog, cmd, displaystatus,
								  module, lang)
								 VALUES (
								 '".$title."',
								 '".$username."',
								 '".$changelog."',
								 '".$cmd."',
								 '".$displaystatus."',
								 '6',
								 '".$_FRONTEND_LANGID."')";
					$objDatabase->Execute($thxQuery);
					$thxId = $objDatabase->Insert_ID();

					$thxQuery = "INSERT INTO ".DBPREFIX."content
								 (id, content, title, metatitle, metadesc, metakeys, expertmode)
								 VALUES
								 (".$thxId.", '".$content."', '".$title."', '".$title."', '".$title."', '".$title."',
								  '".$expertmode."')";

					$objDatabase->Execute($thxQuery);
				}
			}
		}
	}

	/**
	 * Get Javascript Source
	 *
	 * Makes the sourcecode for the javascript based
	 * field checking
	 */
	function _getJsSourceCode($id, $formFields, $preview = false, $show = false)
	{
		$code = "<script src=\"lib/datepickercontrol/datepickercontrol.js\" type=\"text/javascript\"></script>\n";
		$code .= "<script type=\"text/javascript\">\n";
		$code .= "/* <![CDATA[ */\n";

		$code .= "fields = new Array();\n";

		foreach ($formFields as $key => $field) {
			$code .= "fields[$key] = Array(\n";
			$code .= "\t'".addslashes($field['name'])."',\n";
			$code .= "\t{$field['is_required']},\n";
			if ($preview) {
				$code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
			} elseif ($show) {
				$code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
			} else {
				$code .= "\t'". addslashes($this->arrCheckTypes[$field['check_type']]['regex']) ."',\n";
			}
			$code .= "\t'".$field['type']."');\n";
		}

		$code .= <<<JS_checkAllFields
function checkAllFields() {
	var isOk = true;

	for (var field in fields) {
		var type = fields[field][3];
		if (type == 'text' || type == 'file' || type == 'password' || type == 'textarea') {
			value = document.getElementsByName('contactFormField_' + field)[0].value;
			if (value == "" && isRequiredNorm(fields[field][1], value)) {
				isOk = false;
				document.getElementsByName('contactFormField_' + field)[0].style.border = "red 1px solid";
			} else if (value != "" && !matchType(fields[field][2], value)) {
				isOk = false;
				document.getElementsByName('contactFormField_' + field)[0].style.border = "red 1px solid";
			} else {
				document.getElementsByName('contactFormField_' + field)[0].style.borderColor = '';
			}
		} else if (type == 'checkbox') {
			if (!isRequiredCheckbox(fields[field][1], field)) {
				isOk = false;
			}
		} else if (type == 'checkboxGroup') {
			if (!isRequiredCheckBoxGroup(fields[field][1], field)) {
				isOk = false;
			}
		} else if (type == 'radio') {
			if (!isRequiredRadio(fields[field][1], field)) {
				isOk = false;
			}
		}
	}

	if (!isOk) {
		document.getElementById('contactFormError').style.display = "block";
	}
	return isOk;
}

JS_checkAllFields;

		// This is for checking normal text input field if they are required.
		// If yes, it also checks if the field is set. If it is not set, it returns true.
		$code .= <<<JS_isRequiredNorm
function isRequiredNorm(required, value) {
	if (required == 1) {
		if (value == "") {
			return true;
		}
	}
	return false;
}

JS_isRequiredNorm;

		// Matches the type of the value and pattern. Returns true if it matched, false if not.
		$code .= <<<JS_matchType
function matchType(pattern, value) {
	var reg = new RegExp(pattern);
	if (value.match(reg)) {
		return true;
	}
	return false;
}

JS_matchType;

		// Checks if a checkbox is required but not set. Returns false when finding an error.
		$code .= <<<JS_isRequiredCheckbox
function isRequiredCheckbox(required, field) {
	if (required == 1) {
		if (!document.getElementsByName('contactFormField_' + field)[0].checked) {
			document.getElementsByName('contactFormField_' + field)[0].style.border = "red 1px solid";
			return false;
		}
	}
	document.getElementsByName('contactFormField_' + field)[0].style.borderColor = '';

	return true;
}

JS_isRequiredCheckbox;

		// Checks if a multile checkbox is required but not set. Returns false when finding an error.
		$code .= <<<JS_isRequiredCheckBoxGroup
function isRequiredCheckBoxGroup(required, field) {
	if (required == true) {
		var boxes = document.getElementsByName('contactFormField_' + field + '[]');
		var checked = false;
		for (var i = 0; i < boxes.length; i++) {
 			if (boxes[i].checked) {
				checked = true;
			}
		}
		if (checked) {
			setListBorder('contactFormField_' + field + '[]', false);
			return true;
		} else {
			setListBorder('contactFormField_' + field + '[]', '1px red solid');
			return false;
		}
	} else {
		return true;
	}
}

JS_isRequiredCheckBoxGroup;

		// Checks if some radio button need to be checked. Returns false if it finds an error
		$code .= <<<JS_isRequiredRadio
function isRequiredRadio(required, field) {
	if (required == 1) {
		var buttons = document.getElementsByName('contactFormField_' + field);
		var checked = false;
		for (var i = 0; i < buttons.length; i++) {
			if (buttons[i].checked) {
				checked = true;
			}
		}
		if (checked) {
			setListBorder('contactFormField_' + field, false);
			return true;
		} else {
			setListBorder('contactFormField_' + field, '1px red solid');
			return false;
		}
	} else {
		return true;
	}
}

JS_isRequiredRadio;

		// Sets the border attribute of a group of checkboxes or radiobuttons
		$code .= <<<JS_setListBorder
function setListBorder(field, borderColor) {
	var boxes = document.getElementsByName(field);
	for (var i = 0; i < boxes.length; i++) {
		if (borderColor) {
			boxes[i].style.border = borderColor;
		} else {
			boxes[i].style.borderColor = '';
		}
	}
}

JS_setListBorder;

		$code .= <<<JS_misc
/* ]]> */
</script>

JS_misc;
		return $code;
	}
}
?>
