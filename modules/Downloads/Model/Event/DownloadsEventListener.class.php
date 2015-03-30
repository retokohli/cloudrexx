<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Modules\Downloads\Model\Event;


use Cx\Core\Core\Controller\Cx;
use Cx\Core\Event\Model\Entity\EventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

class DownloadsEventListener implements EventListener {

    /**
     * @var Cx
     */
    protected $cx;

    function __construct(Cx $cx)
    {
        $this->cx = $cx;
    }

    public function onEvent($eventName, array $eventArgs)
    {
        $this->$eventName(current($eventArgs));
    }

    public function LoadMediaTypes(MediaBrowserConfiguration $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        $mediaType = new MediaType();
        $mediaType->setName('downloads');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_DOWNLOADS']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesDownloadsPath(),
            $this->cx->getWebsiteImagesDownloadsWebPath(),
        ));
        $mediaType->setAccessIds(array(141));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }


}