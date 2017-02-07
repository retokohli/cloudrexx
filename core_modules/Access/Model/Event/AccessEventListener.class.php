<?php

/**
 * Cloudrexx
 *
 * @link      http://www.cloudrexx.com
 * @copyright Cloudrexx AG 2007-2015
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
 * "Cloudrexx" is a registered trademark of Cloudrexx AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Class AccessEventListener
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_access
 * @version     1.0.0
 */

namespace Cx\Core_Modules\Access\Model\Event;

use Cx\Core\Event\Model\Entity\DefaultEventListener;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;

/**
 * Class AccessEventListener
 *
 * @copyright   CLOUDREXX CMS - Cloudrexx AG Thun
 * @author      Robin Glauser <robin.glauser@comvation.com>
 * @package     cloudrexx
 * @subpackage  coremodule_access
 * @version     1.0.0
 */
class AccessEventListener extends DefaultEventListener
{

    /**
     * @param MediaSourceManager $mediaBrowserConfiguration
     */
    public function mediasourceLoad(
        MediaSourceManager $mediaBrowserConfiguration
    ) {
        global $_ARRAYLANG;
        $mediaType = new MediaSource(
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

    /**
     * Access event listener for clearing esi cache
     *
     * @param array $eventArgs
     *
     * return null
     */
    public function clearEsiCache($eventArgs)
    {
        if (empty($eventArgs) || $eventArgs != 'Access') {
            return;
        }

        $em = $this->cx->getDb()->getEntityManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('p')
            ->from('Cx\Core\ContentManager\Model\Entity\Page', 'p')
            ->where($qb->expr()->in(
                'p.type',
                array(
                    \Cx\Core\ContentManager\Model\Entity\Page::TYPE_CONTENT,
                    \Cx\Core\ContentManager\Model\Entity\Page::TYPE_APPLICATION
                )
            ));
        $availablePages  = $qb->getQuery()->getResult();
        $themeRepo       = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $availableThemes = $themeRepo->findAll();

        if (!$availablePages || !$availableThemes) {
            return;
        }

        foreach ($availablePages as $page) {
            foreach ($availableThemes as $theme) {
                $this->clearCache($page->getId(), $theme->getId());
            }
        }
    }

    /**
     * Clear ESI/SSI cache
     *
     * @param integer $page  ID of the page
     * @param integer $theme ID of the theme
     *
     * @return null
     */
    public function clearCache($page, $theme)
    {
        if (empty($page) || empty($theme)) {
            return;
        }

        $cache = $this->cx->getComponent('Cache');
        $availableBlocks = array(
            'access_currently_online_member_list',
            'access_last_active_member_list',
            'access_latest_registered_member_list',
            'access_birthday_member_list'
        );
        foreach ($availableBlocks as $block) {
            $cache->clearSsiCachePage(
                'Access',
                'getWidget',
                array(
                    'name'            => $block,
                    'targetComponent' => 'View',
                    'targetEntity'    => 'Theme',
                    'targetId'        => $theme,
                )
            );
            $cache->clearSsiCachePage(
                'Access',
                'getWidget',
                array(
                    'name'            => $block,
                    'targetComponent' => 'ContentManager',
                    'targetEntity'    => 'Page',
                    'targetId'        => $page,
                )
            );
        }
    }
}
