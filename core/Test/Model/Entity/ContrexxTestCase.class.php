<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
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
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * ContrexxTestCase
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

/**
 * ContrexxTestCase
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      CLOUDREXX Development Team <info@cloudrexx.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     cloudrexx
 * @subpackage  core_test
 */
abstract class ContrexxTestCase extends \PHPUnit_Framework_TestCase {
    protected static $cx;

    public function __construct() {
        //this is a workaround for the error
        //"PDOException: You cannot serialize or unserialize PDO instances"
        //which appears since changing the charset to utf8 on doctrine config.
        parent::__construct();

        self::$cx = \Env::get('cx');

        $this->backupGlobals = false;
        $this->backupStaticAttributes = false;
    }
}
