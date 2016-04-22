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
 * @subpackage  core_dataaccess
 */

namespace Cx\Core\DataAccess\Model\Entity;

/**
 * DataAccess
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_dataaccess
 */

class DataAccess extends \Cx\Model\Base\EntityBase {
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $dataSource
     */
    private $dataSource;

    /**
     * @var array $fieldList
     */
    private $fieldList;

    /**
     * @var array $accessCondition
     */
    private $accessCondition;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var Cx\Core\DataAccess\Model\Entity\DataAccessApiKey
     */
    private $dataAccessApiKeys;

    /**
     * @var Cx\Core_Modules\Access\Model\Entity\Permission
     */
    private $permission;

    public function __construct()
    {
        $this->dataAccessApiKeys = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function setDataSource($dataSource)
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
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add dataAccessApiKeys
     *
     * @param Cx\Core\DataAccess\Model\Entity\DataAccessApiKey $dataAccessApiKeys
     */
    public function addDataAccessApiKeys(\Cx\Core\DataAccess\Model\Entity\DataAccessApiKey $dataAccessApiKeys)
    {
        $this->dataAccessApiKeys[] = $dataAccessApiKeys;
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
     * Set permission
     *
     * @param Cx\Core_Modules\Access\Model\Entity\Permission $permission
     */
    public function setPermission(\Cx\Core_Modules\Access\Model\Entity\Permission $permission)
    {
        $this->permission = $permission;
    }

    /**
     * Get permission
     *
     * @return Cx\Core_Modules\Access\Model\Entity\Permission $permission
     */
    public function getPermission()
    {
        return $this->permission;
    }
}