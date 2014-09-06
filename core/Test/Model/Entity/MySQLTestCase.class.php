<?php

/**
 * MySQLTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

/**
 * MySQLTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */
class MySQLTestCase extends ContrexxTestCase {
    protected static $database;

    public static function setUpBeforeClass() {
        global $_DBCONFIG;

        // Set database connection details
        $objDb = new \Cx\Core\Model\Model\Entity\Db();
        $objDb->setHost($_DBCONFIG['host']);
        $objDb->setName($_DBCONFIG['database']);
        $objDb->setTablePrefix($_DBCONFIG['tablePrefix']);
        $objDb->setDbType($_DBCONFIG['dbType']);
        $objDb->setCharset($_DBCONFIG['charset']);
        $objDb->setCollation($_DBCONFIG['collation']);
        $objDb->setTimezone($_DBCONFIG['timezone']);

        // Set database user details
        $objDbUser = new \Cx\Core\Model\Model\Entity\DbUser();
        $objDbUser->setName($_DBCONFIG['user']);
        $objDbUser->setPassword($_DBCONFIG['password']);

        // Initialize database connection
        $db = new \Cx\Core\Model\Db($objDb, $objDbUser);
        self::$database = $db->getAdoDb();
    }

    public function setUp() {
        self::$database->BeginTrans();
    }

    public function tearDown() {
        self::$database->RollbackTrans();
    }
}
