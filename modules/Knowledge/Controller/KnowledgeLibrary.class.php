<?php

/**
 * knowledgeLib
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Stefan Heinemann <sh@comvation.com>
 * @package     contrexx
 * @subpackage  module_knowledge
 */

namespace Cx\Modules\Knowledge\Controller;
\Env::get('ClassLoader')->loadFile(ASCMS_LIBRARY_PATH.'/activecalendar/activecalendar.php');

/**
 * Some basic operations for the knowledge module.
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author Stefan Heinemann <sh@comvation.com>
 * @package contrexx
 * @subpackage  module_knowledge
 */
class KnowledgeLibrary {
    /**
     * The settings object
     *
     * @var object
     */
	protected $settings;
	
	/**
	 * The articles object
	 *
	 * @var object
	 */
	protected $articles;
	
	/**
	 * The categories object
	 *
	 * @var object
	 */
	protected $categories;
	
	/**
	 * The tags object
	 *
	 * @var object
	 */
	protected $tags;

	/**
	 * Initialise the needed objects
     */
	public function __construct() {
		$this->categories = new KnowledgeCategory();
		$this->articles = new KnowledgeArticles();
		$this->settings = new KnowledgeSettings();
		$this->tags = new KnowledgeTags();
		
		$this->_arrLanguages 	= $this->createLanguageArray();
	}
	
	/**
	 * Update the global setting
	 *
	 * @param int $value
	 * @throws DatabaseError
	 * @global $objDatabase
	 */
	protected function updateGlobalSetting($value)
	{
            \Cx\Core\Setting\Controller\Setting::init('Config', 'component','Yaml');
            if (isset($value)) {
                \Cx\Core\Setting\Controller\Setting::set('useKnowledgePlaceholders', $value);
                \Cx\Core\Setting\Controller\Setting::update('useKnowledgePlaceholders');
            }
	}
	
	/**
	 * Return the global setting
	 *
	 * @return string
	 * @throws DatabaseError
	 * @global $objDatabase
	 * @return mixed
	 */
	protected function getGlobalSetting()
	{
            //return the global setting('useKnowledgePlaceholders') value
            \Cx\Core\Setting\Controller\Setting::init('Config', 'component','Yaml');
            return \Cx\Core\Setting\Controller\Setting::getValue('useKnowledgePlaceholders');
	}
	
	/**
	 * Creates an array containing all frontend-languages.
	 *
	 * Contents:
	 * $arrValue[$langId]['short']		=>	For Example: en, de, fr, ...
	 * $arrValue[$langId]['long']		=>	For Example: 'English', 'Deutsch', 'French', ...
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
}