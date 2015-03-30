<?php

/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\Contact\Model\Event;

use Cx\Core\Core\Controller\Cx;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

class ContactEventListener implements \Cx\Core\Event\Model\Entity\EventListener  {

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