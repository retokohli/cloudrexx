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
        
        $suite = new \PHPUnit_Framework_TestSuite();
        
        $testFiles = array();
        foreach ($this->arguments['test'] as $testFodler) {
            $testFiles = array_merge($testFiles, self::collectTests($testFodler));
        }
        
        asort($testFiles);
        $suite->addTestFiles($testFiles);
        
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