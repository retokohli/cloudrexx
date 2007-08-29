<?php
/**
 * Contact library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_contact
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Contact library
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author		Comvation Development Team <info@comvation.com>
 * @access		public
 * @version		1.0.0
 * @package     contrexx
 * @subpackage  core_module_contact
 */
class ContactLib
{
	var $arrForms;
	var $_arrSettings;

	/**
	 * Regexpression list
	 */
	var $arrCheckTypes;


	/**
	 * Constructor
	 */
	function ContactLib()
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

	}

	function initContactForms($allLanguages = false)
	{
		global $objDatabase, $_FRONTEND_LANGID;

		if ($allLanguages) {
			$sqlWhere = '';
		} else {
			$sqlWhere = "WHERE tblForm.langId=".$_FRONTEND_LANGID;
		}

		$this->arrForms = array();

		$objContactForms = $objDatabase->Execute("SELECT tblForm.id, tblForm.name, tblForm.mails, tblForm.langId,
													tblForm.subject, tblForm.text, tblForm.feedback, tblForm.showForm, tblForm.`use_captcha`,tblForm.`use_custom_style`,tblForm.`send_copy`,
													COUNT(tblData.id) AS number, MAX(tblData.time) AS last
												FROM ".DBPREFIX."module_contact_form AS tblForm
												LEFT OUTER JOIN ".DBPREFIX."module_contact_form_data AS tblData ON tblForm.id=tblData.id_form
												".$sqlWhere."
												GROUP BY tblForm.id
												ORDER BY last DESC");
		if ($objContactForms !== false) {
			while (!$objContactForms->EOF) {
				$this->arrForms[$objContactForms->fields['id']] = array(
					'name'	=> $objContactForms->fields['name'],
					'emails'	=> $objContactForms->fields['mails'],
					'number'	=> intval($objContactForms->fields['number']),
					'subject'	=> $objContactForms->fields['subject'],
					'last'		=> intval($objContactForms->fields['last']),
					'text'		=> $objContactForms->fields['text'],
					'lang'		=> $objContactForms->fields['langId'],
					'feedback'	=> $objContactForms->fields['feedback'],
					'showForm'	=> $objContactForms->fields['showForm'],
					'useCaptcha'	=> $objContactForms->fields['use_captcha'],
					'useCustomStyle'	=> $objContactForms->fields['use_custom_style'],
					'sendCopy'	=> $objContactForms->fields['send_copy']
				);

				$objContactForms->MoveNext();
			}
		}
	}

	function initCheckTypes()
	{
		global $objDatabase;

		$this->arrCheckTypes = array(
			1	=> array(
				'regex'	=> '.*',
				'name'	=> 'TXT_CONTACT_REGEX_EVERYTHING'
			),
			2	=> array(
				'regex'	=> '^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$',
				'name'	=> 'TXT_CONTACT_REGEX_EMAIL'
			),
			3	=> array(
				'regex'	=> '^(ht|f)tp[s]?\:\/\/[A-Za-z0-9\-\:\.\?\&\=\/\#\%]*$',
				'name'	=> 'TXT_CONTACT_REGEX_URL'
			),
			4	=> array(
				'regex'	=> '^[A-Za-z'.(strtolower(CONTREXX_CHARSET) == 'utf-8' ? utf8_encode('äàáüâûôñèöéè') : 'äàáüâûôñèöéè').'\ ]*$',
				'name'	=> 'TXT_CONTACT_REGEX_TEXT'
			),
			5	=> array(
				'regex'	=> '^[0-9]*$',
				'name'	=> 'TXT_CONTACT_REGEX_NUMBERS'
			)
		);
	}

	function initSettings()
	{
		global $objDatabase;

		$this->_arrSettings = array();
		$objSettings = $objDatabase->Execute("SELECT setname, setvalue FROM ".DBPREFIX."module_contact_settings");

		if ($objSettings !== false) {
			while (!$objSettings->EOF) {
				$this->_arrSettings[$objSettings->fields['setname']] = $objSettings->fields['setvalue'];
				$objSettings->MoveNext();
			}
		}
	}

	function getSettings($reinitialize = false)
	{
		if (!isset($this->_arrSettings) || $reinitialize) {
			$this->initSettings();
		}
		return $this->_arrSettings;
	}

	function getContactFormDetails($id, &$arrEmails, &$subject, &$feedback, &$showForm, &$useCaptcha, &$sendCopy)
	{
		global $objDatabase, $_CONFIG, $_ARRAYLANG;

		$objContactForm = $objDatabase->SelectLimit("SELECT mails, subject, feedback, showForm, use_captcha, send_copy FROM ".DBPREFIX."module_contact_form WHERE id=".$id, 1);
		if ($objContactForm !== false && $objContactForm->RecordCount() == 1) {
			$this->arrForms[$id] = array();
			$arrEmails = explode(',', $objContactForm->fields['mails']);
			$subject = !empty($objContactForm->fields['subject']) ? $objContactForm->fields['subject'] : $_ARRAYLANG['TXT_CONTACT_FORM']." ".$_CONFIG['domainUrl'];
			$feedback = $objContactForm->fields['feedback'];
			$showForm = $objContactForm->fields['showForm'];
			$useCaptcha = $objContactForm->fields['use_captcha'];
			$sendCopy = $objContactForm->fields['send_copy'];
			return true;
		} else {
			return false;
		}
	}

	function getContactFormCaptchaStatus($id)
	{
		global $objDatabase;

		$objContactForm = $objDatabase->SelectLimit("SELECT use_captcha FROM ".DBPREFIX."module_contact_form WHERE id=".$id, 1);
		if ($objContactForm !== false && $objContactForm->RecordCount() == 1) {
			return $objContactForm->fields['use_captcha'];
		} else {
			return false;
		}
	}

	function getFormFields($id)
	{
		global $objDatabase;

		$arrFields = array();

		if (isset($this->arrForms[$id])) {
			$objFields  = $objDatabase->Execute("SELECT id, name, type,
						attributes, is_required,
						check_type
						FROM ".DBPREFIX."module_contact_form_field
						WHERE id_form=".$id." ORDER BY order_id");

			if ($objFields !== false) {
				while (!$objFields->EOF) {
					$arrFields[$objFields->fields['id']] = array(
						'name'			=> $objFields->fields['name'],
						'type'			=> $objFields->fields['type'],
						'attributes'	=> $objFields->fields['attributes'],
						'is_required'	=> $objFields->fields['is_required'],
						'check_type'	=> $objFields->fields['check_type']
					);
					$objFields->MoveNext();
				}
			}
			return $arrFields;
		} else {
			return false;
		}
	}

	function getFormFieldNames($id)
	{
		global $objDatabase;

		$arrFieldNames = array();

		if (isset($this->arrForms[$id])) {
			$objFields  = $objDatabase->Execute("SELECT id, name FROM ".DBPREFIX."module_contact_form_field WHERE id_form=".$id." ORDER BY order_id");

			if ($objFields !== false) {
				while (!$objFields->EOF) {
					$arrFieldNames[$objFields->fields['name']] = $objFields->fields['id'];
					$objFields->MoveNext();
				}
			}
			return $arrFieldNames;
		} else {
			return false;
		}
	}

	function isUniqueFormName($name, $id = 0)
	{
		if (is_array($this->arrForms)) {
			foreach ($this->arrForms as $formId => $arrForm) {
				if ($formId != $id && $arrForm['name'] == $name) {
					return false;
				}
			}
		}
		return true;
	}

	function updateForm($id, $name, $emails, $subject, $text, $feedback, $showForm, $useCaptcha, $useCustomStyle, $arrFields, $sendCopy)
	{
		global $objDatabase;

		$objDatabase->Execute("UPDATE ".DBPREFIX."module_contact_form SET name='".$name."', mails='".addslashes($emails)."',
				subject='".$subject."', text='".$text."', feedback='".$feedback."', showForm=".$showForm.", use_captcha=".$useCaptcha.", use_custom_style=".$useCustomStyle.", send_copy=".$sendCopy." WHERE id=".$id);

		$arrFormFields = $this->getFormFields($id);
		$arrRemoveFormFields = array_diff_assoc($arrFormFields, $arrFields);

		foreach ($arrFields as $fieldId => $arrField) {
			if (isset($arrFormFields[$fieldId])) {
				$this->_updateFormField($fieldId, addslashes($arrField['name']), $arrField['type'], addslashes($arrField['attributes']), $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
			} else {
				$this->_addFormField($id, addslashes($arrField['name']), $arrField['type'], addslashes($arrField['attributes']), $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
			}
		}

		foreach (array_keys($arrRemoveFormFields) as $fieldId) {
			$this->_deleteFormField($fieldId);
		}

		$this->initContactForms(true);
	}

	function addForm($name, $emails, $subject, $text, $feedback, $showForm, $useCaptcha, $useCustomStyle, $arrFields, $sendCopy)
	{
		global $objDatabase, $_FRONTEND_LANGID;

		if ($objDatabase->Execute("INSERT INTO ".DBPREFIX."module_contact_form
								  (`name`,`mails`, `subject`, `text`, `feedback`, `showForm`, `use_captcha`, `use_custom_style`, `send_copy`, `langId`)
								  VALUES
								  ('".$name."', '".addslashes($emails)."', '".$subject."', '".$text."', '".$feedback."', ".$showForm.", ".$useCaptcha.", ".$useCustomStyle.", ".$sendCopy.", ".$_FRONTEND_LANGID.")") !== false) {
			$formId = $objDatabase->Insert_ID();

			foreach ($arrFields as $fieldId => $arrField) {
				$this->_addFormField($formId, $arrField['name'], $arrField['type'], $arrField['attributes'], $arrField['order_id'], $arrField['is_required'], $arrField['check_type']);
			}
		}
		$_REQUEST['formId'] = $formId;

		$this->initContactForms(true);
	}

	function deleteForm($id)
	{
		global $objDatabase;

		if ($objDatabase->Execute("DELETE FROM ".DBPREFIX."module_contact_form WHERE id=".$id) !== false) {
			$this->_deleteFormFieldsByFormId($id);
			$this->_deleteFormDataByFormId($id);

			$this->initContactForms(true);

			return true;
		} else {
			return false;
		}
	}

	function _updateFormField($id, $name, $type, $attributes, $orderId, $isRequired, $checkType)
	{
		global $objDatabase;

		$objDatabase->Execute("UPDATE ".DBPREFIX."module_contact_form_field SET name='".$name."', type='".$type."', attributes='".addslashes($attributes)."', is_required='".$isRequired."', check_type='".$checkType."', order_id=".$orderId." WHERE id=".$id);
	}

	function _addFormField($formId, $name, $type, $attributes, $orderId, $isRequired, $checkType)
	{
		global $objDatabase;

		$objDatabase->Execute("INSERT INTO ".DBPREFIX."module_contact_form_field (`id_form`, `name`, `type`, `attributes`, `order_id`, `is_required`, `check_type`) VALUES (".$formId.", '".$name."', '".$type."', '".addslashes($attributes)."', ".$orderId.", '".$isRequired."', '".$checkType."')");
	}

	function _deleteFormField($id)
	{
		global $objDatabase;

		$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_contact_form_field WHERE id=".$id);
	}

	function _deleteFormFieldsByFormId($id)
	{
		global $objDatabase;

		$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_contact_form_field WHERE id_form=".$id);
	}

	function _deleteFormDataByFormId($id)
	{
		global $objDatabase;

		$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_contact_form_data WHERE id_form=".$id);
	}

	function deleteFormEntry($id)
	{
		global $objDatabase;

		$objDatabase->Execute("DELETE FROM ".DBPREFIX."module_contact_form_data WHERE id=".$id);
	}

	function getFormEntries($formId, &$arrCols, $pagingPos, &$paging, $limit = true)
	{
		global $objDatabase, $_CONFIG;

		$arrEntries = array();
		$arrCols = array();

		$query = "SELECT id, `time`, `host`, `lang`, `ipaddress`, data FROM ".DBPREFIX."module_contact_form_data WHERE id_form=".$formId." ORDER BY `time` DESC";
		$objEntry = $objDatabase->Execute($query);

		$count = $objEntry->RecordCount();
		if ($limit && $count > intval($_CONFIG['corePagingLimit'])) {
			$paging = getPaging($count, $pagingPos, "&amp;cmd=contact&amp;act=forms&amp;tpl=entries&amp;formId=".$formId, 'Kontaktformular Einträge');
			$objEntry = $objDatabase->SelectLimit($query, $_CONFIG['corePagingLimit'], $pagingPos);
		}

		if ($objEntry !== false) {
			while (!$objEntry->EOF) {
				$arrKeyValue = explode(';', $objEntry->fields['data']);
				$arrData = array();
				foreach ($arrKeyValue as $keyValue) {
					$arrTmp = explode(',', $keyValue);
					$arrData[base64_decode($arrTmp[0])] = base64_decode($arrTmp[1]);

					if (!in_array(base64_decode($arrTmp[0]), $arrCols)) {
						array_push($arrCols, base64_decode($arrTmp[0]));
					}
				}

				$arrEntries[$objEntry->fields['id']] = array(
					'time'		=> $objEntry->fields['time'],
					'host'		=> $objEntry->fields['host'],
					'lang'		=> $objEntry->fields['lang'],
					'ipaddress'	=> $objEntry->fields['ipaddress'],
					'data'		=> $arrData
				);
				$objEntry->MoveNext();
			}
		}

		return $arrEntries;
	}

	function getFormEtry($id)
	{
		global $objDatabase;

		$arrEntry;
		$arrCols = array();
		$objEntry = $objDatabase->SelectLimit("SELECT `time`, `host`, `lang`, `ipaddress`, data FROM ".DBPREFIX."module_contact_form_data WHERE id=".$id, 1);

		if ($objEntry !== false) {
			$arrKeyValue = explode(';', $objEntry->fields['data']);
			$arrData = array();
			foreach ($arrKeyValue as $keyValue) {
				$arrTmp = explode(',', $keyValue);
				$arrData[base64_decode($arrTmp[0])] = base64_decode($arrTmp[1]);

				if (!in_array(base64_decode($arrTmp[0]), $arrCols)) {
					array_push($arrCols, base64_decode($arrTmp[0]));
				}
			}

			$arrEntry = array(
				'time'		=> $objEntry->fields['time'],
				'host'		=> $objEntry->fields['host'],
				'lang'		=> $objEntry->fields['lang'],
				'ipaddress'	=> $objEntry->fields['ipaddress'],
				'data'		=> $arrData
			);
		}

		return $arrEntry;
	}
}
?>
