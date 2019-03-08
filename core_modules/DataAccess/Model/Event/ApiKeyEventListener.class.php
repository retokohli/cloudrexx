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
 * EventListener for ApiKeys
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_modules_dataaccess
 * @version     5.0.0
 */
namespace Cx\Core_Modules\DataAccess\Model\Event;

/**
 * EventListener for ApiKeys
 *
 * @copyright   Cloudrexx AG
 * @author      Sam Hawkes <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_modules_dataaccess
 * @version     5.0.0
 */
class ApiKeyEventListener extends \Cx\Core\Event\Model\Entity\DefaultEventListener
{
    /**
     * To be able to store the selected DataAccess entities when we create a
     * new ApiKey, the ApiKey entity must be persisted to the last.
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args contains the
     *                                                     persisted entity.
     * @throws \Cx\Core\Error\Model\Entity\ShinyException  if the json request
     *                                                     fails.
     * @throws \Exception if a param does not exist.
     */
    public function postPersist(\Doctrine\ORM\Event\LifecycleEventArgs $args)
    {
        $dataAccessApiKeys = array();
        if ($this->cx->getRequest()->hasParam('dataAccessApiKeys', false)) {
            $dataAccessApiKeys = $this->cx->getRequest()->getParam(
                'dataAccessApiKeys', false
            );
        }

        $dataAccessReadOnly = array();
        if ($this->cx->getRequest()->hasParam('dataAccessReadOnly', false)) {
            $dataAccessReadOnly = $this->cx->getRequest()->getParam(
                'dataAccessReadOnly', false
            );
        }

        $json = new \Cx\Core\Json\JsonData();
        $jsonResult = $json->data(
            'DataAccess',
            'storeSelectedDataAccess',
            array(
                'postedValue' => $args->getEntity(),
                'entity' => array(
                    'dataAccessApiKeys' => $dataAccessApiKeys,
                    'dataAccessReadOnly'  => $dataAccessReadOnly,
                )
            )
        );

        if ($jsonResult['status'] != 'success') {
            throw new \Cx\Core\Error\Model\Entity\ShinyException('Fail');
        }
    }

}