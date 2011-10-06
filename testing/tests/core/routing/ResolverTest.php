<?php
include_once ASCMS_CORE_PATH.'/routing/URL.class.php';
include_once ASCMS_CORE_PATH.'/routing/Resolver.class.php';
include_once('../testCases/DoctrineTestCase.php');

use Cx\Core\Routing\Resolver as Resolver;
use Cx\Core\Routing\URL as URL;

class ResolverTest extends DoctrineTestCase
{
    protected function insertFixtures() {
        $repo = self::$em->getRepository('Cx\Model\ContentManager\Page');

        $root = new \Cx\Model\ContentManager\Node();
        
        $n1 = new \Cx\Model\ContentManager\Node();
        $n2 = new \Cx\Model\ContentManager\Node();
        $n3 = new \Cx\Model\ContentManager\Node();
        $n4 = new \Cx\Model\ContentManager\Node(); //redirection

        $n1->setParent($root);
        $n2->setParent($n1);
        $n3->setParent($n2);
        $n4->setParent($root);

        $p1 = new \Cx\Model\ContentManager\Page();     
        $p1->setLang(1);
        $p1->setTitle('testpage1');
        $p1->setNode($n1);
        $p1->setUsername('user');

        $p4 = new \Cx\Model\ContentManager\Page();     
        $p4->setLang(1);
        $p4->setTitle('testpage1_child');
        $p4->setNode($n2);
        $p4->setUsername('user');

        $p5 = new \Cx\Model\ContentManager\Page();     
        $p5->setLang(1);
        $p5->setTitle('subtreeTest_target');
        $p5->setNode($n3);
        $p5->setUsername('user');

        self::$em->persist($root);
        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($n3);
        self::$em->persist($n4);
        
        self::$em->persist($p1);
        self::$em->persist($p4);
        self::$em->persist($p5);

        self::$em->flush();

        $p2 = new \Cx\Model\ContentManager\Page();     
        $p2->setLang(1);
        $p2->setTitle('redirection');
        $p2->setNode($n4);
        $p2->setTarget($n2->getId().'|?foo=test');
        $p2->setUsername('user');

        self::$em->persist($p2);

        self::$em->flush();

        //make sure we re-fetch a correct state
        self::$em->clear();
    }

    public function testTargetPathAndParams() {
        $this->insertFixtures();

        $lang = 1;

        $url = new URL('http://example.com/testpage1/testpage1_child/?foo=test');
        $resolver = new Resolver($url, $lang, self::$em);

        $this->assertEquals('testpage1/testpage1_child/', $url->getTargetPath());
        $this->assertEquals('?foo=test', $url->getParams());

        $this->assertEquals(true, $url->isRouted());
    }

    public function testFoundPage() {
        $this->insertFixtures();

        $lang = 1;

        $url = new URL('http://example.com/testpage1/testpage1_child/?foo=test');
        $resolver = new Resolver($url, $lang, self::$em);

        $page = $resolver->getPage();
        $this->assertEquals('testpage1_child', $page->getTitle());
    }

    /**
     * @expectedException Cx\Core\Routing\ResolverException
     */
    public function testInexistantPage() {
        $this->insertFixtures();

        $lang = 1;

        $url = new URL('http://example.com/inexistantPage/?foo=test');
        $resolver = new Resolver($url, $lang, self::$em);

        $page = $resolver->getPage();
    }

    public function testRedirection() {
        $this->insertFixtures();

        $lang = 1;

        $url = new URL('http://example.com/redirection/');
        $resolver = new Resolver($url, $lang, self::$em);

        $page = $resolver->getPage();
        $this->assertEquals('testpage1_child', $page->getTitle());
    }
}