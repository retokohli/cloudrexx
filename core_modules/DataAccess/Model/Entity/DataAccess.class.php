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
 * DataAccess
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_dataaccess
 */

namespace Cx\Core_Modules\DataAccess\Model\Entity;

/**
 * DataAccess
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_dataaccess
 */

class DataAccess extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var Cx\Core\DataSource\Model\Entity\DataSource
     */
    protected $dataSource;

    /**
     * @var array $fieldList
     */
    protected $fieldList;

    /**
     * @var array $accessCondition
     */
    protected $accessCondition;

    /**
     * @var array $allowedOutputMethods
     */
    protected $allowedOutputMethods;

    /**
     * @var Cx\Core_Modules\DataAccess\Model\Entity\DataAccessApiKey
     */
    protected $dataAccessApiKeys;

    /**
     * @var Cx\Core_Modules\Sync\Model\Entity\Relation
     */
    protected $relations;

    /**
     * @var Cx\Core_Modules\Access\Model\Entity\Permission
     */
    protected $readPermission;

    /**
     * @var Cx\Core_Modules\Access\Model\Entity\Permission
     */
    protected $writePermission;

    /**
     * @var \Cx\Core_Modules\Sync\Model\Entity\Sync
     */
    protected $syncs;

    public function __construct()
    {
        $this->syncs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dataAccessApiKeys = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set dataSource
     *
     * @param string $dataSource
     */
    public function setDataSource(\Cx\Core\DataSource\Model\Entity\DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * Get dataSource
     *
     * @return string $dataSource
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Set fieldList
     *
     * @param array $fieldList
     */
    public function setFieldList($fieldList)
    {
        $this->fieldList = $fieldList;
    }

    /**
     * Get fieldList
     *
     * @return array $fieldList
     */
    public function getFieldList()
    {
        return $this->fieldList;
    }

    /**
     * Set accessCondition
     *
     * @param array $accessCondition
     */
    public function setAccessCondition($accessCondition)
    {
        $this->accessCondition = $accessCondition;
    }

    /**
     * Get accessCondition
     *
     * @return array $accessCondition
     */
    public function getAccessCondition()
    {
        return $this->accessCondition;
    }

    /**
     * Set allowedOutputMethods
     *
     * @param array $allowedOutputMethods
     */
    public function setAllowedOutputMethods($allowedOutputMethods)
    {
        $this->allowedOutputMethods = $allowedOutputMethods;
    }

    /**
     * Get allowedOutputMethods
     *
     * @return array $allowedOutputMethods
     */
    public function getAllowedOutputMethods()
    {
        return $this->allowedOutputMethods;
    }

    /**
     * Add dataAccessApiKeys
     *
     * @param Cx\Core_Modules\DataAccess\Model\Entity\DataAccessApiKey $dataAccessApiKeys
     */
    public function addDataAccessApiKeys(\Cx\Core_Modules\DataAccess\Model\Entity\DataAccessApiKey $dataAccessApiKeys)
    {
        $this->dataAccessApiKeys[] = $dataAccessApiKeys;
    }

    /**
     * Set dataAccessApiKeys
     *
     * @param array $dataAccessApiKeys
     */
    public function setDataAccessApiKeys($dataAccessApiKeys)
    {
        $this->dataAccessApiKeys = $dataAccessApiKeys;
    }

    /**
     * Get dataAccessApiKeys
     *
     * @return Doctrine\Common\Collections\Collection $dataAccessApiKeys
     */
    public function getDataAccessApiKeys()
    {
        return $this->dataAccessApiKeys;
    }

    /**
     * Add relations
     *
     * @param Cx\Core_Modules\Sync\Model\Entity\Relation $relation
     */
    public function addRelation(\Cx\Core_Modules\Sync\Model\Entity\Relation $relation)
    {
        $this->relations[] = $relation;
    }

    /**
     * Set relations
     *
     * @param array $relation
     */
    public function setRelation($relation)
    {
        $this->relations = $relation;
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
     * Set Relations
     *
     * @param array $relations
     */
    public function setRelations($relations)
    {
        $this->relations[] = $relations;
    }

    /**
     * Set read permission
     *
     * @param Cx\Core_Modules\Access\Model\Entity\Permission $readPermission
     */
    public function setReadPermission(\Cx\Core_Modules\Access\Model\Entity\Permission $readPermission)
    {
        $this->readPermission = $readPermission;
    }

    /**
     * Get read permission
     *
     * @return Cx\Core_Modules\Access\Model\Entity\Permission $readPermission
     */
    public function getReadPermission()
    {
        return $this->readPermission;
    }

    /**
     * Set write permission
     *
     * @param Cx\Core_Modules\Access\Model\Entity\Permission $writePermission
     */
    public function setWritePermission(\Cx\Core_Modules\Access\Model\Entity\Permission $writePermission)
    {
        $this->writePermission = $writePermission;
    }

    /**
     * Get write permission
     *
     * @return Cx\Core_Modules\Access\Model\Entity\Permission $writePermission
     */
    public function getWritePermission()
    {
        return $this->writePermission;
    }

    /**
     * Add Sync
     *
     * @param \Cx\Core_Modules\Sync\Model\Entity\Sync $sync
     */
    public function addSync(\Cx\Core_Modules\Sync\Model\Entity\Sync $sync)
    {
        $this->syncs[] = $sync;
    }

    /**
     * Set Sync
     *
     * @param array $sync
     */
    public function setSync($sync)
    {
        $this->syncs = $sync;
    }

    /**
     * Get Syncs
     *
     * @return Doctrine\Common\Collections\Collection $syncs
     */
    public function getSyncs()
    {
        return $this->syncs;
    }
}