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
 * Extended class of \MwbExporter\Formatter\Doctrine2\Yaml\Model\Table
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_workbench
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Extended class of \MwbExporter\Formatter\Doctrine2\Yaml\Model\Table
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_workbench
 */
class MwbExporterTable extends \MwbExporter\Formatter\Doctrine2\Yaml\Model\Table
{
    /**
     * Converts Table into Yaml
     *
     * @return \MwbExporter\Formatter\Doctrine2\Yaml
     */
    public function asYAML()
    {
        if ($this->getConfig()->get(Doctrine2YamlFormatter::CFG_AUTOMATIC_REPOSITORY)) {
            $this->getConfig()->set(
                Doctrine2YamlFormatter::CFG_REPOSITORY_NAMESPACE,
                $this->getRespositoryNamespace()
            );
        }
        return parent::asYAML();
    }

    /**
     * {@inheritdoc}
     */
    protected function getVars()
    {
        return array_merge(
            parent::getVars(),
            array(
                '%entity-namespace%' => str_replace('\\', '.', $this->getEntityNamespace())
            )
        );
    }

    /**
     * Get the component namespace
     */
    public function getComponentNamespace()
    {
        $matches = array();
        if (  !preg_match("/(core_module|module|core)_(.*)/i", $this->getRawTableName(), $matches)
            && count($matches) != 3 
        ) {
            return '';
        }
        $componentTypeNS = \Cx\Core\Core\Model\Entity\SystemComponent::getBaseNamespaceForType($matches[1]);
        $tableArray      = explode('_', $matches[2]);
        $componentName   = $this->getComponentName(array_shift($tableArray));

        return $componentTypeNS . '\\' . ucfirst($componentName);
    }

    /**
     * Get the entity namespace.
     *
     * @return string
     */
    public function getEntityNamespace()
    {
        return $this->getComponentNamespace() .'\\Model\\Entity';
    }

    /**
     * Get the repository namespace
     *
     * @return string
     */
    public function getRespositoryNamespace()
    {
        return $this->getComponentNamespace() . '\\Model\\Repository';
    }

    /**
     * Get the table model name.
     *
     * @return string
     */
    public function getModelName()
    {
        $matches = array();
        if (  !preg_match("/(core_module|module|core)_(.*)/i", $this->getRawTableName(), $matches)
            && count($matches) != 3 
        ) {
            return parent::getModelName();
        }

        $tableArray = explode('_', $matches[2]);
        array_shift($tableArray);
        $entityName = implode('_', $tableArray);

        return $this->beautify($entityName);
    }

    /**
     * Get relations as YML
     *
     * @param array $values array of YAML contents
     *
     * @return \Cx\Core_Modules\Workbench\Model\Entity\MwbExporterTable
     */
    protected function getRelationsAsYAML(&$values)
    {
        // 1 <=> ? references
        foreach ($this->getAllLocalForeignKeys() as $local) {
            if ($this->isLocalForeignKeyIgnored($local)) {
                continue;
            }
            $targetEntity     = $local->getOwningTable()->getModelName();
            $targetEntityFQCN = $local->getOwningTable()->getModelNameAsFQCN(
                $local->getReferencedTable()->getEntityNamespace()
            );
            $mappedBy         = $local->getReferencedTable()->getModelName();
            $related          = $local->getForeignM2MRelatedName();

            $this->getDocument()->addLog(
                sprintf('  Writing 1 <=> ? relation "%s"', $targetEntity)
            );

            if ($local->isManyToOne()) {
                $this->getDocument()->addLog(
                    '  Relation considered as "1 <=> N"'
                );

                $type = 'oneToMany';
                $relationName = lcfirst(
                    $this->getRelatedVarName($targetEntity, $related, true)
                );
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge(array(
                    'targetEntity'  => $targetEntityFQCN,
                    'mappedBy'      => lcfirst(
                        $this->getRelatedVarName($mappedBy, $related)
                    ),
                    'cascade'       => $this->getFormatter()
                        ->getCascadeOption($local->parseComment('cascade')),
                    'fetch'         => $this->getFormatter()
                        ->getFetchOption($local->parseComment('fetch')),
                    'orphanRemoval' => $this->getFormatter()
                        ->getBooleanOption($local->parseComment('orphanRemoval')),
                ), $this->getJoins($local));
            } else {
                $this->getDocument()->addLog(
                    '  Relation considered as "1 <=> 1"'
                );

                $type = 'oneToOne';
                $relationName = lcfirst($targetEntity);
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge(array(
                    'targetEntity' => $targetEntityFQCN,
                    'inversedBy'   => lcfirst(
                        $this->getRelatedVarName($mappedBy, $related)
                    ),
                ), $this->getJoins($local));
            }
        }

        // N <=> ? references
        foreach ($this->getAllForeignKeys() as $foreign) {
            if ($this->isForeignKeyIgnored($foreign)) {
                continue;
            }
            $targetEntity     = $foreign->getReferencedTable()->getModelName();
            $targetEntityFQCN = $foreign->getReferencedTable()
                ->getModelNameAsFQCN($foreign->getOwningTable()->getEntityNamespace());
            $inversedBy       = $foreign->getOwningTable()->getModelName();
            $related          = $this->getRelatedName($foreign);

            $this->getDocument()->addLog(
                sprintf('  Writing N <=> ? relation "%s"', $targetEntity)
            );

            if ($foreign->isManyToOne()) {
                $this->getDocument()->addLog(
                    '  Relation considered as "N <=> 1"'
                );

                $type = 'manyToOne';
                $relationName = lcfirst(
                    $this->getRelatedVarName($targetEntity, $related)
                );
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge(array(
                    'targetEntity'  => $targetEntityFQCN,
                    'inversedBy'    => lcfirst(
                        $this->getRelatedVarName($inversedBy, $related, true)
                    ),
                ), $this->getJoins($foreign, false));
            } else {
                $this->getDocument()->addLog(
                    '  Relation considered as "1 <=> 1"'
                );

                $type = 'oneToOne';
                $relationName = lcfirst($targetEntity);
                if (!isset($values[$type])) {
                    $values[$type] = array();
                }
                $values[$type][$relationName] = array_merge(array(
                    'targetEntity'  => $targetEntityFQCN,
                    'inversedBy'    => $foreign->isUnidirectional()
                        ? null
                        : lcfirst($this->getRelatedVarName($inversedBy, $related)),
                ), $this->getJoins($foreign, false));
            }
        }

        return $this;
    }

    /**
     * Get the relational table model name
     *
     * @param string $referenceNamespace referenceNamespace
     *
     * @return string
     */
    public function getModelNameAsFQCN($referenceNamespace = null)
    {
        return $this->getEntityNamespace() . '\\' . $this->getModelName();
    }

    /**
     * Get component name by LOWER CASE name
     *
     * @param string $name component name in lowercase
     *
     * @return string
     */
    public function getComponentName($name)
    {
        if (empty($name)) {
            return;
        }

        $em         = \Cx\Core\Core\Controller\Cx::instanciate()
            ->getDb()->getEntityManager();
        $repository = $em->getRepository('\Cx\Core\Core\Model\Entity\SystemComponent');
        $component  = $repository->findOneBy(array('name' => strtolower($name)));
        if (!$component) {
            return $name;
        }

        return $component->getName();
    }
}
