<?php
include_once(ASCMS_TEST_PATH.'/testCases/DoctrineTestCase.php');

class PageTest extends DoctrineTestCase
{
    public function testValidation() {
        $rootNode = new \Cx\Core\ContentManager\Model\Entity\Node();
        $node = new \Cx\Core\ContentManager\Model\Entity\Node();
        $node->setParent($rootNode);
        $p = new \Cx\Core\ContentManager\Model\Entity\Page();

        $p->setLang(1);
        $p->setTitle('testpage');
        $p->setNode($node);
        $p->setCmd('should_be_valid');

        //shouldn't raise a ValidationException
        self::$em->persist($node);
        self::$em->persist($p);
        self::$em->flush();
    }

    public function testLoggable() {
        $root = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n = new \Cx\Core\ContentManager\Model\Entity\Node();

        $n->setParent($root);

        $p = new \Cx\Core\ContentManager\Model\Entity\Page();

        $p->setLang(1);
        $p->setTitle('testpage');
        $p->setNode($n);

        self::$em->persist($root);
        self::$em->persist($n);

        self::$em->persist($p);

        self::$em->flush();

        //now, create a log
        $p->setTitle('testpage_changed');
        self::$em->persist($p);

        self::$em->flush();

        //now, agiiin
        $p->setTitle('testpage_changed_2');
        self::$em->persist($p);

        self::$em->flush();

        $repo = self::$em->getRepository('Cx\Core\ContentManager\Model\Entity\LogEntry'); // we use default log entry class

        $this->assertEquals('testpage_changed_2',$p->getTitle());
        $repo->revert($p,1);
        $this->assertEquals('testpage',$p->getTitle());
        $repo->revert($p,2);
        $this->assertEquals('testpage_changed',$p->getTitle());
        $repo->revert($p,1);
        $this->assertEquals('testpage',$p->getTitle());
    }

    public function testSlugGeneration() {
        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p->setTitle('test');
        $this->assertEquals('test', $p->getSlug());

        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p->setTitle('test with space');
        $this->assertEquals('test-with-space', $p->getSlug());

        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p->setTitle('test ümläut');
        $this->assertEquals('test-mlut', $p->getSlug());

        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p->setTitle('123');
        $this->assertEquals('123', $p->getSlug());
    }

    public function testImplicitExplicitSlugSetting() {
        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p->setTitle('test');
        $this->assertEquals('test', $p->getSlug());

        $p->setTitle('thisshouldntaffecttheslug');
        $this->assertEquals('test', $p->getSlug());

        $p->setSlug('butThisShould');
        $this->assertEquals('butThisShould', $p->getSlug());
    }

    public function testTargetProperties() {
        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p->setTarget('12|querystring');
        $this->assertEquals(true, $p->isTargetInternal());
        $this->assertEquals(12, $p->getTargetNodeId());
        $this->assertEquals(0, $p->getTargetLangId());
        $this->assertEquals('querystring', $p->getTargetQueryString());

        $p->setTarget('12-1|querystring');
        $this->assertEquals(true, $p->isTargetInternal());
        $this->assertEquals(12, $p->getTargetNodeId());
        $this->assertEquals(1, $p->getTargetLangId());
        $this->assertEquals('querystring', $p->getTargetQueryString());

        $p->setTarget('http://www.example.com');
        $this->assertEquals(false, $p->isTargetInternal());
        $this->assertEquals(0, $p->getTargetNodeId());
        $this->assertEquals(0, $p->getTargetLangId());
        $this->assertEquals(null, $p->getTargetQueryString());
    }

    public function testProtectionProperties() {
        $p = new \Cx\Core\ContentManager\Model\Entity\Page();

        //currently untested because set(Front|Backend)Protection() call static Permission-methods.
        
        /*        $this->assertEquals(false, $p->isFrontendProtected());
        $this->assertEquals(false, $p->isBackendProtected());

        $p->setFrontendProtection(true);
        $this->assertEquals(true, $p->isFrontendProtected());
        $this->assertEquals(false, $p->isBackendProtected());

        $p->setFrontendProtection(false);
        $p->setBackendProtection(true);
        $this->assertEquals(false, $p->isFrontendProtected());
        $this->assertEquals(true, $p->isBackendProtected());*/
    }
}