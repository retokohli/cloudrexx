<?php
class MySQLTestCase extends PHPUnit_Framework_TestCase {
    public static function setUpBeforeClass() {
        $errMsg = '';
        self::$database = getDatabaseObject($errMsg);
    }

    public function setUp() {
        self::$database->StartTrans();
    }

    public function tearDown() {
        self::$database->FailTrans();
    }
}