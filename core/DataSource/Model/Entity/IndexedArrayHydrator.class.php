<?php declare(strict_types=1);

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
 * ArrayHydrator with automatically set indexes
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */

namespace Cx\Core\DataSource\Model\Entity;

/**
 * Exception if hydration fails because indexes cannot be found
 *
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */
class IndexedArrayHydratorException extends \Exception {}

/**
 * Automatically sets indexes and uses Doctrine's ArrayHydrator to hydrate data
 *
 * ArrayHydrator can return data indexed by specified columns. This does not
 * work for composite keys as only one column can be used as index per entity
 * class.
 * This class automatically sets the column to be used as index. If the entity
 * class has a single valued identifier, the single value identifier field is
 * simply passed to ArrayHydrator.
 * For composite keys this "adds" a new column containing the slash ("/")
 * delimited values of the composite key columns. This "virtual column" is then
 * passed to ArrayHydrator as the column to use as index. Since the column is
 * not in the list of fields selected by the ResultSetMapping ArrayHydrator does
 * not add this "virtual column" to the output (except as index).
 * @copyright   Cloudrexx AG
 * @author      Michael Ritter <michael.ritter@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  core_datasource
 */
class IndexedArrayHydrator extends \Doctrine\ORM\Internal\Hydration\ArrayHydrator {

    /**
     * @var array List of "virtual columns" used for composite key injection
     */
    protected $customIdentifierFields = array();

    /**
     * Gets all primary key fields for current result set and precalculates
     * custom fields.
     * @inheritdoc
     */
    protected function hydrateAllData() {
        $this->customIdentifierFields = array();
        // Get all involved entities and their alias
        $i = 0;
        foreach (array_unique($this->_rsm->columnOwnerMap) as $ownerAlias) {
            $className = $this->_rsm->aliasMap[$ownerAlias];
            $metaData = $this->_em->getClassMetadata($className);
            $identifierFields = $metaData->getIdentifierFieldNames();

            // resolve field (e.g. "id") to result field (e.g. "id17")
            foreach ($identifierFields as &$identifierField) {
                $possibleResultIdentfierFields = array_keys(
                    $this->_rsm->fieldMappings,
                    $identifierField
                );
                $identifierField = '';
                foreach ($possibleResultIdentfierFields as $prif) {
                    if ($this->_rsm->columnOwnerMap[$prif] == $ownerAlias) {
                        $identifierField = $prif;
                        break;
                    }
                }
                if (empty($identifierField)) {
                    throw new \IndexedArrayHydratorException('Unable to resolve index field');
                }
            }

            // set identifier field
            if (count($identifierFields) == 1) {
                $this->_rsm->indexByMap[$ownerAlias] = current($identifierFields);
            } else {
                $this->customIdentifierFields['customkey' . $i] = $identifierFields;
                $this->_rsm->indexByMap[$ownerAlias] = 'customkey' . $i;
                $i++;
            }
        }
        return parent::hydrateAllData();
    }

    /**
     * Sets correct composite key value for each entry in the result set
     * @inheritdoc
     */
    protected function hydrateRowData(array $row, array &$cache, array &$result) {
        // add composite keys
        foreach ($this->customIdentifierFields as $fieldName=>$identifierFields) {
            $indexData = array();
            foreach ($identifierFields as $identifierField) {
                $indexData[] = $row[$identifierField];
            }
            $row[$fieldName] = implode('/', $indexData);
        }
        parent::hydrateRowData($row, $cache, $result);
    }
}
