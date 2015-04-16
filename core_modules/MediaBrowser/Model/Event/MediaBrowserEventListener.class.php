<?php

/**
 * Class MediaBrowserEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\MediaBrowser\Model\Event;

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

/**
 * Class MediaBrowserEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */
class MediaBrowserEventListener extends DefaultEventListener
{

    public function mediasourceLoad(
        MediaBrowserConfiguration $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $mediaType = new MediaType();
        $mediaType->setName('files');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_FILES']);
        $mediaType->setDirectory(
            array(
                $this->cx->getWebsiteImagesContentPath(),
                $this->cx->getWebsiteImagesContentWebPath(),
            )
        );
        $mediaType->setPosition(1);
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}