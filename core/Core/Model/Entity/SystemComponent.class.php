<?php
/**
 * A system component (aka "module", "core_module" or "core component")
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core\Core\Model\Entity;

/**
 * Thrown for illegal component types
 */
class SystemComponentException extends \Exception {}

/**
 * A system component (aka "module", "core_module" or "core component")
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class SystemComponent
{
    const TYPE_CORE = 'core';
    const TYPE_CORE_MODULE = 'core_module';
    const TYPE_MODULE = 'module';
    
    /**
     * Unique ID
     * @var integer $id
     */
    private $id;

    /**
     * Component name
     * @var string $name
     */
    private $name;
    
    /**
     * Component type
     * @var enum $type
     */
    private $type;

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
     * Set type
     *
     * @param enum $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return enum $type
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Returns the absolute path to this component's location in the file system
     * @param boolean $allowCustomizing (optional) Set to false if you want to ignore customizings
     * @return string Path for this component
     */
    public function getDirectory($allowCustomizing = true) {
        $basepath = ASCMS_DOCUMENT_ROOT.$this->getPathForType($this->getType());
        $componentPath = $basepath . '/' . $this->getName();
        if (!$allowCustomizing) {
            return $componentPath;
        }
        return \Env::get('ClassLoader')->getFilePath($componentPath);
    }
    
    /**
     * Returns the base namespace for this component
     * @return string Namespace
     */
    public function getNamespace() {
        $ns = self::getBaseNamespaceForType($this->getType());
        $ns .= '\\' . $this->getName();
        return $ns;
    }
    
    /**
     * Returns the type folder (relative to document root)
     * @param string $type Component type name
     * @return string Component type folder relative to document root
     * @throws CommandException For non-existing type
     */
    public static function getPathForType($type) {
        switch ($type) {
            case self::TYPE_CORE:
                return ASCMS_CORE_FOLDER;
                break;
            case self::TYPE_CORE_MODULE:
                return ASCMS_CORE_MODULE_FOLDER;
                break;
            case self::TYPE_MODULE:
                return ASCMS_MODULE_FOLDER;
                break;
            case 'lib':
                return ASCMS_LIBRARY_FOLDER;
                break;
            default:
                throw new SystemComponentException('No such component type "' . $type . '"');
                break;
        }
    }
    
    /**
     * Returns the namespace for a component type
     * @param string $type Component type name
     * @return string Namespace
     * @throws CommandException For non-existing type
     */
    public static function getBaseNamespaceForType($type) {
        switch ($type) {
            case 'core':
                return 'Cx\\Core';
                break;
            case 'core_module':
                return 'Cx\\Core_Modules';
                break;
            case 'module':
                return 'Cx\\Modules';
                break;
            default:
                throw new SystemComponentException('No such component type "' . $type . '"');
                break;
        }
    }
}