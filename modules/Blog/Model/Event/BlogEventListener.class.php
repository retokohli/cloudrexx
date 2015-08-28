<?php
/**
 * Class BlogEventListener
 *
 * @copyright   Comvation AG
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Modules\Blog\Model\Event;

use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;


/**
 * Class BlogEventListener
 *
 * @copyright   Comvation AG
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */
class BlogEventListener extends DefaultEventListener {

    public function mediasourceLoad(MediaSourceManager $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaSource('blog',$_ARRAYLANG['TXT_BLOG_MODULE'],array(
            $this->cx->getWebsiteImagesBlogPath(),
            $this->cx->getWebsiteImagesBlogWebPath(),
        ),array(119));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}