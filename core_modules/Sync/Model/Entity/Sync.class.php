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
 * Sync
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_sync
 */

namespace Cx\Core_Modules\Sync\Model\Entity;

/**
 * Sync
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_sync
 */
class Sync extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $toUri
     */
    protected $toUri;

    /**
     * @var string $apiKey
     */
    protected $apiKey;

    /**
     * @var boolean $active
     */
    protected $active;

    /**
     * @var integer $dataAccess
     */
    protected $dataAccess;

    /**
     * @var Cx\Core_Modules\Sync\Model\Entity\Relation
     */
    protected $relations;

    public function __construct()
    {
        $this->relations = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set toUri
     *
     * @param string $toUri
     */
    public function setToUri($toUri) {
        $this->toUri = $toUri;
    }

    /**
     * Get toUri
     *
     * @return string
     */
    public function getToUri($entityIndexData = array()) {
        if (!count($entityIndexData)) {
            return $this->toUri();
        }
        
        $indexData = implode('/', $entityIndexData);
        
        //<domain>(/<offset>)/api/sync/<apiVersion>/<outputModule>/<dataSource>/<parameters>[(?apikey=<apikey>(&<options>))|?<options>]
        //<domain>(/<offset>)/api/sync/<apiVersion>/<outputModule>/[[DATA_SOURCE]]/[[INDEX_DATA]][(?apikey=[[API_KEY]](&<options>))|?<options>]
        $uri = $this->toUri;
        $uri = str_replace('[[DATA_SOURCE]]', $this->getDataAccess()->getName(), $uri);
        $uri = str_replace('[[API_KEY]]', $this->getApiKey(), $uri);
        $uri = str_replace('[[INDEX_DATA]]', $indexData, $uri);
        return $uri;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Get apiKey
     *
     * @return string
     */
    public function getApiKey() {
        return $this->apiKey;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active) {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Set dataAccess
     *
     * @param integer $dataAccess
     */
    public function setDataAccess($dataAccess)
    {
        $this->dataAccess = $dataAccess;
    }

    /**
     * Get dataAccess
     *
     * @return integer $dataAccess
     */
    public function getDataAccess()
    {
        $em = $this->cx->getDb()->getEntityManager();
        $dataAccessRepo = $em->getRepository('Cx\Core_Modules\DataAccess\Model\Entity\DataAccess');
        return $dataAccessRepo->find($this->dataAccess);
        return $this->dataAccess;
    }

    /**
     * Add relation
     *
     * @param Cx\Core_Modules\Sync\Model\Entity\Relation $relation
     */
    public function addRelation(\Cx\Core_Modules\Sync\Model\Entity\Relation $relation)
    {
        $this->relations[] = $relation;
    }

    /**
     * Get relations
     *
     * @return Doctrine\Common\Collections\Collection $relations
     */
    public function getRelations()
    {
        return $this->relations;
    }
    
    /**
     * Pushes entity changes to remote
     * @param string $eventType One of "post", "put", "delete"
     * @param array $entityIndexData Field=>value-type array with primary key data
     * @param \Cx\Model\Base\EntityBase $entity (optional) Entity for "post" and "put"
     * @throws   HTTP_Request2_Exception
     */
    public function push($eventType, $entityIndexData, $entity) {
        if (!$this->getActive()) {
            return;
        }
        
        $url = $this->getToUri($entityIndexData);
        $method = strtoupper($eventType);
        $content = $entity;
        
        $config = array(
        );
        
        $request = new \HTTP_Request2($url, $method, $config);
        $request->setBody($content);
        
        $request->send();
    }
}
