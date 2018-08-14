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
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_mediasource
 */

namespace Cx\Core\MediaSource\Controller;

use Cx\Core\Core\Model\Entity\SystemComponentController;

/**
 * Class ComponentController
 *
 * @copyright   Cloudrexx AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  core_mediasource
 */
class ComponentController
    extends SystemComponentController
{
    /**
     * Includ all registered indexes
     */
    protected $indexes = array();

    /**
     * Register your events here
     *
     * Do not do anything else here than list statements like
     * $this->cx->getEvents()->addEvent($eventName);
     */
    public function registerEvents() {
        $eventHandlerInstance = $this->cx->getEvents();
        $eventHandlerInstance->addEvent('mediasource.load');
    }

    public function getControllerClasses() {
        // Return an empty array here to let the component handler know that there
        // does not exist a backend, nor a frontend controller of this component.
        return array();
    }

    /**
     * Register a new indexer.
     *
     * @param $indexer string class name
     * @param $type    string type of indexer
     *
     * @throws \Exception if an index already exists with this extension type
     * @return void
     */
    public function registerIndexer($indexer, $type)
    {
        global $_ARRAYLANG;

        if (empty($this->indexes[$type])) {
            $this->indexes[$type] = $indexer;
        } else {
            throw new \Exception($_ARRAYLANG['TXT_INDEX_ALREADY_EXISTS']);
        }
    }

    /**
     * List all indexer
     *
     * @return array
     */
    public function listIndexers()
    {
        return $this->indexes;
    }

    /**
     * Get indexer by id
     *
     * @param $type string type of indexer
     *
     * @return string
     */
    public function getIndexer($type)
    {
        return $this->indexes[$type];
    }
}
