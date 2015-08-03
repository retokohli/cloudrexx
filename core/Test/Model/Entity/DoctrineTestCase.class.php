<?php

/**
 * DoctrineTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

/**
 * DoctrineTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */
abstract class DoctrineTestCase extends ContrexxTestCase {
    protected static $em;

    public static function setUpBeforeClass() {        
        self::$em = \Env::em();
    }

    public function setUp() {
        self::$em->getConnection()->beginTransaction();
    }

    public function tearDown() {
        self::$em->getConnection()->rollback();        
    }
}