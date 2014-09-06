<?php

/**
 * ContrexxTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

/**
 * ContrexxTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */
class ContrexxTestCase extends \PHPUnit_Framework_TestCase {
    public function __construct() {
        //this is a workaround for the error
        //"PDOException: You cannot serialize or unserialize PDO instances"
        //which appears since changing the charset to utf8 on doctrine config.
        parent::__construct();
        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;
    }
}