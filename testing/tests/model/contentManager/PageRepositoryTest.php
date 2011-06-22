<?php
use Doctrine\Common\Util\Debug as DoctrineDebug;

include_once('../testCases/DoctrineTestCase.php');

class PageRepositoryTest extends DoctrineTestCase
{

    public function testTree() {
        $repo = self::$em->getRepository('Cx\Model\ContentManager\Page');
        
        $n1 = new \Cx\Model\ContentManager\Node();
        $n2 = new \Cx\Model\ContentManager\Node();
        $n3 = new \Cx\Model\ContentManager\Node();
        $n4 = new \Cx\Model\ContentManager\Node();
        $n5 = new \Cx\Model\ContentManager\Node();

        $n2->setParent($n1);
        $n3->setParent($n2);
        $n4->setParent($n1);
        $n5->setParent($n2);

        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($n3);
        self::$em->persist($n4);
        self::$em->persist($n5);

        self::$em->flush();

        $childs = $repo->getTreeByLanguage();

        $nRepo = self::$em->getRepository('Cx\Model\ContentManager\Node');
        $nRepo->moveDown($childs[1],1);
        DoctrineDebug::dump($childs[0]->getChildren());

        return true;
    }
}