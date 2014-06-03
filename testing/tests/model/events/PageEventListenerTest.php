<?php
include_once(ASCMS_TEST_PATH.'/testCases/DoctrineTestCase.php');

class PageEventListenerTest extends DoctrineTestCase
{
    public function testUniqueSlugGeneration() {
        $root = new \Cx\Core\ContentManager\Model\Entity\Node();

        $n1 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n1->setParent($root);
        $n2 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n2->setParent($root);
        $n3 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n3->setParent($root);

        $p1 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p1->setLang(1);
        $p1->setTitle('testpage');
        $p1->setNode($n1);

        //provocate a slug conflict
        $p2 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p2->setLang(1);
        $p2->setTitle('testpage');
        $p2->setNode($n2);

        //different language, shouldn't conflict
        $p3 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p3->setLang(2);
        $p3->setTitle('testpage');
        $p3->setNode($n1);

        //provocate another slug conflict
        $p4 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p4->setLang(1);
        $p4->setTitle('testpage');
        $p4->setNode($n3);

        self::$em->persist($root);
        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($n3);
        self::$em->persist($p1);
        self::$em->persist($p2);
        self::$em->persist($p3);
        self::$em->persist($p4);
        self::$em->flush();

        //see whether the listener changed the slug as we expect him to do.
        $this->assertEquals('testpage', $p1->getSlug());
        $this->assertEquals('testpage-1', $p2->getSlug());
        $this->assertEquals('testpage-2', $p4->getSlug());
        //check whether slug uniqueness was checked only language-wide
        $this->assertEquals('testpage', $p3->getSlug());
    }

    public function testUniqueSlugGenerationWithPersistedNodes() {
        $root = new \Cx\Core\ContentManager\Model\Entity\Node();

        $n1 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n1->setParent($root);
        $n2 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n2->setParent($root);

        $p1 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p1->setLang(1);
        $p1->setTitle('testpage');
        $p1->setNode($n1);

        self::$em->persist($root);
        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($p1);
        self::$em->flush();

        $id = $n2->getId();
       
        self::$em->clear();

        $node = self::$em->find('Cx\Core\ContentManager\Model\Entity\Node', $id);

        //provocate a slug conflict
        $p2 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p2->setLang(1);
        $p2->setTitle('testpage');
        $p2->setNode($node);

        //different language, shouldn't conflict
        $p3 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p3->setLang(2);
        $p3->setTitle('testpage');
        $p3->setNode($node);

        $newNode = new \Cx\Core\ContentManager\Model\Entity\Node();
        $newNode->setParent($node->getParent());
        //mixing in a conflict inside the new persists
        $p4 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p4->setLang(1);
        $p4->setTitle('testpage');
        $p4->setNode($newNode);

        self::$em->persist($p2);
        self::$em->persist($p3);
        self::$em->persist($newNode);
        self::$em->persist($p4);
        self::$em->flush();

        $this->assertEquals('testpage', $p1->getSlug());
        $this->assertEquals('testpage-1', $p2->getSlug());
        $this->assertEquals('testpage', $p3->getSlug());
        $this->assertEquals('testpage-2', $p4->getSlug());
    }

    public function testSlugReleasing() {
        $root = new \Cx\Core\ContentManager\Model\Entity\Node();

        $n1 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n1->setParent($root);
        $n2 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n2->setParent($root);

        $p1 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p1->setLang(1);
        $p1->setTitle('testpage');
        $p1->setNode($n1);

        self::$em->persist($root);
        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($p1);
        self::$em->flush();

        $idp1 = $p1->getId();
        $idn2 = $n2->getId();
       
        self::$em->clear();

        $this->assertEquals('testpage', $p1->getSlug());

        $p1 = self::$em->find('Cx\Core\ContentManager\Model\Entity\Page', $idp1);
        $n2 = self::$em->find('Cx\Core\ContentManager\Model\Entity\Node', $idn2);

        //shouldn't provocate a slug conflict, since we delete the other page below
        $p2 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p2->setLang(1);
        $p2->setTitle('testpage');
        $p2->setNode($n2);

        self::$em->remove($p1);
        self::$em->persist($p2);
        self::$em->flush();

        $this->assertEquals('testpage', $p2->getSlug());
   }
}