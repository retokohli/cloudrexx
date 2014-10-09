<?php

/**
 * ResolverTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_resolver
 */

namespace Cx\Core\Routing\Testing\UnitTest;
use Cx\Core\Routing\Resolver as Resolver;
use Cx\Core\Routing\Url as Url;

/**
 * ResolverTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_resolver
 */
class ResolverTest extends \Cx\Core\Test\Model\Entity\DoctrineTestCase
{
    protected $mockFallbackLanguages = array(
        1 => 2,
        2 => 3
    );
    protected function insertFixtures() {        
        $nodeRepo = self::$em->getRepository('Cx\Core\ContentManager\Model\Entity\Node');
        
        $root = $nodeRepo->getRoot();     
        $n1 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n2 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n3 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n4 = new \Cx\Core\ContentManager\Model\Entity\Node(); //redirection
        $n5 = new \Cx\Core\ContentManager\Model\Entity\Node(); //alias

        $n1->setParent($root);
        $n2->setParent($n1);
        $n3->setParent($n2);
        $n4->setParent($root);
        $n5->setParent($root);
        
        $root->addChildren($n1);
        $n1->addChildren($n2);
        $n2->addChildren($n3);
        $root->addChildren($n4);
        $root->addChildren($n4);
        
        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($n3);
        self::$em->persist($n4);
        self::$em->flush();
        
        $p1 = new \Cx\Core\ContentManager\Model\Entity\Page();     
        $p1->setLang(1);
        $p1->setTitle('resolver testpage1');
        $p1->setNode($n1);
        $p1->setNodeIdShadowed($n1->getId());
        $p1->setUseCustomContentForAllChannels('');
        $p1->setUseCustomApplicationTemplateForAllChannels('');
        $p1->setUseSkinForAllChannels('');
        $p1->setCmd('');
        $p1->setActive(1);

        $p4 = new \Cx\Core\ContentManager\Model\Entity\Page();     
        $p4->setLang(1);
        $p4->setTitle('testpage1_child');
        $p4->setNode($n2);
        $p4->setNodeIdShadowed($n2->getId());
        $p4->setUseCustomContentForAllChannels('');
        $p4->setUseCustomApplicationTemplateForAllChannels('');
        $p4->setUseSkinForAllChannels('');
        $p4->setCmd('');
        $p4->setActive(1);
        
        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($p1);
        self::$em->persist($p4);
        self::$em->flush();
        self::$em->refresh($n1);
        self::$em->refresh($n2);

        $p5 = new \Cx\Core\ContentManager\Model\Entity\Page();     
        $p5->setLang(1);
        $p5->setTitle('subtreeTest_target');
        $p5->setNode($n3);
        $p5->setNodeIdShadowed($n3->getId());
        $p5->setUseCustomContentForAllChannels('');
        $p5->setUseCustomApplicationTemplateForAllChannels('');
        $p5->setUseSkinForAllChannels('');
        $p5->setCmd('');
        $p5->setActive(1);
        
        $p6 = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p6->setLang(0);
        $p6->setTitle('testalias');
        $p6->setNode($n5);
        $p6->setType(\Cx\Core\ContentManager\Model\Entity\Page::TYPE_ALIAS);
        $p6->setTarget($p4->getId().'|1');
        $p6->setNodeIdShadowed($n5->getId());
        $p6->setUseCustomContentForAllChannels('');
        $p6->setUseCustomApplicationTemplateForAllChannels('');
        $p6->setUseSkinForAllChannels('');
        $p6->setCmd('');
        $p6->setActive(1);
                
        self::$em->persist($n3);
        self::$em->persist($n5);
        
        self::$em->persist($p5);
        self::$em->persist($p6);
        self::$em->flush();                
        self::$em->refresh($n3);
        self::$em->refresh($n5);

        $p2 = new \Cx\Core\ContentManager\Model\Entity\Page();     
        $p2->setLang(1);
        $p2->setType(\Cx\Core\ContentManager\Model\Entity\Page::TYPE_REDIRECT);
        $p2->setTitle('redirection');
        $p2->setNode($n4);
        $p2->setTarget(\Cx\Core\Routing\NodePlaceholder::fromNode($n2, 1, array('foo' => 'test'))->getPlaceholder());
        $p2->setNodeIdShadowed($n4->getId());
        $p2->setUseCustomContentForAllChannels('');
        $p2->setUseCustomApplicationTemplateForAllChannels('');
        $p2->setUseSkinForAllChannels(''); 
        $p2->setCmd('');
        $p2->setActive(1);

        self::$em->persist($p2);
        self::$em->flush();        
        self::$em->refresh($n4);
        self::$em->refresh($p2);
    }

    public function testTargetPathAndParams() {
        $this->insertFixtures();
        
        return false;
        
        $lang = 1;

        $url = new Url('http://example.com/testpage1/testpage1_child/?foo=test');
        $resolver = new Resolver($url, $lang, self::$em, '', $this->mockFallbackLanguages);
        $resolver->resolve();
        
        $this->assertEquals('testpage1/testpage1_child/', $url->getTargetPath());
        $this->assertEquals('?foo=test', $url->getParams());

        $this->assertEquals(true, $url->isRouted());
    }

    public function testFoundPage() {
        $this->insertFixtures();
        return false;
        $lang = 1;

        $url = new Url('http://example.com/testpage1/testpage1_child/?foo=test');
        $resolver = new Resolver($url, $lang, self::$em, '', $this->mockFallbackLanguages);
        $resolver->resolve();

        $page = $resolver->getPage();
        $this->assertEquals('testpage1_child', $page->getTitle());
    }

    /**
     * @expectedException Cx\Core\Routing\ResolverException
     */
    public function testInexistantPage() {
        $this->insertFixtures();
        return false;
        
        $lang = 1;

        $url = new Url('http://example.com/inexistantPage/?foo=test');
        $resolver = new Resolver($url, $lang, self::$em, '', $this->mockFallbackLanguages);
        $resolver->resolve();

        $page = $resolver->getPage();
    }

    public function testRedirection() {
        $this->insertFixtures();
        return false;
        
        $lang = 1;

        $url = new Url('http://example.com/redirection/');
        $resolver = new Resolver($url, $lang, self::$em, '', $this->mockFallbackLanguages, true);
        $resolver->resolve();

        $page = $resolver->getPage();
        $this->assertEquals('testpage1_child', $page->getTitle());
    }

    protected function getResolvedFallbackPage() {
        return false;
        $repo = self::$em->getRepository('Cx\Core\ContentManager\Model\Entity\Page');

        $root = new \Cx\Core\ContentManager\Model\Entity\Node();
        
        $n1 = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n2 = new \Cx\Core\ContentManager\Model\Entity\Node();

        $n1->setParent($root);

        //test if requesting this page...
        $p2 = new \Cx\Core\ContentManager\Model\Entity\Page();     
        $p2->setLang(1);
        $p2->setTitle('pageThatsFallingBack');
        $p2->setNode($n1);
        $p2->setType(\Cx\Core\ContentManager\Model\Entity\Page::TYPE_FALLBACK);

        //... will yield contents of this page as result.
        $p1 = new \Cx\Core\ContentManager\Model\Entity\Page();     
        $p1->setLang(2);
        $p1->setTitle('pageThatHoldsTheContent');
        $p1->setNode($n1);
        $p1->setType('content');
        $p1->setContent('fallbackContent');

        self::$em->persist($root);
        self::$em->persist($n1);
        self::$em->persist($n2);
        self::$em->persist($p1);
        self::$em->persist($p2);
        self::$em->flush();
        self::$em->clear();

        $url = new Url('http://example.com/pageThatsFallingBack/');
        $resolver = new Resolver($url, 1, self::$em, '', $this->mockFallbackLanguages, true);
        $resolver->resolve();
        $p = $resolver->getPage();

        return $p;
    }

    public function testFallbackRedirection() {
        return false;
        
        $p = $this->getResolvedFallbackPage();

        $this->assertEquals('fallbackContent', $p->getContent());
        $this->assertEquals(true, $p->hasFallbackContent());
    }

    /**
     * @expectedException Cx\Model\Events\PageEventListenerException
     */
    public function testPageListenerForResolvedPages() {
        return false;
        
        $p = $this->getResolvedFallbackPage();

        //try to change something
        $p->setContent('asdf');
        self::$em->persist($p);
        self::$em->flush();
    }
    
    public function testAliasResolving() {
        return false;
        
        $this->insertFixtures();
        
        $url = new Url('http://example.com/testalias');
        $resolver = new Resolver($url, 1, self::$em, '', $this->mockFallbackLanguages, true);
        $resolver->resolveAlias();
        $resolver->resolve();
        $p = $resolver->getPage();
        
        $this->assertEquals(1, $p->getLang());
        $this->assertEquals('testpage1_child', $p->getTitle());
    }
}
