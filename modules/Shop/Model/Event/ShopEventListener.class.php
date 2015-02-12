<?php

/**
 * EventListener for Shop
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 */

namespace Cx\Modules\Shop\Model\Event;
use Cx\Core\Core\Controller\Cx;
use Cx\Core_Modules\MediaBrowser\Controller\MediaBrowserConfiguration;
use Cx\Core_Modules\MediaBrowser\Model\MediaType;

/**
 * EventListener for Shop
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 */
class ShopEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    /**
     * @var Cx
     */
    protected $cx;

    function __construct(Cx $cx)
    {
        $this->cx = $cx;
    }

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
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

    public function LoadMediaTypes(MediaBrowserConfiguration $mediaBrowserConfiguration)
    {
        global $_ARRAYLANG;
        \Env::get('init')->loadLanguageData('MediaBrowser');
        $mediaType = new MediaType();
        $mediaType->setName('shop');
        $mediaType->setHumanName($_ARRAYLANG['TXT_FILEBROWSER_SHOP']);
        $mediaType->setDirectory(array(
            $this->cx->getWebsiteImagesShopPath(),
            $this->cx->getWebsiteImagesShopWebPath(),
        ));
        $mediaType->getAccessIds(array(13));
        $mediaBrowserConfiguration->addMediaType($mediaType);
    }

}
