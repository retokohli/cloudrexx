<?php

/**
 * Wrapper class for PHPUnit_TextUI_TestRunner
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     3.2
 * @package     contrexx
 * @subpackage  core
 */

namespace Cx\Core\Model\Controller;
        
/**
 * Wrapper class for PHPUnit_TextUI_TestRunner
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @version     $Id:    Exp $
 * @package     contrexx
 * @subpackage  core 
 */
class PHPUnitTextUITestRunner extends \PHPUnit_TextUI_TestRunner {
    
    /**
     * Set the loader to be used.
     * 
     * @param \PHPUnit_Runner_TestSuiteLoader $loader
     */
    public function setLoader(\PHPUnit_Runner_TestSuiteLoader $loader = NULL)
    {
        $this->loader = $loader;
    }
    
    /**
     * Reset the printer value from the class
     */
    public function resetPrinter()
    {
        $this->printer = NULL;
    }
}