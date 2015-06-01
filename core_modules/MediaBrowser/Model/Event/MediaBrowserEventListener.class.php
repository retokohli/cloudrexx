<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\MediaBrowser\Model\Event;

use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;

/**
 * Class MediaBrowserEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 */
class MediaBrowserEventListener extends DefaultEventListener
{
    /**
     * @param MediaBrowserConfiguration $mediaBrowserConfiguration
     */
    public function mediasourceLoad(
        MediaSourceManager $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $mediaType = new MediaSource('files',$_ARRAYLANG['TXT_FILEBROWSER_FILES'],   array(
            $this->cx->getWebsiteImagesContentPath(),
            $this->cx->getWebsiteImagesContentWebPath(),
        ),array(), 1);
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}