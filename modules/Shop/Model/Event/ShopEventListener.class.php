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
 * EventListener for Shop
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_shop
 */

namespace Cx\Modules\Shop\Model\Event;
use Cx\Core\Core\Controller\Cx;
use Cx\Core\MediaSource\Model\Entity\MediaSourceManager;
use Cx\Core\MediaSource\Model\Entity\MediaSource;
use Cx\Core\Event\Model\Entity\DefaultEventListener;

/**
 * Class ShopEventListener
 * EventListener for Shop
 *
 * @copyright   Cloudrexx AG
 * @author      Project Team SS4U <info@cloudrexx.com>
 * @package     cloudrexx
 * @subpackage  module_shop
 */
class ShopEventListener extends DefaultEventListener {

    public function SearchFindContent($search) {
        $term_db = $search->getTerm();

        $flagIsReseller = false;
        $objUser = \FWUser::getFWUserObject()->objUser;

        if ($objUser->login()) {
            $objCustomer = \Cx\Modules\Shop\Controller\Customer::getById($objUser->getId());
            \Cx\Core\Setting\Controller\Setting::init('Shop', 'config');
            if ($objCustomer && $objCustomer->is_reseller()) {
                $flagIsReseller = true;
            }
        }

        $querySelect = $queryCount = $queryOrder = null;
        list($querySelect, $queryCount, $queryTail, $queryOrder) = \Cx\Modules\Shop\Controller\Products::getQueryParts(null, null, null, $term_db, false, false, '', $flagIsReseller);
        $query = $querySelect . $queryTail . $queryOrder;//Search query
        $parseSearchData = function(&$searchData) {
                                $searchData['title']   = $searchData['name'];
                                $searchData['content'] = $searchData['long'] ? $searchData['long'] : $searchData['short'];
                                $searchData['score']   = $searchData['score1'] + $searchData['score2'] + $searchData['score3'];
                            };
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'Shop', 'details', 'productId=', $search->getTerm(), $parseSearchData));
        $search->appendResult($result);
    }

    public function mediasourceLoad(MediaSourceManager $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $mediaType = new MediaSource('shop',$_ARRAYLANG['TXT_FILEBROWSER_SHOP'],array(
            $this->cx->getWebsiteImagesShopPath(),
            $this->cx->getWebsiteImagesShopWebPath(),
        ));
        $mediaType->setAccessIds(array(13));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

    /**
     * Clear all Ssi cache
     *
     * @param array $eventArgs
     *
     * @return null
     */
    public function clearEsiCache($eventArgs)
    {
        if (empty($eventArgs) || $eventArgs != 'Shop') {
            return;
        }
        global $objInit;

        //clear shop navbar cache
        $cache     = $this->cx->getComponent('Cache');
        $themeRepo = new \Cx\Core\View\Model\Repository\ThemeRepository();
        $navFiles  = array('shopnavbar', 'shopnavbar2', 'shopnavbar3');
        foreach ($themeRepo->findAll() as $theme) {
            foreach ($navFiles as $navFile) {
                foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
                    $cache->clearSsiCachePage(
                        'Shop',
                        'getNavbar',
                        array(
                            'langId'   => $lang['id'],
                            'template' => $theme->getId(),
                            'file'     => $navFile . '.html'
                        )
                    );
                }
            }
        }

        //clear products block cache
        $pattern = '/' . \Cx\Modules\Shop\Controller\Shop::block_shop_products .
            '(?:_category_(\d+))?/';
        foreach ($themeRepo->findAll() as $theme) {
            $themesBlock = array();
            $searchTemplateFiles = array_merge(
                array('index.html', 'home.html', 'content.html'),
                $objInit->getCustomContentTemplatesForTheme($theme)
            );
            foreach ($searchTemplateFiles as $tplFile) {
                $match   = null;
                $content = $theme->getContentFromFile($tplFile);
                if (!preg_match($pattern, $content, $match)) {
                    continue;
                }
                $themesBlock[] = array(
                    'file' => $tplFile,
                    'catId' => $match[1],
                    'block' => $match[0]
                );
            }

            if (!$themesBlock) {
                continue;
            }

            foreach ($themesBlock as $params) {
                $params['template'] = $theme->getId();
                foreach (\FWLanguage::getActiveFrontendLanguages() as $lang) {
                    $params['langId'] = $lang['id'];
                    $cache->clearSsiCachePage(
                        'Shop',
                        'parseProductsBlock',
                        $params
                    );
                }
            }
        }
    }
}
