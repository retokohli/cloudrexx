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
 * TruncateOperation
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

/**
 * TruncateOperation
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */
class TruncateOperation extends \PHPUnit_Extensions_Database_Operation_Truncate
{

    /**
     * Performs truncate operation
     *
     * @param \PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     * @param \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
     * @throws \PHPUnit_Extensions_Database_Operation_Exception
     * @throws \Exception
     */
    public function execute(
        \PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, 
        \PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet
    ) {
        foreach ($dataSet->getReverseIterator() as $table) {
            /* @var $table PHPUnit_Extensions_Database_DataSet_ITable */

            /**
             * NOTE:
             *
             * Transaction not supported in TRUNCATE statement
             * Use "DELETE FROM" statement instead, slow but supports transaction
             * CASCADE not supported on DELETE query
             */
            $query = "
                DELETE FROM {$connection->quoteSchemaObject($table->getTableMetaData()->getTableName())}
            ";

            try {
                $connection->getConnection()->query('SET FOREIGN_KEY_CHECKS = 0');
                $connection->getConnection()->query($query);
                $connection->getConnection()->query('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Exception $e) {
                $connection->getConnection()->query('SET FOREIGN_KEY_CHECKS = 1');

                if ($e instanceof \PDOException) {
                    throw new \PHPUnit_Extensions_Database_Operation_Exception('TRUNCATE', $query, array(), $table, $e->getMessage());
                }

                throw $e;
            }
        }
    }
}
