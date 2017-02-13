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

        // check if yaml with customized placeholders exists
        if (\Cx\Lib\FileSystem\FileSystem::exists($this->getPath())) {
            // load placeholders from yaml
            $this->placeholders = $this->load($this->getPath());
        }

        // update the language data
        $this->updateLanguageData();
    }

    /**
     * Saves the overwritten placeholders to yaml
     *
     * If the folder for the frontend.yaml doesn't exist yet, it's created.
     * If the frontend.yaml doesn't exist yet it's created
     *
     * @param string $filename Is ignored since $this->path is used
     * @throws \Cx\Lib\FileSystem\FileSystemException
     */
    public function save($filename='') {
        // check if folder of frontend.yaml already exists
        if (!\Cx\Lib\FileSystem\FileSystem::exists(dirname($this->getPath()))) {
            // folder doesn't exist, create it (recursively)
            \Cx\Lib\FileSystem\FileSystem::make_folder(dirname($this->getPath()), true);
        }
        // export placeholders to yaml file
        $this->exportToFile(
            $this->getYamlInterface(),
            $this->getPath()
        );

        // update the language data
        $this->updateLanguageData();
    }

    /**
     * Exports the overwritten placeholders, called by save method
     *
     * @param \Cx\Core_Modules\Listing\Model\Entity\Exportable $exportInterface
     * @return mixed
     */
    public function export(\Cx\Core_Modules\Listing\Model\Entity\Exportable $exportInterface) {
        try {
            return $exportInterface->export($this->getPlaceholders());
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new DataSetException('Exporting overwritten placeholders to frontend.yaml failed!');
        }
    }

    /**
     * Imports the placeholders stored in the yaml file
     * @param \Cx\Core_Modules\Listing\Model\Entity\Importable $importInterface
     * @param $content
     * @return mixed The array containing the placeholders
     */
    public static function import(\Cx\Core_Modules\Listing\Model\Entity\Importable $importInterface, $content) {
        try {
            return $importInterface->import($content);
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new DataSetException('Importing placeholders from yaml failed!');
        }
    }

    /**
     * Updates the language data with the placeholders from the yaml file
     */
    protected function updateLanguageData() {
        // update language data
        foreach ($this->placeholders as $placeholder) {
            $this->data[$placeholder->getName()] = $placeholder->getValue();
        }
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

    public function addPlaceholder($placeholder) {
        foreach ($this->getPlaceholders() as $key => $existingPlaceholders) {
            if ($existingPlaceholders->getName() == $placeholder->getName()) {
                // overwrite existing placeholder
                $this->placeholders[$key] = $placeholder;
                return;
            }
        }
        $this->placeholders[] = $placeholder;
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

    /**
     * Returns an array containing the placeholders before the overwrite
     * @return array The placeholders before overwrite
     */
    public function getData() {
        return $this->data;
    }
}