<?php

/**
 * Class MediaDirEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Modules\MediaDir\Model\Event;

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

/**
 * Class MediaDirEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */
class MediaDirEventListener extends DefaultEventListener
{


    public function mediasourceLoad(
        MediaBrowserConfiguration $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaDir');
        $mediaType = new MediaType();
        $mediaType->setName('mediadir');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_MEDIADIR']);
        $mediaType->setDirectory(
            array(
                $this->cx->getWebsiteImagesMediaDirPath(),
                $this->cx->getWebsiteImagesMediaDirWebPath(),
            )
        );
        $mediaType->setAccessIds(array(153));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }


}