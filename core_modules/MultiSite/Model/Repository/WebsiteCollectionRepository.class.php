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
        //create new entity of website collection
        $websiteCollection = new Cx\Core_Modules\MultiSite\Model\Entity\WebsiteCollection();
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
        
        //Initialize new website
        $websiteThemeId = isset($saleOptions['themeId']) ? $saleOptions['themeId'] : null;
        $website        = \Env::get('em')->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\Website')->initWebsite($saleOptions['websiteName'], $saleOptions['customer'], $websiteThemeId);
        
        //assigning the initialized website to the website collection
        if ($website instanceof \Cx\Core_Modules\MultiSite\Model\Entity\Website) {
            $websiteCollection->addWebsite($website);
        }
        
        //Persist website and websiteCollection to the db
        \Env::get('em')->persist($website);
        \Env::get('em')->persist($websiteCollection);
        //Flush the entity manager
        \Env::get('em')->flush();
    }
}