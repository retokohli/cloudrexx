<?php
include_once('../testCases/DoctrineTestCase.php');
include_once(ASCMS_CORE_PATH.'/LinkGenerator.class.php');

class LinkGeneratorTest extends DoctrineTestCase {
    protected $nodeId;

    protected function insertFixtures() {
        $root = new \Cx\Model\ContentManager\Node();
        $node = new \Cx\Model\ContentManager\Node();
        $node->setParent($root);

        $p = new \Cx\Model\ContentManager\Page();
        $p2 = new \Cx\Model\ContentManager\Page();

        $p->setLang(1);
        $p->setTitle('testpage');
        $p->setNode($node);
        $p->setUsername('user');

        $p2->setLang(2);
        $p2->setTitle('testpage2');
        $p2->setNode($node);
        $p2->setUsername('user');

        //shouldn't raise a ValidationException
        self::$em->persist($root);
        self::$em->persist($node);
        self::$em->persist($p);
        self::$em->persist($p2);
        self::$em->flush();

        $this->nodeId = $node->getId();
    }
    
    public function testScanning() {
        $this->insertFixtures();

        $testContent = 'asdf{NODE_'.$this->nodeId.'_1}';
        $testContent .= ' asdf{NODE_'.$this->nodeId.'_2}';

        $lg = new LinkGenerator('example.com/offset/');
        $lg->scan($testContent);

        $ph = $lg->getPlaceholders();

        $this->assertEquals(2, count($ph));
        $this->assertArrayHasKey('NODE_'.$this->nodeId.'_1', $ph);
        $this->assertArrayHasKey('NODE_'.$this->nodeId.'_2', $ph);

        $this->assertEquals($this->nodeId, $ph['NODE_'.$this->nodeId.'_1']['nodeid']);
        $this->assertEquals(1, $ph['NODE_'.$this->nodeId.'_1']['lang']);

        $this->continueWithFetching($lg);
    }

    public function continueWithFetching($lg) {
        $lg->fetch(self::$em);

        $ph = $lg->getPlaceholders();
        
        $this->assertEquals('example.com/offset/testpage', $ph['NODE_'.$this->nodeId.'_1']);
    }
}
