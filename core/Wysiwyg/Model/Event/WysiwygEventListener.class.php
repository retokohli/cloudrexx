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
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

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
     * @param MediaBrowserConfiguration $mediaBrowserConfiguration
     */
    public function mediasourceLoad(MediaBrowserConfiguration $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaType();
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