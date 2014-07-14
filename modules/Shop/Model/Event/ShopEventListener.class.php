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

/**
 * EventListener for Shop
 * 
 * @copyright   Comvation AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  module_shop
 */
class ShopEventListener implements \Cx\Core\Event\Model\Entity\EventListener {

    public function onEvent($eventName, array $eventArgs) {
        $this->$eventName(current($eventArgs));
    }
   
    public static function SearchFindContent($search) {
        $term_db = $search->getTerm();

        $flagIsReseller = false;
        $objUser = \FWUser::getFWUserObject()->objUser;

        if ($objUser->login()) {
            $objCustomer = \Cx\modules\Shop\Controller\Customer::getById($objUser->getId());
            \SettingDb::init('Shop', 'config');
            if ($objCustomer && $objCustomer->is_reseller()) {
                $flagIsReseller = true;
            }
        }

        $querySelect = $queryCount = $queryOrder = null;
        list($querySelect, $queryCount, $queryTail, $queryOrder) = \Cx\modules\Shop\Controller\Products::getQueryParts(null, null, null, $term_db, false, false, '', $flagIsReseller);
        $query = $querySelect . $queryTail . $queryOrder;//Search query
        $parseSearchData = function(&$searchData) {
                                $searchData['title']   = $searchData['name'];
                                $searchData['content'] = $searchData['long'] ? $searchData['long'] : $searchData['short'];
                                $searchData['score']   = $searchData['score1'] + $searchData['score2'] + $searchData['score3'];
                            };
        $result = new \Cx\Core_Modules\Listing\Model\Entity\DataSet($search->getResultArray($query, 'Shop', 'details', 'productId=', $search->getTerm(), $parseSearchData));
        $search->appendResult($result);
    }

}
