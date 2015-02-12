<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core\Wysiwyg\Model\Event;


use Cx\Core\Core\Controller\Cx;
use Cx\Core\Event\Model\Entity\EventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core_Modules\MediaBrowser\Model\MediaType;

class WysiwygEventListener implements EventListener {

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
        $mediaType->setName('wysiwyg');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_WYSIWYG']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesPath() . '/Wysiwyg',
            $this->cx->getWebsiteImagesWebPath() . '/Wysiwyg',
        ));
        $mediaType->getAccessIds(array(16));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}