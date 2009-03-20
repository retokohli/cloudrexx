<?php

/**
 * Class SurveyLibrary
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@comvation.com>
 * @version	   $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_survey
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Class SurveyLibrary
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Thomas Kaelin <thomas.kaelin@comvation.com>
 * @version	   $Id: index.inc.php,v 1.00 $
 * @package     contrexx
 * @subpackage  module_survey
 * @todo        Edit PHP DocBlocks!
 */
class SurveyLibrary {

	var $_intLangId;
	var $_arrSettings			= array();
	var $_arrLanguages 			= array();
	var $_arrSurveyTranslations	= array();
	var $_arrSurveyValues		= array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->_arrLanguages 			= $this->createLanguageArray();
		$this->_arrSettings				= $this->createSettingsArray();
		$this->_arrSurveyTranslations 	= $this->createSurveyTranslationArray();
		$this->_arrSurveyValues			= $this->createSurveyValuesArray();
	}


	/**
	 * Creates an array containing all frontend-languages. Example: $arrValue[$langId]['short'] or $arrValue[$langId]['long']
	 *
	 * @global 	object		$objDatabase
	 * @return	array		$arrReturn
	 */
	function createLanguageArray() {
		global $objDatabase;

		$arrReturn = array();

		$objResult = $objDatabase->Execute('SELECT		id,
														lang,
														name
											FROM		'.DBPREFIX.'languages
											WHERE		frontend=1
											ORDER BY	id
										');
		while (!$objResult->EOF) {
			$arrReturn[$objResult->fields['id']] = array(	'short'	=>	stripslashes($objResult->fields['lang']),
															'long'	=>	htmlentities(stripslashes($objResult->fields['name']),ENT_QUOTES, CONTREXX_CHARSET)
														);
			$objResult->MoveNext();
		}

		return $arrReturn;
	}


	/**
	 * Create an array containing all settings. Exapmle: $arrSettings['setname']
	 *
	 * @global 	object		$objDatabase
	 * @return 	array		$arrReturn
	 */
	function createSettingsArray() {
		global $objDatabase;

		$arrReturn = array();

		$objResult = $objDatabase->Execute('SELECT	name,
													value
											FROM	'.DBPREFIX.'module_survey_settings
										');
		while (!$objResult->EOF) {
			$arrReturn[$objResult->fields['name']] = $objResult->fields['value'];
			$objResult->MoveNext();
		}

		return $arrReturn;
	}


	/**
	 * Creates an array containing all translations of the surveys. Example: $arrValue[$surveyId][$langId].
	 *
	 * @global 	object		$objDatabase
	 * @return	array		$arrReturn
	 */
	function createSurveyTranslationArray() {
		global $objDatabase;

		$arrReturn = array();

		$objResult = $objDatabase->Execute('SELECT		group_id,
														lang_id,
														subject
											FROM		'.DBPREFIX.'module_survey_groups_lang
											ORDER BY	group_id ASC
										');

		while (!$objResult->EOF) {
			$arrReturn[$objResult->fields['group_id']][$objResult->fields['lang_id']] = htmlentities(stripslashes($objResult->fields['subject']),ENT_QUOTES, CONTREXX_CHARSET);
			$objResult->MoveNext();
		}

		return $arrReturn;
	}


	/**
	 * Creates an array containing all values of the surveys. Example: $arrValue[randomIndex]['xxx'].
	 *
	 * @global 	object		$objDatabase
	 * @return	array		$arrReturn
	 */
	function createSurveyValuesArray() {
		global $objDatabase;

		$arrReturn = array();

		$objResult = $objDatabase->Execute('SELECT		id,
														redirect,
														created,
														lastvote,
														participant,
														isActive,
														isExtended,
														isCommentable,
														isHomeBox
											FROM		'.DBPREFIX.'module_survey_groups
											ORDER BY	created DESC
										');

		$intIndex = 0;
		while (!$objResult->EOF) {
			$arrReturn[$intIndex] = array(	'id'			=> $objResult->fields['id'],
											'redirect'		=>	htmlentities($objResult->fields['redirect']),
											'created'		=>	date(ASCMS_DATE_FORMAT, $objResult->fields['created']),
											'lastvote'		=>	(intval($objResult->fields['lastvote']) == 0) ? 'Keine Teilnehmer.' : date(ASCMS_DATE_FORMAT,$objResult->fields['lastvote']),
											'participant'	=>	intval($objResult->fields['participant']),
											'isActive'		=>	intval($objResult->fields['isActive']),
											'isExtended'	=>	intval($objResult->fields['isExtended']),
											'isCommentable'	=>	intval($objResult->fields['isCommentable']),
											'isHomeBox'		=>	intval($objResult->fields['isHomeBox'])
									);
			++$intIndex;
			$objResult->MoveNext();
		}

		return $arrReturn;
	}


	/**
	 * Get the index-value for the surveyValueArray for a desired survey.
	 *
	 * @param	integer		$intSurveyId
	 * @return	integer		Index for the surveyValueArray. If the id was not found, -1 will be returned.
	 */
	function getSurveyArrayIndex($intSurveyId) {
		$intSurveyId = intval($intSurveyId);

		foreach ($this->_arrSurveyValues as $intIndex => $arrValues) {
			if ($intSurveyId == $arrValues['id']) {
				return $intIndex;
			}
		}

		return -1;
	}
}