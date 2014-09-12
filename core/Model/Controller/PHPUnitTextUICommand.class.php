<?php

/**
 * Wrapper class for PHPUnit_TextUI_Command
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.2
 * @package     contrexx
 * @subpackage  core
 */

namespace Cx\Core\Model\Controller;
        
/**
 * Wrapper class for PHPUnit_TextUI_Command
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     $Id:    Exp $
 * @package     contrexx
 * @subpackage  core 
 */
class PHPUnitTextUICommand extends \PHPUnit_TextUI_Command {
    
    /**
     * Contains test runner object
     * 
     * @var object
     */
    private $runner;
    
    /**
     * @var array
     */
    protected static $defaultArguments = array(
      'listGroups'              => FALSE,
      'loader'                  => NULL,
      'syntaxCheck'             => FALSE,
      'useDefaultConfiguration' => TRUE
    );

    public function __construct() {
        $this->runner = new PHPUnitTextUITestRunner();
    }

    /**
     * {@inheritdoc}
     */
    public function run(array $argv, $exit = TRUE)
    {
        // Customizing
        // Reset the arguments to the default arguments
        $this->arguments = self::$defaultArguments;
        
        $this->handleArguments($argv);
                
        $this->runner->setLoader($this->arguments['loader']);
        
        // Customizing
        // Print the version string        
        $this->runner->printVersionString();
        // Reset the Printer object.This will be again loaded from self::arguments otherwise it will hold the previous results.
        $this->runner->resetPrinter();

        if (is_object($this->arguments['test']) &&
            $this->arguments['test'] instanceof \PHPUnit_Framework_Test) {
            $suite = $this->arguments['test'];
        } else {
            $suite = $this->runner->getTest(
              $this->arguments['test'],
              $this->arguments['testFile'],
              $this->arguments['syntaxCheck']
            );
        }
        
        $testCollector = new \PHPUnit_Runner_IncludePathTestCollector(
            array($this->arguments['test']),
            array('Test.class.php')
        );
        $suite->addTestFiles($testCollector->collectTests());
        
        if (count($suite) == 0) {
            $skeleton = new \PHPUnit_Util_Skeleton_Test(
              $suite->getName(),
              $this->arguments['testFile']
            );

            $result = $skeleton->generate(TRUE);

            if (!$result['incomplete']) {
                eval(str_replace(array('<?php', '?>'), '', $result['code']));
                $suite = new \PHPUnit_Framework_TestSuite(
                  $this->arguments['test'] . 'Test'
                );
            }
        }

        if ($this->arguments['listGroups']) {            

            print "Available test group(s):\n";

            $groups = $suite->getGroups();
            sort($groups);

            foreach ($groups as $group) {
                print " - $group\n";
            }

            exit(\PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
        }
        
        unset($this->arguments['test']);
        unset($this->arguments['testFile']);

        try {
            $result = $this->runner->doRun($suite, $this->arguments);
        }
        
        catch (\PHPUnit_Framework_Exception $e) {
            print $e->getMessage() . "\n";
        }

        if ($exit) {
            if (isset($result) && $result->wasSuccessful()) {
                exit(\PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
            }

            else if (!isset($result) || $result->errorCount() > 0) {
                exit(\PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
            }

            else {
                exit(\PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
            }
        }
    }
}