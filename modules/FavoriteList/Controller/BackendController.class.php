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
 * Backend controller to create the FavoriteList backend view.
 *
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
 * @version     5.0.0
 */

namespace Cx\Modules\FavoriteList\Controller;

/**
 * Backend controller to create the FavoriteList backend view.
 * @copyright   Cloudrexx AG
 * @author      Manuel Schenk <manuel.schenk@comvation.com>
 * @package     cloudrexx
 * @subpackage  module_favoritelist
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
        return array('Catalog', 'Favorite', 'FormField');
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
            $header = $_ARRAYLANG['TXT_MODULE_FAVORITELIST_ACT_DEFAULT'];
        }

        switch ($entityClassName) {
            case 'Cx\Modules\FavoriteList\Model\Entity\Catalog':
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_ACT_CATALOG'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_ID'],
                        ),
                        'session_id' => array(
                            'showOverview' => false,
                            'showDetail' => false,
                        ),
                        'name' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_NAME'],
                        ),
                        'date' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_DATE'],
                            'showDetail' => false,
                        ),
                        'favorites' => array(
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
            case 'Cx\Modules\FavoriteList\Model\Entity\Favorite':
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_ACT_FAVORITE'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_ID'],
                        ),
                        'title' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_TITLE'],
                        ),
                        'link' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_LINK'],
                        ),
                        'description' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_DESCRIPTION'],
                        ),
                        'info' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_INFO'],
                        ),
                        'image_1' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_IMAGE_1'],
                        ),
                        'image_2' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_IMAGE_2'],
                        ),
                        'image_3' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_IMAGE_3'],
                        ),
                        'catalog' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_CATALOG'],
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
                            'catalog',
                            'title',
                            'link',
                            'description',
                            'info',
                            'image_1',
                            'image_2',
                            'image_3',
                        ),
                        'form' => array(
                            'catalog',
                            'title',
                            'link',
                            'description',
                            'info',
                            'image_1',
                            'image_2',
                            'image_3',
                        ),
                    ),
                );
                break;
            case 'Cx\Modules\FavoriteList\Model\Entity\FormField':
                return array(
                    'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_ACT_FORMFIELD'],
                    'fields' => array(
                        'id' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_ID'],
                        ),
                        'name' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_NAME'],
                        ),
                        'type' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_TYP'],
                            'type' => 'select',
                            'validValues' => array(
                                'inputtext' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_INPUTTEXT'],
                                'textarea' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_TEXTAREA'],
                                'select' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_SELECT'],
                                'radio' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_RADIO'],
                                'checkbox' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_CHECKBOX'],
                                'mail' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_MAIL'],
                                'salutation' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_SALUTATION'],
                                'firstname' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_FIRSTNAME'],
                                'lastname' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_LASTNAME'],
                            ),
                        ),
                        'required' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_REQUIRED'],
                        ),
                        'order' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_ORDER'],
                            'showDetail' => false,
                        ),
                        'values' => array(
                            'header' => $_ARRAYLANG['TXT_MODULE_FAVORITELIST_FIELD_VALUES'],
                            'showOverview' => false,
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
}
