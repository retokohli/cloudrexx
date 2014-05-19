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
        /*global $argv;

        // php phpunit.php --bootstrap ../cx_bootstrap.php --testdox ../test/core/
        //\DBG::activate(DBG_PHP);
        $argv = array(
            'phpunit.php',
            //'--bootstrap',
            //'../cx_bootstrap.php',
            '--testdox',
            ASCMS_DOCUMENT_ROOT.'/testing/tests/core/',
        );*/
        
        $arrComponentTypes = array('core', 'core_module', 'module');        
        $testingFolders     = array();
        
        // check for the component type
        if (isset($arguments[2]) && in_array($arguments[2], $arrComponentTypes)) {
            $testingFolders = $this->getTestingFoldersByType($arguments[2]);
        } elseif (!empty ($arguments[2])) {
            // check whether it a valid component
            $componentName = $arguments[2];
            $componentType = '';
            
            foreach ($arrComponentTypes as $cType) {
                $componentPath = ASCMS_DOCUMENT_ROOT . "/" . \Cx\Core\Core\Model\Entity\SystemComponent::getPathForType($cType) . '/' . $componentName;
                if (file_exists($componentPath)) {
                    $componentType = $cType;
                    $testingFolder = $componentPath . '/Testing/';
                    if (file_exists($testingFolder)) {
                        $testingFolders[] = $testingFolder;
                    }
                    break;
                }
            }
        }
        
        // get all testing folder when component type or name not specificed
        if (empty($testingFolders)) {
            foreach ($arrComponentTypes as $cType) {
                $testingFolders = array_merge($testingFolders, $this->getTestingFoldersByType($cType));
            }
        }
        
        //chdir(ASCMS_DOCUMENT_ROOT.'/testing/PHPUnit/');
        //echo shell_exec('php phpunit.php --bootstrap ../cx_bootstrap.php --testdox ../tests/core/');
    }
    
    function getTestingFoldersByType($componentType) {
        $testingFolders = array();
        
        $componentPath = ASCMS_DOCUMENT_ROOT . \Cx\Core\Core\Model\Entity\SystemComponent::getPathForType($componentType);
            
        foreach (glob("$componentPath/*", GLOB_BRACE) as $folder) {
            $testingFolder = $folder . '/Testing/';
            if (file_exists($testingFolder)) {                    
                $testingFolders[] = $testingFolder;
            }
        }
        
        return $testingFolders;
    }
}
