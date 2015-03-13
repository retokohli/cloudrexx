<?php

/**
 * MultiSiteTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */

namespace Cx\Core\Test\Model\Entity;

/**
 * MultiSiteTestCase
 *
 * @copyright   CONTREXX CMS - COMVATION AG
 * @author      COMVATION Development Team <info@comvation.com>
 * @author      ss4u <ss4u.comvation@gmail.com>
 * @package     contrexx
 * @subpackage  core_test
 */
abstract class MultiSiteTestCase extends ContrexxTestCase
{
    
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass() 
    {        
        \Cx\Core_Modules\MultiSite\Controller\ComponentController::$cxMainDomain = \Cx\Core\Setting\Controller\Setting::getValue('multiSiteDomain');
        $_SERVER['HTTPS'] = 'https://';
    }
    
    /**
     * Create a website for testing
     */
    public function createWebsite($websiteName)
    {
        $objFWUser   = \FWUser::getFWUserObject();
        
        /**
         * Creating website requires 2 parameters
         * 1. User object 
         * 2. Website name
         */
        $objUser     = $objFWUser->objUser->getUser(contrexx_input2raw(1));        // get the user object
        
        // check website is manager
        $basepath = \Cx\Core\Setting\Controller\Setting::getValue('websitePath');
        $websiteServiceServer = null;
        
        // When current server is a website manager then it will request the service server to create the website.
        // So we need to fetch the default website website service server.
        if (\Cx\Core\Setting\Controller\Setting::getValue('mode') == \Cx\Core_Modules\MultiSite\Controller\ComponentController::MODE_MANAGER) {
            //get default service server
            $defaultWebsiteServiceServer = self::$cx->getDb()->getEntityManager()->getRepository('Cx\Core_Modules\MultiSite\Model\Entity\WebsiteServiceServer')
            ->findBy(array('id' => \Cx\Core\Setting\Controller\Setting::getValue('defaultWebsiteServiceServer')));
            $websiteServiceServer = $defaultWebsiteServiceServer[0];
        }
        
        /**
         * create new website object and flush into database
         */
        $objWebsite = new \Cx\Core_Modules\MultiSite\Model\Entity\Website($basepath, $websiteName, $websiteServiceServer, $objUser, false);
        self::$cx->getDb()->getEntityManager()->persist($objWebsite);        
        self::$cx->getDb()->getEntityManager()->flush();
        
        // configure the website
        $objWebsite->setup(array('websiteTemplate' => 1));
    }
}
