<?php

namespace Cx;

require_once $doctrineDir.'vendor/doctrine-common/lib/Doctrine/Common/ClassLoader.php';

class ClassLoader extends \Doctrine\Common\ClassLoader {

    protected $namespace;

    /**
     * Creates a new <tt>ClassLoader</tt> that loads classes of the
     * specified namespace from the specified include path.
     *
     * If no include path is given, the ClassLoader relies on the PHP include_path.
     * If neither a namespace nor an include path is given, the ClassLoader will
     * be responsible for loading all classes, thereby relying on the PHP include_path.
     * 
     * @param string $ns The namespace of the classes to load.
     * @param string $includePath The base include path to use.
     */
    public function __construct($ns = null, $includePath = null)
    {
        parent::__construct($ns, $includePath);
        $this->namespace = $ns;
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $classname The name of the class to load.
     * @return boolean TRUE if the class has been successfully loaded, FALSE otherwise.
     */
    public function loadClass($className)
    {
        if (!$this->canLoadClass($className)) {
            return false;
        }

        require ($this->getIncludePath() !== null ? $this->getIncludePath() . DIRECTORY_SEPARATOR : '')
               . str_replace($this->getNamespaceSeparator(), DIRECTORY_SEPARATOR, $className)
               . $this->getFileExtension();
        
        return true;
    }

    /**
     * Asks this ClassLoader whether it can potentially load the class (file) with
     * the given name.
     *
     * @param string $className The fully-qualified name of the class.
     * @return boolean TRUE if this ClassLoader can load the class, FALSE otherwise.
     */
    public function canLoadClass($className)
    {
        if ($this->namespace !== null && strpos($className, $this->namespace) !== 0) {
            return false;
        }
        return file_exists(($this->getIncludePath() !== null ? $this->getIncludePath() . DIRECTORY_SEPARATOR : '')
               . str_replace($this->getNamespaceSeparator(), DIRECTORY_SEPARATOR, $className)
               . $this->getFileExtension());
    }
}
