<?php

/*
 * Represents an abstraction of a component
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
namespace Cx\Core\Core\Model\Entity;

/**
 * Represents an abstraction of a component
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class ReflectionComponent {
    /**
     * List of all available component types
     * @todo Wouldn't it be better to move this to Component class?
     * @var array List of component types 
     */
    protected static $componentTypes = array('core', 'core_module', 'module', 'lib');
    
    /**
     * Name of the component this instance is an abstraction of
     * @var string Component name
     */
    protected $componentName = null;
    
    /**
     * Type of the component this instance is an abstraction of
     * @var string Component type
     */
    protected $componentType = null;
    
    /**
     * Two different ways to instanciate this are supported:
     * 1. Supply an instance of \Cx\Core\Core\Model\Entity\Component
     * 2. Supply a component name and type
     * @param mixed $arg1 Either an instance of \Cx\Core\Core\Model\Entity\Component or the name of a component
     * @param string $arg2 (only if a component name was supplied as $arg1) Component type (one of core_module, module, core, lib)
     */
    public function __construct($arg1, $arg2 = null) {
        if (is_a($arg1, 'Cx\Core\Core\Model\Entity\SystemComponent')) {
            $this->componentName = $arg1->getName();
            $this->componentType = $arg1->getType();
            return;
        } else if (is_string($arg1) && $arg2 && in_array($arg2, self::$componentTypes)) {
            $this->componentName = $arg1;
            $this->componentType = $arg2;
            return;
        }
        throw new \BadMethodCallException('Pass a component or specify a component name and type');
    }
    
    /**
     * Returns the components name
     * @return string Component name
     */
    public function getName() {
        return $this->componentName;
    }
    
    /**
     * Returns the components type
     * @return string Component type
     */
    public function getType() {
        return $this->componentType;
    }
    
    /**
     * Tells wheter this component is customized or not
     * @return boolean True if customized (and customizings are active)
     */
    protected function isCustomized() {
        $basepath = ASCMS_DOCUMENT_ROOT . SystemComponent::getPathForType($this->componentType);
        $componentPath = $basepath . '/' . $this->componentName;
        return \Env::get('ClassLoader')->getFilePath($componentPath) != $componentPath;
    }
    
    /**
     * Returns wheter this component exists or not
     * @param boolean $allowCustomizing (optional) Set to false if you want to ignore customizings
     * @return boolean True if it exists, false otherwise
     */
    public function exists($allowCustomizing = true) {
        return file_exists($this->getDirectory($allowCustomizing));
    }
    
    /**
     * Returns wheter this component is valid or not. A valid component will work as expected
     * @return boolean True if valid, false otherwise
     */
    public function isValid() {
        // file system
        if (!$this->exists()) {
            return false;
        }
        
        // DB: entry in components or modules
        // DB: entry in backend areas
        // DB: existing page if necessary
        
        // what else?
        
        return true;
    }
    
    /**
     * Tells wheter this is a legacy component or not
     * @return boolean True if its a legacy one, false otherwise
     */
    public function isLegacy() {
        if (!$this->exists()) {
            return false;
        }
        if (file_exists($this->getDirectory() . '/Controller/')) {
            return false;
        }
        return true;
    }
    
    /**
     * Returns the absolute path to this component's location in the file system
     * @param boolean $allowCustomizing (optional) Set to false if you want to ignore customizings
     * @return string Path for this component
     */
    public function getDirectory($allowCustomizing = true, $forceCustomized = false) {
        $docRoot = ASCMS_DOCUMENT_ROOT;
        if ($forceCustomized) {
            $allowCustomizing = false;
            $docRoot = ASCMS_CUSTOMIZING_PATH;
        }
        $basepath = $docRoot.SystemComponent::getPathForType($this->componentType);
        $componentPath = $basepath . '/' . $this->componentName;
        if (!$allowCustomizing) {
            return $componentPath;
        }
        return \Env::get('ClassLoader')->getFilePath($componentPath);
    }
    
    /**
     * This adds all necessary DB entries in order to activate this component (if they do not exist)
     * @todo Implement
     */
    public function activate() {
        $cx = \Env::get('cx');
        
        // component
        if (!$this->isLegacy()) {
            $em = $cx->getDb()->getEntityManager();
            $componentRepo = $em->getRepository('Cx\\Core\\Core\\Model\\Entity\\SystemComponent');
            if (!$componentRepo->findOneBy(array(
                'name' => $this->componentName,
                'type' => $this->componentType,
            ))) {
                $component = new \Cx\Core\Core\Model\Entity\SystemComponent();
                $component->setName($this->componentName);
                $component->setType($this->componentType);
                $em->persist($component);
                $em->flush();
            }
        }

        // modules
        $query = '
            INSERT INTO
                `' . DBPREFIX . '_modules`
                (
                    `name`,
                    `distributor`,
                    `description_variable`,
                    `status`,
                    `is_required`,
                    `is_core`,
                    `is_active`
                )
            VALUES
                (
                    \'' . $this->componentName . '\',
                    \'Comvation AG\',
                    \'TXT_' . strtoupper($this->componentType) . '_' . strtoupper($this->componentName) . '\',
                    \'y\',
                    ' . (int) $this->componentType == 'core' . ',
                    ' . (int) ($this->componentType == 'core' || $this->componentType == 'core_module') . ',
                    1
                )
        ';
        $cx->getDb()->getAdoDb()->query($query);
        
        // backend_areas
        // pages (if necessary) from repo (if has existing entry/ies) or empty one
    }
    
    /**
     * This deactivates the component (does not remove any DB entries, except for pages)
     * @todo Test queries and Doctrine Page remove()
     */
    public function deactivate() {
        $cx = \Env::get('cx');
        
        // deactivate in modules
        $adoDb = $cx->getDb()->getAdoDb();
        $query = '
            UPDATE
                `' . DBPREFIX . 'modules`
            SET
                `is_active` = 0
            WHERE
                `name` = \'' . $this->componentName . '\'
        ';
        $adoDb->execute($query);
        
        // remove pages
        $em = $cx->getDb()->getEntityManager();
        $pageRepo = $em->getRepository('Cx\\Core\\ContentManager\\Model\\Entity\\Page');
        $pages = $pageRepo->findBy(array(
            'type' => \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION,
            'module' => $this->componentName,
        ));
        foreach ($pages as $page) {
            $em->remove($page); // <-- does this work?
        }
    }
    
    /**
     * This completely removes this component from DB
     * @todo Test queries
     */
    protected function removeFromDb() {
        $cx = \Env::get('cx');
        
        // component
        $em = $cx->getDb()->getEntityManager();
        $componentRepo = $em->getRepository('Cx\\Core\\Core\\Model\\Entity\\SystemComponent');
        $systemComponent = $componentRepo->findOneBy(array(
            'type' => $this->componentType,
            'name' => $this->componentName,
        ));
        if ($systemComponent) {
            $em->remove($systemComponent->getSystemComponent());
            $em->flush();
        }
        
        // modules (legacy)
        $adoDb = $cx->getDb()->getAdoDb();
        $query = '
            SELECT
                `id`
            FROM
                `' . DBPREFIX . 'modules`
            WHERE
                `name` = \'' . $this->componentName . '\'
        ';
        $res = $adoDb->execute($query);
        $moduleId = $res->fields['id'];
        $query = '
            DELETE FROM
                `' . DBPREFIX . 'modules`
            WHERE
                `id` = \'' . $moduleId . '\'
        ';
        $adoDb->execute($query);
        
        // backend_areas
        $query = '
            DELETE FROM
                `' . DBPREFIX . 'backend_areas`
            WHERE
                `module_id` = \'' . $moduleId . '\'
        ';
        
        // pages
        $this->deactivate();
    }
    
    /**
     * Changes type or name of this component
     * 
     * This can move a component to customizing and back
     * @param string $newName New component name
     * @param string $newType New component type, one of 'core', 'core_module' and 'module'
     * @param boolean $customized (optional) Copy/move to customizing folder? Default false
     * @return ReflectionComponent ReflectionComponent for new component
     */
    public function move($newName, $newType, $customized = false) {
        return $this->internalRelocate($newName, $newType, $customized, false);
    }
    
    /**
     * Generates a copy of this component with name and type specified.
     * 
     * Using the third parameter this can be used to copy a component to
     * customizing or the other way
     * @param string $newName New component name
     * @param string $newType New component type, one of 'core', 'core_module' and 'module'
     * @param boolean $customized (optional) Copy/move to customizing folder? Default false
     * @return ReflectionComponent ReflectionComponent for new component (aka "the copy")
     */
    public function copy($newName, $newType, $customized = false) {
        return $this->internalRelocate($newName, $newType, $customized, true);
    }
    
    /**
     * Fix the namespace of all files of this component
     * @todo Update references in other components!
     * @todo Update references in DB
     * @todo Test namespaces with double backslash
     */
    public function fixNamespaces($oldBaseNs) {
        // calculate new proper base NS
        $baseNs = SystemComponent::getBaseNamespaceForType($this->componentType) . '\\' . $this->componentName;
        $baseDir = $this->getDirectory();
        
        $directoryIterator = new \RecursiveDirectoryIterator($baseDir);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $files = new \RegexIterator($iterator, '/^.+\.php$/i', \RegexIterator::GET_MATCH);
        
        // recursive foreach .php file
        foreach($files as $file) {
            // prepare data
            $file = current($file);
            //$offsetDir = substr($file, strlen($baseDir));
            //$offsetDir = preg_replace('#/[^/]*$#', '', $offsetDir);
            //$offsetNs = preg_replace('#/#', '\\', $offsetDir);
            $ns = $baseNs;// . $offsetNs;
            $oldNs = $oldBaseNs;// . $offsetNs;
            
            
            // file_get_contents()
            $objFile = new \Cx\Lib\FileSystem\File($file);
            $content = $objFile->getData();
            
            // if "namespace" cannot be found, continue (non class file or legacy one)
            if (!preg_match('/namespace ' . preg_replace('/\\\\/', '\\\\\\', $oldNs) . '/', $content)) {
                continue;
            }
            
            // replace old NS with new NS (without leading \, be sure to match \ and \\)
            $content = preg_replace(
                '/' . preg_replace('/\\\\/', '\\\\\\', $oldNs) . '/',
                $ns,
                $content
            );
            $objFile->write($content);
        }
        return true;
    }
    
    /**
     * Relocates this component (copy or move)
     * 
     * This does the following tasks
     * - Remove all DB entries for this component if moved
     * - Relocate the component in filesystem
     * - Fix namespaces of PHP class files
     * - Alter or copy pages
     * - Create DB entries for new component
     * - Activate new component
     * @todo Page changes: copy
     * @param string $newName New component name
     * @param string $newType New component type, one of 'core', 'core_module' and 'module'
     * @param boolean $customized Copy/move to customizing folder?
     * @param boolean $copy Copy or move? True means copy, default is move
     * @return ReflectionComponent New resulting component
     */
    protected function internalRelocate($newName, $newType, $customized, $copy) {
        // create new ReflectionComponent
        $newComponent = new self($newName, $newType);
        
        // move or copy pages before removing DB entries
        $em = \Env::get('cx')->getDb()->getEntityManager();
        $pageRepo = $em->getRepository('Cx\\Core\\ContentManager\\Model\\Entity\\Page');
        $pages = $pageRepo->findBy(array(
            'type' => \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION,
            'module' => $this->componentName,
        ));
        foreach ($pages as $page) {
            if ($copy) {
                // copy page
            } else {
                $page->setModule($newName);
                $em->persist($page);
            }
        }
        $em->flush();
        
        // remove old component from db (component, modules, backend_areas)
        if (!$copy) {
            $this->removeFromDb();
        }
        
        // copy/move in filesystem (name, type and customizing)
        $newLocation = $newComponent->getDirectory(false, $customized);
        $this->internalFsRelocate($newLocation, $copy);
        
        // fix namespaces
        $newComponent->fixNamespaces(SystemComponent::getBaseNamespaceForType($this->componentType) . '\\' . $this->componentName);
        
        // add new component to db and activate it (component, modules, backend_areas, pages)
        $newComponent->activate();
        
        return $newComponent;
    }
    
    /**
     * Moves or copies the filesystem part of this component to another location
     * @param string $destination Destination path
     * @param boolean $copy (optional) Copy or move? True means copy, default is move
     * @return null 
     */
    protected function internalFsRelocate($destination, $copy = false) {
        if ($destination == $this->getDirectory()) {
            // nothing to do
            return;
        }
        
        // move to correct type and name directory
        try {
            $objFile = new \Cx\Lib\FileSystem\File($this->getDirectory());
            if ($copy) {
                $objFile->copy($destination);
            } else {
                $objFile->move($destination);
            }
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
    }
}
