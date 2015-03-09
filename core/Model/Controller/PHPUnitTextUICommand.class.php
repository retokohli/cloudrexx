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
    public function run(array $argv, $exit = true)
    {
        // Customizing
        // Reset the arguments to the default arguments
        $this->arguments = self::$defaultArguments;

        $this->handleArguments($argv);

        $this->runner->setLoader($this->arguments['loader']);
        
        // Reset the Printer object.This will be again loaded from self::arguments otherwise it will hold the previous results.
        $this->runner->resetPrinter();

        if (is_object($this->arguments['test']) &&
            $this->arguments['test'] instanceof \PHPUnit_Framework_Test) {
            $suite = $this->arguments['test'];
        } else {
            $suite = $this->runner->getTest(
                $this->arguments['test'],
                $this->arguments['testFile'],
                $this->arguments['testSuffixes']
            );
        }

        $testFiles = self::collectTests($this->arguments['test']);
        asort($testFiles);
        
        $suite->addTestFiles($testFiles);
        
        if ($this->arguments['listGroups']) {
            $this->printVersionString();

            print "Available test group(s):\n";

            $groups = $suite->getGroups();
            sort($groups);

            foreach ($groups as $group) {
                print " - $group\n";
            }

            if ($exit) {
                exit(\PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
            } else {
                return \PHPUnit_TextUI_TestRunner::SUCCESS_EXIT;
            }
        }

        unset($this->arguments['test']);
        unset($this->arguments['testFile']);

        try {
            $result = $this->runner->doRun($suite, $this->arguments);
        } catch (\PHPUnit_Framework_Exception $e) {
            print $e->getMessage() . "\n";
        }

        $ret = \PHPUnit_TextUI_TestRunner::FAILURE_EXIT;

        if (isset($result) && $result->wasSuccessful()) {
            $ret = \PHPUnit_TextUI_TestRunner::SUCCESS_EXIT;
        } elseif (!isset($result) || $result->errorCount() > 0) {
            $ret = \PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT;
        }

        if ($exit) {
            exit($ret);
        } else {
            return $ret;
        }
    }
    
    private static function collectTests($foldername)
    {
        $result = array();
        
        foreach (glob($foldername.'/*Test.class.php') as $file) {
            $result[] = $file;
        }
        
        foreach (glob($foldername.'/*Test.php') as $file) {
            $result[] = $file;
        }
        
        foreach (glob($foldername.'/*', GLOB_ONLYDIR) as $folder) {
            $result = array_merge($result, self::collectTests($folder));
        }
        
        return $result;
    }

}