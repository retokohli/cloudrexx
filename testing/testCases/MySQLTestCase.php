<?php
require_once('ContrexxTestCase.php');

class MySQLTestCase extends ContrexxTestCase {
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