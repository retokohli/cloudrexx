<?php
/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */


/**
 * @copyright   Comvation AG
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\Media\Model\Event;

use Cx\Core\Core\Controller\Cx;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core_Modules\MediaBrowser\Model\MediaType;

class MediaEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

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
        \Env::get('init')->loadLanguageData('Media');
        for ($i = 1; $i < 5; $i++){
            $mediaType = new MediaType();
            $mediaType->setName('media'.$i);
            $mediaType->setHumanName($_ARRAYLANG['TXT_MEDIA_ARCHIVE'].' '.$i);

            $mediaType->setDirectory(array(
                call_user_func(array($this->cx,'getWebsiteMediaarchive'.$i.'Path')),
                call_user_func(array($this->cx,'getWebsiteMediaarchive'.$i.'WebPath')),
            ));
            $mediaType->getAccessIds(array(7,39,38));
            $mediaBrowserConfiguration->addMediaType($mediaType);
        }
    }
}