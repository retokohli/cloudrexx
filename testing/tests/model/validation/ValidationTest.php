<?php
include_once('../testCases/DoctrineTestCase.php');

class ValidationTest extends DoctrineTestCase
{
    /**
     * @expectedException \Cx\Model\Base\ValidationException
     */
    public function testValidationException() {
        $p = new \Cx\Model\ContentManager\Page();
        $n = new \Cx\Model\ContentManager\Node();

        $p->setLang(1);
        $p->setTitle('testpage');
        $p->setNode($n);
        $p->setUser(1); //bogus user

        //set disallowed module name
        $p->setModule('1|@f2');

        self::$em->persist($n);
        self::$em->persist($p);

        //should raise exception
        self::$em->flush();
    }
}