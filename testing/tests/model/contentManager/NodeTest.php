<?php
include_once('../testCases/DoctrineTestCase.php');

class NodeTest extends DoctrineTestCase
{
    public function testPagesByLang() {
        $root = new \Cx\Model\ContentManager\Node();
        $node = new \Cx\Model\ContentManager\Node();

        $node->setParent($root);

        $p1 = new \Cx\Model\ContentManager\Page();
        $p2 = new \Cx\Model\ContentManager\Page();

        $p1->setNode($node);
        $p2->setNode($node);

        $p1->setLang(1);
        $p1->setTitle('testpage');
        $p1->setUsername('user');

        $p2->setLang(2);
        $p2->setTitle('testpage2');
        $p2->setUsername('user');

        self::$em->persist($root);
        self::$em->persist($node);
        self::$em->persist($p1);
        self::$em->persist($p2);

        self::$em->flush();

        $id = $node->getId();

        self::$em->clear();

        $r = self::$em->getRepository('Cx\Model\ContentManager\Node');
        $n = $r->find($id);

        $pages = $n->getPagesByLang();
        $this->assertArrayHasKey(2, $pages);
        $this->assertArrayHasKey(1, $pages);

        $this->assertEquals('testpage', $pages[1]->getTitle());
        $this->assertEquals('testpage2', $pages[2]->getTitle());
    }
}
