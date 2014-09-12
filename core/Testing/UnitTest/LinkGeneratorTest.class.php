<?php

/**
 * LinkGeneratorTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_linkGenerator
 */

namespace Cx\Core\Testing\UnitTest;

/**
 * LinkGeneratorTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_linkGenerator
 */
class LinkGeneratorTest extends \Cx\Core\Test\Model\Entity\DoctrineTestCase {
    public function testDummyTestToAvoidPHPUnitWarning() {
    }
/*     protected $nodeId; */

/*     protected function insertFixtures() { */
/*         $root = new \Cx\Core\ContentManager\Model\Entity\Node(); */
/*         $node = new \Cx\Core\ContentManager\Model\Entity\Node(); */
/*         $node->setParent($root); */

/*         $p = new \Cx\Core\ContentManager\Model\Entity\Page(); */
/*         $p2 = new \Cx\Core\ContentManager\Model\Entity\Page(); */

/*         $p->setLang(1); */
/*         $p->setTitle('testpage'); */
/*         $p->setNode($node); */
/*         $p->setUsername('user'); */

/*         $p2->setLang(2); */
/*         $p2->setTitle('testpage2'); */
/*         $p2->setNode($node); */
/*         $p2->setUsername('user'); */

/*         //shouldn't raise a ValidationException */
/*         self::$em->persist($root); */
/*         self::$em->persist($node); */
/*         self::$em->persist($p); */
/*         self::$em->persist($p2); */
/*         self::$em->flush(); */

/*         $this->nodeId = $node->getId(); */
/*     } */
    
/*     public function testScanning() { */
/*         $this->insertFixtures(); */

/*         $testContent = 'asdf{NODE_'.$this->nodeId.'_1}'; */
/*         $testContent .= ' asdf{NODE_'.$this->nodeId.'_2}'; */

/*         $lg = new LinkGenerator('example.com/offset/'); */
/*         $lg->scan($testContent); */

/*         $ph = $lg->getPlaceholders(); */

/*         $this->assertEquals(2, count($ph)); */
/*         $this->assertArrayHasKey('NODE_'.$this->nodeId.'_1', $ph); */
/*         $this->assertArrayHasKey('NODE_'.$this->nodeId.'_2', $ph); */

/*         $this->assertEquals($this->nodeId, $ph['NODE_'.$this->nodeId.'_1']['nodeid']); */
/*         $this->assertEquals(1, $ph['NODE_'.$this->nodeId.'_1']['lang']); */

/*         $this->continueWithFetching($lg); */
/*     } */

/*     public function continueWithFetching($lg) { */
/*         $lg->fetch(self::$em); */

/*         $ph = $lg->getPlaceholders(); */
        
/*         $this->assertEquals('example.com/offset/testpage', $ph['NODE_'.$this->nodeId.'_1']); */
/*     } */
}
