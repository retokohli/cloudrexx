<?php

/**
 * Class AccessEventListener
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_access
 * @version     1.0.0
 */

namespace Cx\Core_Modules\Access\Model\Event;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Event\Model\Entity\EventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

class AccessEventListener implements EventListener
{

    /**
     * @var Cx
     */
    protected $cx;

    /**
     * @param Cx $cx
     */
    function __construct(Cx $cx) {
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
    public function LoadMediaTypes(
        MediaBrowserConfiguration $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        $mediaType = new MediaType(
            'access',
            $_ARRAYLANG['TXT_USER_ADMINISTRATION'],
            array(
                $this->cx->getWebsiteImagesAccessPath(),
                $this->cx->getWebsiteImagesAccessWebPath(),
            ),
            array(18)
        );
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}