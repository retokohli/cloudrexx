<?php
/**
 * Framework language
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 * @todo        Edit PHP DocBlocks!
 */

/**
 * Framework language
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  lib_framework
 */
class FWLanguage
{
    var $arrLanguage = NULL;

    function FWLanguage()
    {
        $this->__construct();
    }

    /**
    * Constructor
    *
    * @access private
    * @global object $objDatabase
    */
    function __construct()
    {
    	global $objDatabase;

     	$objLanguages = $objDatabase->Execute("SELECT id, lang, name, charset, themesid, frontend, backend, is_default FROM ".DBPREFIX."languages ORDER BY id");

     	if ($objLanguages !== false) {
		 	while (!$objLanguages->EOF) {
				$this->arrLanguage[$objLanguages->fields['id']] = array(
					'id'			=> $objLanguages->fields['id'],
					'lang'		=> $objLanguages->fields['lang'],
					'name'		=> $objLanguages->fields['name'],
					'charset'	=> $objLanguages->fields['charset'],
					'themesid'	=> $objLanguages->fields['themesid'],
					'frontend'	=> $objLanguages->fields['frontend'],
					'backend'	=> $objLanguages->fields['backend'],
					'is_default'	=> $objLanguages->fields['is_default']
				);
				$objLanguages->MoveNext();
			}
     	}
    }

	/**
	* Gets all language information as an indexed array
	*
	* @return array $languageInfo indexed array
	* @access public
	*/
	function getLanguageArray()
	{
		return $this->arrLanguage;
	}

	/**
	* Gets all language information as an indexed array
	*
	* @return array $languageInfo indexed array
	* @access public
	*/
	function getLanguageParameter($id, $index)
	{
		return isset($this->arrLanguage[$id][$index]) ? $this->arrLanguage[$id][$index] : false;
	}
}
?>