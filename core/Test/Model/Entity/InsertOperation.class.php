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
 * InsertOperation
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

/**
 * InsertOperation
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */
class InsertOperation extends \PHPUnit_Extensions_Database_Operation_Insert
{
    /**
     * Get the query parameters as array
     *
     * @param \PHPUnit_Extensions_Database_DataSet_ITableMetaData   $databaseTableMetaData 
     * @param \PHPUnit_Extensions_Database_DataSet_ITable           $table
     * @param integer                                               $row
     *
     * @return array array of query parameters
     */
    protected function buildOperationArguments(
        \PHPUnit_Extensions_Database_DataSet_ITableMetaData $databaseTableMetaData,
        \PHPUnit_Extensions_Database_DataSet_ITable $table,
        $row
    ) {
        $args = array();
        foreach ($table->getTableMetaData()->getColumns() as $columnName) {
            $columnValue = $table->getValue($row, $columnName);
            $matches     = null;
            if (preg_match('/^\{src:([a-z0-9_\\\:]+)\(\)\}$/i', $columnValue, $matches)) {
                $columnValue = call_user_func($matches[1]);
            }
            $args[] = $columnValue;
        }

        return $args;
    }
}
