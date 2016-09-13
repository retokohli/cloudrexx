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

include_once(ASCMS_TEST_PATH.'/testCases/DoctrineTestCase.php');

class ValidationTest extends DoctrineTestCase
{
    /**
     * @expectedException \Cx\Model\Base\ValidationException
     */
    public function testValidationException() {
        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $n = new \Cx\Core\ContentManager\Model\Entity\Node();

        $p->setLang(1);
        $p->setTitle('testpage');
        $p->setNode($n);

        //set disallowed module name
        $p->setModule('1|@f2');

        self::$em->persist($n);
        self::$em->persist($p);

        //should raise exception
        self::$em->flush();
    }
}