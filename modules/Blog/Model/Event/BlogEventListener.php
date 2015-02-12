<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Modules\Blog\Model\Event;


use Cx\Core\Core\Controller\Cx;
use Cx\Core\Event\Model\Entity\EventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core_Modules\MediaBrowser\Model\MediaType;

class BlogEventListener implements EventListener {

    /**
     * @var Cx
     */
    protected $cx;

    function __construct(Cx $cx)
    {
        $this->cx = $cx;
    }


    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }

    public function LoadMediaTypes(MediaBrowserConfiguration $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaType();
        $mediaType->setName('blog');
        $mediaType->setHumanName($_ARRAYLANG['TXT_BLOG_MODULE']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesBlogPath(),
            $this->cx->getWebsiteImagesBlogWebPath(),
        ));
        $mediaType->getAccessIds(array(119));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}