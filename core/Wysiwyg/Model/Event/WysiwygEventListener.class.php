<?php

/**
 * Class WysiwygEventListener
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_wysiwyg
 * @version     1.0.0
 */

namespace Cx\Core\Wysiwyg\Model\Event;

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;

/**
 * Class WysiwygEventListener
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  core_wysiwyg
 * @version     1.0.0
 */
class WysiwygEventListener extends DefaultEventListener {

    /**
     * @param MediaSourceManager $mediaBrowserConfiguration
     */
    public function mediasourceLoad(MediaSourceManager $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaSource();
        $mediaType->setName('wysiwyg');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_WYSIWYG']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesPath() . '/Wysiwyg',
            $this->cx->getWebsiteImagesWebPath() . '/Wysiwyg',
        ));
        $mediaType->setAccessIds(array(16));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}