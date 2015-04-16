<?php

/**
 * Class BlogEventListener
 *
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Modules\Blog\Model\Event;

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

/**
 * Class BlogEventListener
 *
 * @copyright   Comvation AG
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */
class BlogEventListener extends DefaultEventListener {

    public function mediasourceLoad(MediaBrowserConfiguration $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaType();
        $mediaType->setName('blog');
        $mediaType->setHumanName($_ARRAYLANG['TXT_BLOG_MODULE']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesBlogPath(),
            $this->cx->getWebsiteImagesBlogWebPath(),
        ));
        $mediaType->setAccessIds(array(119));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}