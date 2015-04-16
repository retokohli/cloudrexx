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

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core\Model\Model\Entity\MediaType;

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
     * @param MediaBrowserConfiguration $mediaBrowserConfiguration
     */
    public function mediasourceLoad(MediaBrowserConfiguration $mediaBrowserConfiguration)
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
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}