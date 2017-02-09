<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2016
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
 * LanguageFile
 *
 * @copyright   Cloudrexx AG
 * @author      Nicola Tommasi <nicola.tommasi@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_locale
 * @version     5.0.0
 */

namespace Cx\Core\Locale\Model\Entity;

/**
 * LanguageFile
 *
 * @copyright   Cloudrexx AG
 * @author      Nicola Tommasi <nicola.tommasi@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_locale
 * @version     5.0.0
 */
class LanguageFile extends \Cx\Core_Modules\Listing\Model\Entity\DataSet  {

    /**
     * @var \Cx\Core\Locale\Model\Entity\Language
     */
    protected $language;

    /**
     * @var \Cx\Core\Locale\Model\Entity\Placeholder[]
     */
    protected $placeholders;

    /**
     * @var string
     */
    protected $path;

    /**
     * LanguageFile constructor.
     *
     * Creates new instance of \Cx\Core\Locale\Model\Entity\LanguageFile
     *
     */
    public function __construct(\Cx\Core\Locale\Model\Entity\Language $language, $componentName='Core', $mode='frontend') {
        $this->language = $language;
        // set current language data
        $this->data = \Env::get('init')->loadLanguageData($componentName);
        // set path to yaml file
        $this->path = ASCMS_CUSTOMIZING_PATH . '/lang/' . $this->language->getIso1() . '/' . $mode . '.yaml';
        $this->placeholders = array();
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return Placeholder[]
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * @param Placeholder[] $placeholders
     */
    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getLanguageData()
    {
        return $this->languageData;
    }

    /**
     * @param array $languageData
     */
    public function setLanguageData($languageData)
    {
        $this->languageData = $languageData;
    }
}