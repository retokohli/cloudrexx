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
 * Extended class of Doctrine EntityGenerator
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_workbench
 */
namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Extended class of Doctrine EntityGenerator
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  coremodule_workbench
 */
class EntityGenerator
{
    /**
     * @var bool
     */
    protected $_backupExisting = true;

    /** The extension to use for written php files */
    protected $_extension = '.class.php';

    /** Whether or not the current ClassMetadataInfo instance is new or old */
    protected $_isNew = true;

    protected $_staticReflection = array();

    /** Number of spaces to use for indention in generated code */
    protected $_numSpaces = 4;

    /** The actual spaces to use for indention */
    protected $_spaces = '    ';

    /** The class all generated entities should extend */
    protected $_classToExtend;

    /** Whether or not to generation annotations */
    protected $_generateAnnotations = false;

    /**
     * @var string
     */
    protected $_annotationsPrefix = '';

    /** Whether or not to generated sub methods */
    protected $_generateEntityStubMethods = false;

    /** Whether or not to update the entity class if it exists already */
    protected $_updateEntityIfExists = false;

    /** Whether or not to re-generate entity class if it exists already */
    protected $_regenerateEntityIfExists = false;

    protected static $_classTemplate =
'<?php

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

<namespace>

<entityAnnotation>
<entityClassName>
{
<entityBody>
}';

    protected static $_getMethodTemplate =
'/**
 * <description>
 *
 * @return <variableType>$<variableName>
 */
public function <methodName>()
{
<spaces>return $this-><fieldName>;
}';

    protected static $_setMethodTemplate =
'/**
 * <description>
 *
 * @param <variableType>$<variableName>
 */
public function <methodName>(<methodTypeHint>$<variableName>)
{
<spaces>$this-><fieldName> = $<variableName>;
}';

    protected static $_addMethodTemplate =
'/**
 * <description>
 *
 * @param <variableType>$<variableName>
 */
public function <methodName>(<methodTypeHint>$<variableName>)
{
<spaces>$this-><fieldName>[] = $<variableName>;
}';

    protected static $_lifecycleCallbackMethodTemplate =
'/**
 * @<name>
 */
public function <methodName>()
{
<spaces>// Add your code here
}';

    protected static $_constructorMethodTemplate =
'public function __construct()
{
<spaces><collections>
}
';

    /**
     * Generate and write entity classes for the given array of ClassMetadataInfo instances
     *
     * @param array $metadatas
     * @param string $outputDirectory 
     * @return void
     */
    public function generate(array $metadatas, $outputDirectory)
    {
        foreach ($metadatas as $metadata) {
            $this->writeEntityClass($metadata, $outputDirectory);
        }
    }

    /**
     * Generated and write entity class to disk for the given ClassMetadataInfo instance
     *
     * @param ClassMetadataInfo $metadata
     * @param string $outputDirectory 
     * @return void
     */
    public function writeEntityClass(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata, $outputDirectory)
    {
        $path = $outputDirectory . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $metadata->name) . $this->_extension;
        $dir = dirname($path);

        if ( ! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->_isNew = !file_exists($path) || (file_exists($path) && $this->_regenerateEntityIfExists);

        if ( ! $this->_isNew) {
            $this->_parseTokensInEntityFile(file_get_contents($path));
        }

        if ($this->_backupExisting && file_exists($path)) {
            $backupPath = dirname($path) . DIRECTORY_SEPARATOR . basename($path) . "~";
            if (!copy($path, $backupPath)) {
                throw new \RuntimeException("Attempt to backup overwritten entitiy file but copy operation failed.");
            }
        }

        // If entity doesn't exist or we're re-generating the entities entirely
        if ($this->_isNew) {
            file_put_contents($path, $this->generateEntityClass($metadata));
        // If entity exists and we're allowed to update the entity class
        } else if ( ! $this->_isNew && $this->_updateEntityIfExists) {
            file_put_contents($path, $this->generateUpdatedEntityClass($metadata, $path));
        }
    }

    /**
     * Generate a PHP5 Doctrine 2 entity class from the given ClassMetadataInfo instance
     *
     * @param ClassMetadataInfo $metadata 
     * @return string $code
     */
    public function generateEntityClass(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $placeHolders = array(
            '<namespace>',
            '<entityAnnotation>',
            '<entityClassName>',
            '<entityBody>'
        );

        $replacements = array(
            $this->_generateEntityNamespace($metadata),
            $this->_generateEntityDocBlock($metadata),
            $this->_generateEntityClassName($metadata),
            $this->_generateEntityBody($metadata)
        );

        $code = str_replace($placeHolders, $replacements, self::$_classTemplate);
        return str_replace('<spaces>', $this->_spaces, $code);
    }

    /**
     * Generate the updated code for the given ClassMetadataInfo and entity at path
     *
     * @param ClassMetadataInfo $metadata 
     * @param string $path 
     * @return string $code;
     */
    public function generateUpdatedEntityClass(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata, $path)
    {
        $currentCode = file_get_contents($path);

        $body = $this->_generateEntityBody($metadata);
        $body = str_replace('<spaces>', $this->_spaces, $body);
        $last = strrpos($currentCode, '}');

        return substr($currentCode, 0, $last) . $body . (strlen($body) > 0 ? "\n" : ''). "}";
    }

    /**
     * Set the number of spaces the exported class should have
     *
     * @param integer $numSpaces 
     * @return void
     */
    public function setNumSpaces($numSpaces)
    {
        $this->_spaces = str_repeat(' ', $numSpaces);
        $this->_numSpaces = $numSpaces;
    }

    /**
     * Set the extension to use when writing php files to disk
     *
     * @param string $extension 
     * @return void
     */
    public function setExtension($extension)
    {
        $this->_extension = $extension;
    }

    /**
     * Set the name of the class the generated classes should extend from
     *
     * @return void
     */
    public function setClassToExtend($classToExtend)
    {
        $this->_classToExtend = $classToExtend;
    }

    /**
     * Set whether or not to generate annotations for the entity
     *
     * @param bool $bool 
     * @return void
     */
    public function setGenerateAnnotations($bool)
    {
        $this->_generateAnnotations = $bool;
    }

    /**
     * Set an annotation prefix.
     *
     * @param string $prefix
     */
    public function setAnnotationPrefix($prefix)
    {
        $this->_annotationsPrefix = $prefix;
    }

    /**
     * Set whether or not to try and update the entity if it already exists
     *
     * @param bool $bool 
     * @return void
     */
    public function setUpdateEntityIfExists($bool)
    {
        $this->_updateEntityIfExists = $bool;
    }

    /**
     * Set whether or not to regenerate the entity if it exists
     *
     * @param bool $bool
     * @return void
     */
    public function setRegenerateEntityIfExists($bool)
    {
        $this->_regenerateEntityIfExists = $bool;
    }

    /**
     * Set whether or not to generate stub methods for the entity
     *
     * @param bool $bool
     * @return void
     */
    public function setGenerateStubMethods($bool)
    {
        $this->_generateEntityStubMethods = $bool;
    }

    /**
     * Should an existing entity be backed up if it already exists?
     */
    public function setBackupExisting($bool)
    {
        $this->_backupExisting = $bool;
    }

    protected function _generateEntityNamespace(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        if ($this->_hasNamespace($metadata)) {
            return 'namespace ' . $this->_getNamespace($metadata) .';';
        }
    }

    protected function _generateEntityClassName(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        return 'class ' . $this->_getClassName($metadata) .
            ($this->_extendsClass() ? ' extends ' . $this->_getClassToExtendName() : null);
    }

    protected function _generateEntityBody(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $fieldMappingProperties = $this->_generateEntityFieldMappingProperties($metadata);
        $associationMappingProperties = $this->_generateEntityAssociationMappingProperties($metadata);
        $stubMethods = $this->_generateEntityStubMethods ? $this->_generateEntityStubMethods($metadata) : null;
        $lifecycleCallbackMethods = $this->_generateEntityLifecycleCallbackMethods($metadata);

        $code = array();

        if ($fieldMappingProperties) {
            $code[] = $fieldMappingProperties;
        }

        if ($associationMappingProperties) {
            $code[] = $associationMappingProperties;
        }

        $code[] = $this->_generateEntityConstructor($metadata);

        if ($stubMethods) {
            $code[] = $stubMethods;
        }

        if ($lifecycleCallbackMethods) {
            $code[] = $lifecycleCallbackMethods;
        }

        return implode("\n", $code);
    }

    protected function _generateEntityConstructor(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        if ($this->_hasMethod('__construct', $metadata)) {
            return '';
        }

        $collections = array();
        foreach ($metadata->associationMappings AS $mapping) {
            if ($mapping['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY) {
                $collections[] = '$this->'.$mapping['fieldName'].' = new \Doctrine\Common\Collections\ArrayCollection();';
            }
        }
        if ($collections) {
            return $this->_prefixCodeWithSpaces(str_replace("<collections>", implode("\n", $collections), self::$_constructorMethodTemplate));
        }
        return '';
    }

    /**
     * @todo this won't work if there is a namespace in brackets and a class outside of it.
     * @param string $src
     */
    protected function _parseTokensInEntityFile($src)
    {
        $tokens = token_get_all($src);
        $lastSeenNamespace = "";
        $lastSeenClass = false;
        
        $inNamespace = false;
        $inClass = false;
        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            if (in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT))) {
                continue;
            }

            if ($inNamespace) {
                if ($token[0] == T_NS_SEPARATOR || $token[0] == T_STRING) {
                    $lastSeenNamespace .= $token[1];
                } else if (is_string($token) && in_array($token, array(';', '{'))) {
                    $inNamespace = false;
                }
            }

            if ($inClass) {
                $inClass = false;
                $lastSeenClass = $lastSeenNamespace . '\\' . $token[1];
                $this->_staticReflection[$lastSeenClass]['properties'] = array();
                $this->_staticReflection[$lastSeenClass]['methods'] = array();
            }

            if ($token[0] == T_NAMESPACE) {
                $lastSeenNamespace = "";
                $inNamespace = true;
            } else if ($token[0] == T_CLASS) {
                $inClass = true;
            } else if ($token[0] == T_FUNCTION) {
                if ($tokens[$i+2][0] == T_STRING) {
                    $this->_staticReflection[$lastSeenClass]['methods'][] = $tokens[$i+2][1];
                } else if ($tokens[$i+2][0] == T_AMPERSAND && $tokens[$i+3][0] == T_STRING) {
                    $this->_staticReflection[$lastSeenClass]['methods'][] = $tokens[$i+3][1];
                }
            } else if (in_array($token[0], array(T_VAR, T_PUBLIC, T_PRIVATE, T_PROTECTED)) && $tokens[$i+2][0] != T_FUNCTION) {
                $this->_staticReflection[$lastSeenClass]['properties'][] = substr($tokens[$i+2][1], 1);
            }
        }
    }

    protected function _hasProperty($property, \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        return (
            isset($this->_staticReflection[$metadata->name]) &&
            in_array($property, $this->_staticReflection[$metadata->name]['properties'])
        );
    }

    protected function _hasMethod($method, \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        return (
            isset($this->_staticReflection[$metadata->name]) &&
            in_array($method, $this->_staticReflection[$metadata->name]['methods'])
        );
    }

    protected function _hasNamespace(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        return strpos($metadata->name, '\\') ? true : false;
    }

    protected function _extendsClass()
    {
        return $this->_classToExtend ? true : false;
    }

    protected function _getClassToExtend()
    {
        return $this->_classToExtend;
    }

    protected function _getClassToExtendName()
    {
        $refl = new \ReflectionClass($this->_getClassToExtend());

        return '\\' . $refl->getName();
    }

    protected function _getClassName(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        return ($pos = strrpos($metadata->name, '\\'))
            ? substr($metadata->name, $pos + 1, strlen($metadata->name)) : $metadata->name;
    }

    protected function _getNamespace(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        return substr($metadata->name, 0, strrpos($metadata->name, '\\'));
    }

    protected function _generateEntityDocBlock(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $lines = array();
        $lines[] = '/**';
        $lines[] = ' * '.$metadata->name;

        if ($this->_generateAnnotations) {
            $lines[] = ' *';

            $methods = array(
                '_generateTableAnnotation',
                '_generateInheritanceAnnotation',
                '_generateDiscriminatorColumnAnnotation',
                '_generateDiscriminatorMapAnnotation'
            );

            foreach ($methods as $method) {
                if ($code = $this->$method($metadata)) {
                    $lines[] = ' * ' . $code;
                }
            }

            if ($metadata->isMappedSuperclass) {
                $lines[] = ' * @' . $this->_annotationsPrefix . 'MappedSuperClass';
            } else {
                $lines[] = ' * @' . $this->_annotationsPrefix . 'Entity';
            }

            if ($metadata->customRepositoryClassName) {
                $lines[count($lines) - 1] .= '(repositoryClass="' . $metadata->customRepositoryClassName . '")';
            }

            if (isset($metadata->lifecycleCallbacks) && $metadata->lifecycleCallbacks) {
                $lines[] = ' * @' . $this->_annotationsPrefix . 'HasLifecycleCallbacks';
            }
        }

        $lines[] = ' */';

        return implode("\n", $lines);
    }

    protected function _generateTableAnnotation($metadata)
    {
        $table = array();
        if ($metadata->table['name']) {
            $table[] = 'name="' . $metadata->table['name'] . '"';
        }

        return '@' . $this->_annotationsPrefix . 'Table(' . implode(', ', $table) . ')';
    }

    protected function _generateInheritanceAnnotation($metadata)
    {
        if ($metadata->inheritanceType != \Doctrine\ORM\Mapping\ClassMetadataInfo::INHERITANCE_TYPE_NONE) {
            return '@' . $this->_annotationsPrefix . 'InheritanceType("'.$this->_getInheritanceTypeString($metadata->inheritanceType).'")';
        }
    }

    protected function _generateDiscriminatorColumnAnnotation($metadata)
    {
        if ($metadata->inheritanceType != \Doctrine\ORM\Mapping\ClassMetadataInfo::INHERITANCE_TYPE_NONE) {
            $discrColumn = $metadata->discriminatorValue;
            $columnDefinition = 'name="' . $discrColumn['name']
                . '", type="' . $discrColumn['type']
                . '", length=' . $discrColumn['length'];

            return '@' . $this->_annotationsPrefix . 'DiscriminatorColumn(' . $columnDefinition . ')';
        }
    }

    protected function _generateDiscriminatorMapAnnotation($metadata)
    {
        if ($metadata->inheritanceType != \Doctrine\ORM\Mapping\ClassMetadataInfo::INHERITANCE_TYPE_NONE) {
            $inheritanceClassMap = array();

            foreach ($metadata->discriminatorMap as $type => $class) {
                $inheritanceClassMap[] .= '"' . $type . '" = "' . $class . '"';
            }

            return '@' . $this->_annotationsPrefix . 'DiscriminatorMap({' . implode(', ', $inheritanceClassMap) . '})';
        }
    }

    protected function _generateEntityStubMethods(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $methods = array();

        foreach ($metadata->fieldMappings as $fieldMapping) {
            if ( ! isset($fieldMapping['id']) || ! $fieldMapping['id'] || $metadata->generatorType == \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_NONE) {
                if ($code = $this->_generateEntityStubMethod($metadata, 'set', $fieldMapping['fieldName'], $fieldMapping['type'])) {
                    $methods[] = $code;
                }
            }

            if ($code = $this->_generateEntityStubMethod($metadata, 'get', $fieldMapping['fieldName'], $fieldMapping['type'])) {
                $methods[] = $code;
            }
        }

        foreach ($metadata->associationMappings as $associationMapping) {
            if ($associationMapping['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_ONE) {
                if ($code = $this->_generateEntityStubMethod($metadata, 'set', $associationMapping['fieldName'], $associationMapping['targetEntity'])) {
                    $methods[] = $code;
                }
                if ($code = $this->_generateEntityStubMethod($metadata, 'get', $associationMapping['fieldName'], $associationMapping['targetEntity'])) {
                    $methods[] = $code;
                }
            } else if ($associationMapping['type'] & \Doctrine\ORM\Mapping\ClassMetadataInfo::TO_MANY) {
                if ($code = $this->_generateEntityStubMethod($metadata, 'add', $associationMapping['fieldName'], $associationMapping['targetEntity'])) {
                    $methods[] = $code;
                }
                if ($code = $this->_generateEntityStubMethod($metadata, 'set', $associationMapping['fieldName'], null)) {
                    $methods[] = $code;
                }
                if ($code = $this->_generateEntityStubMethod($metadata, 'get', $associationMapping['fieldName'], 'Doctrine\Common\Collections\Collection')) {
                    $methods[] = $code;
                }
            }
        }

        return implode("\n\n", $methods);
    }

    protected function _generateEntityLifecycleCallbackMethods(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        if (isset($metadata->lifecycleCallbacks) && $metadata->lifecycleCallbacks) {
            $methods = array();

            foreach ($metadata->lifecycleCallbacks as $name => $callbacks) {
                foreach ($callbacks as $callback) {
                    if ($code = $this->_generateLifecycleCallbackMethod($name, $callback, $metadata)) {
                        $methods[] = $code;
                    }
                }
            }

            return implode("\n\n", $methods);
        }

        return "";
    }

    protected function _generateEntityAssociationMappingProperties(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $lines = array();

        foreach ($metadata->associationMappings as $associationMapping) {
            if ($this->_hasProperty($associationMapping['fieldName'], $metadata)) {
                continue;
            }

            $lines[] = $this->_generateAssociationMappingPropertyDocBlock($associationMapping, $metadata);
            $lines[] = $this->_spaces . 'protected $' . $associationMapping['fieldName']
                     . ($associationMapping['type'] == 'manyToMany' ? ' = array()' : null) . ";\n";
        }

        return implode("\n", $lines);
    }

    protected function _generateEntityFieldMappingProperties(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $lines = array();

        foreach ($metadata->fieldMappings as $fieldMapping) {
            if ($this->_hasProperty($fieldMapping['fieldName'], $metadata) ||
                $metadata->isInheritedField($fieldMapping['fieldName'])) {
                continue;
            }

            $lines[] = $this->_generateFieldMappingPropertyDocBlock($fieldMapping, $metadata);
            $lines[] = $this->_spaces . 'protected $' . $fieldMapping['fieldName']
                     . (isset($fieldMapping['default']) ? ' = ' . var_export($fieldMapping['default'], true) : null) . ";\n";
        }

        return implode("\n", $lines);
    }

    protected function _generateEntityStubMethod(\Doctrine\ORM\Mapping\ClassMetadataInfo $metadata, $type, $fieldName, $typeHint = null)
    {
        $methodName = $type . \Doctrine\Common\Util\Inflector::classify($fieldName);

        if ($this->_hasMethod($methodName, $metadata)) {
            return;
        }

        $var = sprintf('_%sMethodTemplate', $type);
        $template = self::$$var;

        $variableType = $typeHint ? $typeHint . ' ' : null;

        $types = \Doctrine\DBAL\Types\Type::getTypesMap();
        $methodTypeHint = $typeHint && ! isset($types[$typeHint]) ? '\\' . $typeHint . ' ' : null;

        $replacements = array(
          '<description>'       => ucfirst($type) . ' ' . $fieldName,
          '<methodTypeHint>'    => $methodTypeHint,
          '<variableType>'      => $variableType,
          '<variableName>'      => \Doctrine\Common\Util\Inflector::camelize($fieldName),
          '<methodName>'        => $methodName,
          '<fieldName>'         => $fieldName
        );

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        return $this->_prefixCodeWithSpaces($method);
    }

    protected function _generateLifecycleCallbackMethod($name, $methodName, $metadata)
    {
        if ($this->_hasMethod($methodName, $metadata)) {
            return;
        }

        $replacements = array(
            '<name>'        => $this->_annotationsPrefix . $name,
            '<methodName>'  => $methodName,
        );

        $method = str_replace(
            array_keys($replacements),
            array_values($replacements),
            self::$_lifecycleCallbackMethodTemplate
        );

        return $this->_prefixCodeWithSpaces($method);
    }

    protected function _generateJoinColumnAnnotation(array $joinColumn)
    {
        $joinColumnAnnot = array();

        if (isset($joinColumn['name'])) {
            $joinColumnAnnot[] = 'name="' . $joinColumn['name'] . '"';
        }

        if (isset($joinColumn['referencedColumnName'])) {
            $joinColumnAnnot[] = 'referencedColumnName="' . $joinColumn['referencedColumnName'] . '"';
        }

        if (isset($joinColumn['unique']) && $joinColumn['unique']) {
            $joinColumnAnnot[] = 'unique=' . ($joinColumn['unique'] ? 'true' : 'false');
        }

        if (isset($joinColumn['nullable'])) {
            $joinColumnAnnot[] = 'nullable=' . ($joinColumn['nullable'] ? 'true' : 'false');
        }

        if (isset($joinColumn['onDelete'])) {
            $joinColumnAnnot[] = 'onDelete=' . ($joinColumn['onDelete'] ? 'true' : 'false');
        }

        if (isset($joinColumn['onUpdate'])) {
            $joinColumnAnnot[] = 'onUpdate=' . ($joinColumn['onUpdate'] ? 'true' : 'false');
        }

        if (isset($joinColumn['columnDefinition'])) {
            $joinColumnAnnot[] = 'columnDefinition="' . $joinColumn['columnDefinition'] . '"';
        }

        return '@' . $this->_annotationsPrefix . 'JoinColumn(' . implode(', ', $joinColumnAnnot) . ')';
    }

    protected function _generateAssociationMappingPropertyDocBlock(array $associationMapping, \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $lines = array();
        $lines[] = $this->_spaces . '/**';
        $lines[] = $this->_spaces . ' * @var ' . $associationMapping['targetEntity'];

        if ($this->_generateAnnotations) {
            $lines[] = $this->_spaces . ' *';

            $type = null;
            switch ($associationMapping['type']) {
                case \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_ONE:
                    $type = 'OneToOne';
                    break;
                case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE:
                    $type = 'ManyToOne';
                    break;
                case \Doctrine\ORM\Mapping\ClassMetadataInfo::ONE_TO_MANY:
                    $type = 'OneToMany';
                    break;
                case \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY:
                    $type = 'ManyToMany';
                    break;
            }
            $typeOptions = array();

            if (isset($associationMapping['targetEntity'])) {
                $typeOptions[] = 'targetEntity="' . $associationMapping['targetEntity'] . '"';
            }

            if (isset($associationMapping['inversedBy'])) {
                $typeOptions[] = 'inversedBy="' . $associationMapping['inversedBy'] . '"';
            }

            if (isset($associationMapping['mappedBy'])) {
                $typeOptions[] = 'mappedBy="' . $associationMapping['mappedBy'] . '"';
            }

            if ($associationMapping['cascade']) {
                $cascades = array();

                if ($associationMapping['isCascadePersist']) $cascades[] = '"persist"';
                if ($associationMapping['isCascadeRemove']) $cascades[] = '"remove"';
                if ($associationMapping['isCascadeDetach']) $cascades[] = '"detach"';
                if ($associationMapping['isCascadeMerge']) $cascades[] = '"merge"';
                if ($associationMapping['isCascadeRefresh']) $cascades[] = '"refresh"';

                $typeOptions[] = 'cascade={' . implode(',', $cascades) . '}';            
            }

            if (isset($associationMapping['orphanRemoval']) && $associationMapping['orphanRemoval']) {
                $typeOptions[] = 'orphanRemoval=' . ($associationMapping['orphanRemoval'] ? 'true' : 'false');
            }

            $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . '' . $type . '(' . implode(', ', $typeOptions) . ')';

            if (isset($associationMapping['joinColumns']) && $associationMapping['joinColumns']) {
                $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'JoinColumns({';

                $joinColumnsLines = array();

                foreach ($associationMapping['joinColumns'] as $joinColumn) {
                    if ($joinColumnAnnot = $this->_generateJoinColumnAnnotation($joinColumn)) {
                        $joinColumnsLines[] = $this->_spaces . ' *   ' . $joinColumnAnnot;
                    }
                }

                $lines[] = implode(",\n", $joinColumnsLines);
                $lines[] = $this->_spaces . ' * })';
            }

            if (isset($associationMapping['joinTable']) && $associationMapping['joinTable']) {
                $joinTable = array();
                $joinTable[] = 'name="' . $associationMapping['joinTable']['name'] . '"';

                if (isset($associationMapping['joinTable']['schema'])) {
                    $joinTable[] = 'schema="' . $associationMapping['joinTable']['schema'] . '"';
                }

                $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'JoinTable(' . implode(', ', $joinTable) . ',';
                $lines[] = $this->_spaces . ' *   joinColumns={';

                foreach ($associationMapping['joinTable']['joinColumns'] as $joinColumn) {
                    $lines[] = $this->_spaces . ' *     ' . $this->_generateJoinColumnAnnotation($joinColumn);
                }

                $lines[] = $this->_spaces . ' *   },';
                $lines[] = $this->_spaces . ' *   inverseJoinColumns={';

                foreach ($associationMapping['joinTable']['inverseJoinColumns'] as $joinColumn) {
                    $lines[] = $this->_spaces . ' *     ' . $this->_generateJoinColumnAnnotation($joinColumn);
                }

                $lines[] = $this->_spaces . ' *   }';
                $lines[] = $this->_spaces . ' * )';
            }

            if (isset($associationMapping['orderBy'])) {
                $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'OrderBy({';

                foreach ($associationMapping['orderBy'] as $name => $direction) {
                    $lines[] = $this->_spaces . ' *     "' . $name . '"="' . $direction . '",'; 
                }

                $lines[count($lines) - 1] = substr($lines[count($lines) - 1], 0, strlen($lines[count($lines) - 1]) - 1);
                $lines[] = $this->_spaces . ' * })';
            }
        }

        $lines[] = $this->_spaces . ' */';

        return implode("\n", $lines);
    }

    protected function _generateFieldMappingPropertyDocBlock(array $fieldMapping, \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
    {
        $lines = array();
        $lines[] = $this->_spaces . '/**';
        $lines[] = $this->_spaces . ' * @var ' . $fieldMapping['type'] . ' $' . $fieldMapping['fieldName'];

        if ($this->_generateAnnotations) {
            $lines[] = $this->_spaces . ' *';

            $column = array();
            if (isset($fieldMapping['columnName'])) {
                $column[] = 'name="' . $fieldMapping['columnName'] . '"';
            }

            if (isset($fieldMapping['type'])) {
                $column[] = 'type="' . $fieldMapping['type'] . '"';
            }

            if (isset($fieldMapping['length'])) {
                $column[] = 'length=' . $fieldMapping['length'];
            }

            if (isset($fieldMapping['precision'])) {
                $column[] = 'precision=' .  $fieldMapping['precision'];
            }

            if (isset($fieldMapping['scale'])) {
                $column[] = 'scale=' . $fieldMapping['scale'];
            }

            if (isset($fieldMapping['nullable'])) {
                $column[] = 'nullable=' .  var_export($fieldMapping['nullable'], true);
            }

            if (isset($fieldMapping['columnDefinition'])) {
                $column[] = 'columnDefinition="' . $fieldMapping['columnDefinition'] . '"';
            }

            if (isset($fieldMapping['unique'])) {
                $column[] = 'unique=' . var_export($fieldMapping['unique'], true);
            }

            $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'Column(' . implode(', ', $column) . ')';

            if (isset($fieldMapping['id']) && $fieldMapping['id']) {
                $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'Id';

                if ($generatorType = $this->_getIdGeneratorTypeString($metadata->generatorType)) {
                    $lines[] = $this->_spaces.' * @' . $this->_annotationsPrefix . 'GeneratedValue(strategy="' . $generatorType . '")';
                }

                if ($metadata->sequenceGeneratorDefinition) {
                    $sequenceGenerator = array();

                    if (isset($metadata->sequenceGeneratorDefinition['sequenceName'])) {
                        $sequenceGenerator[] = 'sequenceName="' . $metadata->sequenceGeneratorDefinition['sequenceName'] . '"';
                    }

                    if (isset($metadata->sequenceGeneratorDefinition['allocationSize'])) {
                        $sequenceGenerator[] = 'allocationSize="' . $metadata->sequenceGeneratorDefinition['allocationSize'] . '"';
                    }

                    if (isset($metadata->sequenceGeneratorDefinition['initialValue'])) {
                        $sequenceGenerator[] = 'initialValue="' . $metadata->sequenceGeneratorDefinition['initialValue'] . '"';
                    }

                    $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'SequenceGenerator(' . implode(', ', $sequenceGenerator) . ')';
                }
            }

            if (isset($fieldMapping['version']) && $fieldMapping['version']) {
                $lines[] = $this->_spaces . ' * @' . $this->_annotationsPrefix . 'Version';
            }
        }

        $lines[] = $this->_spaces . ' */';

        return implode("\n", $lines);
    }

    protected function _prefixCodeWithSpaces($code, $num = 1)
    {
        $lines = explode("\n", $code);

        foreach ($lines as $key => $value) {
            $lines[$key] = str_repeat($this->_spaces, $num) . $lines[$key];
        }

        return implode("\n", $lines);
    }

    protected function _getInheritanceTypeString($type)
    {
        switch ($type) {
            case \Doctrine\ORM\Mapping\ClassMetadataInfo::INHERITANCE_TYPE_NONE:
                return 'NONE';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::INHERITANCE_TYPE_JOINED:
                return 'JOINED';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE:
                return 'SINGLE_TABLE';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::INHERITANCE_TYPE_TABLE_PER_CLASS:
                return 'PER_CLASS';

            default:
                throw new \InvalidArgumentException('Invalid provided InheritanceType: ' . $type);
        }
    }

    protected function _getChangeTrackingPolicyString($policy)
    {
        switch ($policy) {
            case \Doctrine\ORM\Mapping\ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT:
                return 'DEFERRED_IMPLICIT';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::CHANGETRACKING_DEFERRED_EXPLICIT:
                return 'DEFERRED_EXPLICIT';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::CHANGETRACKING_NOTIFY:
                return 'NOTIFY';

            default:
                throw new \InvalidArgumentException('Invalid provided ChangeTrackingPolicy: ' . $policy);
        }
    }

    protected function _getIdGeneratorTypeString($type)
    {
        switch ($type) {
            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_AUTO:
                return 'AUTO';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_SEQUENCE:
                return 'SEQUENCE';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_TABLE:
                return 'TABLE';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_IDENTITY:
                return 'IDENTITY';

            case \Doctrine\ORM\Mapping\ClassMetadataInfo::GENERATOR_TYPE_NONE:
                return 'NONE';

            default:
                throw new \InvalidArgumentException('Invalid provided IdGeneratorType: ' . $type);
        }
    }
}
