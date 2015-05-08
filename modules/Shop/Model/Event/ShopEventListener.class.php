<?php

/**
 * Class ShopEventListener
 * EventListener for Shop
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
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
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
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
        $mediaType = new MediaSource();
        $mediaType->setName('shop');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_SHOP']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesShopPath(),
            $this->cx->getWebsiteImagesShopWebPath(),
        ));
        $mediaType->setAccessIds(array(13));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}
