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

class NodeTest extends DoctrineTestCase
{
    public function testPagesByLang() {
        $root = new \Cx\Core\ContentManager\Model\Entity\Node();
        $node = new \Cx\Core\ContentManager\Model\Entity\Node();

        $node->setParent($root);

        $p1 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p2 = new \Cx\Core\ContentManager\Model\Entity\Page();

        $p1->setNode($node);
        $p2->setNode($node);

        $p1->setLang(1);
        $p1->setTitle('testpage');

        $p2->setLang(2);
        $p2->setTitle('testpage2');

        self::$em->persist($root);
        self::$em->persist($node);
        self::$em->persist($p1);
        self::$em->persist($p2);

        self::$em->flush();

        $id = $p1->getId();

        self::$em->clear();

        $r = self::$em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');
        $p = $r->find($id);

        $pages = $p->getNode()->getPagesByLang();
        $this->assertArrayHasKey(2, $pages);
        $this->assertArrayHasKey(1, $pages);

        $this->assertEquals('testpage', $pages[1]->getTitle());
        $this->assertEquals('testpage2', $pages[2]->getTitle());
    }
}
