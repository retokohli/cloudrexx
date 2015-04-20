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

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Test\Model\Entity\ContrexxTestCase;
use Cx\Core_Modules\MediaBrowser\Model\FileSystem;

/**
 * FileSystemTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @version     1.0.0
 */
class FileSystemTest extends ContrexxTestCase
{
    /**
     * Test the pathchecker in the filessystem.
     */
    public function testPathchecker()
    {
        $this->assertTrue(FileSystem::isVirtualPath('files/Movies'));
        $this->assertTrue(!FileSystem::isVirtualPath('/var/www/contrexx/images/content/Movies'));
    }

    /**
     * Test the subdirectory check in the filesystem class
     */
    public function testSubdirectoryCheck()
    {
        $cx = Cx::instanciate();
        $this->assertTrue(FileSystem::isSubdirectory($cx->getWebsitePath().'/images', 'files/'));
        $this->assertFalse(FileSystem::isSubdirectory($cx->getWebsitePath().'/media', 'files/'));
        $this->assertFalse(FileSystem::isSubdirectory($cx->getWebsitePath().'/images', 'media5/'));
    }


}