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
 * Loads the language data of a specific component in a specific language
 * of either front- or backend
 *
 * Saves/Loads customized language placeholder to/from a yaml file
 *
 * @copyright   Cloudrexx AG
 * @author      Nicola Tommasi <nicola.tommasi@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_locale
 * @version     5.0.0
 */
class LanguageFile extends \Cx\Core_Modules\Listing\Model\Entity\DataSet  {

    /**
     * The locale which defines the language of the language file
     * @var \Cx\Core\Locale\Model\Entity\Locale
     */
    protected $locale;

    /**
     * An Array containing the overwritten placeholders
     * @var \Cx\Core\Locale\Model\Entity\Placeholder[]
     */
    protected $placeholders;

    /**
     * The path to the yaml file containing the customized placeholder data
     * @var string
     */
    protected $path;

    /**
     * LanguageFile constructor.
     *
     * Creates new instance of \Cx\Core\Locale\Model\Entity\LanguageFile
     * Loads component specific language data according to params
     *
     * @param \Cx\Core\Locale\Model\Entity\Locale $locale Defines the language
     * @param string $componentName Defines the component
     * @param boolean $frontend Defines wether to open the frontend or the backend specific file
     *
     */
    public function __construct(\Cx\Core\Locale\Model\Entity\Locale $locale, $componentName='Core', $frontend=true) {
        // set identifier to parse entity view correctly
        $this->setIdentifier('Cx\Core\Locale\Model\Entity\LanguageFile');

        // load component specific language data from init
        $this->locale = $locale;
        $this->data = \Env::get('init')->getComponentSpecificLanguageData($componentName, $frontend, $locale->getId());

        // set path to yaml file
        $mode = $frontend ? 'frontend' : 'backend';
        $this->path = ASCMS_CUSTOMIZING_PATH . '/lang/' . $locale->getSourceLanguage()->getIso1() . '/' . $mode . '.yaml';

        $this->placeholders = array();
    }

    /**
     * Return's the locale
     *
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Sets the locale
     *
     * @param Locale $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Returns an array containing the overwritten placeholders
     * @return Placeholder[]
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Sets the array containing the overwritten placeholders
     * @param Placeholder[] $placeholders
     */
    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * Returns the path of the customized yaml file
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the path of the customized yaml file
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }
}