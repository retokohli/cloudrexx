<?php

/**
 * Class DownloadsEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Modules\Downloads\Model\Event;

use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;

/**
 * Class DownloadsEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */
class DownloadsEventListener extends DefaultEventListener
{

    public function mediasourceLoad(
        MediaSourceManager $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        $mediaType = new MediaSource();
        $mediaType->setName('downloads');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_DOWNLOADS']);
        $mediaType->setDirectory(
            array(
                $this->cx->getWebsiteImagesDownloadsPath(),
                $this->cx->getWebsiteImagesDownloadsWebPath(),
            )
        );
        $mediaType->setAccessIds(array(141));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }


}