<?php

/**
 * NodeTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_contentmanager
 */

namespace Cx\Core\ContentManager\Testing\UnitTest;

/**
 * NodeTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_contentmanager
 */
class NodeTest extends \Cx\Core\Test\Model\Entity\DoctrineTestCase
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
