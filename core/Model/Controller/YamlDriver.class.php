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
 * Wrapper class for Doctrine YAML Driver
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     4.0.0
 * @package     cloudrexx
 * @subpackage  core_model
 */

namespace Cx\Core\Model\Controller;

/**
 * Wrapper class for Doctrine YAML Driver
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Thomas Däppen <thomas.daeppen@comvation.com>
 * @version     4.0.0
 * @package     cloudrexx
 * @subpackage  core_model
 */
class YamlDriver extends \Doctrine\ORM\Mapping\Driver\YamlDriver
{
    /**
     * @var string Class namespace prefix for custom ENUM type classes
     */
    const ENUM_CLASS_PREFIX = '\Cx\Core\Model\Data\Enum\\';

    /**
     * @var array List of registered custom ENUM type classes
     */
    protected static $enumClasses = array();

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata)
    {
        $element = $this->getElement($className, true);
        // Customizing for Cloudrexx: YamlEntity extension
        if ($element['type'] == 'YamlEntity') {
            $metadata->setCustomRepositoryClass(
                isset($element['repositoryClass']) ? $element['repositoryClass'] : null
            );
            $metadata->isMappedSuperclass = true;
        }
        parent::loadMetadataForClass($className, $metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getElement($className, $raw = false)
    {
        $result = $this->loadMappingFile($this->locator->findMappingFile($className));
        if (!$raw && $result[$className]['type'] == 'YamlEntity') {
            $result[$className]['type'] = 'entity';
        }
        if ($raw) {
            return $result[$className];
        }

        return $this->handleCustomEnumTypeClasses($className, $result);
    }

    /**
     * Makes ENUM types work in doctrine
     *
     * Turns the following YAML mapping:
     *   Cx\Type\Component\Model\Entity\Example:
     *     type: entity
     *     fields:
     *       example:
     *         type: enum
     *         values: ['foo', 'bar']
     * Into:
     *   Cx\Type\Component\Model\Entity\Example:
     *     type: entity
     *     fields:
     *       example:
     *         type: enum_component_example_example
     *
     * Additionally it creates a custom class for this ENUM in core/Model/Data/Enum/
     * and maps the type to the class.
     *
     * See also: Customizing in lib/doctrine/Doctrine/ORM/Mapping/Driver/YamlDriver.php
     * @param string $className Class name to get metadata for
     * @param string $result Result of the YAML parsing so far
     * @return string Parsed YAML
     */
    protected function handleCustomEnumTypeClasses($className, $result) {
        $classParts = explode('\\', $className);
        if (current($classParts) != 'Cx') {
            return $result[$className];
        }
        foreach ($result[$className]['fields'] as $fieldName=>&$fieldMapping) {
            if ($fieldMapping['type'] != 'enum') {
                continue;
            }
            $customEnumClassNS = static::ENUM_CLASS_PREFIX .
                $classParts[2] . '\\' . $classParts[5];
            $customEnumClassName = $customEnumClassNS . '\\' .  ucfirst($fieldName);

            // If class is already registered in this request, we abort here
            if (in_array($customEnumClassName, static::$enumClasses)) {
                continue;
            }

            // Register custom ENUM type class
            $customTypeName = strtolower(
                'enum_' . $classParts[2] . '_' . $classParts[5] . '_' . $fieldName
            );
            $cx = \Cx\Core\Core\Controller\Cx::instanciate();
            $fieldMapping['type'] = $customTypeName;
            \Doctrine\DBAL\Types\Type::addType(
                $customTypeName,
                substr($customEnumClassName, 1)
            );
            $connection = $cx->getDb()->getEntityManager()->getConnection();
            $connection->getDatabasePlatform()->registerDoctrineTypeMapping(
                $customTypeName,
                $customTypeName
            );
            static::$enumClasses[] = $customEnumClassName;

            // If class is already present, we abort here
            $classLoader = $cx->getClassLoader();
            if ($classLoader->classExists($customEnumClassName, false, true)) {
                continue;
            }

            // Create custom ENUM type class
            $customEnumClass = new \Cx\Core\Html\Sigma(
                $cx->getCodeBaseCorePath() . '/Model/View/Template/Generic'
            );
            $customEnumClass->loadTemplateFile('EnumClass.tpl');
            $customEnumClass->setVariable(array(
                'NAMESPACE' => substr($customEnumClassNS, 1),
                'CLASS_NAME' => ucfirst($fieldName),
            ));
            $first = true;
            foreach ($fieldMapping['values'] as $value) {
                if ($first) {
                    $first = false;
                    $customEnumClass->hideBlock('nonfirst');
                } else {
                    $customEnumClass->touchBlock('nonfirst');
                }
                $customEnumClass->setVariable('VALUE', $value);
                $customEnumClass->parse('value');
            }

            $customClassFileName = $cx->getCodeBaseCorePath() .
                '/Model/Data/Enum/' . $classParts[2] . '/' . $classParts[5] .
                '/' . ucfirst($fieldName) . '.class.php';
            \Cx\Lib\FileSystem\FileSystem::make_folder(
                dirname($customClassFileName), true
            );
            $file = new \Cx\Lib\FileSystem\File($customClassFileName);
            $file->write($customEnumClass->get());
        }
        return $result[$className];
    }
}
