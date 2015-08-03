<?php

/**
 * ValidationTest
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
 * ValidationTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_contentmanager
 */
class ValidationTest extends \Cx\Core\Test\Model\Entity\DoctrineTestCase
{
    /**
     * @expectedException \Cx\Model\Base\ValidationException
     */
    public function testValidationException() {
        
        $nodeRepo = self::$em->getRepository('Cx\Core\ContentManager\Model\Entity\Node');

        $n = new \Cx\Core\ContentManager\Model\Entity\Node();
        $n->setParent($nodeRepo->getRoot());
        $nodeRepo->getRoot()->addChildren($n);

        self::$em->persist($n);
        self::$em->flush();
        
        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $p->setNode($n);

        $p->setLang(1);
        $p->setTitle('validation testpage');        
        $p->setNodeIdShadowed($n->getId());
        $p->setUseCustomContentForAllChannels('');
        $p->setUseCustomApplicationTemplateForAllChannels('');
        $p->setUseSkinForAllChannels('');
        $p->setType(\Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION);        
        $p->setActive(1);

        //set disallowed module name
        $p->setModule('1|@f2');
        $p->setCmd('');

        self::$em->persist($n);
        self::$em->persist($p);

        //should raise exception
        self::$em->flush();
    }
}