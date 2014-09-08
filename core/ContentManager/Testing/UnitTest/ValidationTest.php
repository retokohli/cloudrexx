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
        $p = new \Cx\Core\ContentManager\Model\Entity\Page();
        $n = new \Cx\Core\ContentManager\Model\Entity\Node();

        $p->setLang(1);
        $p->setTitle('testpage');
        $p->setNode($n);

        //set disallowed module name
        $p->setModule('1|@f2');

        self::$em->persist($n);
        self::$em->persist($p);

        //should raise exception
        self::$em->flush();
    }
}