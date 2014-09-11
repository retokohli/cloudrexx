<?php



namespace Cx\Core_Modules\MediaBrowser\Testing;

use Cx\Core_Modules\MediaBrowser\Model\FileSystem;

class FileSystemTest extends \PHPUnit_Framework_TestCase
{

    public function testPathchecker()
    {
        $this->assertTrue(FileSystem::isVirtualPath('files/Movies'));
    }
}