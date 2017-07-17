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
 * LanguageFileException
 *
 * @copyright   Cloudrexx AG
 * @author      Nicola Tommasi <nicola.tommasi@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_locale
 * @version     5.0.0
 */
class LanguageFileException extends \Exception {}

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
     * The source language which defines the language file
     * @var \Cx\Core\Locale\Model\Entity\Language
     */
    protected $language;

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
     * @param \Cx\Core\Locale\Model\Entity\Language $language Defines the language
     * @param string $componentName Defines the component
     * @param boolean $frontend Defines wether to open the frontend or the backend specific file
     * @param boolean $onlyCustomized Defines wether to load only the customized language placeholders or all
     * @throws \Cx\Core\Locale\Model\Entity\LanguageFileException
     */
    public function __construct(\Cx\Core\Locale\Model\Entity\Language $language, $componentName='Core', $frontend=true, $onlyCustomized=true) {
        global $_ARRAYLANG;

        // set the language
        $this->language = $language;
        if (!isset($this->language)) {
            throw new LanguageFileException(
                $_ARRAYLANG['TXT_CORE_LOCALE_LANGUAGEFILE_LANGUAGE_NOT_SET']
            );
        }

        // set identifier to parse entity view correctly
        $this->setIdentifier('Cx\Core\Locale\Model\Entity\LanguageFile');

        // load component specific language data from init
        if (!$onlyCustomized) {
            $this->data = \Env::get('init')->getComponentSpecificLanguageDataByCode($componentName, $frontend, $language->getIso1(), false);
        }

        // set path to yaml file
        $mode = $frontend ? 'frontend' : 'backend';
        $this->path = ASCMS_CUSTOMIZING_PATH . '/lang/' . $language->getIso1() . '/' . $mode . '.yaml';

        // check if yaml with customized placeholders exists
        if (!\Cx\Lib\FileSystem\FileSystem::exists($this->getPath())) {
            return;
        }

        // load placeholders from yaml
        $this->placeholders = $this->load($this->getPath());

        // update the language data
        $this->updateLanguageData();
    }

    /**
     * Saves the overwritten placeholders to the YAML file
     *
     * If the folder for the YAML file doesn't exist yet, it's created.
     * If the YAML file doesn't exist yet it's created
     *
     * @param string $filename Is ignored since $this->path is used
     * @throws \Cx\Lib\FileSystem\FileSystemException
     */
    public function save($filename='') {
        // check if folder of yaml file already exists
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
     * @param \Cx\Core_Modules\Listing\Model\Entity\Exportable $exportInterface
     * @return mixed
     */
    public function export(\Cx\Core_Modules\Listing\Model\Entity\Exportable $exportInterface) {
        global $_ARRAYLANG;

        try {
            // export the placeholders to the yaml file
            return $exportInterface->export($this->getPlaceholders());
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new \Cx\Core_Modules\Listing\Model\Entity\DataSetException(
                $_ARRAYLANG['TXT_CORE_LOCALE_LANGUAGEFILE_EXPORT_FAILED']
            );
        }
    }

    /**
     * Imports the placeholders stored in the yaml file
     * @param \Cx\Core_Modules\Listing\Model\Entity\Importable $importInterface
     * @param $content The file's content
     * @return array The array containing the placeholders
     */
    public static function import(\Cx\Core_Modules\Listing\Model\Entity\Importable $importInterface, $content) {
        global $_ARRAYLANG;

        try {
            return $importInterface->import($content);
        } catch (\Exception $e) {
            \DBG::msg($e->getMessage());
            throw new \Cx\Core_Modules\Listing\Model\Entity\DataSetException(
                $_ARRAYLANG['TXT_CORE_LOCALE_LANGUAGEFILE_IMPORT_FAILED']
            );
        }
    }

    /**
     * Updates the language data with the placeholders from the yaml file
     */
    public function updateLanguageData() {
        // update language data
        foreach ($this->placeholders as $placeholder) {
            $this->data[$placeholder->getName()] = $placeholder->getValue();
        }
    }

    /**
     * Returns the language
     * @return Language The language object
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Sets the language
     * @param Language $language The language object
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Returns an array containing the overwritten placeholders
     * @return Placeholder[] The array containing the placeholders
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Sets the array containing the overwritten placeholders
     * @param Placeholder[] $placeholders The array containing the placeholders
     */
    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * Adds a placeholder to the placeholder array
     * @param Placeholder $placeholder The placeholder to add
     */
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
     * Returns the path of the customized YAML file
     * @return string The path to the YAML file
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the path of the customized YAML file
     * @param string $path The path to the YAML file
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