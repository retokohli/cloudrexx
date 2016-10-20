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
     * This function returns the ViewGeneration options for a given entityClass
     *
     * @access protected
     * @global $_ARRAYLANG
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
                        'fallback' => array(
                            'header' => $_ARRAYLANG['TXT_CORE_LOCALE_FIELD_FALLBACK'],
                            'table' => array(
                                'parse' => function ($value, $rowData) {
                                    $em = $this->cx->getDb()->getEntityManager();
                                    $localeRepo = $em->getRepository('Cx\Core\Locale\Model\Entity\Locale');
                                    $locale = $localeRepo->find($value);
                                    return $locale->getLabel();
                                },
                            ),
                        ),
                        'sourceLanguage' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                        'locale' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                        'locales' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                        'languageRelatedByIso1' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                        'languageRelatedBySourceLanguage' => array(
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
            //for ($i = 0; $i <= $parseObject->length(); $i++) {
            //    $parseObject->add($i, array('Default' => 'Yes'));
            //}
            $parseObject->add(1, array('Default' => 'Yes'));
            $parseObject->add(2, array('Default' => 'Yes'));
            return $parseObject;
        }
        return $entityClassName;
    }
}
