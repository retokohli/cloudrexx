<?php
class DoctrineTestCase extends PHPUnit_Framework_TestCase {
    protected static $em;

    public static function setUpBeforeClass() {
        include_once('../../config/doctrine.php');
        self::$em = Env::em();
    }

    public function setUp() {
        self::$em->getConnection()->beginTransaction();
    }

    public function tearDown() {
        self::$em->getConnection()->rollback();
        self::$em->clear();
    }
}