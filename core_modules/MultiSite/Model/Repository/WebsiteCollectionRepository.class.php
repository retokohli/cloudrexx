<?php

/**
 * Class WebsiteCollectionRepository
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

namespace Cx\Core_Modules\MultiSite\Model\Repository;

class WebsiteCollectionRepositoryException extends \Exception {}

/**
 * Class WebsiteCollectionRepository
 * 
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      Project Team SS4U <info@comvation.com>
 * @package     contrexx
 * @subpackage  coremodule_multisite
 */

class WebsiteCollectionRepository extends \Doctrine\ORM\EntityRepository {
    
    /**
     * find one for sale
     * 
     * @param array $productOptions
     * @param array $saleOptions
     */
    public function findOneForSale($productOptions, $saleOptions) { 
        global $_ARRAYLANG;
        
        $website           = null;
        $websiteCollection = null;
        $baseSubscription  = isset($saleOptions['baseSubscription']) ? $saleOptions['baseSubscription'] : '';
        if ($baseSubscription instanceof \Cx\Modules\Order\Model\Entity\Subscription) {
            $productEntity = $baseSubscription->getProductEntity();
            if ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
                \Env::get('em')->remove($baseSubscription);
                $website = $productEntity;
            } else if ($productEntity instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection) {
                // We have to unset WebsiteCollection from subscription before we call Subscription::terminate().
                // Otherwise the associated Websites will all be disabled due to the 'terminated' model event
                $baseSubscription->setProductEntity(null);
                $baseSubscription->terminate();
                $websiteCollection = $productEntity;
            }
        } else {            
            //Initialize new website
            $websiteThemeId = isset($saleOptions['themeId']) ? $saleOptions['themeId'] : null;
            $websiteName = isset($saleOptions['websiteName']) ? $saleOptions['websiteName'] : null;
            $customer = isset($saleOptions['customer']) ? $saleOptions['customer'] : null;
            $website = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->initWebsite($websiteName, $customer, $websiteThemeId);
        }
        
        if (!($websiteCollection instanceof \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection)) {
            $websiteCollection = new \Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection();
        }
        
        if ($productOptions['websiteCollectionQuota']) {
            $websiteCollection->setQuota($productOptions['websiteCollectionQuota']);
        }

        //If the $productOptions['websiteTemplate] is empty, take the value from multisite option defaultWebsiteTemplate
        if (empty($productOptions['websiteTemplate'])) {
            $productOptions['websiteTemplate'] = \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteTemplate');
        }
        
        //Assigning the websiteTemplate specified by the websiteTemplate of the selected Product.
        $websiteTemplate = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteTemplate')->findOneById($productOptions['websiteTemplate']);
        if (!$websiteTemplate) {
            throw new WebsiteCollectionRepositoryException($_ARRAYLANG['TXT_MULTISITE_WEBSITE_TEMPLATE_FAILED']);
        }
        $websiteCollection->setWebsiteTemplate($websiteTemplate);
        
        //assigning the initialized website to the website collection
        if ($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
            $websiteCollection->addWebsite($website);
            \Env::get('em')->persist($website);
        }

        //Persist website and websiteCollection to the db
        \Env::get('em')->persist($websiteCollection);
        //Flush the entity manager
        \Env::get('em')->flush();

        return $websiteCollection;
    }
}
