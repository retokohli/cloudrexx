<?php
/**
 * Command to access behat command line tools
 * @author Michael Ritter <michael.ritter@comvation.com>
 */

namespace Cx\Core_Modules\Workbench\Model\Entity;

/**
 * Command to access behat command line tools
 * @author Michael Ritter <michael.ritter@comvation.com>
 */
class TestCommand extends Command {
    
    /**
     * Command name
     * @var string
     */
    protected $name = 'test';
    
    /**
     * Command description
     * @var string
     */
    protected $description = 'Wrapper for behat command line tools';
    
    /**
     * Command synopsis
     * @var string
     */
    protected $synopsis = 'workbench(.bat) test ([{component_name}|{component_type}]) ({some_crazy_arguments_for_phpunit})';
    
    /**
     * Command help text
     * @var string
     */
    protected $help = 'To be defined';
    
    /**
     * Array of testing folders
     */
    protected $testingFolders = array();

    /**
     * Execute this command
     * @param array $arguments Array of commandline arguments
     */
    public function execute(array $arguments) {
        /*
         * When creating a new component
         *  - cd to component's testing folder
         *  - behat --init
         *  - sample feature file (behat story-syntax)
         *  - behat --snippets
         * 
         * To execute tests
         *  - cd to component's testing folder
         *  - behat
         * 
         * Create test code
         *  - behat --snippets
         */
        global $argv;
        
        $systemConfig      = \Env::get('config');
        $useCustomizing    = isset($systemConfig['useCustomizings']) && $systemConfig['useCustomizings'] == 'on';
        
        $arrComponentTypes = array('core', 'core_module', 'module');                
        
        // check for the component type
        if (isset($arguments[2]) && in_array($arguments[2], $arrComponentTypes)) {
            if (isset($arguments[3])) {
                $componentFolder        = $this->getModuleFolder($arguments[3], $arguments[2], $useCustomizing);
                if (!$this->addTestingFolderToArray($arguments[3], $componentFolder)) {
                    $this->interface->show(ASCMS_TESTING_FOLDER . " not exists in the component ". $arguments[3] .'!');
                    return;
                }
            } else {
                $this->getTestingFoldersByType($arguments[2], $useCustomizing);
            }
        } elseif (!empty ($arguments[2])) {
            // check whether it a valid component
            $componentName = $arguments[2];
            
            foreach ($arrComponentTypes as $cType) {
                $componentFolder        = $this->getModuleFolder($componentName, $cType, $useCustomizing);
                if ($this->addTestingFolderToArray($componentName, $componentFolder)) {
                    break;
                }                                
            }
        }
        
        // get all testing folder when component type or name not specificed
        if (empty($this->testingFolders)) {
            foreach ($arrComponentTypes as $cType) {
                $this->getTestingFoldersByType($cType, $useCustomizing);
            }
        }
                
        $phpUnitTestPath = ASCMS_LIBRARY_PATH.'/PHPUnit';
        if(!file_exists($phpUnitTestPath)) {
            $this->interface->show("PhpUnit is not found in ". $phpUnitTestPath);
            return;
        }

        if (empty($this->testingFolders)) {
            $this->interface->show("Test cases not found!.\nPlease make sure the test cases are palced inside ([{component_name}|{component_type}])/Testing folder.");
            return;
        }

        // Needs to change the dir because files might be loaded by its relative path inside PHPUnit
        chdir($phpUnitTestPath);
        require_once $phpUnitTestPath.'/PHP/CodeCoverage/Filter.php';
        \PHP_CodeCoverage_Filter::getInstance()->addFileToBlacklist(__FILE__, 'PHPUNIT');

        if (extension_loaded('xdebug')) {
            xdebug_disable();
        }

        if (strpos('@php_bin@', '@php_bin') === 0) {            
            set_include_path($phpUnitTestPath . PATH_SEPARATOR . get_include_path());
        }

        require_once $phpUnitTestPath.'/PHPUnit/Autoload.php';

        define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
                    
        $command = new \PHPUnit_TextUI_Command;
        foreach ($this->testingFolders as $testingFolder) {
            $_SERVER['argv'] = $argv = array(
                $phpUnitTestPath,
                '--testdox',
                $testingFolder,
            );
            $_SERVER['argc'] = count($argv);

            $command->run($_SERVER['argv'], false);
        }
        
        $this->interface->show('Done');
    }
    
    /**
     * Return the testing folders by component type
     * 
     * @param  string $componentType Component type
     * 
     * @return array Testing folders by given component type
     */
    private function getTestingFoldersByType($componentType, $useCustomizing) {
        
        $cx = \Env::get('cx');
        $em = $cx->getDb()->getEntityManager();        
        
        $systemComponentRepo = $em->getRepository('Cx\\Core\\Core\\Model\\Entity\\SystemComponent');
        $systemComponents = $systemComponentRepo->findBy(array('type'=>$componentType));
        
        if (!empty($systemComponents)) {
            foreach ($systemComponents as $systemComponent) {
                $this->addTestingFolderToArray($systemComponent->getName(), $systemComponent->getDirectory($useCustomizing));
            }
        }
        
        // load the old legacy components. assume core_module, module can only possible
        if (in_array($componentType, array('core_module', 'module'))) {
            static $objModuleChecker = NULL;

            if (!isset($objModuleChecker)) {
                $objModuleChecker = \Cx\Core\ModuleChecker::getInstance(\Env::get('em'), \Env::get('db'), \Env::get('ClassLoader'));
            }
            
            $arrModules = array();
            switch ($componentType) {
                case 'core_module':
                    $arrModules = $objModuleChecker->getCoreModules();
                    break;
                case 'module':
                    $arrModules = $objModuleChecker->getModules();
                    break;
                default:
                    break;
            }
            
            foreach ($arrModules as $component) {
                if (!array_key_exists($component, $this->testingFolders)) {
                    $componentFolder = $this->getModuleFolder($component, $componentType, $useCustomizing);
                    $this->addTestingFolderToArray($component, $componentFolder);
                }
            }
        }
    }
    
    /**
     * Returns module folder name
     * 
     * @param string  $componentName     Component Name
     * @param string  $componentType     Component Type
     * @param boolean $allowCustomizing  Check for the customizing folder
     * 
     * @return string module folder name
     */
    private function getModuleFolder($componentName, $componentType, $allowCustomizing = true)
    {
        $basepath      = ASCMS_DOCUMENT_ROOT . \Cx\Core\Core\Model\Entity\SystemComponent::getPathForType($componentType);
        $componentPath = $basepath . '/' . $componentName;
        
        if (!$allowCustomizing) {
            return $componentPath;
        }
        
        return \Env::get('cx')->getClassLoader()->getFilePath($componentPath);
    }
    
    /**
     * Added module testing folder to a array
     * 
     * @param string $componentName Component name
     * @param string $componentFolder Module Fodler path
     *      
     * @return boolean true if added successfully otherwise false
     */
    private function addTestingFolderToArray($componentName, $componentFolder)
    {
        $componentTestingFolder = $componentFolder . ASCMS_TESTING_FOLDER;
        
        if (!empty($componentFolder) && self::hasTestingFiles($componentTestingFolder) && file_exists($componentFolder) && file_exists($componentTestingFolder)) {
            $this->testingFolders[$componentName] = $componentTestingFolder;
            return true;
        }
        
        return false;        
    }
    
    /**
     * Return true if the folder has php unit test cases false otherwise
     * 
     * @param string $foldername absolute path of the folder to check the testing files
     * 
     * @return boolean Return true if the folder has php unit test cases false otherwise
     */
    private static function hasTestingFiles($foldername)
    {
        if (glob($foldername.'/*Test.php')) {
            // phpunit test cases should end with Test.php
            return true;
        }
        
        foreach (glob($foldername.'/*', GLOB_ONLYDIR) as $folder) {
            return self::hasTestingFiles($folder);
        }
        
        return false;
    }
}
