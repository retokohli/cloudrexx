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


use Cx\Core\Core\Controller\Cx;
use Cx\Core\Event\Model\Entity\EventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

class WysiwygEventListener implements EventListener {

    /**
     * @var Cx
     */
    protected $cx;

    /**
     * @param Cx $cx
     */
    function __construct(Cx $cx)
    {
        $this->cx = $cx;
    }

    /**
     * @param       $eventName
     * @param array $eventArgs
     */
    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }

    /**
     * @param MediaBrowserConfiguration $mediaBrowserConfiguration
     */
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
        $mediaType->setAccessIds(array(16));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}