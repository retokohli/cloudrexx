<?php

/**
 * Contrexx
 *
 * @link      http://www.contrexx.com
 * @copyright Comvation AG 2007-2014
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

require_once('ContrexxTestCase.php');

class DoctrineTestCase extends ContrexxTestCase {
    protected static $em;

    public static function setUpBeforeClass() {
        /*        include_once('../../config/configuration.php');
        include_once('../../core/API.php');
        include_once('../../config/doctrine.php');*/
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