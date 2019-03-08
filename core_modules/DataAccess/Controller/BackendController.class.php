<?php
/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2019
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
 * Backend controller to create a default backend view.
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_modules_dataaccess
 * @version     5.0.0
 */
namespace Cx\Core_Modules\DataAccess\Controller;

/**
 * Backend controller to create a default backend view.
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_modules_dataaccess
 * @version     5.0.0
 */
class BackendController
    extends \Cx\Core\Core\Model\Entity\SystemComponentBackendController
{
    /**
     * This function returns the ViewGeneration options for a given entityClass
     *
     * @access protected
     * @global $_ARRAYLANG
     * @param $entityClassName string contains the FQCN from entity
     * @param $dataSetIdentifier string if $entityClassName is DataSet, this is
     *                                  used for better partition
     * @return array with options
     */
    protected function getViewGeneratorOptions(
        $entityClassName, $dataSetIdentifier = ''
    ) {
        $options = parent::getViewGeneratorOptions(
            $entityClassName,
            $dataSetIdentifier
        );

        switch ($entityClassName) {
            case 'Cx\Core_Modules\DataAccess\Model\Entity\ApiKey':
                $options['fields'] = array(
                    'id' => array(
                        'table' => array(
                            'attributes' => array(
                                'class' => 'data-access-id'
                            )
                        )
                    ),
                    'dataAccessApiKeys' => array(
                        'showOverview' => false,
                        'formfield' => array(
                            'adapter' => 'DataAccess',
                            'method' => 'getDataAccessSearch'
                        ),
                        'storecallback' => array(
                            'adapter' => 'DataAccess',
                            'method' => 'storeSelectedDataAccess'
                        ),
                        'tooltip' => $_ARRAYLANG[
                            'TXT_CORE_MODULE_DATA_ACCESS_INFO_SELECT'
                        ]
                    ),
                    'dataAccessReadOnly' => array(
                        'custom' => true,
                        'showOverview' => false,
                        'formfield' => array(
                            'adapter' => 'DataAccess',
                            'method' => 'getDataAccessReadOnlySearch'
                        ),
                        'tooltip' => $_ARRAYLANG[
                            'TXT_CORE_MODULE_DATA_ACCESS_INFO_SELECT_READ_ONLY'
                        ]
                    ),
                );
                break;
        }

        return $options;
    }

    /**
     * Return true here if you want the first tab to be an entity view
     * @return boolean True if overview should be shown, false otherwise
     */
    protected function showOverviewPage()
    {
        return false;
    }
}