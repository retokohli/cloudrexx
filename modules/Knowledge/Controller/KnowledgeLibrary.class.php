<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * knowledgeLib
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author Stefan Heinemann <sh@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_knowledge
 */

namespace Cx\Modules\Knowledge\Controller;
\Env::get('ClassLoader')->loadFile(ASCMS_LIBRARY_PATH.'/activecalendar/activecalendar.php');

/**
 * Some basic operations for the knowledge module.
 * 
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author Stefan Heinemann <sh@comvation.com>
 * @package cloudrexx
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
            \Cx\Core\Setting\Controller\Setting::init('Config', 'component', 'Yaml');
            if (isset($value)) {
                if (!\Cx\Core\Setting\Controller\Setting::isDefined('useKnowledgePlaceholders')) {
                    \Cx\Core\Setting\Controller\Setting::add('useKnowledgePlaceholders', $value, 1, \Cx\Core\Setting\Controller\Setting::TYPE_RADIO, '1:TXT_ACTIVATED,0:TXT_DEACTIVATED', 'component');
                } else {
                    \Cx\Core\Setting\Controller\Setting::set('useKnowledgePlaceholders', $value);
                   \Cx\Core\Setting\Controller\Setting::update('useKnowledgePlaceholders');
                }
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
            return \Cx\Core\Setting\Controller\Setting::getValue('useKnowledgePlaceholders','Config');
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