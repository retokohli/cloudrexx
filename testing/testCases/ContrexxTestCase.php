<?php
/**
 * Base class for all contrexx tests.
 */
class ContrexxTestCase extends PHPUnit_Framework_TestCase {
    public function __construct() {
        //this is a workaround for the error
        //"PDOException: You cannot serialize or unserialize PDO instances"
        //which appears since changing the charset to utf8 on doctrine config.
        parent::__construct();
        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;
    }
}