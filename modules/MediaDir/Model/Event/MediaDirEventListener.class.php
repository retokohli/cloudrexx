<?php

/**
 * Class MediaDirEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Modules\MediaDir\Model\Event;


use Cx\Core\Core\Controller\Cx;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;

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
        MediaSourceManager $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaDir');
        $mediaType = new MediaSource('mediadir',$_ARRAYLANG['TXT_FILEBROWSER_MEDIADIR'], array(
            $this->cx->getWebsiteImagesMediaDirPath(),
            $this->cx->getWebsiteImagesMediaDirWebPath(),
        ),array(153));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }


}