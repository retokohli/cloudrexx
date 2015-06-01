<?php

/**
 * MediaSourceManagerTest
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_mediasource
 */

namespace Cx\Core_Modules\MediaBrowser\Testing\UnitTest;

use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\Test\Model\Entity\ContrexxTestCase;

/**
 * Class MediaSourceManagerTest
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @version     1.0.0
 * @package     contrexx
 * @subpackage  core_mediasource
 */
class MediaSourceManagerTest extends ContrexxTestCase
{

    public function testMediaSourceManager()
    {
        $testCx = new TestCx();
        $name = "Test";
        $testCx->getEvents()->addMediaSource(new MediaSource($name, "test"));
        $mediaSourceManger = new MediaSourceManager($testCx);
        $mediaTypes = $mediaSourceManger->getMediaTypes();
        $this->assertEquals($name,$mediaTypes['Test']->getName());
        $this->assertTrue(count($mediaSourceManger->getMediaTypes()) == 1);
    }
}