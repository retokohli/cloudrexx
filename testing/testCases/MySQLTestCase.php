<?php
require_once('ContrexxTestCase.php');

class MySQLTestCase extends ContrexxTestCase {
    protected static $database;

    public static function setUpBeforeClass() {
        $db = new \Cx\Core\Model\Db();
        self::$database = $db->getAdoDb();
    }

    public function setUp() {
        self::$database->BeginTrans();
    }

    public function tearDown() {
        self::$database->RollbackTrans();
    }
}