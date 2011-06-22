<?php
class DoctrineTestCase extends PHPUnit_Framework_TestCase {
    protected static $em;
    protected static $conn;

    public static function setUpBeforeClass() {
        include_once('../../config/doctrine.php');
        self::$em = Env::em();
        self::$conn=self::$em->getConnection();
    }

    public function setUp() {
        self::$conn->beginTransaction();
    }

    public function tearDown() {
        self::$conn->rollback();
    }
}