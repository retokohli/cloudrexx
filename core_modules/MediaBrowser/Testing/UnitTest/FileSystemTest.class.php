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
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  coremodule_mediabrowser
 */
class FileSystemTest extends \Cx\Core\Test\Model\Entity\ContrexxTestCase
{

    public function testPathchecker()
    {
        $this->assertTrue(FileSystem::isVirtualPath('files/Movies'));
        $this->assertTrue(!FileSystem::isVirtualPath('/var/www/contrexx/images/content/Movies'));
    }

    public function testSubdirectoryCheck()
    {
        $this->assertTrue(FileSystem::isSubdirectory('/home/robin/Web/trunk/subfolder/images', 'files/'));
        $this->assertTrue(FileSystem::isSubdirectory('/home/robin/Web/trunk/subfolder/media', 'media2/'));
        $this->assertFalse(FileSystem::isSubdirectory('/home/robin/Web/trunk/subfolder/media', 'files/'));
        $this->assertFalse(FileSystem::isSubdirectory('/home/robin/Web/trunk/subfolder/images', 'media5/'));
    }


}