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
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;

/**
 * Class ContactEventListener
 *
 * @copyright   CONTREXX CMS - Comvation AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_contact
 * @version     1.0.0
 */
class ContactEventListener extends DefaultEventListener  {

    /**
     * @param MediaSourceManager $mediaBrowserConfiguration
     */
    public function mediasourceLoad(MediaSourceManager $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('Contact');
        $mediaType = new MediaSource('attach',$_ARRAYLANG['TXT_CONTACT_UPLOADS'],array(
            $this->cx->getWebsiteImagesAttachPath(),
            $this->cx->getWebsiteImagesAttachWebPath(),
        ));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}