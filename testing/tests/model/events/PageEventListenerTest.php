<?php
include_once('../testCases/DoctrineTestCase.php');

class PageEventListenerTest extends DoctrineTestCase
{
    public function testUniqueSlugGeneration() {
        $root = new \Cx\Model\ContentManager\Node();

        $n1 = new \Cx\Model\ContentManager\Node();
        $n1->setParent($root);
        $n2 = new \Cx\Model\ContentManager\Node();
        $n2->setParent($root);

        $p1 = new \Cx\Model\ContentManager\Page();
        $p1->setLang(1);
        $p1->setTitle('testpage');
        $p1->setNode($n1);
        $p1->setUsername('user');

        //provocate a slug conflict
        $p2 = new \Cx\Model\ContentManager\Page();
        $p2->setLang(1);
        $p2->setTitle('testpage');
        $p2->setNode($n2);
        $p2->setUsername('user');

        //different language, shouldn't conflict
        $p3 = new \Cx\Model\ContentManager\Page();
        $p3->setLang(2);
        $p3->setTitle('testpage');
        $p3->setNode($n1);
        $p3->setUsername('user');

        self::$em->persist($root);
        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($p1);
        self::$em->persist($p2);
        self::$em->persist($p3);
        self::$em->flush();

        //see whether the listener changed the slug as we expect him to do.
        $this->assertEquals('testpage', $p1->getSlug());
        $this->assertEquals('testpage-1', $p2->getSlug());
        //check whether slug uniqueness was checked only language-wide
        $this->assertEquals('testpage', $p3->getSlug());
    }
}