<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\MediaBrowser\Model\Event;

use Cx\Core\Core\Controller\Cx;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

class MediaBrowserEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

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
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $mediaType = new MediaType();
        $mediaType->setName('files');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_FILES']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesContentPath(),
            $this->cx->getWebsiteImagesContentWebPath(),
        ));
        $mediaType->setPosition(1);
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}