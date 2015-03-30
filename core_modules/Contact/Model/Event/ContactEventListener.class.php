<?php

/**
 * Class ContactEventListener
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_contact
 * @version     1.0.0
 */

namespace Cx\Core_Modules\Contact\Model\Event;

use Cx\Core\Core\Controller\Cx;
use Cx\Core\Event\Model\Entity\EventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

class ContactEventListener implements EventListener  {

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
    public function onEvent($eventName, array $eventArgs)
    {
        $this->$eventName(current($eventArgs));
    }

    /**
     * @param MediaBrowserConfiguration $mediaBrowserConfiguration
     */
    public function LoadMediaTypes(MediaBrowserConfiguration $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('Contact');
        $mediaType = new MediaType();
        $mediaType->setName('attach');
        $mediaType->setHumanName($_ARRAYLANG['TXT_CONTACT_UPLOADS']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesAttachPath(),
                $this->cx->getWebsiteImagesAttachWebPath(),
        ));
        $mediaType->getAccessIds(array());
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}