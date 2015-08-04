<?php

/**
 * Contrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Comvation AG 2007-2015
 * @version   Contrexx 4.0
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Contrexx" is a registered trademark of Comvation AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @copyright   Comvation AG 
 * @author Robin Glauser <robin.glauser@comvation.com>
 * @package     contrexx
 */

namespace Cx\Core_Modules\MediaBrowser\Model\Event;

use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;

/**
 * Class MediaBrowserEventListener
 *
 * @copyright   Comvation AG
 * @author      Robin Glauser <robin.glauser@comvation.com>
 */
class MediaBrowserEventListener extends DefaultEventListener
{
    /**
     * @param MediaBrowserConfiguration $mediaBrowserConfiguration
     */
    public function mediasourceLoad(
        MediaSourceManager $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $mediaType = new MediaSource('files',$_ARRAYLANG['TXT_FILEBROWSER_FILES'],   array(
            $this->cx->getWebsiteImagesContentPath(),
            $this->cx->getWebsiteImagesContentWebPath(),
        ),array(), 1);
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }
}