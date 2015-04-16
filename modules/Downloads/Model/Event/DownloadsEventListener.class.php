<?php

/**
 * Class DownloadsEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Modules\Downloads\Model\Event;

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

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
        MediaBrowserConfiguration $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        $mediaType = new MediaType();
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