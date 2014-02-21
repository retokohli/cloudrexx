<?php
/**
 * Represents an abstraction of a component
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */

namespace Cx\Core\Core\Model\Entity;

/**
 * ReflectionComponentException
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
 */
class ReflectionComponentException extends \Exception {}

/**
 * Represents an abstraction of a component
 *
 * @copyright   Comvation AG
 * @author      Michael Ritter <michael.ritter@comvation.com>
 * @package     contrexx
 * @subpackage  core_core
 * @version     3.1.0
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
     * Fully qualified filename for/of the package file
     * @var string ZIP package filename
     */
    protected $packageFile = null;

    /**
     * Two different ways to instanciate this are supported:
     * 1. Supply an instance of \Cx\Core\Core\Model\Entity\Component
     * 2. Supply a install package zip filename
     * 3. Supply a component name and type
     * @param mixed $arg1 Either an instance of \Cx\Core\Core\Model\Entity\Component or the name of a component
     * @param string|null $arg2 (only if a component name was supplied as $arg1) Component type (one of core_module, module, core, lib)
     * @throws ReflectionComponentException
     * @throws \BadMethodCallException
     */
    public function __construct($arg1, $arg2 = null) {
        if (is_a($arg1, 'Cx\Core\Core\Model\Entity\SystemComponent')) {
            $this->componentName = $arg1->getName();
            $this->componentType = $arg1->getType();
            return;
        }
        $arg1Parts = explode('.', $arg1);
		if (file_exists($arg1) && end($arg1Parts) == 'zip') {
            // clean up tmp dir
            \Cx\Lib\FileSystem\FileSystem::delete_folder(ASCMS_TEMP_PATH . '/appcache', true);
        
            // Uncompress package using PCLZip
            $file = new \PclZip($arg1);
            $list = $file->extract(PCLZIP_OPT_PATH, ASCMS_TEMP_PATH . '/appcache');
            
            // Check for meta.yml, if none: throw Exception
            if (!file_exists(ASCMS_TEMP_PATH . '/appcache/meta.yml')) {
                throw new ReflectionComponentException('This ain\'t no package file: "' . $arg1 . '"');
            }
            
            // Read meta info
            $metaTypes = array('core'=>'core', 'core_module'=>'system', 'module'=>'application', 'lib'=>'other');
            $yaml = new \Symfony\Component\Yaml\Yaml();
            $content = file_get_contents(ASCMS_TEMP_PATH . '/appcache/meta.yml');
            $meta = $yaml->load($content);
            $type = array_search($meta['DlcInfo']['type'], $metaTypes);
            if (!$type) {
                $type = 'lib';
            }
            
            // initialize ReflectionComponent
            $this->packageFile = $arg1;
            $this->componentName = $meta['DlcInfo']['name'];
            $this->componentType = $type;
            return;
        } else if (is_string($arg1) && $arg2 && in_array($arg2, self::$componentTypes)) {
            $this->componentName = $arg1;
            $this->componentType = $arg2;
            return;
        }
        throw new \BadMethodCallException('Pass a component or zip package filename or specify a component name and type');
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
     * @param boolean $forceCustomized (optional) If true, the directory in customizing folder is returned, default false
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
     * Installs this component from a zip file (if available)
     * @todo DB stuff (structure and data)
     * @todo check dependency versions
     */
    public function install() {
        // Check (not already installed (different version), all dependencies installed)
        if (!$this->packageFile) {
            throw new SystemComponentException('Package file not available');
        }
        if (!file_exists(ASCMS_TEMP_PATH . '/appcache/meta.yml')) {
            throw new ReflectionComponentException('Invalid package file');
        }
        if ($this->exists()) {
            throw new SystemComponentException('Component is already installed');
        }
        
        // Read meta file
        $yaml = new \Symfony\Component\Yaml\Yaml();
        $content = file_get_contents(ASCMS_TEMP_PATH . '/appcache/meta.yml');
        $meta = $yaml->load($content);
        
        // Check dependencies
        foreach ($meta['DlcInfo']['dependencies'] as $dependencyInfo) {
            $dependency = new static($dependencyInfo['name'], $dependencyInfo['type']);
            if (!$dependency->exists()) {
                throw new SystemComponentException('Dependency "' . $dependency->getName() . '" not met');
            }
        }
        
        // Copy ZIP contents
        $filesystem = new \Cx\Lib\FileSystem\FileSystem();
        $filesystem->copyDir(
            ASCMS_TEMP_PATH . '/appcache',
            ASCMS_TEMP_WEB_PATH . '/appcache',
            'files',
            ASCMS_DOCUMENT_ROOT,
            ASCMS_PATH_OFFSET,
            '',
            true
        );        
        
        // Activate (if type is system or application)
        if ($this->componentType != 'core' && $this->componentType != 'core_module' && $this->componentType != 'module') {
            return;
        }
        
        // Copy ZIP contents (also copy meta.yml into component folder if type is system or application)
        try {
            $objFile = new \Cx\Lib\FileSystem\File(ASCMS_TEMP_PATH . '/appcache/meta.yml');
            $objFile->copy($this->getDirectory(false) . '/meta.yml');
        } catch (\Cx\Lib\FileSystem\FileSystemException $e) {
            \DBG::msg($e->getMessage());
        }
        
        // init DB structure from doctrine yaml files
        // load DB data from /data yaml files
        
        // Activate this component
        $this->activate();
    }
    
    /**
     * Create zip install package for this component
     * @param string $path Path to store zip file at
     */
    public function pack($path) {
        // Create temp working folder
        // Copy contents to folder
        // Create data files
        // Create meta.yml
        // Compress
    }
    
    /**
     * Creates this component using a skeleton
     */
    public function create() {
        if ($this->exists()) {
            return;
        }
        
        // copy skeleton component
        \Cx\Lib\FileSystem\FileSystem::copy_folder(ASCMS_CORE_PATH.'/Core/Data/Skeleton', $this->getDirectory(false));
        
        $this->fixNamespaces('Cx\Modules\Skeleton', $this->getDirectory());
        $this->fixLanguagePlaceholders('MODULE_SKELETON', $this->getDirectory());
        
        // activate component
        $this->activate();
    }
    
    /**
     * Removes this component
     * 
     * This might not work perfectly for legacy components, since there could
     * be files outside the component's directory!
     * Be sure there is no other component relying on this one!
     */
    public function remove() {
        // remove from db
        $this->removeFromDb();
        
        // if there are no files, quit
        if (!$this->exists()) {
            return;
        }
        
        // remove from fs
        \Cx\Lib\FileSystem\FileSystem::delete_folder($this->getDirectory(), true);
    }
    
    /**
     * List dependencies from this component to other parts of the system
     * @todo List files for matches
     * @todo Make this work for legacy components too
     * @return array Returns an array like array({dependency}=>{number_of_times_used})
     */
    public function getDependencies() {
        if ($this->isLegacy()) {
            return array('unknown');
        }
        $dependencies = array();
        
        $directoryIterator = new \RecursiveDirectoryIterator($this->getDirectory());
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $files = new \RegexIterator($iterator, '/^.+\.php$/i', \RegexIterator::GET_MATCH);
        
        // recursive foreach .php file
        $componentNs = SystemComponent::getBaseNamespaceForType($this->componentType) . '\\' . $this->componentName;
        $matches = array();
        foreach($files as $file) {
            $file = current($file);
            // search for namespaces other than Component's
            
            $objFile = new \Cx\Lib\FileSystem\File($file);
            $content = $objFile->getData();
            
            preg_match_all('/(?:[A-Za-z_]+)?\\\\[A-Za-z_\\\\]+/', $content, $matches);
            foreach ($matches[0] as $match) {
                if (substr($match, 0, 1) != '\\') {
                    $match = '\\' . $match;
                }
                $matchBaseNs = substr($match, 0, strlen('\\' . $componentNs));
                if ($matchBaseNs != '\\' . $componentNs && strlen($match) > 2) {
                    if (preg_match('/\\\\r\\\\n/', $match)) {
                        continue;
                    }
                    if (preg_match('/\\\\(?:Doctrine|Gedmo)/', $match)) {
                        $match = '\\Doctrine\\...';
                    }
                    $dependencies[] = preg_replace('/\\\\\\\\/', '\\', $match);
                }
            }
        }
        
        $dependencies = array_count_values($dependencies);
        arsort($dependencies);
        return $dependencies;
    }
    
    /**
     * This adds all necessary DB entries in order to activate this component (if they do not exist)
     * @todo Backend navigation entry (from meta.yml)
     * @todo Pages (from meta.yml)
     */
    public function activate() {
        if (!$this->exists()) {
            throw new \Cx\Core\Core\Controller\ComponentException('No such component: "' . $this->componentName . '" of type "' . $this->componentType . '"');
        }
        
        $cx = \Env::get('cx');
        $em = $cx->getDb()->getEntityManager();
        
        // component
        if (!$this->isLegacy()) {
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
        $distributor = 'Comvation AG';
        $workbenchComponent = new self('Workbench', 'core_module');
        if ($workbenchComponent->exists()) {
            $workbench = new \Cx\Core_Modules\Workbench\Controller\Workbench();
            $distributor = $workbench->getConfigEntry('distributor');
        }
        $query = '
            SELECT
                `id`
            FROM
                `' . DBPREFIX . 'modules`
            WHERE
                `name` = \'' . $this->componentName . '\'
        ';
        $result = $cx->getDb()->getAdoDb()->query($query);
        if (!$result->EOF) {
            $id = $result->fields['id'];
            $query = '
                UPDATE
                    `' . DBPREFIX . 'modules`
                SET
                    `status` = \'y\',
                    `is_required` = ' . ((int) ($this->componentType == 'core')) . ',
                    `is_core` = ' . ((int) ($this->componentType == 'core' || $this->componentType == 'core_module')) . ',
                    `is_active` = 1
                WHERE
                    `id` = ' . $id . '
            ';
        } else {
            $query = '
                SELECT
                    `id`
                FROM
                    `' . DBPREFIX . 'modules`
                WHERE
                    `id` >= 900
                ORDER BY
                    `id` DESC
                LIMIT 1
            ';
            $id = 900;
            $result = $cx->getDb()->getAdoDb()->query($query);
            if (!$result->EOF) {
                $id = $result->fields['id'] + 1;
            }
            $query = '
                INSERT INTO
                    `' . DBPREFIX . 'modules`
                    (
                        `id`,
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
                        ' . $id . ',
                        \'' . $this->componentName . '\',
                        \'' . $distributor . '\',
                        \'TXT_' . strtoupper($this->componentType) . '_' . strtoupper($this->componentName) . '_DESCRIPTION\',
                        \'y\',
                        ' . ((int) ($this->componentType == 'core')) . ',
                        ' . ((int) ($this->componentType == 'core' || $this->componentType == 'core_module')) . ',
                        1
                    )
            ';
        }
        $cx->getDb()->getAdoDb()->query($query);
        
        // backend_areas
        $query = '
            SELECT
                `area_id`
            FROM
                `'.DBPREFIX.'backend_areas`
            WHERE
                `uri` LIKE \'%cmd=' . contrexx_raw2db($this->componentName) . '&%\' OR
                `uri` LIKE \'%cmd=' . contrexx_raw2db($this->componentName) . '\'
        ';
        $result = $cx->getDb()->getAdoDb()->query($query);
        if (!$result->EOF) {
            $query = '
                UPDATE
                    `'.DBPREFIX.'backend_areas`
                SET
                    `module_id` = ' . $id . '
                WHERE
                    `area_id` = ' . $result->fields['area_id'] . '
            ';
        } else {
            $parent = 0;
            if ($this->componentType == 'module') {
                $parent = 2;
            }
            $order_id = 0;
            $query = '
                SELECT
                    `order_id`
                FROM
                    `'.DBPREFIX.'backend_areas`
                WHERE
                    `parent_area_id` = ' . $parent . '
                ORDER BY
                    `order_id` DESC
                LIMIT 1
            ';
            $result = $cx->getDb()->getAdoDb()->query($query);
            if (!$result->EOF) {
                $order_id = $result->fields['order_id'] + 1;
            }
            $access_id = 900;
            $query = '
                SELECT
                    `access_id`
                FROM
                    `'.DBPREFIX.'backend_areas`
                WHERE
                    `access_id` >= 900
                ORDER BY
                    `access_id` DESC
                LIMIT 1
            ';
            $result = $cx->getDb()->getAdoDb()->query($query);
            if (!$result->EOF) {
                $access_id = $result->fields['access_id'] + 1;
            }
            $query = '
                INSERT INTO
                    `'.DBPREFIX.'backend_areas`
                    (
                        `parent_area_id`,
                        `type`,
                        `scope`,
                        `area_name`,
                        `is_active`,
                        `uri`,
                        `target`,
                        `module_id`,
                        `order_id`,
                        `access_id`
                    )
                VALUES
                    (
                        ' . $parent . ',
                        \'navigation\',
                        \'backend\',
                        \'TXT_' . strtoupper($this->componentType) . '_' . strtoupper($this->componentName) . '\',
                        ' . ((int) ($parent == 2)) . ',
                        \'index.php?cmd=' . $this->componentName . '\',
                        \'_self\',
                        ' . $id . ',
                        ' . $order_id . ',
                        ' . $access_id . '
                    )
            ';
        }
        $cx->getDb()->getAdoDb()->query($query);
        
        // pages (if necessary) from repo (if has existing entry/ies) or empty one
        if ($this->componentType != 'module') {
            // only modules need a frontend page to be active
            return;
        }
        
        // we will not use modulemanager here in order to be able to replace
        // modulemanager by this in a later release
        
        // does the module repository have something for us?
        if (!$this->loadPagesFromModuleRepository($id)) {
        
            $nodeRepo = $em->getRepository('\Cx\Core\ContentManager\Model\Entity\Node');
            $pageRepo = $em->getRepository('\Cx\Core\ContentManager\Model\Entity\Page');
        
            // if not: create an empty page
            $parcat = $nodeRepo->getRoot();
            $newnode = new \Cx\Core\ContentManager\Model\Entity\Node();
            $newnode->setParent($parcat); // replace root node by parent!
            $em->persist($newnode);
            $em->flush();
            $nodeRepo->moveDown($newnode, true); // move to the end of this level
            foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
                if ($lang['is_default'] === 'true' || $lang['fallback'] == null) {
                    $type = \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION;
                } else {
                    $type = \Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK;
                }
                $page = $pageRepo->createPage(
                    $newnode,
                    $lang['id'],
                    $this->componentName,
                    $type,
                    $this->componentName,
                    '',
                    false,
                    ''
                );
                $em->persist($page);
            }
            $em->flush();
        }
    }

    /**
     * Loads pages from module repository
     * @param int $moduleId the module id
     * @return boolean True on success, false if no pages found in repo
     */
    protected function loadPagesFromModuleRepository($moduleId) {
        $cx = \Env::get('cx');
        $em = $cx->getDb()->getEntityManager();
        
        $id = $moduleId;
        
        $nodeRepo = $em->getRepository('\Cx\Core\ContentManager\Model\Entity\Node');
        $pageRepo = $em->getRepository('\Cx\Core\ContentManager\Model\Entity\Page');

        $module_name = $this->componentName;
            
        // get content from repo
        $query = '
            SELECT
                `id`,
                `moduleid`,
                `content`,
                `title`,
                `cmd`,
                `expertmode`,
                `parid`,
                `displaystatus`,
                `username`,
                `displayorder`
            FROM
                `'.DBPREFIX.'module_repository`
            WHERE
                `moduleid` = ' . $id . '
            ORDER BY
                `parid` ASC
        ';
        $objResult = $cx->getDb()->getAdoDb()->query($query);
        if ($objResult->EOF) {
            // no pages
            return false;
        }

        $paridarray = array();
        while (!$objResult->EOF) {
            // define parent node
            $root = false;
            if (isset($paridarray[$objResult->fields['parid']])) {
                $parcat = $paridarray[$objResult->fields['parid']];
            } else {
                $root = true;
                $parcat = $nodeRepo->getRoot();
            }

            // create node
            $newnode = new \Cx\Core\ContentManager\Model\Entity\Node();
            $newnode->setParent($parcat); // replace root node by parent!
            $em->persist($newnode);
            $em->flush();
            $nodeRepo->moveDown($newnode, true); // move to the end of this level
            $paridarray[$objResult->fields['id']] = $newnode;

            // add content to default lang
            // add content to all langs without fallback
            // link content to all langs with fallback
            foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
                if ($lang['is_default'] === 'true' || $lang['fallback'] == null) {
                    $page = $pageRepo->createPage(
                        $newnode,
                        $lang['id'],
                        $objResult->fields['title'],
                        \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION,
                        $module_name,
                        $objResult->fields['cmd'],
                        !$root && $objResult->fields['displaystatus'],
                        $objResult->fields['content']
                    );
                } else {
                    $page = $pageRepo->createPage(
                        $newnode,
                        $lang['id'],
                        $objResult->fields['title'],
                        \Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK,
                        $module_name,
                        $objResult->fields['cmd'],
                        !$root && $objResult->fields['displaystatus'],
                        ''
                    );
                }
                $em->persist($page);
            }
            $em->flush();
            $objResult->MoveNext();
        }

        return true;
    }
    
    /**
     * This deactivates the component (does not remove any DB entries, except for pages)
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
            'module' => $this->componentName,
        ));
        foreach ($pages as $page) {
            $em->remove($page);
        }
        $em->flush();
    }
    
    /**
     * This completely removes this component from DB
     * @todo Test removing components tables (including doctrine schema)
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
        $adoDb->execute($query);
        
        // module tables (LIKE DBPREFIX . strtolower($moduleName)%)
        $query = '
            SHOW TABLES
            LIKE
                \'' . DBPREFIX . 'module_' . strtolower($this->componentName) . '%\'
        ';
        $result = $adoDb->execute($query);
        while (!$result->EOF) {
            $query = '
                DROP TABLE
                    `' . current($result->fields) . '`
            ';
            $adoDb->execute($query);
        }
                
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
     * @param string $oldBaseNs Base namespace of old component
     * @param string $baseDir Directory in which the recursive replace should be done
     * @return bool
     * @todo Test references update in DB
     */
    public function fixNamespaces($oldBaseNs, $baseDir) {
        // calculate new proper base NS
        $baseNs = SystemComponent::getBaseNamespaceForType($this->componentType) . '\\' . $this->componentName;
        //$baseDir = $this->getDirectory();
        
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
            $regexDoubleBackslash = '/' . preg_quote(str_replace('\\', '\\\\', $oldNs) . '\\', '/') . '/';
            
            $content = preg_replace(
                $regexDoubleBackslash,
                preg_quote(str_replace('\\', '\\\\', $ns)) . '\\\\',
                $content
            );
            
            $content = preg_replace(
                '/' . preg_quote($oldNs . '\\', '/') . '/',
                $ns . '\\',
                $content
            );
            $objFile->write($content);
        }
        
        // fix namespaces in DB
        // at the moment, only log_entry stores namespaces so we can simply:
        $em = \Env::get('cx')->getDb()->getEntityManager();
        $query = $em->createQuery('SELECT FROM Cx\Core\ContentManager\Model\Entity\LogEntry l WHERE l.object_class LIKE \'' . $ns . '%\'')->useResultCache(true);
        foreach ($query->getResult() as $log) {
            $object_class = $log->getObjectClass();
            $object_class = preg_replace(
                '/' . preg_quote($oldNs . '\\', '/') . '/',
                $ns . '\\',
                $object_class
            );
            $log->setObjectClass($object_class);
            $em->persist($log);
        }
        $em->flush();
        
        return true;
    }
    
    /**
     * Fix the language variables of all files of this component
     * @param string $oldBaseIndex Base language var index of old component
     * @param string $baseDir Directory in which the recursive replace should be done
     */
    public function fixLanguagePlaceholders($oldBaseIndex, $baseDir) {
        $baseIndex = strtoupper($this->componentType . '_' . $this->componentName);
        
        $directoryIterator = new \RecursiveDirectoryIterator($baseDir);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);
        $files = new \RegexIterator($iterator, '/^.+\.(php|html|js)$/i', \RegexIterator::GET_MATCH);
        
        // recursive foreach .php, .html and .js file
        foreach($files as $file) {
            // prepare data
            $file = current($file);
            $bi = $baseIndex;
            $oldBi = $oldBaseIndex;
            
            
            // file_get_contents()
            $objFile = new \Cx\Lib\FileSystem\File($file);
            $content = $objFile->getData();
            
            $content = preg_replace(
                '/' . $oldBi . '/',
                preg_quote($bi),
                $content
            );
            echo 'Replace ' . $oldBi . ' by ' . $bi . ' in ' . $file . "\n";
            
            $objFile->write($content);
        }
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
     * @todo Test copy of pages
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
                $node = $page->getNode()->copy();
                $em->persist($node);
                $node->getPage()->setModule($newName);
                $em->persist($node->getPage());
            } else {
                $page->setModule($newName);
                $em->persist($page);
            }
        }
        $em->flush();
        
        // remove old component from db (component, modules, backend_areas)
        if (!$copy) {
            $this->removeFromDb();
        } else {
            // copy db tables and refactor
        }
        
        // copy/move in filesystem (name, type and customizing)
        $newLocation = $newComponent->getDirectory(false, $customized);
        $this->internalFsRelocate($newLocation, $copy);
        
        // fix namespaces
        $baseDir = ASCMS_DOCUMENT_ROOT;
        if ($copy) {
            $baseDir = $newComponent->getDirectory();
        }
        $newComponent->fixNamespaces(SystemComponent::getBaseNamespaceForType($this->componentType) . '\\' . $this->componentName, $baseDir);
        $newComponent->fixLanguagePlaceholders(strtoupper($this->componentType . '_' . $this->componentName), $baseDir);
        
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
