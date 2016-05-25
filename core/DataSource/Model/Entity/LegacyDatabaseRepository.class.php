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
 * LegacyDatabaseRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */

namespace Cx\Core\DataSource\Model\Entity;

/**
 * LegacyDatabaseRepository
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */
class LegacyDatabaseRepository extends DataSource {
    
    public function get($elementId, $filter, $order, $limit, $offset, $fieldList) {
        $tableName = DBPREFIX . $this->getIdentifier();
        
        // $elementId
        $whereList = array();
        if (isset($elementId)) {
            $whereList[] = '`id` = "' . contrexx_raw2db($elementId) . '"';
        }
        
        // $filter
        if (count($filter)) {
            foreach ($filter as $field => $value) {
                if (count($fieldList) && !in_array($field, $fieldList)) {
                    continue;
                }
                $whereList[] = '`' . contrexx_raw2db($field) . '` = "' . contrexx_raw2db($value) . '"';
            }
        }
        
        // $order
        $orderList = array();
        if (count($order)) {
            foreach ($order as $field => $ascdesc) {
                if (count($fieldList) && !in_array($field, $fieldList)) {
                    continue;
                }
                if (!in_array($ascdesc, array('ASC', 'DESC'))) {
                    $ascdesc = 'ASC';
                }
                $orderList[] = '`' . contrexx_raw2db($field) . '` ' . $ascdesc;
            }
        }
        
        // $limit, $offset
        $limitQuery = '';
        if ($limit) {
            $limitQuery = 'LIMIT ' . intval($limit);
            if ($offset) {
                $limitQuery .= ',' . intval($offset);
            }
        }
        
        // $fieldList
        $fieldListQuery = '*';
        if (count($fieldList)) {
            $fieldListQuery = '`' . implode('`, `', $fieldList) . '`';
        }
        
        // query parsing
        $whereQuery = '';
        if (count($whereList)) {
            $whereQuery = 'WHERE ' . implode(' AND ', $whereList);
        }
        $orderQuery = '';
        if (count($orderList)) {
            $orderQuery = 'ORDER BY ' . implode(', ', $orderList);
        }
        $query = '
            SELECT
                ' . $fieldListQuery . '
            FROM
                `' . $tableName . '`
            ' . $whereQuery . '
            ' . $orderQuery . '
            ' . $limitQuery . '
        ';
        
        $result = $this->cx->getDb()->getAdoDb()->query($query);
        $data = array();
        while (!$result->EOF) {
            $data[] = $result->fields;
            $result->MoveNext();
        }
        
        return $data;//new \Cx\Core_Modules\Listing\Model\Entity\DataSet($data);//array($query);
    }
}

