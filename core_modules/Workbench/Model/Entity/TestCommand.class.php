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
    protected $synopsis = 'workbench(.bat) db ??';
    
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
        chdir(ASCMS_DOCUMENT_ROOT.'/testing/PHPUnit/');
        echo shell_exec('php phpunit.php --bootstrap ../cx_bootstrap.php --testdox ../tests/core/');
        //require_once(ASCMS_DOCUMENT_ROOT.'/testing/PHPUnit/phpunit.php');
    }
}
