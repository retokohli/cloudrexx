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
 * Backend controller to create the locale backend view.
 *
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @author      Nicola Tommasi <nicola.tommasi@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_locale
 * @version     5.0.0
 */

namespace Cx\Core\Locale\Controller;

/**
 * Backend controller to create the locale backend view.
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @author      Nicola Tommasi <nicola.tommasi@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_locale
 * @version     5.0.0
 */
class BackendController extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{

    /**
     * Returns a list of available commands (?act=XY)
     * @return array List of acts
     */
    public function getCommands()
    {
        return array('Locale', 'Backend');
    }

    /**
     * Return true here if you want the first tab to be an entity view
     * @return boolean True if overview should be shown, false otherwise
     */
    protected function showOverviewPage()
    {
        return false;
    }

    /**
     * Use this to parse your backend page
     *
     * You will get the template located in /View/Template/{CMD}.html
     * You can access Cx class using $this->cx
     * To show messages, use \Message class
     * @param \Cx\Core\Html\Sigma $template Template for current CMD
     * @param array $cmd CMD separated by slashes
     */
    public function parsePage(\Cx\Core\Html\Sigma $template, array $cmd) {
        global $_ARRAYLANG;

        switch (current($cmd)) {
            case 'Backend':
                // We don't want to parse the entity view
                $this->parseBackendPage($template);
                return;
                break;
            default:
                // Parse entity view generation pages
                $entityClassName = $this->getNamespace() . '\\Model\\Entity\\' . current($cmd);
                if (in_array($entityClassName, $this->getEntityClasses())) {
                    $this->parseEntityClassPage($template, $entityClassName, current($cmd));
                    return;
                }
                break;
        }
    }

    /**
     * Parses the localization configuration page for backend
     *
     * @param \Cx\Core\Html\Sigma $template Template for cmd Backend
     */
    public function parseBackendPage($template) {
        global $_CONFIG;

        // load backend.css
        \JS::registerCSS($this->cx->getCoreFolderName() . '/Html/View/Style/Backend.css');

        // parse active language dropdown
        if (!$template->blockExists('source_languages')) {
            return;
        }
        $em = $this->cx->getDb()->getEntityManager();
        // get source languages from repository
        $languageRepo = $em->getRepository('Cx\Core\Locale\Model\Entity\Language');
        $criteria = array('source' => true);
        $languages = $languageRepo->findBy($criteria);

        // build options array for select with source languages
        $selectOptions = array();
        foreach ($languages as $language) {
            $selectOptions[$language->getIso1()] = $language->getIso1();
        }

        // get already active backend languages
        $backendRepo = $em->getRepository('Cx\Core\Locale\Model\Entity\Backend');
        $backendLanguages = $backendRepo->findAll();

        // create array of already active backend languages
        $activeLanguages = array();
        $selectedLanguages = array();
        foreach($backendLanguages as $backendLanguage) {
            // use the effective iso1 code as key for the array, to make the preselecting of getOptions() work
            $selectedLanguages[$backendLanguage->getIso1()->getIso1()] = true;
            // store id => iso1 of backend language in active languages to use for default language dropdown
            $activeLanguages[$backendLanguage->getId()] = $backendLanguage->getIso1();
        }

        // create multiple select with all languages as options
        $activeLangOptions = \Html::getOptions(
            $selectOptions,
            $selectedLanguages
        );
        $template->setVariable('SOURCE_LANGUAGES', $activeLangOptions);

        // parse default language dropdown
        if (!$template->blockExists('default_language')) {
            return;
        }

        // create single select with active languages as options
        $defaultLanguageSelect = \Html::getSelect(
            'defaultLanguage',
            $activeLanguages,
            $_CONFIG['defaultLanguageId'], // preselect default language from settings
            'defaultLanguage'
        );
        $template->setVariable('BACKEND_DEFAULT_LANGUAGE', $defaultLanguageSelect);
    }

    /**
     * This function returns the ViewGeneration options for a given entityClass
     *
     * @access protected
     * @global $_ARRAYLANG
     * @global $_CONFIG
     * @param $entityClassName contains the FQCN from entity
     * @return array with options
     */
    protected function getViewGeneratorOptions($entityClassName)
    {
        global $_ARRAYLANG;

        $classNameParts = explode('\\', $entityClassName);
        $classIdentifier = end($classNameParts);

        $langVarName = 'TXT_' . strtoupper($this->getType() . '_' . $this->getName() . '_ACT_' . $classIdentifier);
        if (isset($_ARRAYLANG[$langVarName])) {
            $header = $_ARRAYLANG[$langVarName];
        } else {
            $header = $_ARRAYLANG['TXT_CORE_LOCALE_ACT_DEFAULT'];
        }

        switch ($entityClassName) {
            case 'Cx\Core\Locale\Model\Entity\Backend':
                return array(
                    'header' => $_ARRAYLANG['TXT_CORE_LOCALE_ACT_BACKEND'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_ID'],
                        ),
                        'iso1' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_ISO1'],
                        ),
                        'language' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                    ),
                    'functions' => array(
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,
                        'filtering' => false,
                    ),
                );
                break;
            case 'Cx\Core\Locale\Model\Entity\Locale':
                if (!isset($_GET['order'])) {
                    $_GET['order'] = 'id';
                }
                return array(
                    'header' => $_ARRAYLANG['TXT_CORE_LOCALE_ACT_LOCALE'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_ID'],
                            'tooltip' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_ID'],
                        ),
                        'iso1' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_ISO1'],
                            'tooltip' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_ISO1'],
                        ),
                        'label' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_LABEL'],
                        ),
                        'country' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_COUNTRY'],
                        ),
                        'default' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_DEFAULT'],
                            'type' => 'radio',
                            'table' => array(
                                'parse' => function ($value, $rowData) {
                                    global $_CONFIG;
                                    return \Html::getRadio(
                                        'langDefaultStatus',
                                        $rowData['id'],
                                        false,
                                        $rowData['id'] == $_CONFIG['defaultLocaleId']
                                        );
                                },
                            ),
                        ),
                        'fallback' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_FALLBACK'],
                            'table' => array(
                                'parse' => function ($value, $rowData) {
                                    if (!is_object($value)) {
                                        return '';
                                    }
                                    return $value->getLabel();
                                },
                            ),
                        ),
                        'sourceLanguage' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_SOURCE_LANGUAGE'],
                        ),
                        'locales' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                    ),
                    'functions' => array(
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,
                        'filtering' => false,
                    ),
                    'order' => array(
                        'overview' => array(
                            'id',
                            'label',
                            'iso1',
                            'country',
                            'default',
                            'fallback',
                        ),
                        'form' => array(
                            'id',
                            'label',
                            'iso1',
                            'country',
                            'fallback',
                        ),
                    ),
                );
                break;
            default:
                return array(
                    'header' => $header,
                    'functions' => array(
                        'add' => true,
                        'edit' => true,
                        'delete' => true,
                        'sorting' => true,
                        'paging' => true,
                        'filtering' => false,
                    ),
                );
        }
    }

    /**
     * Returns the object to parse a wiew with
     *
     * If you overwrite this and return anything else than string, filter will not work
     * @return string|array|object An entity class name, entity, array of entities or DataSet
     */
    protected function getViewGeneratorParseObjectForEntityClass($entityClassName) {
        if ($entityClassName == 'Cx\Core\Locale\Model\Entity\Locale') {
            $em = $this->cx->getDb()->getEntityManager();
            $localeRepo = $em->getRepository($entityClassName);
            $parseObject = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($localeRepo->findAll());
            foreach ($parseObject as $index => $value) {
                $parseObject->add($index, array('default' => false));
            }
            return $parseObject;
        }
        return $entityClassName;
    }
}
