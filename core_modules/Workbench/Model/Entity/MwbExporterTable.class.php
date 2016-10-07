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
        $componentName   = array_shift($tableArray);

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
}
