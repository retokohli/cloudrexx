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
 * DataAccessRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_dataaccess
 */

namespace Cx\Core_Modules\DataAccess\Model\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * DataAccessRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_dataaccess
 */
class DataAccessRepository extends EntityRepository {
    
    /**
     * Tries to find a matching DataAccess entity for the given criteria
     * @param \Cx\Core_Modules\DataAccess\Controller\OutputController $outputModule Output module to use for parsing
     * @param \Cx\Core\DataSource\Model\Entity\DataSource $dataSource Requested data source
     * @param string $method Request method (get, post, ...)
     * @param string $requestApiKey API key used in request
     * @return boolean Wheter the current user has access to this data source or not
     */
    public function hasAccess($outputModule, $dataSource, $method, $requestApiKey) {
        $requestReadonly = $method == 'get';
        
        // do we have a DataAccess for this DataSource?
        $dataAccesses = $dataSource->getDataAccesses();
        if (!$dataAccesses->count()) {
            \DBG::msg('This DataSource has no DataAccess!');
            return false;
        }
        
        // does our apiKey match with one or more of the DataAccesses?
        $validApiKeys = array();
        foreach ($dataAccesses as $dataAccess) {
            $apiKeys = $dataAccess->getDataAccessApiKeys();
            foreach ($apiKeys as $apiKey) {
                // if write access is needed (!$requestReadonly): does the api key allow write?
                if (!$requestReadonly && $apiKey->getReadOnly()) {
                    continue;
                }
                
                if ($apiKey->getApiKey()->getApiKey() != $requestApiKey) {
                    continue;
                }
                
                $validApiKeys[] = $apiKey;
            }
        }
        
        // $validApiKeys now contains all DataAccessApiKey entities that allow
        // this request. If there's at least one, this user has access to this
        // DataAccess object.
        if (!count($validApiKeys)) {
            \DBG::msg('There\'s no DataAccess with a matching API key!');
            return false;
        }
        
        // Now let's check if one of the remaining data access objects allow
        // access:
        foreach ($validApiKeys as $apiKey) {
            $dataAccess = $apiKey->getDataAccess();
            
            $permission = null;
            if ($requestReadonly) {
                //$permission = $dataAccess->getReadPermission();
                $permission = $dataAccess->getPermission();
            } else {
                $permission = $dataAccess->getWritePermission();
            }
            if (!$permission || $permission->hasAccess()) {
                return true;
            }
        }
        \DBG::msg('Your API key does not allow access to this DataSource!');
        return false;
    }
}

