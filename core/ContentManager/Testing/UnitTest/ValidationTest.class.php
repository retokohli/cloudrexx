<?php

/**
 * Contrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Comvation AG 2007-2015
 * @version   Contrexx 4.0
 * 
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

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