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
 * ComponentControllerTest
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      Nick B rönnimann <nick.brönnimann@comvation.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 */

namespace Cx\Core\Wysiwyg\Testing;

/**
 * ComponentControllerTest
 *
 * @copyright   CLOUDREXX CMS - CLOUDREXX AG
 * @author      Cloudrexx Development Team <info@cloudrexx.com>
 * @author      Nick B rönnimann <nick.brönnimann@comvation.com>
 * @version     1.0.0
 * @package     cloudrexx
 * @subpackage  core_wysiwyg
 */
class ComponentControllerTest extends \Cx\Core\Test\Model\Entity\ContrexxTestCase {
    public function testGetToolbar() {
        $fwUser = new \FWUser(true);
        $_POST['USERNAME'] = 'noreply@contrexx.com';
        $_POST['PASSWORD'] = '123456';
        if ($fwUser->checkAuth()) {
            $em = self::$cx->getDb()->getEntityManager();
            $toolbarRepo = $em->getRepository('\\Cx\\Core\\Wysiwyg\\Model\\Entity\\WysiwygToolbar');
            $toolbars = $toolbarRepo->findAll();
            $firstToolbar = $toolbars[0];
            $secondToolbar = $toolbars[1];

            $this->assertEquals('', json_encode(array_diff($firstToolbar, $secondToolbar)));
        }
    }
}
