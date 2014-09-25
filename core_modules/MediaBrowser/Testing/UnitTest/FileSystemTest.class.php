<?php

/**
 * FileSystemTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */

namespace Cx\Core_Modules\MediaBrowser\Testing\UnitTest;
use Cx\Core_Modules\MediaBrowser\Model\FileSystem;

/**
 * FileSystemTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Comvation Development Team <info@comvation.com>
 * @author      SS4U <ss4u.comvation@gmail.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class FileSystemTest extends \Cx\Core\Test\Model\Entity\DoctrineTestCase
{

    public function testPathchecker()
    {
        $this->assertTrue(FileSystem::isVirtualPath('files/Movies'));
    }
}