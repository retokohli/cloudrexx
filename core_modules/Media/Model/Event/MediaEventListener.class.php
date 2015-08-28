<?php

/**
 * Class MediaEventListener
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_media
 * @version     1.0.0
 */

namespace Cx\Core_Modules\Media\Model\Event;

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;

/**
 * Class MediaEventListener
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_media
 * @version     1.0.0
 */
class MediaEventListener extends DefaultEventListener
{

    /**
     * @param MediaSourceManager $mediaBrowserConfiguration
     */
    public function mediasourceLoad(
        MediaSourceManager $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('Media');
        for ($i = 1; $i < 5; $i++) {
            $mediaType = new MediaSource('media' . $i,$_ARRAYLANG['TXT_MEDIA_ARCHIVE'] . ' ' . $i, array(
                call_user_func(
                    array($this->cx, 'getWebsiteMediaarchive' . $i . 'Path')
                ),
                call_user_func(
                    array(
                        $this->cx, 'getWebsiteMediaarchive' . $i . 'WebPath'
                    )
                ),
            ),array(7, 39, 38));

            $mediaBrowserConfiguration->addMediaType($mediaType);
        }
    }
}