<?php
require_once('ContrexxTestCase.php');

class MySQLTestCase extends ContrexxTestCase {
    protected static $database;

    public static function setUpBeforeClass() {
        $errMsg = '';
        self::$database = getDatabaseObject($errMsg);
    }

    public function setUp() {
        self::$database->BeginTrans();
    }

    public function tearDown() {
        self::$database->RollbackTrans();
    }
}